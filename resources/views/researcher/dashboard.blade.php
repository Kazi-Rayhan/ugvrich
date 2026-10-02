@php
    /* Imports live above the component tag: the slot compiles to a closure,
       and PHP will not accept a `use` import inside one. */
    use App\Models\ResearchIdea;
@endphp

<x-layouts.researcher :title="__('researcher.nav.dashboard')">

    @php
        $user = auth()->user();
        $statuses = ResearchIdea::statuses();
    @endphp

    <div class="space-y-7">

        {{-- Greeting --}}
        <div>
            <h2 class="font-display text-[23px] font-bold tracking-tight text-ink-950 sm:text-[27px]">
                {{ __('researcher.dashboard.welcome', ['name' => \Illuminate\Support\Str::of($user->name)->words(1, '')]) }}
            </h2>
            <p class="mt-2 text-[14.5px] text-ink-600">{{ __('researcher.dashboard.lead') }}</p>
        </div>

        {{-- Profile completion. Shown as a prompt, never as a gate: the rest of
             the portal stays usable whatever this says. --}}
        @if ($completion < 100)
            <div class="rounded-[1.5rem] border border-brand-200 bg-brand-50/70 p-6 sm:p-7">
                <div class="flex flex-wrap items-start justify-between gap-5">
                    <div class="min-w-0">
                        <p class="font-display text-[17px] font-bold text-ink-950">{{ __('researcher.dashboard.complete_cta') }}</p>
                        <p class="mt-1.5 max-w-xl text-[13.5px] leading-relaxed text-ink-600">{{ __('researcher.dashboard.complete_lead') }}</p>
                    </div>

                    <a href="{{ route('researcher.profile') }}" class="btn-primary shrink-0">
                        {{ __('researcher.profile.title') }} <x-ui-icon name="arrow-right" class="h-4 w-4" />
                    </a>
                </div>

                <div class="mt-6">
                    <div class="flex items-center justify-between text-[12.5px] font-semibold">
                        <span class="text-ink-600">{{ __('researcher.dashboard.completion') }}</span>
                        <span class="font-numeric tabular-nums text-brand-700">{{ $completion }}%</span>
                    </div>
                    <div class="mt-2 h-2 overflow-hidden rounded-full bg-white">
                        <div class="h-full rounded-full bg-brand-600 transition-all duration-700" style="width: {{ $completion }}%"></div>
                    </div>
                </div>

                <ul class="mt-5 flex flex-wrap gap-x-5 gap-y-2">
                    @foreach ($checklist as $section => $done)
                        <li class="flex items-center gap-2 text-[13px] {{ $done ? 'text-ink-700' : 'text-ink-400' }}">
                            @if ($done)
                                <x-ui-icon name="check" class="h-4 w-4 text-brand-600" stroke="2.6" />
                            @else
                                <span class="flex h-4 w-4 items-center justify-center"><span class="h-2 w-2 rounded-full ring-1 ring-ink-300"></span></span>
                            @endif
                            {{ __('researcher.profile.sections.'.$section) }}
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- The numbers --}}
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ([
                ['ideas', $counts['ideas'], 'lightbulb', route('researcher.ideas.index')],
                ['proposals', $counts['proposals'], 'document', null],
                ['projects', $counts['projects'], 'briefcase', null],
                ['papers', $counts['papers'], 'academic', null],
            ] as [$key, $value, $icon, $url])
                <{{ $url ? 'a' : 'div' }} @if ($url) href="{{ $url }}" @endif
                    class="group rounded-2xl border border-ink-100 bg-white p-5 transition duration-300 {{ $url ? 'hover:-translate-y-1 hover:border-brand-300' : '' }}">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-ink-50 text-ink-500 transition group-hover:bg-brand-50 group-hover:text-brand-600">
                        <x-ui-icon :name="$icon" class="h-[18px] w-[18px]" />
                    </span>
                    <p class="mt-4 font-numeric text-[30px] font-bold leading-none tabular-nums text-ink-950">{{ $value }}</p>
                    <p class="mt-2 text-[13px] font-medium text-ink-600">{{ __('researcher.dashboard.counts.'.$key) }}</p>
                </{{ $url ? 'a' : 'div' }}>
            @endforeach
        </div>

        <div class="grid gap-6 lg:grid-cols-[1.4fr_0.6fr]">

            {{-- Ideas so far --}}
            <div class="rounded-[1.5rem] border border-ink-100 bg-white">
                <div class="flex items-center justify-between gap-4 border-b border-ink-100 px-6 py-4">
                    <p class="font-display text-[15.5px] font-bold text-ink-950">{{ __('researcher.ideas.title') }}</p>
                    <a href="{{ route('researcher.ideas.create') }}" class="inline-flex items-center gap-1.5 text-[13px] font-semibold text-brand-700 hover:text-brand-600">
                        <x-ui-icon name="plus" class="h-4 w-4" />
                        {{ __('researcher.ideas.new') }}
                    </a>
                </div>

                @if ($ideas->isEmpty())
                    <div class="px-6 py-10 text-center">
                        <p class="text-[14.5px] font-semibold text-ink-700">{{ __('researcher.ideas.none') }}</p>
                        <p class="mx-auto mt-2 max-w-sm text-[13.5px] leading-relaxed text-ink-500">{{ __('researcher.ideas.none_lead') }}</p>
                        <a href="{{ route('researcher.ideas.create') }}" class="btn-primary mt-6">
                            {{ __('researcher.ideas.new') }} <x-ui-icon name="arrow-right" class="h-4 w-4" />
                        </a>
                    </div>
                @else
                    <ul class="divide-y divide-ink-100">
                        @foreach ($ideas->take(5) as $idea)
                            <li>
                                <a href="{{ route('researcher.ideas.show', $idea) }}" class="flex items-center justify-between gap-4 px-6 py-4 transition hover:bg-ink-50/70">
                                    <span class="min-w-0">
                                        <span class="block truncate text-[14px] font-semibold text-ink-950">{{ $idea->title }}</span>
                                        <span class="mt-0.5 block text-[12.5px] text-ink-500">{{ $idea->department }} · {{ $idea->research_field }}</span>
                                    </span>
                                    <x-researcher-status :status="$idea->status" :labels="$statuses" />
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            {{-- What has happened --}}
            <div class="rounded-[1.5rem] border border-ink-100 bg-white">
                <p class="border-b border-ink-100 px-6 py-4 font-display text-[15.5px] font-bold text-ink-950">{{ __('researcher.dashboard.activity') }}</p>

                @if ($activity->isEmpty())
                    <p class="px-6 py-8 text-[13.5px] leading-relaxed text-ink-500">{{ __('researcher.dashboard.no_activity') }}</p>
                @else
                    <ul class="divide-y divide-ink-100">
                        @foreach ($activity as $review)
                            <li class="px-6 py-4">
                                <p class="text-[13.5px] font-semibold text-ink-900">
                                    {{ \App\Models\ResearchReview::decisions()[$review->decision] ?? $review->decision }}
                                </p>
                                @if ($review->comment)
                                    <p class="mt-1 line-clamp-2 text-[12.5px] leading-relaxed text-ink-600">{{ $review->comment }}</p>
                                @endif
                                <p class="mt-1.5 text-[11.5px] text-ink-400">{{ $review->created_at?->diffForHumans() }}</p>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>
</x-layouts.researcher>
