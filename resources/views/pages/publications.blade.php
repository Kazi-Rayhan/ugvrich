<x-layouts.app :title="__('site.nav.publications')"
               :description="__('site.publications.meta_description')">

    <x-page-hero
        :eyebrow="__('site.nav.publications')"
        :title="__('site.publications.hero_title')"
        :lead="__('site.publications.hero_lead')"
        :breadcrumbs="[__('site.nav.publications') => null]" />

    @php
        $papers = $journals->concat($conferences)->concat($other);
    @endphp

    {{-- ---------------- Papers ---------------- --}}
    <section class="bg-white py-16 sm:py-20"
             x-data="{ kind: 'all' }">
        <div class="container-rich">
            <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                <x-section-heading
                    :eyebrow="__('site.publications.papers_eyebrow')"
                    :title="__('site.publications.papers_title', ['count' => $papers->count()])"
                    :lead="__('site.publications.papers_lead')" />

                <div class="reveal flex flex-wrap gap-1.5 rounded-2xl border border-ink-200 bg-ink-50 p-1.5">
                    @foreach ([['all', $papers->count()], ['journal', $journals->count()], ['conference', $conferences->count()], ['publication', $other->count()]] as [$key, $count])
                        @if ($count || $key === 'all')
                            <button type="button" @click="kind = '{{ $key }}'"
                                    class="area-tab !py-2 !text-[13px]" :class="kind === '{{ $key }}' && 'is-on'">
                                {{ __('site.publications.filter_'.$key) }}
                                <span class="area-tab-count">{{ $count }}</span>
                            </button>
                        @endif
                    @endforeach
                </div>
            </div>

            <div class="mt-10 grid gap-4 md:grid-cols-2">
                @forelse ($papers as $i => $item)
                    <article class="reveal" style="transition-delay: {{ min($i * 60, 300) }}ms"
                             x-show="kind === 'all' || kind === '{{ $item->kind }}'" x-transition.opacity>
                        <div class="group relative flex h-full gap-5 overflow-hidden rounded-[1.5rem] border border-ink-100 bg-white p-5 transition duration-500 hover:-translate-y-0.5 hover:border-brand-200 hover:shadow-[0_22px_48px_-30px_rgba(2,34,81,0.45)] sm:p-6">
                            <div class="flex w-16 shrink-0 flex-col items-center self-start overflow-hidden rounded-2xl border border-ink-100 text-center transition-colors group-hover:border-brand-200">
                                <span class="w-full bg-navy-700 py-1 text-[10px] font-bold uppercase tracking-[0.14em] text-white transition-colors group-hover:bg-brand-600">{{ __('site.publications.year') }}</span>
                                <span class="py-2 font-numeric text-[17px] font-bold tabular-nums text-ink-950">{{ $item->year ?: '—' }}</span>
                            </div>

                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-1.5">
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-brand-50 px-2.5 py-1 text-[11.5px] font-semibold text-brand-700">
                                        <x-ui-icon :name="$item->kind === 'conference' ? 'users' : 'document'" class="h-3.5 w-3.5 shrink-0" />
                                        {{ \App\Support\Vocabulary::label('publication_kinds', $item->kind, 'Publication') }}
                                    </span>
                                    @if ($item->venue)
                                        <span class="truncate text-[12.5px] muted">{{ $item->venue }}</span>
                                    @endif
                                </div>

                                <h3 class="mt-3 font-display text-[17px] font-bold leading-snug text-ink-950 transition-colors group-hover:text-brand-700">{{ $item->title }}</h3>

                                @if ($item->authors)
                                    <p class="mt-2 flex items-center gap-1.5 text-[13.5px] muted">
                                        <x-ui-icon name="users" class="h-3.5 w-3.5 shrink-0 text-ink-400" /> {{ $item->authors }}
                                    </p>
                                @endif

                                @if ($item->doi)
                                    <p class="mt-1.5 text-[12.5px] muted">DOI: {{ $item->doi }}</p>
                                @endif

                                @if ($item->url)
                                    <a href="{{ $item->url }}" target="_blank" rel="noopener noreferrer"
                                       class="mt-4 inline-flex items-center gap-1.5 text-[13px] font-semibold text-brand-600 after:absolute after:inset-0">
                                        {{ __('site.publications.view_publication') }} <x-ui-icon name="external" class="h-3.5 w-3.5" />
                                    </a>
                                @endif
                            </div>
                        </div>
                    </article>
                @empty
                    <p class="rounded-2xl border-2 border-dashed border-ink-200 p-8 text-center text-[14.5px] muted md:col-span-2">{{ __('site.publications.empty') }}</p>
                @endforelse
            </div>
        </div>
    </section>

    {{-- ---------------- Funded projects ---------------- --}}
    @if ($funded->isNotEmpty())
        <section class="border-t border-ink-100 bg-ink-50 py-16 sm:py-20">
            <div class="container-rich">
                <x-section-heading
                    :eyebrow="__('site.publications.funding_eyebrow')"
                    :title="__('site.publications.funding_title')"
                    :lead="__('site.publications.funding_lead')" />

                <div class="mt-10 grid gap-4 md:grid-cols-2">
                    @foreach ($funded as $i => $item)
                        <div class="reveal relative isolate overflow-hidden rounded-[1.75rem] bg-navy-700 p-6 text-white sm:p-7" style="transition-delay: {{ min($i * 70, 350) }}ms">
                            <div class="pointer-events-none absolute inset-0 -z-10 text-white grid-overlay opacity-20" aria-hidden="true"></div>
                            <div class="pointer-events-none absolute -right-16 -top-16 -z-10 h-44 w-44 rounded-full bg-brand-600/40" aria-hidden="true"></div>

                            <div class="flex items-start justify-between gap-3">
                                <h3 class="font-display text-[17px] font-bold leading-snug !text-white">{{ $item->title }}</h3>
                                @if ($item->year)
                                    <span class="shrink-0 rounded-full bg-white px-2.5 py-0.5 font-numeric text-[12px] font-bold tabular-nums text-navy-700">{{ $item->year }}</span>
                                @endif
                            </div>

                            <dl class="mt-4 space-y-2 text-[13.5px]">
                                @if ($item->venue)
                                    <div class="flex items-center gap-2 text-white/85">
                                        <dt class="sr-only">{{ __('site.publications.funder') }}</dt>
                                        <x-ui-icon name="building" class="h-4 w-4 shrink-0 text-brand-300" />
                                        <dd>{{ $item->venue }}</dd>
                                    </div>
                                @endif
                                @if ($item->authors)
                                    <div class="flex items-center gap-2 text-white/70">
                                        <dt class="sr-only">{{ __('site.publications.investigators') }}</dt>
                                        <x-ui-icon name="users" class="h-4 w-4 shrink-0 text-brand-300" />
                                        <dd>{{ $item->authors }}</dd>
                                    </div>
                                @endif
                            </dl>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
</x-layouts.app>
