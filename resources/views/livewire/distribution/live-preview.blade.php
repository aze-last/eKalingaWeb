<div class="min-h-screen bg-[#070D1E] text-white p-6 sm:p-10 flex flex-col justify-between" wire:poll.2s>
    <!-- Header Banner -->
    <div class="flex items-center justify-between border-b border-slate-800 pb-6">
        <div class="flex items-center gap-4">
            <img src="{{ asset($municipalSeal) }}" alt="Seal" class="w-16 h-16 object-contain drop-shadow-xl">
            <div>
                <h1 class="text-xs font-extrabold uppercase tracking-widest text-emerald-400">{{ $municipalityName }}</h1>
                <h2 class="text-2xl sm:text-3xl font-black text-white tracking-tight">{{ $program->title }}</h2>
                <p class="text-xs text-slate-400 font-mono">{{ $program->program_code }} • {{ $program->benefit_type->value === 'Cash' ? '₱'.number_format($program->unit_amount, 2).' CASH SUBSIDY' : "{$program->item_quantity_per_beneficiary} {$program->item_unit} {$program->item_name}" }}</p>
            </div>
        </div>

        <div class="flex items-center gap-6 text-right">
            <div>
                <span class="text-xs text-slate-400 uppercase tracking-wider block">DISBURSED</span>
                <span class="text-3xl font-black font-mono text-emerald-400">{{ number_format($totalReleased) }}</span>
            </div>
            <div>
                <span class="text-xs text-slate-400 uppercase tracking-wider block">IN QUEUE</span>
                <span class="text-3xl font-black font-mono text-amber-400">{{ number_format($totalQueued) }}</span>
            </div>
        </div>
    </div>

    <!-- Main Content Area: Latest Announced Recipient -->
    <div class="my-8 grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
        <!-- Left Hero: Current Disbursed Recipient -->
        <div class="lg:col-span-7 bg-gradient-to-br from-[#0F1C36] to-[#0A261D] border-2 border-emerald-500/50 rounded-3xl p-8 sm:p-12 shadow-2xl space-y-6 relative overflow-hidden">
            <div class="flex items-center justify-between">
                <span class="px-3 py-1 rounded-full text-xs font-black bg-emerald-500 text-slate-950 uppercase tracking-widest animate-pulse">
                    CURRENT RELEASE
                </span>
                <span class="text-xs font-mono text-emerald-400">{{ $latestClaim?->claimed_at?->format('h:i:s A') }}</span>
            </div>

            @if($latestClaim)
                <div>
                    <h3 class="text-3xl sm:text-5xl font-black text-white tracking-tight leading-none">
                        {{ $latestClaim->beneficiary?->full_name }}
                    </h3>
                    <p class="text-lg text-emerald-400 font-bold mt-2">
                        Brgy. {{ $latestClaim->beneficiary?->barangay }} • Household #{{ $latestClaim->beneficiary?->household_no }}
                    </p>
                </div>

                <div class="pt-6 border-t border-slate-700/60 flex items-center justify-between font-mono">
                    <div>
                        <span class="text-xs text-slate-400 block font-sans">CLAIM REFERENCE</span>
                        <span class="text-sm font-bold text-white">{{ $latestClaim->claim_code }}</span>
                    </div>
                    <div class="text-right">
                        <span class="text-xs text-slate-400 block font-sans">AID GRANTED</span>
                        <span class="text-2xl font-black text-emerald-400">₱{{ number_format($latestClaim->unit_amount, 2) }}</span>
                    </div>
                </div>
            @else
                <div class="py-12 text-center text-slate-400 text-lg font-medium">
                    Waiting for the first disbursement scan...
                </div>
            @endif
        </div>

        <!-- Right Side: Upcoming Beneficiaries in Line -->
        <div class="lg:col-span-5 bg-[#0F172A] border border-slate-800 rounded-3xl p-6 shadow-xl space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                <h3 class="text-sm font-bold uppercase tracking-wider text-slate-300">Next In Line (Queued)</h3>
                <span class="text-xs text-amber-400 font-mono font-bold">{{ $upcomingQueue->count() }} calling</span>
            </div>

            <div class="space-y-3">
                @forelse($upcomingQueue as $queue)
                    <div class="bg-[#162035] border border-slate-700/60 rounded-xl p-3.5 flex items-center justify-between">
                        <div>
                            <p class="font-bold text-white text-sm">{{ $queue->beneficiary?->full_name }}</p>
                            <p class="text-xs text-slate-400">Brgy. {{ $queue->beneficiary?->barangay }}</p>
                        </div>
                        <span class="px-2 py-1 rounded bg-amber-500/10 text-amber-400 border border-amber-500/20 text-xs font-mono font-bold">
                            NEXT
                        </span>
                    </div>
                @empty
                    <p class="text-center py-8 text-slate-500 text-xs">No beneficiaries queued.</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Footer Announcement -->
    <div class="flex flex-col sm:flex-row items-center justify-between text-xs text-slate-400 border-t border-slate-800 pt-4">
        <span>Please prepare your official Barcode / QR Claim Stub and Valid Government ID.</span>
        <span class="font-mono text-emerald-400">eKalinga+ Live Broadcast Engine</span>
    </div>
</div>
