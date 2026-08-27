<div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
    <!-- Left Sidebar: Report Configuration & Filter Panel -->
    <div class="lg:col-span-4 bg-white border border-slate-200 rounded-xl p-6 shadow-xs space-y-5 h-fit">
        <div>
            <h2 class="text-lg font-bold text-brand tracking-tight">Report Generator</h2>
            <p class="text-xs text-slate-500 mt-1">Compile executive financial, disbursement, and audit reports.</p>
        </div>

        <!-- Report Type Selectors with Help Cards -->
        <div class="space-y-2.5">
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-500">Select Report Type</label>
            
            @foreach($reportTypes as $type)
                <div 
                    wire:click="$set('selectedReportType', '{{ $type->value }}')"
                    class="p-3.5 rounded-xl border transition-all cursor-pointer {{ $selectedReportType === $type->value ? 'bg-emerald-50 border-brand text-neutral-strong shadow-xs' : 'bg-slate-50 border-slate-200 text-slate-700 hover:bg-slate-100' }}"
                >
                    <div class="flex items-center justify-between">
                        <h4 class="text-xs font-bold {{ $selectedReportType === $type->value ? 'text-brand' : 'text-slate-800' }}">{{ $type->label() }}</h4>
                        @if($selectedReportType === $type->value)
                            <span class="w-2 h-2 rounded-full bg-brand"></span>
                        @endif
                    </div>
                    <p class="text-[11px] text-slate-500 mt-1 leading-snug">{{ $type->description() }}</p>
                </div>
            @endforeach
        </div>

        <!-- Date Range Filter -->
        <div class="space-y-3 pt-2 border-t border-slate-100">
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-500">Date Range</label>
            <div class="grid grid-cols-2 gap-2">
                <div>
                    <span class="text-[10px] text-slate-500 block mb-1 font-bold">Date From</span>
                    <input 
                        type="date" 
                        wire:model.live="dateFrom" 
                        class="w-full bg-white border border-slate-300 rounded-lg px-3 py-1.5 text-xs text-neutral-strong"
                    >
                </div>
                <div>
                    <span class="text-[10px] text-slate-500 block mb-1 font-bold">Date To</span>
                    <input 
                        type="date" 
                        wire:model.live="dateTo" 
                        class="w-full bg-white border border-slate-300 rounded-lg px-3 py-1.5 text-xs text-neutral-strong"
                    >
                </div>
            </div>
        </div>

        <!-- Conditional Program Filter -->
        @if($selectedReportType === 'DistributionClaims')
            <div class="space-y-1.5 pt-2 border-t border-slate-100">
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-500">Filter by Ayuda Project</label>
                <select wire:model.live="selectedProgramId" class="w-full bg-white border border-slate-300 rounded-lg px-3 py-2 text-xs text-neutral-strong cursor-pointer">
                    <option value="">All Active & Closed Projects</option>
                    @foreach($programs as $p)
                        <option value="{{ $p->id }}">{{ $p->program_code }} - {{ $p->title }}</option>
                    @endforeach
                </select>
            </div>
        @endif
    </div>

    <!-- Right Center: Live Snapshot Preview & Export Toolbar -->
    <div class="lg:col-span-8 space-y-5">
        <!-- Export Actions Toolbar -->
        <div class="bg-white border border-slate-200 rounded-xl p-4 flex flex-wrap items-center justify-between gap-3 shadow-xs">
            <div class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-brand animate-pulse"></span>
                <span class="text-xs font-bold text-neutral-strong uppercase tracking-wider">Document Snapshot Ready</span>
            </div>

            <div class="flex items-center gap-2">
                <!-- CSV Export -->
                <button 
                    wire:click="exportCsv"
                    class="px-3.5 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 border border-slate-200 text-xs font-bold text-neutral-strong transition-colors flex items-center gap-1.5 cursor-pointer shadow-xs"
                >
                    <svg class="w-3.5 h-3.5 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>Export CSV</span>
                </button>

                <!-- PDF Download -->
                <a 
                    href="{{ route('reports.pdf', ['type' => $selectedReportType, 'date_from' => $dateFrom, 'date_to' => $dateTo, 'program_id' => $selectedProgramId]) }}" 
                    target="_blank"
                    class="px-4 py-1.5 rounded-lg bg-brand hover:bg-emerald-700 text-xs font-bold text-white shadow-xs transition-all flex items-center gap-1.5 cursor-pointer"
                >
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    <span>Download Official PDF</span>
                </a>

                <!-- Print Preview -->
                <button 
                    onclick="window.print()"
                    class="px-3.5 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 border border-slate-200 text-xs font-bold text-neutral-strong transition-colors flex items-center gap-1.5 cursor-pointer"
                >
                    <svg class="w-3.5 h-3.5 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    <span>Print</span>
                </button>
            </div>
        </div>

        <!-- Document Preview Body -->
        <div class="bg-white border border-slate-200 rounded-xl p-6 shadow-xs space-y-6">
            <!-- Header Metadata -->
            <div class="border-b border-slate-100 pb-4">
                <span class="text-[10px] uppercase font-bold tracking-widest text-brand">Municipality of Sulop • Official Audit Report</span>
                <h3 class="text-xl font-black text-neutral-strong tracking-tight mt-1">{{ $snapshot->metadata['title'] }}</h3>
                <p class="text-xs text-slate-500 mt-1">{{ $snapshot->metadata['subtitle'] }}</p>
                <div class="flex flex-wrap gap-4 mt-3 text-xs font-mono text-slate-600">
                    <span><strong>Period:</strong> {{ $snapshot->metadata['date_range_label'] }}</span>
                    <span><strong>Filter:</strong> {{ $snapshot->metadata['program_label'] }}</span>
                    <span><strong>Generated By:</strong> {{ $snapshot->metadata['generated_by'] }}</span>
                </div>
            </div>

            <!-- Executive Summary Highlights -->
            @if(!empty($snapshot->highlights))
                <div class="bg-emerald-50/50 border-l-4 border-brand rounded-r-xl p-4 space-y-2">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-brand">Executive Summary</h4>
                    <ul class="space-y-1 text-xs text-slate-700">
                        @foreach($snapshot->highlights as $bullet)
                            <li class="flex items-start gap-2">
                                <span class="text-brand mt-1">•</span>
                                <span>{{ $bullet }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- KPI Metric Cards Grid -->
            @if(!empty($snapshot->metrics))
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    @foreach($snapshot->metrics as $metric)
                        <div class="bg-slate-50 border border-slate-200 rounded-xl p-3.5 text-center">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500 block">{{ $metric['label'] }}</span>
                            <span class="text-base font-black font-mono text-neutral-strong block mt-1">{{ $metric['value'] }}</span>
                            @if(isset($metric['subtext']))
                                <span class="text-[10px] text-slate-500 block mt-0.5">{{ $metric['subtext'] }}</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif

            <!-- Data Table Preview -->
            <div class="overflow-x-auto border border-slate-200 rounded-xl">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-500 font-bold uppercase tracking-wider border-b border-slate-200">
                        <tr>
                            @foreach($snapshot->headers as $header)
                                <th class="px-3 py-2.5">{{ $header }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($snapshot->rows as $row)
                            <tr class="hover:bg-slate-50 transition-colors">
                                @foreach($row as $val)
                                    <td class="px-3 py-2.5 text-neutral-strong">{{ $val }}</td>
                                @endforeach
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ count($snapshot->headers) }}" class="text-center py-10 text-slate-400">
                                    No records found in database matching criteria.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Signature Lines Preview -->
            <div class="grid grid-cols-3 gap-4 pt-8 border-t border-slate-100 text-center text-xs">
                <div>
                    <p class="text-[10px] text-slate-400 mb-6">Prepared by:</p>
                    <p class="font-bold text-neutral-strong border-t border-slate-200 pt-1.5 uppercase">{{ $snapshot->signatures['prepared_by'] }}</p>
                    <p class="text-[10px] text-slate-500">Ayuda Operations Officer</p>
                </div>
                <div>
                    <p class="text-[10px] text-slate-400 mb-6">Reviewed by:</p>
                    <p class="font-bold text-neutral-strong border-t border-slate-200 pt-1.5 uppercase">{{ $snapshot->signatures['reviewed_by'] }}</p>
                    <p class="text-[10px] text-slate-500">Municipal Budget Officer / Accountant</p>
                </div>
                <div>
                    <p class="text-[10px] text-slate-400 mb-6">Approved by:</p>
                    <p class="font-bold text-neutral-strong border-t border-slate-200 pt-1.5 uppercase">{{ $snapshot->signatures['approved_by'] }}</p>
                    <p class="text-[10px] text-slate-500">Municipal Mayor</p>
                </div>
            </div>
        </div>
    </div>
</div>
