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

    {{-- ---------------- The write-up ----------------
         Proposed: concept, how and why as a connected story. Current: the key
         highlights, then what comes next in a dark card of its own. Beside it,
         a sticky card with who is behind it, the SDGs and the way to partner. --}}
    @php
        // "Mechanical (Lead), EEE, CSE" or "… + …" → one chip per department.
        $people = collect(preg_split('/\s*[,;+]\s*|\s+(?:and|ও|এবং)\s+/u', rtrim((string) ($isProposed ? $item['departments'] : $item['lead']), '।. ')))
            ->map(fn ($x) => trim($x))->filter()->values();
        $story = [
            'concept' => ['lightbulb', 'from-sky-400 to-sky-600'],
            'how' => ['cog', 'from-brand-500 to-brand-700'],
            'why' => ['target', 'from-gold-300 to-gold-500'],
        ];
    @endphp

    <section class="relative isolate bg-white pb-16 pt-12 sm:pb-24 sm:pt-14">
        <div class="container-rich">
            <div class="grid gap-x-12 gap-y-10 lg:grid-cols-[minmax(0,1.35fr)_minmax(0,0.65fr)]">

                {{-- The story --}}
                <div>
                    @if ($isProposed)
                        <ol class="relative space-y-5">
                            {{-- The thread through the three --}}
                            <span class="absolute bottom-10 left-[1.65rem] top-10 hidden w-0.5 rounded-full bg-gradient-to-b from-sky-200 via-brand-200 to-gold-200 sm:block" aria-hidden="true"></span>

                            @foreach ($story as $field => [$icon, $gradient])
                                <li class="reveal relative flex gap-5" style="transition-delay: {{ $loop->index * 90 }}ms">
                                    <span class="relative z-10 flex h-[3.3rem] w-[3.3rem] shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br {{ $gradient }} text-white shadow-[0_14px_28px_-14px_rgba(7,20,38,0.6)] ring-4 ring-white">
                                        <x-ui-icon :name="$icon" class="h-6 w-6" />
                                        <span class="absolute -right-1.5 -top-1.5 flex h-5 w-5 items-center justify-center rounded-full bg-navy-900 font-numeric text-[10px] font-bold text-white">{{ $num($loop->iteration) }}</span>
                                    </span>
                                    <div class="min-w-0 flex-1 rounded-[1.5rem] border border-ink-100 bg-white p-6 transition duration-300 hover:border-brand-200 hover:shadow-[0_24px_50px_-36px_rgba(7,20,38,0.45)] sm:p-7">
                                        <h2 class="font-display text-[20px] font-bold text-ink-950 sm:text-[22px]">{{ $doc['headings'][$field] }}</h2>
                                        <p class="mt-3 text-[15.5px] leading-[1.9] text-ink-700">{{ $item[$field] }}</p>
                                    </div>
                                </li>
                            @endforeach
                        </ol>
                    @else
                        <div class="reveal flex gap-5">
                            <span class="flex h-[3.3rem] w-[3.3rem] shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-brand-500 to-brand-700 text-white shadow-[0_14px_28px_-14px_var(--color-brand-600)]">
                                <x-ui-icon name="lightbulb" class="h-6 w-6" />
                            </span>
                            <div class="min-w-0 flex-1 rounded-[1.5rem] border border-ink-100 bg-white p-6 sm:p-8">
                                <h2 class="font-display text-[22px] font-bold text-ink-950">{{ $doc['headings']['key_highlights'] }}</h2>
                                <p class="mt-3 text-[16px] leading-[1.9] text-ink-700">{{ $now }}</p>
                            </div>
                        </div>

                        @if ($upNext)
                            {{-- What comes next: its own dark card --}}
                            <div class="reveal relative isolate mt-6 overflow-hidden rounded-[1.75rem] bg-navy-800 p-7 text-white shadow-[0_40px_80px_-50px_rgba(2,22,52,0.85)] sm:p-9" style="transition-delay: 90ms">
                                <div class="pointer-events-none absolute -right-16 -top-16 -z-10 h-56 w-56 rounded-full bg-gold-400/25 blur-[70px]" aria-hidden="true"></div>
                                <div class="pointer-events-none absolute -bottom-20 -left-10 -z-10 h-56 w-56 rounded-full bg-brand-500/25 blur-[70px]" aria-hidden="true"></div>
                                <div class="pointer-events-none absolute inset-0 -z-10 text-white grid-overlay opacity-[0.06]" aria-hidden="true"></div>

                                <div class="flex items-center gap-4">
                                    <span class="relative flex h-12 w-12 items-center justify-center rounded-2xl bg-gold-300 text-navy-900 shadow-[0_0_30px_4px_rgba(242,205,107,0.35)]">
                                        <x-ui-icon name="rocket" class="h-6 w-6" />
                                    </span>
                                    <p class="font-display text-[22px] font-bold !text-white">{{ $doc['headings']['next'] }}</p>
                                </div>
                                <p class="mt-5 border-l-2 border-gold-300/60 pl-5 text-[16px] leading-[1.9] text-white/85">{{ $upNext }}</p>
                            </div>
                        @endif
                    @endif
                </div>

                {{-- The side card --}}
                <aside class="lg:sticky lg:top-28 lg:self-start">
                    <div class="reveal overflow-hidden rounded-[1.75rem] border border-ink-100 bg-white shadow-[0_30px_70px_-50px_rgba(7,20,38,0.45)]">
                        <div class="p-6 sm:p-7">
                            <p class="flex items-center gap-2 text-[11.5px] font-bold uppercase tracking-[0.16em] text-brand-600">
                                <x-ui-icon name="users" class="h-4 w-4" />
                                {{ $isProposed ? $doc['headings']['departments'] : $doc['headings']['lead_support'] }}
                            </p>
                            <ul class="mt-4 flex flex-wrap gap-2">
                                @foreach ($people as $person)
                                    <li @class([
                                        'rounded-full px-3.5 py-1.5 text-[13px] font-semibold',
                                        'bg-brand-600 text-white' => $loop->first,
                                        'bg-ink-50 text-ink-700 ring-1 ring-ink-100' => ! $loop->first,
                                    ])>{{ $person }}</li>
                                @endforeach
                            </ul>

                            @if ($isProposed && ($item['sdg'] ?? null))
                                <p class="mt-7 flex items-center gap-2 text-[11.5px] font-bold uppercase tracking-[0.16em] text-brand-600">
                                    <x-ui-icon name="globe" class="h-4 w-4" />
                                    {{ $doc['headings']['sdg'] }}
                                </p>
                                <ul class="mt-4 flex flex-wrap gap-2">
                                    @foreach ($chips($item['sdg']) as $chip)
                                        <li class="rounded-xl bg-navy-800 px-3 py-1.5 text-[12.5px] font-semibold text-white">{{ $chip }}</li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>

                        {{-- Partner on it --}}
                        <div class="relative isolate overflow-hidden bg-gradient-to-br from-brand-600 to-brand-800 p-6 text-white sm:p-7">
                            <div class="pointer-events-none absolute -right-10 -top-10 -z-10 h-32 w-32 rounded-full bg-white/10 blur-2xl" aria-hidden="true"></div>
                            <p class="text-[14px] leading-relaxed text-white/85">{{ __('site.innovation.partner_note') }}</p>
                            <a href="{{ route('contact') }}" data-magnetic
                               class="group mt-5 inline-flex w-full items-center justify-center gap-2 rounded-full bg-white px-5 py-3.5 text-[14px] font-bold text-brand-700 transition duration-300 hover:bg-gold-200 hover:text-navy-900">
                                {{ __('site.innovation.partner_cta') }}
                                <x-ui-icon name="arrow-right" class="h-4 w-4 transition-transform duration-300 group-hover:translate-x-1" />
                            </a>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </section>

    {{-- ---------------- The one before and the one after ---------------- --}}
    @if ($previous || $next)
        <section class="border-t border-ink-100 bg-ink-50 py-12 sm:py-14">
            <div class="container-rich grid gap-4 sm:grid-cols-2">
                @foreach ([['previous', $previous], ['next', $next]] as [$key, $neighbour])
                    @if ($neighbour)
                        <a href="{{ route('innovation.show', $neighbour['slug']) }}"
                           @class([
                               'group flex items-center gap-4 rounded-[1.5rem] border border-ink-100 bg-white p-5 transition duration-300 hover:-translate-y-0.5 hover:border-brand-200 hover:shadow-[0_22px_44px_-30px_rgba(7,20,38,0.45)] sm:p-6',
                               'flex-row-reverse text-end' => $key === 'next',
                           ])>
                            <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-ink-50 text-ink-600 ring-1 ring-ink-100 transition duration-300 group-hover:bg-brand-600 group-hover:text-white group-hover:ring-brand-600">
                                <x-ui-icon name="arrow-right" @class(['h-5 w-5 transition-transform duration-300', 'rotate-180 group-hover:-translate-x-0.5' => $key === 'previous', 'group-hover:translate-x-0.5' => $key === 'next']) />
                            </span>
                            <span class="min-w-0 flex-1">
                                <span class="block text-[11.5px] font-bold uppercase tracking-[0.16em] text-ink-400">{{ __('site.innovation.'.$key) }}</span>
                                <span class="mt-1 block truncate font-display text-[17px] font-bold text-ink-950 transition group-hover:text-brand-700">{{ $neighbour['name'] }}</span>
                            </span>
                        </a>
                    @else
                        <span class="hidden sm:block"></span>
                    @endif
                @endforeach
            </div>
        </section>
    @endif
</x-layouts.app>
