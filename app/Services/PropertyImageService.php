<?php

namespace App\Services;

use App\Enums\PropertyStatus;
use App\Models\Property;
use App\Models\PropertyImage;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

/**
 * Handles the full lifecycle of a property image.
 *
 * Every uploaded file is verified (real image, allowed MIME, safe dimensions),
 * re-encoded through GD to strip EXIF and embedded payloads, then stored on a
 * private disk. Database writes run inside a transaction with a row lock on
 * the property so concurrent uploads cannot exceed the limit or create more
 * than one cover image.
 */
class PropertyImageService
{
    public function disk(): string
    {
        return (string) config('properties.images.disk', 'property-images');
    }

    public function store(Property $property, UploadedFile $file, ?string $alt = null): PropertyImage
    {
        $processed = $this->process($file);

        $disk = $this->disk();
        $path = $this->pathFor($property, $processed['extension']);

        Storage::disk($disk)->put($path, $processed['contents']);

        try {
            return DB::transaction(function () use ($property, $processed, $disk, $path, $alt): PropertyImage {
                $locked = $this->lockProperty($property);

                $max = (int) config('properties.images.max_per_property', 20);
                $count = $locked->images()->count();

                if ($count >= $max) {
                    throw ValidationException::withMessages([
                        'photos' => 'لا يمكن إضافة أكثر من '.$max.' صورة للعقار الواحد.',
                    ]);
                }

                $nextOrder = $count === 0
                    ? 0
                    : ((int) $locked->images()->max('sort_order')) + 1;

                $image = $locked->images()->create([
                    'path' => $path,
                    'disk' => $disk,
                    'mime_type' => $processed['mime'],
                    'size' => strlen($processed['contents']),
                    'alt' => $alt,
                    'sort_order' => $nextOrder,
                    'is_cover' => $count === 0,
                ]);

                $this->returnToReview($locked);

                return $image;
            });
        } catch (Throwable $exception) {
            Storage::disk($disk)->delete($path);

            throw $exception;
        }
    }

    public function replace(Property $property, PropertyImage $image, UploadedFile $file): PropertyImage
    {
        $processed = $this->process($file);

        $disk = $this->disk();
        $path = $this->pathFor($property, $processed['extension']);

        Storage::disk($disk)->put($path, $processed['contents']);

        try {
            /** @var array{image: PropertyImage, old: array{disk: string, path: string}} $result */
            $result = DB::transaction(function () use ($property, $image, $processed, $disk, $path): array {
                $locked = $this->lockProperty($property);
                $target = $locked->images()->whereKey($image->getKey())->firstOrFail();

                $old = ['disk' => $target->diskName(), 'path' => $target->path];

                // Keep the existing position, cover flag and alt text.
                $target->path = $path;
                $target->disk = $disk;
                $target->mime_type = $processed['mime'];
                $target->size = strlen($processed['contents']);
                $target->save();

                $this->returnToReview($locked);

                return ['image' => $target, 'old' => $old];
            });
        } catch (Throwable $exception) {
            Storage::disk($disk)->delete($path);

            throw $exception;
        }

        $this->deleteFile($result['old']['disk'], $result['old']['path'], $path);

        return $result['image'];
    }

    public function delete(Property $property, PropertyImage $image): void
    {
        /** @var array{disk: string, path: string} $removed */
        $removed = DB::transaction(function () use ($property, $image): array {
            $locked = $this->lockProperty($property);
            $target = $locked->images()->whereKey($image->getKey())->firstOrFail();

            $removed = ['disk' => $target->diskName(), 'path' => $target->path];
            $wasCover = $target->is_cover;

            $target->delete();

            if ($wasCover) {
                $next = $locked->images()->orderBy('sort_order')->orderBy('id')->first();

                if ($next !== null) {
                    $next->is_cover = true;
                    $next->save();
                }
            }

            $this->normalizeOrder($locked);
            $this->returnToReview($locked);

            return $removed;
        });

        // Only files owned by this platform are removed. Legacy demo images
        // are shared public assets and must never be deleted here.
        if ($removed['disk'] === $this->disk()) {
            Storage::disk($removed['disk'])->delete($removed['path']);
        }
    }

    public function setCover(Property $property, PropertyImage $image): void
    {
        DB::transaction(function () use ($property, $image): void {
            $locked = $this->lockProperty($property);
            $target = $locked->images()->whereKey($image->getKey())->firstOrFail();

            $locked->images()->where('is_cover', true)->update(['is_cover' => false]);

            $target->is_cover = true;
            $target->save();
        });
    }

    /**
     * Move an image one position up (-1) or down (+1) and persist the new
     * order as a dense 0..n-1 sequence.
     */
    public function move(Property $property, PropertyImage $image, int $offset): void
    {
        if (! in_array($offset, [-1, 1], true)) {
            return;
        }

        DB::transaction(function () use ($property, $image, $offset): void {
            $locked = $this->lockProperty($property);

            $images = $locked->images()
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get()
                ->values()
                ->all();

            $index = null;

            foreach ($images as $position => $row) {
                if ($row->getKey() === $image->getKey()) {
                    $index = $position;
                    break;
                }
            }

            if ($index === null) {
                throw (new ModelNotFoundException)->setModel(PropertyImage::class, [$image->getKey()]);
            }

            $swap = $index + $offset;

            if ($swap < 0 || $swap >= count($images)) {
                return;
            }

            [$images[$index], $images[$swap]] = [$images[$swap], $images[$index]];

            foreach ($images as $position => $row) {
                if ($row->sort_order !== $position) {
                    $row->sort_order = $position;
                    $row->save();
                }
            }
        });
    }

    private function lockProperty(Property $property): Property
    {
        return Property::query()->lockForUpdate()->findOrFail($property->getKey());
    }

    private function normalizeOrder(Property $property): void
    {
        $property->images()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->each(function (PropertyImage $image, int $position): void {
                if ($image->sort_order !== $position) {
                    $image->sort_order = $position;
                    $image->save();
                }
            });
    }

    /**
     * A published listing whose gallery changed must return to review. The
     * publishing metadata is intentionally kept.
     */
    private function returnToReview(Property $property): void
    {
        if ($property->status === PropertyStatus::Published) {
            $property->status = PropertyStatus::Pending;
            $property->save();
        }
    }

    private function pathFor(Property $property, string $extension): string
    {
        return 'properties/'.$property->getKey().'/'.Str::uuid().'.'.$extension;
    }

    private function deleteFile(string $disk, string $path, string $newPath): void
    {
        if ($path === $newPath) {
            return;
        }

        if ($disk !== $this->disk()) {
            return;
        }

        Storage::disk($disk)->delete($path);
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

        $maxKb = (int) config('properties.images.max_kb', 5120);

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

        if (! in_array($mime, (array) config('properties.images.mimetypes', []), true)) {
            throw $this->invalid('نوع محتوى الصورة غير مسموح به.');
        }

        $detectedMime = $file->getMimeType();

        if ($detectedMime !== null && $detectedMime !== $mime) {
            throw $this->invalid('نوع محتوى الصورة لا يطابق صيغتها الحقيقية.');
        }

        $minWidth = (int) config('properties.images.min_width', 200);
        $minHeight = (int) config('properties.images.min_height', 200);
        $maxWidth = (int) config('properties.images.max_width', 6000);
        $maxHeight = (int) config('properties.images.max_height', 6000);
        $maxPixels = (int) config('properties.images.max_pixels', 36000000);

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

        $image = $this->applyOrientation($image, $realPath, $mime);

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

    private function applyOrientation(\GdImage $image, string $realPath, string $mime): \GdImage
    {
        if ($mime !== 'image/jpeg' || ! function_exists('exif_read_data')) {
            return $image;
        }

        $exif = @exif_read_data($realPath);
        $orientation = (int) ($exif['Orientation'] ?? 1);

        $rotate = match ($orientation) {
            3, 4 => 180,
            5, 6 => -90,
            7, 8 => 90,
            default => 0,
        };

        if ($rotate !== 0) {
            $rotated = imagerotate($image, $rotate, 0);

            if ($rotated !== false) {
                imagedestroy($image);
                $image = $rotated;
            }
        }

        if (in_array($orientation, [2, 4, 5, 7], true)) {
            imageflip($image, in_array($orientation, [4, 7], true) ? IMG_FLIP_VERTICAL : IMG_FLIP_HORIZONTAL);
        }

        return $image;
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
