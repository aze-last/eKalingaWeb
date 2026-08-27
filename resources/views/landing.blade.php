<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>eKalinga+ | Municipal Ayuda & Assistance Management System — Sulop, Davao del Sur</title>
    <meta name="description" content="Official Ayuda Management & Fiscal Distribution System for the Municipality of Sulop. Real-time fund tracking, biometric QR verification, and immutable ledger governance.">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Vite CSS & JS -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        /* Custom Keyframe Animations & Reveal Transitions */
        @keyframes laser-scan {
            0% { top: 0%; opacity: 0.8; }
            50% { top: 92%; opacity: 1; }
            100% { top: 0%; opacity: 0.8; }
        }

        @keyframes float-gentle {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-8px) rotate(0.5deg); }
        }

        @keyframes pulse-glow {
            0%, 100% { opacity: 0.4; transform: scale(1); }
            50% { opacity: 0.8; transform: scale(1.04); }
        }

        @keyframes beam-flow {
            0% { transform: translateY(-100%); opacity: 0; }
            50% { opacity: 1; }
            100% { transform: translateY(200%); opacity: 0; }
        }

        .animate-laser {
            animation: laser-scan 2.4s ease-in-out infinite;
        }

        .animate-float {
            animation: float-gentle 6s ease-in-out infinite;
        }

        .animate-pulse-slow {
            animation: pulse-glow 4s ease-in-out infinite;
        }

        .animate-beam {
            animation: beam-flow 3s cubic-bezier(0.4, 0, 0.2, 1) infinite;
        }

        /* Scroll Reveal Utility Classes */
        .reveal-init {
            opacity: 0;
            transform: translateY(32px);
            transition: opacity 0.8s cubic-bezier(0.16, 1, 0.3, 1), transform 0.8s cubic-bezier(0.16, 1, 0.3, 1);
            will-change: opacity, transform;
        }

        .reveal-left {
            opacity: 0;
            transform: translateX(-32px);
            transition: opacity 0.8s cubic-bezier(0.16, 1, 0.3, 1), transform 0.8s cubic-bezier(0.16, 1, 0.3, 1);
            will-change: opacity, transform;
        }

        .reveal-right {
            opacity: 0;
            transform: translateX(32px);
            transition: opacity 0.8s cubic-bezier(0.16, 1, 0.3, 1), transform 0.8s cubic-bezier(0.16, 1, 0.3, 1);
            will-change: opacity, transform;
        }

        .reveal-scale {
            opacity: 0;
            transform: scale(0.94);
            transition: opacity 0.8s cubic-bezier(0.16, 1, 0.3, 1), transform 0.8s cubic-bezier(0.16, 1, 0.3, 1);
            will-change: opacity, transform;
        }

        .revealed {
            opacity: 1 !important;
            transform: translateY(0) translateX(0) scale(1) !important;
        }

        /* Ambient Grid Background */
        .bg-grid-pattern {
            background-size: 36px 36px;
            background-image: 
                linear-gradient(to right, rgba(15, 23, 42, 0.04) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(15, 23, 42, 0.04) 1px, transparent 1px);
        }

        .bg-dot-pattern {
            background-size: 24px 24px;
            background-image: radial-gradient(rgba(21, 128, 61, 0.12) 1.2px, transparent 1.2px);
        }

        /* Reduced Motion Accessibility */
        @media (prefers-reduced-motion: reduce) {
            .reveal-init, .reveal-left, .reveal-right, .reveal-scale {
                opacity: 1 !important;
                transform: none !important;
                transition: none !important;
            }
            .animate-laser, .animate-float, .animate-pulse-slow, .animate-beam {
                animation: none !important;
            }
        }
    </style>
</head>
<body class="bg-[#F8FAFC] text-slate-900 font-sans antialiased selection:bg-amber-400 selection:text-slate-900 overflow-x-hidden">

    <!-- Scroll Progress Indicator (Top Glowing Beam) -->
    <div id="scroll-progress-bar" class="fixed top-0 left-0 h-1 bg-gradient-to-r from-brand via-accent to-emerald-400 z-50 transition-all duration-75 w-0"></div>

    <!-- Navigation Bar -->
    <header class="sticky top-0 z-40 bg-white/85 backdrop-blur-md border-b border-slate-200/80 transition-shadow duration-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
            <!-- Brand Logo / Wordmark -->
            <a href="/" class="flex items-center gap-3.5 group">
                <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-brand to-emerald-800 text-white flex items-center justify-center shadow-md shadow-brand/20 group-hover:scale-105 transition-transform duration-200">
                    <svg class="w-6 h-6 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-xl font-black tracking-tight text-neutral-strong group-hover:text-brand transition-colors">eKalinga<span class="text-accent">+</span></span>
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100/70 text-brand border border-emerald-200/60 uppercase tracking-wide">Sulop LGU</span>
                    </div>
                    <p class="text-[11px] text-slate-500 font-medium">Ayuda & Assistance Management System</p>
                </div>
            </a>

            <!-- Desktop Nav Links -->
            <nav class="hidden md:flex items-center gap-8 text-xs font-bold text-slate-600">
                <a href="#features" class="hover:text-brand transition-colors">Core Modules</a>
                <a href="#how-it-works" class="hover:text-brand transition-colors">How It Works</a>
                <a href="#transparency" class="hover:text-brand transition-colors">Auditable Ledger</a>
                <a href="#trust" class="hover:text-brand transition-colors">LGU Governance</a>
            </nav>

            <!-- Action CTAs -->
            <div class="flex items-center gap-3">
                @auth
                    <a 
                        href="{{ route('dashboard') }}" 
                        class="px-4 py-2.5 rounded-lg bg-brand text-white text-xs font-bold hover:bg-emerald-800 transition-all shadow-xs hover:shadow-md flex items-center gap-2 group"
                    >
                        <span>Dashboard Workspace</span>
                        <svg class="w-4 h-4 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </a>
                @else
                    <a 
                        href="{{ route('login') }}" 
                        class="px-5 py-2.5 rounded-lg bg-accent hover:bg-amber-400 text-neutral-strong text-xs font-black transition-all shadow-sm hover:shadow-md hover:scale-[1.02] flex items-center gap-2"
                    >
                        <span>Portal Sign In</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                    </a>
                @endauth
            </div>
        </div>
    </header>

    <!-- HERO SECTION -->
    <section class="relative pt-12 pb-24 lg:pt-20 lg:pb-32 overflow-hidden bg-grid-pattern">
        <!-- Ambient Color Flares -->
        <div class="absolute top-1/4 -left-32 w-96 h-96 bg-brand/10 rounded-full blur-3xl pointer-events-none animate-pulse-slow"></div>
        <div class="absolute top-1/3 -right-32 w-96 h-96 bg-accent/15 rounded-full blur-3xl pointer-events-none animate-pulse-slow" style="animation-delay: 2s;"></div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-8 items-center">
                
                <!-- Hero Left: Typography & Direct Action -->
                <div class="lg:col-span-7 space-y-7 text-center lg:text-left">
                    <!-- Municipal Seal & System Badge -->
                    <div class="inline-flex items-center gap-2.5 px-3.5 py-1.5 rounded-full bg-white border border-slate-200/90 shadow-xs reveal-init" data-reveal>
                        <span class="w-2 h-2 rounded-full bg-brand animate-ping"></span>
                        <span class="text-[11px] font-bold text-slate-700 tracking-wide uppercase">
                            Municipality of Sulop, Davao del Sur
                        </span>
                        <span class="text-slate-300">•</span>
                        <span class="text-[11px] font-mono font-bold text-brand">AMS 2026</span>
                    </div>

                    <!-- Main Catchphrase -->
                    <h1 class="text-4xl sm:text-5xl lg:text-6xl font-black text-neutral-strong tracking-tight leading-[1.1] reveal-init" data-reveal data-delay="100">
                        Auditable, Transparent <br class="hidden sm:block">
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-brand via-emerald-600 to-teal-700">Ayuda Distribution</span>
                        <span class="block text-2xl sm:text-3xl lg:text-4xl font-extrabold text-slate-600 mt-2">for Every Sulop Citizen.</span>
                    </h1>

                    <!-- Descriptive Subtitle -->
                    <p class="text-base sm:text-lg text-slate-600 font-normal leading-relaxed max-w-2xl mx-auto lg:mx-0 reveal-init" data-reveal data-delay="200">
                        The official municipal platform integrating government allocations, philanthropic donations, live CRS citizen validation, and QR scanner releases into an immutable, double-entry audit ledger.
                    </p>

                    <!-- CTAs -->
                    <div class="flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-4 pt-2 reveal-init" data-reveal data-delay="300">
                        <a 
                            href="{{ route('login') }}" 
                            class="w-full sm:w-auto px-7 py-4 rounded-xl bg-accent hover:bg-amber-400 text-neutral-strong text-sm font-black transition-all shadow-md shadow-amber-500/20 hover:shadow-lg hover:scale-105 flex items-center justify-center gap-2.5 group cursor-pointer"
                        >
                            <span>Enter Operational Portal</span>
                            <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                        </a>

                        <a 
                            href="#features" 
                            class="w-full sm:w-auto px-6 py-4 rounded-xl bg-white hover:bg-slate-50 text-slate-700 text-sm font-bold border border-slate-200 transition-all shadow-xs hover:border-slate-300 flex items-center justify-center gap-2 cursor-pointer"
                        >
                            <svg class="w-4 h-4 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            <span>Explore 5 Modules</span>
                        </a>
                    </div>

                    <!-- Trust Stats Bar -->
                    <div class="pt-6 border-t border-slate-200/80 grid grid-cols-2 sm:grid-cols-4 gap-4 text-left reveal-init" data-reveal data-delay="400">
                        <div>
                            <span class="text-[11px] font-bold text-slate-600 uppercase tracking-wider block">Citizens Mapped</span>
                            <span class="text-xl sm:text-2xl font-black text-brand font-mono">40,584</span>
                            <span class="text-[10px] text-slate-500 block">CRS Live Registry</span>
                        </div>
                        <div>
                            <span class="text-[11px] font-bold text-slate-600 uppercase tracking-wider block">Barangays</span>
                            <span class="text-xl sm:text-2xl font-black text-neutral-strong font-mono">25/25</span>
                            <span class="text-[10px] text-slate-500 block">Complete Coverage</span>
                        </div>
                        <div>
                            <span class="text-[11px] font-bold text-slate-600 uppercase tracking-wider block">Aid Envelopes</span>
                            <span class="text-xl sm:text-2xl font-black text-amber-600 font-mono">₱9.06M</span>
                            <span class="text-[10px] text-slate-500 block">Gov + Private Funds</span>
                        </div>
                        <div>
                            <span class="text-[11px] font-bold text-slate-600 uppercase tracking-wider block">Audit Integrity</span>
                            <span class="text-xl sm:text-2xl font-black text-brand font-mono">100%</span>
                            <span class="text-[10px] text-slate-500 block">Append-Only Logs</span>
                        </div>
                    </div>
                </div>

                <!-- Hero Right: Interactive Kiosk & Scanner Visual Demonstration -->
                <div class="lg:col-span-5 relative reveal-scale" data-reveal data-delay="200">
                    <div class="relative mx-auto max-w-md bg-white rounded-2xl border border-slate-200 p-5 shadow-xl shadow-slate-200/50 animate-float">
                        
                        <!-- Top Terminal Header -->
                        <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full bg-emerald-500"></span>
                                <span class="w-3 h-3 rounded-full bg-amber-400"></span>
                                <span class="w-3 h-3 rounded-full bg-slate-300"></span>
                                <span class="text-[11px] font-mono font-bold text-slate-500 ml-2">SULOP-KIOSK-01</span>
                            </div>
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-brand border border-emerald-200">
                                Live Verification Mode
                            </span>
                        </div>

                        <!-- Scanner Interactive Mockup Card -->
                        <div class="relative bg-slate-900 rounded-xl p-5 text-white overflow-hidden shadow-inner">
                            <!-- Laser Scanning Animation Bar -->
                            <div class="absolute left-0 right-0 h-0.5 bg-gradient-to-r from-transparent via-amber-400 to-transparent shadow-[0_0_12px_#F59E0B] animate-laser z-20 pointer-events-none"></div>

                            <div class="flex items-center justify-between text-xs mb-3 text-slate-400 font-mono">
                                <span>CAMERA FEED [SCAN_ACTIVE]</span>
                                <span class="text-emerald-400 font-bold">MATCH: 100%</span>
                            </div>

                            <!-- Mock QR Code & Target Frame -->
                            <div class="relative w-36 h-36 mx-auto bg-slate-800/80 rounded-xl border border-slate-700 flex items-center justify-center p-3 my-2">
                                <div class="w-full h-full bg-white p-2 rounded-lg flex items-center justify-center">
                                    <!-- QR Code Graphic -->
                                    <svg class="w-full h-full text-slate-900" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M2 2h8v8H2V2zm2 2v4h4V4H4zm10-2h8v8h-8V2zm2 2v4h4V4h-4zM2 14h8v8H2v-8zm2 2v4h4v-4H4zm13-2h1v2h-1v-2zm-3 0h2v1h-2v-1zm4 3h1v3h-1v-3zm-2 2h2v1h-2v-1zm-2-2h1v1h-1v-1zm4-2h1v1h-1v-1zm-3 4h1v1h-1v-1zm-1-3h1v2h-1v-2zm-1 2h1v2h-1v-2zm-1-1h1v1h-1v-1zm2 3h2v1h-2v-1zm4-1h2v1h-2v-1z"/>
                                    </svg>
                                </div>
                                <div class="absolute -top-1 -left-1 w-3 h-3 border-t-2 border-l-2 border-accent"></div>
                                <div class="absolute -top-1 -right-1 w-3 h-3 border-t-2 border-r-2 border-accent"></div>
                                <div class="absolute -bottom-1 -left-1 w-3 h-3 border-b-2 border-l-2 border-accent"></div>
                                <div class="absolute -bottom-1 -right-1 w-3 h-3 border-b-2 border-r-2 border-accent"></div>
                            </div>

                            <!-- Live Verified Resident Card -->
                            <div class="mt-4 bg-slate-800/90 border border-slate-700 rounded-lg p-3 text-xs space-y-1.5">
                                <div class="flex items-center justify-between">
                                    <span class="text-[10px] font-bold uppercase text-slate-400">Verified Citizen</span>
                                    <span class="text-[10px] font-mono text-accent font-bold">CRN-0001</span>
                                </div>
                                <p class="font-bold text-white text-sm">Bienvinido M. Regidor</p>
                                <div class="flex items-center justify-between text-[11px] text-slate-400 pt-1 border-t border-slate-700/60">
                                    <span>Purok 5, Poblacion</span>
                                    <span class="text-emerald-400 font-bold">₱5,000.00 Authorized</span>
                                </div>
                            </div>
                        </div>

                        <!-- Instant Transaction Toast -->
                        <div class="mt-4 p-3 rounded-xl bg-emerald-50 border border-emerald-200/80 flex items-center justify-between text-xs">
                            <div class="flex items-center gap-2.5">
                                <div class="w-6 h-6 rounded-full bg-brand text-white flex items-center justify-center font-bold text-[10px]">
                                    ✓
                                </div>
                                <div>
                                    <p class="font-bold text-neutral-strong">Instant Claim Logged</p>
                                    <p class="text-[10px] text-slate-500 font-mono">HASH: SHA256-d7a8...91f2</p>
                                </div>
                            </div>
                            <span class="font-mono font-bold text-brand text-xs">CLM-2026-0001</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- TRUST & GOVERNANCE SIGNALS -->
    <section id="trust" class="py-12 bg-white border-y border-slate-200/80">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto space-y-2 mb-8 reveal-init" data-reveal>
                <h2 class="text-xs font-bold uppercase tracking-widest text-brand">Institutional Accountability</h2>
                <p class="text-xl sm:text-2xl font-black text-neutral-strong">Built to National Audit Standards & LGU Mandates</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Trust Pillar 1 -->
                <div class="p-6 rounded-xl bg-slate-50 border border-slate-200/80 space-y-3 hover:bg-emerald-50/40 transition-colors reveal-init" data-reveal data-delay="100">
                    <div class="w-10 h-10 rounded-lg bg-emerald-100 text-brand flex items-center justify-center font-bold">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    </div>
                    <h3 class="text-sm font-bold text-neutral-strong">Anti-Duplicate Fraud Engine</h3>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Deterministic checking across Civil Registry IDs and household members prevents double claims within the same program window.
                    </p>
                </div>

                <!-- Trust Pillar 2 -->
                <div class="p-6 rounded-xl bg-slate-50 border border-slate-200/80 space-y-3 hover:bg-emerald-50/40 transition-colors reveal-init" data-reveal data-delay="200">
                    <div class="w-10 h-10 rounded-lg bg-amber-100 text-amber-800 flex items-center justify-center font-bold">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <h3 class="text-sm font-bold text-neutral-strong">Append-Only Immutable Ledger</h3>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Every allocation and release generates a permanent ledger event. Zero physical deletions ensure full Commission on Audit (COA) compliance.
                    </p>
                </div>

                <!-- Trust Pillar 3 -->
                <div class="p-6 rounded-xl bg-slate-50 border border-slate-200/80 space-y-3 hover:bg-emerald-50/40 transition-colors reveal-init" data-reveal data-delay="300">
                    <div class="w-10 h-10 rounded-lg bg-emerald-100 text-brand flex items-center justify-center font-bold">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                    </div>
                    <h3 class="text-sm font-bold text-neutral-strong">National GGMS Interoperability</h3>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Direct synchronization with national grant management databases (`u518908950_ggms`) ensures unified reporting and cross-agency auditing.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- WHAT IT DOES — 5 REAL MODULES -->
    <section id="features" class="py-20 lg:py-28 bg-[#F1F5F9] relative">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto space-y-3 mb-16 reveal-init" data-reveal>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-50 border border-emerald-200 text-brand text-xs font-bold uppercase">
                    5 Operational Modules
                </div>
                <h2 class="text-3xl sm:text-4xl font-black text-neutral-strong tracking-tight">
                    Engineered for Precision Municipal Aid Operations
                </h2>
                <p class="text-sm sm:text-base text-slate-600">
                    Explore the production modules powering the Municipality of Sulop's relief, emergency grants, and cash-for-work distribution.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                
                <!-- Module 1: Budget Management -->
                <div class="bg-white rounded-2xl border border-slate-200/90 p-7 shadow-xs hover:shadow-xl hover:-translate-y-1.5 transition-all duration-300 group reveal-init" data-reveal data-delay="100">
                    <div class="w-12 h-12 rounded-xl bg-emerald-50 text-brand border border-emerald-200/60 flex items-center justify-center mb-5 group-hover:scale-110 transition-transform">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="text-lg font-bold text-neutral-strong group-hover:text-brand transition-colors">Budget Management</h3>
                        <span class="text-[10px] font-mono font-bold text-brand bg-emerald-50 px-2 py-0.5 rounded">₱9.06M Total</span>
                    </div>
                    <p class="text-xs text-slate-600 leading-relaxed mb-4">
                        Dual tracking for Government funding envelopes and private philanthropic cash donations with real-time balance calculations and strict cap enforcement.
                    </p>
                    <ul class="text-xs space-y-1.5 text-slate-500 font-medium border-t border-slate-100 pt-3">
                        <li class="flex items-center gap-2"><span class="text-brand">✓</span> Earmark project caps & unit amounts</li>
                        <li class="flex items-center gap-2"><span class="text-brand">✓</span> Immutable double-entry ledger stream</li>
                    </ul>
                </div>

                <!-- Module 2: Project Distribution -->
                <div class="bg-white rounded-2xl border border-slate-200/90 p-7 shadow-xs hover:shadow-xl hover:-translate-y-1.5 transition-all duration-300 group reveal-init" data-reveal data-delay="200">
                    <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-700 border border-amber-200/60 flex items-center justify-center mb-5 group-hover:scale-110 transition-transform">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    </div>
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="text-lg font-bold text-neutral-strong group-hover:text-brand transition-colors">Project Distribution</h3>
                        <span class="text-[10px] font-mono font-bold text-amber-800 bg-amber-50 px-2 py-0.5 rounded">QR & Manual</span>
                    </div>
                    <p class="text-xs text-slate-600 leading-relaxed mb-4">
                        Batch pre-enrollment with 3-bucket queue (Pending, Released, Unreleased), live kiosk screen broadcaster, and instant release audio alerts.
                    </p>
                    <ul class="text-xs space-y-1.5 text-slate-500 font-medium border-t border-slate-100 pt-3">
                        <li class="flex items-center gap-2"><span class="text-accent">✓</span> Multi-lane live preview screen for queues</li>
                        <li class="flex items-center gap-2"><span class="text-accent">✓</span> Sub-second QR verification barcode engine</li>
                    </ul>
                </div>

                <!-- Module 3: Civil Registry Masterlist -->
                <div class="bg-white rounded-2xl border border-slate-200/90 p-7 shadow-xs hover:shadow-xl hover:-translate-y-1.5 transition-all duration-300 group reveal-init" data-reveal data-delay="300">
                    <div class="w-12 h-12 rounded-xl bg-emerald-50 text-brand border border-emerald-200/60 flex items-center justify-center mb-5 group-hover:scale-110 transition-transform">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    </div>
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="text-lg font-bold text-neutral-strong group-hover:text-brand transition-colors">Masterlist (CRS Live)</h3>
                        <span class="text-[10px] font-mono font-bold text-brand bg-emerald-50 px-2 py-0.5 rounded">40.5K Records</span>
                    </div>
                    <p class="text-xs text-slate-600 leading-relaxed mb-4">
                        Direct read-only query into the municipal Civil Registry System (`val_beneficiaries`) with full individual timeline of acquired benefits.
                    </p>
                    <ul class="text-xs space-y-1.5 text-slate-500 font-medium border-t border-slate-100 pt-3">
                        <li class="flex items-center gap-2"><span class="text-brand">✓</span> 25-barangay filtered citizen profiling</li>
                        <li class="flex items-center gap-2"><span class="text-brand">✓</span> Full historical claims ledger per citizen</li>
                    </ul>
                </div>

                <!-- Module 4: GGMS Transactions -->
                <div class="bg-white rounded-2xl border border-slate-200/90 p-7 shadow-xs hover:shadow-xl hover:-translate-y-1.5 transition-all duration-300 group reveal-init" data-reveal data-delay="100">
                    <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-700 border border-amber-200/60 flex items-center justify-center mb-5 group-hover:scale-110 transition-transform">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                    </div>
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="text-lg font-bold text-neutral-strong group-hover:text-brand transition-colors">GGMS Transactions</h3>
                        <span class="text-[10px] font-mono font-bold text-amber-800 bg-amber-50 px-2 py-0.5 rounded">88 Synced</span>
                    </div>
                    <p class="text-xs text-slate-600 leading-relaxed mb-4">
                        Consolidated transaction tracking synchronizing local Ayuda releases with the central Government Grant Management System.
                    </p>
                    <ul class="text-xs space-y-1.5 text-slate-500 font-medium border-t border-slate-100 pt-3">
                        <li class="flex items-center gap-2"><span class="text-accent">✓</span> Automatic sync status & payload inspector</li>
                        <li class="flex items-center gap-2"><span class="text-accent">✓</span> Real-time grant reconciliation</li>
                    </ul>
                </div>

                <!-- Module 5: Reports & Audits -->
                <div class="bg-white rounded-2xl border border-slate-200/90 p-7 shadow-xs hover:shadow-xl hover:-translate-y-1.5 transition-all duration-300 group reveal-init" data-reveal data-delay="200">
                    <div class="w-12 h-12 rounded-xl bg-emerald-50 text-brand border border-emerald-200/60 flex items-center justify-center mb-5 group-hover:scale-110 transition-transform">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="text-lg font-bold text-neutral-strong group-hover:text-brand transition-colors">Reports & Audits</h3>
                        <span class="text-[10px] font-mono font-bold text-brand bg-emerald-50 px-2 py-0.5 rounded">PDF Liquidation</span>
                    </div>
                    <p class="text-xs text-slate-600 leading-relaxed mb-4">
                        Generate official liquidation documents with cryptographic SHA-256 snapshot hashes, COA-ready breakdowns, and one-click PDF export.
                    </p>
                    <ul class="text-xs space-y-1.5 text-slate-500 font-medium border-t border-slate-100 pt-3">
                        <li class="flex items-center gap-2"><span class="text-brand">✓</span> SHA-256 verification seals on exports</li>
                        <li class="flex items-center gap-2"><span class="text-brand">✓</span> Executive financial summary cards</li>
                    </ul>
                </div>

                <!-- Mission Card: Sulop Municipal Vision -->
                <div class="bg-gradient-to-br from-brand to-emerald-900 rounded-2xl p-7 text-white shadow-lg flex flex-col justify-between reveal-init" data-reveal data-delay="300">
                    <div class="space-y-3">
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-white/20 text-accent uppercase tracking-wider">
                            Executive Mandate
                        </span>
                        <h3 class="text-xl font-bold text-white leading-snug">Empowering Sulop through Transparent Governance</h3>
                        <p class="text-xs text-emerald-100/80 leading-relaxed">
                            Ensuring that every peso allocated reaches the rightful indigent families with verifiable audit receipts.
                        </p>
                    </div>

                    <div class="pt-6 mt-4 border-t border-emerald-700/60 flex items-center justify-between">
                        <span class="text-[11px] font-mono text-emerald-200">Office of the Mayor</span>
                        <a href="{{ route('login') }}" class="text-xs font-bold text-accent hover:underline flex items-center gap-1">
                            Access Portal &rarr;
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- HOW IT WORKS — SCROLL-LINKED ANIMATED PIPELINE -->
    <section id="how-it-works" class="py-20 lg:py-28 bg-white relative overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative">
            <div class="text-center max-w-3xl mx-auto space-y-3 mb-20 reveal-init" data-reveal>
                <span class="text-xs font-bold uppercase tracking-widest text-brand">End-to-End Workflow</span>
                <h2 class="text-3xl sm:text-4xl font-black text-neutral-strong tracking-tight">How Assistance Flows Through eKalinga+</h2>
                <p class="text-sm sm:text-base text-slate-600">
                    From capital intake to verified citizen hand-off in 4 immutable steps.
                </p>
            </div>

            <!-- Pipeline Timeline Container -->
            <div class="relative">
                <!-- Connecting Center Beam (Desktop) -->
                <div class="hidden lg:block absolute top-1/2 left-0 right-0 h-1 bg-slate-200 -translate-y-1/2 z-0">
                    <div id="pipeline-beam" class="h-full bg-gradient-to-r from-brand via-accent to-emerald-500 w-0 transition-all duration-300"></div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 relative z-10">
                    
                    <!-- Step 1 -->
                    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs relative space-y-4 hover:border-brand transition-colors reveal-init" data-reveal data-delay="100">
                        <div class="w-12 h-12 rounded-xl bg-brand text-white font-black text-lg flex items-center justify-center shadow-md shadow-brand/20">
                            01
                        </div>
                        <h3 class="text-base font-bold text-neutral-strong">Fund Intake & Earmark</h3>
                        <p class="text-xs text-slate-600 leading-relaxed">
                            Government office budgets (GGMS) and private donations are recorded as discrete funding envelopes with spending caps.
                        </p>
                        <div class="text-[10px] font-mono text-slate-500 bg-slate-50 p-2 rounded border border-slate-100">
                            Status: <span class="text-brand font-bold">Ledger Logged</span>
                        </div>
                    </div>

                    <!-- Step 2 -->
                    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs relative space-y-4 hover:border-brand transition-colors reveal-init" data-reveal data-delay="200">
                        <div class="w-12 h-12 rounded-xl bg-brand text-white font-black text-lg flex items-center justify-center shadow-md shadow-brand/20">
                            02
                        </div>
                        <h3 class="text-base font-bold text-neutral-strong">CRS Cross-Matching</h3>
                        <p class="text-xs text-slate-600 leading-relaxed">
                            Citizens are matched live against the Civil Registry System (`val_beneficiaries`) to verify residency and prevent duplicate claims.
                        </p>
                        <div class="text-[10px] font-mono text-slate-500 bg-slate-50 p-2 rounded border border-slate-100">
                            Lookup: <span class="text-brand font-bold">40,584 Verified</span>
                        </div>
                    </div>

                    <!-- Step 3 -->
                    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs relative space-y-4 hover:border-brand transition-colors reveal-init" data-reveal data-delay="300">
                        <div class="w-12 h-12 rounded-xl bg-accent text-neutral-strong font-black text-lg flex items-center justify-center shadow-md shadow-amber-500/20">
                            03
                        </div>
                        <h3 class="text-base font-bold text-neutral-strong">QR Scanner Release</h3>
                        <p class="text-xs text-slate-600 leading-relaxed">
                            Field officers scan citizen QR or National ID at distribution hubs. The system authorizes and registers the disbursement in real time.
                        </p>
                        <div class="text-[10px] font-mono text-slate-500 bg-slate-50 p-2 rounded border border-slate-100">
                            Verification: <span class="text-amber-600 font-bold">Instant Scan</span>
                        </div>
                    </div>

                    <!-- Step 4 -->
                    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs relative space-y-4 hover:border-brand transition-colors reveal-init" data-reveal data-delay="400">
                        <div class="w-12 h-12 rounded-xl bg-brand text-white font-black text-lg flex items-center justify-center shadow-md shadow-brand/20">
                            04
                        </div>
                        <h3 class="text-base font-bold text-neutral-strong">Audit Write & GGMS Sync</h3>
                        <p class="text-xs text-slate-600 leading-relaxed">
                            An immutable audit record is committed to the local ledger, and the transaction is automatically synced with the national GGMS registry.
                        </p>
                        <div class="text-[10px] font-mono text-slate-500 bg-slate-50 p-2 rounded border border-slate-100">
                            Integrity: <span class="text-brand font-bold">SHA-256 Sealed</span>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </section>

    <!-- TRANSPARENCY & AUDIT LEDGER HIGHLIGHT -->
    <section id="transparency" class="py-20 lg:py-28 bg-[#F8FAFC] border-t border-slate-200/80">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
                
                <div class="lg:col-span-6 space-y-6 reveal-left" data-reveal>
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-50 border border-emerald-200 text-brand text-xs font-bold uppercase">
                        Auditable By Design
                    </div>
                    <h2 class="text-3xl sm:text-4xl font-black text-neutral-strong tracking-tight">
                        Zero Deletions. Complete Fiscal Traceability.
                    </h2>
                    <p class="text-sm sm:text-base text-slate-600 leading-relaxed">
                        eKalinga+ implements a strict append-only architectural pattern. Funding movements cannot be overwritten or deleted. Municipal auditors and regional oversight bodies can generate deterministic snapshots verified against cryptographic hashes.
                    </p>

                    <div class="space-y-4 pt-2">
                        <div class="flex items-start gap-3.5">
                            <div class="w-6 h-6 rounded-full bg-emerald-100 text-brand flex items-center justify-center font-bold text-xs shrink-0 mt-0.5">
                                ✓
                            </div>
                            <div>
                                <h4 class="text-sm font-bold text-neutral-strong">Cryptographic Snapshot Hashing</h4>
                                <p class="text-xs text-slate-500">Every PDF report includes an indelible SHA-256 checksum certifying its exact state at generation time.</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3.5">
                            <div class="w-6 h-6 rounded-full bg-emerald-100 text-brand flex items-center justify-center font-bold text-xs shrink-0 mt-0.5">
                                ✓
                            </div>
                            <div>
                                <h4 class="text-sm font-bold text-neutral-strong">Officer Accountability Logging</h4>
                                <p class="text-xs text-slate-500">Every disbursement records the issuing officer ID, exact timestamp, and terminal station identifier.</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3.5">
                            <div class="w-6 h-6 rounded-full bg-emerald-100 text-brand flex items-center justify-center font-bold text-xs shrink-0 mt-0.5">
                                ✓
                            </div>
                            <div>
                                <h4 class="text-sm font-bold text-neutral-strong">COA Liquidation Ready</h4>
                                <p class="text-xs text-slate-500">Export structured liquidation summaries with breakdown per funding envelope and recipient roster.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Ledger Visual Preview -->
                <div class="lg:col-span-6 reveal-right" data-reveal>
                    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xl space-y-4 font-mono text-xs">
                        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                            <span class="text-slate-400 uppercase font-bold text-[10px]">Live Ledger Stream</span>
                            <span class="px-2 py-0.5 rounded text-[10px] bg-emerald-50 text-brand font-bold">IMMUTABLE_LOG</span>
                        </div>

                        <div class="space-y-2.5">
                            <div class="p-3 rounded-lg bg-slate-50 border border-slate-100 space-y-1">
                                <div class="flex items-center justify-between text-[11px]">
                                    <span class="font-bold text-neutral-strong font-sans">OVP Private Donation Earmark</span>
                                    <span class="text-brand font-bold">+₱1,000,000.00</span>
                                </div>
                                <div class="flex items-center justify-between text-[10px] text-slate-400">
                                    <span>REF: DON-2026-0001</span>
                                    <span>Aug 19, 2026</span>
                                </div>
                            </div>

                            <div class="p-3 rounded-lg bg-slate-50 border border-slate-100 space-y-1">
                                <div class="flex items-center justify-between text-[11px]">
                                    <span class="font-bold text-neutral-strong font-sans">Ayuda Office Allocation (GGMS)</span>
                                    <span class="text-brand font-bold">+₱300,000.00</span>
                                </div>
                                <div class="flex items-center justify-between text-[10px] text-slate-400">
                                    <span>REF: OFF-2026-0006</span>
                                    <span>Aug 22, 2026</span>
                                </div>
                            </div>

                            <div class="p-3 rounded-lg bg-slate-50 border border-slate-100 space-y-1">
                                <div class="flex items-center justify-between text-[11px]">
                                    <span class="font-bold text-neutral-strong font-sans">Indigent Cash Release #0001</span>
                                    <span class="text-slate-700 font-bold">-₱5,000.00</span>
                                </div>
                                <div class="flex items-center justify-between text-[10px] text-slate-400">
                                    <span>REC: Bienvinido Regidor (CRN-0001)</span>
                                    <span>CLAIMED_OK</span>
                                </div>
                            </div>
                        </div>

                        <div class="p-3 rounded-lg bg-emerald-50/70 border border-emerald-200/60 text-emerald-800 text-[11px] font-sans flex items-center justify-between">
                            <span>Balance Integrity Verified</span>
                            <span class="font-mono font-bold text-brand">MATCH: 100%</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- CALL TO ACTION BANNER -->
    <section class="py-16 lg:py-20 bg-gradient-to-br from-brand via-emerald-800 to-slate-900 text-white relative overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative text-center space-y-6">
            <h2 class="text-3xl sm:text-4xl font-black text-white tracking-tight">
                Ready to Access the Sulop Ayuda Management System?
            </h2>
            <p class="text-emerald-100/90 text-sm sm:text-base max-w-2xl mx-auto">
                Authorized municipal staff, disbursing officers, and system administrators can log in below with their assigned municipal credentials.
            </p>
            <div class="pt-2 flex flex-col sm:flex-row items-center justify-center gap-4">
                <a 
                    href="{{ route('login') }}" 
                    class="px-8 py-4 rounded-xl bg-accent hover:bg-amber-400 text-neutral-strong text-sm font-black transition-all shadow-lg hover:scale-105 flex items-center gap-2 cursor-pointer"
                >
                    <span>Sign In to eKalinga+ Portal</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </a>
            </div>
        </div>
    </section>

    <!-- FOOTER -->
    <footer class="bg-white border-t border-slate-200 py-12 text-xs text-slate-500">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row items-center justify-between gap-6 pb-8 border-b border-slate-100">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-brand text-white flex items-center justify-center font-bold text-xs">
                        eK+
                    </div>
                    <div>
                        <span class="font-bold text-neutral-strong text-sm">eKalinga+</span>
                        <p class="text-[11px] text-slate-400">Municipality of Sulop, Province of Davao del Sur</p>
                    </div>
                </div>

                <div class="flex items-center gap-6 font-medium text-slate-600">
                    <a href="#features" class="hover:text-brand transition-colors">Modules</a>
                    <a href="#how-it-works" class="hover:text-brand transition-colors">Workflow</a>
                    <a href="#transparency" class="hover:text-brand transition-colors">Audit Trail</a>
                    <a href="{{ route('login') }}" class="text-brand font-bold hover:underline">Staff Login</a>
                </div>
            </div>

            <div class="pt-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-[11px]">
                <p>&copy; {{ date('Y') }} Municipality of Sulop. All rights reserved. System developed for municipal assistance distribution governance.</p>
                <div class="flex items-center gap-4 font-mono text-[10px] text-slate-400">
                    <span>HOSTINGER_REMOTE_ACTIVE</span>
                    <span>•</span>
                    <span>CRS_VAL_BENEFICIARIES</span>
                </div>
            </div>
        </div>
    </footer>

    <!-- INTERACTION & SCROLL-TRIGGERED MOTION ENGINE -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // 1. Scroll Progress Bar Update
            const progressBar = document.getElementById('scroll-progress-bar');
            const pipelineBeam = document.getElementById('pipeline-beam');

            const handleScroll = () => {
                const scrollTop = window.scrollY;
                const docHeight = document.documentElement.scrollHeight - window.innerHeight;
                const scrollPercent = docHeight > 0 ? (scrollTop / docHeight) * 100 : 0;
                
                if (progressBar) {
                    progressBar.style.width = scrollPercent + '%';
                }

                // Update How It Works Pipeline Connecting Beam
                if (pipelineBeam) {
                    const howItWorksSection = document.getElementById('how-it-works');
                    if (howItWorksSection) {
                        const rect = howItWorksSection.getBoundingClientRect();
                        const sectionHeight = rect.height;
                        const progress = Math.max(0, Math.min(100, ((window.innerHeight - rect.top) / (sectionHeight + window.innerHeight)) * 100 * 1.3));
                        pipelineBeam.style.width = progress + '%';
                    }
                }
            };

            window.addEventListener('scroll', handleScroll, { passive: true });
            handleScroll();

            // 2. IntersectionObserver for Staggered Scroll-Triggered Reveals
            const revealElements = document.querySelectorAll('[data-reveal]');

            if ('IntersectionObserver' in window) {
                const observer = new IntersectionObserver((entries, obs) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            const delay = entry.target.getAttribute('data-delay') || 0;
                            setTimeout(() => {
                                entry.target.classList.add('revealed');
                            }, delay);
                            obs.unobserve(entry.target);
                        }
                    });
                }, {
                    root: null,
                    threshold: 0.12,
                    rootMargin: '0px 0px -40px 0px'
                });

                revealElements.forEach(el => observer.observe(el));
            } else {
                // Fallback for older browsers
                revealElements.forEach(el => el.classList.add('revealed'));
            }
        });
    </script>
</body>
</html>
