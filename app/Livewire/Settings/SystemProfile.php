<?php

namespace App\Livewire\Settings;

use App\Models\Setting;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.app')]
#[Title('System Profile - eKalinga+')]
class SystemProfile extends Component
{
    use WithFileUploads;

    // Brand Identity Text Fields
    public string $system_name = 'eKalinga+';

    public string $system_subtitle = 'Ayuda Management System';

    public string $municipality_name = 'Municipality of Sulop';

    public string $province_name = 'Province of Davao del Sur';

    public string $country_name = 'Republic of the Philippines';

    public string $municipal_address = 'Sulop Digos City Davao Del Sur';

    public string $tagline = 'Better Service, Better Care';

    // Existing Assets
    public ?string $existingSealUrl = null;

    public ?string $existingFaviconUrl = null;

    public ?string $existingLoginBgUrl = null;

    // Temporary Uploads
    public $sealFile = null;

    public $faviconFile = null;

    public $loginBgFile = null;

    public function mount(): void
    {
        Gate::authorize('manage-users');

        $this->system_name = (string) Setting::get('system_name', 'eKalinga+');
        $this->system_subtitle = (string) Setting::get('system_subtitle', 'Ayuda Management System');
        $this->municipality_name = (string) Setting::get('municipality_name', 'Municipality of Sulop');
        $this->province_name = (string) Setting::get('province_name', 'Province of Davao del Sur');
        $this->country_name = (string) Setting::get('country_name', 'Republic of the Philippines');
        $this->municipal_address = (string) Setting::get('municipal_address', 'Sulop Digos City Davao Del Sur');
        $this->tagline = (string) Setting::get('tagline', 'Better Service, Better Care');

        $this->existingSealUrl = Setting::get('municipal_seal_url', '/images/Site_logo.png');
        $this->existingFaviconUrl = Setting::get('favicon_url', '/images/Site_logo.png');
        $this->existingLoginBgUrl = Setting::get('login_bg_url');
    }

    public function saveSettings(): void
    {
        Gate::authorize('manage-users');

        $this->validate([
            'system_name' => ['required', 'string', 'max:100'],
            'system_subtitle' => ['nullable', 'string', 'max:150'],
            'municipality_name' => ['required', 'string', 'max:150'],
            'province_name' => ['nullable', 'string', 'max:150'],
            'country_name' => ['nullable', 'string', 'max:150'],
            'municipal_address' => ['nullable', 'string', 'max:255'],
            'tagline' => ['nullable', 'string', 'max:200'],
            'sealFile' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:2048'],
            'faviconFile' => ['nullable', 'image', 'mimes:ico,png,jpg,jpeg,webp,svg', 'max:1024'],
            'loginBgFile' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        // 1. Save Text Settings
        Setting::set('system_name', trim($this->system_name), 'string', 'branding');
        Setting::set('system_subtitle', trim($this->system_subtitle), 'string', 'branding');
        Setting::set('municipality_name', trim($this->municipality_name), 'string', 'branding');
        Setting::set('province_name', trim($this->province_name), 'string', 'branding');
        Setting::set('country_name', trim($this->country_name), 'string', 'branding');
        Setting::set('municipal_address', trim($this->municipal_address), 'string', 'branding');
        Setting::set('tagline', trim($this->tagline), 'string', 'branding');

        // 2. Handle Official Seal / Logo Upload
        if ($this->sealFile) {
            if ($this->existingSealUrl && str_starts_with($this->existingSealUrl, '/storage/branding/seal_')) {
                $oldPath = str_replace('/storage/', '', $this->existingSealUrl);
                Storage::disk('public')->delete($oldPath);
            }

            $sealPath = $this->sealFile->store('branding', 'public');
            $sealUrl = '/storage/'.$sealPath;
            Setting::set('municipal_seal_url', $sealUrl, 'string', 'branding');
            $this->existingSealUrl = $sealUrl;
            $this->sealFile = null;
        }

        // 3. Handle Browser Tab Favicon Upload
        if ($this->faviconFile) {
            if ($this->existingFaviconUrl && str_starts_with($this->existingFaviconUrl, '/storage/branding/fav_')) {
                $oldPath = str_replace('/storage/', '', $this->existingFaviconUrl);
                Storage::disk('public')->delete($oldPath);
            }

            $favPath = $this->faviconFile->store('branding', 'public');
            $favUrl = '/storage/'.$favPath;
            Setting::set('favicon_url', $favUrl, 'string', 'branding');
            $this->existingFaviconUrl = $favUrl;
            $this->faviconFile = null;
        }

        // 4. Handle Login Left Panel Wallpaper Upload
        if ($this->loginBgFile) {
            if ($this->existingLoginBgUrl && str_starts_with($this->existingLoginBgUrl, '/storage/branding/bg_')) {
                $oldPath = str_replace('/storage/', '', $this->existingLoginBgUrl);
                Storage::disk('public')->delete($oldPath);
            }

            $bgPath = $this->loginBgFile->store('branding', 'public');
            $bgUrl = '/storage/'.$bgPath;
            Setting::set('login_bg_url', $bgUrl, 'string', 'branding');
            $this->existingLoginBgUrl = $bgUrl;
            $this->loginBgFile = null;
        }

        $this->dispatch('toast', type: 'success', message: 'System Profile & Branding updated successfully.');
    }

    public function removeSeal(): void
    {
        Gate::authorize('manage-users');

        if ($this->existingSealUrl && str_starts_with($this->existingSealUrl, '/storage/branding/')) {
            $oldPath = str_replace('/storage/', '', $this->existingSealUrl);
            Storage::disk('public')->delete($oldPath);
        }

        $defaultSeal = '/images/Site_logo.png';
        Setting::set('municipal_seal_url', $defaultSeal, 'string', 'branding');
        $this->existingSealUrl = $defaultSeal;
        $this->sealFile = null;

        $this->dispatch('toast', type: 'info', message: 'Official seal restored to system default.');
    }

    public function removeFavicon(): void
    {
        Gate::authorize('manage-users');

        if ($this->existingFaviconUrl && str_starts_with($this->existingFaviconUrl, '/storage/branding/')) {
            $oldPath = str_replace('/storage/', '', $this->existingFaviconUrl);
            Storage::disk('public')->delete($oldPath);
        }

        $defaultFavicon = '/images/Site_logo.png';
        Setting::set('favicon_url', $defaultFavicon, 'string', 'branding');
        $this->existingFaviconUrl = $defaultFavicon;
        $this->faviconFile = null;

        $this->dispatch('toast', type: 'info', message: 'Favicon restored to default seal.');
    }

    public function removeLoginBg(): void
    {
        Gate::authorize('manage-users');

        if ($this->existingLoginBgUrl && str_starts_with($this->existingLoginBgUrl, '/storage/branding/')) {
            $oldPath = str_replace('/storage/', '', $this->existingLoginBgUrl);
            Storage::disk('public')->delete($oldPath);
        }

        Setting::set('login_bg_url', '', 'string', 'branding');
        $this->existingLoginBgUrl = null;
        $this->loginBgFile = null;

        $this->dispatch('toast', type: 'info', message: 'Custom login background removed.');
    }

    public function render()
    {
        return view('livewire.settings.system-profile');
    }
}
