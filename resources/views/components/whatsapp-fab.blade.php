{{-- The WhatsApp button, opposite the consultation one.

     Bottom right, which is where people reach for a chat button, and where
     the consultation button deliberately is not. It only appears once a
     number has been saved in Site Settings, so the corner stays empty rather
     than offering a link to nowhere. --}}

@php
    // wa.me takes digits only: no +, no spaces, no dashes.
    $number = preg_replace('/\D/', '', (string) $site->get('contact_whatsapp'));
    $message = rawurlencode(__('site.actions.whatsapp_message', ['site' => $site->name()]));
@endphp

@if ($number)
    <a href="https://wa.me/{{ $number }}?text={{ $message }}"
       target="_blank"
       rel="noopener noreferrer"
       data-whatsapp-fab
       title="{{ __('site.actions.whatsapp') }}"
       aria-label="{{ __('site.actions.whatsapp') }}"
       class="group fixed bottom-5 right-5 z-50 inline-flex h-14 items-center justify-center gap-2 rounded-full
              bg-[#25D366] px-4 text-white shadow-lg shadow-emerald-900/25
              transition-all duration-300 ease-[cubic-bezier(0.22,1,0.36,1)]
              hover:bg-[#1FB955] hover:shadow-xl
              focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2
              focus-visible:outline-[#25D366]">
        <x-ui-icon name="whatsapp" class="h-6 w-6 shrink-0" />

        {{-- The label unrolls on hover, and is always there for assistive tech. --}}
        <span class="max-w-0 overflow-hidden whitespace-nowrap text-sm font-semibold opacity-0
                     transition-all duration-300 ease-[cubic-bezier(0.22,1,0.36,1)]
                     group-hover:max-w-[14rem] group-hover:opacity-100
                     group-focus-visible:max-w-[14rem] group-focus-visible:opacity-100
                     motion-reduce:transition-none">
            {{ __('site.actions.whatsapp') }}
        </span>
    </a>

    <style>
        /* Clear of the iPhone home indicator, which otherwise sits on top of it. */
        [data-whatsapp-fab] {
            bottom: calc(1.25rem + env(safe-area-inset-bottom, 0px));
            right: calc(1.25rem + env(safe-area-inset-right, 0px));
        }

        /* A touch screen has no hover, so the label would never unroll and its
           padding would only make the circle lopsided. */
        @media (hover: none) {
            [data-whatsapp-fab] {
                width: 3.5rem;
                padding: 0;
            }

            [data-whatsapp-fab] span {
                display: none;
            }
        }
    </style>
@endif
