<?php

namespace App\Livewire\Profile;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.app')]
#[Title('My Account - eKalinga+')]
class MyAccount extends Component
{
    use WithFileUploads;

    // Profile Details
    public string $name = '';

    public string $username = '';

    public string $email = '';

    public ?string $existingAvatarUrl = null;

    public $avatar = null;

    // Password Form
    public string $current_password = '';

    public string $new_password = '';

    public string $new_password_confirmation = '';

    public bool $must_change_password = false;

    public function mount(): void
    {
        $user = Auth::user();
        if (! $user) {
            abort(401);
        }

        $this->name = $user->name;
        $this->username = $user->username ?? '';
        $this->email = $user->email;
        $this->existingAvatarUrl = $user->avatar_url;
        $this->must_change_password = (bool) $user->must_change_password;
    }

    public function updateProfile(): void
    {
        /** @var User $user */
        $user = Auth::user();

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'alpha_dash', 'max:50', Rule::unique('users', 'username')->ignore($user->id)],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $avatarUrl = $user->avatar_url;

        if ($this->avatar) {
            // Delete old avatar if custom uploaded
            if ($user->avatar_url && str_starts_with($user->avatar_url, '/storage/avatars/')) {
                $oldPath = str_replace('/storage/', '', $user->avatar_url);
                Storage::disk('public')->delete($oldPath);
            }

            $path = $this->avatar->store('avatars', 'public');
            $avatarUrl = '/storage/'.$path;
            $this->existingAvatarUrl = $avatarUrl;
            $this->avatar = null;
        }

        $user->update([
            'name' => $validated['name'],
            'username' => strtolower($validated['username']),
            'email' => strtolower($validated['email']),
            'avatar_url' => $avatarUrl,
        ]);

        $this->dispatch('toast', type: 'success', message: 'Profile details saved successfully.');
    }

    public function removeAvatar(): void
    {
        /** @var User $user */
        $user = Auth::user();

        if ($user->avatar_url && str_starts_with($user->avatar_url, '/storage/avatars/')) {
            $oldPath = str_replace('/storage/', '', $user->avatar_url);
            Storage::disk('public')->delete($oldPath);
        }

        $user->update(['avatar_url' => null]);
        $this->existingAvatarUrl = null;
        $this->avatar = null;

        $this->dispatch('toast', type: 'info', message: 'Profile picture removed.');
    }

    public function updatePassword(): void
    {
        /** @var User $user */
        $user = Auth::user();

        $rules = [
            'new_password' => ['required', 'string', 'min:8', 'same:new_password_confirmation'],
        ];

        // Require current password only if not forced reset
        if (! $this->must_change_password) {
            $rules['current_password'] = ['required', 'current_password'];
        }

        $this->validate($rules);

        $user->update([
            'password' => Hash::make($this->new_password),
            'must_change_password' => false,
        ]);

        $this->current_password = '';
        $this->new_password = '';
        $this->new_password_confirmation = '';
        $this->must_change_password = false;

        $this->dispatch('toast', type: 'success', message: 'Your password has been changed securely.');
    }

    public function render()
    {
        /** @var User $user */
        $user = Auth::user();

        return view('livewire.profile.my-account', [
            'user' => $user,
        ]);
    }
}
