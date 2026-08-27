<div class="min-h-screen w-full flex flex-col lg:flex-row bg-[#080E1E] text-white relative overflow-hidden" x-data="{ showPassword: false }">
    <!-- Left Hero & Branding Section -->
    <div class="lg:w-7/12 relative bg-gradient-to-br from-[#070D1C] via-[#0B1426] to-[#091524] p-8 sm:p-12 lg:p-16 flex flex-col justify-between overflow-hidden">
        <!-- Curved Organic SVG Divider (Desktop) -->
        <div class="hidden lg:block absolute right-0 top-0 bottom-0 w-32 pointer-events-none z-10 translate-x-1/2">
            <svg class="h-full w-full text-[#080E1E] fill-current" viewBox="0 0 100 100" preserveAspectRatio="none">
                <path d="M0 0 C 40 30, 40 70, 0 100 L100 100 L100 0 Z"></path>
            </svg>
        </div>

        <!-- Subtle Ambient Background Glow -->
        <div class="absolute -top-24 -left-24 w-96 h-96 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-24 -left-24 w-96 h-96 bg-blue-500/5 rounded-full blur-3xl pointer-events-none"></div>

        <!-- Top Left Brand Badge -->
        <div class="relative z-20 flex items-center gap-3">
            <div class="w-8 h-8 rounded-full bg-white/10 p-1 flex items-center justify-center backdrop-blur-xs border border-white/20">
                <img src="{{ asset($municipalLogo) }}" alt="Seal" class="w-full h-full object-contain">
            </div>
            <div>
                <span class="text-sm font-bold tracking-tight text-white block">Sulop Ayuda</span>
                <span class="text-[10px] text-slate-400 font-mono block uppercase">eKalinga+ Management</span>
            </div>
        </div>

        <!-- Center Large Municipal Seal Emblem -->
        <div class="relative z-20 my-12 lg:my-0 flex flex-col items-center justify-center">
            <div class="relative group max-w-[280px] sm:max-w-[340px] lg:max-w-[380px] w-full">
                <!-- Glowing Aura -->
                <div class="absolute inset-0 bg-emerald-500/15 rounded-full blur-2xl group-hover:bg-emerald-500/25 transition-all duration-500"></div>
                <img 
                    src="{{ asset($municipalLogo) }}" 
                    alt="Municipality of Sulop Official Seal" 
                    class="relative w-full h-auto object-contain drop-shadow-2xl brightness-110 contrast-105"
                >
            </div>
        </div>

        <!-- Bottom Tagline & Headline -->
        <div class="relative z-20 max-w-xl">
            <h1 class="text-3xl sm:text-4xl lg:text-5xl font-black text-white tracking-tight leading-tight">
                Connect to your municipal ayuda dashboard and sign in.
            </h1>
            <p class="mt-4 text-sm sm:text-base text-slate-400 leading-relaxed font-normal">
                Manage all your municipal assistance, budget allocations, and ayuda disbursements in one place.
            </p>
        </div>
    </div>

    <!-- Right Dedicated Sign-In Section -->
    <div class="lg:w-5/12 bg-[#080E1E] p-8 sm:p-12 lg:p-16 flex flex-col justify-center relative z-20">
        <div class="w-full max-w-md mx-auto space-y-6">
            
            <!-- Header with Title and Return Indicator -->
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-3xl font-extrabold text-white tracking-tight">Sign In</h2>
                    <p class="text-xs text-slate-400 mt-1">Admin Login • Authorized Personnel Only</p>
                </div>
                <div class="text-emerald-400">
                    <svg class="w-6 h-6 transform rotate-45" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                    </svg>
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

            <!-- Quick Demo Accounts (Local/Testing Environment Only) -->
            @if($isLocalEnvironment)
                <div class="pt-4 border-t border-slate-800/80">
                    <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400 mb-2.5 text-center">
                        Development Quick Access
                    </p>
                    <div class="grid grid-cols-2 gap-2">
                        <button 
                            type="button" 
                            wire:click="quickLogin('SuperAdmin')"
                            class="bg-[#0E1626] hover:bg-[#152035] border border-slate-800 rounded-xl p-2.5 text-left transition-colors cursor-pointer"
                        >
                            <p class="text-xs font-bold text-white">Super Admin</p>
                            <p class="text-[10px] text-slate-400 font-mono">superadmin / password</p>
                        </button>
                        <button 
                            type="button" 
                            wire:click="quickLogin('Admin')"
                            class="bg-[#0E1626] hover:bg-[#152035] border border-slate-800 rounded-xl p-2.5 text-left transition-colors cursor-pointer"
                        >
                            <p class="text-xs font-bold text-white">Admin (MSWDO)</p>
                            <p class="text-[10px] text-slate-400 font-mono">admin / password</p>
                        </button>
                    </div>
                </div>
            @endif

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
