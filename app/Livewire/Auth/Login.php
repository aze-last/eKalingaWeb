<?php

namespace App\Livewire\Auth;

use App\Models\ActivityLog;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Request;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.guest')]
#[Title('Admin Login - eKalinga+')]
class Login extends Component
{
    public string $login = '';

    public string $password = '';

    public bool $remember = false;

    public string $municipalLogo = '';

    public string $municipalityName = '';

    public string $provinceName = '';

    public string $countryName = '';

    public string $municipalAddress = '';

    public string $tagline = '';

    public string $systemName = '';

    public string $systemSubtitle = '';

    public ?string $loginBgUrl = null;

    public string $systemSerial = '';

    public bool $isLocalEnvironment = false;

    public function mount(): void
    {
        if (Auth::check()) {
            $this->redirect(route('dashboard'), navigate: true);
        }

        $this->municipalLogo = Setting::get('municipal_seal_url', '/images/Site_logo.png');
        $this->municipalityName = Setting::get('municipality_name', 'Municipality of Sulop');
        $this->provinceName = Setting::get('province_name', 'Province of Davao del Sur');
        $this->countryName = Setting::get('country_name', 'Republic of the Philippines');
        $this->municipalAddress = Setting::get('municipal_address', 'Sulop Digos City Davao Del Sur');
        $this->tagline = Setting::get('tagline', 'Better Service, Better Care');
        $this->systemName = Setting::get('system_name', 'eKalinga+');
        $this->systemSubtitle = Setting::get('system_subtitle', 'Ayuda Management System');
        $this->loginBgUrl = Setting::get('login_bg_url');
        $this->systemSerial = Setting::get('system_serial', 'AMS-SULOP-2026-X1');
        $this->isLocalEnvironment = app()->environment('local', 'testing');
    }

    public function authenticate(): void
    {
        $this->validate([
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ], [
            'login.required' => 'Enter your username or email.',
            'password.required' => 'Enter your password.',
        ]);

        $loginInput = trim($this->login);

        // Case-insensitive lookup by username or email
        $user = User::where(function ($q) use ($loginInput) {
            $q->whereRaw('LOWER(username) = ?', [strtolower($loginInput)])
                ->orWhereRaw('LOWER(email) = ?', [strtolower($loginInput)]);
        })->first();

        if ($user && Hash::check($this->password, $user->password)) {
            if (! $user->is_active) {
                $this->password = '';
                $this->addError('login', 'Your account has been deactivated. Please contact the administrator.');

                return;
            }

            Auth::login($user, $this->remember);
            session()->regenerate();

            $user->update(['last_login_at' => now()]);

            ActivityLog::create([
                'user_id' => $user->id,
                'action' => 'Login',
                'module' => 'Auth',
                'description' => "User {$user->name} ({$user->role->value}) successfully logged in.",
                'ip_address' => Request::ip(),
            ]);

            $this->redirect(route('dashboard'), navigate: true);

            return;
        }

        // Never repopulate password on failed authentication
        $this->password = '';
        $this->addError('login', 'These credentials do not match our records or the account is inactive.');
    }

    public function render()
    {
        return view('livewire.auth.login');
    }
}
