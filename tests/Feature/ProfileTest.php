<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_roles_can_edit_their_own_identity_without_changing_role(): void
    {
        foreach (['admin', 'gudang', 'pimpinan'] as $role) {
            $user = User::factory()->create(['role' => $role]);
            $this->actingAs($user)->get('/profile')->assertOk()->assertSee('Profil Saya');
            $this->patch('/profile', ['name' => 'Nama Baru', 'username' => 'new-'.$role, 'email' => $role.'@example.com', 'role' => 'admin'])->assertRedirect('/profile');
            $this->assertSame($role, $user->fresh()->role);
            $this->assertSame('Nama Baru', $user->fresh()->name);
        }
    }

    public function test_duplicate_email_username_and_invalid_photo_are_rejected(): void
    {
        $other = User::factory()->create();
        $user = User::factory()->create();
        $this->actingAs($user)->patch('/profile', [
            'name' => 'Name', 'email' => $other->email, 'username' => $other->username,
            'photo' => UploadedFile::fake()->createWithContent('photo.svg', '<svg xmlns="http://www.w3.org/2000/svg"></svg>'),
        ])->assertSessionHasErrors(['email', 'username', 'photo']);
        $this->assertSame($user->email, $user->fresh()->email);
    }

    public function test_photo_is_stored_in_database_served_privately_and_can_be_removed(): void
    {
        $user = User::factory()->create(['username' => 'photo-user']);
        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jRZkAAAAASUVORK5CYII=');
        $data = ['name' => $user->name, 'username' => $user->username, 'email' => $user->email];
        $this->actingAs($user)->patch('/profile', $data + ['photo' => UploadedFile::fake()->createWithContent('photo.png', $png)])->assertSessionHasNoErrors()->assertRedirect('/profile');
        $this->get('/profile/photo')->assertOk()->assertHeader('Content-Type', 'image/png')->assertContent($png);
        $other = User::factory()->create();
        $this->actingAs($other)->get('/profile/photo')->assertHeader('Content-Type', 'image/svg+xml');
        $this->actingAs($user)->patch('/profile', $data + ['remove_photo' => '1'])->assertRedirect('/profile');
        $this->assertDatabaseCount('user_profile_photos', 0);
    }

    public function test_password_requires_current_password_confirmation_and_rotates_credentials(): void
    {
        $user = User::factory()->create(['password' => 'old-password-123', 'remember_token' => 'old-token']);
        $this->actingAs($user)->put('/profile/password', ['current_password' => 'wrong', 'password' => 'new-password-123', 'password_confirmation' => 'different'])->assertSessionHasErrors(['current_password', 'password']);
        $this->assertTrue(Hash::check('old-password-123', $user->fresh()->password));
        DB::table('password_reset_tokens')->insert(['email' => $user->email, 'token' => 'old-reset-token', 'created_at' => now()]);
        $this->put('/profile/password', ['current_password' => 'old-password-123', 'password' => 'new-password-123', 'password_confirmation' => 'new-password-123'])->assertRedirect('/profile');
        $this->assertTrue(Hash::check('new-password-123', $user->fresh()->password));
        $this->assertNotSame('old-token', $user->fresh()->remember_token);
        $this->assertDatabaseCount('password_reset_tokens', 0);
    }

    public function test_guests_cannot_read_or_update_profiles(): void
    {
        $this->get('/profile')->assertRedirect('/login');
        $this->get('/profile/photo')->assertRedirect('/login');
        $this->patch('/profile', [])->assertRedirect('/login');
        $this->put('/profile/password', [])->assertRedirect('/login');
    }

    public function test_ten_mb_photo_is_accepted_and_compressed_but_larger_upload_is_rejected(): void
    {
        $user = User::factory()->create(['username' => 'large-photo-user']);
        $data = ['name' => $user->name, 'username' => $user->username, 'email' => $user->email];
        $this->actingAs($user)->patch('/profile', $data + ['photo' => UploadedFile::fake()->image('large.png', 800, 800)->size(10240)])
            ->assertSessionHasNoErrors()->assertRedirect('/profile');
        $photo = DB::table('user_profile_photos')->where('user_id', $user->id)->first();
        $this->assertSame('image/jpeg', $photo->mime);
        $bytes = base64_decode($photo->data);
        $this->assertLessThan(512 * 1024, strlen($bytes));
        $this->assertSame(512, getimagesizefromstring($bytes)[0]);
        $this->patch('/profile', $data + ['photo' => UploadedFile::fake()->image('too-large.png')->size(10241)])
            ->assertSessionHasErrors('photo');
        $this->assertSame($photo->data, DB::table('user_profile_photos')->where('user_id', $user->id)->value('data'));
    }

    public function test_high_resolution_photo_is_resized_even_when_file_is_small(): void
    {
        $user = User::factory()->create(['username' => 'high-resolution-user']);
        $data = ['name' => $user->name, 'username' => $user->username, 'email' => $user->email];
        $this->actingAs($user)->patch('/profile', $data + ['photo' => UploadedFile::fake()->image('camera.jpg', 4096, 3072)])
            ->assertSessionHasNoErrors()->assertRedirect('/profile');
        $photo = DB::table('user_profile_photos')->where('user_id', $user->id)->first();
        $dimensions = getimagesizefromstring(base64_decode($photo->data));
        $this->assertSame(512, $dimensions[0]);
        $this->assertSame(384, $dimensions[1]);
        $this->patch('/profile', $data + ['photo' => UploadedFile::fake()->image('oversized.jpg', 8193, 1)])
            ->assertSessionHasErrors('photo');
    }
}
