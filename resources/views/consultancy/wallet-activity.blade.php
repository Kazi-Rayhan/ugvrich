@php
    use App\Models\ExpertWalletEntry;
    use App\Support\Money;
@endphp
    <section class="min-w-0 rounded-2xl border border-ink-200 bg-white p-5 shadow-sm sm:p-6">
        <h2 class="font-display text-[17px] font-bold">{{ $heading }}</h2>
        @if ($entries->isEmpty())
            <p class="mt-3 text-[13.5px] text-ink-600">{{ $emptyMessage }}</p>
        @else
            <ul class="mt-4 divide-y divide-ink-100 overflow-hidden rounded-xl border border-ink-100">
                @foreach ($entries as $entry)
                    @php
                        $status = $entry->kind === ExpertWalletEntry::CREDIT ? 'credited' : ($entry->status === ExpertWalletEntry::PAID ? 'paid_status' : $entry->status);
                        $badge = match ($status) {
                            'credited', 'paid_status' => 'bg-teal-50 text-teal-900',
                            'approved' => 'bg-brand-50 text-brand-800',
                            'rejected' => 'bg-rose-50 text-rose-800',
                            default => 'bg-ink-100 text-ink-700',
                        };
                    @endphp
                    <li class="grid grid-cols-2 items-start gap-x-3 gap-y-2 p-3.5 text-[13.5px] md:grid-cols-[minmax(0,1fr)_minmax(0,.8fr)_minmax(0,.9fr)_minmax(0,1.2fr)] md:items-center md:px-4">
                        <div class="min-w-0 wrap-anywhere">
                            <p class="font-semibold text-ink-950">{{ __('expert_wallet.'.$entry->kind) }}</p>
                            <p class="mt-0.5 text-[12px] text-ink-600">
                                {{ $entry->created_at->format('d M Y') }}
                                @if ($entry->method) <span aria-hidden="true">&middot;</span> {{ __('expert_wallet.'.$entry->method) }} @endif
                            </p>
                        </div>
                        <p class="text-right font-numeric font-bold tabular-nums wrap-anywhere text-ink-950 md:text-left">{{ Money::format($entry->amount_minor, true) }}</p>
                        <div class="min-w-0">
                            <span class="inline-block rounded-full px-2.5 py-1 text-[12px] font-semibold {{ $badge }}">{{ __('expert_wallet.'.$status) }}</span>
                        </div>
                        <div class="min-w-0 text-right text-[12.5px] wrap-anywhere text-ink-600 md:text-left">
                            @if ($entry->reference || $entry->kind === ExpertWalletEntry::WITHDRAWAL)
                                <span class="sr-only">{{ __('expert_wallet.reference') }} </span>{{ $entry->reference ?? '#'.$entry->id }}
                            @endif
                            @if ($entry->note)<span class="mt-0.5 block whitespace-pre-wrap">{{ $entry->note }}</span>@endif
                        </div>
                    </li>
                @endforeach
            </ul>
            <div class="mt-4">{{ $entries->links() }}</div>
        @endif
    </section>
