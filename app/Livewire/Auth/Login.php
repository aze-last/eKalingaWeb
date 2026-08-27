<?php

namespace App\Livewire\Auth;

use App\Models\ActivityLog;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
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

    public string $municipalAddress = '';

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
        $this->municipalAddress = Setting::get('municipal_address', 'Purok 1, Poblacion, Sulop, Davao del Sur');
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

        $fieldType = filter_var($this->login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        if (Auth::attempt([$fieldType => $this->login, 'password' => $this->password], $this->remember)) {
            session()->regenerate();

            /** @var User $user */
            $user = Auth::user();

            if (! $user->is_active) {
                Auth::logout();
                $this->password = '';
                $this->addError('login', 'Your account has been deactivated. Please contact the administrator.');

                return;
            }

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

    public function quickLogin(string $role): void
    {
        if (! $this->isLocalEnvironment) {
            return;
        }

        $email = $role === 'SuperAdmin' ? 'superadmin@sulop.gov.ph' : 'admin@sulop.gov.ph';
        $user = User::where('email', $email)->first();

        if ($user) {
            Auth::login($user, true);
            session()->regenerate();
            $this->redirect(route('dashboard'), navigate: true);
        }
    }

    public function render()
    {
        return view('livewire.auth.login');
    }
}
