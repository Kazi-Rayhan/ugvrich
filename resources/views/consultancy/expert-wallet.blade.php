@php
    use App\Models\ExpertWalletEntry;
    use App\Support\Money;
    $destinations = $application->wallet_destinations ?? [];
@endphp

<x-consultancy-dashboard :application="$application" page="wallet">
    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 sm:gap-4">
        @foreach (['available', 'held', 'paid'] as $balance)
            <section @class(['min-w-0 rounded-2xl border border-ink-200 p-4 shadow-sm sm:p-6', 'col-span-2 bg-linear-to-br from-teal-50/80 to-white sm:col-span-1' => $balance === 'available', 'bg-white' => $balance !== 'available'])>
                <div class="mb-3 grid size-9 place-items-center rounded-xl border border-teal-100 bg-teal-50 text-teal-800">
                    <x-ui-icon :name="['available' => 'wallet', 'held' => 'document', 'paid' => 'check'][$balance]" class="size-4" />
                </div>
                <h2 class="text-[13px] font-semibold text-ink-700">{{ __('expert_wallet.'.$balance) }}</h2>
                <p class="mt-2 font-numeric text-[clamp(1.125rem,2.4vw,1.75rem)] font-bold leading-tight tabular-nums wrap-anywhere text-ink-950">{{ Money::format($balances[$balance], true) }}</p>
            </section>
        @endforeach
    </div>

    <div class="grid items-start gap-4 xl:grid-cols-[minmax(0,1.2fr)_minmax(0,1fr)] xl:gap-5">
        <section class="min-w-0 rounded-2xl border border-ink-200 bg-white p-5 shadow-sm sm:p-6">
            <h2 class="font-display text-[17px] font-bold">{{ __('expert_wallet.details') }}</h2>
            <div class="mt-4 space-y-3">
                @foreach (['bank' => ['bank_name', 'account_name', 'account_number', 'routing_number'], 'bkash' => ['bkash_number']] as $method => $fields)
                    <details @if (old('method', array_key_first($destinations) ?? 'bank') === $method) open @endif class="group rounded-xl border border-ink-200 bg-white open:border-teal-200">
                        <summary class="flex cursor-pointer list-none items-center justify-between gap-3 rounded-xl bg-ink-50/70 px-4 py-3 text-[14px] font-semibold focus-visible:outline-2 focus-visible:outline-teal-700 [&::-webkit-details-marker]:hidden">
                            {{ __('expert_wallet.'.$method) }}
                            <x-ui-icon name="chevron-down" class="size-4 shrink-0 text-ink-500 group-open:rotate-180" />
                        </summary>
                        <form method="POST" action="{{ route('expert.wallet.payout') }}" class="space-y-4 border-t border-ink-100 p-4">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="method" value="{{ $method }}">
                            <div @class(['grid min-w-0 gap-4', 'sm:grid-cols-2' => $method === 'bank'])>
                                @foreach ($fields as $field)
                                    <label class="field-label block min-w-0">
                                        {{ __('expert_wallet.'.$field) }}
                                        <input name="{{ $field }}" class="field-input mt-2" value="{{ $destinations[$method][$field] ?? '' }}"
                                            inputmode="{{ in_array($field, ['account_number', 'routing_number', 'bkash_number'], true) ? 'tel' : 'text' }}"
                                            maxlength="{{ in_array($field, ['account_number', 'routing_number', 'bkash_number'], true) ? 30 : 120 }}" autocomplete="off" @required($field !== 'routing_number')>
                                    </label>
                                @endforeach
                            </div>
                            <button type="submit" class="btn-lead w-full sm:w-auto">{{ __('expert_wallet.save_method') }}</button>
                        </form>
                    </details>
                @endforeach
            </div>
        </section>

        <section class="min-w-0 rounded-2xl border border-ink-200 bg-white p-5 shadow-sm sm:p-6">
            <h2 class="font-display text-[17px] font-bold">{{ __('expert_wallet.withdraw') }}</h2>
            <p class="mt-2 text-[13.5px] leading-relaxed text-ink-600">{{ __('expert_wallet.'.($destinations ? 'withdraw_hint' : 'setup_hint')) }}</p>
            @if ($destinations)
                <form method="POST" action="{{ route('expert.wallet.withdraw') }}" class="mt-4 space-y-4">
                    @csrf
                    <input type="hidden" name="request_key" value="{{ Str::uuid() }}">
                    <label class="field-label block">{{ __('expert_wallet.method') }}
                        <select name="method" class="field-input mt-2" required>
                            @foreach (array_keys($destinations) as $method)
                                <option value="{{ $method }}" @selected(old('method') === $method)>{{ __('expert_wallet.'.$method) }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="field-label block">{{ __('expert_wallet.amount') }}
                        <input name="amount" type="number" min="0.01" max="{{ Money::fromMinor($balances['available']) }}" step="0.01" value="{{ old('amount') }}" class="field-input mt-2" required>
                    </label>
                    <button type="submit" class="btn-lead w-full disabled:cursor-not-allowed disabled:opacity-50 sm:w-auto" @disabled($balances['available'] <= 0)>{{ __('expert_wallet.withdraw') }}</button>
                </form>
            @endif
        </section>
    </div>

    <section class="min-w-0 rounded-2xl border border-ink-200 bg-white p-5 shadow-sm sm:p-6">
        <h2 class="font-display text-[17px] font-bold">{{ __('expert_wallet.history') }}</h2>
        @if ($entries->isEmpty())
            <p class="mt-3 text-[13.5px] text-ink-600">{{ __('expert_wallet.no_activity') }}</p>
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
</x-consultancy-dashboard>
