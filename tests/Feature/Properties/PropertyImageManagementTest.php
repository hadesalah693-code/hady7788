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
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class PropertyImageManagementTest extends TestCase
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

    private function makeImage(Property $property, array $attributes = []): PropertyImage
    {
        $path = 'properties/'.$property->id.'/'.Str::uuid().'.jpg';

        Storage::disk('property-images')->put($path, 'image-bytes');

        return PropertyImage::factory()->create(array_merge([
            'property_id' => $property->id,
            'disk' => 'property-images',
            'path' => $path,
            'mime_type' => 'image/jpeg',
            'size' => 11,
            'sort_order' => 0,
            'is_cover' => false,
        ], $attributes));
    }

    public function test_setting_a_cover_keeps_exactly_one_cover(): void
    {
        $user = User::factory()->create();
        $property = $this->makeProperty($user);

        $first = $this->makeImage($property, ['sort_order' => 0, 'is_cover' => true]);
        $second = $this->makeImage($property, ['sort_order' => 1]);

        $this->actingAs($user);

        Livewire::test(PropertyImages::class, ['propertyId' => $property->id])
            ->call('setCover', $second->id)
            ->assertSet('message', 'تم تعيين الصورة الرئيسية.');

        $this->assertFalse($first->refresh()->is_cover);
        $this->assertTrue($second->refresh()->is_cover);
        $this->assertSame(1, PropertyImage::query()->where('is_cover', true)->count());
    }

    public function test_deleting_the_cover_promotes_the_next_image_and_removes_the_file(): void
    {
        $user = User::factory()->create();
        $property = $this->makeProperty($user);

        $cover = $this->makeImage($property, ['sort_order' => 0, 'is_cover' => true]);
        $next = $this->makeImage($property, ['sort_order' => 1]);

        $coverPath = $cover->path;

        $this->actingAs($user);

        Livewire::test(PropertyImages::class, ['propertyId' => $property->id])
            ->call('deleteImage', $cover->id)
            ->assertSet('message', 'تم حذف الصورة.');

        $this->assertModelMissing($cover);
        $this->assertTrue($next->refresh()->is_cover);
        $this->assertSame(0, $next->refresh()->sort_order);
        Storage::disk('property-images')->assertMissing($coverPath);
    }

    public function test_images_can_be_reordered_and_the_order_is_persisted(): void
    {
        $user = User::factory()->create();
        $property = $this->makeProperty($user);

        $first = $this->makeImage($property, ['sort_order' => 0, 'is_cover' => true]);
        $second = $this->makeImage($property, ['sort_order' => 1]);
        $third = $this->makeImage($property, ['sort_order' => 2]);

        $this->actingAs($user);

        Livewire::test(PropertyImages::class, ['propertyId' => $property->id])
            ->call('moveDown', $first->id)
            ->assertSet('message', 'تم تحديث ترتيب الصور.');

        $ordered = $property->images()->pluck('id')->all();
        $this->assertSame([$second->id, $first->id, $third->id], $ordered);
        $this->assertSame([0, 1, 2], $property->images()->pluck('sort_order')->all());

        Livewire::test(PropertyImages::class, ['propertyId' => $property->id])
            ->call('moveUp', $third->id);

        $this->assertSame([$second->id, $third->id, $first->id], $property->images()->pluck('id')->all());
        $this->assertSame([0, 1, 2], $property->images()->pluck('sort_order')->all());
    }

    public function test_replacing_an_image_keeps_position_and_cover_and_deletes_the_old_file(): void
    {
        $user = User::factory()->create();
        $property = $this->makeProperty($user);

        $image = $this->makeImage($property, [
            'sort_order' => 5,
            'is_cover' => true,
            'mime_type' => 'image/png',
            'path' => 'properties/'.$property->id.'/old.png',
        ]);

        Storage::disk('property-images')->put($image->path, 'old-bytes');
        $oldPath = $image->path;

        $this->actingAs($user);

        Livewire::test(PropertyImages::class, ['propertyId' => $property->id])
            ->call('startReplace', $image->id)
            ->set('replacement', UploadedFile::fake()->image('replacement.jpg', 400, 300))
            ->call('replace')
            ->assertSet('message', 'تم استبدال الصورة مع الحفاظ على ترتيبها.');

        $image->refresh();

        $this->assertSame(5, $image->sort_order);
        $this->assertTrue($image->is_cover);
        $this->assertSame('image/jpeg', $image->mime_type);
        $this->assertNotSame($oldPath, $image->path);
        $this->assertStringStartsWith('properties/'.$property->id.'/', $image->path);
        $this->assertStringNotContainsString('replacement', $image->path);

        Storage::disk('property-images')->assertMissing($oldPath);
        Storage::disk('property-images')->assertExists($image->path);
    }

    public function test_an_image_from_another_property_cannot_be_managed_through_idor(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();

        $property = $this->makeProperty($owner);
        $foreignProperty = $this->makeProperty($other);
        $foreignImage = $this->makeImage($foreignProperty, ['is_cover' => true]);

        $this->actingAs($owner);

        Livewire::test(PropertyImages::class, ['propertyId' => $property->id])
            ->call('setCover', $foreignImage->id)
            ->assertStatus(404);

        $this->assertTrue($foreignImage->refresh()->is_cover);
    }

    public function test_owner_cannot_manage_images_of_a_sold_or_rented_property(): void
    {
        $user = User::factory()->create();

        foreach ([PropertyStatus::Sold, PropertyStatus::Rented] as $status) {
            $property = $this->makeProperty($user, ['status' => $status]);
            $image = $this->makeImage($property, ['is_cover' => true]);

            $this->actingAs($user);

            Livewire::test(PropertyImages::class, ['propertyId' => $property->id])
                ->call('setCover', $image->id)
                ->assertForbidden();
        }
    }

    public function test_admin_can_manage_images_of_a_sold_property(): void
    {
        $owner = User::factory()->create();
        $admin = User::factory()->admin()->create();

        $property = $this->makeProperty($owner, ['status' => PropertyStatus::Sold]);
        $first = $this->makeImage($property, ['sort_order' => 0, 'is_cover' => true]);
        $second = $this->makeImage($property, ['sort_order' => 1]);

        $this->actingAs($admin);

        Livewire::test(PropertyImages::class, ['propertyId' => $property->id])
            ->call('setCover', $second->id)
            ->assertSet('message', 'تم تعيين الصورة الرئيسية.');

        $this->assertFalse($first->refresh()->is_cover);
        $this->assertTrue($second->refresh()->is_cover);
    }

    public function test_reordering_and_cover_changes_keep_a_published_property_published(): void
    {
        $user = User::factory()->create();
        $property = $this->makeProperty($user, [
            'status' => PropertyStatus::Published,
            'published_at' => now()->subDay(),
        ]);

        $first = $this->makeImage($property, ['sort_order' => 0, 'is_cover' => true]);
        $second = $this->makeImage($property, ['sort_order' => 1]);

        $this->actingAs($user);

        Livewire::test(PropertyImages::class, ['propertyId' => $property->id])
            ->call('setCover', $second->id);

        $this->assertSame(PropertyStatus::Published, $property->refresh()->status);

        Livewire::test(PropertyImages::class, ['propertyId' => $property->id])
            ->call('moveDown', $first->id);

        $this->assertSame(PropertyStatus::Published, $property->refresh()->status);
    }

    public function test_deleting_an_image_from_a_published_property_returns_it_to_review(): void
    {
        $user = User::factory()->create();
        $property = $this->makeProperty($user, [
            'status' => PropertyStatus::Published,
            'published_at' => now()->subDay(),
        ]);

        $cover = $this->makeImage($property, ['is_cover' => true]);
        $this->makeImage($property, ['sort_order' => 1]);

        $this->actingAs($user);

        Livewire::test(PropertyImages::class, ['propertyId' => $property->id])
            ->call('deleteImage', $cover->id);

        $this->assertSame(PropertyStatus::Pending, $property->refresh()->status);
        $this->assertNotNull($property->published_at);
    }

    public function test_an_archived_property_stays_archived_after_uploading_an_image(): void
    {
        $user = User::factory()->create();
        $property = $this->makeProperty($user, ['status' => PropertyStatus::Archived]);

        $this->actingAs($user);

        Livewire::test(PropertyImages::class, ['propertyId' => $property->id])
            ->set('photos', [UploadedFile::fake()->image('archived.jpg', 300, 300)])
            ->call('upload');

        $this->assertSame(PropertyStatus::Archived, $property->refresh()->status);
        $this->assertSame(1, $property->images()->count());
    }

    public function test_media_of_a_soft_deleted_property_cannot_be_managed(): void
    {
        $user = User::factory()->create();
        $property = $this->makeProperty($user);
        $image = $this->makeImage($property, ['is_cover' => true]);

        $this->actingAs($user);

        $component = Livewire::test(PropertyImages::class, ['propertyId' => $property->id]);

        $property->delete();

        $component
            ->call('setCover', $image->id)
            ->assertStatus(404);
    }
}
