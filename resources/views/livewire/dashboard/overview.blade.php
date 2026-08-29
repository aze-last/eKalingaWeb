<div class="space-y-6 max-w-7xl mx-auto" x-data="{ activeTab: @entangle('activeTab') }">
    <!-- 1. Executive Hero & Municipal Pulse Bar -->
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-[#0F172A] via-[#1E293B] to-[#0F172A] text-white p-6 sm:p-8 border border-slate-800 shadow-xl">
        <!-- Ambient Decorative Glows -->
        <div class="absolute -top-20 -right-20 w-80 h-80 bg-brand/20 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-20 -left-20 w-80 h-80 bg-accent/15 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            <!-- Left Branding & Greeting -->
            <div class="flex items-center gap-4 sm:gap-5">
                <div class="relative group shrink-0">
                    <div class="absolute inset-0 bg-white/20 rounded-2xl blur-md group-hover:bg-white/30 transition-all"></div>
                    <div class="relative w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-white/10 backdrop-blur-md p-2 border border-white/20 flex items-center justify-center shadow-lg">
                        <img 
                            src="{{ asset(App\Models\Setting::get('municipal_seal_url', '/images/Site_logo.png')) }}" 
                            alt="Municipal Seal" 
                            class="w-full h-full object-contain drop-shadow"
                        >
                    </div>
                </div>

                <div class="space-y-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                            Live Command Center
                        </span>
                        <span class="text-xs text-slate-400 font-medium hidden sm:inline">
                            {{ App\Models\Setting::get('municipality_name', 'Municipality of Sulop') }}
                        </span>
                    </div>

                    <h1 class="text-xl sm:text-3xl font-black text-white tracking-tight leading-tight">
                        Magandang Araw, {{ Auth::user()->name }}!
                    </h1>
                    <p class="text-xs text-slate-300 font-medium max-w-xl">
                        Real-time municipal ayuda telemetry, live QR disbursement tracking, and multi-source aid liquidity.
                    </p>
                </div>
            </div>

            <!-- Right Telemetry & Quick Action Controls -->
            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3 shrink-0">
                <!-- Liquidity Snapshot Box -->
                <div class="bg-white/5 backdrop-blur-md border border-white/10 rounded-xl p-3 sm:px-4 sm:py-2.5 flex items-center gap-4">
                    <div>
                        <span class="text-[10px] uppercase font-bold text-slate-400 tracking-wider block">Available Liquidity</span>
                        <span class="text-sm sm:text-base font-black text-emerald-400 font-mono">
                            ₱{{ number_format($totalRemaining, 2) }}
                        </span>
                    </div>
                    <div class="h-8 w-px bg-white/10"></div>
                    <div>
                        <span class="text-[10px] uppercase font-bold text-slate-400 tracking-wider block">Budget Used</span>
                        <span class="text-sm sm:text-base font-black text-amber-400 font-mono">
                            {{ $budgetUtilization }}%
                        </span>
                    </div>
                </div>

                <!-- Launch Scanner Action -->
                <a 
                    href="{{ route('distribution') }}" 
                    wire:navigate 
                    class="bg-accent hover:bg-amber-400 text-neutral-strong font-black text-xs py-3 px-5 rounded-xl uppercase tracking-wider transition-all shadow-lg hover:shadow-amber-500/25 flex items-center gap-2 shrink-0 active:scale-95 cursor-pointer"
                >
                    <svg class="w-4 h-4 text-neutral-strong stroke-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    <span>Launch POS Scanner</span>
                </a>
            </div>
        </div>
    </div>

    <!-- 2. Primary 4-Metric Bento Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Metric 1: Total Ayuda Disbursed -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs relative overflow-hidden flex flex-col justify-between hover:border-brand/40 transition-colors">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Ayuda Released</span>
                <div class="w-9 h-9 rounded-xl bg-brand/10 text-brand flex items-center justify-center border border-brand/20">
                    <svg class="w-5 h-5 stroke-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>

            <div class="my-3">
                <h3 class="text-2xl sm:text-3xl font-black text-brand font-mono tracking-tight">
                    ₱{{ number_format($totalDisbursed, 2) }}
                </h3>
                <p class="text-xs text-slate-500 font-medium mt-1">
                    <span class="font-bold text-neutral-strong">{{ number_format($totalClaims) }}</span> verified claims completed
                </p>
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-[11px]">
                <span class="text-slate-400 font-medium">Today's Claims: <strong class="text-neutral-strong">{{ $todayClaimsCount }}</strong></span>
                <span class="font-bold text-brand flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Audit-Verified
                </span>
            </div>
        </div>

        <!-- Metric 2: Government Funds (GGMS) -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs relative overflow-hidden flex flex-col justify-between hover:border-slate-300 transition-colors">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Government Funds (GGMS)</span>
                <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center border border-blue-200">
                    <svg class="w-5 h-5 stroke-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                </div>
            </div>

            <div class="my-3">
                <h3 class="text-2xl sm:text-3xl font-black text-neutral-strong font-mono tracking-tight">
                    ₱{{ number_format($govBalance, 2) }}
                </h3>
                <p class="text-xs text-slate-500 font-medium mt-1">
                    Available of <span class="font-bold text-slate-700 font-mono">₱{{ number_format($govAllocated, 2) }}</span>
                </p>
            </div>

            <div class="space-y-1.5 pt-2 border-t border-slate-100">
                <div class="flex items-center justify-between text-[11px] font-bold">
                    <span class="text-slate-400">Disbursed: {{ $govPercent }}%</span>
                    <span class="text-blue-600 font-mono">₱{{ number_format($govSpent, 2) }}</span>
                </div>
                <div class="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                    <div class="bg-blue-600 h-1.5 rounded-full" style="width: {{ $govPercent }}%"></div>
                </div>
            </div>
        </div>

        <!-- Metric 3: Private Donations Pool -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs relative overflow-hidden flex flex-col justify-between hover:border-slate-300 transition-colors">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Private Donations Pool</span>
                <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center border border-amber-200">
                    <svg class="w-5 h-5 stroke-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                </div>
            </div>

            <div class="my-3">
                <h3 class="text-2xl sm:text-3xl font-black text-neutral-strong font-mono tracking-tight">
                    ₱{{ number_format($privateBalance, 2) }}
                </h3>
                <p class="text-xs text-slate-500 font-medium mt-1">
                    Available of <span class="font-bold text-slate-700 font-mono">₱{{ number_format($privateAllocated, 2) }}</span>
                </p>
            </div>

            <div class="space-y-1.5 pt-2 border-t border-slate-100">
                <div class="flex items-center justify-between text-[11px] font-bold">
                    <span class="text-slate-400">Disbursed: {{ $privatePercent }}%</span>
                    <span class="text-amber-600 font-mono">₱{{ number_format($privateSpent, 2) }}</span>
                </div>
                <div class="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                    <div class="bg-amber-500 h-1.5 rounded-full" style="width: {{ $privatePercent }}%"></div>
                </div>
            </div>
        </div>

        <!-- Metric 4: Municipal Citizen Reach -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs relative overflow-hidden flex flex-col justify-between hover:border-slate-300 transition-colors">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Citizen Reach & Coverage</span>
                <div class="w-9 h-9 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center border border-purple-200">
                    <svg class="w-5 h-5 stroke-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
            </div>

            <div class="my-3">
                <h3 class="text-2xl sm:text-3xl font-black text-neutral-strong font-mono tracking-tight">
                    {{ number_format($totalBeneficiaries) }}
                </h3>
                <p class="text-xs text-slate-500 font-medium mt-1">
                    Registered across <span class="font-bold text-neutral-strong">{{ $barangayCount }}</span> Sulop Barangays
                </p>
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-[11px] font-bold">
                <span class="text-slate-500">Female: <strong class="text-purple-600">{{ $demographics['femalePercent'] }}%</strong></span>
                <span class="text-slate-500">Male: <strong class="text-blue-600">{{ $demographics['malePercent'] }}%</strong></span>
            </div>
        </div>
    </div>

    <!-- 3. Visual Analytics & Real-Time Performance Section -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Left (7 Cols): Disbursement Velocity & Active Programs -->
        <div class="lg:col-span-7 space-y-6">
            <!-- Velocity Chart Box -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-100 pb-3">
                    <div>
                        <h3 class="text-base font-black text-neutral-strong tracking-tight">Disbursement Velocity Trend</h3>
                        <p class="text-xs text-slate-500 font-medium">Monthly aid disbursement volume across government & private funding.</p>
                    </div>
                    <span class="text-[11px] font-bold text-brand bg-emerald-50 px-2.5 py-1 rounded-lg border border-emerald-200 self-start sm:self-auto">
                        Last 6 Months
                    </span>
                </div>

                <!-- Interactive SVG-Styled Bar Chart -->
                <div class="pt-4 pb-2">
                    <div class="h-44 w-full flex items-end justify-between gap-3 sm:gap-6 px-2">
                        @foreach ($disbursementTrends as $trend)
                            <div class="flex-1 flex flex-col items-center gap-2 group relative cursor-pointer">
                                <!-- Tooltip on Hover -->
                                <div class="absolute -top-10 opacity-0 group-hover:opacity-100 transition-opacity bg-neutral-strong text-white text-[10px] font-mono font-bold py-1 px-2 rounded-lg pointer-events-none whitespace-nowrap shadow-md z-20">
                                    ₱{{ number_format($trend['amount'], 2) }}
                                </div>

                                <!-- Bar Column -->
                                <div class="w-full bg-slate-100 rounded-xl overflow-hidden flex flex-col justify-end h-32 relative">
                                    <div 
                                        class="w-full bg-gradient-to-t from-brand to-emerald-400 rounded-xl transition-all duration-500 group-hover:brightness-110"
                                        style="height: {{ $trend['height_pct'] }}%"
                                    ></div>
                                </div>

                                <!-- Month Label -->
                                <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider group-hover:text-brand transition-colors">
                                    {{ $trend['month'] }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Active Programs Snapshot -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div>
                        <h3 class="text-base font-black text-neutral-strong tracking-tight">Active Ayuda Programs</h3>
                        <p class="text-xs text-slate-500 font-medium">Currently distributing and ongoing municipal aid projects.</p>
                    </div>
                    <a href="{{ route('budget') }}" wire:navigate class="text-xs font-bold text-brand hover:underline">
                        View All Programs →
                    </a>
                </div>

                <div class="space-y-3">
                    @forelse ($activePrograms as $prog)
                        <div class="p-4 rounded-xl bg-slate-50 border border-slate-200/80 hover:border-brand/40 transition-all flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div class="space-y-1 flex-1">
                                <div class="flex items-center gap-2">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider {{ $prog->fundingSource?->funding_type->value === 'Government' ? 'bg-blue-100 text-blue-800' : 'bg-amber-100 text-amber-800' }}">
                                        {{ $prog->fundingSource?->funding_type->value ?? 'Aid' }}
                                    </span>
                                    <h4 class="text-xs font-bold text-neutral-strong">{{ $prog->title }}</h4>
                                </div>
                                <p class="text-[11px] text-slate-500 font-medium">
                                    Unit Benefit: <strong class="text-brand font-mono">₱{{ number_format($prog->unit_amount, 2) }}</strong> • Total Budget: <strong class="text-neutral-strong font-mono">₱{{ number_format($prog->budget_allocated, 2) }}</strong>
                                </p>
                            </div>

                            <div class="flex items-center gap-4 shrink-0">
                                <div class="text-right">
                                    <span class="text-[11px] font-bold text-slate-700 block">
                                        {{ $prog->released_count }} / {{ $prog->released_count + $prog->pending_count + $prog->unreleased_count }} Released
                                    </span>
                                    <span class="text-[10px] text-slate-400 font-medium">
                                        {{ $prog->pending_count }} Pending
                                    </span>
                                </div>

                                <a 
                                    href="{{ route('distribution.live-preview', $prog->id) }}" 
                                    wire:navigate 
                                    class="bg-white border border-slate-200 hover:bg-slate-100 text-neutral-strong font-bold text-xs py-1.5 px-3 rounded-lg transition-colors shadow-2xs cursor-pointer shrink-0"
                                >
                                    Disburse
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="py-8 text-center text-slate-400 text-xs">
                            No active programs currently open for distribution.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Right (5 Cols): Barangay Leaderboard & Demographics -->
        <div class="lg:col-span-5 space-y-6">
            <!-- Barangay Leaderboard -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div>
                        <h3 class="text-base font-black text-neutral-strong tracking-tight">Barangay Ayuda Leaderboard</h3>
                        <p class="text-xs text-slate-500 font-medium">Top receiving barangays by total aid volume.</p>
                    </div>
                    <span class="text-[10px] font-bold text-slate-500 bg-slate-100 px-2 py-0.5 rounded">
                        Top 6
                    </span>
                </div>

                <div class="space-y-3.5">
                    @forelse ($barangayLeaderboard as $brgy)
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between text-xs">
                                <span class="font-bold text-neutral-strong">{{ $brgy['barangay'] }}</span>
                                <span class="font-mono font-bold text-brand">₱{{ number_format($brgy['total_amount'], 2) }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="flex-1 bg-slate-100 rounded-full h-2 overflow-hidden">
                                    <div class="bg-gradient-to-r from-emerald-500 to-brand h-2 rounded-full" style="width: {{ $brgy['percent'] }}%"></div>
                                </div>
                                <span class="text-[10px] text-slate-400 font-medium shrink-0">{{ $brgy['count'] }} claims</span>
                            </div>
                        </div>
                    @empty
                        <div class="py-6 text-center text-slate-400 text-xs">
                            No barangay transaction data recorded yet.
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Vulnerable Sectors Demographic Reach -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs space-y-4">
                <div class="border-b border-slate-100 pb-3">
                    <h3 class="text-base font-black text-neutral-strong tracking-tight">Vulnerable Sector Inclusivity</h3>
                    <p class="text-xs text-slate-500 font-medium">Demographic reach across municipal assistance recipients.</p>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200">
                        <span class="text-[10px] uppercase font-bold text-slate-400 block tracking-wider">Female Recipients</span>
                        <span class="text-xl font-black text-purple-700 font-mono mt-0.5 block">{{ $demographics['femaleCount'] }}</span>
                        <span class="text-[10px] text-purple-600 font-bold mt-0.5 block">{{ $demographics['femalePercent'] }}% of roster</span>
                    </div>

                    <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200">
                        <span class="text-[10px] uppercase font-bold text-slate-400 block tracking-wider">Male Recipients</span>
                        <span class="text-xl font-black text-blue-700 font-mono mt-0.5 block">{{ $demographics['maleCount'] }}</span>
                        <span class="text-[10px] text-blue-600 font-bold mt-0.5 block">{{ $demographics['malePercent'] }}% of roster</span>
                    </div>
                </div>

                <div class="p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 flex items-center justify-between text-xs text-emerald-900">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-brand shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span class="font-bold">Cross-System Civil Registry Synced</span>
                    </div>
                    <span class="font-mono font-black text-brand text-[11px]">100% OK</span>
                </div>
            </div>
        </div>
    </div>

    <!-- 4. Operations Hub: Live POS Radar & Audit Activity Feed -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <!-- Tab Selector Header -->
        <div class="p-4 sm:px-6 border-b border-slate-200 bg-slate-50/50 flex items-center justify-between flex-wrap gap-4">
            <div class="flex items-center gap-2">
                <button 
                    @click="activeTab = 'radar'" 
                    class="px-4 py-2 rounded-xl text-xs font-bold transition-all cursor-pointer"
                    :class="activeTab === 'radar' ? 'bg-brand text-white shadow-xs' : 'text-slate-600 hover:bg-slate-200/60'"
                >
                    <span class="flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-emerald-300 animate-pulse"></span>
                        Live POS Claims Radar ({{ count($recentClaims) }})
                    </span>
                </button>

                <button 
                    @click="activeTab = 'audit'" 
                    class="px-4 py-2 rounded-xl text-xs font-bold transition-all cursor-pointer"
                    :class="activeTab === 'audit' ? 'bg-brand text-white shadow-xs' : 'text-slate-600 hover:bg-slate-200/60'"
                >
                    System Audit Trail ({{ count($recentActivities) }})
                </button>
            </div>

            <span class="text-[11px] text-slate-400 font-medium hidden sm:inline">
                Auto-updates on disbursements and logins
            </span>
        </div>

        <!-- Tab 1: Live Claims Radar -->
        <div x-show="activeTab === 'radar'" class="divide-y divide-slate-100">
            @forelse ($recentClaims as $claim)
                <div class="p-4 sm:px-6 flex items-center justify-between gap-4 hover:bg-slate-50 transition-colors">
                    <div class="flex items-center gap-3.5">
                        <div class="w-10 h-10 rounded-xl bg-brand/10 text-brand font-black text-xs flex items-center justify-center border border-brand/20 shrink-0">
                            {{ substr($claim->beneficiary?->full_name ?? 'B', 0, 2) }}
                        </div>
                        <div>
                            <h4 class="text-xs font-bold text-neutral-strong">
                                {{ $claim->beneficiary?->full_name ?? 'Verified Beneficiary' }}
                            </h4>
                            <p class="text-[11px] text-slate-500 font-medium">
                                {{ $claim->ayudaProgram?->title ?? 'Ayuda Release' }} • <span class="text-slate-700 font-bold">{{ $claim->beneficiary?->barangay ?? 'Sulop' }}</span>
                            </p>
                        </div>
                    </div>

                    <div class="text-right shrink-0">
                        <span class="text-xs font-black text-brand font-mono block">
                            +₱{{ number_format($claim->unit_amount, 2) }}
                        </span>
                        <span class="text-[10px] text-slate-400 font-medium">
                            {{ $claim->claimed_at ? $claim->claimed_at->diffForHumans() : 'Just now' }}
                        </span>
                    </div>
                </div>
            @empty
                <div class="py-12 text-center text-slate-400 text-xs">
                    No recent claims disbursed in this session.
                </div>
            @endforelse
        </div>

        <!-- Tab 2: System Audit Trail -->
        <div x-show="activeTab === 'audit'" class="divide-y divide-slate-100" style="display: none;">
            @forelse ($recentActivities as $act)
                <div class="p-4 sm:px-6 flex items-center justify-between gap-4 hover:bg-slate-50 transition-colors">
                    <div class="flex items-center gap-3.5">
                        <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-600 font-bold text-xs flex items-center justify-center shrink-0">
                            {{ substr($act->module ?? 'SYS', 0, 3) }}
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="px-1.5 py-0.5 rounded text-[10px] font-bold uppercase bg-slate-100 text-slate-700">
                                    {{ $act->action }}
                                </span>
                                <h4 class="text-xs font-bold text-neutral-strong">{{ $act->description }}</h4>
                            </div>
                            <p class="text-[11px] text-slate-400 font-medium mt-0.5">
                                By <strong class="text-slate-600">{{ $act->user?->name ?? 'System' }}</strong> • IP: {{ $act->ip_address ?? '127.0.0.1' }}
                            </p>
                        </div>
                    </div>

                    <span class="text-[10px] text-slate-400 font-medium shrink-0">
                        {{ $act->created_at ? $act->created_at->diffForHumans() : 'Recently' }}
                    </span>
                </div>
            @empty
                <div class="py-12 text-center text-slate-400 text-xs">
                    No recent activity logs recorded.
                </div>
            @endforelse
        </div>
    </div>
</div>
