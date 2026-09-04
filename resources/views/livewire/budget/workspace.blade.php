<div class="space-y-6">
    <!-- Header & Action Ribbon -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 bg-white p-6 rounded-xl border border-slate-200 shadow-xs">
        <div>
            <div class="flex items-center gap-2.5">
                <h1 class="text-2xl font-bold text-brand tracking-tight">Budget & Funding Management</h1>
                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-brand border border-emerald-200" title="Synchronized live ledger and verified civil registry">
                    Live Ledger & Registry
                </span>
            </div>
            <p class="text-xs text-slate-500 mt-1">Unified fiscal tracking, 1:1 funding allocation, household-audited candidate enrollment, and immutable ledger governance.</p>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-wrap items-center gap-2.5">
            <button 
                wire:click="openDonationModal"
                class="px-3.5 py-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-xs font-bold text-neutral-strong border border-slate-200 transition-colors flex items-center gap-1.5 cursor-pointer shadow-xs"
                title="Record a private cash or in-kind goods donation into the municipal pool"
            >
                <svg class="w-4 h-4 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Record Donation</span>
            </button>
            <button 
                wire:click="openProjectModal"
                class="px-4 py-2 rounded-lg bg-accent hover:bg-amber-400 text-xs font-bold text-neutral-strong transition-colors shadow-xs flex items-center gap-1.5 cursor-pointer uppercase tracking-wide"
                title="Launch 4-step wizard to create an operational ayuda distribution project with 1:1 funding"
            >
                <svg class="w-4 h-4 text-neutral-strong" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                <span>Create Ayuda Project</span>
            </button>
        </div>
    </div>

    <!-- Navigation Sub-Tabs -->
    <div class="flex border-b border-slate-200 gap-6 text-sm font-semibold">
        <button 
            wire:click="$set('activeTab', 'overview')"
            title="View high-level fiscal KPI summary, funding sources, and active projects"
            class="pb-3 flex items-center gap-2 transition-colors cursor-pointer border-b-2 {{ $activeTab === 'overview' ? 'border-brand text-brand font-bold' : 'border-transparent text-slate-500 hover:text-neutral-strong' }}"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
            <span>Financial Overview</span>
        </button>

        <button 
            wire:click="$set('activeTab', 'registry')"
            title="Search and inspect all government allocations, GGMS projects, private donations, and local programs"
            class="pb-3 flex items-center gap-2 transition-colors cursor-pointer border-b-2 {{ $activeTab === 'registry' ? 'border-brand text-brand font-bold' : 'border-transparent text-slate-500 hover:text-neutral-strong' }}"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
            <span>Unified Project & Funding Registry</span>
        </button>

        <button 
            wire:click="$set('activeTab', 'ledger')"
            title="Inspect immutable double-entry transaction log and historical audit trail"
            class="pb-3 flex items-center gap-2 transition-colors cursor-pointer border-b-2 {{ $activeTab === 'ledger' ? 'border-brand text-brand font-bold' : 'border-transparent text-slate-500 hover:text-neutral-strong' }}"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            <span>Immutable Ledger</span>
        </button>

        <button 
            wire:click="$set('activeTab', 'ggms_sync')"
            title="View and synchronize mirrored central government GGMS fund allocations"
            class="pb-3 flex items-center gap-2 transition-colors cursor-pointer border-b-2 {{ $activeTab === 'ggms_sync' ? 'border-brand text-brand font-bold' : 'border-transparent text-slate-500 hover:text-neutral-strong' }}"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
            <span>Government GGMS Mirror</span>
        </button>
    </div>

    <!-- TAB 1: OVERVIEW & ACTIVE PROJECTS -->
    @if($activeTab === 'overview')
        <!-- Financial KPI Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-surface border border-slate-200 rounded-xl p-5 shadow-xs">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Total Municipal Ayuda Pool</span>
                <h3 class="text-2xl font-black text-neutral-strong tracking-tight font-mono mt-2">₱{{ number_format($totalAllocated, 2) }}</h3>
                <p class="text-xs text-slate-500 mt-1">Disbursed: <span class="font-mono font-bold text-brand">₱{{ number_format($totalDisbursed, 2) }}</span></p>
            </div>
            <div class="bg-surface border border-slate-200 rounded-xl p-5 shadow-xs">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Government (GGMS) Available</span>
                <h3 class="text-2xl font-black text-neutral-strong tracking-tight font-mono mt-2">₱{{ number_format($govBalance, 2) }}</h3>
                <p class="text-xs text-slate-500 mt-1">Total grant: <span class="font-mono font-bold text-slate-700">₱{{ number_format($govAllocated, 2) }}</span></p>
            </div>
            <div class="bg-surface border border-slate-200 rounded-xl p-5 shadow-xs">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Private Donations Available</span>
                <h3 class="text-2xl font-black text-neutral-strong tracking-tight font-mono mt-2">₱{{ number_format($privateBalance, 2) }}</h3>
                <p class="text-xs text-slate-500 mt-1">Total received: <span class="font-mono font-bold text-slate-700">₱{{ number_format($privateAllocated, 2) }}</span></p>
            </div>
            <div class="bg-surface border border-slate-200 rounded-xl p-5 shadow-xs">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Active Operational Projects</span>
                <h3 class="text-2xl font-black text-brand tracking-tight font-mono mt-2">{{ $programs->where('status', App\Enums\ProgramStatus::Active)->count() }}</h3>
                <p class="text-xs text-slate-500 mt-1"><span class="font-bold text-neutral-strong">{{ $programs->count() }}</span> total projects on record</p>
            </div>
        </div>

        <!-- Funding Sources Master List -->
        <div class="bg-surface border border-slate-200 rounded-xl p-6 shadow-xs">
            <h3 class="text-base font-bold text-neutral-strong tracking-tight mb-4">Funding Sources & Allocations</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-500 font-bold uppercase tracking-wider border-y border-slate-100">
                        <tr>
                            <th class="px-4 py-3 rounded-l-lg">Source Code</th>
                            <th class="px-4 py-3">Title & Envelopes</th>
                            <th class="px-4 py-3">Type</th>
                            <th class="px-4 py-3 text-right">Allocated</th>
                            <th class="px-4 py-3 text-right">Disbursed</th>
                            <th class="px-4 py-3 text-right">Available Balance</th>
                            <th class="px-4 py-3 text-center rounded-r-lg">Projects Linked</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($fundingSources as $source)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-4 py-3.5 font-mono font-bold text-brand">{{ $source->source_code }}</td>
                                <td class="px-4 py-3.5">
                                    <p class="font-bold text-neutral-strong">{{ $source->title }}</p>
                                    <p class="text-[11px] text-slate-500">{{ $source->office }} • FY {{ $source->fiscal_year }}</p>
                                </td>
                                <td class="px-4 py-3.5">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $source->funding_type->value === 'Government' ? 'bg-emerald-50 text-brand border border-emerald-200' : 'bg-amber-50 text-warning border border-amber-200' }}">
                                        {{ $source->funding_type->value }}
                                    </span>
                                </td>
                                <td class="px-4 py-3.5 text-right font-mono font-bold text-neutral-strong">₱{{ number_format($source->allocated_amount, 2) }}</td>
                                <td class="px-4 py-3.5 text-right font-mono text-slate-600">₱{{ number_format($source->spent_amount, 2) }}</td>
                                <td class="px-4 py-3.5 text-right font-mono font-bold text-brand">₱{{ number_format($source->remaining_balance, 2) }}</td>
                                <td class="px-4 py-3.5 text-center font-mono font-bold text-slate-700">{{ $source->ayuda_programs_count }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Operational Ayuda Projects List -->
        <div class="bg-surface border border-slate-200 rounded-xl p-6 shadow-xs">
            <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100">
                <div>
                    <h3 class="text-base font-bold text-neutral-strong tracking-tight">Ayuda Projects (Operational Distributions)</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Projects created in Budget and disbursed via POS Scanner</p>
                </div>
                <button 
                    wire:click="openProjectModal" 
                    class="text-xs text-brand hover:underline font-bold cursor-pointer"
                    title="Launch wizard to create a new ayuda distribution project"
                >
                    + New Project
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-500 font-bold uppercase tracking-wider border-y border-slate-100">
                        <tr>
                            <th class="px-4 py-3 rounded-l-lg">Project Code</th>
                            <th class="px-4 py-3">Title & Benefit</th>
                            <th class="px-4 py-3">Linked Fund</th>
                            <th class="px-4 py-3 text-right">Budget Cap</th>
                            <th class="px-4 py-3 text-right">Disbursed</th>
                            <th class="px-4 py-3 text-right">Unspent Earmark</th>
                            <th class="px-4 py-3 text-center">Status</th>
                            <th class="px-4 py-3 text-center rounded-r-lg">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($programs as $program)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-4 py-3.5 font-mono font-bold text-brand">{{ $program->program_code }}</td>
                                <td class="px-4 py-3.5">
                                    <p class="font-bold text-neutral-strong">{{ $program->title }}</p>
                                    <p class="text-[11px] text-slate-500">
                                        {{ $program->benefit_type->value }} (₱{{ number_format($program->unit_amount, 2) }}) • Target: {{ $program->target_beneficiaries }}
                                    </p>
                                </td>
                                <td class="px-4 py-3.5 font-mono text-slate-600">{{ $program->fundingSource?->source_code }}</td>
                                <td class="px-4 py-3.5 text-right font-mono font-bold text-neutral-strong">₱{{ number_format($program->budget_cap, 2) }}</td>
                                <td class="px-4 py-3.5 text-right font-mono text-brand">₱{{ number_format($program->disbursed_amount, 2) }}</td>
                                <td class="px-4 py-3.5 text-right font-mono font-bold text-slate-700">₱{{ number_format($program->remaining_balance, 2) }}</td>
                                <td class="px-4 py-3.5 text-center">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $program->status->value === 'Active' ? 'bg-emerald-50 text-brand border border-emerald-200' : 'bg-slate-100 text-slate-600' }}">
                                        {{ $program->status->value }}
                                    </span>
                                </td>
                                <td class="px-4 py-3.5 text-center">
                                    @if($program->status->value === 'Active' && $program->remaining_balance > 0)
                                        <button 
                                            wire:click="confirmReallocation({{ $program->id }})"
                                            class="px-2.5 py-1 rounded bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200 text-[11px] font-bold cursor-pointer transition-colors"
                                            title="Reclaim remaining unspent earmark funds back to parent funding source and close project"
                                        >
                                            Reallocate
                                        </button>
                                    @else
                                        <span class="text-slate-400 font-mono text-[11px]">-</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <!-- TAB 2: UNIFIED PROJECT & FUNDING REGISTRY -->
    @if($activeTab === 'registry')
        <div class="bg-surface border border-slate-200 rounded-xl p-6 shadow-xs space-y-5">
            <!-- Header & Search -->
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 pb-4 border-b border-slate-100">
                <div>
                    <h3 class="text-base font-bold text-neutral-strong tracking-tight">Unified Project & Funding Registry</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Centralized read-only browser covering all government allocations, GGMS sub-projects, private donations, and local distribution programs.</p>
                </div>

                <div class="w-full sm:w-72">
                    <input 
                        type="text" 
                        wire:model.live.debounce.250ms="registrySearch" 
                        placeholder="Search code, title, donor..." 
                        class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-xs text-neutral-strong focus:ring-1 focus:ring-brand"
                        title="Filter registry records by code, title, office, or donor particulars"
                    >
                </div>
            </div>

            <!-- Segmented Category Filter Bar -->
            <div class="flex flex-wrap items-center gap-1.5 p-1 bg-slate-100 rounded-lg max-w-fit text-xs font-bold">
                <button 
                    wire:click="$set('registryCategory', 'ALL')"
                    class="px-3 py-1.5 rounded-md transition-all cursor-pointer {{ $registryCategory === 'ALL' ? 'bg-white text-brand shadow-xs' : 'text-slate-600 hover:text-neutral-strong' }}"
                    title="Show all registered government funds, GGMS projects, private donations, and local programs"
                >
                    All Categories ({{ $registryItems->count() }})
                </button>
                <button 
                    wire:click="$set('registryCategory', 'GOV_FUND')"
                    class="px-3 py-1.5 rounded-md transition-all cursor-pointer {{ $registryCategory === 'GOV_FUND' ? 'bg-white text-brand shadow-xs' : 'text-slate-600 hover:text-neutral-strong' }}"
                    title="Filter view to government appropriations and funding sources"
                >
                    Government Funds
                </button>
                <button 
                    wire:click="$set('registryCategory', 'GGMS_PROJECT')"
                    class="px-3 py-1.5 rounded-md transition-all cursor-pointer {{ $registryCategory === 'GGMS_PROJECT' ? 'bg-white text-brand shadow-xs' : 'text-slate-600 hover:text-neutral-strong' }}"
                    title="Filter view to centralized GGMS sub-projects"
                >
                    GGMS Projects
                </button>
                <button 
                    wire:click="$set('registryCategory', 'PRIVATE_DONATION')"
                    class="px-3 py-1.5 rounded-md transition-all cursor-pointer {{ $registryCategory === 'PRIVATE_DONATION' ? 'bg-white text-brand shadow-xs' : 'text-slate-600 hover:text-neutral-strong' }}"
                    title="Filter view to private cash and in-kind donor contributions"
                >
                    Private Donations
                </button>
                <button 
                    wire:click="$set('registryCategory', 'DISTRIBUTION_PROJECT')"
                    class="px-3 py-1.5 rounded-md transition-all cursor-pointer {{ $registryCategory === 'DISTRIBUTION_PROJECT' ? 'bg-white text-brand shadow-xs' : 'text-slate-600 hover:text-neutral-strong' }}"
                    title="Filter view to municipal operational ayuda distribution programs"
                >
                    Distribution Projects
                </button>
            </div>

            <!-- Unified Data Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-500 font-bold uppercase tracking-wider border-y border-slate-100">
                        <tr>
                            <th class="px-4 py-3 rounded-l-lg">Category</th>
                            <th class="px-4 py-3">Title & Particulars</th>
                            <th class="px-4 py-3 text-right">Allocated / Cap</th>
                            <th class="px-4 py-3 text-right">Disbursed / Spent</th>
                            <th class="px-4 py-3 text-right">Available Balance</th>
                            <th class="px-4 py-3 text-center">Status</th>
                            <th class="px-4 py-3 text-right rounded-r-lg">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($registryItems as $item)
                            <tr 
                                wire:click="openRegistryInspector('{{ $item['category'] }}', '{{ $item['id'] }}')"
                                class="hover:bg-slate-50/80 transition-colors cursor-pointer group"
                                title="Click to inspect full details and allocations for {{ $item['title'] }}"
                            >
                                <td class="px-4 py-3.5">
                                    @php
                                        $catColor = match($item['category']) {
                                            'Government Fund' => 'bg-emerald-50 text-brand border-emerald-200',
                                             'GGMS Project' => 'bg-blue-50 text-blue-700 border-blue-200',
                                            'Private Donation' => 'bg-amber-50 text-amber-800 border-amber-200',
                                            'Distribution Project' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                                            default => 'bg-slate-100 text-slate-700 border-slate-200',
                                        };
                                    @endphp
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold border {{ $catColor }}">
                                        {{ $item['category'] }}
                                    </span>
                                </td>
                                <td class="px-4 py-3.5">
                                    <p class="font-bold text-neutral-strong group-hover:text-brand">{{ $item['title'] }}</p>
                                    <p class="text-[11px] text-slate-500"><span class="font-mono font-bold text-brand">{{ $item['code'] }}</span> • {{ $item['detail_summary'] }}</p>
                                </td>
                                <td class="px-4 py-3.5 text-right font-mono font-bold text-neutral-strong">
                                    ₱{{ number_format($item['allocated'], 2) }}
                                </td>
                                <td class="px-4 py-3.5 text-right font-mono text-slate-600">
                                    ₱{{ number_format($item['spent'], 2) }}
                                </td>
                                <td class="px-4 py-3.5 text-right font-mono font-bold text-brand">
                                    ₱{{ number_format($item['balance'], 2) }}
                                </td>
                                <td class="px-4 py-3.5 text-center">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700">
                                        {{ $item['status'] }}
                                    </span>
                                </td>
                                <td class="px-4 py-3.5 text-right">
                                    <button 
                                        class="text-xs text-brand font-bold group-hover:underline flex items-center justify-end gap-1 ml-auto"
                                        title="Inspect detailed specifications and financial record"
                                    >
                                        <span>Inspect</span>
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-12 text-center text-slate-400 font-medium">
                                    No records matching filter criteria in unified registry.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <!-- TAB 3: IMMUTABLE LEDGER -->
    @if($activeTab === 'ledger')
        <div class="bg-surface border border-slate-200 rounded-xl p-6 shadow-xs space-y-4">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 pb-3 border-b border-slate-100">
                <div>
                    <h3 class="text-base font-bold text-neutral-strong tracking-tight">Immutable Audit Ledger Stream</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Double-entry audit log of all initial allocations, donations, earmarks, releases, and reallocations.</p>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <input 
                        type="text" 
                        wire:model.live.debounce.250ms="ledgerSearch" 
                        placeholder="Search ref, notes..." 
                        class="bg-white border border-slate-300 rounded-lg px-3 py-1.5 text-xs text-neutral-strong"
                        title="Filter ledger entries by reference code, notes, or program"
                    >
                    <select 
                        wire:model.live="ledgerFilterType" 
                        class="bg-white border border-slate-300 rounded-lg px-3 py-1.5 text-xs text-neutral-strong cursor-pointer"
                        title="Filter audit log by transaction event type"
                    >
                        <option value="ALL">All Event Types</option>
                        <option value="ALLOCATION">Allocation</option>
                        <option value="DONATION">Donation</option>
                        <option value="EARMARK">Earmark</option>
                        <option value="RELEASE">Release</option>
                        <option value="REALLOCATION">Reallocation</option>
                    </select>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-500 font-bold uppercase tracking-wider border-y border-slate-100">
                        <tr>
                            <th class="px-4 py-3 rounded-l-lg">Reference Code</th>
                            <th class="px-4 py-3">Event Type</th>
                            <th class="px-4 py-3">Source / Project</th>
                            <th class="px-4 py-3 text-right">Amount (₱)</th>
                            <th class="px-4 py-3 text-right">New Balance (₱)</th>
                            <th class="px-4 py-3">Logged By</th>
                            <th class="px-4 py-3 text-right rounded-r-lg">Timestamp</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($ledgerEntries as $entry)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-4 py-3.5 font-mono font-bold text-brand">{{ $entry->reference_code }}</td>
                                <td class="px-4 py-3.5">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold 
                                        @if($entry->entry_type->value === 'ALLOCATION') bg-emerald-50 text-brand border border-emerald-200
                                        @elseif($entry->entry_type->value === 'DONATION') bg-amber-50 text-warning border border-amber-200
                                        @elseif($entry->entry_type->value === 'RELEASE') bg-rose-50 text-rose-700 border border-rose-200
                                        @elseif($entry->entry_type->value === 'REALLOCATION') bg-purple-50 text-purple-700 border border-purple-200
                                        @else bg-slate-100 text-slate-700 border border-slate-200 @endif
                                    ">
                                        {{ $entry->entry_type->value }}
                                    </span>
                                </td>
                                <td class="px-4 py-3.5">
                                    <p class="font-bold text-neutral-strong">{{ $entry->fundingSource?->source_code }}</p>
                                    <p class="text-[11px] text-slate-500">{{ $entry->ayudaProgram?->program_code ?: $entry->notes }}</p>
                                </td>
                                <td class="px-4 py-3.5 text-right font-mono font-bold text-neutral-strong">
                                    ₱{{ number_format($entry->amount, 2) }}
                                </td>
                                <td class="px-4 py-3.5 text-right font-mono font-bold text-brand">
                                    ₱{{ number_format($entry->balance_after, 2) }}
                                </td>
                                <td class="px-4 py-3.5 text-slate-600">{{ $entry->creator?->name ?: 'System Audit' }}</td>
                                <td class="px-4 py-3.5 text-right font-mono text-slate-500 text-[11px]">
                                    {{ $entry->created_at->format('M d, Y h:i A') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-12 text-center text-slate-400 font-medium">
                                    No ledger audit records found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="pt-2">
                {{ $ledgerEntries->links() }}
            </div>
        </div>
    @endif

    <!-- TAB 4: GGMS MIRROR -->
    @if($activeTab === 'ggms_sync')
        <div class="bg-surface border border-slate-200 rounded-xl p-6 shadow-xs space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div>
                    <h3 class="text-base font-bold text-neutral-strong tracking-tight">Government GGMS Mirrored Allocations</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Synchronized from central GGMS database (`u518908950_ggms` / `OFF-2026-0006`).</p>
                </div>
                <button 
                    wire:click="syncGgms"
                    wire:loading.attr="disabled"
                    wire:target="syncGgms"
                    class="px-3 py-1.5 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-brand border border-emerald-200 text-xs font-bold transition-colors cursor-pointer flex items-center gap-1.5 disabled:opacity-75"
                    title="Fetch and synchronize live government grant allocations from central GGMS database"
                >
                    <svg wire:loading.remove wire:target="syncGgms" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    <svg wire:loading wire:target="syncGgms" class="animate-spin w-3.5 h-3.5 text-brand" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                    <span wire:loading.remove wire:target="syncGgms">Sync Grants Now</span>
                    <span wire:loading wire:target="syncGgms">Synchronizing GGMS...</span>
                </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($ggmsCaches as $cache)
                    <div class="p-4 rounded-xl border border-slate-200 bg-slate-50/50 space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="font-mono font-bold text-xs text-brand">{{ $cache->project_code }}</span>
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-brand">
                                {{ $cache->status }}
                            </span>
                        </div>
                        <h4 class="text-xs font-bold text-neutral-strong">{{ $cache->title }}</h4>
                        <div class="pt-2 border-t border-slate-200 flex justify-between text-xs font-mono">
                            <span class="text-slate-500">Allocated:</span>
                            <span class="font-bold text-neutral-strong">₱{{ number_format($cache->allocated_budget, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-xs font-mono">
                            <span class="text-slate-500">Disbursed:</span>
                            <span class="text-brand">₱{{ number_format($cache->spent_budget, 2) }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- SLIDE-OVER DETAIL INSPECTOR DRAWER (FOR UNIFIED REGISTRY) -->
    @if($showRegistryInspector && $inspectingRecord)
        <div class="fixed inset-0 z-50 overflow-hidden" aria-labelledby="slide-over-title" role="dialog" aria-modal="true">
            <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-xs transition-opacity" wire:click="closeRegistryInspector"></div>

            <div class="fixed inset-y-0 right-0 pl-10 max-w-full flex">
                <div class="w-screen max-w-md bg-white border-l border-slate-200 shadow-2xl p-6 space-y-6 flex flex-col justify-between overflow-y-auto">
                    <div class="space-y-5">
                        <!-- Drawer Top Header -->
                        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                            <div>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-brand border border-emerald-200">
                                    {{ $inspectingRecord['category'] }}
                                </span>
                                <h3 class="text-base font-bold text-neutral-strong mt-1.5">{{ $inspectingRecord['title'] }}</h3>
                            </div>
                            <button 
                                wire:click="closeRegistryInspector" 
                                class="text-slate-400 hover:text-neutral-strong text-xl font-bold cursor-pointer"
                                title="Close registry inspector drawer"
                            >&times;</button>
                        </div>

                        <!-- Financial Metric Card -->
                        <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 space-y-2.5">
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-slate-500">Allocated Earmark:</span>
                                <span class="font-mono font-bold text-neutral-strong">₱{{ number_format($inspectingRecord['allocated'], 2) }}</span>
                            </div>
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-slate-500">Total Disbursed:</span>
                                <span class="font-mono font-bold text-slate-700">₱{{ number_format($inspectingRecord['spent'], 2) }}</span>
                            </div>
                            <div class="flex items-center justify-between text-xs pt-2 border-t border-slate-200">
                                <span class="font-bold text-slate-700">Available Balance:</span>
                                <span class="font-mono font-black text-brand text-sm">₱{{ number_format($inspectingRecord['balance'], 2) }}</span>
                            </div>
                        </div>

                        <!-- Category-Specific Breakdown -->
                        <div>
                            <h5 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Category Specifications</h5>
                            <div class="p-3 bg-slate-50 rounded-lg border border-slate-100 text-xs space-y-1.5 font-mono">
                                <div class="flex justify-between">
                                    <span class="text-slate-500">Office / Unit:</span>
                                    <span class="text-neutral-strong">{{ $inspectingRecord['office'] }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-slate-500">Fiscal Period:</span>
                                    <span class="text-neutral-strong">FY {{ $inspectingRecord['fiscal_year'] }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-slate-500">Registration Date:</span>
                                    <span class="text-neutral-strong">{{ $inspectingRecord['created_at'] }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-slate-500">Lifecycle Status:</span>
                                    <span class="font-bold text-brand">{{ $inspectingRecord['status'] }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer Action -->
                    <div class="pt-4 border-t border-slate-100">
                        <button 
                            wire:click="closeRegistryInspector"
                            class="w-full py-2.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs cursor-pointer transition-colors"
                            title="Dismiss inspector and return to registry table"
                        >
                            Close Inspector
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- MODAL 1: PRIVATE DONATION FORM -->
    <div 
        x-show="$wire.showDonationModal"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-[#0F172A]/80 backdrop-blur-md"
        style="display: none;"
    >
        <div class="w-full max-w-lg bg-white border border-slate-200 rounded-2xl shadow-xl p-6 space-y-4 text-neutral-strong">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-base font-bold text-brand">Record Private Donation</h3>
                <button 
                    wire:click="closeDonationModal" 
                    class="text-slate-400 hover:text-neutral-strong text-xl font-bold cursor-pointer"
                    title="Close donation window without saving"
                >&times;</button>
            </div>

            <form wire:submit="saveDonation" class="space-y-4 text-xs">
                <!-- Donation Type Toggle -->
                <div>
                    <label class="block font-bold uppercase tracking-wider text-slate-500 mb-1.5">Donation Type</label>
                    <div class="grid grid-cols-2 gap-2">
                        <label 
                            class="flex items-center justify-center p-2.5 rounded-lg border cursor-pointer font-bold transition-all {{ $donationType === 'Cash' ? 'bg-emerald-50 border-brand text-brand shadow-xs' : 'bg-slate-50 border-slate-200 text-slate-600 hover:bg-slate-100' }}"
                            title="Select for monetary cash donation in Philippine Peso (₱)"
                        >
                            <input type="radio" wire:model.live="donationType" value="Cash" class="hidden">
                            <span>Cash Donation (₱)</span>
                        </label>
                        <label 
                            class="flex items-center justify-center p-2.5 rounded-lg border cursor-pointer font-bold transition-all {{ $donationType === 'Goods' ? 'bg-emerald-50 border-brand text-brand shadow-xs' : 'bg-slate-50 border-slate-200 text-slate-600 hover:bg-slate-100' }}"
                            title="Select for physical supplies, relief goods, or equipment"
                        >
                            <input type="radio" wire:model.live="donationType" value="Goods" class="hidden">
                            <span>Goods / In-Kind</span>
                        </label>
                    </div>
                </div>

                <!-- Donor Information -->
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-600 mb-1">Donor Type</label>
                        <select wire:model="donorType" class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-neutral-strong" title="Select whether donor is an organization or individual">
                            <option value="Organization">Organization / Foundation</option>
                            <option value="Person">Individual Person</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-600 mb-1">Donor Name *</label>
                        <input type="text" wire:model="donorName" placeholder="Donor or Org Name" class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-neutral-strong" required>
                    </div>
                </div>

                <!-- Dynamic Cash Fields -->
                @if($donationType === 'Cash')
                    <div>
                        <label class="block font-bold text-slate-600 mb-1">Donation Amount (PHP) *</label>
                        <input type="number" step="0.01" wire:model="cashAmount" placeholder="e.g. 50000.00" class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-neutral-strong font-mono text-sm" required>
                    </div>
                @else
                    <!-- Dynamic Goods Fields -->
                    <div class="grid grid-cols-3 gap-2">
                        <div class="col-span-2">
                            <label class="block font-bold text-slate-600 mb-1">Item Name *</label>
                            <input type="text" wire:model="goodsItemName" placeholder="e.g. Rice 25kg" class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-neutral-strong" required>
                        </div>
                        <div>
                            <label class="block font-bold text-slate-600 mb-1">Unit</label>
                            <select wire:model="goodsUnit" class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-neutral-strong">
                                <option value="Sacks">Sacks</option>
                                <option value="Boxes">Boxes</option>
                                <option value="Kits">Kits</option>
                                <option value="Pieces">Pieces</option>
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-slate-600 mb-1">Quantity *</label>
                            <input type="number" wire:model="goodsQuantity" placeholder="e.g. 100" class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-neutral-strong font-mono" required>
                        </div>
                        <div>
                            <label class="block font-bold text-slate-600 mb-1">Estimated Value (₱)</label>
                            <input type="number" step="0.01" wire:model="goodsEstimatedValue" placeholder="e.g. 150000.00" class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-neutral-strong font-mono">
                        </div>
                    </div>
                @endif

                <div>
                    <label class="block font-bold text-slate-600 mb-1">Notes / Reference Details</label>
                    <textarea wire:model="donationNotes" rows="2" placeholder="Official receipt #, remarks..." class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-neutral-strong"></textarea>
                </div>

                <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                    <button 
                        type="button" 
                        wire:click="closeDonationModal" 
                        class="px-4 py-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold cursor-pointer"
                        title="Discard entries and close form"
                    >
                        Cancel
                    </button>
                    <button 
                        type="submit" 
                        wire:loading.attr="disabled"
                        wire:target="saveDonation"
                        class="px-4 py-2 rounded-lg bg-brand hover:bg-emerald-700 text-white font-bold cursor-pointer shadow-xs flex items-center gap-1.5 disabled:opacity-75"
                        title="Post donation to the municipal funding pool and record immutable ledger entry"
                    >
                        <svg wire:loading wire:target="saveDonation" class="animate-spin w-3.5 h-3.5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                        <span wire:loading.remove wire:target="saveDonation">Post Donation to Ledger</span>
                        <span wire:loading wire:target="saveDonation">Posting to Immutable Ledger...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 2: PROJECT CREATION 4-STEP WIZARD -->
    @if($showProjectModal)
    <div 
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-[#0F172A]/80 backdrop-blur-md overflow-hidden animate-fadeIn"
    >
        <div class="w-full max-w-4xl bg-white border border-slate-200 rounded-2xl shadow-2xl p-6 text-neutral-strong max-h-[92vh] flex flex-col justify-between">
            
            <!-- Wizard Header & Progress Bar -->
            <div class="flex items-center justify-between border-b border-slate-100 pb-3 shrink-0">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-brand border border-emerald-200">
                            Wizard Step {{ $wizardStep }} of 4
                        </span>
                        <h3 class="text-base font-bold text-brand">Create Ayuda Project</h3>
                    </div>
                    <p class="text-xs text-slate-500 mt-0.5">
                        @if($wizardStep === 1) Select 1:1 Funding Source & Benefit Type
                        @elseif($wizardStep === 2) Configure Project Particulars & Targets
                        @elseif($wizardStep === 3) Financial Earmark Review & Timeline
                        @elseif($wizardStep === 4) Optional Beneficiary Pre-Enrollment (CRS Live)
                        @endif
                    </p>
                </div>
                <button 
                    wire:click="closeProjectModal" 
                    class="text-slate-400 hover:text-neutral-strong text-xl font-bold cursor-pointer"
                    title="Exit project creation wizard"
                >&times;</button>
            </div>

            <!-- Step Progress Indicators -->
            <div class="grid grid-cols-4 gap-2 text-center text-xs font-bold my-3 shrink-0">
                <div class="p-2 rounded-lg border {{ $wizardStep >= 1 ? 'bg-emerald-50 border-brand text-brand' : 'bg-slate-50 border-slate-200 text-slate-400' }}">
                    1. Funding Source
                </div>
                <div class="p-2 rounded-lg border {{ $wizardStep >= 2 ? 'bg-emerald-50 border-brand text-brand' : 'bg-slate-50 border-slate-200 text-slate-400' }}">
                    2. Particulars
                </div>
                <div class="p-2 rounded-lg border {{ $wizardStep >= 3 ? 'bg-emerald-50 border-brand text-brand' : 'bg-slate-50 border-slate-200 text-slate-400' }}">
                    3. Earmark Review
                </div>
                <div class="p-2 rounded-lg border {{ $wizardStep >= 4 ? 'bg-emerald-50 border-brand text-brand' : 'bg-slate-50 border-slate-200 text-slate-400' }}">
                    4. Beneficiaries
                </div>
            </div>

            <!-- WIZARD STEP BODY (SCROLLABLE IF NEEDED) -->
            <div class="flex-1 min-h-0 overflow-y-auto py-1">
                <!-- STEP 1: FUNDING SOURCE -->
                @if($wizardStep === 1)
                    <div class="space-y-4 text-xs">
                        <div>
                            <label class="block font-bold uppercase tracking-wider text-slate-500 mb-1.5">Select 1:1 Funding Source *</label>
                            <select 
                                wire:model.live="newProjectFundingSourceId" 
                                class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-neutral-strong font-medium"
                                title="Choose parent source pool to fund this project 1:1"
                            >
                                <option value="">-- Select Source Pool --</option>
                                @foreach($fundingSources as $source)
                                    <option value="{{ $source->id }}">
                                        [{{ $source->funding_type->value }}] {{ $source->source_code }} - {{ $source->title }} (Available: ₱{{ number_format($source->remaining_balance, 2) }})
                                    </option>
                                @endforeach
                            </select>
                            @error('newProjectFundingSourceId') <p class="text-rose-600 font-bold text-[11px] mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Selected Funding Source Overview Card -->
                        @if($this->selectedFundingSource)
                            <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 space-y-2.5 animate-fadeIn">
                                <div class="flex items-center justify-between">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $this->selectedFundingSource->funding_type->value === 'Government' ? 'bg-emerald-50 text-brand border border-emerald-200' : 'bg-amber-50 text-amber-800 border border-amber-200' }}">
                                        {{ $this->selectedFundingSource->funding_type->value }} Fund
                                    </span>
                                    <span class="font-mono font-bold text-[11px] text-slate-600">{{ $this->selectedFundingSource->source_code }}</span>
                                </div>
                                <div>
                                    <p class="font-bold text-neutral-strong text-xs">{{ $this->selectedFundingSource->title }}</p>
                                    <p class="text-[11px] text-slate-500">{{ $this->selectedFundingSource->office }} · FY {{ $this->selectedFundingSource->fiscal_year }}</p>
                                </div>
                                <div class="grid grid-cols-3 gap-2 pt-2 border-t border-slate-200/70 text-center">
                                    <div class="p-1.5 rounded-lg bg-white border border-slate-100">
                                        <p class="text-[10px] text-slate-400 font-bold uppercase">Allocated</p>
                                        <p class="font-mono font-bold text-neutral-strong text-xs">₱{{ number_format($this->selectedFundingSource->allocated_amount, 2) }}</p>
                                    </div>
                                    <div class="p-1.5 rounded-lg bg-white border border-slate-100">
                                        <p class="text-[10px] text-slate-400 font-bold uppercase">Disbursed</p>
                                        <p class="font-mono font-bold text-slate-600 text-xs">₱{{ number_format($this->selectedFundingSource->spent_amount, 2) }}</p>
                                    </div>
                                    <div class="p-1.5 rounded-lg bg-emerald-50/80 border border-emerald-100">
                                        <p class="text-[10px] text-brand font-bold uppercase">Available Cap</p>
                                        <p class="font-mono font-bold text-brand text-xs">₱{{ number_format($this->selectedFundingSource->remaining_balance, 2) }}</p>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div>
                            <label class="block font-bold uppercase tracking-wider text-slate-500 mb-1.5">Benefit Type</label>
                            <div class="grid grid-cols-2 gap-3">
                                <label 
                                    class="flex items-center justify-center p-3 rounded-lg border cursor-pointer font-bold transition-all {{ $newProjectBenefitType === 'Cash' ? 'bg-emerald-50 border-brand text-brand shadow-xs' : 'bg-slate-50 border-slate-200 text-slate-600 hover:bg-slate-100' }}"
                                    title="Disburse direct financial assistance in Philippine Peso (₱)"
                                >
                                    <input type="radio" wire:model.live="newProjectBenefitType" value="Cash" class="hidden">
                                    <span>Direct Cash Assistance (₱)</span>
                                </label>
                                <label 
                                    class="flex items-center justify-center p-3 rounded-lg border cursor-pointer font-bold transition-all {{ $newProjectBenefitType === 'Goods' ? 'bg-emerald-50 border-brand text-brand shadow-xs' : 'bg-slate-50 border-slate-200 text-slate-600 hover:bg-slate-100' }}"
                                    title="Disburse in-kind goods, supplies, or food packs"
                                >
                                    <input type="radio" wire:model.live="newProjectBenefitType" value="Goods" class="hidden">
                                    <span>In-Kind Goods / Supplies</span>
                                </label>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- STEP 2: PROJECT PARTICULARS -->
                @if($wizardStep === 2)
                    @php
                        $selectedSource = $this->selectedFundingSource;
                        $sourceBal = (float) ($selectedSource?->remaining_balance ?? 0);
                        $enteredCap = (float) ($newProjectBudgetCap ?: 0);
                        $isCapExceeded = $enteredCap > $sourceBal;
                        $calcTotal = $this->calculatedTotalCost;
                        $capUsagePct = $sourceBal > 0 ? min(100, round(($enteredCap / $sourceBal) * 100, 1)) : 0;
                    @endphp

                    <div class="space-y-3.5 text-xs">
                        <!-- Active Funding Source Context Banner -->
                        @if($selectedSource)
                            <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-between text-xs">
                                <div class="flex items-center gap-2">
                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-bold {{ $selectedSource->funding_type->value === 'Government' ? 'bg-emerald-50 text-brand border border-emerald-200' : 'bg-amber-50 text-amber-800 border border-amber-200' }}">
                                        {{ $selectedSource->funding_type->value }}
                                    </span>
                                    <div>
                                        <p class="font-bold text-neutral-strong text-[11px] leading-tight">{{ $selectedSource->title }}</p>
                                        <p class="text-[10px] text-slate-500 font-mono">{{ $selectedSource->source_code }}</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-[10px] text-slate-400 font-bold uppercase">Pool Allocation Balance</p>
                                    <p class="font-mono font-black text-brand text-xs">₱{{ number_format($sourceBal, 2) }}</p>
                                </div>
                            </div>
                        @endif

                        <div>
                            <label class="block font-bold text-slate-600 mb-1">Project Title *</label>
                            <input type="text" wire:model="newProjectTitle" placeholder="{{ $newProjectBenefitType === 'Goods' ? 'e.g. 2026 Calamity Rice & Food Pack Relief' : 'e.g. Sulop Indigent Emergency Cash Aid 2026' }}" class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-neutral-strong font-medium">
                            @error('newProjectTitle') <p class="text-rose-600 font-bold text-[11px] mt-1">{{ $message }}</p> @enderror
                        </div>

                        @if($newProjectBenefitType === 'Goods')
                            <!-- DEDICATED IN-KIND GOODS & SUPPLIES SPECIFICATION -->
                            <div class="p-3.5 rounded-xl bg-emerald-50/40 border border-emerald-200/80 space-y-3">
                                <div class="flex items-center gap-2 text-brand">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                    <span class="font-bold uppercase tracking-wider text-[11px]">In-Kind Package & Inventory Allocation</span>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                    <div class="sm:col-span-2">
                                        <label class="block font-bold text-slate-600 mb-1">Item / Package Name *</label>
                                        <input type="text" wire:model="newProjectItemName" placeholder="e.g. Premium Well-Milled Rice (25kg)" class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-neutral-strong font-medium">
                                        @error('newProjectItemName') <p class="text-rose-600 font-bold text-[11px] mt-1">{{ $message }}</p> @enderror
                                    </div>
                                    <div>
                                        <label class="block font-bold text-slate-600 mb-1">Unit of Measure *</label>
                                        <input type="text" wire:model="newProjectItemUnit" placeholder="e.g. Sacks, Boxes, Packs, Kits" class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-neutral-strong font-medium">
                                        @error('newProjectItemUnit') <p class="text-rose-600 font-bold text-[11px] mt-1">{{ $message }}</p> @enderror
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="block font-bold text-slate-600 mb-1">Qty per Beneficiary *</label>
                                        <input 
                                            type="number" 
                                            min="1" 
                                            wire:model.live.debounce.250ms="newProjectItemQty" 
                                            placeholder="1" 
                                            class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-neutral-strong font-mono font-bold"
                                        >
                                        @error('newProjectItemQty') <p class="text-rose-600 font-bold text-[11px] mt-1">{{ $message }}</p> @enderror
                                    </div>
                                    <div>
                                        <label class="block font-bold text-slate-600 mb-1">Target Beneficiary Count *</label>
                                        <input 
                                            type="number" 
                                            min="1" 
                                            wire:model.live.debounce.250ms="newProjectTargetCount" 
                                            placeholder="50" 
                                            class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-neutral-strong font-mono font-bold"
                                        >
                                        @error('newProjectTargetCount') <p class="text-rose-600 font-bold text-[11px] mt-1">{{ $message }}</p> @enderror
                                    </div>
                                </div>

                                <!-- Live Goods Total Calculation Banner -->
                                <div class="p-3 rounded-lg bg-white border border-emerald-200 flex items-center justify-between">
                                    <div>
                                        <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Total Goods Distribution Requirement</p>
                                        <p class="font-mono text-neutral-strong text-xs mt-0.5">
                                            {{ $newProjectTargetCount ?: 0 }} recipients × {{ $newProjectItemQty ?: 1 }} {{ $newProjectItemUnit ?: 'units' }} = 
                                            <span class="text-brand font-black text-sm">{{ $this->calculatedTotalGoodsQty }} {{ $newProjectItemUnit ?: 'units' }} total</span>
                                        </p>
                                    </div>
                                    <span class="px-2.5 py-1 rounded-md text-[11px] font-bold bg-emerald-50 text-brand border border-emerald-200">
                                        📦 In-Kind Mode
                                    </span>
                                </div>
                            </div>
                        @else
                            <!-- CASH BENEFIT FIELDS -->
                            <!-- Budget Cap Input with Live Listener & Max Auto-Fill -->
                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <label class="block font-bold text-slate-600">Budget Cap (₱) *</label>
                                    @if($sourceBal > 0)
                                        <button 
                                            type="button" 
                                            wire:click="setBudgetCapToMax" 
                                            class="text-[11px] font-bold text-brand hover:underline cursor-pointer flex items-center gap-1"
                                            title="Auto-fill with maximum available pool balance (₱{{ number_format($sourceBal, 2) }})"
                                        >
                                            <span>Use Max Available (₱{{ number_format($sourceBal, 2) }})</span>
                                        </button>
                                    @endif
                                </div>
                                <input 
                                    type="number" 
                                    step="0.01" 
                                    wire:model.live.debounce.250ms="newProjectBudgetCap" 
                                    placeholder="e.g. {{ $sourceBal > 0 ? number_format($sourceBal, 2, '.', '') : '50000.00' }}" 
                                    class="w-full bg-white border {{ $isCapExceeded ? 'border-rose-500 ring-1 ring-rose-500' : 'border-slate-300' }} rounded-lg px-3 py-2 text-neutral-strong font-mono font-bold"
                                >
                                @error('newProjectBudgetCap') 
                                    <p class="text-rose-600 font-bold text-[11px] mt-1">{{ $message }}</p> 
                                @enderror

                                <!-- Real-time Budget Cap Feedback Box -->
                                @if($enteredCap > 0)
                                    <div class="mt-2 p-2.5 rounded-lg border {{ $isCapExceeded ? 'bg-rose-50 border-rose-200 text-rose-800' : 'bg-emerald-50/70 border-emerald-200 text-slate-700' }} space-y-1.5">
                                        <div class="flex items-center justify-between text-[11px] font-bold">
                                            <span>
                                                @if($isCapExceeded)
                                                    ⚠️ Exceeds Available Pool Balance!
                                                @else
                                                    ✓ Budget Cap within Pool Limit ({{ $capUsagePct }}% of pool)
                                                @endif
                                            </span>
                                            <span class="font-mono">
                                                @if($isCapExceeded)
                                                    Over by ₱{{ number_format($enteredCap - $sourceBal, 2) }}
                                                @else
                                                    Leaves ₱{{ number_format($sourceBal - $enteredCap, 2) }}
                                                @endif
                                            </span>
                                        </div>
                                        <div class="w-full bg-slate-200 rounded-full h-1.5 overflow-hidden">
                                            <div 
                                                class="h-1.5 rounded-full transition-all duration-300 {{ $isCapExceeded ? 'bg-rose-600' : 'bg-brand' }}" 
                                                style="width: {{ min(100, $capUsagePct) }}%"
                                            ></div>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <!-- Unit Benefit & Target Beneficiaries -->
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block font-bold text-slate-600 mb-1">Unit Benefit (₱) per Beneficiary</label>
                                    <input 
                                        type="number" 
                                        step="0.01" 
                                        wire:model.live.debounce.250ms="newProjectUnitAmount" 
                                        placeholder="e.g. 5000.00" 
                                        class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-neutral-strong font-mono"
                                    >
                                </div>
                                <div>
                                    <label class="block font-bold text-slate-600 mb-1">Target Beneficiary Count</label>
                                    <input 
                                        type="number" 
                                        wire:model.live.debounce.250ms="newProjectTargetCount" 
                                        placeholder="e.g. 50" 
                                        class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-neutral-strong font-mono"
                                    >
                                </div>
                            </div>

                            <!-- Live Cost Multiplier & Sync Helper -->
                            @if($calcTotal > 0)
                                <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-200 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2 text-xs">
                                    <div>
                                        <p class="text-[10px] text-slate-500 font-bold uppercase">Estimated Total Payout Calculation</p>
                                        <p class="font-mono text-neutral-strong text-xs font-bold">
                                            {{ $newProjectTargetCount }} recipients × ₱{{ number_format((float) ($newProjectUnitAmount ?: 0), 2) }} = 
                                            <span class="{{ $calcTotal > $sourceBal ? 'text-rose-600' : 'text-brand' }}">₱{{ number_format($calcTotal, 2) }}</span>
                                        </p>
                                    </div>
                                    @if(abs($calcTotal - $enteredCap) > 0.01 && $calcTotal <= $sourceBal)
                                        <button 
                                            type="button" 
                                            wire:click="syncBudgetCapWithCalculated" 
                                            class="px-2.5 py-1 rounded bg-amber-50 hover:bg-amber-100 border border-amber-200 text-amber-900 font-bold text-[10px] transition-colors cursor-pointer shrink-0"
                                            title="Set the Budget Cap to exactly match the calculated payout (₱{{ number_format($calcTotal, 2) }})"
                                        >
                                            Apply ₱{{ number_format($calcTotal, 2) }} to Cap
                                        </button>
                                    @endif
                                </div>
                            @endif
                        @endif

                        <div>
                            <label class="block font-bold text-slate-600 mb-1">Target Barangay (Scope)</label>
                            <select wire:model.live="newProjectTargetBarangay" class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-neutral-strong" title="Filter target municipality scope">
                                <option value="">Municipality-Wide</option>
                                @foreach($barangays as $brgy)
                                    <option value="{{ $brgy }}">{{ $brgy }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                @endif

                <!-- STEP 3: FINANCIAL EARMARK & TIMELINE REVIEW -->
                @if($wizardStep === 3)
                    @php
                        $selectedSource = $this->selectedFundingSource;
                        $sourceBal = (float) ($selectedSource?->remaining_balance ?? 0);
                        $enteredCap = (float) ($newProjectBudgetCap ?: 0);
                    @endphp

                    <div class="space-y-4 text-xs">
                        <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 space-y-2.5">
                            <div class="flex justify-between items-center pb-2 border-b border-slate-200">
                                <span class="text-slate-500 font-medium">Target Funding Source:</span>
                                <span class="font-bold text-neutral-strong text-right">
                                    {{ $selectedSource?->title ?: 'N/A' }} 
                                    <span class="font-mono text-slate-500 text-[11px]">({{ $selectedSource?->source_code }})</span>
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-500">Project Title:</span>
                                <span class="font-bold text-neutral-strong">{{ $newProjectTitle }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-500">Benefit Type:</span>
                                <span class="font-bold {{ $newProjectBenefitType === 'Goods' ? 'text-brand' : 'text-neutral-strong' }}">
                                    {{ $newProjectBenefitType === 'Goods' ? '📦 In-Kind Goods / Supplies' : '💵 Direct Cash Assistance' }}
                                </span>
                            </div>

                            @if($newProjectBenefitType === 'Goods')
                                <div class="flex justify-between">
                                    <span class="text-slate-500">Aid Package per Beneficiary:</span>
                                    <span class="font-bold text-brand">{{ $newProjectItemQty }} {{ $newProjectItemUnit }} {{ $newProjectItemName }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-slate-500">Total Distribution Volume:</span>
                                    <span class="font-mono font-bold text-neutral-strong">{{ $this->calculatedTotalGoodsQty }} {{ $newProjectItemUnit }} Total</span>
                                </div>
                            @else
                                <div class="flex justify-between">
                                    <span class="text-slate-500">Earmarked Budget Cap:</span>
                                    <span class="font-mono font-bold text-brand">₱{{ number_format($enteredCap, 2) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-slate-500">Available Pool Balance After Earmark:</span>
                                    <span class="font-mono font-bold text-slate-700">₱{{ number_format(max(0, $sourceBal - $enteredCap), 2) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-slate-500">Unit Amount:</span>
                                    <span class="font-mono text-neutral-strong">₱{{ number_format((float) ($newProjectUnitAmount ?: 0), 2) }} / recipient</span>
                                </div>
                            @endif

                            <div class="flex justify-between">
                                <span class="text-slate-500">Target Coverage:</span>
                                <span class="font-medium text-slate-700">{{ $newProjectTargetBarangay ?: 'Municipality-Wide' }} ({{ $newProjectTargetCount }} slots)</span>
                            </div>
                            <div class="flex justify-between pt-1 border-t border-slate-200">
                                <span class="text-slate-500">Enrolled Beneficiaries:</span>
                                <span class="font-bold {{ count($selectedBeneficiaries) > 0 ? 'text-brand font-mono' : 'text-emerald-700' }}">
                                    @if(count($selectedBeneficiaries) > 0)
                                        ✓ {{ count($selectedBeneficiaries) }} candidate(s) pre-selected
                                    @else
                                        ⚡ Will auto-enroll {{ $newProjectTargetCount }} citizens upon creation
                                    @endif
                                </span>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block font-bold text-slate-600 mb-1">Start Date</label>
                                <input type="date" wire:model="newProjectStartDate" class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-neutral-strong">
                            </div>
                            <div>
                                <label class="block font-bold text-slate-600 mb-1">End Date</label>
                                <input type="date" wire:model="newProjectEndDate" class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-neutral-strong">
                            </div>
                        </div>

                        <div>
                            <label class="block font-bold text-slate-600 mb-1">Project Notes / Program Description</label>
                            <textarea wire:model="newProjectDescription" rows="2" placeholder="Special requirements, guidelines, voucher details..." class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-neutral-strong"></textarea>
                        </div>
                    </div>
                @endif

                <!-- STEP 4: BENEFICIARY CANDIDATE ENROLLMENT (TWO-PANE + HOUSEHOLD REVIEW) -->
                @if($wizardStep === 4)
                    <div class="space-y-3 text-xs h-full flex flex-col min-h-0">
                        <!-- Summary Alert Banner -->
                        <div class="p-2.5 rounded-lg bg-emerald-50 border border-emerald-200 flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs text-brand shrink-0">
                            <span class="font-medium">
                                Showing first {{ count($candidates) }} of {{ number_format($totalCandidatesCount) }} Sulop citizens in live CRS registry.
                            </span>
                            <div class="flex items-center gap-2">
                                <button 
                                    type="button"
                                    wire:click="autoFillCandidatePool"
                                    wire:loading.attr="disabled"
                                    wire:target="autoFillCandidatePool"
                                    class="px-2.5 py-1 rounded bg-brand text-white hover:bg-emerald-800 text-[11px] font-bold cursor-pointer transition-colors shadow-2xs flex items-center gap-1 disabled:opacity-75"
                                    title="Auto-populate selected queue with up to {{ $newProjectTargetCount }} citizens matching filter"
                                >
                                    <svg wire:loading wire:target="autoFillCandidatePool" class="animate-spin h-3 w-3 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                                    <span wire:loading.remove wire:target="autoFillCandidatePool">⚡ Auto-Fill {{ $newProjectTargetCount }} Slots</span>
                                    <span wire:loading wire:target="autoFillCandidatePool">Enlisting {{ $newProjectTargetCount }}...</span>
                                </button>
                                <span class="font-mono font-bold bg-white px-2 py-0.5 rounded border border-emerald-200">{{ count($selectedBeneficiaries) }} / {{ $newProjectTargetCount }} selected</span>
                            </div>
                        </div>

                        <!-- Two-Pane Layout Container with Fixed Height and Inner Scroll -->
                        <div class="grid grid-cols-1 lg:grid-cols-12 gap-3 h-[380px] min-h-0">
                            
                            <!-- Left Pane: Searchable / Paginated Candidates -->
                            <div class="lg:col-span-7 border border-slate-200 rounded-xl p-3 flex flex-col min-h-0 bg-slate-50/50 relative">
                                <!-- Filters -->
                                <div class="flex gap-2 mb-2 shrink-0">
                                    <div class="relative w-full">
                                        <input 
                                            type="text" 
                                            wire:model.live.debounce.250ms="candidateSearch" 
                                            placeholder="Search citizen name or CRN..." 
                                            class="w-full bg-white border border-slate-300 rounded-lg px-2.5 py-1.5 text-xs text-neutral-strong pr-7"
                                            title="Search CRS citizen records by full name or Civil Registry Number (CRN)"
                                        >
                                        <div wire:loading wire:target="candidateSearch" class="absolute right-2 top-2">
                                            <svg class="animate-spin h-3.5 w-3.5 text-brand" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                                        </div>
                                    </div>
                                    <select 
                                        wire:model.live="candidateBarangay" 
                                        class="w-36 bg-white border border-slate-300 rounded-lg px-2 py-1.5 text-xs text-neutral-strong cursor-pointer"
                                        title="Filter candidate pool by barangay"
                                    >
                                        <option value="">All Barangays</option>
                                        @foreach($barangays as $brgy)
                                            <option value="{{ $brgy }}">{{ $brgy }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Candidate List (Scrollable) -->
                                <div class="flex-1 min-h-0 overflow-y-auto divide-y divide-slate-100 bg-white rounded-lg border border-slate-200 relative">
                                    <!-- Candidate search/filter loading overlay -->
                                    <div wire:loading.flex wire:target="candidateSearch, candidateBarangay" class="absolute inset-0 bg-white/80 backdrop-blur-xs z-10 flex-col items-center justify-center gap-2">
                                        <svg class="animate-spin h-6 w-6 text-brand" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                        </svg>
                                        <span class="text-xs font-bold text-neutral-strong">Filtering CRS Registry...</span>
                                    </div>

                                    <!-- Candidate Review Profile Loading Overlay -->
                                    <div wire:loading.flex wire:target="openHouseholdReview" class="absolute inset-0 bg-slate-900/40 backdrop-blur-xs z-20 flex-col items-center justify-center p-4">
                                        <div class="bg-white p-4 rounded-xl shadow-2xl flex items-center gap-3 border border-slate-200 animate-fadeIn">
                                            <svg class="animate-spin h-5 w-5 text-brand shrink-0" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                            </svg>
                                            <div>
                                                <p class="font-bold text-xs text-neutral-strong">Auditing Household & Benefits...</p>
                                                <p class="text-[10px] text-slate-500">Querying CRS live demographics & past aid history</p>
                                            </div>
                                        </div>
                                    </div>

                                    @forelse($candidates as $candidate)
                                        <div 
                                            wire:click="openHouseholdReview({{ $candidate->id }})"
                                            class="p-2 hover:bg-emerald-50/60 cursor-pointer flex items-center justify-between transition-colors group"
                                            title="Click to review {{ $candidate->full_name }}'s household history before enrolling"
                                        >
                                            <div>
                                                <p class="font-bold text-neutral-strong group-hover:text-brand text-xs">{{ $candidate->full_name }}</p>
                                                <p class="text-[10px] text-slate-500 font-mono">
                                                    {{ $candidate->civil_registry_id ?: $candidate->civilregistry_id ?: $candidate->beneficiary_id }} • {{ $candidate->barangay }}
                                                </p>
                                            </div>
                                            <div class="flex items-center gap-1">
                                                <span wire:loading.remove wire:target="openHouseholdReview({{ $candidate->id }})" class="text-[10px] font-bold text-brand group-hover:underline px-1.5 py-0.5 rounded bg-emerald-50 border border-emerald-100">
                                                    Review &rarr;
                                                </span>
                                                <span wire:loading.flex wire:target="openHouseholdReview({{ $candidate->id }})" class="items-center gap-1 text-[10px] font-bold text-brand px-1.5 py-0.5 rounded bg-emerald-100">
                                                    <svg class="animate-spin h-3 w-3 text-brand" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                                                    Auditing...
                                                </span>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="p-8 text-center text-slate-400">
                                            No CRS citizen records match your filter.
                                        </div>
                                    @endforelse
                                </div>
                            </div>

                            <!-- Right Pane: Currently Selected List -->
                            <div class="lg:col-span-5 border border-slate-200 rounded-xl p-3 flex flex-col min-h-0 bg-white relative">
                                <div class="flex items-center justify-between pb-2 border-b border-slate-100 shrink-0">
                                    <span class="font-bold text-neutral-strong text-xs flex items-center gap-1.5">
                                        <span>Selected Candidates</span>
                                        <span class="px-1.5 py-0.2 rounded bg-emerald-50 text-brand border border-emerald-200 font-mono font-bold text-[10px]">
                                            {{ count($selectedBeneficiaries) }}
                                        </span>
                                    </span>
                                    @if(count($selectedBeneficiaries) > 0)
                                        <button 
                                            wire:click="clearAllCandidates" 
                                            wire:loading.attr="disabled"
                                            wire:target="clearAllCandidates"
                                            class="text-[11px] text-rose-600 hover:underline font-bold cursor-pointer flex items-center gap-1 disabled:opacity-75"
                                            title="Clear all selected candidate beneficiaries from queue"
                                        >
                                            <svg wire:loading wire:target="clearAllCandidates" class="animate-spin h-3 w-3 text-rose-600" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                                            <span wire:loading.remove wire:target="clearAllCandidates">Clear All</span>
                                            <span wire:loading wire:target="clearAllCandidates">Clearing...</span>
                                        </button>
                                    @endif
                                </div>

                                <!-- Selected Items Container (Scrollable) -->
                                <div class="flex-1 min-h-0 overflow-y-auto divide-y divide-slate-100 my-1.5">
                                    @forelse($selectedBeneficiaries as $key => $sel)
                                        <div class="py-1.5 flex items-center justify-between">
                                            <div>
                                                <p class="font-bold text-neutral-strong text-xs">{{ $sel['full_name'] }}</p>
                                                <p class="text-[10px] text-slate-400 font-mono">{{ $sel['barangay'] }}</p>
                                            </div>
                                            <button 
                                                wire:click="removeCandidate('{{ $key }}')" 
                                                wire:loading.attr="disabled"
                                                wire:target="removeCandidate('{{ $key }}')"
                                                class="text-slate-400 hover:text-rose-600 p-1 cursor-pointer text-sm font-bold disabled:opacity-75"
                                                title="Remove {{ $sel['full_name'] }} from selection list"
                                            >
                                                <span wire:loading.remove wire:target="removeCandidate('{{ $key }}')">&times;</span>
                                                <svg wire:loading wire:target="removeCandidate('{{ $key }}')" class="animate-spin h-3 w-3 text-rose-600" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                                            </button>
                                        </div>
                                    @empty
                                        <div class="p-8 text-center text-slate-400 text-xs">
                                            No candidates enrolled yet. Click any candidate on the left to review household history and add.
                                        </div>
                                    @endforelse
                                </div>

                                <div class="pt-2 border-t border-slate-100 text-[11px] text-slate-500 font-mono shrink-0">
                                    Target Count: {{ $newProjectTargetCount }} slots
                                </div>
                            </div>

                        </div>
                    </div>
                @endif
            </div>

            <!-- Global Action Loading Overlay inside Project Wizard Modal -->
            <div wire:loading.flex wire:target="createProject" class="absolute inset-0 bg-[#0F172A]/70 backdrop-blur-xs z-50 rounded-2xl flex-col items-center justify-center p-6 text-white">
                <div class="bg-white text-neutral-strong px-7 py-6 rounded-2xl shadow-2xl flex flex-col items-center gap-3 border border-slate-200 animate-fadeIn text-center">
                    <svg class="animate-spin h-9 w-9 text-brand" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                    </svg>
                    <div>
                        <p class="font-bold text-sm text-neutral-strong">Creating Ayuda Project</p>
                        <p class="text-xs text-slate-500 mt-0.5">Earmarking budget pool & recording immutable ledger entry...</p>
                    </div>
                </div>
            </div>

            <!-- WIZARD FOOTER ACTIONS -->
            <div class="flex items-center justify-between pt-3 border-t border-slate-100 shrink-0">
                @if($wizardStep > 1)
                    <button 
                        type="button" 
                        wire:click="prevStep" 
                        wire:loading.attr="disabled"
                        wire:target="prevStep, nextStep, createProject"
                        class="px-4 py-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs cursor-pointer disabled:opacity-75"
                        title="Return to previous wizard step"
                    >
                        &larr; Back
                    </button>
                @else
                    <button 
                        type="button" 
                        wire:click="closeProjectModal" 
                        wire:loading.attr="disabled"
                        wire:target="createProject"
                        class="px-4 py-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs cursor-pointer disabled:opacity-75"
                        title="Cancel and exit project wizard"
                    >
                        Cancel
                    </button>
                @endif

                <div class="flex items-center gap-2">
                    @if($wizardStep === 1)
                        <button 
                            type="button" 
                            wire:click="nextStep" 
                            wire:loading.attr="disabled"
                            wire:target="nextStep"
                            class="px-5 py-2 rounded-lg bg-brand hover:bg-emerald-800 text-white font-bold text-xs cursor-pointer flex items-center gap-1.5 shadow-xs disabled:opacity-75"
                            title="Proceed to configure project particulars and target coverage"
                        >
                            <svg wire:loading wire:target="nextStep" class="animate-spin h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                            <span wire:loading.remove wire:target="nextStep">Continue to Particulars</span>
                            <span wire:loading wire:target="nextStep">Validating Source...</span>
                            <svg wire:loading.remove wire:target="nextStep" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </button>
                    @elseif($wizardStep === 2)
                        <button 
                            type="button" 
                            wire:click="nextStep" 
                            wire:loading.attr="disabled"
                            wire:target="nextStep"
                            class="px-5 py-2 rounded-lg bg-brand hover:bg-emerald-800 text-white font-bold text-xs cursor-pointer flex items-center gap-1.5 shadow-xs disabled:opacity-75"
                            title="Proceed to financial earmark review and timeline settings"
                        >
                            <svg wire:loading wire:target="nextStep" class="animate-spin h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                            <span wire:loading.remove wire:target="nextStep">Review Earmark</span>
                            <span wire:loading wire:target="nextStep">Validating Budget Cap...</span>
                            <svg wire:loading.remove wire:target="nextStep" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </button>
                    @elseif($wizardStep === 3)
                        <button 
                            type="button" 
                            wire:click="createProject" 
                            wire:loading.attr="disabled"
                            wire:target="createProject, nextStep"
                            class="px-4 py-2 rounded-lg bg-slate-200 hover:bg-slate-300 text-neutral-strong font-bold text-xs cursor-pointer shadow-xs flex items-center gap-1.5 disabled:opacity-75"
                            title="Immediately create project without pre-enrolling beneficiary candidates"
                        >
                            <svg wire:loading wire:target="createProject" class="animate-spin h-3.5 w-3.5 text-neutral-strong" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                            <span wire:loading.remove wire:target="createProject">Skip Enrollment & Create Project</span>
                            <span wire:loading wire:target="createProject">Creating Project...</span>
                        </button>
                        <button 
                            type="button" 
                            wire:click="nextStep" 
                            wire:loading.attr="disabled"
                            wire:target="nextStep, createProject"
                            class="px-5 py-2 rounded-lg bg-accent hover:bg-amber-400 text-neutral-strong font-bold text-xs cursor-pointer flex items-center gap-1.5 shadow-xs disabled:opacity-75"
                            title="Proceed to candidate search and household audit screening"
                        >
                            <svg wire:loading wire:target="nextStep" class="animate-spin h-3.5 w-3.5 text-neutral-strong" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                            <span wire:loading.remove wire:target="nextStep">Enroll Candidates (Step 4)</span>
                            <span wire:loading wire:target="nextStep">Loading CRS Pool...</span>
                            <svg wire:loading.remove wire:target="nextStep" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </button>
                    @elseif($wizardStep === 4)
                        <button 
                            type="button" 
                            wire:click="createProject" 
                            wire:loading.attr="disabled"
                            wire:target="createProject"
                            class="px-6 py-2.5 rounded-lg bg-brand hover:bg-emerald-800 text-white font-black text-xs cursor-pointer shadow-md flex items-center gap-1.5 disabled:opacity-75"
                            title="Finalize project creation and enroll all selected citizens into the project"
                        >
                            <svg wire:loading wire:target="createProject" class="animate-spin h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                            <span wire:loading.remove wire:target="createProject">Create Project & Enroll Candidates</span>
                            <span wire:loading wire:target="createProject">Enrolling Citizens & Posting Ledger...</span>
                        </button>
                    @endif
                </div>
            </div>

        </div>
    </div>
    @endif

    <!-- MODAL 2B: HOUSEHOLD AUDIT REVIEW MODAL (TWO-STEP CONFIRMATION) -->
    @if($showHouseholdModal && $reviewingCandidate)
        <div class="fixed inset-0 z-[70] flex items-center justify-center p-4 bg-[#0F172A]/85 backdrop-blur-md">
            <div class="w-full max-w-2xl bg-white border border-slate-200 rounded-2xl shadow-2xl p-6 space-y-4 text-neutral-strong relative">
                
                <!-- Modal Header -->
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-brand border border-emerald-200">
                                Household Review
                            </span>
                            <h3 class="text-base font-bold text-neutral-strong">{{ $reviewingCandidate['full_name'] }}</h3>
                        </div>
                        <p class="text-xs text-slate-500 mt-0.5">
                            CRN: <span class="font-mono font-bold text-brand">{{ $reviewingCandidate['civil_registry_id'] }}</span> • Barangay: {{ $reviewingCandidate['barangay'] }}
                        </p>
                    </div>
                    <button 
                        wire:click="closeHouseholdReview" 
                        wire:loading.attr="disabled"
                        wire:target="confirmAddCandidate"
                        class="text-slate-400 hover:text-neutral-strong text-xl font-bold cursor-pointer disabled:opacity-75"
                        title="Close household review modal"
                    >&times;</button>
                </div>

                <!-- Demographics & Household Structure Card -->
                <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 text-xs space-y-1.5">
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500">Household Code:</span>
                        <span class="font-mono font-bold text-neutral-strong">{{ $reviewingHouseholdCode }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500">Household Head:</span>
                        <span class="font-bold text-neutral-strong">{{ $reviewingHouseholdHead }}</span>
                    </div>
                    @if($reviewingDemographicsSummary)
                        <div class="flex items-center justify-between pt-1 border-t border-slate-200">
                            <span class="text-slate-500">Demographics:</span>
                            <span class="font-medium text-slate-700">{{ $reviewingDemographicsSummary }}</span>
                        </div>
                    @endif
                </div>

                <!-- Household Members & Claims History Table -->
                <div class="space-y-2">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Household Members & Claims Record</span>
                    <div class="border border-slate-200 rounded-xl overflow-hidden max-h-48 overflow-y-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-50 text-slate-500 font-bold uppercase tracking-wider border-b border-slate-200 sticky top-0">
                                <tr>
                                    <th class="px-3 py-2">Full Name</th>
                                    <th class="px-3 py-2">Relationship</th>
                                    <th class="px-3 py-2">Sex / Age</th>
                                    <th class="px-3 py-2 text-right">Prior Benefits</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 bg-white">
                                @foreach($reviewingHouseholdMembers as $member)
                                    <tr>
                                        <td class="px-3 py-2 font-bold text-neutral-strong">{{ $member['full_name'] }}</td>
                                        <td class="px-3 py-2 text-slate-600">{{ $member['relationship'] }}</td>
                                        <td class="px-3 py-2 text-slate-500 font-mono">{{ $member['sex'] }} • {{ $member['age'] }}</td>
                                        <td class="px-3 py-2 text-right font-mono font-bold {{ $member['benefits_count'] > 0 ? 'text-amber-600' : 'text-slate-400' }}">
                                            {{ $member['benefits_count'] }} claim(s)
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Household Total Statement -->
                <div class="p-3 rounded-lg bg-amber-50/70 border border-amber-200 text-xs text-amber-900 font-medium flex items-center justify-between">
                    @if($reviewingHouseholdTotalBenefits > 0)
                        <span>⚠️ This household has received <strong>{{ $reviewingHouseholdTotalBenefits }} benefit(s)</strong> in total across all municipal projects.</span>
                    @else
                        <span>✓ This household has not received any aid benefits yet.</span>
                    @endif
                </div>

                <!-- Modal Actions -->
                <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                    <button 
                        type="button" 
                        wire:click="closeHouseholdReview" 
                        wire:loading.attr="disabled"
                        wire:target="confirmAddCandidate"
                        class="px-4 py-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold cursor-pointer disabled:opacity-75"
                        title="Cancel candidate selection and return to list"
                    >
                        Cancel
                    </button>
                    <button 
                        type="button" 
                        wire:click="confirmAddCandidate" 
                        wire:loading.attr="disabled"
                        wire:target="confirmAddCandidate"
                        class="px-5 py-2 rounded-lg bg-brand hover:bg-emerald-800 text-white text-xs font-bold cursor-pointer shadow-xs flex items-center gap-1.5 disabled:opacity-75"
                        title="Confirm household eligibility and add candidate to project enrollment queue"
                    >
                        <svg wire:loading wire:target="confirmAddCandidate" class="animate-spin h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                        <span wire:loading.remove wire:target="confirmAddCandidate">Add to Project</span>
                        <span wire:loading wire:target="confirmAddCandidate">Adding Candidate...</span>
                    </button>
                </div>

            </div>
        </div>
    @endif

    <!-- MODAL 3: REALLOCATION CONFIRMATION -->
    <div 
        x-show="$wire.showReallocationModal"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-[#0F172A]/80 backdrop-blur-md"
        style="display: none;"
    >
        <div class="w-full max-w-md bg-white border border-slate-200 rounded-2xl shadow-xl p-6 space-y-4 text-neutral-strong">
            <h3 class="text-base font-bold text-neutral-strong">Reallocate Unspent Project Earmark?</h3>
            <p class="text-xs text-slate-600">
                This action will reclaim the remaining unspent budget cap from this project back into its parent funding source's unrestricted pool and close the project. A permanent Reallocation entry will be posted to the ledger.
            </p>
            <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                <button 
                    type="button" 
                    wire:click="$set('showReallocationModal', false)" 
                    wire:loading.attr="disabled"
                    wire:target="executeReallocation"
                    class="px-4 py-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold cursor-pointer disabled:opacity-75"
                    title="Cancel reallocation and keep project active"
                >
                    Cancel
                </button>
                <button 
                    type="button" 
                    wire:click="executeReallocation" 
                    wire:loading.attr="disabled"
                    wire:target="executeReallocation"
                    class="px-4 py-2 rounded-lg bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold cursor-pointer shadow-xs flex items-center gap-1.5 disabled:opacity-75"
                    title="Execute immutable reallocation: reclaim unspent balance to source and close project"
                >
                    <svg wire:loading wire:target="executeReallocation" class="animate-spin h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                    <span wire:loading.remove wire:target="executeReallocation">Confirm Reallocation</span>
                    <span wire:loading wire:target="executeReallocation">Reallocating...</span>
                </button>
            </div>
        </div>
    </div>
</div>
