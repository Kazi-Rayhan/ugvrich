@php
    $dashboardUrl = auth()->check()
        ? \App\Support\RoleRedirector::pathFor(auth()->user())
        : null;
@endphp

<div x-data="{ accountOpen: false }" @click.outside="accountOpen = false" class="relative z-20 shrink-0">
    <button type="button"
            @click="accountOpen = ! accountOpen"
            @keydown.escape.window="accountOpen = false"
            :aria-expanded="accountOpen"
            aria-label="{{ auth()->check() ? __('site.nav.dashboard') : __('site.nav.login') }}"
            class="flex h-9 w-9 items-center justify-center rounded-full border border-ink-200 bg-white text-ink-700 transition hover:border-brand-300 hover:text-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-300">
        <x-ui-icon name="user" class="h-[17px] w-[17px]" />
    </button>

    <div x-show="accountOpen" x-cloak
         x-transition:enter="transition duration-150 ease-out"
         x-transition:enter-start="translate-y-1 opacity-0"
         x-transition:enter-end="translate-y-0 opacity-100"
         x-transition:leave="transition duration-100 ease-in"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="translate-y-1 opacity-0"
         class="absolute end-0 top-full mt-3 w-56 overflow-hidden rounded-2xl border border-ink-100 bg-white p-2 shadow-[0_18px_45px_-20px_rgba(7,20,38,0.35)]">
        @if (auth()->check())
            <a href="{{ $dashboardUrl }}"
               class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-[13.5px] font-semibold text-ink-700 transition hover:bg-ink-50 hover:text-brand-700">
                <x-ui-icon name="grid" class="h-4 w-4 text-ink-400" />
                {{ __('site.nav.dashboard') }}
            </a>
            <form method="POST" action="{{ route('researcher.logout') }}">
                @csrf
                <button type="submit"
                        class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left text-[13.5px] font-semibold text-ink-600 transition hover:bg-rose-50 hover:text-rose-700">
                    <x-ui-icon name="arrow-right" class="h-4 w-4" />
                    {{ __('researcher.nav.logout') }}
                </button>
            </form>
        @else
            <a href="{{ route('login') }}"
               class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-[13.5px] font-semibold text-ink-700 transition hover:bg-ink-50 hover:text-brand-700">
                <x-ui-icon name="arrow-right" class="h-4 w-4 text-ink-400" />
                {{ __('site.nav.login') }}
            </a>
        @endif
    </div>
</div>
