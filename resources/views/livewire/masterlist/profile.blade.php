<div class="space-y-6">
    <!-- Top Back Navigation & Header -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 bg-white p-6 rounded-xl border border-slate-200 shadow-xs">
        <div class="flex items-center gap-4">
            <a 
                href="{{ route('masterlist') }}" 
                wire:navigate 
                class="p-2.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 transition-colors cursor-pointer border border-slate-200"
                title="Back to Masterlist"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            </a>
            <div>
                <div class="flex items-center gap-2.5">
                    <h1 class="text-2xl font-bold text-brand tracking-tight">Beneficiary Profile</h1>
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-brand border border-emerald-200">
                        CRS Verified Citizen
                    </span>
                </div>
                <p class="text-xs text-slate-500 mt-0.5">
                    Civil Registry Record #<span class="font-mono font-bold text-neutral-strong">{{ $civilRegistryId }}</span> • Live consolidated claims history
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2.5">
            <a 
                href="{{ route('masterlist') }}" 
                wire:navigate 
                class="px-3.5 py-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-xs font-bold text-neutral-strong border border-slate-200 transition-colors flex items-center gap-1.5 cursor-pointer shadow-xs"
            >
                <span>&larr; Back to Masterlist</span>
            </a>
        </div>
    </div>

    @if($connectionError && !$beneficiary)
        <div class="p-4 rounded-xl border border-amber-300 bg-amber-50 text-warning text-xs space-y-1">
            <div class="flex items-center gap-2 font-bold">
                <svg class="w-4 h-4 text-warning shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <span>External CRS Database Connection Notice</span>
            </div>
            <p class="text-slate-700 font-mono text-[11px]">{{ $connectionError }}</p>
        </div>
    @endif

    <!-- 2-Column Profile Layout: Identity Card & Summary Metrics -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        
        <!-- Left: CRS Citizen Identity Card -->
        <div class="lg:col-span-5 bg-white border border-slate-200 rounded-xl p-6 shadow-xs space-y-5">
            <div class="flex items-start justify-between border-b border-slate-100 pb-4">
                <div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Civil Registry Identity</span>
                    <h2 class="text-lg font-black text-neutral-strong mt-0.5">
                        {{ $beneficiary?->full_name ?? ('Citizen Record #' . $civilRegistryId) }}
                    </h2>
                    <p class="text-xs text-brand font-bold mt-0.5 font-mono">
                        CRN: {{ $beneficiary?->civil_registry_id ?: $civilRegistryId }}
                    </p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-brand flex items-center justify-center font-bold text-xs shrink-0 border border-emerald-200 shadow-xs">
                    {{ substr($beneficiary?->full_name ?? 'CR', 0, 2) }}
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4 text-xs">
                <div>
                    <span class="text-slate-400 text-[10px] font-bold uppercase block">Barangay</span>
                    <span class="font-bold text-neutral-strong">{{ $beneficiary?->barangay ?? 'N/A' }}</span>
                </div>
                <div>
                    <span class="text-slate-400 text-[10px] font-bold uppercase block">Household No.</span>
                    <span class="font-mono font-bold text-neutral-strong">{{ $beneficiary?->household_no ?? 'N/A' }}</span>
                </div>
                <div>
                    <span class="text-slate-400 text-[10px] font-bold uppercase block">Gender</span>
                    <span class="font-medium text-slate-700">{{ $beneficiary?->gender ?? $beneficiary?->sex ?? 'N/A' }}</span>
                </div>
                <div>
                    <span class="text-slate-400 text-[10px] font-bold uppercase block">Birth Date</span>
                    <span class="font-medium text-slate-700">
                        {{ isset($beneficiary->birth_date) ? (\Carbon\Carbon::hasFormat($beneficiary->birth_date, 'Y-m-d') ? \Carbon\Carbon::parse($beneficiary->birth_date)->format('M d, Y') : $beneficiary->birth_date) : ($beneficiary->birthdate ?? 'N/A') }}
                    </span>
                </div>
                <div>
                    <span class="text-slate-400 text-[10px] font-bold uppercase block">Age</span>
                    <span class="font-bold text-neutral-strong">
                        @if($beneficiary?->age !== null)
                            {{ $beneficiary->age }} years old
                        @else
                            N/A
                        @endif
                    </span>
                </div>
                <div>
                    <span class="text-slate-400 text-[10px] font-bold uppercase block">Special Classification</span>
                    <div class="flex flex-wrap items-center gap-1 mt-0.5">
                        @if($beneficiary?->is_senior)
                            <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200">Senior Citizen</span>
                        @endif
                        @if($beneficiary?->is_pwd)
                            <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-800 border border-blue-200">PWD</span>
                        @endif
                        @if(!$beneficiary?->is_senior && !$beneficiary?->is_pwd)
                            <span class="text-slate-500 font-medium">Regular Resident</span>
                        @endif
                    </div>
                </div>
                <div>
                    <span class="text-slate-400 text-[10px] font-bold uppercase block">Contact No.</span>
                    <span class="font-mono text-slate-700">{{ $beneficiary?->contact_no ?? $beneficiary?->contact_number ?? $beneficiary?->phone_no ?? 'None' }}</span>
                </div>
                <div>
                    <span class="text-slate-400 text-[10px] font-bold uppercase block">Data Source</span>
                    <span class="font-bold text-brand">eRehistro</span>
                </div>
            </div>

            @if(!empty($beneficiary?->address))
                <div class="pt-3 border-t border-slate-100 text-xs">
                    <span class="text-slate-400 text-[10px] font-bold uppercase block mb-0.5">Address</span>
                    <p class="text-slate-700 leading-snug">{{ $beneficiary->address }}</p>
                </div>
            @endif
        </div>

        <!-- Right: Acquired Benefits Lifetime Stats -->
        <div class="lg:col-span-7 space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-xs">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Total Ayuda Disbursed</span>
                    <h3 class="text-2xl font-black text-brand tracking-tight font-mono mt-2">₱{{ number_format($totalBenefitsReceived, 2) }}</h3>
                    <p class="text-xs text-slate-500 mt-1">Across all municipal assistance programs</p>
                </div>

                <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-xs">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Verified Assistance Events</span>
                    <h3 class="text-2xl font-black text-neutral-strong tracking-tight font-mono mt-2">{{ $totalClaimsCount }}</h3>
                    <p class="text-xs text-slate-500 mt-1">Completed disbursement claims</p>
                </div>
            </div>

            <div class="bg-emerald-50/60 border border-emerald-200 rounded-xl p-4 text-xs text-slate-700 flex items-start gap-3">
                <div class="w-5 h-5 rounded-md bg-emerald-100 text-brand flex items-center justify-center shrink-0 mt-0.5 font-bold">
                    ✓
                </div>
                <div>
                    <h4 class="font-bold text-brand">Central Audit-Verified Claims</h4>
                    <p class="text-[11px] text-slate-600 mt-0.5 leading-relaxed">
                        Claims below represent real-time local records matched by Civil Registry ID against Project Distribution releases and GGMS consolidated transactions.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Acquired Benefits & Claims History Table -->
    <div class="bg-surface border border-slate-200 rounded-xl p-6 shadow-xs space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <div>
                <h3 class="text-base font-bold text-neutral-strong tracking-tight">Acquired Benefits & Claims History</h3>
                <p class="text-xs text-slate-500">Full immutable timeline of all aid released to this individual.</p>
            </div>
            <span class="px-2.5 py-0.5 rounded-full text-xs font-mono font-bold bg-slate-100 text-slate-700 border border-slate-200">
                {{ $history->count() }} Records Found
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 font-bold uppercase tracking-wider border-y border-slate-100">
                    <tr>
                        <th class="px-4 py-3 rounded-l-lg">Disbursement Date</th>
                        <th class="px-4 py-3">Project / Program Title</th>
                        <th class="px-4 py-3">Reference Code</th>
                        <th class="px-4 py-3">Module Source</th>
                        <th class="px-4 py-3 text-right">Amount / Package</th>
                        <th class="px-4 py-3">Processed By</th>
                        <th class="px-4 py-3 text-center rounded-r-lg">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($history as $item)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-4 py-3.5 font-mono text-slate-600 text-[11px]">
                                {{ $item['disbursed_at'] ? \Carbon\Carbon::parse($item['disbursed_at'])->format('M d, Y h:i A') : '—' }}
                            </td>
                            <td class="px-4 py-3.5">
                                <p class="font-bold text-neutral-strong">{{ $item['program_title'] }}</p>
                                <span class="text-[10px] text-slate-400 font-mono">{{ $item['program_code'] }}</span>
                            </td>
                            <td class="px-4 py-3.5 font-mono font-bold text-brand">
                                {{ $item['reference_code'] }}
                            </td>
                            <td class="px-4 py-3.5">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                    {{ $item['module'] }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5 text-right">
                                <span class="font-mono font-bold text-brand text-xs">
                                    ₱{{ number_format((float)$item['amount'], 2) }}
                                </span>
                                @if(!empty($item['item_details']))
                                    <span class="block text-[10px] text-slate-500">{{ $item['item_details'] }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 text-slate-600 font-medium">
                                {{ $item['officer'] }}
                                <span class="block text-[10px] text-slate-400 font-mono">{{ $item['verification_method'] }}</span>
                            </td>
                            <td class="px-4 py-3.5 text-center">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-brand border border-emerald-200">
                                    Disbursed
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-12 text-slate-400 font-medium">
                                No prior assistance claims recorded for this beneficiary.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
