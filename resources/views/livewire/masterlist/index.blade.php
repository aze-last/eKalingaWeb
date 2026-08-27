<div class="space-y-6">
    <!-- Header & Info Banner -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 bg-white p-6 rounded-xl border border-slate-200 shadow-xs">
        <div>
            <div class="flex items-center gap-2.5">
                <h1 class="text-2xl font-bold text-brand tracking-tight">Civil Registry Masterlist</h1>
                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-brand border border-emerald-200">
                    Live CRS Connection
                </span>
            </div>
            <p class="text-xs text-slate-500 mt-1">Direct read-only registry of all validated accounts in Sulop from the central Civil Registry System (CRS).</p>
        </div>

        <div class="flex items-center gap-2">
            <span class="text-xs font-mono text-slate-500 bg-slate-50 border border-slate-200 px-3 py-1.5 rounded-lg">
                CRS: <strong class="text-brand font-bold">val_beneficiaries</strong>
            </span>
        </div>
    </div>

    <!-- Connection Error Warning Alert if applicable -->
    @if($connectionError)
        <div class="p-4 rounded-xl border border-amber-300 bg-amber-50/80 text-warning text-xs space-y-1">
            <div class="flex items-center gap-2 font-bold">
                <svg class="w-4 h-4 text-warning shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <span>External CRS Database Notice</span>
            </div>
            <p class="text-slate-700 leading-relaxed font-mono text-[11px]">{{ $connectionError }}</p>
            <p class="text-[11px] text-slate-500 italic mt-0.5">Please check network connection or remote IP whitelist on the CRS MySQL host (192.203.175.157:3306).</p>
        </div>
    @endif

    <!-- Search & Barangay Filter Toolbar -->
    <div class="bg-surface border border-slate-200 rounded-xl p-6 shadow-xs space-y-4">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 pb-4 border-b border-slate-100">
            <div>
                <h3 class="text-base font-bold text-neutral-strong tracking-tight">Beneficiary Records</h3>
                <p class="text-xs text-slate-500">Click any row to open the complete identity card and disbursement history.</p>
            </div>

            <!-- Search & Filters -->
            <div class="flex flex-wrap items-center gap-2.5 w-full sm:w-auto">
                <input 
                    wire:model.live.debounce.300ms="search"
                    type="text" 
                    placeholder="Search name, CRN, or household #..."
                    class="bg-white border border-slate-300 rounded-lg px-3.5 py-2 text-xs text-neutral-strong placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-brand w-full sm:w-64"
                >
                @if(!empty($barangays))
                    <select 
                        wire:model.live="selectedBarangay"
                        class="bg-white border border-slate-300 rounded-lg px-3 py-2 text-xs text-neutral-strong focus:outline-none focus:ring-1 focus:ring-brand cursor-pointer"
                    >
                        <option value="">All Barangays</option>
                        @foreach($barangays as $b)
                            <option value="{{ $b }}">{{ $b }}</option>
                        @endforeach
                    </select>
                @endif
            </div>
        </div>

        <!-- Masterlist Table -->
        <div class="overflow-x-auto relative">
            <!-- Loading Skeleton Overlay -->
            <div wire:loading class="absolute inset-0 bg-white/70 backdrop-blur-xs z-10 flex items-center justify-center">
                <div class="flex items-center gap-2 text-brand font-bold text-xs">
                    <svg class="animate-spin h-4 w-4 text-brand" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span>Querying live CRS records...</span>
                </div>
            </div>

            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 font-bold uppercase tracking-wider border-y border-slate-100">
                    <tr>
                        <th class="px-4 py-3 rounded-l-lg">Civil Registry ID (CRN)</th>
                        <th class="px-4 py-3">Full Name</th>
                        <th class="px-4 py-3">Barangay</th>
                        <th class="px-4 py-3">Household No.</th>
                        <th class="px-4 py-3">Birth Date / Age</th>
                        <th class="px-4 py-3">Contact</th>
                        <th class="px-4 py-3 text-right rounded-r-lg">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($beneficiaries as $ben)
                        @php
                            $targetId = $ben->civil_registry_id ?: $ben->id;
                        @endphp
                        <tr 
                            class="hover:bg-emerald-50/40 transition-colors cursor-pointer group"
                            onclick="window.location='{{ route('masterlist.profile', ['civilRegistryId' => $targetId]) }}'"
                        >
                            <td class="px-4 py-3.5 font-mono font-bold text-brand group-hover:underline">
                                <a href="{{ route('masterlist.profile', ['civilRegistryId' => $targetId]) }}" wire:navigate class="block">
                                    {{ $ben->civil_registry_id ?: ('ID-' . $ben->id) }}
                                </a>
                            </td>
                            <td class="px-4 py-3.5">
                                <a href="{{ route('masterlist.profile', ['civilRegistryId' => $targetId]) }}" wire:navigate class="block">
                                    <p class="font-bold text-neutral-strong group-hover:text-brand">{{ $ben->full_name }}</p>
                                    @if(isset($ben->gender) || isset($ben->sex))
                                        <span class="text-[10px] text-slate-400 font-medium">{{ $ben->gender ?? $ben->sex }}</span>
                                    @endif
                                </a>
                            </td>
                            <td class="px-4 py-3.5 text-slate-700 font-medium">
                                {{ $ben->barangay ?? 'N/A' }}
                            </td>
                            <td class="px-4 py-3.5 font-mono text-slate-600">
                                {{ $ben->household_no ?? 'N/A' }}
                            </td>
                            <td class="px-4 py-3.5 text-slate-600">
                                {{ isset($ben->birth_date) ? (\Carbon\Carbon::hasFormat($ben->birth_date, 'Y-m-d') ? \Carbon\Carbon::parse($ben->birth_date)->format('M d, Y') : $ben->birth_date) : ($ben->birthdate ?? '—') }}
                            </td>
                            <td class="px-4 py-3.5 font-mono text-slate-600">
                                {{ $ben->contact_no ?? $ben->contact_number ?? $ben->phone_no ?? '—' }}
                            </td>
                            <td class="px-4 py-3.5 text-right">
                                <a 
                                    href="{{ route('masterlist.profile', ['civilRegistryId' => $targetId]) }}" 
                                    wire:navigate 
                                    class="inline-flex items-center gap-1 text-[11px] font-bold text-brand hover:underline px-2.5 py-1 rounded-md bg-emerald-50 border border-emerald-200"
                                >
                                    <span>View Profile</span>
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-16 text-slate-400 font-medium">
                                @if($connectionError)
                                    Unable to query CRS database. Please check connection credentials.
                                @else
                                    No records found in CRS masterlist.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
            <span class="text-xs text-slate-500 font-mono">Showing 25 records per page</span>
            {{ $beneficiaries->links() }}
        </div>
    </div>
</div>
