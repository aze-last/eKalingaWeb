<?php

namespace App\Livewire\Users;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
#[Title('User Management - eKalinga+')]
class Index extends Component
{
    use WithPagination;

    public const MODULE_PERMISSIONS = [
        'masterlist' => [
            'label' => 'Masterlist & Citizen Profiles',
            'desc' => 'View, filter, and inspect registered citizens and civil records.',
        ],
        'budget' => [
            'label' => 'Budget Management',
            'desc' => 'Manage funding allocations, record donations, and review ledgers.',
        ],
        'distribution' => [
            'label' => 'Project Distribution & Scanner',
            'desc' => 'Execute ayuda distribution, QR verification, and claim payouts.',
        ],
        'ggms' => [
            'label' => 'GGMS Transactions Ledger',
            'desc' => 'Access and monitor synced GGMS municipal transaction records.',
        ],
        'reports' => [
            'label' => 'Reports & PDF Audits',
            'desc' => 'Generate and export certified COA reports and audit summaries.',
        ],
    ];

    // Filter & Search State
    public string $search = '';

    public string $roleFilter = 'ALL';

    public string $statusFilter = 'ALL';

    // User Create/Edit Modal State
    public bool $showUserModal = false;

    public bool $isEditing = false;

    public ?int $editingUserId = null;

    public string $name = '';

    public string $username = '';

    public string $email = '';

    public string $role = 'Admin';

    public string $password = '';

    public string $password_confirmation = '';

    public array $selectedPermissions = [];

    public bool $is_active = true;

    // Reset Password Modal State
    public bool $showPasswordModal = false;

    public ?int $targetPasswordUserId = null;

    public string $targetPasswordUserName = '';

    public string $new_password = '';

    public string $new_password_confirmation = '';

    // Soft Delete Confirmation Modal State
    public bool $showDeleteModal = false;

    public ?int $targetDeleteUserId = null;

    public string $targetDeleteUserName = '';

    public function mount(): void
    {
        Gate::authorize('manage-users');
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedRoleFilter(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        Gate::authorize('create', User::class);

        $this->resetValidation();
        $this->isEditing = false;
        $this->editingUserId = null;
        $this->name = '';
        $this->username = '';
        $this->email = '';
        $this->role = UserRole::Admin->value;
        $this->password = '';
        $this->password_confirmation = '';
        // Default permissions for new Admin
        $this->selectedPermissions = ['masterlist', 'distribution'];
        $this->is_active = true;

        $this->showUserModal = true;
    }

    public function openEditModal(int $id): void
    {
        $user = User::findOrFail($id);
        Gate::authorize('update', $user);

        $this->resetValidation();
        $this->isEditing = true;
        $this->editingUserId = $user->id;
        $this->name = $user->name;
        $this->username = $user->username ?? '';
        $this->email = $user->email;
        $this->role = $user->role->value;
        $this->password = '';
        $this->password_confirmation = '';
        $this->selectedPermissions = is_array($user->permissions) ? $user->permissions : [];
        $this->is_active = (bool) $user->is_active;

        $this->showUserModal = true;
    }

    public function closeUserModal(): void
    {
        $this->showUserModal = false;
        $this->resetValidation();
    }

    public function saveUser(): void
    {
        if ($this->isEditing) {
            $user = User::findOrFail($this->editingUserId);
            Gate::authorize('update', $user);

            // Self-role downgrade guard
            if ($user->id === Auth::id() && $this->role !== UserRole::SuperAdmin->value) {
                $this->dispatch('toast', type: 'error', message: 'You cannot change your own SuperAdmin role.');

                return;
            }

            $validated = $this->validate([
                'name' => ['required', 'string', 'max:255'],
                'username' => ['required', 'string', 'alpha_dash', 'max:50', Rule::unique('users', 'username')->ignore($user->id)],
                'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
                'role' => ['required', Rule::enum(UserRole::class)],
                'selectedPermissions' => ['array'],
                'selectedPermissions.*' => ['string', Rule::in(array_keys(self::MODULE_PERMISSIONS))],
                'is_active' => ['boolean'],
            ]);

            // If editing current user, keep is_active true
            if ($user->id === Auth::id()) {
                $validated['is_active'] = true;
            }

            $user->update([
                'name' => $validated['name'],
                'username' => strtolower($validated['username']),
                'email' => strtolower($validated['email']),
                'role' => $validated['role'],
                'permissions' => $validated['role'] === UserRole::SuperAdmin->value ? array_keys(self::MODULE_PERMISSIONS) : $this->selectedPermissions,
                'is_active' => $validated['is_active'],
            ]);

            $this->showUserModal = false;
            $this->dispatch('toast', type: 'success', message: 'User updated successfully.');
        } else {
            Gate::authorize('create', User::class);

            $validated = $this->validate([
                'name' => ['required', 'string', 'max:255'],
                'username' => ['required', 'string', 'alpha_dash', 'max:50', Rule::unique('users', 'username')],
                'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
                'password' => ['required', 'string', 'min:8', 'same:password_confirmation'],
                'role' => ['required', Rule::enum(UserRole::class)],
                'selectedPermissions' => ['array'],
                'selectedPermissions.*' => ['string', Rule::in(array_keys(self::MODULE_PERMISSIONS))],
                'is_active' => ['boolean'],
            ]);

            User::create([
                'name' => $validated['name'],
                'username' => strtolower($validated['username']),
                'email' => strtolower($validated['email']),
                'password' => Hash::make($validated['password']),
                'role' => $validated['role'],
                'permissions' => $validated['role'] === UserRole::SuperAdmin->value ? array_keys(self::MODULE_PERMISSIONS) : $this->selectedPermissions,
                'is_active' => $validated['is_active'],
            ]);

            $this->showUserModal = false;
            $this->dispatch('toast', type: 'success', message: 'User account created successfully.');
        }
    }

    public function openPasswordModal(int $id): void
    {
        $user = User::findOrFail($id);
        Gate::authorize('resetPassword', $user);

        $this->resetValidation();
        $this->targetPasswordUserId = $user->id;
        $this->targetPasswordUserName = $user->name;
        $this->new_password = '';
        $this->new_password_confirmation = '';
        $this->showPasswordModal = true;
    }

    public function closePasswordModal(): void
    {
        $this->showPasswordModal = false;
        $this->resetValidation();
    }

    public function resetPassword(): void
    {
        $user = User::findOrFail($this->targetPasswordUserId);
        Gate::authorize('resetPassword', $user);

        $this->validate([
            'new_password' => ['required', 'string', 'min:8', 'same:new_password_confirmation'],
        ]);

        $user->update([
            'password' => Hash::make($this->new_password),
            'must_change_password' => true,
        ]);

        $this->showPasswordModal = false;
        $this->dispatch('toast', type: 'success', message: "Password for {$user->name} has been reset (user prompted to change on next login).");
    }

    public function toggleStatus(int $id): void
    {
        if ($id === Auth::id()) {
            $this->dispatch('toast', type: 'error', message: 'You cannot deactivate your own account.');

            return;
        }

        $user = User::findOrFail($id);
        Gate::authorize('toggleStatus', $user);

        $user->is_active = ! $user->is_active;
        $user->save();

        $statusStr = $user->is_active ? 'activated' : 'deactivated';
        $this->dispatch('toast', type: 'info', message: "User account {$user->name} has been {$statusStr}.");
    }

    public function openDeleteModal(int $id): void
    {
        if ($id === Auth::id()) {
            $this->dispatch('toast', type: 'error', message: 'You cannot delete your own account.');

            return;
        }

        $user = User::findOrFail($id);
        Gate::authorize('delete', $user);

        $this->targetDeleteUserId = $user->id;
        $this->targetDeleteUserName = $user->name;
        $this->showDeleteModal = true;
    }

    public function closeDeleteModal(): void
    {
        $this->showDeleteModal = false;
    }

    public function deleteUser(): void
    {
        if ($this->targetDeleteUserId === Auth::id()) {
            $this->dispatch('toast', type: 'error', message: 'You cannot delete your own account.');
            $this->showDeleteModal = false;

            return;
        }

        $user = User::findOrFail($this->targetDeleteUserId);
        Gate::authorize('delete', $user);

        $user->delete(); // Soft delete

        $this->showDeleteModal = false;
        $this->dispatch('toast', type: 'success', message: "User {$user->name} has been removed (soft deleted).");
    }

    public function render()
    {
        // Compute metrics
        $metrics = [
            'total' => User::count(),
            'superadmins' => User::where('role', UserRole::SuperAdmin->value)->where('is_active', true)->count(),
            'admins' => User::where('role', UserRole::Admin->value)->where('is_active', true)->count(),
            'inactive' => User::where('is_active', false)->count(),
        ];

        $query = User::query()->latest();

        if ($this->search) {
            $term = trim($this->search);
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('username', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%");
            });
        }

        if ($this->roleFilter !== 'ALL') {
            $query->where('role', $this->roleFilter);
        }

        if ($this->statusFilter === 'ACTIVE') {
            $query->where('is_active', true);
        } elseif ($this->statusFilter === 'INACTIVE') {
            $query->where('is_active', false);
        }

        $users = $query->paginate(10);

        return view('livewire.users.index', [
            'users' => $users,
            'metrics' => $metrics,
            'availableModules' => self::MODULE_PERMISSIONS,
        ]);
    }
}
