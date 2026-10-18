<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DeploymentAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_bootstrap_creates_only_one_admin_and_never_resets_existing_password(): void
    {
        config(['deployment.admin_email' => 'owner@example.com', 'deployment.admin_password' => 'initial-secret-12345']);
        $this->artisan('deploy:admin')->assertSuccessful();
        $admin = User::sole();
        $this->assertSame('admin', $admin->role);
        $this->assertTrue(Hash::check('initial-secret-12345', $admin->password));
        config(['deployment.admin_password' => 'changed-secret-12345']);
        $this->artisan('deploy:admin')->assertSuccessful();
        $this->assertDatabaseCount('users', 1);
        $this->assertTrue(Hash::check('initial-secret-12345', $admin->fresh()->password));
        $this->assertDatabaseCount('items', 0);
    }
}
