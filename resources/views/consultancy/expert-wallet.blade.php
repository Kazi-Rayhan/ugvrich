@php
    use App\Models\ExpertWalletEntry;
    use App\Support\Money;
    $destinations = $application->wallet_destinations ?? [];
@endphp

<x-consultancy-dashboard :application="$application" page="wallet">
    <div class="grid gap-4 sm:grid-cols-3">
        @foreach (['available', 'held', 'paid'] as $balance)
            <section @class(['min-w-0 rounded-2xl border p-5 shadow-sm sm:p-6', 'border-navy-700 bg-navy-700 text-white' => $balance === 'available', 'border-ink-200 bg-white text-ink-950' => $balance !== 'available'])>
                <h2 @class(['text-[13px] font-semibold', '!text-white/85' => $balance === 'available', 'text-ink-600' => $balance !== 'available'])>{{ __('expert_wallet.'.$balance) }}</h2>
                <p class="mt-3 break-words font-numeric text-[clamp(1.35rem,3vw,2rem)] font-bold tabular-nums leading-tight">{{ Money::format($balances[$balance], true) }}</p>
            </section>
        @endforeach
    </div>

    <div class="grid items-start gap-5 xl:grid-cols-[minmax(0,1.2fr)_minmax(0,1fr)]">
        <section class="min-w-0 rounded-2xl border border-ink-200 bg-white p-5 shadow-sm sm:p-6">
            <h2 class="font-display text-[17px] font-bold">{{ __('expert_wallet.details') }}</h2>
            <div class="mt-4 space-y-3">
                @foreach (['bank' => ['bank_name', 'account_name', 'account_number', 'routing_number'], 'bkash' => ['bkash_number']] as $method => $fields)
                    <details @if (! isset($destinations[$method]) || old('method') === $method) open @endif class="rounded-xl border border-ink-200 bg-ink-50/50">
                        <summary class="cursor-pointer px-4 py-3 text-[14px] font-semibold">{{ __('expert_wallet.'.$method) }}</summary>
                        <form method="POST" action="{{ route('expert.wallet.payout') }}" class="space-y-4 border-t border-ink-100 p-4">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="method" value="{{ $method }}">
                            <div class="grid gap-4 sm:grid-cols-2">
                                @foreach ($fields as $field)
                                    <label class="field-label block">
                                        {{ __('expert_wallet.'.$field) }}
                                        <input name="{{ $field }}" class="field-input mt-2" value="{{ $destinations[$method][$field] ?? '' }}"
                                            inputmode="{{ in_array($field, ['account_number', 'routing_number', 'bkash_number'], true) ? 'tel' : 'text' }}"
                                            maxlength="{{ in_array($field, ['account_number', 'routing_number', 'bkash_number'], true) ? 30 : 120 }}" autocomplete="off" @required($field !== 'routing_number')>
                                    </label>
                                @endforeach
                            </div>
                            <button type="submit" class="btn-lead">{{ __('expert_wallet.save_method') }}</button>
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
                    <button type="submit" class="btn-lead disabled:cursor-not-allowed disabled:opacity-50" @disabled($balances['available'] <= 0)>{{ __('expert_wallet.withdraw') }}</button>
                </form>
            @endif
        </section>
    </div>

    <section class="min-w-0 rounded-2xl border border-ink-200 bg-white p-5 shadow-sm sm:p-6">
        <h2 class="font-display text-[17px] font-bold">{{ __('expert_wallet.history') }}</h2>
        @if ($entries->isEmpty())
            <p class="mt-3 text-[13.5px] text-ink-600">{{ __('expert_wallet.no_activity') }}</p>
        @else
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full text-left text-[13.5px]">
                    <thead class="border-b border-ink-100 text-ink-600"><tr>
                        @foreach (['date', 'type', 'amount', 'status', 'reference'] as $heading)
                            <th scope="col" class="px-3 py-2.5 font-semibold">{{ __('expert_wallet.'.$heading) }}</th>
                        @endforeach
                    </tr></thead>
                    <tbody class="divide-y divide-ink-100">
                        @foreach ($entries as $entry)
                            @php($status = $entry->kind === ExpertWalletEntry::CREDIT ? 'credited' : ($entry->status === ExpertWalletEntry::PAID ? 'paid_status' : $entry->status))
                            <tr>
                                <td class="whitespace-nowrap p-3">{{ $entry->created_at->format('d M Y') }}</td>
                                <td class="p-3">{{ __('expert_wallet.'.$entry->kind) }} @if ($entry->method)<span class="block text-[12px] text-ink-500">{{ __('expert_wallet.'.$entry->method) }}</span>@endif</td>
                                <td class="whitespace-nowrap p-3 font-semibold">{{ Money::format($entry->amount_minor, true) }}</td>
                                <td class="p-3"><span class="rounded-full bg-ink-50 px-2.5 py-1 text-[12px] font-semibold">{{ __('expert_wallet.'.$status) }}</span></td>
                                <td class="p-3 break-words">
                                    {{ $entry->reference ?? ($entry->kind === ExpertWalletEntry::WITHDRAWAL ? '#'.$entry->id : '') }}
                                    @if ($entry->note)<span class="block whitespace-pre-wrap text-[12px] text-ink-600">{{ $entry->note }}</span>@endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $entries->links() }}</div>
        @endif
    </section>
</x-consultancy-dashboard>
