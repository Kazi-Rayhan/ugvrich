@if ($faqs->isNotEmpty())
    <section class="py-24 bg-white sm:py-28">
        <div class="container-rich grid gap-14 lg:grid-cols-[0.85fr_1.15fr] lg:gap-20">

            <div class="lg:sticky lg:top-28 lg:self-start">
                <x-section-heading
                    :eyebrow="__('site.home.faq_eyebrow')"
                    :title="__('site.home.faq_title')" />

                @php
                    $phone = $site->get('contact_phone');
                    $email = $site->get('contact_email');
                    $team = isset($experts) ? $experts->filter(fn ($e) => $e->photo)->take(4) : collect();
                @endphp

                <div class="reveal relative isolate mt-9 overflow-hidden rounded-[2rem] bg-brand-700 p-2 text-white"
                     style="transition-delay: 100ms">
                    <div class="pointer-events-none absolute inset-0 -z-10 text-ink-950 grid-overlay opacity-60" aria-hidden="true"></div>
                    <div class="pointer-events-none absolute -right-16 -top-16 -z-10 h-48 w-48 rounded-full bg-brand-600" aria-hidden="true"></div>
                    <div class="pointer-events-none absolute -right-28 -top-28 -z-10 h-72 w-72 rounded-full border border-white/10" aria-hidden="true"></div>

                    {{-- Intro --}}
                    <div class="px-6 pb-7 pt-7 sm:px-7">
                        @if ($team->isNotEmpty())
                            <div class="flex items-center gap-3">
                                <div class="flex -space-x-3">
                                    @foreach ($team as $member)
                                        <img src="{{ \Illuminate\Support\Facades\Storage::url($member->photo) }}" alt="{{ $member->name }}" loading="lazy"
                                             class="h-11 w-11 rounded-full object-cover object-top ring-[3px] ring-brand-700">
                                    @endforeach
                                </div>
                                <span class="text-[12.5px] font-medium leading-tight text-brand-100">{!! __('site.home.faq_team') !!}</span>
                            </div>
                        @else
                            <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white text-brand-700">
                                <x-ui-icon name="users" class="h-5 w-5" />
                            </span>
                        @endif

                        <p class="mt-6 font-display text-[26px] font-bold leading-tight !text-white">{{ __('site.home.faq_still') }}</p>
                        <p class="mt-2 text-[15px] leading-relaxed text-white/75">
                            {{ __('site.home.faq_still_body') }}
                        </p>
                    </div>

                    {{-- Contact tiles --}}
                    <div class="space-y-2 rounded-[1.6rem] bg-white p-2 text-ink-900">
                        @if ($phone)
                            <a href="tel:{{ preg_replace('/[^\d+]/', '', (string) $phone) }}"
                               class="group flex items-center gap-4 rounded-2xl p-3 transition hover:bg-brand-50">
                                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-600 transition group-hover:bg-brand-600 group-hover:text-white">
                                    <x-ui-icon name="phone" class="h-5 w-5" />
                                </span>
                                <span class="min-w-0 flex-1">
                                    <span class="block text-[11px] font-semibold uppercase tracking-[0.14em] text-ink-400">{{ __('site.home.faq_call') }}</span>
                                    <span class="block truncate font-display text-[16px] font-semibold text-ink-950">{{ $phone }}</span>
                                </span>
                                <x-ui-icon name="arrow-up-right" class="h-4 w-4 shrink-0 text-ink-300 transition group-hover:rotate-45 group-hover:text-brand-600" />
                            </a>
                        @endif
                        @if ($email)
                            <a href="mailto:{{ $email }}"
                               class="group flex items-center gap-4 rounded-2xl p-3 transition hover:bg-brand-50">
                                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-600 transition group-hover:bg-brand-600 group-hover:text-white">
                                    <x-ui-icon name="mail" class="h-5 w-5" />
                                </span>
                                <span class="min-w-0 flex-1">
                                    <span class="block text-[11px] font-semibold uppercase tracking-[0.14em] text-ink-400">{{ __('site.home.faq_email') }}</span>
                                    <span class="block truncate font-display text-[16px] font-semibold text-ink-950">{{ $email }}</span>
                                </span>
                                <x-ui-icon name="arrow-up-right" class="h-4 w-4 shrink-0 text-ink-300 transition group-hover:rotate-45 group-hover:text-brand-600" />
                            </a>
                        @endif

                        <a href="{{ route('contact') }}"
                           class="group flex items-center justify-center gap-2 rounded-2xl bg-brand-600 px-5 py-3.5 text-[14px] font-semibold !text-white transition hover:bg-brand-700">
                            {{ __('site.home.faq_contact') }}
                            <x-ui-icon name="arrow-right" class="h-4 w-4 transition-transform duration-300 group-hover:translate-x-1" />
                        </a>
                    </div>
                </div>
            </div>

            <div class="reveal space-y-3" x-data="{ open: 0 }">
                @foreach ($faqs as $i => $faq)
                    <div class="group/faq relative overflow-hidden rounded-2xl border transition-all duration-300"
                         :class="open === {{ $i }}
                             ? 'border-brand-200 bg-brand-50/50 shadow-[0_18px_40px_-28px_rgba(2,34,81,0.5)]'
                             : 'border-ink-100 bg-white hover:border-brand-100 hover:bg-ink-50/70'">

                        {{-- Accent bar on the open item --}}
                        <span class="absolute inset-y-0 left-0 w-1 origin-top bg-brand-600 transition-transform duration-500 ease-[cubic-bezier(0.22,1,0.36,1)]"
                              :class="open === {{ $i }} ? 'scale-y-100' : 'scale-y-0'" aria-hidden="true"></span>

                        <h3>
                            <button type="button" @click="open = open === {{ $i }} ? null : {{ $i }}"
                                    id="faq-q-{{ $i }}" aria-controls="faq-a-{{ $i }}"
                                    class="flex w-full items-center gap-4 px-5 py-5 text-left sm:px-6"
                                    :aria-expanded="open === {{ $i }}">
                                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg font-numeric text-[12.5px] font-bold tabular-nums transition-colors duration-300"
                                      :class="open === {{ $i }} ? 'bg-brand-600 text-white' : 'bg-ink-50 text-ink-400 group-hover/faq:bg-brand-50 group-hover/faq:text-brand-600'">
                                    {{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}
                                </span>
                                <span class="flex-1 font-display text-[16px] font-semibold leading-snug transition-colors duration-300"
                                      :class="open === {{ $i }} ? 'text-brand-700' : 'text-ink-950'">
                                    {{ $faq->question }}
                                </span>
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full border transition-all duration-300"
                                      :class="open === {{ $i }} ? 'rotate-45 border-brand-600 bg-brand-600 text-white' : 'border-ink-200 bg-white text-ink-600 group-hover/faq:border-brand-300 group-hover/faq:text-brand-600'">
                                    <x-ui-icon name="plus" class="h-4 w-4" />
                                </span>
                            </button>
                        </h3>

                        <div id="faq-a-{{ $i }}" role="region" aria-labelledby="faq-q-{{ $i }}"
                             x-show="open === {{ $i }}" x-collapse.duration.350ms @if ($i > 0) x-cloak @endif>
                            <p class="pb-6 pl-[4.25rem] pr-6 text-[15px] leading-relaxed text-ink-600 sm:pl-[4.5rem] sm:pr-16">{{ $faq->answer }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif
