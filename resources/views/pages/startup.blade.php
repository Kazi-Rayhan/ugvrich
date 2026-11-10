<x-layouts.app :title="__('site.nav.startup')"
               :description="__('site.startup.meta_description')">

    <x-page-hero
        :eyebrow="__('site.startup.hero_eyebrow')"
        :title="__('site.startup.hero_title')"
        :lead="__('site.startup.hero_lead')"
        :breadcrumbs="[__('site.nav.startup') => null]">
        @ideasopen
        <a href="{{ route('ideas.create') }}" class="btn-primary">
            {{ __('site.actions.submit_idea') }} <x-ui-icon name="arrow-up-right" class="h-4 w-4" />
        </a>
        @endideasopen
        <a href="#journey" class="btn-ghost">{{ __('site.startup.see_journey') }}</a>
    </x-page-hero>

    @php
        $stages = \App\Support\Vocabulary::all('startup_stages');

        // One icon per stage, in the same order as the config; the line beside
        // it is keyed by the same stage name.
        $icons = [
            'idea' => 'lightbulb',
            'evaluation' => 'check',
            'mentorship' => 'users',
            'prototype' => 'cog',
            'business_model' => 'chart',
            'funding' => 'star',
            'startup' => 'rocket',
            'market' => 'briefcase',
        ];
    @endphp

    {{-- ---------------- The journey ----------------
         A chain: every stage is a link on one continuous line, four to a row
         on desktop, folding to a vertical run on narrow screens. --}}
    <section id="journey" class="scroll-mt-28 bg-white py-16 sm:py-20">
        <div class="container-rich">
            <x-section-heading
                :eyebrow="__('site.startup.journey_eyebrow')"
                :title="__('site.startup.journey_title')"
                :lead="__('site.startup.journey_lead')" />

            <ol class="chain mt-14">
                @foreach ($stages as $key => $label)
                    @php
                        $icon = $icons[$key] ?? 'check';
                        $text = __('site.startup.stage_'.$key);
                        $n = $loop->iteration;
                    @endphp
                    <li @class(['chain-link reveal', 'has-line' => ! $loop->last])
                        style="transition-delay: {{ min($loop->index * 60, 420) }}ms">

                        <div class="chain-node">
                            <span class="chain-dot">
                                <x-ui-icon :name="$icon" class="h-5 w-5" />
                            </span>
                            <span class="chain-number">{{ str_pad($n, 2, '0', STR_PAD_LEFT) }}</span>
                        </div>

                        <div class="chain-body">
                            <h3 class="font-display text-[15.5px] font-bold leading-snug text-ink-950">{{ $label }}</h3>
                            @if ($text)
                                <p class="mt-2 text-[13px] leading-relaxed muted">{{ $text }}</p>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ol>

            <p class="mt-10 flex flex-wrap items-center justify-center gap-3 text-[13px] muted">
                <span class="inline-flex items-center gap-2">
                    <span class="h-2.5 w-2.5 rounded-full bg-brand-600"></span> {{ __('site.startup.journey_start') }}
                </span>
                <x-ui-icon name="arrow-right" class="h-3.5 w-3.5 text-ink-300" />
                <span class="inline-flex items-center gap-2">
                    <span class="h-2.5 w-2.5 rounded-full bg-navy-700"></span> {{ __('site.startup.journey_end') }}
                </span>
            </p>
        </div>
    </section>

    {{-- ---------------- What the hub provides ---------------- --}}
    <section class="border-t border-ink-100 bg-ink-50 py-16 sm:py-20">
        <div class="container-rich grid gap-12 lg:grid-cols-[0.9fr_1.1fr] lg:gap-16">
            <div>
                <x-section-heading
                    :eyebrow="__('site.startup.support_eyebrow')"
                    :title="__('site.startup.support_title')"
                    :lead="__('site.startup.support_lead')" />

                @ideasopen
                <a href="{{ route('ideas.create') }}" class="btn-primary reveal mt-8">
                    {{ __('site.actions.submit_idea') }} <x-ui-icon name="arrow-up-right" class="h-4 w-4" />
                </a>
                @endideasopen
            </div>

            <div class="grid gap-3 sm:grid-cols-2">
                @foreach ([
                    ['academic', 'mentor'],
                    ['beaker', 'lab'],
                    ['shield', 'ip'],
                    ['chart', 'model'],
                    ['star', 'funding'],
                    ['handshake', 'industry'],
                ] as $i => [$icon, $offer])
                    <div class="reveal rounded-2xl border border-ink-100 bg-white p-5" style="transition-delay: {{ min($i * 50, 300) }}ms">
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-50 text-brand-600">
                            <x-ui-icon :name="$icon" class="h-4.5 w-4.5" />
                        </span>
                        <p class="mt-4 font-display text-[15px] font-semibold text-ink-950">{{ __('site.startup.offer_'.$offer) }}</p>
                        <p class="mt-1.5 text-[13px] leading-relaxed muted">{{ __('site.startup.offer_'.$offer.'_note') }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ---------------- Who can submit ---------------- --}}
    <section class="border-t border-ink-100 bg-white py-16 sm:py-20">
        <div class="container-rich">
            <div class="reveal relative isolate overflow-hidden rounded-[2.5rem] bg-navy-700 px-7 py-12 sm:px-12 sm:py-14">
                <div class="pointer-events-none absolute inset-0 -z-10 text-white grid-overlay opacity-20" aria-hidden="true"></div>
                <div class="pointer-events-none absolute -right-24 -top-24 -z-10 h-72 w-72 rounded-full bg-brand-600/50" aria-hidden="true"></div>

                <div class="grid items-center gap-10 lg:grid-cols-[1.1fr_0.9fr] lg:gap-14">
                    <div>
                        <h2 class="font-display text-[26px] font-bold leading-tight !text-white sm:text-[34px]">
                            {{ __('site.startup.cta_title') }}
                        </h2>
                        <p class="mt-4 max-w-xl text-[15.5px] leading-relaxed text-white/75">
                            {{ __('site.startup.cta_body') }}
                        </p>
                        @ideasopen
                        <a href="{{ route('ideas.create') }}" class="btn-invert mt-8">
                            {{ __('site.actions.submit_idea') }} <x-ui-icon name="arrow-up-right" class="h-4 w-4" />
                        </a>
                        @endideasopen
                    </div>

                    <div class="flex flex-wrap gap-2">
                        @foreach (\App\Support\Vocabulary::all('idea_roles') as $role)
                            <span class="rounded-full border border-white/15 bg-white/[0.07] px-4 py-2 text-[13.5px] font-medium text-white/85">{{ $role }}</span>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>
</x-layouts.app>
