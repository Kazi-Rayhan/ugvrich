<x-layouts.app :title="$expert->name" :description="$expert->research_interests">

    <x-page-hero
        :eyebrow="$expert->department"
        :title="$expert->name"
        :lead="$expert->designation"
        :breadcrumbs="[__('site.experts.breadcrumb') => route('experts.index'), $expert->name => null]" />

    <section class="py-16 bg-white sm:py-20">
        <div class="container-rich grid gap-12 lg:grid-cols-[0.65fr_1.35fr] lg:gap-16">

            {{-- Profile card --}}
            <aside class="lg:sticky lg:top-32 lg:self-start">
                <div class="card reveal text-center">
                    @if ($expert->photo)
                        <img src="{{ Storage::url($expert->photo) }}" alt="{{ $expert->name }}"
                             class="mx-auto h-32 w-32 rounded-3xl object-cover">
                    @else
                        <span class="mx-auto flex h-32 w-32 items-center justify-center rounded-3xl
                                     bg-brand-600 font-display text-4xl font-bold text-white">
                            {{ $expert->initials }}
                        </span>
                    @endif

                    <h2 class="mt-6 font-display text-xl font-bold">{{ $expert->name }}</h2>
                    <p class="mt-1.5 font-medium text-brand-600">{{ $expert->designation }}</p>
                    <p class="mt-1 text-[13.5px] muted">{{ $expert->department }}</p>

                    @if ($expert->category)
                        <a href="{{ route('services.show', $expert->category) }}"
                           class="mt-5 inline-flex items-center gap-2 rounded-full border hairline px-4 py-2 text-[12.5px] font-medium
                                  muted transition hover:border-brand-300 hover:text-brand-700">
                            <x-ui-icon :name="$expert->category->icon ?? 'grid'" class="h-3.5 w-3.5" />
                            {{ $expert->category->name }}
                        </a>
                    @endif

                    <div class="mt-7 space-y-2.5 border-t hairline pt-6 text-left">
                        @if ($expert->email)
                            <a href="mailto:{{ $expert->email }}"
                               class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-[13.5px] muted transition hover:text-brand-700 hover:bg-ink-50">
                                <x-ui-icon name="mail" class="h-4 w-4 shrink-0 text-brand-600" />
                                <span class="truncate">{{ $expert->email }}</span>
                            </a>
                        @endif
                        @if ($expert->phone)
                            <a href="tel:{{ preg_replace('/[^\d+]/', '', $expert->phone) }}"
                               class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-[13.5px] muted transition hover:text-brand-700 hover:bg-ink-50">
                                <x-ui-icon name="phone" class="h-4 w-4 shrink-0 text-brand-600" />
                                {{ $expert->phone }}
                            </a>
                        @endif
                        @foreach (['linkedin' => 'LinkedIn', 'scholar' => 'Google Scholar'] as $field => $label)
                            @if ($expert->{$field})
                                <a href="{{ $expert->{$field} }}" target="_blank" rel="noopener noreferrer"
                                   class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-[13.5px] muted transition hover:text-brand-700 hover:bg-ink-50">
                                    <x-ui-icon :name="$field === 'linkedin' ? 'linkedin' : 'academic'" class="h-4 w-4 shrink-0 text-brand-600" />
                                    {{ $label }}
                                </a>
                            @endif
                        @endforeach
                    </div>

                    <a href="{{ route('consultancy.create') }}" class="btn-primary mt-6 w-full">
                        {{ __('site.actions.request_consultancy') }} <x-ui-icon name="arrow-right" class="h-4 w-4" />
                    </a>
                </div>
            </aside>

            {{-- Detail --}}
            <div class="space-y-10">
                @if ($expert->expertise)
                    <div class="reveal">
                        <h3 class="font-display text-xl font-bold">{{ __('site.experts.expertise') }}</h3>
                        <div class="mt-5 flex flex-wrap gap-2.5">
                            @foreach ($expert->expertise as $tag)
                                <span class="rounded-full border hairline px-4 py-2 text-[13.5px] font-medium muted">{{ $tag }}</span>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if ($expert->research_interests)
                    <div class="reveal" style="transition-delay: 80ms">
                        <h3 class="font-display text-xl font-bold">{{ __('site.experts.interests') }}</h3>
                        <p class="mt-4 text-[16px] leading-[1.8] muted">{{ $expert->research_interests }}</p>
                    </div>
                @endif

                @if ($expert->bio)
                    <div class="prose-rich reveal text-[16px] muted" style="transition-delay: 140ms">
                        <h3 class="text-xl font-display font-bold text-ink-950">{{ __('site.experts.profile') }}</h3>
                        <div class="mt-4">{!! nl2br(e($expert->bio)) !!}</div>
                    </div>
                @endif

                <div class="reveal rounded-3xl border-l-4 border-brand-600 p-7 bg-ink-50">
                    <h3 class="font-display text-lg font-bold">{{ __('site.experts.available_for') }}</h3>
                    <ul class="mt-4 grid gap-2.5 sm:grid-cols-2">
                        @foreach (['consultancy', 'research', 'supervision', 'training', 'review', 'panels'] as $item)
                            <li class="flex items-start gap-2.5 text-[14px] muted">
                                <x-ui-icon name="check" class="mt-[3px] h-4 w-4 shrink-0 text-brand-600" stroke="2.4" />{{ __('site.experts.available_'.$item) }}
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </section>

    @if ($related->isNotEmpty())
        <section class="border-t hairline py-20 bg-ink-50">
            <div class="container-rich">
                <x-section-heading :eyebrow="__('site.experts.related_eyebrow')" :title="__('site.experts.related_title')" />
                <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($related as $i => $item)
                        <x-cards.expert-profile-card :expert="$item" :index="$i" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif

</x-layouts.app>
