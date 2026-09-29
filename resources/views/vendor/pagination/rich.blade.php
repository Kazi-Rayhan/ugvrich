@if ($paginator->hasPages())
    @php
        $btn = 'inline-flex h-11 items-center justify-center gap-2 rounded-full border px-4 text-[14px] font-semibold transition duration-300';
    @endphp

    <nav role="navigation" aria-label="Pagination" class="flex flex-col items-center gap-5 sm:flex-row sm:justify-between">
        {{-- Summary --}}
        <p class="order-2 text-[13.5px] muted sm:order-1">
            Showing
            <span class="font-semibold text-ink-900">{{ $paginator->firstItem() }}–{{ $paginator->lastItem() }}</span>
            of
            <span class="font-semibold text-ink-900">{{ $paginator->total() }}</span>
        </p>

        <div class="order-1 flex items-center gap-2 rounded-full border border-ink-100 bg-white p-1.5 shadow-[0_12px_30px_-22px_rgba(11,15,24,0.35)] sm:order-2">
            {{-- Previous --}}
            @if ($paginator->onFirstPage())
                <span class="{{ $btn }} cursor-not-allowed border-transparent text-ink-300" aria-disabled="true">
                    <x-ui-icon name="arrow-left" class="h-4 w-4" />
                    <span class="hidden sm:inline">Prev</span>
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Previous page"
                   class="{{ $btn }} group border-transparent text-ink-700 hover:bg-ink-50 hover:text-brand-700">
                    <x-ui-icon name="arrow-left" class="h-4 w-4 transition-transform duration-300 group-hover:-translate-x-0.5" />
                    <span class="hidden sm:inline">Prev</span>
                </a>
            @endif

            {{-- Pages (desktop) --}}
            <span class="hidden items-center gap-1 sm:flex">
                @foreach ($elements as $element)
                    @if (is_string($element))
                        <span class="flex h-11 w-9 items-center justify-center text-ink-400" aria-disabled="true">…</span>
                    @endif

                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <span aria-current="page"
                                      class="flex h-11 min-w-11 items-center justify-center rounded-full bg-brand-600 px-3 font-numeric text-[14px] font-bold tabular-nums text-white shadow-[0_10px_22px_-10px_var(--color-brand-600)]">
                                    {{ $page }}
                                </span>
                            @else
                                <a href="{{ $url }}" aria-label="Go to page {{ $page }}"
                                   class="flex h-11 min-w-11 items-center justify-center rounded-full px-3 font-numeric text-[14px] font-semibold tabular-nums text-ink-600 transition hover:bg-brand-50 hover:text-brand-700">
                                    {{ $page }}
                                </a>
                            @endif
                        @endforeach
                    @endif
                @endforeach
            </span>

            {{-- Compact counter (mobile) --}}
            <span class="px-3 font-numeric text-[14px] font-semibold tabular-nums text-ink-700 sm:hidden">
                {{ $paginator->currentPage() }} <span class="text-ink-300">/</span> {{ $paginator->lastPage() }}
            </span>

            {{-- Next --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Next page"
                   class="{{ $btn }} group border-transparent bg-ink-950 text-white hover:bg-brand-600">
                    <span class="hidden sm:inline">Next</span>
                    <x-ui-icon name="arrow-right" class="h-4 w-4 transition-transform duration-300 group-hover:translate-x-0.5" />
                </a>
            @else
                <span class="{{ $btn }} cursor-not-allowed border-transparent bg-ink-100 text-ink-400" aria-disabled="true">
                    <span class="hidden sm:inline">Next</span>
                    <x-ui-icon name="arrow-right" class="h-4 w-4" />
                </span>
            @endif
        </div>
    </nav>
@endif
