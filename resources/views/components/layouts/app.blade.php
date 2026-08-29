<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? (App\Models\Setting::get('system_name', 'eKalinga+')) }} - {{ App\Models\Setting::get('municipality_name', 'Municipality of Sulop') }}</title>
    
    <!-- Favicon Icon -->
    <link rel="icon" type="image/x-icon" href="{{ asset(App\Models\Setting::get('favicon_url', '/images/Site_logo.png')) }}">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
        <script>
            tailwind.config = {
                theme: {
                    extend: {
                        colors: {
                            brand: '#15803D',
                            accent: '#F59E0B',
                            sidebar: '#F8FAFC',
                            surface: '#FFFFFF',
                            'page-bg': '#F1F5F9',
                            'neutral-strong': '#0F172A',
                            success: '#15803D',
                            error: '#BE123C',
                            warning: '#854D0E',
                        }
                    }
                }
            }
        </script>
    @endif
    @livewireStyles
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        .font-mono {
            font-family: 'JetBrains Mono', monospace;
        }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-[#F1F5F9] text-[#0F172A] antialiased min-h-screen selection:bg-brand selection:text-white" x-data="appState()">

    <!-- Toast Notification Container -->
    <div class="fixed top-5 right-5 z-50 flex flex-col gap-2 max-w-sm w-full pointer-events-none">
        <template x-for="(toast, index) in toasts" :key="toast.id">
            <div 
                x-show="toast.visible"
                x-transition:enter="transition ease-out duration-300 transform"
                x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                x-transition:leave="transition ease-in duration-200 transform"
                x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                x-transition:leave-end="opacity-0 -translate-y-2 scale-95"
                class="pointer-events-auto p-4 rounded-xl shadow-md border flex items-center gap-3 bg-white"
                :class="{
                    'border-emerald-500 text-emerald-950': toast.type === 'success',
                    'border-rose-500 text-rose-950': toast.type === 'error',
                    'border-amber-500 text-amber-950': toast.type === 'warning',
                    'border-slate-300 text-slate-900': toast.type === 'info'
                }"
            >
                <div class="shrink-0">
                    <template x-if="toast.type === 'success'">
                        <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    </template>
                    <template x-if="toast.type === 'error'">
                        <svg class="w-5 h-5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </template>
                    <template x-if="toast.type === 'warning'">
                        <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </template>
                    <template x-if="toast.type === 'info'">
                        <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </template>
                </div>
                <div class="flex-1 text-sm font-medium" x-text="toast.message"></div>
                <button @click="removeToast(toast.id)" class="text-slate-400 hover:text-slate-700 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </template>
    </div>

    <!-- App Container Grid -->
    <div class="flex h-screen overflow-hidden bg-[#F1F5F9]">
        <!-- Sidebar Navigation (Fixed w-80, Light bg-sidebar) -->
        <aside class="w-80 bg-[#F8FAFC] border-r border-slate-200 flex flex-col justify-between shrink-0 select-none shadow-xs">
            <div>
                <!-- Brand Header -->
                <div class="h-20 px-6 flex items-center gap-3.5 border-b border-slate-200 bg-white">
                    <img src="{{ asset(App\Models\Setting::get('municipal_seal_url', '/images/Site_logo.png')) }}" alt="Seal" class="w-10 h-10 object-contain drop-shadow-xs">
                    <div>
                        <h1 class="text-xs font-black tracking-wider text-neutral-strong uppercase leading-tight">{{ App\Models\Setting::get('municipality_name', 'MUNICIPALITY OF SULOP') }}</h1>
                        <p class="text-[11px] font-bold text-brand tracking-wide mt-0.5">eKalinga+ Ayuda System</p>
                    </div>
                </div>

                <!-- Navigation Section -->
                <div class="p-4 space-y-6">
                    <div>
                        <p class="px-3 text-[11px] font-bold uppercase tracking-wider text-brand mb-2">Municipal Operations</p>
                        <nav class="space-y-1">
                            <!-- Dashboard -->
                            <a 
                                href="{{ route('dashboard') }}" 
                                wire:navigate 
                                class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors cursor-pointer {{ request()->routeIs('dashboard') ? 'bg-brand/10 text-brand font-bold' : 'text-slate-700 hover:bg-slate-100 hover:text-neutral-strong' }}"
                            >
                                <svg class="w-5 h-5 shrink-0 {{ request()->routeIs('dashboard') ? 'text-brand' : 'text-slate-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                                <span>Dashboard</span>
                            </a>

                            <!-- Masterlist -->
                            <a 
                                href="{{ route('masterlist') }}" 
                                wire:navigate 
                                class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors cursor-pointer {{ request()->routeIs('masterlist*') ? 'bg-brand/10 text-brand font-bold' : 'text-slate-700 hover:bg-slate-100 hover:text-neutral-strong' }}"
                            >
                                <svg class="w-5 h-5 shrink-0 {{ request()->routeIs('masterlist*') ? 'text-brand' : 'text-slate-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                <span>Masterlist</span>
                            </a>

                            <!-- Budget Management -->
                            <a 
                                href="{{ route('budget') }}" 
                                wire:navigate 
                                class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors cursor-pointer {{ request()->routeIs('budget*') ? 'bg-brand/10 text-brand font-bold' : 'text-slate-700 hover:bg-slate-100 hover:text-neutral-strong' }}"
                            >
                                <svg class="w-5 h-5 shrink-0 {{ request()->routeIs('budget*') ? 'text-brand' : 'text-slate-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span>Budget Management</span>
                            </a>

                            <!-- Project Distribution -->
                            <a 
                                href="{{ route('distribution') }}" 
                                wire:navigate 
                                class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors cursor-pointer {{ request()->routeIs('distribution*') ? 'bg-brand/10 text-brand font-bold' : 'text-slate-700 hover:bg-slate-100 hover:text-neutral-strong' }}"
                            >
                                <svg class="w-5 h-5 shrink-0 {{ request()->routeIs('distribution*') ? 'text-brand' : 'text-slate-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                <span>Project Distribution</span>
                            </a>

                            <!-- GGMS Transactions -->
                            <a 
                                href="{{ route('ggms') }}" 
                                wire:navigate 
                                class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors cursor-pointer {{ request()->routeIs('ggms*') ? 'bg-brand/10 text-brand font-bold' : 'text-slate-700 hover:bg-slate-100 hover:text-neutral-strong' }}"
                            >
                                <svg class="w-5 h-5 shrink-0 {{ request()->routeIs('ggms*') ? 'text-brand' : 'text-slate-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                                <span>GGMS Transactions</span>
                            </a>

                            <!-- Reports & Audits -->
                            <a 
                                href="{{ route('reports') }}" 
                                wire:navigate 
                                class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors cursor-pointer {{ request()->routeIs('reports*') ? 'bg-brand/10 text-brand font-bold' : 'text-slate-700 hover:bg-slate-100 hover:text-neutral-strong' }}"
                            >
                                <svg class="w-5 h-5 shrink-0 {{ request()->routeIs('reports*') ? 'text-brand' : 'text-slate-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                <span>Reports & Audits</span>
                            </a>
                        </nav>
                    </div>

                    @if(Auth::user()?->isSuperAdmin())
                        <div>
                            <p class="px-3 text-[11px] font-bold uppercase tracking-wider text-brand mb-2">System Administration</p>
                            <nav class="space-y-1">
                                <!-- User Management -->
                                <a 
                                    href="{{ route('users.index') }}" 
                                    wire:navigate 
                                    class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors cursor-pointer {{ request()->routeIs('users*') ? 'bg-brand/10 text-brand font-bold' : 'text-slate-700 hover:bg-slate-100 hover:text-neutral-strong' }}"
                                >
                                    <svg class="w-5 h-5 shrink-0 {{ request()->routeIs('users*') ? 'text-brand' : 'text-slate-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                                    <span>User Management</span>
                                </a>

                                <!-- System Profile & Branding -->
                                <a 
                                    href="{{ route('settings.profile') }}" 
                                    wire:navigate 
                                    class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors cursor-pointer {{ request()->routeIs('settings.profile') ? 'bg-brand/10 text-brand font-bold' : 'text-slate-700 hover:bg-slate-100 hover:text-neutral-strong' }}"
                                >
                                    <svg class="w-5 h-5 shrink-0 {{ request()->routeIs('settings.profile') ? 'text-brand' : 'text-slate-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    <span>System Profile</span>
                                </a>
                            </nav>
                        </div>
                    @endif

                    <!-- Quick Primary Actions (Amber Fills with Dark Text) -->
                    <div class="pt-2 border-t border-slate-200">
                        <a 
                            href="{{ route('distribution') }}" 
                            wire:navigate 
                            class="w-full bg-accent hover:bg-amber-400 text-neutral-strong font-bold text-xs py-2.5 px-4 rounded-lg tracking-wider uppercase transition-colors shadow-xs flex items-center justify-center gap-2 cursor-pointer"
                        >
                            <svg class="w-4 h-4 text-neutral-strong" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            <span>LAUNCH SCANNER</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- User Footer & Logout -->
            <div class="p-4 border-t border-slate-200 bg-white">
                <div class="flex items-center justify-between gap-2">
                    <a 
                        href="{{ route('profile.index') }}" 
                        wire:navigate 
                        title="View My Account"
                        class="flex items-center gap-3 min-w-0 flex-1 p-1.5 -m-1.5 rounded-xl hover:bg-slate-50 transition-colors cursor-pointer group"
                    >
                        @if (Auth::user()?->avatar_url)
                            <img src="{{ asset(Auth::user()->avatar_url) }}" alt="Avatar" class="w-9 h-9 rounded-lg object-cover border border-slate-200 shrink-0 shadow-2xs group-hover:border-brand transition-colors">
                        @else
                            <div class="w-9 h-9 rounded-lg {{ Auth::user()?->isSuperAdmin() ? 'bg-brand' : 'bg-slate-700' }} flex items-center justify-center font-bold text-white text-xs shrink-0 shadow-xs group-hover:ring-2 group-hover:ring-brand/20 transition-all">
                                {{ substr(Auth::user()->name ?? 'AD', 0, 2) }}
                            </div>
                        @endif
                        <div class="truncate flex-1">
                            <p class="text-xs font-bold text-neutral-strong truncate group-hover:text-brand transition-colors">{{ Auth::user()->name ?? 'Administrator' }}</p>
                            <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] font-semibold bg-emerald-50 text-brand border border-emerald-200">
                                {{ Auth::user()->role->value ?? 'Admin' }}
                            </span>
                        </div>
                    </a>
                    <form method="POST" action="{{ route('logout') }}" class="shrink-0">
                        @csrf
                        <button type="submit" title="Sign Out" class="p-2 text-slate-400 hover:text-error hover:bg-rose-50 rounded-lg transition-colors cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <!-- Main Content Area -->
        <main class="flex-1 flex flex-col min-w-0 overflow-hidden bg-page-bg">
            <!-- Header Bar -->
            <header class="h-16 bg-white border-b border-slate-200 px-[30px] flex items-center justify-between shrink-0">
                <div class="flex items-center gap-3">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Municipality of Sulop</span>
                    <span class="text-slate-300">/</span>
                    <span class="text-xs font-extrabold text-brand">{{ $title ?? 'Ayuda Management' }}</span>
                </div>

                <!-- Status Pills & Quick Tools -->
                <div class="flex items-center gap-4 text-xs">
                    <!-- Live GGMS Hub Status Pill -->
                    <div class="flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-50 border border-emerald-200 text-brand font-bold text-[11px]">
                        <span class="w-2 h-2 rounded-full bg-brand animate-pulse"></span>
                        <span>GGMS Hub Online</span>
                    </div>

                    <!-- Audio Feedback Indicator -->
                    <button 
                        @click="toggleAudio()" 
                        class="flex items-center gap-1.5 px-3 py-1 rounded-lg border border-slate-200 bg-slate-50 hover:bg-slate-100 text-slate-700 transition-colors cursor-pointer text-xs"
                        :title="audioEnabled ? 'Sound feedback active' : 'Sound feedback muted'"
                    >
                        <svg x-show="audioEnabled" class="w-3.5 h-3.5 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z"/></svg>
                        <svg x-show="!audioEnabled" class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2"/></svg>
                        <span x-text="audioEnabled ? 'Audio ON' : 'Audio OFF'"></span>
                    </button>

                    <!-- Realtime Clock -->
                    <div class="font-mono text-slate-500 text-xs hidden sm:block font-medium" x-text="currentTime"></div>
                </div>
            </header>

            <!-- Mandatory Password Change Top Banner (when outside of My Account) -->
            @if (Auth::user()?->must_change_password && ! request()->routeIs('profile.index'))
                <div class="bg-amber-500 text-neutral-strong px-[30px] py-2.5 flex items-center justify-between gap-4 font-bold text-xs shadow-xs shrink-0">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-neutral-strong shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <span>Security Alert: Your password was reset by an administrator. Please set a new password now.</span>
                    </div>
                    <a 
                        href="{{ route('profile.index') }}" 
                        wire:navigate 
                        class="bg-neutral-strong hover:bg-slate-800 text-white font-black text-[11px] py-1 px-3 rounded-lg uppercase tracking-wider transition-colors shrink-0 shadow-2xs cursor-pointer"
                    >
                        Change Password
                    </a>
                </div>
            @endif

            <!-- Scrollable Content Body with Standardized p-[30px] -->
            <div class="flex-1 overflow-y-auto p-[30px] bg-page-bg">
                {{ $slot }}
            </div>
        </main>
    </div>

    <!-- Mandatory Password Reset Security Dialog Modal -->
    @if (Auth::user()?->must_change_password && ! request()->routeIs('profile.index'))
        <div 
            x-data="{ showNotice: true }" 
            x-show="showNotice" 
            x-cloak
            class="fixed inset-0 z-50 overflow-y-auto"
            aria-labelledby="password-reset-notice-title" 
            role="dialog" 
            aria-modal="true"
        >
            <div 
                x-show="showNotice"
                x-transition:enter="ease-out duration-200"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                class="fixed inset-0 bg-[#0F172A]/80 backdrop-blur-md transition-opacity"
            ></div>

            <div class="flex min-h-screen items-center justify-center p-4 text-center sm:p-0">
                <div 
                    x-show="showNotice"
                    x-transition:enter="ease-out duration-200"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-md border border-amber-300 p-6"
                >
                    <div class="flex items-center gap-3.5 pb-3 border-b border-slate-100">
                        <div class="w-11 h-11 rounded-xl bg-amber-100 border border-amber-300 flex items-center justify-center text-amber-900 shrink-0">
                            <svg class="w-6 h-6 text-amber-900 stroke-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        </div>
                        <div>
                            <h3 class="text-base font-black text-neutral-strong tracking-tight" id="password-reset-notice-title">
                                Temporary Password Notice
                            </h3>
                            <p class="text-xs text-amber-900 font-bold">Password Change Required</p>
                        </div>
                    </div>

                    <div class="mt-4 space-y-3">
                        <p class="text-xs text-slate-600 leading-relaxed">
                            A <span class="font-bold text-neutral-strong">Super Administrator</span> recently reset the password for your account. You are currently operating with a temporary password.
                        </p>
                        <div class="p-3 rounded-xl bg-amber-50 border border-amber-200 text-xs text-amber-900">
                            <p class="font-bold">Security Safeguard:</p>
                            <p class="text-[11px] text-amber-800 mt-0.5">Please update to a secure, private password in your account settings before continuing municipal duties.</p>
                        </div>
                    </div>

                    <div class="mt-6 flex items-center justify-end gap-2">
                        <button 
                            type="button" 
                            @click="showNotice = false" 
                            class="px-4 py-2 rounded-xl text-xs font-bold text-slate-500 hover:bg-slate-100 transition-colors cursor-pointer"
                        >
                            Remind Me Later
                        </button>
                        <a 
                            href="{{ route('profile.index') }}" 
                            wire:navigate 
                            class="bg-accent hover:bg-amber-400 text-neutral-strong font-bold text-xs py-2 px-5 rounded-xl tracking-wider uppercase transition-colors shadow-xs flex items-center gap-2 cursor-pointer"
                        >
                            <span>Change Password Now</span>
                            <svg class="w-4 h-4 text-neutral-strong" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @livewireScripts
    
    <!-- Audio & Global State Script -->
    <script>
        function appState() {
            return {
                audioEnabled: true,
                currentTime: '',
                toasts: [],
                init() {
                    this.updateTime();
                    setInterval(() => this.updateTime(), 1000);

                    // Listen to Livewire toast events
                    window.addEventListener('toast', (e) => {
                        this.addToast(e.detail.type || 'info', e.detail.message || '');
                    });

                    // Audio cue triggers
                    window.addEventListener('play-audio-success', () => this.playSuccessTone());
                    window.addEventListener('play-audio-error', () => this.playErrorTone());
                },
                updateTime() {
                    const now = new Date();
                    this.currentTime = now.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) + ' ' + now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
                },
                toggleAudio() {
                    this.audioEnabled = !this.audioEnabled;
                },
                addToast(type, message) {
                    const id = Date.now() + Math.random();
                    this.toasts.push({ id, type, message, visible: true });
                    setTimeout(() => this.removeToast(id), 4000);
                },
                removeToast(id) {
                    const index = this.toasts.findIndex(t => t.id === id);
                    if (index !== -1) {
                        this.toasts[index].visible = false;
                        setTimeout(() => {
                            this.toasts = this.toasts.filter(t => t.id !== id);
                        }, 300);
                    }
                },
                playSuccessTone() {
                    if (!this.audioEnabled) return;
                    try {
                        const ctx = new (window.AudioContext || window.webkitAudioContext)();
                        const osc1 = ctx.createOscillator();
                        const osc2 = ctx.createOscillator();
                        const gain = ctx.createGain();

                        osc1.type = 'sine';
                        osc2.type = 'sine';
                        osc1.frequency.setValueAtTime(880, ctx.currentTime);
                        osc2.frequency.setValueAtTime(1320, ctx.currentTime);

                        gain.gain.setValueAtTime(0.2, ctx.currentTime);
                        gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.15);

                        osc1.connect(gain);
                        osc2.connect(gain);
                        gain.connect(ctx.destination);

                        osc1.start();
                        osc2.start();
                        osc1.stop(ctx.currentTime + 0.15);
                        osc2.stop(ctx.currentTime + 0.15);
                    } catch (e) {
                        console.error('AudioContext error:', e);
                    }
                },
                playErrorTone() {
                    if (!this.audioEnabled) return;
                    try {
                        const ctx = new (window.AudioContext || window.webkitAudioContext)();
                        const osc = ctx.createOscillator();
                        const gain = ctx.createGain();

                        osc.type = 'sawtooth';
                        osc.frequency.setValueAtTime(220, ctx.currentTime);
                        osc.frequency.setValueAtTime(180, ctx.currentTime + 0.1);

                        gain.gain.setValueAtTime(0.25, ctx.currentTime);
                        gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.3);

                        osc.connect(gain);
                        gain.connect(ctx.destination);

                        osc.start();
                        osc.stop(ctx.currentTime + 0.3);
                    } catch (e) {
                        console.error('AudioContext error:', e);
                    }
                }
            };
        }
    </script>
</body>
</html>
