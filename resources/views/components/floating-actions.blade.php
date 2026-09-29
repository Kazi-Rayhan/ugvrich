{{-- The two buttons that follow you down the page, stacked in one corner.

     Bottom left rather than bottom right: the right corner is where chat
     widgets, cookie notices and back-to-top buttons all land by default, and a
     button that spends its life underneath one of those is not a button.

     Icons, not labels: at this size a word competes with the content it floats
     over. The words are still there for screen readers and as a tooltip, and
     they slide out on hover where there is room for them.

     WhatsApp sits above, the booking button at the bottom — nearest the thumb,
     because it is the one the site is asking for. If either is missing the
     other simply takes the corner. --}}

@php
    /* WhatsApp: the number from Site Settings, falling back to the office
       phone, since most places use one line for both. wa.me takes digits
       only — no +, no spaces, no dashes. */
    $whatsapp = preg_replace('/\D/', '', (string) ($site->get('contact_whatsapp') ?: $site->get('contact_phone')));
    $whatsappMessage = rawurlencode(__('site.actions.whatsapp_message', ['site' => $site->name()]));

    /* Offering to take somebody where they already are reads as a broken link,
       and the button would cover the form they came to fill in. */
    $showBooking = ! \App\Support\Navigation::isCurrent('consultancy.create');

    $button = 'group inline-flex h-14 w-14 items-center justify-center rounded-full px-4 text-white
               hover:w-auto focus-visible:w-auto
               shadow-lg transition-all duration-300 ease-[cubic-bezier(0.22,1,0.36,1)] hover:shadow-xl
               focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2';

    // The label unrolls on hover, and is always there for assistive tech.
    $label = 'ms-0 max-w-0 overflow-hidden whitespace-nowrap text-sm font-semibold opacity-0
              transition-all duration-300 ease-[cubic-bezier(0.22,1,0.36,1)]
              group-hover:ms-2 group-hover:max-w-[14rem] group-hover:opacity-100
              group-focus-visible:ms-2 group-focus-visible:max-w-[14rem] group-focus-visible:opacity-100
              motion-reduce:transition-none';
@endphp

@if ($whatsapp || $showBooking)
    <div data-floating-actions class="fixed bottom-5 left-5 z-50 flex flex-col items-start gap-3">
        @if ($whatsapp)
            <a href="https://wa.me/{{ $whatsapp }}?text={{ $whatsappMessage }}"
               target="_blank"
               rel="noopener noreferrer"
               title="{{ __('site.actions.whatsapp') }}"
               aria-label="{{ __('site.actions.whatsapp') }}"
               class="{{ $button }} bg-[#25D366] shadow-emerald-900/25 hover:bg-[#1FB955] focus-visible:outline-[#25D366]">
                <x-ui-icon name="whatsapp" class="h-6 w-6 shrink-0" />
                <span class="{{ $label }}">{{ __('site.actions.whatsapp') }}</span>
            </a>
        @endif

        @if ($showBooking)
            <a href="{{ route('consultancy.create') }}"
               title="{{ __('site.actions.request_consultancy') }}"
               aria-label="{{ __('site.actions.request_consultancy') }}"
               class="{{ $button }} bg-brand-600 shadow-brand-900/25 hover:bg-brand-500 focus-visible:outline-brand-400">
                <x-ui-icon name="calendar" class="h-6 w-6 shrink-0" />
                <span class="{{ $label }}">{{ __('site.actions.request_consultancy') }}</span>
            </a>
        @endif
    </div>

    <style>
        /* Clear of the iPhone home indicator, which otherwise sits on top of it. */
        [data-floating-actions] {
            bottom: calc(1.25rem + env(safe-area-inset-bottom, 0px));
            left: calc(1.25rem + env(safe-area-inset-left, 0px));
        }

        /* A touch screen has no hover, so the label would never unroll; the
           button stays the circle it already is. */
        @media (hover: none) {
            [data-floating-actions] span {
                display: none;
            }
        }
    </style>
@endif
