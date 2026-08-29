<div class="min-h-screen w-full flex flex-col lg:flex-row bg-[#080E1E] text-white relative overflow-hidden" x-data="{ showPassword: false }">
    <!-- Left Hero & Municipal Branding Section -->
    <div class="lg:w-7/12 relative min-h-[480px] lg:min-h-screen p-8 sm:p-12 lg:p-16 flex flex-col justify-between overflow-hidden">
        <!-- Background Wallpaper with Deep Blue Heraldic Overlay -->
        <div 
            class="absolute inset-0 bg-cover bg-center transition-all duration-700"
            style="
                @if ($loginBgUrl)
                    background-image: url('{{ asset($loginBgUrl) }}');
                @else
                    background-color: #1E3A8A;
                @endif
            "
        ></div>

        <!-- Blue Heraldic Tint & Vignette Overlay -->
        <div class="absolute inset-0 bg-gradient-to-br from-[#1D4ED8]/85 via-[#1E40AF]/90 to-[#0F172A]/95 backdrop-blur-[0.5px]"></div>

        <!-- Subtle Ambient Background Glow -->
        <div class="absolute -top-24 -left-24 w-96 h-96 bg-blue-400/20 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-24 -left-24 w-96 h-96 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <!-- Top Left Republic Header -->
        <div class="relative z-20 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-white/10 p-1 flex items-center justify-center backdrop-blur-xs border border-white/20">
                    <img src="{{ asset($municipalLogo) }}" alt="Seal" class="w-full h-full object-contain">
                </div>
                <div>
                    <span class="text-xs font-bold tracking-tight text-white block">{{ $systemName }}</span>
                    <span class="text-[10px] text-blue-200 font-mono block uppercase">{{ $systemSubtitle }}</span>
                </div>
            </div>
        </div>

        <!-- Center Large Municipal Seal Emblem & Hierarchy -->
        <div class="relative z-20 my-auto py-12 flex flex-col items-center justify-center text-center space-y-6">
            <!-- Seal with Glowing Aura -->
            <div class="relative group">
                <div class="absolute inset-0 bg-white/25 rounded-full blur-2xl group-hover:bg-white/35 transition-all duration-500 animate-pulse"></div>
                <div class="relative w-36 h-36 sm:w-44 sm:h-44 rounded-full bg-white/95 p-3 shadow-2xl border-2 border-white/90 flex items-center justify-center mx-auto transition-transform duration-300 group-hover:scale-105">
                    <img 
                        src="{{ asset($municipalLogo) }}" 
                        alt="{{ $municipalityName }} Official Seal" 
                        class="w-full h-full object-contain drop-shadow-md"
                    >
                </div>
            </div>

            <!-- Hierarchy Text -->
            <div class="space-y-1 max-w-lg mx-auto">
                <p class="text-xs sm:text-sm font-medium text-blue-100 italic tracking-wider">{{ $countryName }}</p>
                <p class="text-xs sm:text-sm font-medium text-blue-200 italic tracking-wider">{{ $provinceName }}</p>
                <h1 class="text-2xl sm:text-4xl font-black text-white tracking-tight leading-tight uppercase drop-shadow-md mt-1">
                    {{ $municipalityName }}
                </h1>
                <p class="text-xs sm:text-sm text-blue-100 font-medium tracking-wide mt-1">{{ $municipalAddress }}</p>
            </div>

            <!-- Divider & Tagline -->
            @if ($tagline)
                <div class="pt-2 max-w-md w-full mx-auto space-y-2">
                    <div class="h-px bg-white/25 w-3/4 mx-auto"></div>
                    <p class="text-xs sm:text-sm font-medium text-blue-200 italic tracking-widest">
                        "{{ $tagline }}"
                    </p>
                </div>
            @endif
        </div>

        <!-- Bottom System Line -->
        <div class="relative z-20 flex items-center justify-between text-xs text-blue-200/80 pt-4 border-t border-white/10 font-mono">
            <span>{{ $systemName }}</span>
            <span>Sulop Ayuda Management</span>
        </div>
    </div>

    <!-- Right Dedicated Sign-In Section -->
    <div class="lg:w-5/12 bg-[#080E1E] p-8 sm:p-12 lg:p-16 flex flex-col justify-center relative z-20">
        <div class="w-full max-w-md mx-auto space-y-6">
            
            <!-- Header with Title and System Identity -->
            <div class="text-center sm:text-left space-y-2">
                <div class="w-12 h-12 rounded-xl bg-[#0E1626] border border-slate-800 p-2 flex items-center justify-center shadow-xs mx-auto sm:mx-0">
                    <img src="{{ asset($municipalLogo) }}" alt="Seal" class="w-full h-full object-contain">
                </div>
                <div>
                    <h2 class="text-2xl sm:text-3xl font-black text-white tracking-tight">{{ $systemName }}</h2>
                    <p class="text-xs text-slate-400 font-medium">{{ $systemSubtitle }}</p>
                </div>
                <div class="pt-2">
                    <h3 class="text-base font-bold text-slate-200">Admin Sign In</h3>
                    <p class="text-xs text-slate-400">Sign in to manage municipal operations.</p>
                </div>
            </div>

            <!-- Validation Error Alert -->
            @error('login')
                <div class="p-3.5 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-400 flex items-start gap-2.5 text-xs">
                    <svg class="w-4 h-4 text-rose-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="font-medium">{{ $message }}</span>
                </div>
            @enderror

            <!-- Form -->
            <form wire:submit="authenticate" class="space-y-4">
                <!-- Username or Email Field -->
                <div>
                    <input 
                        wire:model="login"
                        type="text" 
                        id="login" 
                        placeholder="Username or Email"
                        class="w-full bg-[#0E1626] border border-slate-800 rounded-xl px-4 py-3.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all"
                        required
                        autofocus
                    >
                </div>

                <!-- Password Field -->
                <div class="relative">
                    <input 
                        wire:model="password"
                        :type="showPassword ? 'text' : 'password'" 
                        id="password" 
                        placeholder="Password"
                        class="w-full bg-[#0E1626] border border-slate-800 rounded-xl px-4 py-3.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-all pr-12"
                        required
                    >
                    <button 
                        type="button" 
                        @click="showPassword = !showPassword"
                        class="absolute right-4 top-3.5 text-slate-500 hover:text-slate-300 focus:outline-none cursor-pointer"
                        aria-label="Toggle password visibility"
                    >
                        <svg x-show="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        <svg x-show="showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                        </svg>
                    </button>
                    @error('password')
                        <p class="mt-1 text-xs text-rose-400 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Remember Me & Forgot Password -->
                <div class="flex items-center justify-between pt-1">
                    <label class="flex items-center gap-2 cursor-pointer select-none">
                        <input 
                            wire:model="remember"
                            type="checkbox" 
                            class="w-4 h-4 rounded bg-[#0E1626] border-slate-700 text-emerald-500 focus:ring-emerald-500 cursor-pointer"
                        >
                        <span class="text-xs text-slate-400 font-medium">Remember me</span>
                    </label>
                    <span class="text-xs text-slate-400 hover:text-slate-200 transition-colors cursor-pointer">
                        Forgot Password?
                    </span>
                </div>

                <!-- Green Submit Button -->
                <div class="pt-2">
                    <button 
                        type="submit" 
                        class="w-full bg-[#10B981] hover:bg-[#059669] text-white font-bold text-sm py-3.5 px-4 rounded-xl shadow-lg shadow-emerald-950/40 transition-all duration-200 flex items-center justify-center gap-2 cursor-pointer disabled:opacity-60"
                        wire:loading.attr="disabled"
                    >
                        <span wire:loading.remove>Login</span>
                        <span wire:loading class="flex items-center gap-2">
                            <svg class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span>Signing In...</span>
                        </span>
                    </button>
                </div>
            </form>

            <!-- Terms & Privacy Footer -->
            <div class="pt-6 text-center text-xs text-slate-400">
                By signing in, you agree to our 
                <a href="#" class="text-white hover:underline font-medium">Terms and Conditions</a> 
                &amp; 
                <a href="#" class="text-white hover:underline font-medium">Privacy Policy</a>
            </div>

        </div>
    </div>
</div>
