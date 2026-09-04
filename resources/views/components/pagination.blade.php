@php
if (! isset($scrollTo)) {
    $scrollTo = false;
}

$scrollIntoViewJsSnippet = ($scrollTo !== false)
    ? <<<JS
       (\$el.closest('{$scrollTo}') || document.querySelector('{$scrollTo}')).scrollIntoView({ behavior: 'smooth' })
    JS
    : '';
@endphp

<div>
    @if ($paginator->hasPages())
        <nav role="navigation" aria-label="Pagination Navigation" class="flex flex-wrap items-center justify-between gap-2 py-1 select-none">
            {{-- Results counter --}}
            <div class="text-[11px] text-slate-500 font-medium">
                <span>Showing</span>
                <span class="font-bold text-neutral-strong">{{ $paginator->firstItem() }}-{{ $paginator->lastItem() }}</span>
                <span class="text-slate-400">of</span>
                <span class="font-bold text-neutral-strong">{{ $paginator->total() }}</span>
            </div>

            {{-- Compact Page Controls --}}
            <div class="inline-flex items-center gap-1">
                {{-- Previous Page Button --}}
                @if ($paginator->onFirstPage())
                    <span aria-disabled="true" aria-label="{{ __('pagination.previous') }}" class="w-7 h-7 flex items-center justify-center rounded-lg border border-slate-200 bg-slate-50 text-slate-300 cursor-not-allowed">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                    </span>
                @else
                    <button type="button" 
                        wire:click="previousPage('{{ $paginator->getPageName() }}')" 
                        @if($scrollIntoViewJsSnippet) x-on:click="{{ $scrollIntoViewJsSnippet }}" @endif
                        wire:loading.attr="disabled"
                        class="w-7 h-7 flex items-center justify-center rounded-lg border border-slate-200 bg-white hover:bg-slate-100 active:bg-slate-200 text-slate-600 transition-colors cursor-pointer shadow-2xs disabled:opacity-50" 
                        aria-label="{{ __('pagination.previous') }}"
                        title="Previous Page"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                    </button>
                @endif

                {{-- Page Numbers / Elements --}}
                @foreach ($elements as $element)
                    {{-- Dots Separator --}}
                    @if (is_string($element))
                        <span aria-disabled="true" class="w-4 h-7 flex items-center justify-center text-slate-400 text-xs font-bold">
                            {{ $element }}
                        </span>
                    @endif

                    {{-- Array of Pages --}}
                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            <span wire:key="paginator-{{ $paginator->getPageName() }}-page{{ $page }}">
                                @if ($page == $paginator->currentPage())
                                    <span aria-current="page" class="min-w-7 h-7 px-1.5 flex items-center justify-center rounded-lg bg-brand border border-brand text-white text-xs font-bold shadow-xs">
                                        {{ $page }}
                                    </span>
                                @else
                                    <button type="button" 
                                        wire:click="gotoPage({{ $page }}, '{{ $paginator->getPageName() }}')" 
                                        @if($scrollIntoViewJsSnippet) x-on:click="{{ $scrollIntoViewJsSnippet }}" @endif
                                        wire:loading.attr="disabled"
                                        class="min-w-7 h-7 px-1.5 flex items-center justify-center rounded-lg border border-slate-200 bg-white hover:bg-slate-100 active:bg-slate-200 text-slate-700 text-xs font-semibold transition-colors cursor-pointer shadow-2xs disabled:opacity-50" 
                                        aria-label="{{ __('Go to page :page', ['page' => $page]) }}"
                                    >
                                        {{ $page }}
                                    </button>
                                @endif
                            </span>
                        @endforeach
                    @endif
                @endforeach

                {{-- Next Page Button --}}
                @if ($paginator->hasMorePages())
                    <button type="button" 
                        wire:click="nextPage('{{ $paginator->getPageName() }}')" 
                        @if($scrollIntoViewJsSnippet) x-on:click="{{ $scrollIntoViewJsSnippet }}" @endif
                        wire:loading.attr="disabled"
                        class="w-7 h-7 flex items-center justify-center rounded-lg border border-slate-200 bg-white hover:bg-slate-100 active:bg-slate-200 text-slate-600 transition-colors cursor-pointer shadow-2xs disabled:opacity-50" 
                        aria-label="{{ __('pagination.next') }}"
                        title="Next Page"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                    </button>
                @else
                    <span aria-disabled="true" aria-label="{{ __('pagination.next') }}" class="w-7 h-7 flex items-center justify-center rounded-lg border border-slate-200 bg-slate-50 text-slate-300 cursor-not-allowed">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                    </span>
                @endif
            </div>
        </nav>
    @endif
</div>
