<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Livewire\Users\Index;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superAdmin = User::factory()->create([
            'name' => 'Super Admin User',
            'username' => 'superadmin',
            'email' => 'superadmin@sulop.gov.ph',
            'role' => UserRole::SuperAdmin,
            'is_active' => true,
        ]);

        $this->admin = User::factory()->create([
            'name' => 'Regular Admin User',
            'username' => 'regularadmin',
            'email' => 'admin@sulop.gov.ph',
            'role' => UserRole::Admin,
            'permissions' => ['masterlist', 'distribution'],
            'is_active' => true,
        ]);
    }

    public function test_superadmin_can_access_user_management_screen(): void
    {
        $response = $this->actingAs($this->superAdmin)->get(route('users.index'));

        $response->assertOk();
        $response->assertSeeLivewire(Index::class);
        $response->assertSee('System User Management');
        $response->assertSee('Super Admin User');
        $response->assertSee('Regular Admin User');
    }

    public function test_regular_admin_is_forbidden_from_user_management_screen(): void
    {
        $response = $this->actingAs($this->admin)->get(route('users.index'));

        $response->assertForbidden();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get(route('users.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_superadmin_can_create_admin_with_specific_permissions(): void
    {
        Livewire::actingAs($this->superAdmin)
            ->test(Index::class)
            ->call('openCreateModal')
            ->set('name', 'Maria Clara')
            ->set('username', 'mariac')
            ->set('email', 'maria@sulop.gov.ph')
            ->set('role', UserRole::Admin->value)
            ->set('password', 'Password123!')
            ->set('password_confirmation', 'Password123!')
            ->set('selectedPermissions', ['budget', 'reports'])
            ->call('saveUser')
            ->assertDispatched('toast', function ($event, $params) {
                return ($params['type'] ?? '') === 'success';
            });

        $created = User::where('email', 'maria@sulop.gov.ph')->first();
        $this->assertNotNull($created);
        $this->assertEquals('mariac', $created->username);
        $this->assertEquals(UserRole::Admin, $created->role);
        $this->assertTrue($created->canManageBudget());
        $this->assertTrue($created->canViewReports());
        $this->assertFalse($created->canDistribute());
    }

    public function test_superadmin_can_update_user_and_permissions(): void
    {
        Livewire::actingAs($this->superAdmin)
            ->test(Index::class)
            ->call('openEditModal', $this->admin->id)
            ->set('name', 'Updated Admin Name')
            ->set('selectedPermissions', ['masterlist', 'budget', 'distribution', 'ggms', 'reports'])
            ->call('saveUser')
            ->assertDispatched('toast', function ($event, $params) {
                return ($params['type'] ?? '') === 'success';
            });

        $this->admin->refresh();
        $this->assertEquals('Updated Admin Name', $this->admin->name);
        $this->assertTrue($this->admin->canManageBudget());
        $this->assertTrue($this->admin->canViewGgms());
    }

    public function test_superadmin_can_reset_admin_password(): void
    {
        Livewire::actingAs($this->superAdmin)
            ->test(Index::class)
            ->call('openPasswordModal', $this->admin->id)
            ->set('new_password', 'NewSecretPassword123!')
            ->set('new_password_confirmation', 'NewSecretPassword123!')
            ->call('resetPassword')
            ->assertDispatched('toast', function ($event, $params) {
                return ($params['type'] ?? '') === 'success';
            });

        $this->admin->refresh();
        $this->assertTrue(Hash::check('NewSecretPassword123!', $this->admin->password));
    }

    public function test_superadmin_cannot_deactivate_or_delete_own_account(): void
    {
        // Attempt self deactivation
        Livewire::actingAs($this->superAdmin)
            ->test(Index::class)
            ->call('toggleStatus', $this->superAdmin->id)
            ->assertDispatched('toast', function ($event, $params) {
                return ($params['type'] ?? '') === 'error';
            });

        $this->superAdmin->refresh();
        $this->assertTrue($this->superAdmin->is_active);

        // Attempt self soft deletion
        Livewire::actingAs($this->superAdmin)
            ->test(Index::class)
            ->call('openDeleteModal', $this->superAdmin->id)
            ->assertDispatched('toast', function ($event, $params) {
                return ($params['type'] ?? '') === 'error';
            });

        $this->superAdmin->refresh();
        $this->assertNull($this->superAdmin->deleted_at);
    }

    public function test_superadmin_can_soft_delete_admin_user(): void
    {
        Livewire::actingAs($this->superAdmin)
            ->test(Index::class)
            ->call('openDeleteModal', $this->admin->id)
            ->call('deleteUser')
            ->assertDispatched('toast', function ($event, $params) {
                return ($params['type'] ?? '') === 'success';
            });

        $this->assertNull(User::find($this->admin->id));
        $this->assertNotNull(User::withTrashed()->find($this->admin->id));
    }
}
