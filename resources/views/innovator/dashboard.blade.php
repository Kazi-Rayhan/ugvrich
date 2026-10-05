<x-layouts.app :title="__('innovator.portal')">

    @php
        $num = fn ($value) => \App\Support\Numerals::localize((string) $value);
        $stageKeys = array_keys($stages);

        // Status pills: one colour per state the office can set.
        $statusTone = [
            'new' => 'bg-sky-50 text-sky-700 ring-sky-200',
            'in_review' => 'bg-amber-50 text-amber-700 ring-amber-200',
            'accepted' => 'bg-brand-50 text-brand-700 ring-brand-200',
            'on_hold' => 'bg-ink-50 text-ink-600 ring-ink-200',
            'declined' => 'bg-rose-50 text-rose-700 ring-rose-200',
        ];
    @endphp

    {{-- ---------------- Welcome ---------------- --}}
    <section class="relative isolate overflow-hidden bg-navy-800 text-white">
        <div class="pointer-events-none absolute inset-0 -z-10 text-white grid-overlay opacity-[0.1]" aria-hidden="true"></div>
        <div class="pointer-events-none absolute -right-32 -top-40 -z-10 h-[28rem] w-[28rem] rounded-full bg-brand-600/30 blur-[120px]" aria-hidden="true"></div>

        <div class="container-rich flex flex-col gap-8 py-14 sm:py-16 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="eyebrow-invert">{{ __('innovator.eyebrow') }}</p>
                <h1 class="mt-6 font-display text-[32px] font-bold leading-[1.1] tracking-[-0.02em] !text-white sm:text-[44px]">
                    {{ __('innovator.welcome', ['name' => \Illuminate\Support\Str::of($user->name)->explode(' ')->first()]) }}
                </h1>
                <p class="mt-4 max-w-xl text-[16px] leading-[1.75] text-white/70">{{ __('innovator.lead') }}</p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('ideas.create') }}" class="btn-lead group" data-magnetic>
                    <span class="relative">{{ __('innovator.submit_another') }}</span>
                    <x-ui-icon name="plus" class="relative h-[18px] w-[18px]" />
                </a>
                <form method="POST" action="{{ route('innovator.logout') }}">
                    @csrf
                    <button type="submit" class="btn-invert px-6 py-4 text-[14px]">{{ __('innovator.logout') }}</button>
                </form>
            </div>
        </div>
    </section>

    <section class="bg-ink-50 py-12 sm:py-16">
        <div class="container-rich">

            @if (session('saved'))
                <div class="mb-6 flex items-center gap-3 rounded-2xl border border-brand-200 bg-brand-50 px-5 py-4 text-[14px] font-medium text-brand-800" role="status">
                    <x-ui-icon name="check" class="h-5 w-5 text-brand-600" stroke="2.4" /> {{ session('saved') }}
                </div>
            @endif

            {{-- Three figures --}}
            <div class="grid gap-4 sm:grid-cols-3">
                @foreach ([
                    [$ideas->count(), __('innovator.stats.ideas'), 'lightbulb'],
                    [$ideas->where('status', 'in_review')->count(), __('innovator.stats.in_review'), 'clock'],
                    [$ideas->where('status', 'accepted')->count(), __('innovator.stats.accepted'), 'star'],
                ] as [$value, $label, $icon])
                    <div class="flex items-center gap-4 rounded-[1.5rem] border border-ink-100 bg-white p-5">
                        <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-brand-50 text-brand-600 ring-1 ring-brand-100">
                            <x-ui-icon :name="$icon" class="h-5 w-5" />
                        </span>
                        <div>
                            <p class="font-display text-[28px] font-bold leading-none tabular-nums text-ink-950">{{ $num($value) }}</p>
                            <p class="mt-1 text-[13px] text-ink-500">{{ trim($label) }}</p>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- ---------------- My ideas ---------------- --}}
            <h2 class="mt-12 font-display text-[24px] font-bold text-ink-950">{{ __('innovator.ideas_title') }}</h2>

            @forelse ($ideas as $idea)
                @php
                    $at = array_search($idea->stage, $stageKeys, true);
                    $at = $at === false ? 0 : $at;
                @endphp
                <article class="mt-5 overflow-hidden rounded-[1.75rem] border border-ink-100 bg-white shadow-[0_20px_50px_-40px_rgba(7,20,38,0.45)]">
                    <div class="flex flex-col gap-4 p-6 sm:flex-row sm:items-start sm:justify-between sm:p-7">
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="font-numeric text-[12px] font-bold tabular-nums text-ink-400">{{ $idea->reference }}</span>
                                @if ($idea->category_name)
                                    <span class="rounded-full bg-navy-50 px-3 py-1 text-[12px] font-semibold text-navy-700">{{ $idea->category_name }}</span>
                                @endif
                            </div>
                            <h3 class="mt-2.5 font-display text-[20px] font-bold leading-snug text-ink-950">{{ $idea->title }}</h3>
                            <p class="mt-1.5 text-[13px] text-ink-500">{{ __('innovator.submitted', ['date' => $num($idea->created_at->translatedFormat('j F Y'))]) }}</p>
                        </div>

                        <div class="flex shrink-0 flex-wrap items-center gap-2">
                            <span class="rounded-full px-3.5 py-1.5 text-[12.5px] font-semibold ring-1 {{ $statusTone[$idea->status] ?? $statusTone['new'] }}">
                                {{ __('innovator.status.'.$idea->status) }}
                            </span>
                            @if ($idea->document)
                                <a href="{{ Storage::url($idea->document) }}" target="_blank" rel="noopener"
                                   class="inline-flex items-center gap-1.5 rounded-full border border-ink-200 px-3.5 py-1.5 text-[12.5px] font-semibold text-ink-700 transition hover:border-brand-300 hover:text-brand-700">
                                    <x-ui-icon name="document" class="h-3.5 w-3.5" /> {{ __('innovator.download') }}
                                </a>
                            @endif
                        </div>
                    </div>

                    {{-- The journey: done in green, the current stage ringed, the rest to come --}}
                    <div class="border-t border-ink-100 bg-ink-50/50 px-6 py-5 sm:px-7">
                        <div class="flex items-center justify-between gap-3">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-ink-400">{{ __('innovator.journey') }}</p>
                            <p class="text-[12.5px] font-semibold text-brand-700">
                                {{ __('innovator.stage_of', ['current' => $num($at + 1), 'total' => $num(count($stageKeys))]) }} · {{ $stages[$stageKeys[$at]] }}
                            </p>
                        </div>

                        <ol class="mt-4 grid grid-cols-8 gap-1.5" aria-label="{{ __('innovator.journey') }}">
                            @foreach ($stages as $key => $label)
                                <li class="min-w-0" @if ($loop->index === $at) aria-current="step" @endif>
                                    <span @class([
                                        'block h-2 rounded-full',
                                        'bg-brand-600' => $loop->index < $at,
                                        'bg-gold-400 ring-4 ring-gold-100' => $loop->index === $at,
                                        'bg-ink-200' => $loop->index > $at,
                                    ])></span>
                                    <span @class([
                                        'mt-2 hidden truncate text-[11px] leading-tight lg:block',
                                        'font-semibold text-ink-900' => $loop->index === $at,
                                        'text-ink-500' => $loop->index !== $at,
                                    ]) title="{{ $label }}">{{ $label }}</span>
                                </li>
                            @endforeach
                        </ol>
                    </div>
                </article>
            @empty
                <div class="mt-5 flex flex-col items-center rounded-[1.75rem] border-2 border-dashed border-ink-200 bg-white px-6 py-14 text-center">
                    <span class="flex h-14 w-14 items-center justify-center rounded-2xl bg-brand-50 text-brand-600">
                        <x-ui-icon name="lightbulb" class="h-6 w-6" />
                    </span>
                    <p class="mt-4 font-display text-[18px] font-bold text-ink-950">{{ __('innovator.empty_title') }}</p>
                    <p class="mt-1.5 text-[14px] text-ink-500">{{ __('innovator.empty_body') }}</p>
                    <a href="{{ route('ideas.create') }}" class="btn-primary mt-6">{{ __('innovator.submit_another') }}</a>
                </div>
            @endforelse
        </div>
    </section>
</x-layouts.app>
