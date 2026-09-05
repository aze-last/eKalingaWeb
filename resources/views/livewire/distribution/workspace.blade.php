<div class="space-y-6" x-data="scannerComponent()" x-init="initScanner()">
    <!-- Top Action & Info Banner -->
    <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4 bg-white p-6 rounded-xl border border-slate-200 shadow-xs">
        <div class="flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-emerald-50 border border-emerald-200 text-brand flex items-center justify-center shrink-0 shadow-xs">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-2xl font-bold text-brand tracking-tight">Project Distribution Terminal</h1>
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-brand border border-emerald-200 flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-brand animate-ping"></span>
                        Scanner Armed
                    </span>
                </div>
                <p class="text-xs text-slate-500 mt-0.5">Point hardware scanner or type Civil Registry ID / Household #. Unfocused input auto-routes to scanner.</p>
            </div>
        </div>

        <!-- Right Quick Controls -->
        <div class="flex flex-wrap items-center gap-2.5 w-full lg:w-auto">
            <!-- Project Picker Dropdown -->
            <select 
                wire:model.live="selectedProjectId"
                class="bg-white border border-slate-300 rounded-lg px-3 py-2 text-xs text-neutral-strong font-bold focus:outline-none focus:ring-1 focus:ring-brand cursor-pointer"
            >
                <option value="">-- Select a Project --</option>
                @foreach($activeProjects as $proj)
                    <option value="{{ $proj->id }}">
                        {{ $proj->program_code }} - {{ $proj->title }}
                    </option>
                @endforeach
            </select>

            <button
                wire:click="openProjectPicker"
                class="px-3.5 py-2 rounded-lg {{ $selectedProjectId ? 'bg-slate-100 hover:bg-slate-200 text-neutral-strong border border-slate-200' : 'bg-brand hover:bg-emerald-800 text-white border border-emerald-700' }} text-xs font-bold transition-colors flex items-center gap-1.5 cursor-pointer shadow-xs"
                title="Open project search picker"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                <span>{{ $selectedProjectId ? 'Change Project' : 'Select Project' }}</span>
            </button>

            <button 
                wire:click="openBeneficiaryPicker"
                wire:loading.attr="disabled"
                wire:target="openBeneficiaryPicker"
                class="px-3.5 py-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-xs font-bold text-neutral-strong border border-slate-200 transition-colors flex items-center gap-1.5 cursor-pointer shadow-xs disabled:opacity-75"
            >
                <svg wire:loading.remove wire:target="openBeneficiaryPicker" class="w-4 h-4 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                <svg wire:loading wire:target="openBeneficiaryPicker" class="animate-spin h-4 w-4 text-brand" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                <span wire:loading.remove wire:target="openBeneficiaryPicker">+ Enlist Beneficiary</span>
                <span wire:loading wire:target="openBeneficiaryPicker">Loading Registry...</span>
            </button>

            @if($selectedProjectId)
                <a 
                    href="{{ route('distribution.live-preview', ['project' => $selectedProjectId]) }}" 
                    target="_blank"
                    class="px-3.5 py-2 rounded-lg bg-accent hover:bg-amber-400 text-neutral-strong text-xs font-bold transition-colors flex items-center gap-1.5 cursor-pointer shadow-xs uppercase tracking-wide"
                    title="Open Live Public Queue Announcement Window"
                >
                    <svg class="w-4 h-4 text-neutral-strong" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    <span>Live Display</span>
                </a>
            @endif
        </div>
    </div>

    @if($currentProject)
        <!-- Project Stats Ribbon -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 bg-white border border-slate-200 rounded-xl p-4 text-xs font-mono shadow-xs">
            <div>
                <span class="text-slate-500 text-[10px] block font-sans uppercase font-bold">Budget Cap</span>
                <span class="text-neutral-strong font-black text-sm">₱{{ number_format($currentProject->budget_cap, 2) }}</span>
            </div>
            <div>
                <span class="text-slate-500 text-[10px] block font-sans uppercase font-bold">Disbursed Value</span>
                <span class="text-brand font-black text-sm">₱{{ number_format($currentProject->total_disbursed_amount, 2) }}</span>
            </div>
            <div>
                <span class="text-slate-500 text-[10px] block font-sans uppercase font-bold">Aid Package</span>
                <span class="text-neutral-strong font-bold font-sans">
                    {{ $currentProject->benefit_type->value === 'Cash' ? '₱'.number_format($currentProject->unit_amount, 2).' Cash' : "{$currentProject->item_quantity_per_beneficiary} {$currentProject->item_unit} {$currentProject->item_name}" }}
                </span>
            </div>
            <div>
                <span class="text-slate-500 text-[10px] block font-sans uppercase font-bold">Target Coverage</span>
                <span class="text-slate-700 font-bold font-sans">{{ $currentProject->target_barangay ?: 'Municipality-Wide' }} ({{ $currentProject->target_beneficiaries }} pax)</span>
            </div>
        </div>

        <!-- POS Scanner Input Bar -->
        <div class="bg-white border-2 border-brand/40 rounded-xl p-3 flex items-center gap-3 shadow-xs">
            <div class="text-brand pl-2">
                <svg class="w-5 h-5 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
            </div>
            <input 
                x-ref="scannerField"
                type="text" 
                placeholder="Scan QR barcode or enter Civil Registry ID / Household # (Press Enter to Disburse)..."
                @keydown.enter.prevent="submitManualScan($event.target.value); $event.target.value = '';"
                class="flex-1 bg-transparent border-none text-neutral-strong text-sm placeholder-slate-400 focus:outline-none font-mono font-bold"
                autocomplete="off"
            >
            <span class="text-[11px] font-mono text-brand font-bold uppercase tracking-wider px-2.5 py-1 bg-emerald-50 rounded-md border border-emerald-200">
                READY
            </span>
        </div>

        <!-- 3-Column Bucket Layout Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
            
            <!-- COLUMN 1: RELEASED / CLAIMED (Left - Green) -->
            <div class="bg-surface border border-emerald-200 rounded-xl p-5 shadow-xs flex flex-col h-[640px]">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-brand"></span>
                        <h3 class="text-sm font-bold text-neutral-strong tracking-tight">Claimed / Released</h3>
                    </div>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-mono font-bold bg-emerald-50 text-brand border border-emerald-200">
                        {{ $releasedList->total() }}
                    </span>
                </div>

                <div class="py-2.5 relative">
                    <input 
                        wire:model.live.debounce.300ms="releasedSearch"
                        type="text" 
                        placeholder="Search claimed recipients..."
                        class="w-full bg-white border border-slate-300 rounded-lg px-3 py-1.5 text-xs text-neutral-strong placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-brand pr-8"
                    >
                    <div wire:loading wire:target="releasedSearch" class="absolute right-2.5 top-4">
                        <svg class="animate-spin h-3.5 w-3.5 text-brand" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                    </div>
                </div>

                <!-- Scrollable List -->
                <div class="flex-1 overflow-y-auto space-y-2.5 pr-1 divide-y divide-slate-100">
                    @forelse($releasedList as $item)
                        <div wire:key="released-item-{{ $item->id }}" class="pt-2.5 first:pt-0">
                            <div class="bg-emerald-50/40 hover:bg-emerald-50/70 border border-emerald-200/80 rounded-xl p-3 transition-colors">
                                <div class="flex items-start justify-between gap-2">
                                    <div>
                                        <p class="font-bold text-neutral-strong text-xs">{{ $item->beneficiary?->full_name }}</p>
                                        <p class="text-[10px] text-slate-500 font-mono">{{ $item->beneficiary?->civil_registry_id }} • HH: {{ $item->beneficiary?->household_no }}</p>
                                    </div>
                                    <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-emerald-100 text-brand border border-emerald-200">CLAIMED</span>
                                </div>
                                <div class="mt-2 flex items-center justify-between text-[10px] text-slate-500 pt-1.5 border-t border-emerald-100">
                                    <span>Brgy. {{ $item->beneficiary?->barangay }}</span>
                                    <span class="font-mono font-bold text-brand">{{ $item->processed_at?->format('h:i A') }}</span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-16 text-slate-400 text-xs font-medium">
                            No claimed beneficiaries yet.
                        </div>
                    @endforelse
                </div>

                <div class="pt-3 border-t border-slate-100 text-xs">
                    {{ $releasedList->links('components.pagination') }}
                </div>
            </div>

            <!-- COLUMN 2: PENDING / QUEUED (Center - Amber) -->
            <div class="bg-surface border border-amber-200 rounded-xl p-5 shadow-xs flex flex-col h-[640px]">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-accent animate-pulse"></span>
                        <h3 class="text-sm font-bold text-neutral-strong tracking-tight">Pending / Queued</h3>
                    </div>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-mono font-bold bg-amber-50 text-warning border border-amber-200">
                        {{ $pendingList->total() }}
                    </span>
                </div>

                <div class="py-2.5 relative">
                    <input 
                        wire:model.live.debounce.300ms="pendingSearch"
                        type="text" 
                        placeholder="Search queued beneficiaries..."
                        class="w-full bg-white border border-slate-300 rounded-lg px-3 py-1.5 text-xs text-neutral-strong placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-brand pr-8"
                    >
                    <div wire:loading wire:target="pendingSearch" class="absolute right-2.5 top-4">
                        <svg class="animate-spin h-3.5 w-3.5 text-brand" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                    </div>
                </div>

                <!-- Scrollable List -->
                <div class="flex-1 overflow-y-auto space-y-2.5 pr-1 divide-y divide-slate-100">
                    @forelse($pendingList as $item)
                        <div wire:key="pending-item-{{ $item->id }}" class="pt-2.5 first:pt-0">
                            <div class="bg-slate-50 hover:bg-slate-100 border border-slate-200 rounded-xl p-3 space-y-2 transition-all">
                                <div class="flex items-start justify-between gap-2">
                                    <div>
                                        <p class="font-bold text-neutral-strong text-xs">{{ $item->beneficiary?->full_name }}</p>
                                        <p class="text-[10px] text-slate-500 font-mono">{{ $item->beneficiary?->civil_registry_id }} • HH: {{ $item->beneficiary?->household_no }}</p>
                                    </div>
                                    <span class="text-[10px] text-slate-500">Brgy. {{ $item->beneficiary?->barangay }}</span>
                                </div>

                                <!-- Action Buttons -->
                                <div class="flex items-center justify-between gap-2 pt-1 border-t border-slate-200">
                                    <button 
                                        type="button"
                                        wire:key="btn-exclude-{{ $item->id }}"
                                        wire:click="moveToUnreleased({{ $item->id }})"
                                        wire:loading.attr="disabled"
                                        wire:target="moveToUnreleased({{ $item->id }})"
                                        class="px-2 py-1 rounded bg-rose-50 hover:bg-rose-100 text-error border border-rose-200 text-[10px] font-bold transition-colors cursor-pointer flex items-center gap-1 disabled:opacity-75"
                                    >
                                        <svg wire:loading wire:target="moveToUnreleased({{ $item->id }})" class="animate-spin h-2.5 w-2.5 text-error" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                                        <span wire:loading.remove wire:target="moveToUnreleased({{ $item->id }})">Exclude</span>
                                        <span wire:loading wire:target="moveToUnreleased({{ $item->id }})">Excluding...</span>
                                    </button>
                                    <button 
                                        type="button"
                                        wire:key="btn-disburse-{{ $item->id }}"
                                        wire:click="releaseBeneficiary({{ $item->id }})"
                                        wire:loading.attr="disabled"
                                        wire:target="releaseBeneficiary({{ $item->id }})"
                                        class="px-3 py-1 rounded-lg bg-brand hover:bg-emerald-700 text-white text-[11px] font-bold shadow-xs transition-all cursor-pointer flex items-center gap-1 disabled:opacity-75"
                                    >
                                        <svg wire:loading wire:target="releaseBeneficiary({{ $item->id }})" class="animate-spin h-3 w-3 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                                        <span wire:loading.remove wire:target="releaseBeneficiary({{ $item->id }})" class="flex items-center gap-1">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                            <span>Disburse</span>
                                        </span>
                                        <span wire:loading wire:target="releaseBeneficiary({{ $item->id }})">Disbursing...</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-10 px-3 space-y-3">
                            <div class="w-10 h-10 mx-auto rounded-xl bg-amber-50 border border-amber-200 text-warning flex items-center justify-center">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                            </div>
                            <p class="text-xs font-bold text-neutral-strong">No beneficiaries in distribution queue yet.</p>
                            <p class="text-[11px] text-slate-500">Auto-fill target {{ $currentProject->target_beneficiaries }} slots from CRS or enlist individual citizens.</p>
                            <div class="flex flex-col gap-1.5 pt-1">
                                <button 
                                    wire:click="autoEnrollBeneficiariesForProject"
                                    wire:loading.attr="disabled"
                                    wire:target="autoEnrollBeneficiariesForProject"
                                    class="w-full px-3 py-1.5 rounded-lg bg-brand hover:bg-emerald-700 text-white text-xs font-bold shadow-xs cursor-pointer flex items-center justify-center gap-1.5 disabled:opacity-75"
                                >
                                    <svg wire:loading wire:target="autoEnrollBeneficiariesForProject" class="animate-spin h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                                    <span wire:loading.remove wire:target="autoEnrollBeneficiariesForProject">⚡ Auto-Fill {{ $currentProject->target_beneficiaries }} Target Slots</span>
                                    <span wire:loading wire:target="autoEnrollBeneficiariesForProject">Enrolling...</span>
                                </button>
                                <button 
                                    wire:click="openBeneficiaryPicker"
                                    wire:loading.attr="disabled"
                                    wire:target="openBeneficiaryPicker"
                                    class="w-full px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold border border-slate-200 cursor-pointer disabled:opacity-75"
                                >
                                    + Enlist Manually
                                </button>
                            </div>
                        </div>
                    @endforelse
                </div>

                <div class="pt-3 border-t border-slate-100 text-xs">
                    {{ $pendingList->links('components.pagination') }}
                </div>
            </div>

            <!-- COLUMN 3: UNRELEASED / EXCLUDED (Right - Rose) -->
            <div class="bg-surface border border-rose-200 rounded-xl p-5 shadow-xs flex flex-col h-[640px]">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-error"></span>
                        <h3 class="text-sm font-bold text-neutral-strong tracking-tight">Unreleased / Excluded</h3>
                    </div>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-mono font-bold bg-rose-50 text-error border border-rose-200">
                        {{ $unreleasedList->total() }}
                    </span>
                </div>

                <div class="py-2.5 relative">
                    <input 
                        wire:model.live.debounce.300ms="unreleasedSearch"
                        type="text" 
                        placeholder="Search excluded candidates..."
                        class="w-full bg-white border border-slate-300 rounded-lg px-3 py-1.5 text-xs text-neutral-strong placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-rose-500 pr-8"
                    >
                    <div wire:loading wire:target="unreleasedSearch" class="absolute right-2.5 top-4">
                        <svg class="animate-spin h-3.5 w-3.5 text-rose-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                    </div>
                </div>

                <!-- Scrollable List -->
                <div class="flex-1 overflow-y-auto space-y-2.5 pr-1 divide-y divide-slate-100">
                    @forelse($unreleasedList as $item)
                        <div wire:key="unreleased-item-{{ $item->id }}" class="pt-2.5 first:pt-0">
                            <div class="bg-rose-50/40 hover:bg-rose-50/70 border border-rose-200 rounded-xl p-3 space-y-2 transition-colors">
                                <div>
                                    <p class="font-bold text-neutral-strong text-xs">{{ $item->beneficiary?->full_name }}</p>
                                    <p class="text-[10px] text-slate-500 font-mono">{{ $item->beneficiary?->civil_registry_id }} • Brgy. {{ $item->beneficiary?->barangay }}</p>
                                    @if($item->exclusion_reason)
                                        <p class="text-[10px] text-error mt-1 font-medium italic">{{ $item->exclusion_reason }}</p>
                                    @endif
                                </div>

                                <div class="flex justify-end pt-1 border-t border-rose-100">
                                    <button 
                                        type="button"
                                        wire:key="btn-restore-{{ $item->id }}"
                                        wire:click="moveToPending({{ $item->id }})"
                                        wire:loading.attr="disabled"
                                        wire:target="moveToPending({{ $item->id }})"
                                        class="px-2.5 py-1 rounded bg-slate-100 hover:bg-slate-200 text-slate-700 text-[10px] font-bold border border-slate-200 transition-colors cursor-pointer flex items-center gap-1 disabled:opacity-75"
                                    >
                                        <svg wire:loading wire:target="moveToPending({{ $item->id }})" class="animate-spin h-2.5 w-2.5 text-slate-700" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                                        <span wire:loading.remove wire:target="moveToPending({{ $item->id }})">Restore to Queue</span>
                                        <span wire:loading wire:target="moveToPending({{ $item->id }})">Restoring...</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-16 text-slate-400 text-xs font-medium">
                            No excluded candidates.
                        </div>
                    @endforelse
                </div>

                <div class="pt-3 border-t border-slate-100 text-xs">
                    {{ $unreleasedList->links('components.pagination') }}
                </div>
            </div>
        </div>
    @else
        <div class="bg-surface border border-slate-200 rounded-xl p-12 text-center space-y-5">
            <div class="w-14 h-14 mx-auto rounded-2xl bg-emerald-50 border border-emerald-200 text-brand flex items-center justify-center">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            </div>
            <div>
                <p class="font-bold text-neutral-strong text-sm">No Active Project Selected</p>
                <p class="text-xs text-slate-500 mt-1">Select an active Ayuda Program to launch the POS distribution terminal.</p>
            </div>
            <button
                wire:click="openProjectPicker"
                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-brand hover:bg-emerald-800 text-white text-xs font-bold shadow-xs cursor-pointer transition-colors"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                Select Project
            </button>
        </div>
    @endif

    <!-- MODAL 1: PROJECT PICKER -->
    @if($showProjectPickerModal)
    <div
        x-data
        @keydown.escape.window="$wire.closeProjectPicker()"
        @keydown.enter.window="$wire.confirmProjectSelection()"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-[#0F172A]/85 backdrop-blur-md animate-fadeIn"
    >
        <div class="w-full max-w-lg bg-white border border-slate-200 rounded-2xl shadow-xl flex flex-col text-neutral-strong" style="max-height: 80vh;">

            <!-- Header -->
            <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100 shrink-0">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-brand"></span>
                    <h3 class="text-base font-bold text-brand">Select Active Project</h3>
                </div>
                <button wire:click="closeProjectPicker" class="text-slate-400 hover:text-neutral-strong text-xl font-bold cursor-pointer" title="Cancel (Esc)">&times;</button>
            </div>

            <!-- Search Field (auto-focused when modal opens) -->
            <div class="px-5 py-3 border-b border-slate-100 shrink-0">
                <div class="relative">
                    <svg class="absolute left-3 top-2.5 w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input
                        wire:model.live.debounce.200ms="projectPickerSearch"
                        type="text"
                        id="project-picker-search"
                        placeholder="Search by project name or code..."
                        class="w-full bg-white border border-slate-300 rounded-lg pl-9 pr-8 py-2 text-xs text-neutral-strong placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-brand"
                        x-init="setTimeout(() => $el.focus(), 50)"
                        autocomplete="off"
                    >
                    <div wire:loading wire:target="projectPickerSearch" class="absolute right-2.5 top-2.5">
                        <svg class="animate-spin h-3.5 w-3.5 text-brand" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                    </div>
                </div>
            </div>

            <!-- Program List: click to highlight, double-click to confirm (requirement 7) -->
            <div class="flex-1 overflow-y-auto min-h-0 divide-y divide-slate-100">
                @forelse($pickerPrograms as $proj)
                    <div
                        wire:click="highlightProject({{ $proj->id }})"
                        wire:dblclick="selectProject({{ $proj->id }})"
                        class="flex items-center gap-3 px-5 py-3.5 cursor-pointer transition-colors select-none
                            {{ $projectPickerHighlightedId === $proj->id
                                ? 'bg-emerald-50 border-l-4 border-brand'
                                : 'hover:bg-slate-50 border-l-4 border-transparent' }}"
                    >
                        <div class="flex-1 min-w-0">
                            <p class="font-bold text-neutral-strong text-xs truncate">{{ $proj->title }}</p>
                            <p class="text-[10px] text-slate-500 font-mono mt-0.5">
                                {{ $proj->program_code }}
                                &middot; {{ $proj->target_beneficiaries }} slots
                                &middot; {{ $proj->target_barangay ?: 'Municipality-Wide' }}
                            </p>
                        </div>
                        @if($projectPickerHighlightedId === $proj->id)
                            <svg class="w-4 h-4 text-brand shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        @endif
                    </div>
                @empty
                    <div class="text-center py-12 text-slate-400 text-xs font-medium">
                        @if($projectPickerSearch)
                            No active projects match &ldquo;{{ $projectPickerSearch }}&rdquo;.
                        @else
                            No active Ayuda Programs found. Create one in Budget Management first.
                        @endif
                    </div>
                @endforelse
            </div>

            <!-- Footer -->
            <div class="flex items-center justify-between px-5 py-3.5 border-t border-slate-100 shrink-0 bg-slate-50/60 rounded-b-2xl">
                <span class="text-[10px] text-slate-400 font-mono">{{ $pickerPrograms->count() }} active program(s)</span>
                <div class="flex items-center gap-2">
                    <button
                        type="button"
                        wire:click="closeProjectPicker"
                        class="px-4 py-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold cursor-pointer transition-colors"
                    >
                        Cancel
                    </button>
                    <!-- Disabled when no row is highlighted (requirement 6) -->
                    <button
                        type="button"
                        wire:click="confirmProjectSelection"
                        wire:loading.attr="disabled"
                        wire:target="confirmProjectSelection"
                        @disabled($projectPickerHighlightedId === null)
                        class="px-5 py-2 rounded-lg bg-brand hover:bg-emerald-800 text-white text-xs font-bold cursor-pointer shadow-xs transition-colors flex items-center gap-1.5 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <svg wire:loading wire:target="confirmProjectSelection" class="animate-spin h-3 w-3 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                        <span wire:loading.remove wire:target="confirmProjectSelection">Select Project</span>
                        <span wire:loading wire:target="confirmProjectSelection">Loading...</span>
                    </button>
                </div>
            </div>

        </div>
    </div>
    @endif

    <!-- MODAL 2: HOUSEHOLD DUPLICATE WARNING MODAL -->
    @if($showDuplicateWarningModal)
    <div 
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-[#0F172A]/85 backdrop-blur-md animate-fadeIn"
    >
        <div class="w-full max-w-lg bg-white border-2 border-amber-400 rounded-2xl shadow-2xl p-6 space-y-4 text-neutral-strong">
            <div class="flex items-center gap-3 text-warning">
                <div class="w-10 h-10 rounded-xl bg-amber-50 border border-amber-200 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6 text-warning" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h3 class="text-base font-bold text-neutral-strong">Household Duplicate Warning</h3>
                        @if(!empty($duplicateWarningData['household_no']) && $duplicateWarningData['household_no'] !== 'N/A')
                            <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-amber-100 text-amber-900 border border-amber-300">
                                HH #{{ $duplicateWarningData['household_no'] }}
                            </span>
                        @endif
                    </div>
                    <p class="text-xs text-warning font-medium">Cross-member assistance detected for this household</p>
                </div>
            </div>

            <div class="bg-amber-50/70 border border-amber-200 rounded-xl p-4 text-xs text-slate-700 space-y-2.5">
                <p class="font-bold text-amber-900">{{ $duplicateWarningData['message'] ?? '' }}</p>
                
                @if(!empty($duplicateWarningData['existing_claims']))
                    <div class="space-y-1.5 pt-2 border-t border-amber-200/80">
                        <span class="text-[10px] font-bold text-amber-900 uppercase tracking-wider block">Prior Household Claims on Record:</span>
                        <div class="space-y-1.5 max-h-48 overflow-y-auto">
                            @foreach($duplicateWarningData['existing_claims'] as $prior)
                                <div class="p-2.5 rounded-lg bg-white border border-amber-200 text-[11px] space-y-1 shadow-2xs">
                                    <div class="flex items-center justify-between">
                                        <span class="font-bold text-neutral-strong">{{ $prior['member_name'] }}</span>
                                        <span class="font-mono font-bold text-brand">₱{{ number_format((float)$prior['amount'], 2) }}</span>
                                    </div>
                                    <div class="flex items-center justify-between text-[10px] text-slate-500 font-mono">
                                        <span>{{ $prior['program'] }} ({{ $prior['claim_code'] }})</span>
                                        <span class="text-warning font-bold">{{ $prior['date'] }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <p class="text-xs text-slate-600">
                Do you wish to authorize an administrative release override for this beneficiary?
            </p>

            <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                <button 
                    type="button" 
                    wire:click="cancelOverrideRelease" 
                    wire:loading.attr="disabled"
                    class="px-4 py-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold cursor-pointer disabled:opacity-75"
                >
                    Reject / Cancel
                </button>
                <button 
                    type="button" 
                    wire:click="confirmOverrideRelease" 
                    wire:loading.attr="disabled"
                    wire:target="confirmOverrideRelease"
                    class="px-4 py-2 rounded-lg bg-accent hover:bg-amber-400 text-neutral-strong text-xs font-bold cursor-pointer flex items-center gap-1.5 disabled:opacity-75"
                >
                    <svg wire:loading wire:target="confirmOverrideRelease" class="animate-spin h-3.5 w-3.5 text-neutral-strong" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                    <span wire:loading.remove wire:target="confirmOverrideRelease">Authorize Override Release</span>
                    <span wire:loading wire:target="confirmOverrideRelease">Authorizing & Disbursing...</span>
                </button>
            </div>
        </div>
    </div>
    @endif

    <!-- MODAL 3: BENEFICIARY ENLISTMENT PICKER -->
    @if($showBeneficiaryPickerModal)
    <div 
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-[#0F172A]/80 backdrop-blur-md animate-fadeIn"
    >
        <div class="w-full max-w-2xl bg-white border border-slate-200 rounded-2xl shadow-xl p-6 space-y-4 text-neutral-strong">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-brand"></span>
                    <h3 class="text-base font-bold text-brand">Enlist Masterlist Beneficiary</h3>
                </div>
                <button wire:click="closeBeneficiaryPicker" class="text-slate-400 hover:text-neutral-strong text-xl font-bold cursor-pointer">&times;</button>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div class="relative">
                    <input 
                        wire:model.live.debounce.300ms="pickerSearch"
                        type="text" 
                        placeholder="Search name or civil registry ID..."
                        class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-xs text-neutral-strong placeholder-slate-400 focus:ring-brand focus:border-brand pr-8"
                    >
                    <div wire:loading wire:target="pickerSearch" class="absolute right-2.5 top-2.5">
                        <svg class="animate-spin h-3.5 w-3.5 text-brand" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                    </div>
                </div>
                <select wire:model.live="pickerBarangay" class="bg-white border border-slate-300 rounded-lg px-3 py-2 text-xs text-neutral-strong cursor-pointer">
                    <option value="">All Barangays</option>
                    @foreach($barangays as $b)
                        <option value="{{ $b }}">{{ $b }}</option>
                    @endforeach
                </select>
            </div>

            <div class="max-h-72 overflow-y-auto divide-y divide-slate-100 text-xs relative">
                <!-- Search Loading Overlay for Picker -->
                <div wire:loading.flex wire:target="pickerSearch, pickerBarangay" class="absolute inset-0 bg-white/80 backdrop-blur-xs z-10 flex-col items-center justify-center gap-2">
                    <svg class="animate-spin h-6 w-6 text-brand" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                    </svg>
                    <span class="text-xs font-bold text-neutral-strong">Searching CRS Masterlist...</span>
                </div>

                @forelse($pickerBeneficiaries as $ben)
                    <div wire:key="picker-ben-{{ $ben->id }}" class="py-2.5 flex items-center justify-between gap-3">
                        <div>
                            <p class="font-bold text-neutral-strong">{{ $ben->full_name }}</p>
                            <p class="text-[10px] text-slate-500 font-mono">{{ $ben->civil_registry_id }} • HH: {{ $ben->household_no }} • Brgy. {{ $ben->barangay }}</p>
                        </div>
                        <button 
                            type="button"
                            wire:key="btn-enroll-ben-{{ $ben->id }}"
                            wire:click="enrollBeneficiary({{ $ben->id }})"
                            wire:loading.attr="disabled"
                            wire:target="enrollBeneficiary({{ $ben->id }})"
                            class="px-3 py-1 rounded-lg bg-brand hover:bg-emerald-700 text-white font-bold text-[11px] cursor-pointer flex items-center gap-1 disabled:opacity-75"
                        >
                            <svg wire:loading wire:target="enrollBeneficiary({{ $ben->id }})" class="animate-spin h-3 w-3 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                            <span wire:loading.remove wire:target="enrollBeneficiary({{ $ben->id }})">+ Enlist to Queue</span>
                            <span wire:loading wire:target="enrollBeneficiary({{ $ben->id }})">Enlisting...</span>
                        </button>
                    </div>
                @empty
                    <p class="text-center py-6 text-slate-400">No beneficiaries found in database.</p>
                @endforelse
            </div>
        </div>
    </div>
    @endif

    <!-- GLOBAL LIVEWIRE ACTIVITY STATUS BADGE (Fixed Bottom Right) -->
    <div 
        wire:loading 
        class="fixed bottom-5 right-5 z-[99] bg-[#0F172A]/90 backdrop-blur-md text-white text-xs font-bold px-4 py-2.5 rounded-full shadow-2xl border border-slate-700 flex items-center gap-2.5 animate-fadeIn pointer-events-none"
    >
        <svg class="animate-spin h-4 w-4 text-emerald-400" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
        </svg>
        <span>Processing Request...</span>
    </div>
</div>

<script>
    function scannerComponent() {
        return {
            buffer: '',
            lastKeyTime: 0,
            lastProcessedTime: 0,
            initScanner() {
                // Keyboard Wedge hardware scanner listener (HID Emulation)
                window.addEventListener('keydown', (e) => {
                    // Do not intercept if user is typing in a modal search input or form field
                    if (e.target.tagName === 'TEXTAREA' || (e.target.tagName === 'INPUT' && e.target !== this.$refs.scannerField)) {
                        return;
                    }

                    const currentTime = Date.now();
                    const timeDiff = currentTime - this.lastKeyTime;

                    // Stale buffer eviction: If time between keystrokes > 75ms, reset stale buffer
                    if (timeDiff > 75) {
                        this.buffer = '';
                    }
                    this.lastKeyTime = currentTime;

                    if (e.key === 'Enter') {
                        // Debounce duplicate Enter signals within 400ms
                        if (currentTime - this.lastProcessedTime < 400) {
                            this.buffer = '';
                            return;
                        }

                        const payload = this.buffer.trim();
                        if (payload.length >= 2) {
                            this.lastProcessedTime = currentTime;
                            @this.handleScan(payload);
                            this.buffer = '';
                        }
                    } else if (e.key.length === 1 && !e.ctrlKey && !e.altKey && !e.metaKey) {
                        this.buffer += e.key;
                    }
                });
            },
            submitManualScan(value) {
                const clean = value ? value.trim() : '';
                const currentTime = Date.now();
                if (clean.length > 0 && (currentTime - this.lastProcessedTime >= 400)) {
                    this.lastProcessedTime = currentTime;
                    @this.handleScan(clean);
                }
            }
        };
    }
</script>
