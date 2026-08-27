<div class="space-y-6">
    <!-- Top Welcome & Actions Header -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 bg-white p-6 rounded-xl border border-slate-200 shadow-xs">
        <div>
            <div class="flex items-center gap-2.5">
                <h1 class="text-2xl font-bold text-brand tracking-tight">Municipal Ayuda Command Center</h1>
                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-brand border border-emerald-200">
                    Live Session
                </span>
            </div>
            <p class="text-xs text-slate-500 mt-1">Real-time overview of municipal aid funds, active disbursements, and cross-system audit logs.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2.5">
            <!-- Loading Indicator Pill -->
            <div wire:loading.flex class="items-center gap-2 px-3 py-1.5 rounded-lg bg-emerald-50 border border-emerald-200 text-xs font-bold text-brand shadow-xs">
                <span class="relative flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-brand"></span>
                </span>
                <span>Refreshing Dashboard...</span>
            </div>

            <a href="{{ route('budget') }}" wire:navigate class="px-3.5 py-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-xs font-bold text-neutral-strong border border-slate-200 transition-colors flex items-center gap-1.5 cursor-pointer">
                <svg class="w-4 h-4 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                <span>New Project</span>
            </a>
            <a href="{{ route('distribution') }}" wire:navigate class="px-4 py-2 rounded-lg bg-accent hover:bg-amber-400 text-xs font-bold text-neutral-strong transition-colors shadow-xs flex items-center gap-1.5 cursor-pointer uppercase tracking-wide">
                <svg class="w-4 h-4 text-neutral-strong" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                <span>Launch POS Scanner</span>
            </a>
        </div>
    </div>

    <!-- SKELETON 4 KPI CARDS (ON WIRE:LOADING) -->
    <div wire:loading.grid class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        @for($i = 0; $i < 4; $i++)
            <div class="bg-surface border border-slate-200 rounded-xl p-5 shadow-xs animate-pulse space-y-3">
                <div class="flex items-center justify-between">
                    <div class="h-3 bg-slate-200 rounded w-32"></div>
                    <div class="w-8 h-8 rounded-lg bg-slate-100"></div>
                </div>
                <div class="h-7 bg-slate-200 rounded w-28 mt-2"></div>
                <div class="h-2.5 bg-slate-100 rounded w-44"></div>
                <div class="w-full bg-slate-100 rounded-full h-1.5 mt-4"></div>
            </div>
        @endfor
    </div>

    <!-- 4 Primary KPI Summary Cards (HIDDEN ON WIRE:LOADING) -->
    <div wire:loading.remove class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Gov Funds Card -->
        <div class="bg-surface border border-slate-200 rounded-xl p-5 shadow-xs relative overflow-hidden">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Government Funds (GGMS)</span>
                <div class="w-8 h-8 rounded-lg bg-emerald-50 text-brand flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                </div>
            </div>
            <div class="mt-3">
                <h3 class="text-2xl font-black text-neutral-strong font-mono">₱{{ number_format($govBalance, 2) }}</h3>
                <p class="text-xs text-slate-500 mt-1">Available of <span class="font-bold text-slate-700 font-mono">₱{{ number_format($govAllocated, 2) }}</span> allocated</p>
            </div>
            <div class="mt-4 w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                <div class="bg-brand h-1.5 rounded-full" style="width: {{ $govAllocated > 0 ? min(100, round(($govSpent / $govAllocated) * 100)) : 0 }}%"></div>
            </div>
        </div>

        <!-- Private Donations Card -->
        <div class="bg-surface border border-slate-200 rounded-xl p-5 shadow-xs relative overflow-hidden">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Private Donations Pool</span>
                <div class="w-8 h-8 rounded-lg bg-emerald-50 text-brand flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                </div>
            </div>
            <div class="mt-3">
                <h3 class="text-2xl font-black text-neutral-strong font-mono">₱{{ number_format($privateBalance, 2) }}</h3>
                <p class="text-xs text-slate-500 mt-1">Available of <span class="font-bold text-slate-700 font-mono">₱{{ number_format($privateAllocated, 2) }}</span> total</p>
            </div>
            <div class="mt-4 w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                <div class="bg-brand h-1.5 rounded-full" style="width: {{ $privateAllocated > 0 ? min(100, round(($privateSpent / $privateAllocated) * 100)) : 0 }}%"></div>
            </div>
        </div>

        <!-- Total Disbursed Value Card -->
        <div class="bg-surface border border-slate-200 rounded-xl p-5 shadow-xs relative overflow-hidden">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Ayuda Released</span>
                <div class="w-8 h-8 rounded-lg bg-emerald-50 text-brand flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <div class="mt-3">
                <h3 class="text-2xl font-black text-brand font-mono">₱{{ number_format($totalDisbursed, 2) }}</h3>
                <p class="text-xs text-slate-500 mt-1"><span class="text-neutral-strong font-bold">{{ number_format($totalClaims) }}</span> verified claims completed</p>
            </div>
            <div class="mt-4 flex items-center gap-1.5 text-[11px] text-brand font-bold">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>100% Audit-Verified</span>
            </div>
        </div>

        <!-- Beneficiaries & GGMS Hub Card -->
        <div class="bg-surface border border-slate-200 rounded-xl p-5 shadow-xs relative overflow-hidden">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Beneficiaries & GGMS Hub</span>
                <div class="w-8 h-8 rounded-lg bg-emerald-50 text-brand flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
            </div>
            <div class="mt-3">
                <h3 class="text-2xl font-black text-neutral-strong font-mono">{{ number_format($totalBeneficiaries) }}</h3>
                <p class="text-xs text-slate-500 mt-1"><span class="text-brand font-bold font-mono">{{ number_format($ggmsCount) }}</span> GGMS synced records</p>
            </div>
            <div class="mt-4 flex items-center gap-1.5 text-[11px] text-slate-600 font-medium">
                <span class="w-2 h-2 rounded-full bg-brand"></span>
                <span>{{ $barangayCount }} Barangays Active</span>
            </div>
        </div>
    </div>

    <!-- Active Distribution Programs Overview -->
    <div class="bg-surface border border-slate-200 rounded-xl p-6 shadow-xs">
        <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100">
            <div>
                <h3 class="text-base font-bold text-neutral-strong tracking-tight">Active Distribution Initiatives</h3>
                <p class="text-xs text-slate-500 mt-0.5">Live queue status across ongoing ayuda releases</p>
            </div>
            <a href="{{ route('distribution') }}" wire:navigate class="text-xs text-brand hover:underline font-bold flex items-center gap-1">
                <span>View Full Workspace</span>
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>

        <!-- SKELETON INITIATIVES (ON WIRE:LOADING) -->
        <div wire:loading.block class="space-y-3">
            @for($i = 0; $i < 3; $i++)
                <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4 animate-pulse">
                    <div class="space-y-2 flex-1">
                        <div class="flex items-center gap-2">
                            <div class="h-4 bg-slate-200 rounded w-24"></div>
                            <div class="h-4 bg-slate-200 rounded w-16"></div>
                            <div class="h-4 bg-slate-200 rounded w-48"></div>
                        </div>
                        <div class="h-3 bg-slate-100 rounded w-64"></div>
                    </div>
                    <div class="space-y-2 shrink-0">
                        <div class="h-3 bg-slate-200 rounded w-36 ml-auto"></div>
                        <div class="w-48 bg-slate-200 rounded-full h-2"></div>
                    </div>
                </div>
            @endfor
        </div>

        <!-- ACTUAL INITIATIVES (HIDDEN ON WIRE:LOADING) -->
        <div wire:loading.remove class="space-y-3">
            @forelse($activePrograms as $program)
                @php
                    $total = $program->pending_count + $program->released_count + $program->unreleased_count;
                    $claimedPct = $total > 0 ? round(($program->released_count / $total) * 100) : 0;
                @endphp
                <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2">
                            <span class="font-mono text-xs font-bold text-brand">{{ $program->program_code }}</span>
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-white text-slate-700 border border-slate-200">
                                {{ $program->benefit_type->value }}
                            </span>
                            <span class="text-xs text-slate-700 font-bold truncate">• {{ $program->title }}</span>
                        </div>
                        <p class="text-xs text-slate-500 mt-1">
                            Budget Cap: <span class="font-mono text-neutral-strong font-bold">₱{{ number_format($program->budget_cap, 2) }}</span>
                            | Disbursed: <span class="font-mono text-brand font-bold">₱{{ number_format($program->total_disbursed_amount, 2) }}</span>
                        </p>
                    </div>

                    <!-- 3-Bucket Status Badges & Mini Bar -->
                    <div class="flex flex-col sm:items-end gap-1.5 shrink-0">
                        <div class="flex items-center gap-2 text-xs font-medium">
                            <span class="text-brand font-bold"><strong class="font-mono">{{ $program->released_count }}</strong> Claimed</span>
                            <span class="text-slate-300">/</span>
                            <span class="text-slate-700"><strong class="font-mono">{{ $program->pending_count }}</strong> Queued</span>
                            <span class="text-slate-300">/</span>
                            <span class="text-error font-bold"><strong class="font-mono">{{ $program->unreleased_count }}</strong> Excluded</span>
                        </div>
                        <div class="w-48 bg-slate-200 rounded-full h-2 overflow-hidden flex">
                            <div class="bg-brand h-2" style="width: {{ $claimedPct }}%"></div>
                            <div class="bg-accent h-2" style="width: {{ $total > 0 ? round(($program->pending_count / $total) * 100) : 0 }}%"></div>
                            <div class="bg-error h-2" style="width: {{ $total > 0 ? round(($program->unreleased_count / $total) * 100) : 0 }}%"></div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-8 text-slate-500 text-sm">
                    No active distribution programs found. Create one in Budget Management.
                </div>
            @endforelse
        </div>
    </div>

    <!-- 2-Column Activity Stream -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Releases Feed -->
        <div class="bg-surface border border-slate-200 rounded-xl p-6 shadow-xs">
            <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100">
                <h3 class="text-sm font-bold text-neutral-strong tracking-tight">Recent Ayuda Claims</h3>
                <span class="text-xs text-slate-400 font-medium">Live feed</span>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse($recentClaims as $claim)
                    <div class="py-3 flex items-center justify-between gap-3 text-xs">
                        <div class="min-w-0">
                            <p class="font-bold text-neutral-strong truncate">{{ $claim->beneficiary?->full_name }}</p>
                            <p class="text-[11px] text-slate-500 truncate">
                                Brgy. {{ $claim->beneficiary?->barangay }} • {{ $claim->ayudaProgram?->title }}
                            </p>
                        </div>
                        <div class="text-right shrink-0">
                            <p class="font-mono font-bold text-brand">₱{{ number_format($claim->unit_amount, 2) }}</p>
                            <p class="text-[10px] text-slate-400">{{ $claim->claimed_at->diffForHumans() }}</p>
                        </div>
                    </div>
                @empty
                    <p class="py-6 text-center text-xs text-slate-400">No releases logged yet.</p>
                @endforelse
            </div>
        </div>

        <!-- Administrative Audit Trail -->
        <div class="bg-surface border border-slate-200 rounded-xl p-6 shadow-xs">
            <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100">
                <h3 class="text-sm font-bold text-neutral-strong tracking-tight">Audit Trail Stream</h3>
                <a href="{{ route('reports') }}" wire:navigate class="text-xs text-brand hover:underline font-semibold">Full logs</a>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse($recentActivities as $log)
                    <div class="py-3 flex items-start gap-3 text-xs">
                        <div class="w-6 h-6 rounded-md bg-emerald-50 text-brand flex items-center justify-center shrink-0 mt-0.5 font-bold text-[10px]">
                            {{ substr($log->action, 0, 1) }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-neutral-strong font-medium leading-snug">{{ $log->description }}</p>
                            <p class="text-[10px] text-slate-400 mt-0.5">
                                by <span class="text-slate-700 font-semibold">{{ $log->user?->name ?? 'System' }}</span> • {{ $log->created_at->diffForHumans() }}
                            </p>
                        </div>
                    </div>
                @empty
                    <p class="py-6 text-center text-xs text-slate-400">No audit activity recorded.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
