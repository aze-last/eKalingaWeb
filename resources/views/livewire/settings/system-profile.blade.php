<div class="space-y-6 max-w-7xl mx-auto">
    <!-- Header Title Bar -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded text-[11px] font-black uppercase tracking-wider bg-brand/10 text-brand border border-brand/20">SuperAdmin Only</span>
                <span class="text-xs text-slate-400 font-medium">System Configuration</span>
            </div>
            <h1 class="text-2xl font-black text-neutral-strong tracking-tight mt-1">System Profile & Brand Identity</h1>
            <p class="text-xs text-slate-500 font-medium mt-0.5">Customize municipal credentials, seal logos, tab favicon, and the login hero background wallpaper.</p>
        </div>

        <div class="flex items-center gap-3">
            <button 
                wire:click="saveSettings"
                class="bg-brand hover:bg-emerald-800 text-white font-bold text-xs py-2.5 px-5 rounded-xl tracking-wider uppercase transition-all shadow-xs hover:shadow flex items-center gap-2 cursor-pointer active:scale-95"
            >
                <svg wire:loading wire:target="saveSettings" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                <svg wire:loading.remove wire:target="saveSettings" class="w-4 h-4 text-white stroke-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                <span>Save All Changes</span>
            </button>
        </div>
    </div>

    <!-- Main Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Left Configuration Column (7 Cols) -->
        <div class="lg:col-span-7 space-y-6">
            <!-- 1. Text & Hierarchy Settings -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs space-y-5">
                <div class="border-b border-slate-100 pb-3">
                    <h3 class="text-base font-black text-neutral-strong tracking-tight">System & Municipal Credentials</h3>
                    <p class="text-xs text-slate-500 font-medium">These names are displayed across the header, reports, PDF certificates, and login.</p>
                </div>

                <div class="space-y-4">
                    <!-- System Name & Subtitle -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">System Name <span class="text-error">*</span></label>
                            <input 
                                type="text" 
                                wire:model.live="system_name"
                                placeholder="e.g. eKalinga+"
                                class="w-full bg-white border border-slate-200 rounded-xl px-3.5 py-2 text-xs text-neutral-strong focus:outline-none focus:ring-2 focus:ring-brand/20 focus:border-brand transition-all"
                            >
                            @error('system_name') <span class="text-[11px] font-bold text-error mt-0.5 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">System Subtitle</label>
                            <input 
                                type="text" 
                                wire:model.live="system_subtitle"
                                placeholder="e.g. Ayuda Management System"
                                class="w-full bg-white border border-slate-200 rounded-xl px-3.5 py-2 text-xs text-neutral-strong focus:outline-none focus:ring-2 focus:ring-brand/20 focus:border-brand transition-all"
                            >
                            @error('system_subtitle') <span class="text-[11px] font-bold text-error mt-0.5 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <!-- Municipality & Province -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Municipality / City Name <span class="text-error">*</span></label>
                            <input 
                                type="text" 
                                wire:model.live="municipality_name"
                                placeholder="e.g. Municipality of Sulop"
                                class="w-full bg-white border border-slate-200 rounded-xl px-3.5 py-2 text-xs text-neutral-strong focus:outline-none focus:ring-2 focus:ring-brand/20 focus:border-brand transition-all"
                            >
                            @error('municipality_name') <span class="text-[11px] font-bold text-error mt-0.5 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Province Name</label>
                            <input 
                                type="text" 
                                wire:model.live="province_name"
                                placeholder="e.g. Province of Davao del Sur"
                                class="w-full bg-white border border-slate-200 rounded-xl px-3.5 py-2 text-xs text-neutral-strong focus:outline-none focus:ring-2 focus:ring-brand/20 focus:border-brand transition-all"
                            >
                            @error('province_name') <span class="text-[11px] font-bold text-error mt-0.5 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <!-- Country Header & Municipal Address -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Country / Government Header</label>
                            <input 
                                type="text" 
                                wire:model.live="country_name"
                                placeholder="e.g. Republic of the Philippines"
                                class="w-full bg-white border border-slate-200 rounded-xl px-3.5 py-2 text-xs text-neutral-strong focus:outline-none focus:ring-2 focus:ring-brand/20 focus:border-brand transition-all"
                            >
                            @error('country_name') <span class="text-[11px] font-bold text-error mt-0.5 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Municipal Address / Location Line</label>
                            <input 
                                type="text" 
                                wire:model.live="municipal_address"
                                placeholder="e.g. Sulop Digos City Davao Del Sur"
                                class="w-full bg-white border border-slate-200 rounded-xl px-3.5 py-2 text-xs text-neutral-strong focus:outline-none focus:ring-2 focus:ring-brand/20 focus:border-brand transition-all"
                            >
                            @error('municipal_address') <span class="text-[11px] font-bold text-error mt-0.5 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <!-- Tagline / Motto -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Municipal Tagline / Motto</label>
                        <input 
                            type="text" 
                            wire:model.live="tagline"
                            placeholder="e.g. Better Service, Better Care"
                            class="w-full bg-white border border-slate-200 rounded-xl px-3.5 py-2 text-xs text-neutral-strong focus:outline-none focus:ring-2 focus:ring-brand/20 focus:border-brand transition-all"
                        >
                        @error('tagline') <span class="text-[11px] font-bold text-error mt-0.5 block">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            <!-- 2. Media Assets (Logos, Favicon & Wallpaper) -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs space-y-5">
                <div class="border-b border-slate-100 pb-3">
                    <h3 class="text-base font-black text-neutral-strong tracking-tight">Media & Visual Assets</h3>
                    <p class="text-xs text-slate-500 font-medium">Upload municipal seals, favicon icons, and login panel background imagery.</p>
                </div>

                <!-- Official Seal & Favicon Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Website Seal / Logo -->
                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 space-y-3">
                        <div class="flex items-center justify-between">
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Website Seal / Logo</label>
                            <span class="text-[10px] text-slate-400 font-medium">PNG, JPG, SVG (Max 2MB)</span>
                        </div>

                        <div class="flex items-center gap-3">
                            <div class="w-14 h-14 rounded-xl bg-white border border-slate-200 p-1 flex items-center justify-center shrink-0 shadow-2xs">
                                @if ($sealFile)
                                    <img src="{{ $sealFile->temporaryUrl() }}" alt="Seal Preview" class="w-full h-full object-contain">
                                @elseif ($existingSealUrl)
                                    <img src="{{ asset($existingSealUrl) }}" alt="Seal" class="w-full h-full object-contain">
                                @else
                                    <span class="text-xs text-slate-300">No Seal</span>
                                @endif
                            </div>

                            <div class="flex-1 space-y-1.5">
                                <input type="file" wire:model="sealFile" id="seal-input" accept="image/*" class="hidden">
                                <div class="flex items-center gap-2">
                                    <label for="seal-input" class="bg-white border border-slate-200 hover:bg-slate-100 text-neutral-strong font-bold text-[11px] py-1.5 px-3 rounded-lg transition-colors cursor-pointer shadow-2xs">
                                        Upload Logo
                                    </label>
                                    @if ($sealFile || ($existingSealUrl && $existingSealUrl !== '/images/Site_logo.png'))
                                        <button type="button" wire:click="removeSeal" class="text-slate-500 hover:text-error text-[11px] font-bold py-1.5 px-2 transition-colors cursor-pointer">
                                            Reset
                                        </button>
                                    @endif
                                </div>
                                <div wire:loading wire:target="sealFile" class="text-[10px] text-brand font-bold">Uploading seal...</div>
                            </div>
                        </div>
                        @error('sealFile') <span class="text-[11px] font-bold text-error block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Browser Tab Favicon -->
                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 space-y-3">
                        <div class="flex items-center justify-between">
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Browser Tab Favicon</label>
                            <span class="text-[10px] text-slate-400 font-medium">ICO, PNG, SVG (Max 1MB)</span>
                        </div>

                        <div class="flex items-center gap-3">
                            <div class="w-14 h-14 rounded-xl bg-white border border-slate-200 p-2 flex items-center justify-center shrink-0 shadow-2xs">
                                @if ($faviconFile)
                                    <img src="{{ $faviconFile->temporaryUrl() }}" alt="Favicon Preview" class="w-8 h-8 object-contain">
                                @elseif ($existingFaviconUrl)
                                    <img src="{{ asset($existingFaviconUrl) }}" alt="Favicon" class="w-8 h-8 object-contain">
                                @else
                                    <span class="text-xs text-slate-300">No Icon</span>
                                @endif
                            </div>

                            <div class="flex-1 space-y-1.5">
                                <input type="file" wire:model="faviconFile" id="fav-input" accept="image/*" class="hidden">
                                <div class="flex items-center gap-2">
                                    <label for="fav-input" class="bg-white border border-slate-200 hover:bg-slate-100 text-neutral-strong font-bold text-[11px] py-1.5 px-3 rounded-lg transition-colors cursor-pointer shadow-2xs">
                                        Upload Favicon
                                    </label>
                                    @if ($faviconFile || ($existingFaviconUrl && $existingFaviconUrl !== '/images/Site_logo.png'))
                                        <button type="button" wire:click="removeFavicon" class="text-slate-500 hover:text-error text-[11px] font-bold py-1.5 px-2 transition-colors cursor-pointer">
                                            Reset
                                        </button>
                                    @endif
                                </div>
                                <div wire:loading wire:target="faviconFile" class="text-[10px] text-brand font-bold">Uploading favicon...</div>
                            </div>
                        </div>
                        @error('faviconFile') <span class="text-[11px] font-bold text-error block">{{ $message }}</span> @enderror
                    </div>
                </div>

                <!-- Login Left Panel Wallpaper Background -->
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 space-y-3">
                    <div class="flex items-center justify-between">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Login Left Panel Background Photo</label>
                            <p class="text-[11px] text-slate-500 font-medium">Municipal hall, landmark, or scenic photo displayed with a modern blue theme overlay.</p>
                        </div>
                        <span class="text-[10px] text-slate-400 font-medium">JPG, PNG (Max 5MB)</span>
                    </div>

                    <div class="flex items-center gap-4">
                        <div class="relative w-32 h-20 rounded-xl overflow-hidden bg-slate-900 border border-slate-200 shrink-0 shadow-2xs">
                            @if ($loginBgFile)
                                <img src="{{ $loginBgFile->temporaryUrl() }}" alt="BG Preview" class="w-full h-full object-cover">
                            @elseif ($existingLoginBgUrl)
                                <img src="{{ asset($existingLoginBgUrl) }}" alt="BG" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex flex-col items-center justify-center text-[10px] text-slate-400 bg-gradient-to-br from-[#070D1C] to-[#0B1426]">
                                    <svg class="w-5 h-5 text-slate-500 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    <span>Default Theme</span>
                                </div>
                            @endif
                        </div>

                        <div class="flex-1 space-y-2">
                            <input type="file" wire:model="loginBgFile" id="bg-input" accept="image/*" class="hidden">
                            <div class="flex items-center gap-2">
                                <label for="bg-input" class="bg-white border border-slate-200 hover:bg-slate-100 text-neutral-strong font-bold text-xs py-2 px-3.5 rounded-xl transition-colors cursor-pointer shadow-2xs inline-flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    <span>Upload Background Photo</span>
                                </label>
                                @if ($loginBgFile || $existingLoginBgUrl)
                                    <button type="button" wire:click="removeLoginBg" class="text-error hover:bg-rose-50 text-xs font-bold py-2 px-3 rounded-xl transition-colors cursor-pointer">
                                        Remove
                                    </button>
                                @endif
                            </div>
                            <div wire:loading wire:target="loginBgFile" class="text-[10px] text-brand font-bold">Uploading background wallpaper...</div>
                        </div>
                    </div>
                    @error('loginBgFile') <span class="text-[11px] font-bold text-error block">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>

        <!-- Right Live Preview Column (5 Cols) -->
        <div class="lg:col-span-5 space-y-6">
            <!-- Simulated Browser Tab Preview -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs space-y-3">
                <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-700">Browser Tab Appearance</h4>
                    <span class="text-[10px] font-bold text-brand bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">Live Simulation</span>
                </div>

                <div class="bg-slate-100 p-2 rounded-xl border border-slate-200">
                    <div class="flex items-center gap-2 bg-white px-3 py-1.5 rounded-lg border border-slate-200 shadow-2xs max-w-xs">
                        <div class="w-4 h-4 shrink-0">
                            @if ($faviconFile)
                                <img src="{{ $faviconFile->temporaryUrl() }}" alt="Favicon" class="w-full h-full object-contain">
                            @elseif ($existingFaviconUrl)
                                <img src="{{ asset($existingFaviconUrl) }}" alt="Favicon" class="w-full h-full object-contain">
                            @else
                                <img src="{{ asset('/images/Site_logo.png') }}" alt="Favicon" class="w-full h-full object-contain">
                            @endif
                        </div>
                        <span class="text-xs font-bold text-neutral-strong truncate">
                            {{ $system_name }} - {{ $municipality_name }}
                        </span>
                        <svg class="w-3 h-3 text-slate-400 ml-auto shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </div>
                </div>
            </div>

            <!-- Simulated Login Screen Left Hero Panel (Matches Reference Screenshot!) -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs space-y-3">
                <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-700">Login Hero Panel Preview</h4>
                    <span class="text-[10px] font-bold text-accent bg-amber-50 px-2 py-0.5 rounded border border-amber-200">Exact Match</span>
                </div>

                <!-- Simulation Container with Blue Gradient / Image Overlay -->
                <div class="relative w-full rounded-2xl overflow-hidden shadow-lg border border-slate-300 min-h-[440px] flex flex-col justify-between p-6 text-white select-none">
                    <!-- Background Wallpaper with Deep Blue Blend -->
                    <div 
                        class="absolute inset-0 bg-cover bg-center transition-all duration-300"
                        style="
                            @if ($loginBgFile)
                                background-image: url('{{ $loginBgFile->temporaryUrl() }}');
                            @elseif ($existingLoginBgUrl)
                                background-image: url('{{ asset($existingLoginBgUrl) }}');
                            @else
                                background-color: #1E3A8A;
                            @endif
                        "
                    ></div>

                    <!-- Blue Heraldic Tint Overlay -->
                    <div class="absolute inset-0 bg-gradient-to-b from-[#1D4ED8]/85 via-[#1E40AF]/90 to-[#172554]/95 backdrop-blur-[1px]"></div>

                    <!-- Ambient Glow -->
                    <div class="absolute top-1/4 left-1/2 -translate-x-1/2 w-48 h-48 bg-blue-400/20 rounded-full blur-2xl pointer-events-none"></div>

                    <!-- Top Sub-Header -->
                    <div class="relative z-10 flex items-center justify-between">
                        <div class="text-[10px] font-bold uppercase tracking-wider text-blue-200/90">
                            {{ $country_name }}
                        </div>
                        <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-white/15 text-white border border-white/20">Preview</span>
                    </div>

                    <!-- Center Seal & Hierarchy Badge -->
                    <div class="relative z-10 my-auto text-center space-y-4 py-4">
                        <div class="flex items-center justify-center">
                            <div class="relative group">
                                <div class="absolute inset-0 bg-white/25 rounded-full blur-xl animate-pulse"></div>
                                <div class="relative w-24 h-24 sm:w-28 sm:h-28 rounded-full bg-white/95 p-2 shadow-2xl border-2 border-white/80 flex items-center justify-center mx-auto">
                                    @if ($sealFile)
                                        <img src="{{ $sealFile->temporaryUrl() }}" alt="Seal" class="w-full h-full object-contain">
                                    @elseif ($existingSealUrl)
                                        <img src="{{ asset($existingSealUrl) }}" alt="Seal" class="w-full h-full object-contain">
                                    @else
                                        <img src="{{ asset('/images/Site_logo.png') }}" alt="Seal" class="w-full h-full object-contain">
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Municipality Text Hierarchy -->
                        <div class="space-y-0.5">
                            <p class="text-[11px] font-medium text-blue-200 italic tracking-wide">{{ $province_name }}</p>
                            <h2 class="text-xl sm:text-2xl font-black text-white tracking-tight drop-shadow-md uppercase">
                                {{ $municipality_name }}
                            </h2>
                            <p class="text-xs text-blue-100 font-medium tracking-wide">{{ $municipal_address }}</p>
                        </div>

                        <!-- Divider & Tagline -->
                        <div class="pt-4 max-w-xs mx-auto space-y-2">
                            <div class="h-px bg-white/25 w-full"></div>
                            <p class="text-xs font-semibold text-blue-200 italic tracking-wider">
                                "{{ $tagline }}"
                            </p>
                        </div>
                    </div>

                    <!-- Bottom Bar -->
                    <div class="relative z-10 flex items-center justify-between text-[10px] text-blue-300/80 pt-2 border-t border-white/10 font-mono">
                        <span>{{ $system_name }}</span>
                        <span>{{ $system_subtitle }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
