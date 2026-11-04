<?php

namespace App\Livewire\Properties;

use App\Livewire\Properties\Concerns\AuthorizesPropertyMedia;
use App\Services\PropertyImageService;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithFileUploads;
use Throwable;

class PropertyImages extends Component
{
    use AuthorizesPropertyMedia;
    use WithFileUploads;

    /** @var array<int, mixed> */
    public array $photos = [];

    /** @var mixed */
    public $replacement = null;

    public ?int $replacingImageId = null;

    public ?string $message = null;

    public ?string $error = null;

    public function mount(int $propertyId): void
    {
        $this->propertyId = $propertyId;

        $this->mediaProperty();
    }

    public function updatedPhotos(): void
    {
        $this->message = null;
        $this->error = null;
    }

    public function upload(): void
    {
        $this->resetFeedback();

        $property = $this->mediaProperty();
        $this->assertCanManage($property);

        try {
            $this->validate([
                'photos' => ['required', 'array', 'max:'.$this->maxImages()],
                'photos.*' => $this->imageRules(),
            ], $this->validationMessages());
        } catch (ValidationException $exception) {
            $this->error = $exception->validator->errors()->first();

            return;
        }

        $service = app(PropertyImageService::class);
        $stored = 0;
        $failures = [];

        foreach ($this->photos as $photo) {
            try {
                $service->store($property, $photo);
                $stored++;
            } catch (ValidationException $exception) {
                $failures[] = $exception->validator->errors()->first();
            } catch (Throwable $exception) {
                report($exception);
                $failures[] = 'تعذر رفع إحدى الصور. جرّب مرة أخرى.';
            }
        }

        $this->photos = [];

        if ($stored > 0) {
            $this->message = 'تم رفع '.$stored.' صورة بنجاح.';
        }

        if ($failures !== []) {
            $this->error = implode(' ', array_values(array_unique($failures)));
        }
    }

    public function removePhoto(int $index): void
    {
        if (! array_key_exists($index, $this->photos)) {
            return;
        }

        unset($this->photos[$index]);
        $this->photos = array_values($this->photos);
    }

    public function setCover(int $imageId): void
    {
        $this->resetFeedback();

        $property = $this->mediaProperty();
        $this->assertCanManage($property);

        $image = $property->images()->findOrFail($imageId);

        app(PropertyImageService::class)->setCover($property, $image);

        $this->message = 'تم تعيين الصورة الرئيسية.';
    }

    public function moveUp(int $imageId): void
    {
        $this->move($imageId, -1);
    }

    public function moveDown(int $imageId): void
    {
        $this->move($imageId, 1);
    }

    public function deleteImage(int $imageId): void
    {
        $this->resetFeedback();

        $property = $this->mediaProperty();
        $this->assertCanManage($property);

        $image = $property->images()->findOrFail($imageId);

        app(PropertyImageService::class)->delete($property, $image);

        $this->message = 'تم حذف الصورة.';
    }

    public function startReplace(int $imageId): void
    {
        $this->resetFeedback();

        $property = $this->mediaProperty();
        $this->assertCanManage($property);

        $property->images()->findOrFail($imageId);

        $this->replacingImageId = $imageId;
        $this->replacement = null;
    }

    public function cancelReplace(): void
    {
        $this->replacingImageId = null;
        $this->replacement = null;
    }

    public function replace(): void
    {
        $this->resetFeedback();

        $property = $this->mediaProperty();
        $this->assertCanManage($property);

        if ($this->replacingImageId === null) {
            return;
        }

        $image = $property->images()->findOrFail($this->replacingImageId);

        try {
            $this->validate([
                'replacement' => $this->imageRules(),
            ], $this->validationMessages());
        } catch (ValidationException $exception) {
            $this->error = $exception->validator->errors()->first();

            return;
        }

        try {
            app(PropertyImageService::class)->replace($property, $image, $this->replacement);
        } catch (ValidationException $exception) {
            $this->error = $exception->validator->errors()->first();

            return;
        } catch (Throwable $exception) {
            report($exception);
            $this->error = 'تعذر استبدال الصورة. جرّب مرة أخرى.';

            return;
        }

        $this->replacingImageId = null;
        $this->replacement = null;
        $this->message = 'تم استبدال الصورة مع الحفاظ على ترتيبها.';
    }

    public function render(): View
    {
        $property = $this->mediaProperty();

        $property->load(['images' => fn ($query) => $query->orderBy('sort_order')->orderBy('id')]);

        return view('livewire.properties.property-images', [
            'property' => $property,
            'canManage' => $this->canManage($property),
            'maxImages' => $this->maxImages(),
            'maxSizeLabel' => $this->maxSizeLabel(),
        ]);
    }

    private function move(int $imageId, int $offset): void
    {
        $this->resetFeedback();

        $property = $this->mediaProperty();
        $this->assertCanManage($property);

        $image = $property->images()->findOrFail($imageId);

        app(PropertyImageService::class)->move($property, $image, $offset);

        $this->message = 'تم تحديث ترتيب الصور.';
    }

    /**
     * @return array<int, string>
     */
    private function imageRules(): array
    {
        return [
            'required',
            'file',
            'image',
            'mimes:'.implode(',', (array) config('properties.images.mimes', [])),
            'mimetypes:'.implode(',', (array) config('properties.images.mimetypes', [])),
            'max:'.$this->maxKb(),
        ];
    }

    /**
     * @return array<string, string>
     */
    private function validationMessages(): array
    {
        $size = $this->maxSizeLabel();

        return [
            'photos.required' => 'اختر صورة واحدة على الأقل.',
            'photos.max' => 'لا يمكن رفع أكثر من '.$this->maxImages().' صورة في المرة الواحدة.',
            'photos.*.required' => 'تعذر قراءة إحدى الصور المحددة.',
            'photos.*.file' => 'تعذر رفع إحدى الصور المحددة.',
            'photos.*.image' => 'الملف المرفوع ليس صورة صالحة.',
            'photos.*.mimes' => 'صيغة الصورة غير مدعومة. المسموح: JPG وPNG وWEBP.',
            'photos.*.mimetypes' => 'نوع محتوى الصورة غير مسموح به.',
            'photos.*.max' => 'حجم الصورة أكبر من الحد المسموح به ('.$size.').',
            'replacement.required' => 'اختر صورة للاستبدال.',
            'replacement.file' => 'تعذر رفع الصورة المحددة.',
            'replacement.image' => 'الملف المرفوع ليس صورة صالحة.',
            'replacement.mimes' => 'صيغة الصورة غير مدعومة. المسموح: JPG وPNG وWEBP.',
            'replacement.mimetypes' => 'نوع محتوى الصورة غير مسموح به.',
            'replacement.max' => 'حجم الصورة أكبر من الحد المسموح به ('.$size.').',
        ];
    }

    private function maxImages(): int
    {
        return (int) config('properties.images.max_per_property', 20);
    }

    private function maxKb(): int
    {
        return (int) config('properties.images.max_kb', 5120);
    }

    private function maxSizeLabel(): string
    {
        $kilobytes = $this->maxKb();

        return $kilobytes >= 1024
            ? rtrim(rtrim(number_format($kilobytes / 1024, 1, '.', ''), '0'), '.').' ميجابايت'
            : $kilobytes.' كيلوبايت';
    }

    private function resetFeedback(): void
    {
        $this->message = null;
        $this->error = null;
    }
}
