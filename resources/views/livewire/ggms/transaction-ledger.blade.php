<div class="space-y-6">
    <!-- Header with Sync Button & Status Pill -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 bg-white p-6 rounded-xl border border-slate-200 shadow-xs">
        <div>
            <div class="flex items-center gap-2.5">
                <h1 class="text-2xl font-bold text-brand tracking-tight">GGMS Consolidated Transactions</h1>
                <div class="flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-emerald-50 text-brand border border-emerald-200 text-[11px] font-bold">
                    <span class="w-1.5 h-1.5 rounded-full bg-brand animate-pulse"></span>
                    <span>Cross-Module Sync Hub Online</span>
                </div>
            </div>
            <p class="text-xs text-slate-500 mt-1">Append-only audit-grade disbursement ledger mapping internal AMS codes to central GGMS grant identifiers.</p>
        </div>

        <div class="flex items-center gap-2.5">
            @if($pendingRetryCount > 0)
                <span class="px-3 py-1.5 rounded-lg bg-amber-50 border border-amber-200 text-warning text-xs font-mono font-bold">
                    {{ $pendingRetryCount }} Offline Pending
                </span>
            @endif
            <button 
                wire:click="syncNow"
                class="px-4 py-2 rounded-lg bg-accent hover:bg-amber-400 text-neutral-strong text-xs font-bold transition-all shadow-xs flex items-center gap-1.5 cursor-pointer uppercase tracking-wide"
            >
                <svg class="w-4 h-4 text-neutral-strong" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                <span>Sync Now</span>
            </button>
        </div>
    </div>

    <!-- 4 KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-surface border border-slate-200 rounded-xl p-5 shadow-xs">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Total Synced Transactions</span>
            <h3 class="text-2xl font-black text-neutral-strong tracking-tight font-mono mt-2">{{ number_format($totalSynced) }}</h3>
            <p class="text-xs text-slate-500 mt-1">Disbursements recorded</p>
        </div>
        <div class="bg-surface border border-slate-200 rounded-xl p-5 shadow-xs">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Project Distributions</span>
            <h3 class="text-2xl font-black text-brand tracking-tight font-mono mt-2">{{ number_format($distributionCount) }}</h3>
            <p class="text-xs text-slate-500 mt-1">Ayuda releases</p>
        </div>
        <div class="bg-surface border border-slate-200 rounded-xl p-5 shadow-xs">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Cash-for-Work & Seminars</span>
            <h3 class="text-2xl font-black text-neutral-strong tracking-tight font-mono mt-2">{{ number_format($cfwCount) }}</h3>
            <p class="text-xs text-slate-500 mt-1">Legacy records</p>
        </div>
        <div class="bg-surface border border-slate-200 rounded-xl p-5 shadow-xs">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Aid Request Releases</span>
            <h3 class="text-2xl font-black text-neutral-strong tracking-tight font-mono mt-2">{{ number_format($aidRequestCount) }}</h3>
            <p class="text-xs text-slate-500 mt-1">Individual cases</p>
        </div>
    </div>

    <!-- Category Tabs & Search Bar -->
    <div class="bg-surface border border-slate-200 rounded-xl p-6 shadow-xs space-y-4">
        <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4 pb-4 border-b border-slate-100">
            <!-- Tabs -->
            <div class="flex flex-wrap gap-2 text-xs font-bold">
                <button 
                    wire:click="$set('categoryTab', 'ALL')"
                    class="px-3.5 py-1.5 rounded-lg border transition-all cursor-pointer {{ $categoryTab === 'ALL' ? 'bg-emerald-50 border-brand text-brand shadow-xs' : 'bg-slate-50 border-slate-200 text-slate-600 hover:bg-slate-100' }}"
                >
                    All Transactions
                </button>
                <button 
                    wire:click="$set('categoryTab', 'Project Distribution')"
                    class="px-3.5 py-1.5 rounded-lg border transition-all cursor-pointer {{ $categoryTab === 'Project Distribution' ? 'bg-emerald-50 border-brand text-brand shadow-xs' : 'bg-slate-50 border-slate-200 text-slate-600 hover:bg-slate-100' }}"
                >
                    Project Distribution
                </button>
                <button 
                    wire:click="$set('categoryTab', 'Cash For Work')"
                    class="px-3.5 py-1.5 rounded-lg border transition-all cursor-pointer {{ $categoryTab === 'Cash For Work' ? 'bg-emerald-50 border-brand text-brand shadow-xs' : 'bg-slate-50 border-slate-200 text-slate-600 hover:bg-slate-100' }}"
                >
                    Cash For Work
                </button>
                <button 
                    wire:click="$set('categoryTab', 'Aid Request')"
                    class="px-3.5 py-1.5 rounded-lg border transition-all cursor-pointer {{ $categoryTab === 'Aid Request' ? 'bg-emerald-50 border-brand text-brand shadow-xs' : 'bg-slate-50 border-slate-200 text-slate-600 hover:bg-slate-100' }}"
                >
                    Aid Request
                </button>
            </div>

            <!-- Search & Barangay Filter -->
            <div class="flex flex-wrap items-center gap-2.5 w-full lg:w-auto">
                <input 
                    wire:model.live.debounce.300ms="search"
                    type="text" 
                    placeholder="Search name, ID, project code..."
                    class="bg-white border border-slate-300 rounded-lg px-3 py-1.5 text-xs text-neutral-strong placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-brand w-full sm:w-60"
                >
                <select 
                    wire:model.live="selectedBarangay"
                    class="bg-white border border-slate-300 rounded-lg px-3 py-1.5 text-xs text-neutral-strong focus:outline-none focus:ring-1 focus:ring-brand cursor-pointer"
                >
                    <option value="">All Barangays</option>
                    @foreach($barangays as $b)
                        <option value="{{ $b }}">{{ $b }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- Transactions Table (Fixed 25 per page) -->
        <div class="overflow-x-auto relative">
            <!-- Loading Skeleton Overlay -->
            <div wire:loading class="absolute inset-0 bg-white/70 backdrop-blur-xs z-10 flex items-center justify-center">
                <div class="flex items-center gap-2 text-brand font-bold text-xs">
                    <svg class="animate-spin h-4 w-4 text-brand" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span>Loading GGMS transactions...</span>
                </div>
            </div>

            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 font-bold uppercase tracking-wider border-y border-slate-100">
                    <tr>
                        <th class="px-4 py-3 rounded-l-lg">Project Code</th>
                        <th class="px-4 py-3">GGMS Grant #</th>
                        <th class="px-4 py-3">Module</th>
                        <th class="px-4 py-3">Beneficiary</th>
                        <th class="px-4 py-3">Barangay & HH</th>
                        <th class="px-4 py-3 text-right">Amount Disbursed</th>
                        <th class="px-4 py-3">Disbursement Date</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-center rounded-r-lg">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($transactions as $trx)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-4 py-3.5 font-mono font-bold text-brand">{{ $trx->project_code }}</td>
                            <td class="px-4 py-3.5 font-mono text-slate-700">{{ $trx->project_details_id ?: '—' }}</td>
                            <td class="px-4 py-3.5">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                    {{ $trx->project_name }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5 font-bold text-neutral-strong">
                                {{ $trx->last_name }}, {{ $trx->first_name }}
                                <span class="block text-[10px] text-slate-500 font-mono font-normal">{{ $trx->civil_registry_id }}</span>
                            </td>
                            <td class="px-4 py-3.5 text-slate-600">
                                {{ $trx->barangay }}
                                <span class="block text-[10px] text-slate-400 font-mono">HH: {{ $trx->household_no }}</span>
                            </td>
                            <td class="px-4 py-3.5 text-right font-mono font-bold text-brand">₱{{ number_format($trx->amount, 2) }}</td>
                            <td class="px-4 py-3.5 font-mono text-slate-500 text-[11px]">{{ $trx->disbursement_date->format('M d, Y h:i A') }}</td>
                            <td class="px-4 py-3.5 text-center">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-brand border border-emerald-200">
                                    {{ $trx->sync_status }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5 text-center">
                                <button 
                                    wire:click="inspectTransaction({{ $trx->id }})"
                                    class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-[11px] font-bold border border-slate-200 transition-colors cursor-pointer"
                                >
                                    Inspect
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-12 text-slate-400">No consolidated transactions found in database matching criteria.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Mandatory 25-Per-Page Pagination -->
        <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
            <span class="text-xs text-slate-500 font-mono">Showing 25 records per page</span>
            {{ $transactions->links() }}
        </div>
    </div>

    <!-- SLIDE-OUT DETAIL INSPECTOR PANEL -->
    <div 
        x-show="$wire.showInspector"
        class="fixed inset-0 z-50 overflow-hidden bg-[#0F172A]/80 backdrop-blur-md"
        style="display: none;"
    >
        <div class="absolute inset-y-0 right-0 max-w-full flex pl-10">
            <div 
                @click.away="$wire.closeInspector()"
                class="w-screen max-w-md bg-white border-l border-slate-200 p-6 flex flex-col justify-between shadow-2xl space-y-4 text-neutral-strong"
            >
                <div class="space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-brand"></span>
                            <h3 class="text-base font-bold text-brand tracking-tight">Transaction Audit Inspector</h3>
                        </div>
                        <button wire:click="closeInspector" class="text-slate-400 hover:text-neutral-strong text-xl font-bold cursor-pointer">&times;</button>
                    </div>

                    @if($inspectedRecord)
                        <!-- Identity & Codes -->
                        <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 space-y-2 text-xs font-mono">
                            <div class="flex justify-between">
                                <span class="text-slate-500 font-sans font-bold">Project Code:</span>
                                <span class="font-bold text-brand">{{ $inspectedRecord->project_code }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-500 font-sans font-bold">GGMS Grant Code:</span>
                                <span class="text-slate-700">{{ $inspectedRecord->project_details_id ?: 'N/A' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-500 font-sans font-bold">Disbursement Amount:</span>
                                <span class="font-bold text-brand text-sm">₱{{ number_format($inspectedRecord->amount, 2) }}</span>
                            </div>
                        </div>

                        <!-- Beneficiary Metadata -->
                        <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 space-y-2 text-xs">
                            <h4 class="font-bold uppercase tracking-wider text-slate-500 text-[10px]">Beneficiary Register Details</h4>
                            <p class="font-bold text-neutral-strong">{{ $inspectedRecord->last_name }}, {{ $inspectedRecord->first_name }} {{ $inspectedRecord->middle_name }}</p>
                            <p class="text-slate-500 font-mono text-[11px]">CRN: {{ $inspectedRecord->civil_registry_id }}</p>
                            <p class="text-slate-500 font-mono text-[11px]">Household No: {{ $inspectedRecord->household_no }}</p>
                            <p class="text-slate-500 text-[11px]">Barangay: {{ $inspectedRecord->barangay }}</p>
                        </div>

                        <!-- Forensic Timestamps -->
                        <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 space-y-2 text-xs">
                            <h4 class="font-bold uppercase tracking-wider text-slate-500 text-[10px]">Audit Timestamps</h4>
                            <p class="text-slate-700">Disbursed: <span class="font-mono font-bold text-neutral-strong">{{ $inspectedRecord->disbursement_date->format('M d, Y h:i:s A') }}</span></p>
                            <p class="text-slate-700">Sync Status: <span class="text-brand font-bold">{{ $inspectedRecord->sync_status }}</span></p>
                            <p class="text-slate-700">Recorded By: <span class="text-slate-900 font-medium">{{ $inspectedRecord->recorder?->name ?? 'Admin Officer' }}</span></p>
                        </div>

                        <!-- Raw Payload JSON Snapshot -->
                        @if($inspectedRecord->payload)
                            <div class="space-y-1">
                                <h4 class="font-bold uppercase tracking-wider text-slate-500 text-[10px]">Raw Payload Snapshot</h4>
                                <pre class="bg-slate-900 text-emerald-400 border border-slate-800 rounded-xl p-3 text-[10px] font-mono overflow-x-auto max-h-40">{{ json_encode($inspectedRecord->payload, JSON_PRETTY_PRINT) }}</pre>
                            </div>
                        @endif
                    @endif
                </div>

                <div class="pt-3 border-t border-slate-100">
                    <button wire:click="closeInspector" class="w-full py-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold cursor-pointer">
                        Close Inspector
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
