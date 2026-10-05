<x-layouts.app :title="$item['name']" :description="$item['concept'] ?? $item['highlights']">

    @php
        /*
         | One innovation, in full — in either phase.
         |
         | The Innovation page gives each a card with a paragraph on it;
         | everything the document says about one of them is here. Same wording,
         | same sections, same order — only the room is different.
         |
         | The two phases are written up differently. A proposed innovation has
         | the concept / how / why write-up with departments and SDGs beside it;
         | a current one has the team leading it and where the work stands.
         */
        $label = 'text-[10.5px] font-semibold uppercase tracking-[0.18em] text-ink-400';
        $rule = 'reveal mb-5 block h-1 w-12 rounded-full bg-brand-600';

        $num = fn ($value) => app()->getLocale() === 'bn'
            ? strtr((string) $value, ['0' => '০', '1' => '১', '2' => '২', '3' => '৩', '4' => '৪', '5' => '৫', '6' => '৬', '7' => '৭', '8' => '৮', '9' => '৯'])
            : (string) $value;

        $chips = fn (string $line) => collect(explode(',', $line))->map(fn ($chip) => trim($chip))->filter();

        // The document numbers its own headings; the page reads better without.
        $unnumbered = fn (string $text) => trim(preg_replace('/^[\d০-৯]+(?:\.[\d০-৯]+)*\.?\s*/u', '', $text));
        $title = fn (string $key) => $unnumbered($doc['headings'][$key] ?? '');

        // One icon per part of the write-up, matching the Innovation page.
        $fieldIcons = ['concept' => 'lightbulb', 'how' => 'cog', 'why' => 'target'];

        $isProposed = $phase === \App\Models\Innovation::PROPOSED;

        // The banner title: the name, and its other-language name smaller beside it.
        $heroTitle = e($item['name']).(isset($item['native'])
            ? ' <span class="font-sans text-[0.55em] font-medium text-white/60">('.e($item['native']).')</span>'
            : '');

        /* A current innovation's highlights paragraph carries what it does now
           and then what comes next, the same split the Innovation page makes. */
        if (! $isProposed) {
            $split = preg_split('/\s*'.preg_quote($doc['headings']['next'], '/').'\s*:\s*/u', $item['highlights'], 2);
            [$now, $upNext] = [$split[0], $split[1] ?? null];
        }
    @endphp

    {{-- ---------------- Hero: the usual page banner ---------------- --}}
    <x-page-hero
        :eyebrow="$isProposed ? __('site.innovation.type.proposed') : __('site.innovation.type.current')"
        :title="$heroTitle"
        :lead="$item['subtitle'] ?? ($isProposed ? $item['tagline'] : null)"
        :breadcrumbs="[__('site.nav.innovation') => route('innovation.index').'#areas', $item['name'] => null]" />

    {{-- ---------------- The innovation's photograph, in full ---------------- --}}
    <section class="bg-white pt-12 sm:pt-16">
        <div class="container-rich">
            <figure class="reveal relative overflow-hidden rounded-[2rem] bg-ink-100 shadow-[0_40px_80px_-50px_rgba(7,20,38,0.55)]">
                <x-media-frame :src="$cover" :alt="$item['name']" :seed="$item['name'].' '.($item['tagline'] ?? '')"
                               icon="lightbulb" ratio="aspect-[16/10] sm:aspect-[21/9]" />

                <figcaption class="absolute left-5 top-5 flex flex-wrap items-center gap-2 sm:left-7 sm:top-7">
                    <span class="rounded-lg bg-white/90 px-2.5 py-1 font-numeric text-[12px] font-bold tabular-nums text-ink-900 backdrop-blur-sm">
                        {{ $num(str_pad($index + 1, 2, '0', STR_PAD_LEFT)) }}
                    </span>
                    <span @class([
                        'inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-[12px] font-bold uppercase tracking-[0.08em] shadow-[0_8px_18px_-10px_rgba(7,20,38,0.6)]',
                        'bg-brand-600 text-white' => ! $isProposed,
                        'bg-gold-300 text-navy-900' => $isProposed,
                    ])>
                        <x-ui-icon :name="$isProposed ? 'lightbulb' : 'cog'" class="h-3.5 w-3.5" />
                        {{ $isProposed ? __('site.innovation.type.proposed') : __('site.innovation.type.current') }}
                    </span>
                </figcaption>
            </figure>
        </div>
    </section>

    {{-- ---------------- The write-up ---------------- --}}
    <section class="bg-white pb-16 pt-12 sm:pb-20 sm:pt-14">
        <div class="container-rich">
            <div class="grid gap-x-12 gap-y-10 lg:grid-cols-[1.3fr_0.7fr]">

                <div class="space-y-10">
                    @if ($isProposed)
                        @foreach (['concept', 'how', 'why'] as $field)
                            <div class="reveal relative ps-10">
                                <span class="absolute left-0 top-0 flex h-7 w-7 items-center justify-center rounded-xl bg-brand-50 text-brand-600">
                                    <x-ui-icon :name="$fieldIcons[$field]" class="h-4 w-4" />
                                </span>

                                <h2 class="{{ $label }}">{{ $doc['headings'][$field] }}</h2>
                                <p class="mt-3 text-[15.5px] leading-[1.9] text-ink-700">{{ $item[$field] }}</p>
                            </div>
                        @endforeach
                    @else
                        <div class="reveal relative ps-10">
                            <span class="absolute left-0 top-0 flex h-7 w-7 items-center justify-center rounded-xl bg-brand-50 text-brand-600">
                                <x-ui-icon name="lightbulb" class="h-4 w-4" />
                            </span>

                            <h2 class="{{ $label }}">{{ $doc['headings']['key_highlights'] }}</h2>
                            <p class="mt-3 text-[15.5px] leading-[1.9] text-ink-700">{{ $now }}</p>
                        </div>

                        @if ($upNext)
                            <div class="reveal rounded-[1.5rem] bg-gradient-to-br from-brand-50 to-white p-7 ring-1 ring-brand-100">
                                <p class="flex items-center gap-2 {{ $label }}">
                                    <x-ui-icon name="arrow-right" class="h-3.5 w-3.5 text-brand-600" />
                                    {{ $doc['headings']['next'] }}
                                </p>
                                <p class="mt-3 text-[15px] leading-[1.9] text-brand-900">{{ $upNext }}</p>
                            </div>
                        @endif
                    @endif
                </div>

                <aside class="reveal space-y-7 self-start rounded-[1.75rem] bg-ink-50/80 p-7 sm:p-8">
                    <div>
                        <p class="flex items-center gap-2 {{ $label }}">
                            <x-ui-icon name="users" class="h-3.5 w-3.5 text-brand-600" />
                            {{ $isProposed ? $doc['headings']['departments'] : $doc['headings']['lead_support'] }}
                        </p>
                        <p class="mt-3 text-[14px] leading-[1.85] text-ink-700">{{ $isProposed ? $item['departments'] : $item['lead'] }}</p>
                    </div>

                    @if ($isProposed)
                        <div class="border-t border-ink-200 pt-6">
                            <p class="flex items-center gap-2 {{ $label }}">
                                <x-ui-icon name="globe" class="h-3.5 w-3.5 text-brand-600" />
                                {{ $doc['headings']['sdg'] }}
                            </p>
                            <ul class="mt-3 flex flex-wrap gap-1.5">
                                @foreach ($chips($item['sdg']) as $chip)
                                    <li class="rounded-lg border border-navy-100 bg-white px-2.5 py-1 text-[12.5px] font-semibold text-navy-700">{{ $chip }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="border-t border-ink-200 pt-6">
                        <a href="{{ route('contact') }}"
                           class="inline-flex w-full items-center justify-center gap-2 rounded-full bg-brand-600 px-5 py-3 text-[14px] font-semibold text-white transition duration-300 hover:bg-brand-500">
                            {{ __('site.innovation.partner_cta') }}
                            <x-ui-icon name="arrow-right" class="h-4 w-4" />
                        </a>
                        <p class="mt-3 text-[12.5px] leading-relaxed text-ink-500">{{ __('site.innovation.partner_note') }}</p>
                    </div>
                </aside>
            </div>
        </div>
    </section>

    {{-- ---------------- The one before and the one after ---------------- --}}
    @if ($previous || $next)
        <section class="border-t border-ink-100 bg-ink-50 py-12">
            <div class="container-rich grid gap-4 sm:grid-cols-2">
                @foreach ([['previous', $previous, 'rotate-180', 'text-start'], ['next', $next, '', 'text-end sm:justify-self-end']] as [$key, $neighbour, $flip, $align])
                    @if ($neighbour)
                        <a href="{{ route('innovation.show', $neighbour['slug']) }}"
                           class="group flex w-full flex-col rounded-2xl border border-ink-200 bg-white p-5 transition duration-300 hover:-translate-y-0.5 hover:border-brand-300 {{ $align }}">
                            <span class="flex items-center gap-2 {{ $label }} {{ $key === 'next' ? 'justify-end' : '' }}">
                                @if ($key === 'previous')
                                    <x-ui-icon name="arrow-right" class="h-3 w-3 {{ $flip }}" />
                                @endif
                                {{ __('site.innovation.'.$key) }}
                                @if ($key === 'next')
                                    <x-ui-icon name="arrow-right" class="h-3 w-3" />
                                @endif
                            </span>
                            <span class="mt-1.5 font-display text-[16px] font-bold text-ink-950 transition group-hover:text-brand-700">{{ $neighbour['name'] }}</span>
                        </a>
                    @else
                        <span></span>
                    @endif
                @endforeach
            </div>
        </section>
    @endif
</x-layouts.app>
