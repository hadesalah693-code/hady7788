<?php

namespace Tests\Feature\Account;

use App\Livewire\Account\Profile;
use App\Models\City;
use App\Models\Governorate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_prefills_the_authenticated_user_data(): void
    {
        $user = User::factory()->create([
            'name' => 'أحمد محمد',
            'phone' => '01012345678',
            'whatsapp' => '01098765432',
            'bio' => 'نبذة قصيرة',
            'avatar_path' => 'avatars/existing.jpg',
        ]);

        $this->actingAs($user);

        Livewire::test(Profile::class)
            ->assertSet('name', 'أحمد محمد')
            ->assertSet('email', $user->email)
            ->assertSet('phone', '01012345678')
            ->assertSet('whatsapp', '01098765432')
            ->assertSet('bio', 'نبذة قصيرة')
            ->assertSet('currentAvatar', 'avatars/existing.jpg');
    }

    public function test_user_can_update_their_own_personal_data(): void
    {
        $governorate = Governorate::factory()->create(['name_ar' => 'القاهرة']);
        $city = City::factory()->create(['governorate_id' => $governorate->id, 'name_ar' => 'مدينة نصر']);

        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test(Profile::class)
            ->set('name', 'اسم محدّث')
            ->set('email', 'updated@example.test')
            ->set('phone', '01112345678')
            ->set('whatsapp', '01198765432')
            ->set('bio', 'نبذة محدّثة عن الحساب')
            ->set('governorate_id', $governorate->id)
            ->set('city_id', $city->id)
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('savedMessage', __('app.account.profile.saved'));

        $user->refresh();

        $this->assertSame('اسم محدّث', $user->name);
        $this->assertSame('updated@example.test', $user->email);
        $this->assertSame('01112345678', $user->phone);
        $this->assertSame('01198765432', $user->whatsapp);
        $this->assertSame('نبذة محدّثة عن الحساب', $user->bio);
        $this->assertSame($governorate->id, $user->governorate_id);
        $this->assertSame($city->id, $user->city_id);
    }

    public function test_updating_the_governorate_resets_the_selected_city(): void
    {
        $first = Governorate::factory()->create(['name_ar' => 'القاهرة']);
        $second = Governorate::factory()->create(['name_ar' => 'الجيزة']);
        $city = City::factory()->create(['governorate_id' => $first->id]);

        $this->actingAs(User::factory()->create());

        Livewire::test(Profile::class)
            ->set('governorate_id', $first->id)
            ->set('city_id', $city->id)
            ->assertSet('city_id', $city->id)
            ->set('governorate_id', $second->id)
            ->assertSet('city_id', null);
    }

    public function test_name_is_required_and_must_have_a_minimum_length(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(Profile::class)
            ->set('name', '')
            ->call('save')
            ->assertHasErrors(['name' => 'required']);

        Livewire::test(Profile::class)
            ->set('name', 'أ')
            ->call('save')
            ->assertHasErrors(['name' => 'min']);
    }

    public function test_email_must_be_valid_and_unique(): void
    {
        $other = User::factory()->create(['email' => 'taken@example.test']);

        $this->actingAs(User::factory()->create());

        Livewire::test(Profile::class)
            ->set('email', 'not-an-email')
            ->call('save')
            ->assertHasErrors(['email' => 'email']);

        Livewire::test(Profile::class)
            ->set('email', $other->email)
            ->call('save')
            ->assertHasErrors(['email' => 'unique']);
    }

    public function test_phone_must_be_valid_and_unique(): void
    {
        $other = User::factory()->create(['phone' => '01055555555']);

        $this->actingAs(User::factory()->create());

        Livewire::test(Profile::class)
            ->set('phone', 'not-a-phone')
            ->call('save')
            ->assertHasErrors(['phone' => 'regex']);

        Livewire::test(Profile::class)
            ->set('phone', $other->phone)
            ->call('save')
            ->assertHasErrors(['phone' => 'unique']);
    }

    public function test_bio_cannot_exceed_one_thousand_characters(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(Profile::class)
            ->set('bio', str_repeat('ا', 1001))
            ->call('save')
            ->assertHasErrors(['bio' => 'max']);
    }

    public function test_city_must_belong_to_the_selected_governorate(): void
    {
        $first = Governorate::factory()->create(['name_ar' => 'القاهرة']);
        $second = Governorate::factory()->create(['name_ar' => 'الإسكندرية']);
        $foreignCity = City::factory()->create(['governorate_id' => $second->id]);

        $this->actingAs(User::factory()->create());

        Livewire::test(Profile::class)
            ->set('governorate_id', $first->id)
            ->set('city_id', $foreignCity->id)
            ->call('save')
            ->assertHasErrors(['city_id' => 'exists']);
    }

    public function test_user_can_upload_a_valid_avatar(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();

        $this->actingAs($user);

        Livewire::test(Profile::class)
            ->set('avatar', UploadedFile::fake()->image('avatar.jpg', 600, 600))
            ->call('save')
            ->assertHasNoErrors();

        $user->refresh();

        $this->assertNotNull($user->avatar_path);
        Storage::disk('public')->assertExists($user->avatar_path);
    }

    public function test_avatar_must_be_an_allowed_image_type(): void
    {
        Storage::fake('public');

        $this->actingAs(User::factory()->create());

        Livewire::test(Profile::class)
            ->set('avatar', UploadedFile::fake()->create('document.pdf', 100, 'application/pdf'))
            ->assertHasErrors(['avatar']);
    }

    public function test_avatar_cannot_exceed_two_megabytes(): void
    {
        Storage::fake('public');

        $this->actingAs(User::factory()->create());

        Livewire::test(Profile::class)
            ->set('avatar', UploadedFile::fake()->image('large.jpg', 600, 600)->size(3000))
            ->assertHasErrors(['avatar' => 'max']);
    }

    public function test_avatar_must_have_acceptable_dimensions(): void
    {
        Storage::fake('public');

        $this->actingAs(User::factory()->create());

        Livewire::test(Profile::class)
            ->set('avatar', UploadedFile::fake()->image('tiny.jpg', 50, 50))
            ->assertHasErrors(['avatar' => 'dimensions']);
    }

    public function test_replacing_the_avatar_deletes_the_previous_file(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('avatars/old.jpg', 'old');

        $user = User::factory()->create(['avatar_path' => 'avatars/old.jpg']);

        $this->actingAs($user);

        Livewire::test(Profile::class)
            ->set('avatar', UploadedFile::fake()->image('new.jpg', 600, 600))
            ->call('save')
            ->assertHasNoErrors();

        $user->refresh();

        Storage::disk('public')->assertMissing('avatars/old.jpg');
        Storage::disk('public')->assertExists($user->avatar_path);
    }

    public function test_user_can_remove_their_avatar(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('avatars/old.jpg', 'old');

        $user = User::factory()->create(['avatar_path' => 'avatars/old.jpg']);

        $this->actingAs($user);

        Livewire::test(Profile::class)
            ->call('removeAvatar')
            ->assertSet('currentAvatar', null)
            ->assertSet('savedMessage', __('app.account.profile.saved'));

        $this->assertNull($user->refresh()->avatar_path);
        Storage::disk('public')->assertMissing('avatars/old.jpg');
    }

    public function test_saving_the_profile_never_updates_another_user(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create(['name' => 'المستخدم الآخر', 'email' => 'other@example.test']);

        $this->actingAs($user);

        Livewire::test(Profile::class)
            ->set('name', 'الاسم الجديد')
            ->set('email', 'new@example.test')
            ->call('save')
            ->assertHasNoErrors();

        $other->refresh();

        $this->assertSame('المستخدم الآخر', $other->name);
        $this->assertSame('other@example.test', $other->email);
        $this->assertSame('الاسم الجديد', $user->refresh()->name);
    }
}
