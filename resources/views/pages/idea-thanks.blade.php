<x-layouts.app :title="__('site.thanks.idea_title')"
               :description="__('site.thanks.idea_description')">

    @php
        $firstName = \Illuminate\Support\Str::of($submission['name'] ?? '')
            ->replaceMatches('/^(Dr\.?|Prof\.?|Mr\.?|Ms\.?|Mrs\.?|Md\.?|Engr\.?)\s+/i', '')
            ->explode(' ')->first();
    @endphp

    <section class="relative isolate overflow-hidden bg-ink-50 py-16 sm:py-24">
        <div class="pointer-events-none absolute inset-0 -z-10 text-brand-700 grid-overlay opacity-40 [mask-image:radial-gradient(ellipse_at_top,black,transparent_70%)]" aria-hidden="true"></div>

        <div class="container-rich">
            <div class="mx-auto max-w-2xl text-center">
                <span class="mx-auto flex h-20 w-20 items-center justify-center rounded-3xl bg-brand-600 text-white">
                    <x-ui-icon name="lightbulb" class="h-8 w-8" />
                </span>

                <h1 class="mt-8 font-display text-4xl font-bold leading-[1.1] tracking-tight text-ink-950 sm:text-5xl">
                    {{ __('site.thanks.thank_you', ['name' => $firstName ? ', '.$firstName : '']) }}
                </h1>

                <p class="mx-auto mt-5 max-w-xl text-[17px] leading-relaxed muted">
                    {{ __('site.thanks.idea_body') }}
                </p>

                {{-- The innovator account: a new one waits for its password, an existing one already has the idea --}}
                @if (($submission['account'] ?? null) === 'new')
                    <div class="mx-auto mt-8 flex max-w-xl items-start gap-3 rounded-2xl border border-brand-200 bg-white p-5 text-left shadow-[0_20px_40px_-32px_rgba(7,20,38,0.4)]">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-600">
                            <x-ui-icon name="mail" class="h-5 w-5" />
                        </span>
                        <p class="text-[14.5px] leading-relaxed text-ink-700">{{ __('innovator.thanks.new', ['email' => $submission['email'] ?? '']) }}</p>
                    </div>
                @elseif (($submission['account'] ?? null) === 'existing')
                    <div class="mx-auto mt-8 flex max-w-xl flex-wrap items-center justify-between gap-3 rounded-2xl border border-brand-200 bg-white p-5 text-left">
                        <p class="text-[14.5px] text-ink-700">{{ __('innovator.thanks.existing') }}</p>
                        <a href="{{ auth()->user()?->isInnovator() ? route('innovator.dashboard') : route('login') }}" class="text-[14px] font-semibold text-brand-700 hover:underline">{{ __('innovator.thanks.dashboard') }} →</a>
                    </div>
                @endif

                <div class="mt-10 flex flex-wrap items-center justify-center gap-3">
                    <a href="{{ route('startup') }}" class="btn-primary group">
                        {!! __('site.thanks.back_to_startup') !!} <x-ui-icon name="arrow-right" class="h-4 w-4 transition-transform duration-300 group-hover:translate-x-0.5" />
                    </a>
                    <a href="{{ route('innovation.index') }}" class="btn-ghost">{{ __('site.nav.innovation') }}</a>
                    <a href="{{ route('projects.index') }}" class="btn-ghost">{{ __('site.thanks.see_projects') }}</a>
                </div>
            </div>
        </div>
    </section>
</x-layouts.app>
