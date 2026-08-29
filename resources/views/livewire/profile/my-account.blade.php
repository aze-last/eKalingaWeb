<div class="space-y-6 max-w-5xl mx-auto">
    <!-- Header Title & Role Summary -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200 shadow-xs">
        <div class="flex items-center gap-4">
            <div class="relative group">
                @if ($avatar)
                    <img src="{{ $avatar->temporaryUrl() }}" alt="Preview" class="w-16 h-16 rounded-2xl object-cover border-2 border-brand shadow-xs">
                @elseif ($existingAvatarUrl)
                    <img src="{{ asset($existingAvatarUrl) }}" alt="Avatar" class="w-16 h-16 rounded-2xl object-cover border border-slate-200 shadow-xs">
                @else
                    <div class="w-16 h-16 rounded-2xl {{ $user->isSuperAdmin() ? 'bg-brand' : 'bg-slate-700' }} flex items-center justify-center font-black text-white text-xl shadow-xs">
                        {{ strtoupper(substr($user->name, 0, 2)) }}
                    </div>
                @endif
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-2xl font-black text-neutral-strong tracking-tight">{{ $user->name }}</h1>
                    @if ($user->isSuperAdmin())
                        <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider bg-emerald-50 text-brand border border-emerald-200">SuperAdmin</span>
                    @else
                        <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider bg-slate-100 text-slate-700 border border-slate-200">Administrator</span>
                    @endif
                </div>
                <p class="text-xs text-slate-500 font-medium mt-0.5">
                    <span>&#64;{{ $user->username ?? 'user' }}</span>
                    <span class="mx-1.5">&bull;</span>
                    <span>{{ $user->email }}</span>
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold {{ $user->is_active ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200' }}">
                <span class="w-2 h-2 rounded-full {{ $user->is_active ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>
                <span>{{ $user->is_active ? 'Account Active' : 'Account Suspended' }}</span>
            </span>
        </div>
    </div>

    <!-- Mandatory Password Change Security Alert Banner -->
    @if ($must_change_password)
        <div class="p-5 rounded-2xl bg-amber-50 border-2 border-amber-400 text-amber-950 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 shadow-sm animate-pulse">
            <div class="flex items-start gap-3.5">
                <div class="w-10 h-10 rounded-xl bg-amber-200/80 border border-amber-300 flex items-center justify-center text-amber-900 shrink-0 mt-0.5 sm:mt-0">
                    <svg class="w-5 h-5 text-amber-900 stroke-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <div>
                    <h4 class="text-sm font-black uppercase tracking-wider text-amber-950">Password Change Required</h4>
                    <p class="text-xs text-amber-900 font-medium mt-0.5">Your password was recently reset by a system administrator. For your security, you must update your password below before continuing normal operations.</p>
                </div>
            </div>
            <a 
                href="#password-card" 
                class="shrink-0 bg-neutral-strong hover:bg-slate-800 text-white font-bold text-xs py-2 px-4 rounded-xl tracking-wider uppercase transition-colors shadow-xs"
            >
                Change Now
            </a>
        </div>
    @endif

    <!-- Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Left: Profile Details Card (7 Cols) -->
        <div class="lg:col-span-7 bg-white p-6 rounded-2xl border border-slate-200 shadow-xs space-y-6">
            <div class="border-b border-slate-100 pb-4">
                <h3 class="text-base font-black text-neutral-strong tracking-tight">Personal Details</h3>
                <p class="text-xs text-slate-500 font-medium mt-0.5">Update your account name, email address, and username identifier.</p>
            </div>

            <form wire:submit.prevent="updateProfile" class="space-y-4">
                <!-- Avatar Upload Section -->
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200">
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Profile Picture</label>
                    <div class="flex items-center gap-4">
                        <div class="relative shrink-0">
                            @if ($avatar)
                                <img src="{{ $avatar->temporaryUrl() }}" alt="Upload Preview" class="w-14 h-14 rounded-xl object-cover border-2 border-brand">
                            @elseif ($existingAvatarUrl)
                                <img src="{{ asset($existingAvatarUrl) }}" alt="Avatar" class="w-14 h-14 rounded-xl object-cover border border-slate-200">
                            @else
                                <div class="w-14 h-14 rounded-xl bg-slate-200 text-slate-600 flex items-center justify-center font-bold text-sm">
                                    {{ strtoupper(substr($user->name, 0, 2)) }}
                                </div>
                            @endif
                            <div wire:loading wire:target="avatar" class="absolute inset-0 bg-black/40 rounded-xl flex items-center justify-center">
                                <svg class="animate-spin h-5 w-5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            </div>
                        </div>

                        <div class="flex-1 space-y-1.5">
                            <input 
                                type="file" 
                                wire:model="avatar" 
                                id="avatar-input" 
                                accept="image/png,image/jpeg,image/webp"
                                class="hidden"
                            >
                            <div class="flex items-center gap-2">
                                <label 
                                    for="avatar-input" 
                                    class="bg-white border border-slate-200 hover:bg-slate-100 text-neutral-strong font-bold text-xs py-1.5 px-3 rounded-lg transition-colors cursor-pointer inline-flex items-center gap-1.5 shadow-2xs"
                                >
                                    <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    <span>Upload New</span>
                                </label>

                                @if ($existingAvatarUrl || $avatar)
                                    <button 
                                        type="button" 
                                        wire:click="removeAvatar"
                                        class="text-error hover:bg-rose-50 text-xs font-bold py-1.5 px-3 rounded-lg transition-colors cursor-pointer"
                                    >
                                        Remove
                                    </button>
                                @endif
                            </div>
                            <p class="text-[11px] text-slate-400">PNG, JPG, or WEBP up to 2MB.</p>
                        </div>
                    </div>
                    @error('avatar') <span class="text-[11px] font-bold text-error mt-2 block">{{ $message }}</span> @enderror
                </div>

                <!-- Full Name -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Full Name <span class="text-error">*</span></label>
                    <input 
                        type="text" 
                        wire:model="name"
                        class="w-full bg-white border border-slate-200 rounded-xl px-3.5 py-2 text-xs text-neutral-strong focus:outline-none focus:ring-2 focus:ring-brand/20 focus:border-brand transition-all"
                    >
                    @error('name') <span class="text-[11px] font-bold text-error mt-0.5 block">{{ $message }}</span> @enderror
                </div>

                <!-- Username & Email Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Username (Login ID) <span class="text-error">*</span></label>
                        <input 
                            type="text" 
                            wire:model="username"
                            class="w-full bg-white border border-slate-200 rounded-xl px-3.5 py-2 text-xs text-neutral-strong focus:outline-none focus:ring-2 focus:ring-brand/20 focus:border-brand transition-all"
                        >
                        @error('username') <span class="text-[11px] font-bold text-error mt-0.5 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Email Address <span class="text-error">*</span></label>
                        <input 
                            type="email" 
                            wire:model="email"
                            class="w-full bg-white border border-slate-200 rounded-xl px-3.5 py-2 text-xs text-neutral-strong focus:outline-none focus:ring-2 focus:ring-brand/20 focus:border-brand transition-all"
                        >
                        @error('email') <span class="text-[11px] font-bold text-error mt-0.5 block">{{ $message }}</span> @enderror
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="pt-3 border-t border-slate-100 flex justify-end">
                    <button 
                        type="submit"
                        class="bg-brand hover:bg-emerald-800 text-white font-bold text-xs py-2.5 px-5 rounded-xl tracking-wider uppercase transition-colors shadow-xs flex items-center gap-2 cursor-pointer"
                    >
                        <svg wire:loading wire:target="updateProfile" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        <span>Save Profile Changes</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- Right: Security & Password Card (5 Cols) -->
        <div id="password-card" class="lg:col-span-5 space-y-6">
            <!-- Password Update Form -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs space-y-4">
                <div class="border-b border-slate-100 pb-3">
                    <h3 class="text-base font-black text-neutral-strong tracking-tight">Security & Password</h3>
                    <p class="text-xs text-slate-500 font-medium mt-0.5">Ensure your account uses a strong, private password.</p>
                </div>

                <form wire:submit.prevent="updatePassword" class="space-y-4">
                    @if (! $must_change_password)
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Current Password <span class="text-error">*</span></label>
                            <input 
                                type="password" 
                                wire:model="current_password"
                                placeholder="Enter current password"
                                class="w-full bg-white border border-slate-200 rounded-xl px-3.5 py-2 text-xs text-neutral-strong focus:outline-none focus:ring-2 focus:ring-brand/20 focus:border-brand transition-all"
                            >
                            @error('current_password') <span class="text-[11px] font-bold text-error mt-0.5 block">{{ $message }}</span> @enderror
                        </div>
                    @else
                        <div class="p-3 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 text-xs">
                            <p class="font-bold">Administrator Reset Active</p>
                            <p class="text-[11px] text-amber-800 mt-0.5">You do not need your previous password. Set your new private password below.</p>
                        </div>
                    @endif

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">New Password <span class="text-error">*</span></label>
                        <input 
                            type="password" 
                            wire:model="new_password"
                            placeholder="Min. 8 characters"
                            class="w-full bg-white border border-slate-200 rounded-xl px-3.5 py-2 text-xs text-neutral-strong focus:outline-none focus:ring-2 focus:ring-brand/20 focus:border-brand transition-all"
                        >
                        @error('new_password') <span class="text-[11px] font-bold text-error mt-0.5 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Confirm New Password <span class="text-error">*</span></label>
                        <input 
                            type="password" 
                            wire:model="new_password_confirmation"
                            placeholder="Re-enter new password"
                            class="w-full bg-white border border-slate-200 rounded-xl px-3.5 py-2 text-xs text-neutral-strong focus:outline-none focus:ring-2 focus:ring-brand/20 focus:border-brand transition-all"
                        >
                        @error('new_password_confirmation') <span class="text-[11px] font-bold text-error mt-0.5 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="pt-3 border-t border-slate-100 flex justify-end">
                        <button 
                            type="submit"
                            class="bg-accent hover:bg-amber-400 text-neutral-strong font-bold text-xs py-2.5 px-5 rounded-xl tracking-wider uppercase transition-colors shadow-xs flex items-center gap-2 cursor-pointer"
                        >
                            <svg wire:loading wire:target="updatePassword" class="animate-spin -ml-1 mr-2 h-4 w-4 text-neutral-strong" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            <span>Update Password</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Role & Permissions Inspector Card -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs space-y-3">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div>
                        <h4 class="text-xs font-bold uppercase tracking-wider text-slate-700">Assigned System Scope</h4>
                        <p class="text-[11px] text-slate-400 font-medium">Your current access level in eKalinga+.</p>
                    </div>
                    @if ($user->isSuperAdmin())
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-brand border border-emerald-200">Full Root</span>
                    @else
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200">Role-Bounded</span>
                    @endif
                </div>

                @if ($user->isSuperAdmin())
                    <p class="text-xs text-brand font-medium">You have unrestricted access to all municipal modules, ledgers, system settings, and user management.</p>
                @else
                    <div class="space-y-1.5 pt-1">
                        <p class="text-[11px] font-bold text-slate-600">Granted Modules:</p>
                        <div class="flex flex-wrap gap-1.5">
                            @php
                                $perms = is_array($user->permissions) ? $user->permissions : [];
                            @endphp
                            @forelse ($perms as $p)
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                    {{ ucfirst($p) }}
                                </span>
                            @empty
                                <span class="text-[11px] text-slate-400 italic">No module permissions assigned. Contact a SuperAdmin.</span>
                            @endforelse
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
