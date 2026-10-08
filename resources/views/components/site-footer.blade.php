@php
    $categories = $site->navCategories();
@endphp

<footer>

    {{-- Newsletter: full-width light band --}}
    <div id="newsletter" class="relative isolate overflow-hidden border-t border-brand-100 bg-brand-50">
        <div class="pointer-events-none absolute inset-0 -z-10 text-brand-700 grid-overlay opacity-50 [mask-image:linear-gradient(to_bottom,black,transparent)]" aria-hidden="true"></div>
        <div class="pointer-events-none absolute -right-32 top-1/2 -z-10 h-[30rem] w-[30rem] -translate-y-1/2 rounded-full border border-brand-200/60" aria-hidden="true"></div>
        <div class="pointer-events-none absolute -right-10 top-1/2 -z-10 h-[18rem] w-[18rem] -translate-y-1/2 rounded-full bg-white/70" aria-hidden="true"></div>

        <div class="container-rich py-16 sm:py-20">
            <div class="grid items-end gap-10 lg:grid-cols-[1.1fr_0.9fr] lg:gap-16">
                <div>
                    <p class="inline-flex items-center gap-2 text-[11px] font-semibold uppercase tracking-[0.2em] text-brand-700">
                        <span class="h-px w-8 bg-brand-600"></span>
                        {{ __('site.footer.newsletter') }}
                    </p>
                    <h3 class="mt-5 font-display text-3xl font-bold leading-[1.08] text-ink-950 sm:text-5xl">
                        {{ __('site.footer.newsletter_heading') }}<br class="hidden sm:block">
                        <span class="text-brand-600">{{ __('site.footer.newsletter_heading_accent') }}</span>
                    </h3>
                    <p class="mt-5 max-w-xl text-[16px] leading-relaxed text-ink-600">
                        {{ __('site.footer.newsletter_body') }}
                    </p>
                </div>

                <form action="{{ route('subscribe') }}" method="POST" class="w-full">
                    @csrf
                    <label for="footer-email" class="block text-[13px] font-semibold text-ink-700">{{ __('site.footer.email_label') }}</label>

                    <div class="group mt-3 flex items-center gap-3 border-b-2 border-ink-200 pb-3 transition-colors focus-within:border-brand-600">
                        <x-ui-icon name="mail" class="h-5 w-5 shrink-0 text-ink-400 transition-colors group-focus-within:text-brand-600" />
                        <input id="footer-email" type="email" name="email" required autocomplete="email" placeholder="{{ __('site.footer.email_placeholder') }}"
                               value="{{ old('email') }}"
                               class="w-full min-w-0 flex-1 bg-transparent py-2 font-display text-lg text-ink-950 placeholder:text-ink-400 focus:outline-none sm:text-xl">
                        <button type="submit" aria-label="{{ __('site.actions.subscribe') }}"
                                class="group/btn inline-flex h-12 shrink-0 items-center gap-2 rounded-full bg-brand-600 pl-5 pr-2 text-[14px] font-semibold text-white shadow-[0_10px_26px_-12px_var(--color-brand-600)] transition hover:bg-brand-700">
                            <span class="hidden sm:inline">{{ __('site.actions.subscribe') }}</span>
                            <span class="flex h-8 w-8 items-center justify-center rounded-full bg-white text-brand-700 transition-transform duration-300 group-hover/btn:rotate-[-45deg]">
                                <x-ui-icon name="arrow-right" class="h-4 w-4" />
                            </span>
                        </button>
                    </div>

                    @if (session('subscribed'))
                        <p class="mt-4 flex items-center gap-2 text-sm font-medium text-brand-700" role="status">
                            <span class="flex h-5 w-5 items-center justify-center rounded-full bg-brand-600 text-white"><x-ui-icon name="check" class="h-3 w-3" stroke="3" /></span>
                            {{ session('subscribed') }}
                        </p>
                    @endif
                    @error('email')
                        <p class="mt-4 text-sm text-red-600" role="alert">{{ $message }}</p>
                    @enderror
                </form>
            </div>

            {{-- Topics strip --}}
            <div class="mt-14 grid grid-cols-2 gap-px overflow-hidden rounded-2xl border border-brand-100 bg-brand-100 lg:grid-cols-4">
                @foreach ([['briefcase', 'consultancy'], ['document', 'research'], ['calendar', 'events'], ['handshake', 'collaboration']] as [$icon, $topic])
                    <div class="group flex items-center gap-3 bg-white px-5 py-4 transition-colors hover:bg-brand-50/60">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-600 transition-colors group-hover:bg-brand-600 group-hover:text-white">
                            <x-ui-icon :name="$icon" class="h-4 w-4" />
                        </span>
                        <span class="text-[13.5px] font-medium text-ink-700">{{ __('site.footer.topic_'.$topic) }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Main footer: dark --}}
    <div class="relative isolate overflow-hidden bg-ink-950 text-white/60">
        <div class="pointer-events-none absolute inset-0 -z-10 text-white grid-overlay opacity-[0.25] [mask-image:linear-gradient(to_top,black,transparent)]" aria-hidden="true"></div>

        <div class="container-rich grid gap-12 py-16 sm:py-20 md:grid-cols-2 lg:grid-cols-12">
            <div class="lg:col-span-4">
                <x-brand-mark :invert="true" class="h-24" />

                <p class="mt-6 max-w-sm text-[15px] leading-relaxed text-white/60">
                    {{ $site->get('about_intro') }}
                </p>

                <div class="mt-7 flex items-center gap-3">
                    @foreach ($site->socials() as $icon => $url)
                        <a href="{{ $url }}" target="_blank" rel="noopener noreferrer"
                           aria-label="{{ str_replace('-social', '', $icon) }}"
                           class="flex h-10 w-10 items-center justify-center rounded-full border border-white/10 bg-white/[0.04] text-white/70 transition hover:-translate-y-0.5 hover:border-brand-600 hover:bg-brand-600 hover:text-white">
                            <x-ui-icon :name="$icon" class="h-4 w-4" />
                        </a>
                    @endforeach
                </div>
            </div>

            <div class="lg:col-span-3">
                <h4 class="text-[12px] font-semibold uppercase tracking-[0.18em] !text-white">{{ __('site.footer.consultancy') }}</h4>
                <ul class="mt-5 space-y-3 text-[15px]">
                    @foreach ($categories as $category)
                        <li>
                            <a href="{{ route('services.show', $category) }}" class="group inline-flex items-center gap-2 text-white/60 transition hover:text-white">
                                <span class="h-px w-0 bg-brand-400 transition-all duration-300 group-hover:w-3"></span>
                                {{ $category->name }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="lg:col-span-2">
                <h4 class="text-[12px] font-semibold uppercase tracking-[0.18em] !text-white">{{ __('site.footer.explore') }}</h4>
                <ul class="mt-5 space-y-3 text-[15px]">
                    @foreach ([
                        ['site.nav.about', 'about'],
                        ['site.footer.research_innovation', 'research'],
                        ['site.nav.projects', 'projects.index'],
                        ['site.footer.experts', 'experts.index'],
                        ['site.nav.news', 'news.index'],
                        ['site.nav.membership', 'membership.create'],
                        ['site.nav.contact', 'contact'],
                    ] as [$label, $routeName])
                        <li>
                            <a href="{{ route($routeName) }}" class="group inline-flex items-center gap-2 text-white/60 transition hover:text-white">
                                <span class="h-px w-0 bg-brand-400 transition-all duration-300 group-hover:w-3"></span>
                                {{ __($label) }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="lg:col-span-3">
                <h4 class="text-[12px] font-semibold uppercase tracking-[0.18em] !text-white">{{ __('site.footer.contact') }}</h4>
                <ul class="mt-5 space-y-4 text-[15px] text-white/60">
                    @if ($address = $site->get('contact_address'))
                        <li class="flex gap-3">
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-white/[0.06] text-brand-300"><x-ui-icon name="map-pin" class="h-4 w-4" /></span>
                            <span class="pt-1">{{ $address }}</span>
                        </li>
                    @endif
                    @if ($email = $site->get('contact_email'))
                        <li class="flex gap-3">
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-white/[0.06] text-brand-300"><x-ui-icon name="mail" class="h-4 w-4" /></span>
                            <a href="mailto:{{ $email }}" class="pt-1 transition hover:text-white">{{ $email }}</a>
                        </li>
                    @endif
                    @if ($phone = $site->get('contact_phone'))
                        <li class="flex gap-3">
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-white/[0.06] text-brand-300"><x-ui-icon name="phone" class="h-4 w-4" /></span>
                            <a href="tel:{{ preg_replace('/[^\d+]/', '', $phone) }}" class="pt-1 transition hover:text-white">{{ $phone }}</a>
                        </li>
                    @endif
                    @if ($hours = $site->get('contact_hours'))
                        <li class="flex gap-3">
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-white/[0.06] text-brand-300"><x-ui-icon name="clock" class="h-4 w-4" /></span>
                            <span class="pt-1">{{ $hours }}</span>
                        </li>
                    @endif
                </ul>
            </div>
        </div>

        <div class="border-t border-white/10">
            <div class="container-rich flex flex-col items-center justify-between gap-3 py-6 text-sm text-white/45 sm:flex-row">
                <p>{{ __('site.footer.copyright', ['year' => date('Y'), 'name' => $site->name()]) }}</p>
                @if ($website = $site->get('contact_website'))
                    <a href="{{ $website }}" target="_blank" rel="noopener noreferrer"
                       class="flex items-center gap-1.5 transition hover:text-white">
                        {{ preg_replace('#^https?://#', '', $website) }}
                        <x-ui-icon name="external" class="h-3.5 w-3.5" />
                    </a>
                @endif
            </div>
        </div>
    </div>
</footer>
