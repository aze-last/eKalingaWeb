<div class="space-y-6">
    <!-- Header Title & Action Bar -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded text-[11px] font-black uppercase tracking-wider bg-brand/10 text-brand border border-brand/20">SuperAdmin Restricted</span>
                <span class="text-xs text-slate-400 font-medium">Security & Authorization Control</span>
            </div>
            <h1 class="text-2xl font-black text-neutral-strong tracking-tight mt-1">System User Management</h1>
            <p class="text-xs text-slate-500 font-medium mt-0.5">Configure municipal accounts, assign role boundaries, and control module permissions.</p>
        </div>

        <div class="flex items-center gap-3">
            <button 
                wire:click="openCreateModal"
                class="bg-accent hover:bg-amber-400 text-neutral-strong font-bold text-xs py-2.5 px-4 rounded-xl tracking-wider uppercase transition-all shadow-xs hover:shadow flex items-center gap-2 cursor-pointer active:scale-95"
            >
                <svg class="w-4 h-4 text-neutral-strong stroke-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                <span>Add New User</span>
            </button>
        </div>
    </div>

    <!-- Metric Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total Users -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Total System Users</span>
                <div class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center text-slate-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
            </div>
            <p class="text-3xl font-black text-neutral-strong mt-2">{{ number_format($metrics['total']) }}</p>
            <p class="text-[11px] text-slate-400 font-medium mt-1">Total registered municipal operators</p>
        </div>

        <!-- SuperAdmins -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-brand">Super Administrators</span>
                <div class="w-8 h-8 rounded-lg bg-emerald-50 border border-emerald-200 flex items-center justify-center text-brand">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                </div>
            </div>
            <p class="text-3xl font-black text-brand mt-2">{{ number_format($metrics['superadmins']) }}</p>
            <p class="text-[11px] text-slate-400 font-medium mt-1">Full root privileges & user management</p>
        </div>

        <!-- Admins -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-700">Administrators</span>
                <div class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center text-slate-700">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                </div>
            </div>
            <p class="text-3xl font-black text-slate-800 mt-2">{{ number_format($metrics['admins']) }}</p>
            <p class="text-[11px] text-slate-400 font-medium mt-1">Granular module-bounded operators</p>
        </div>

        <!-- Inactive -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-rose-700">Deactivated</span>
                <div class="w-8 h-8 rounded-lg bg-rose-50 border border-rose-200 flex items-center justify-center text-rose-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                </div>
            </div>
            <p class="text-3xl font-black text-rose-700 mt-2">{{ number_format($metrics['inactive']) }}</p>
            <p class="text-[11px] text-slate-400 font-medium mt-1">Suspended or locked logins</p>
        </div>
    </div>

    <!-- Filter & Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <!-- Filter Controls -->
        <div class="p-5 border-b border-slate-200 bg-slate-50/50 flex flex-col md:flex-row md:items-center justify-between gap-3">
            <div class="flex flex-1 items-center gap-3">
                <div class="relative flex-1 max-w-md">
                    <input 
                        type="text" 
                        wire:model.live.debounce.300ms="search"
                        placeholder="Search by name, username, or email..."
                        class="w-full bg-white border border-slate-200 rounded-xl px-3.5 py-2 pl-9 text-xs text-neutral-strong placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand/20 focus:border-brand transition-all"
                    >
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>

                <div class="w-40">
                    <select 
                        wire:model.live="roleFilter"
                        class="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs text-neutral-strong focus:outline-none focus:ring-2 focus:ring-brand/20 focus:border-brand transition-all"
                    >
                        <option value="ALL">All Roles</option>
                        <option value="SuperAdmin">SuperAdmin</option>
                        <option value="Admin">Admin</option>
                    </select>
                </div>

                <div class="w-40">
                    <select 
                        wire:model.live="statusFilter"
                        class="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs text-neutral-strong focus:outline-none focus:ring-2 focus:ring-brand/20 focus:border-brand transition-all"
                    >
                        <option value="ALL">All Statuses</option>
                        <option value="ACTIVE">Active Only</option>
                        <option value="INACTIVE">Deactivated Only</option>
                    </select>
                </div>
            </div>

            <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                Showing {{ $users->total() }} Users
            </div>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-50 text-[11px] font-black uppercase tracking-wider text-slate-500">
                        <th class="py-3 px-4">User</th>
                        <th class="py-3 px-4">Role</th>
                        <th class="py-3 px-4">Module Permissions</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4">Last Login</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs font-medium">
                    @forelse ($users as $user)
                        <tr class="hover:bg-slate-50/75 transition-colors {{ ! $user->is_active ? 'bg-slate-50/40 text-slate-400' : '' }}">
                            <!-- Identity -->
                            <td class="py-3 px-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl flex items-center justify-center font-bold text-white text-xs shrink-0 shadow-2xs {{ $user->isSuperAdmin() ? 'bg-brand' : 'bg-slate-700' }}">
                                        {{ strtoupper(substr($user->name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="font-bold text-neutral-strong {{ ! $user->is_active ? 'line-through text-slate-400' : '' }}">{{ $user->name }}</span>
                                            @if ($user->id === Auth::id())
                                                <span class="px-1.5 py-0.5 rounded text-[9px] font-extrabold bg-amber-100 text-amber-900 border border-amber-300">You</span>
                                            @endif
                                        </div>
                                        <div class="flex items-center gap-2 text-[11px] text-slate-400">
                                            <span>&#64;{{ $user->username ?? 'user' }}</span>
                                            <span>&bull;</span>
                                            <span>{{ $user->email }}</span>
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- Role -->
                            <td class="py-3 px-4">
                                @if ($user->isSuperAdmin())
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider bg-emerald-50 text-brand border border-emerald-200">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                        <span>SuperAdmin</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider bg-slate-100 text-slate-700 border border-slate-200">
                                        Administrator
                                    </span>
                                @endif
                            </td>

                            <!-- Module Permissions -->
                            <td class="py-3 px-4">
                                @if ($user->isSuperAdmin())
                                    <span class="inline-flex items-center gap-1 text-[11px] font-bold text-brand">
                                        <svg class="w-3.5 h-3.5 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        <span>Full Root Access (All Modules)</span>
                                    </span>
                                @else
                                    <div class="flex flex-wrap gap-1 max-w-md">
                                        @php
                                            $userPerms = is_array($user->permissions) ? $user->permissions : [];
                                        @endphp
                                        @if (empty($userPerms))
                                            <span class="text-[11px] text-slate-400 italic">No module permissions assigned</span>
                                        @else
                                            @foreach ($userPerms as $perm)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                                    {{ $availableModules[$perm]['label'] ?? ucfirst($perm) }}
                                                </span>
                                            @endforeach
                                        @endif
                                    </div>
                                @endif
                            </td>

                            <!-- Status -->
                            <td class="py-3 px-4">
                                @if ($user->is_active)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        <span>Active</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                        <span>Deactivated</span>
                                    </span>
                                @endif
                            </td>

                            <!-- Last Login -->
                            <td class="py-3 px-4 text-slate-500 text-[11px]">
                                {{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Never logged in' }}
                            </td>

                            <!-- Actions -->
                            <td class="py-3 px-4 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <!-- Edit User & Permissions -->
                                    <button 
                                        wire:click="openEditModal({{ $user->id }})"
                                        title="Edit User & Permissions"
                                        class="p-1.5 rounded-lg text-slate-500 hover:text-brand hover:bg-emerald-50 transition-colors cursor-pointer"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </button>

                                    <!-- Reset Password -->
                                    <button 
                                        wire:click="openPasswordModal({{ $user->id }})"
                                        title="Direct Password Reset"
                                        class="p-1.5 rounded-lg text-slate-500 hover:text-accent hover:bg-amber-50 transition-colors cursor-pointer"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                                    </button>

                                    @if ($user->id !== Auth::id())
                                        <!-- Toggle Status -->
                                        <button 
                                            wire:click="toggleStatus({{ $user->id }})"
                                            title="{{ $user->is_active ? 'Deactivate User' : 'Activate User' }}"
                                            class="p-1.5 rounded-lg transition-colors cursor-pointer {{ $user->is_active ? 'text-slate-500 hover:text-rose-600 hover:bg-rose-50' : 'text-slate-500 hover:text-emerald-600 hover:bg-emerald-50' }}"
                                        >
                                            @if ($user->is_active)
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                            @else
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            @endif
                                        </button>

                                        <!-- Soft Delete -->
                                        <button 
                                            wire:click="openDeleteModal({{ $user->id }})"
                                            title="Delete User (Soft Delete)"
                                            class="p-1.5 rounded-lg text-slate-400 hover:text-error hover:bg-rose-50 transition-colors cursor-pointer"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-400">
                                <div class="flex flex-col items-center justify-center">
                                    <svg class="w-10 h-10 text-slate-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                    <p class="font-bold text-sm text-slate-600">No users found</p>
                                    <p class="text-xs text-slate-400 mt-0.5">Try adjusting your search criteria or role filters.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if ($users->hasPages())
            <div class="p-4 border-t border-slate-200 bg-slate-50/50">
                {{ $users->links() }}
            </div>
        @endif
    </div>

    <!-- ========================================================================= -->
    <!-- MODAL 1: CREATE / EDIT USER OVERLAY PANEL (Alpine.js Transitions & Blur) -->
    <!-- ========================================================================= -->
    <div 
        x-data="{ show: @entangle('showUserModal') }"
        x-show="show"
        x-cloak
        class="fixed inset-0 z-50 overflow-y-auto"
        aria-labelledby="modal-title" 
        role="dialog" 
        aria-modal="true"
    >
        <!-- Backdrop Scrim -->
        <div 
            x-show="show"
            x-transition:enter="ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-[#0F172A]/80 backdrop-blur-md transition-opacity"
            @click="$wire.closeUserModal()"
        ></div>

        <div class="flex min-h-screen items-center justify-center p-4 text-center sm:p-0">
            <div 
                x-show="show"
                x-transition:enter="ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="ease-in duration-150"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-xl border border-slate-200"
            >
                <div class="bg-white px-6 pt-6 pb-4">
                    <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                        <div>
                            <h3 class="text-lg font-black text-neutral-strong tracking-tight" id="modal-title">
                                {{ $isEditing ? 'Edit User Account & Permissions' : 'Create New System User' }}
                            </h3>
                            <p class="text-xs text-slate-500 font-medium mt-0.5">
                                {{ $isEditing ? 'Update operator details and granular access control.' : 'Register a new administrator and assign module access.' }}
                            </p>
                        </div>
                        <button 
                            type="button" 
                            wire:click="closeUserModal"
                            class="p-1 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition-colors cursor-pointer"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <form wire:submit.prevent="saveUser" class="mt-4 space-y-4">
                        <!-- Name & Username Grid -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Full Name <span class="text-error">*</span></label>
                                <input 
                                    type="text" 
                                    wire:model="name"
                                    placeholder="e.g., Juan Dela Cruz"
                                    class="w-full bg-white border border-slate-200 rounded-xl px-3.5 py-2 text-xs text-neutral-strong placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand/20 focus:border-brand transition-all"
                                >
                                @error('name') <span class="text-[11px] font-bold text-error mt-0.5 block">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Username <span class="text-error">*</span></label>
                                <input 
                                    type="text" 
                                    wire:model="username"
                                    placeholder="e.g., juandc"
                                    class="w-full bg-white border border-slate-200 rounded-xl px-3.5 py-2 text-xs text-neutral-strong placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand/20 focus:border-brand transition-all"
                                >
                                @error('username') <span class="text-[11px] font-bold text-error mt-0.5 block">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <!-- Email & Role Grid -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Email Address <span class="text-error">*</span></label>
                                <input 
                                    type="email" 
                                    wire:model="email"
                                    placeholder="juan@sulop.gov.ph"
                                    class="w-full bg-white border border-slate-200 rounded-xl px-3.5 py-2 text-xs text-neutral-strong placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand/20 focus:border-brand transition-all"
                                >
                                @error('email') <span class="text-[11px] font-bold text-error mt-0.5 block">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Role <span class="text-error">*</span></label>
                                <select 
                                    wire:model.live="role"
                                    class="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs text-neutral-strong focus:outline-none focus:ring-2 focus:ring-brand/20 focus:border-brand transition-all"
                                    {{ $isEditing && $editingUserId === Auth::id() ? 'disabled' : '' }}
                                >
                                    <option value="Admin">Administrator (Bounded Access)</option>
                                    <option value="SuperAdmin">Super Administrator (Full Root Access)</option>
                                </select>
                                @error('role') <span class="text-[11px] font-bold text-error mt-0.5 block">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <!-- Passwords (Only on Create) -->
                        @if (! $isEditing)
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 p-4 rounded-xl bg-slate-50 border border-slate-200">
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Initial Password <span class="text-error">*</span></label>
                                    <input 
                                        type="password" 
                                        wire:model="password"
                                        placeholder="Min. 8 characters"
                                        class="w-full bg-white border border-slate-200 rounded-xl px-3.5 py-2 text-xs text-neutral-strong focus:outline-none focus:ring-2 focus:ring-brand/20 focus:border-brand transition-all"
                                    >
                                    @error('password') <span class="text-[11px] font-bold text-error mt-0.5 block">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Confirm Password <span class="text-error">*</span></label>
                                    <input 
                                        type="password" 
                                        wire:model="password_confirmation"
                                        placeholder="Re-enter password"
                                        class="w-full bg-white border border-slate-200 rounded-xl px-3.5 py-2 text-xs text-neutral-strong focus:outline-none focus:ring-2 focus:ring-brand/20 focus:border-brand transition-all"
                                    >
                                    @error('password_confirmation') <span class="text-[11px] font-bold text-error mt-0.5 block">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        @endif

                        <!-- Module Permissions Matrix (Only visible when role is Admin) -->
                        @if ($role === 'Admin')
                            <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 space-y-3">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-xs font-bold uppercase tracking-wider text-slate-700">Module Access Permissions</p>
                                        <p class="text-[11px] text-slate-500 font-medium">Select the system modules this administrator is permitted to access.</p>
                                    </div>
                                    <span class="text-[10px] font-black uppercase tracking-wider text-brand px-2 py-0.5 rounded bg-emerald-50 border border-emerald-200">
                                        {{ count($selectedPermissions) }} Granted
                                    </span>
                                </div>

                                <div class="space-y-2 pt-1">
                                    @foreach ($availableModules as $key => $module)
                                        <label class="flex items-start gap-3 p-2.5 rounded-lg border border-slate-200 bg-white hover:bg-slate-50/80 transition-colors cursor-pointer">
                                            <input 
                                                type="checkbox" 
                                                wire:model="selectedPermissions" 
                                                value="{{ $key }}"
                                                class="w-4 h-4 rounded text-brand focus:ring-brand/20 border-slate-300 mt-0.5 cursor-pointer"
                                            >
                                            <div class="min-w-0">
                                                <p class="text-xs font-bold text-neutral-strong">{{ $module['label'] }}</p>
                                                <p class="text-[11px] text-slate-500">{{ $module['desc'] }}</p>
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                                @error('selectedPermissions') <span class="text-[11px] font-bold text-error block">{{ $message }}</span> @enderror
                            </div>
                        @else
                            <div class="p-4 rounded-xl bg-emerald-50/50 border border-emerald-200 flex items-center gap-3">
                                <svg class="w-5 h-5 text-brand shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                <div>
                                    <p class="text-xs font-bold text-brand">Super Administrators have universal root access</p>
                                    <p class="text-[11px] text-slate-600">All module permissions and system settings are unrestricted for this role.</p>
                                </div>
                            </div>
                        @endif

                        <!-- Status Toggle (On Edit) -->
                        @if ($isEditing && $editingUserId !== Auth::id())
                            <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-slate-200">
                                <div>
                                    <p class="text-xs font-bold text-neutral-strong">Account Status</p>
                                    <p class="text-[11px] text-slate-500">Allow this user to sign in to the application.</p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" wire:model="is_active" class="sr-only peer">
                                    <div class="w-9 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-brand"></div>
                                </label>
                            </div>
                        @endif

                        <!-- Modal Actions -->
                        <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-2">
                            <button 
                                type="button" 
                                wire:click="closeUserModal"
                                class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-100 transition-colors cursor-pointer"
                            >
                                Cancel
                            </button>
                            <button 
                                type="submit"
                                class="bg-brand hover:bg-emerald-800 text-white font-bold text-xs py-2 px-5 rounded-xl tracking-wider uppercase transition-colors shadow-xs flex items-center gap-2 cursor-pointer"
                            >
                                <svg wire:loading wire:target="saveUser" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                <span>{{ $isEditing ? 'Save Changes' : 'Create Account' }}</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- MODAL 2: DIRECT PASSWORD RESET (Alpine.js Transitions & Blur)              -->
    <!-- ========================================================================= -->
    <div 
        x-data="{ show: @entangle('showPasswordModal') }"
        x-show="show"
        x-cloak
        class="fixed inset-0 z-50 overflow-y-auto"
        aria-labelledby="reset-modal-title" 
        role="dialog" 
        aria-modal="true"
    >
        <!-- Backdrop Scrim -->
        <div 
            x-show="show"
            x-transition:enter="ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-[#0F172A]/80 backdrop-blur-md transition-opacity"
            @click="$wire.closePasswordModal()"
        ></div>

        <div class="flex min-h-screen items-center justify-center p-4 text-center sm:p-0">
            <div 
                x-show="show"
                x-transition:enter="ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="ease-in duration-150"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-md border border-slate-200"
            >
                <div class="bg-white p-6">
                    <div class="flex items-center gap-3 pb-4 border-b border-slate-100">
                        <div class="w-10 h-10 rounded-xl bg-amber-50 border border-amber-200 flex items-center justify-center text-accent">
                            <svg class="w-5 h-5 text-accent stroke-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                        </div>
                        <div>
                            <h3 class="text-base font-black text-neutral-strong tracking-tight" id="reset-modal-title">
                                Reset Account Password
                            </h3>
                            <p class="text-xs text-slate-500 font-medium">Target user: <span class="font-bold text-neutral-strong">{{ $targetPasswordUserName }}</span></p>
                        </div>
                    </div>

                    <form wire:submit.prevent="resetPassword" class="mt-4 space-y-4">
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

                        <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-2">
                            <button 
                                type="button" 
                                wire:click="closePasswordModal"
                                class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-100 transition-colors cursor-pointer"
                            >
                                Cancel
                            </button>
                            <button 
                                type="submit"
                                class="bg-accent hover:bg-amber-400 text-neutral-strong font-bold text-xs py-2 px-5 rounded-xl tracking-wider uppercase transition-colors shadow-xs cursor-pointer"
                            >
                                Update Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- MODAL 3: SOFT DELETE CONFIRMATION DIALOG (Alpine.js Transitions & Blur)   -->
    <!-- ========================================================================= -->
    <div 
        x-data="{ show: @entangle('showDeleteModal') }"
        x-show="show"
        x-cloak
        class="fixed inset-0 z-50 overflow-y-auto"
        aria-labelledby="delete-modal-title" 
        role="dialog" 
        aria-modal="true"
    >
        <!-- Backdrop Scrim -->
        <div 
            x-show="show"
            x-transition:enter="ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-[#0F172A]/80 backdrop-blur-md transition-opacity"
            @click="$wire.closeDeleteModal()"
        ></div>

        <div class="flex min-h-screen items-center justify-center p-4 text-center sm:p-0">
            <div 
                x-show="show"
                x-transition:enter="ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="ease-in duration-150"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-md border border-slate-200 p-6"
            >
                <div class="flex items-center gap-3 pb-3">
                    <div class="w-10 h-10 rounded-xl bg-rose-50 border border-rose-200 flex items-center justify-center text-error">
                        <svg class="w-5 h-5 text-error" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-base font-black text-neutral-strong" id="delete-modal-title">Confirm Account Soft Deletion</h3>
                        <p class="text-xs text-slate-500 font-medium">Target: <span class="font-bold text-neutral-strong">{{ $targetDeleteUserName }}</span></p>
                    </div>
                </div>

                <div class="p-3 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 text-xs mt-2">
                    <p class="font-bold">Safe Soft-Delete Safeguard:</p>
                    <p class="text-[11px] text-amber-800 mt-0.5">This user will be immediately blocked from logging in. Database records and audit trails remain preserved in the system.</p>
                </div>

                <div class="pt-5 flex items-center justify-end gap-2">
                    <button 
                        type="button" 
                        wire:click="closeDeleteModal"
                        class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-100 transition-colors cursor-pointer"
                    >
                        Cancel
                    </button>
                    <button 
                        type="button" 
                        wire:click="deleteUser"
                        class="bg-error hover:bg-rose-800 text-white font-bold text-xs py-2 px-5 rounded-xl tracking-wider uppercase transition-colors shadow-xs cursor-pointer"
                    >
                        Confirm Delete
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
