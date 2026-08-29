<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Livewire\Profile\MyAccount;
use App\Livewire\Users\Index;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class MyAccountTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superAdmin = User::factory()->create([
            'name' => 'Super Admin Officer',
            'username' => 'superadmin',
            'email' => 'superadmin@sulop.gov.ph',
            'password' => Hash::make('OldPassword123!'),
            'role' => UserRole::SuperAdmin,
            'is_active' => true,
        ]);

        $this->admin = User::factory()->create([
            'name' => 'Regular Admin Officer',
            'username' => 'regularadmin',
            'email' => 'admin@sulop.gov.ph',
            'password' => Hash::make('AdminPassword123!'),
            'role' => UserRole::Admin,
            'permissions' => ['budget', 'distribution'],
            'is_active' => true,
        ]);
    }

    public function test_authenticated_user_can_access_my_account(): void
    {
        // Admin access
        $response = $this->actingAs($this->admin)->get(route('profile.index'));
        $response->assertOk();
        $response->assertSeeLivewire(MyAccount::class);
        $response->assertSee('Personal Details');
        $response->assertSee('Regular Admin Officer');

        // SuperAdmin access
        $response = $this->actingAs($this->superAdmin)->get(route('profile.index'));
        $response->assertOk();
        $response->assertSeeLivewire(MyAccount::class);
        $response->assertSee('Super Admin Officer');
    }

    public function test_guest_cannot_access_my_account(): void
    {
        $response = $this->get(route('profile.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_admin_is_strictly_forbidden_from_user_management(): void
    {
        $response = $this->actingAs($this->admin)->get(route('users.index'));
        $response->assertForbidden();
    }

    public function test_user_can_update_profile_details(): void
    {
        Livewire::actingAs($this->admin)
            ->test(MyAccount::class)
            ->set('name', 'Admin Officer Updated')
            ->set('username', 'adminupdated')
            ->set('email', 'admin.updated@sulop.gov.ph')
            ->call('updateProfile')
            ->assertDispatched('toast', function ($event, $params) {
                return ($params['type'] ?? '') === 'success';
            });

        $this->admin->refresh();
        $this->assertEquals('Admin Officer Updated', $this->admin->name);
        $this->assertEquals('adminupdated', $this->admin->username);
        $this->assertEquals('admin.updated@sulop.gov.ph', $this->admin->email);
    }

    public function test_user_can_upload_and_remove_avatar(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('custom_avatar.png');

        Livewire::actingAs($this->admin)
            ->test(MyAccount::class)
            ->set('avatar', $file)
            ->call('updateProfile')
            ->assertDispatched('toast', function ($event, $params) {
                return ($params['type'] ?? '') === 'success';
            });

        $this->admin->refresh();
        $this->assertNotNull($this->admin->avatar_url);
        $this->assertStringStartsWith('/storage/avatars/', $this->admin->avatar_url);

        // Remove Avatar
        Livewire::actingAs($this->admin)
            ->test(MyAccount::class)
            ->call('removeAvatar')
            ->assertDispatched('toast', function ($event, $params) {
                return ($params['type'] ?? '') === 'info';
            });

        $this->admin->refresh();
        $this->assertNull($this->admin->avatar_url);
    }

    public function test_user_can_change_password_with_valid_current_password(): void
    {
        Livewire::actingAs($this->admin)
            ->test(MyAccount::class)
            ->set('current_password', 'AdminPassword123!')
            ->set('new_password', 'BrandNewPassword123!')
            ->set('new_password_confirmation', 'BrandNewPassword123!')
            ->call('updatePassword')
            ->assertDispatched('toast', function ($event, $params) {
                return ($params['type'] ?? '') === 'success';
            });

        $this->admin->refresh();
        $this->assertTrue(Hash::check('BrandNewPassword123!', $this->admin->password));
    }

    public function test_superadmin_reset_flags_must_change_password(): void
    {
        Livewire::actingAs($this->superAdmin)
            ->test(Index::class)
            ->call('openPasswordModal', $this->admin->id)
            ->set('new_password', 'TemporaryResetPass123!')
            ->set('new_password_confirmation', 'TemporaryResetPass123!')
            ->call('resetPassword');

        $this->admin->refresh();
        $this->assertTrue($this->admin->must_change_password);
    }

    public function test_user_updating_password_clears_must_change_password_flag(): void
    {
        // Set user to must change password
        $this->admin->update(['must_change_password' => true]);

        Livewire::actingAs($this->admin)
            ->test(MyAccount::class)
            ->set('new_password', 'SecureChosenPassword123!')
            ->set('new_password_confirmation', 'SecureChosenPassword123!')
            ->call('updatePassword')
            ->assertDispatched('toast', function ($event, $params) {
                return ($params['type'] ?? '') === 'success';
            });

        $this->admin->refresh();
        $this->assertFalse($this->admin->must_change_password);
        $this->assertTrue(Hash::check('SecureChosenPassword123!', $this->admin->password));
    }
}
