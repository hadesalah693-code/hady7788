<?php

namespace Tests\Feature\Properties;

use App\Enums\PropertyStatus;
use App\Livewire\Properties\PropertyImages;
use App\Models\Property;
use App\Models\PropertyImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class PropertyImageUploadTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('property-images');
    }

    private function makeProperty(User $user, array $attributes = []): Property
    {
        return Property::factory()->create(array_merge([
            'user_id' => $user->id,
            'status' => PropertyStatus::Draft,
            'published_at' => null,
            'published_by' => null,
            'views_count' => 0,
            'is_featured' => false,
        ], $attributes));
    }

    public function test_owner_uploads_an_image_to_the_private_disk_as_the_cover(): void
    {
        $user = User::factory()->create();
        $property = $this->makeProperty($user);

        $this->actingAs($user);

        Livewire::test(PropertyImages::class, ['propertyId' => $property->id])
            ->set('photos', [UploadedFile::fake()->image('my-photo.jpg', 640, 480)])
            ->call('upload')
            ->assertSet('error', null)
            ->assertSet('message', 'تم رفع 1 صورة بنجاح.');

        $image = PropertyImage::query()->firstOrFail();

        $this->assertSame($property->id, $image->property_id);
        $this->assertSame('property-images', $image->disk);
        $this->assertSame('image/jpeg', $image->mime_type);
        $this->assertTrue($image->is_cover);
        $this->assertSame(0, $image->sort_order);
        $this->assertGreaterThan(0, $image->size);
        $this->assertStringStartsWith('properties/'.$property->id.'/', $image->path);
        $this->assertStringNotContainsString('my-photo', $image->path);
        $this->assertStringEndsWith('.jpg', $image->path);

        Storage::disk('property-images')->assertExists($image->path);
        $this->assertSame(PropertyStatus::Draft, $property->refresh()->status);
    }

    public function test_only_the_first_image_becomes_the_cover(): void
    {
        $user = User::factory()->create();
        $property = $this->makeProperty($user);

        $this->actingAs($user);

        Livewire::test(PropertyImages::class, ['propertyId' => $property->id])
            ->set('photos', [
                UploadedFile::fake()->image('one.jpg', 300, 300),
                UploadedFile::fake()->image('two.png', 300, 300),
            ])
            ->call('upload')
            ->assertSet('message', 'تم رفع 2 صورة بنجاح.');

        $images = PropertyImage::query()->orderBy('sort_order')->get();

        $this->assertCount(2, $images);
        $this->assertTrue($images[0]->is_cover);
        $this->assertFalse($images[1]->is_cover);
        $this->assertSame(0, $images[0]->sort_order);
        $this->assertSame(1, $images[1]->sort_order);
    }

    public function test_uploading_beyond_the_property_limit_is_rejected(): void
    {
        config(['properties.images.max_per_property' => 2]);

        $user = User::factory()->create();
        $property = $this->makeProperty($user);

        PropertyImage::factory()->count(2)->create([
            'property_id' => $property->id,
            'disk' => 'public',
            'path' => 'images/demo/apartment-1.svg',
        ]);

        $this->actingAs($user);

        $component = Livewire::test(PropertyImages::class, ['propertyId' => $property->id])
            ->set('photos', [UploadedFile::fake()->image('extra.jpg', 300, 300)])
            ->call('upload');

        $this->assertStringContainsString('لا يمكن إضافة أكثر من 2 صورة', (string) $component->get('error'));
        $this->assertSame(2, PropertyImage::query()->count());
    }

    public function test_a_file_that_is_not_a_real_image_is_rejected(): void
    {
        $user = User::factory()->create();
        $property = $this->makeProperty($user);

        $this->actingAs($user);

        $component = Livewire::test(PropertyImages::class, ['propertyId' => $property->id])
            ->set('photos', [UploadedFile::fake()->create('evil.jpg', 100, 'image/jpeg')])
            ->call('upload');

        $this->assertNotNull($component->get('error'));
        $this->assertSame(0, PropertyImage::query()->count());
        $this->assertSame([], Storage::disk('property-images')->allFiles());
    }

    public function test_svg_files_are_rejected(): void
    {
        $user = User::factory()->create();
        $property = $this->makeProperty($user);

        $this->actingAs($user);

        $svg = '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>';

        $component = Livewire::test(PropertyImages::class, ['propertyId' => $property->id])
            ->set('photos', [UploadedFile::fake()->createWithContent('icon.svg', $svg)])
            ->call('upload');

        $this->assertNotNull($component->get('error'));
        $this->assertSame(0, PropertyImage::query()->count());
    }

    public function test_images_larger_than_the_configured_size_are_rejected(): void
    {
        config(['properties.images.max_kb' => 1]);

        $user = User::factory()->create();
        $property = $this->makeProperty($user);

        $this->actingAs($user);

        $component = Livewire::test(PropertyImages::class, ['propertyId' => $property->id])
            ->set('photos', [UploadedFile::fake()->image('big.jpg', 2000, 2000)])
            ->call('upload');

        $this->assertStringContainsString('حجم الصورة أكبر من الحد المسموح', (string) $component->get('error'));
        $this->assertSame(0, PropertyImage::query()->count());
    }

    public function test_the_total_pixel_limit_is_enforced(): void
    {
        config(['properties.images.max_pixels' => 10000]);

        $user = User::factory()->create();
        $property = $this->makeProperty($user);

        $this->actingAs($user);

        $component = Livewire::test(PropertyImages::class, ['propertyId' => $property->id])
            ->set('photos', [UploadedFile::fake()->image('wide.jpg', 300, 300)])
            ->call('upload');

        $this->assertStringContainsString('عدد بكسلات الصورة كبير جدًا', (string) $component->get('error'));
        $this->assertSame(0, PropertyImage::query()->count());
    }

    public function test_non_owner_cannot_upload_to_another_users_property(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $property = $this->makeProperty($owner);

        $this->actingAs($owner);

        $component = Livewire::test(PropertyImages::class, ['propertyId' => $property->id])
            ->set('photos', [UploadedFile::fake()->image('intruder.jpg', 300, 300)]);

        $this->actingAs($other);

        $component->call('upload')->assertForbidden();

        $this->assertSame(0, PropertyImage::query()->count());
    }

    public function test_suspended_user_cannot_upload(): void
    {
        $owner = User::factory()->create();
        $property = $this->makeProperty($owner);

        $this->actingAs($owner);

        $component = Livewire::test(PropertyImages::class, ['propertyId' => $property->id])
            ->set('photos', [UploadedFile::fake()->image('suspended.jpg', 300, 300)]);

        $this->actingAs(User::factory()->suspended()->create());

        $component->call('upload')->assertForbidden();

        $this->assertSame(0, PropertyImage::query()->count());
    }

    public function test_uploading_to_a_published_property_returns_it_to_review(): void
    {
        $user = User::factory()->create();
        $property = $this->makeProperty($user, [
            'status' => PropertyStatus::Published,
            'published_at' => now()->subDay(),
        ]);

        $this->actingAs($user);

        Livewire::test(PropertyImages::class, ['propertyId' => $property->id])
            ->set('photos', [UploadedFile::fake()->image('new.jpg', 300, 300)])
            ->call('upload');

        $property->refresh();

        $this->assertSame(PropertyStatus::Pending, $property->status);
        $this->assertNotNull($property->published_at);
    }
}
