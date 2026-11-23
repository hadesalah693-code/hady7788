<?php

namespace App\Services;

use App\Models\ServiceProvider;
use App\Models\ServiceProviderImage;
use App\Models\ServiceRequest;
use App\Models\ServiceRequestImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

/**
 * Validates, re-encodes and stores service images.
 *
 * Customer request photos live on a private disk and are only reachable
 * through a guarded route. Portfolio photos are public because providers use
 * them to market their work.
 */
class ServiceImageService
{
    public function requestDisk(): string
    {
        return (string) config('service_module.images.disk', 'service-images');
    }

    public function storeForRequest(ServiceRequest $request, UploadedFile $file): ServiceRequestImage
    {
        $max = (int) config('service_module.images.max_per_request', 5);

        if ($request->images()->count() >= $max) {
            throw $this->invalid('لا يمكن إضافة أكثر من '.$max.' صور للطلب الواحد.');
        }

        $processed = $this->process($file);
        $disk = $this->requestDisk();
        $path = 'service-requests/'.$request->getKey().'/'.Str::uuid().'.'.$processed['extension'];

        Storage::disk($disk)->put($path, $processed['contents']);

        try {
            return $request->images()->create([
                'path' => $path,
                'disk' => $disk,
                'mime_type' => $processed['mime'],
                'size' => strlen($processed['contents']),
            ]);
        } catch (Throwable $exception) {
            Storage::disk($disk)->delete($path);

            throw $exception;
        }
    }

    public function storeForProvider(ServiceProvider $provider, UploadedFile $file): ServiceProviderImage
    {
        $max = (int) config('service_module.images.max_per_provider', 8);

        if ($provider->images()->count() >= $max) {
            throw $this->invalid('لا يمكن إضافة أكثر من '.$max.' صور لأعمالك السابقة.');
        }

        $processed = $this->process($file);
        $disk = 'public';
        $path = 'service-providers/'.$provider->getKey().'/'.Str::uuid().'.'.$processed['extension'];

        Storage::disk($disk)->put($path, $processed['contents'], ['visibility' => 'public']);

        try {
            return $provider->images()->create([
                'path' => $path,
                'disk' => $disk,
                'mime_type' => $processed['mime'],
                'size' => strlen($processed['contents']),
                'sort_order' => (int) $provider->images()->max('sort_order') + 1,
            ]);
        } catch (Throwable $exception) {
            Storage::disk($disk)->delete($path);

            throw $exception;
        }
    }

    public function deleteRequestImage(ServiceRequestImage $image): void
    {
        Storage::disk($image->disk ?: $this->requestDisk())->delete($image->path);
        $image->delete();
    }

    public function deleteProviderImage(ServiceProviderImage $image): void
    {
        Storage::disk($image->disk ?: 'public')->delete($image->path);
        $image->delete();
    }

    /**
     * Verify the uploaded file and return safe, re-encoded bytes.
     *
     * @return array{contents: string, mime: string, extension: string}
     */
    private function process(UploadedFile $file): array
    {
        $realPath = $file->getRealPath();

        if ($realPath === false || ! is_file($realPath)) {
            throw $this->invalid('تعذر قراءة الملف المرفوع.');
        }

        $maxKb = (int) config('service_module.images.max_kb', 5120);

        if ((int) $file->getSize() > $maxKb * 1024) {
            throw $this->invalid('حجم الصورة أكبر من الحد المسموح به ('.$this->formatSize($maxKb).').');
        }

        $info = @getimagesize($realPath);

        if ($info === false) {
            throw $this->invalid('الملف المرفوع ليس صورة صالحة.');
        }

        $width = (int) ($info[0] ?? 0);
        $height = (int) ($info[1] ?? 0);
        $mime = (string) ($info['mime'] ?? '');

        $allowed = [
            IMAGETYPE_JPEG => ['mime' => 'image/jpeg', 'extension' => 'jpg'],
            IMAGETYPE_PNG => ['mime' => 'image/png', 'extension' => 'png'],
            IMAGETYPE_WEBP => ['mime' => 'image/webp', 'extension' => 'webp'],
        ];

        $type = @exif_imagetype($realPath);

        if ($type === false || ! isset($allowed[$type])) {
            throw $this->invalid('صيغة الصورة غير مدعومة. المسموح: JPG وPNG وWEBP.');
        }

        if ($allowed[$type]['mime'] !== $mime) {
            throw $this->invalid('نوع محتوى الصورة لا يطابق صيغتها الحقيقية.');
        }

        if (! in_array($mime, (array) config('service_module.images.mimetypes', []), true)) {
            throw $this->invalid('نوع محتوى الصورة غير مسموح به.');
        }

        $detectedMime = $file->getMimeType();

        if ($detectedMime !== null && $detectedMime !== $mime) {
            throw $this->invalid('نوع محتوى الصورة لا يطابق صيغتها الحقيقية.');
        }

        $minWidth = (int) config('service_module.images.min_width', 200);
        $minHeight = (int) config('service_module.images.min_height', 200);
        $maxWidth = (int) config('service_module.images.max_width', 6000);
        $maxHeight = (int) config('service_module.images.max_height', 6000);
        $maxPixels = (int) config('service_module.images.max_pixels', 36_000_000);

        if ($width < $minWidth || $height < $minHeight) {
            throw $this->invalid('أبعاد الصورة صغيرة جدًا. الحد الأدنى '.$minWidth.'×'.$minHeight.' بكسل.');
        }

        if ($width > $maxWidth || $height > $maxHeight) {
            throw $this->invalid('أبعاد الصورة كبيرة جدًا. الحد الأقصى '.$maxWidth.'×'.$maxHeight.' بكسل.');
        }

        if (($width * $height) > $maxPixels) {
            throw $this->invalid('عدد بكسلات الصورة كبير جدًا. استخدم صورة أصغر.');
        }

        $contents = @file_get_contents($realPath);

        if ($contents === false) {
            throw $this->invalid('تعذر قراءة محتوى الصورة.');
        }

        $image = @imagecreatefromstring($contents);

        if ($image === false) {
            throw $this->invalid('الملف المرفوع ليس صورة صالحة.');
        }

        ob_start();

        $encoded = match ($mime) {
            'image/jpeg' => imagejpeg($image, null, 85),
            'image/png' => imagepng($image, null, 6),
            'image/webp' => imagewebp($image, null, 85),
        };

        $output = ob_get_clean();

        imagedestroy($image);

        if ($encoded !== true || $output === false || $output === '') {
            throw $this->invalid('تعذر معالجة الصورة. جرّب صورة أخرى.');
        }

        return [
            'contents' => $output,
            'mime' => $mime,
            'extension' => $allowed[$type]['extension'],
        ];
    }

    private function invalid(string $message): ValidationException
    {
        return ValidationException::withMessages(['photos' => $message]);
    }

    private function formatSize(int $kilobytes): string
    {
        return $kilobytes >= 1024
            ? rtrim(rtrim(number_format($kilobytes / 1024, 1, '.', ''), '0'), '.').' ميجابايت'
            : $kilobytes.' كيلوبايت';
    }
}
