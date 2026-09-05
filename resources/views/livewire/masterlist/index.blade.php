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
            <!-- Real-time Query Activity Pill -->
            <div wire:loading.flex class="items-center gap-2 px-3 py-1.5 rounded-lg bg-emerald-50 border border-emerald-200 text-xs font-bold text-brand shadow-xs">
                <span class="relative flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-brand"></span>
                </span>
                <span>Fetching Live CRS Data...</span>
            </div>

            <span wire:loading.remove class="text-xs font-mono text-slate-500 bg-slate-50 border border-slate-200 px-3 py-1.5 rounded-lg">
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
            <p class="text-[11px] text-slate-500 italic mt-0.5">Please check network connection or remote IP whitelist on the CRS MySQL host (193.203.175.157:3306).</p>
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
                <div class="relative w-full sm:w-64">
                    <input 
                        wire:model.live.debounce.300ms="search"
                        type="text" 
                        placeholder="Search name, CRN, or household #..."
                        class="bg-white border border-slate-300 rounded-lg pl-3.5 pr-8 py-2 text-xs text-neutral-strong placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-brand w-full"
                    >
                    <div wire:loading wire:target="search" class="absolute right-2.5 top-2.5">
                        <svg class="animate-spin h-3.5 w-3.5 text-brand" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </div>
                </div>

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

                <!-- SKELETON LOADING ROWS (SHOWN ON WIRE:LOADING) -->
                <tbody wire:loading class="divide-y divide-slate-100">
                    @for($i = 0; $i < 8; $i++)
                        <tr class="animate-pulse">
                            <td class="px-4 py-4">
                                <div class="h-3.5 bg-slate-200 rounded w-28"></div>
                            </td>
                            <td class="px-4 py-4">
                                <div class="space-y-1.5">
                                    <div class="h-3.5 bg-slate-200 rounded w-40"></div>
                                    <div class="h-2.5 bg-slate-100 rounded w-16"></div>
                                </div>
                            </td>
                            <td class="px-4 py-4">
                                <div class="h-3.5 bg-slate-200 rounded w-24"></div>
                            </td>
                            <td class="px-4 py-4">
                                <div class="h-3.5 bg-slate-200 rounded w-20"></div>
                            </td>
                            <td class="px-4 py-4">
                                <div class="h-3.5 bg-slate-200 rounded w-24"></div>
                            </td>
                            <td class="px-4 py-4">
                                <div class="h-3.5 bg-slate-200 rounded w-20"></div>
                            </td>
                            <td class="px-4 py-4 text-right">
                                <div class="h-6 bg-slate-200 rounded-md w-20 ml-auto"></div>
                            </td>
                        </tr>
                    @endfor
                </tbody>

                <!-- ACTUAL DATA ROWS (HIDDEN DURING WIRE:LOADING) -->
                <tbody wire:loading.remove class="divide-y divide-slate-100">
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
                            <td class="px-4 py-3.5 text-slate-600 leading-tight">
                                @php
                                    $dobFormatted = null;
                                    $rawBirth = $ben->birth_date;
                                    if ($rawBirth) {
                                        try {
                                            $dobFormatted = \Carbon\Carbon::parse($rawBirth)->format('M d, Y');
                                        } catch (\Throwable) {
                                            $dobFormatted = $rawBirth;
                                        }
                                    }
                                    $ageVal = $ben->age;
                                    $isSeniorVal = $ben->is_senior;
                                @endphp

                                @if($dobFormatted)
                                    <div class="font-medium text-slate-800">{{ $dobFormatted }}</div>
                                    @if($ageVal !== null)
                                        <div class="text-[11px] {{ $isSeniorVal ? 'text-amber-700 font-semibold' : 'text-slate-500' }}">
                                            ({{ $ageVal }} yrs{{ $isSeniorVal ? ' · Senior' : '' }})
                                        </div>
                                    @endif
                                @elseif($ageVal !== null)
                                    <div class="text-[11px] {{ $isSeniorVal ? 'text-amber-700 font-semibold' : 'text-slate-700 font-medium' }}">
                                        {{ $ageVal }} yrs{{ $isSeniorVal ? ' · Senior' : '' }}
                                    </div>
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
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
