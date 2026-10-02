<x-layouts.researcher :title="__('researcher.support.title')">

    @php $statuses = \App\Models\ResearchSupport::statusLabels(); @endphp

    <div class="space-y-6">
        <div class="flex flex-wrap items-start justify-between gap-5">
            <div class="max-w-2xl">
                <h2 class="font-display text-[22px] font-bold tracking-tight text-ink-950 sm:text-[26px]">{{ __('researcher.support.title') }}</h2>
                <p class="mt-2 text-[14.5px] leading-[1.8] text-ink-600">{{ __('researcher.support.lead') }}</p>
            </div>

            <a href="{{ route('researcher.support.create') }}" class="btn-lead group shrink-0">
                <span class="relative">{{ __('researcher.support.new') }}</span>
                <x-ui-icon name="arrow-right" class="relative h-[18px] w-[18px] transition-transform duration-300 group-hover:translate-x-1" />
            </a>
        </div>

        @if ($requests->isEmpty())
            <div class="rounded-[1.5rem] border border-dashed border-ink-200 bg-white px-6 py-16 text-center">
                <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-brand-50 text-brand-600">
                    <x-ui-icon name="heart" class="h-6 w-6" />
                </span>
                <p class="mt-5 font-display text-[17px] font-bold text-ink-950">{{ __('researcher.support.none') }}</p>
                <p class="mx-auto mt-2 max-w-sm text-[14px] leading-relaxed text-ink-500">{{ __('researcher.support.none_lead') }}</p>
                <a href="{{ route('researcher.support.create') }}" class="btn-primary mt-6">
                    {{ __('researcher.support.new') }} <x-ui-icon name="arrow-right" class="h-4 w-4" />
                </a>
            </div>
        @else
            <ul class="divide-y divide-ink-100 overflow-hidden rounded-[1.5rem] border border-ink-100 bg-white">
                @foreach ($requests as $support)
                    <li>
                        <a href="{{ route('researcher.support.show', $support) }}" class="group block px-6 py-5 transition hover:bg-ink-50/70">
                            <div class="flex items-start justify-between gap-4">
                                <div class="min-w-0">
                                    <p class="font-numeric text-[11.5px] font-semibold tracking-wider text-ink-400">{{ $support->reference() }}</p>
                                    <h3 class="mt-1 truncate font-display text-[16px] font-bold text-ink-950 transition group-hover:text-brand-700">{{ $support->title }}</h3>
                                </div>

                                <x-researcher-status :status="$support->status" :labels="$statuses" />
                            </div>

                            @if ($support->support_types)
                                <div class="mt-3 flex flex-wrap gap-1.5">
                                    @foreach (array_slice($support->support_types, 0, 4) as $type)
                                        <span class="rounded-lg bg-ink-50 px-2.5 py-1 text-[12px] font-medium text-ink-600">{{ $types[$type] ?? $type }}</span>
                                    @endforeach

                                    @if (count($support->support_types) > 4)
                                        <span class="rounded-lg bg-ink-50 px-2.5 py-1 font-numeric text-[12px] font-medium text-ink-500">+{{ count($support->support_types) - 4 }}</span>
                                    @endif
                                </div>
                            @endif

                            <div class="mt-3 flex flex-wrap items-center gap-x-5 gap-y-1 text-[12px] text-ink-400">
                                <span>{{ __('researcher.support.sent_on') }} {{ $support->created_at?->isoFormat('D MMM YYYY') }}</span>

                                @if ($support->needed_by)
                                    <span>{{ __('researcher.support.needed_by') }} {{ $support->needed_by->isoFormat('D MMM YYYY') }}</span>
                                @endif

                                @if ($support->admin_notes)
                                    <span class="inline-flex items-center gap-1 font-semibold text-brand-600">
                                        <x-ui-icon name="chat" class="h-3.5 w-3.5" />
                                        {{ __('researcher.support.reply') }}
                                    </span>
                                @endif
                            </div>
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</x-layouts.researcher>
