{{-- The consultation button that follows you down the page.

     An icon, not a label: at this size a word competes with the content it
     floats over, and the calendar says "book a time" in every language the
     site speaks. The words are still there for screen readers and as a
     tooltip, and they slide out on hover where there is room for them.

     Bottom left rather than bottom right: the right corner is where chat
     widgets, cookie notices and back-to-top buttons all land by default, and a
     button that spends its life underneath one of those is not a button.

     Hidden on the booking page itself - offering to take somebody where they
     already are reads as a broken link, and it would cover the form they came
     to fill in. --}}

@if (! \App\Support\Navigation::isCurrent('consultancy.create'))
    <a href="{{ route('consultancy.create') }}"
       data-book-consultation-fab
       title="{{ __('site.actions.request_consultancy') }}"
       aria-label="{{ __('site.actions.request_consultancy') }}"
       class="group fixed bottom-5 left-5 z-50 inline-flex h-14 items-center justify-center gap-2 rounded-full
              bg-brand-600 pl-4 pr-4 text-white shadow-lg shadow-brand-900/25
              transition-all duration-300 ease-[cubic-bezier(0.22,1,0.36,1)]
              hover:bg-brand-500 hover:shadow-xl
              focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2
              focus-visible:outline-brand-400">
        <x-ui-icon name="calendar" class="h-6 w-6 shrink-0" />

        {{-- The label unrolls on hover, and is always there for assistive tech. --}}
        <span class="max-w-0 overflow-hidden whitespace-nowrap text-sm font-semibold opacity-0
                     transition-all duration-300 ease-[cubic-bezier(0.22,1,0.36,1)]
                     group-hover:max-w-[14rem] group-hover:opacity-100
                     group-focus-visible:max-w-[14rem] group-focus-visible:opacity-100
                     motion-reduce:transition-none">
            {{ __('site.actions.request_consultancy') }}
        </span>
    </a>

    <style>
        /* Clear of the iPhone home indicator, which otherwise sits on top of it. */
        [data-book-consultation-fab] {
            bottom: calc(1.25rem + env(safe-area-inset-bottom, 0px));
            left: calc(1.25rem + env(safe-area-inset-left, 0px));
        }

        /* A touch screen has no hover, so the label would never unroll and its
           padding would only make the circle lopsided. */
        @media (hover: none) {
            [data-book-consultation-fab] {
                width: 3.5rem;
                padding: 0;
            }

            [data-book-consultation-fab] span {
                display: none;
            }
        }
    </style>
@endif
