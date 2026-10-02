@php
    use App\Models\FundingOpportunity;

    $categories = FundingOpportunity::categories();
@endphp

<x-layouts.researcher :title="$opportunity->title">

    <div class="max-w-3xl space-y-6">

        <div class="rounded-[1.5rem] border border-ink-100 bg-white p-6 sm:p-8">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div class="min-w-0">
                    <span class="inline-flex rounded-full bg-brand-50 px-2.5 py-1 text-[11.5px] font-semibold text-brand-700">
                        {{ $categories[$opportunity->category] ?? $opportunity->category }}
                    </span>

                    <h2 class="mt-3 font-display text-[21px] font-bold leading-snug text-ink-950 sm:text-[25px]">{{ $opportunity->title }}</h2>

                    @if ($opportunity->organization)
                        <p class="mt-2 text-[13.5px] text-ink-600">{{ __('researcher.funding.organization') }}: {{ $opportunity->organization }}</p>
                    @endif
                </div>

                @if ($opportunity->hasClosed())
                    <span class="shrink-0 rounded-full bg-rose-50 px-3 py-1.5 text-[12px] font-semibold text-rose-700 ring-1 ring-rose-200">
                        {{ __('researcher.funding.closed_note') }}
                    </span>
                @endif
            </div>

            <dl class="mt-7 grid gap-5 border-t border-ink-100 pt-6 sm:grid-cols-3">
                @foreach ([
                    'deadline' => $opportunity->deadline?->isoFormat('D MMMM YYYY') ?? __('researcher.funding.no_deadline'),
                    'amount' => $opportunity->amount,
                    'organization' => $opportunity->department ?: $opportunity->research_area,
                ] as $key => $value)
                    @continue(blank($value))

                    <div>
                        <dt class="text-[10.5px] font-semibold uppercase tracking-[0.16em] text-ink-400">{{ __('researcher.funding.'.$key) }}</dt>
                        <dd class="mt-1.5 text-[14px] text-ink-800">{{ $value }}</dd>
                    </div>
                @endforeach
            </dl>
        </div>

        @php
            $sections = [
                'lead' => $opportunity->description,
                'eligibility' => $opportunity->eligibility,
                'how_to_apply' => $opportunity->how_to_apply,
            ];
        @endphp

        @if (collect($sections)->filter()->isNotEmpty())
            <div class="rounded-[1.5rem] border border-ink-100 bg-white p-6 sm:p-8">
                <dl class="space-y-6">
                    @foreach ($sections as $key => $value)
                        @continue(blank($value))

                        <div>
                            @if ($key !== 'lead')
                                <dt class="text-[10.5px] font-semibold uppercase tracking-[0.16em] text-ink-400">{{ __('researcher.funding.'.$key) }}</dt>
                            @endif
                            <dd class="@if ($key !== 'lead') mt-2 @endif whitespace-pre-line text-[14.5px] leading-[1.85] text-ink-700">{{ $value }}</dd>
                        </div>
                    @endforeach
                </dl>
            </div>
        @endif

        <div class="flex flex-wrap items-center gap-4">
            @if ($opportunity->external_url)
                <a href="{{ $opportunity->external_url }}" target="_blank" rel="noopener noreferrer" class="btn-primary">
                    {{ __('researcher.funding.external') }} <x-ui-icon name="arrow-up-right" class="h-4 w-4" />
                </a>
            @endif

            @if ($opportunity->document)
                <a href="{{ Storage::url($opportunity->document) }}" target="_blank" rel="noopener"
                   class="inline-flex items-center gap-2 text-[13.5px] font-semibold text-brand-700 hover:text-brand-600">
                    <x-ui-icon name="document" class="h-4 w-4" />
                    {{ __('researcher.funding.document') }}
                </a>
            @endif

            <a href="{{ route('researcher.funding.index') }}" class="text-[13.5px] font-semibold text-ink-500 hover:text-ink-800">
                {{ __('researcher.funding.opportunities') }}
            </a>
        </div>
    </div>
</x-layouts.researcher>
