@php
    use App\Support\Money;
@endphp
<x-consultancy-dashboard :application="$application" page="wallet_withdrawals">
    @include('consultancy.wallet-navigation')
    <section class="min-w-0 rounded-2xl border border-ink-200 bg-white p-5 shadow-sm sm:p-6">
        <h2 class="font-display text-[17px] font-bold">{{ __('expert_wallet.withdraw') }}</h2>
        <p class="mt-2 text-[13.5px] leading-relaxed text-ink-600">{{ __('expert_wallet.'.($destinations ? 'withdraw_hint' : 'setup_hint')) }}</p>
        <p class="mt-4 text-[13px] text-ink-600">{{ __('expert_wallet.available') }}
            <strong class="ml-1 font-numeric text-base text-navy-900">{{ Money::format($balances['available'], true) }}</strong>
        </p>
        @if ($destinations)
            <form method="POST" action="{{ route('expert.wallet.withdraw') }}" class="mt-4 space-y-4">
                @csrf
                <input type="hidden" name="request_key" value="{{ Str::uuid() }}">
                <label class="field-label block">{{ __('expert_wallet.method') }}
                    <select name="method" class="field-input mt-2" required>
                        @foreach ($destinations as $id => $destination)
                            <option value="{{ $id }}" @selected(old('method') === $id)>
                                {{ $destination['method'] === 'bank'
                                    ? $destination['details']['bank_name'].' / '.($destination['details']['branch_name'] ?? '').' •••• '.substr($destination['details']['account_number'], -4)
                                    : __('expert_wallet.bkash').' •••• '.substr($destination['details']['bkash_number'], -4) }}
                            </option>
                        @endforeach
                    </select>
                </label>
                <label class="field-label block">{{ __('expert_wallet.amount') }}
                    <input name="amount" type="number" min="0.01" max="{{ Money::fromMinor($balances['available']) }}" step="0.01" value="{{ old('amount') }}" class="field-input mt-2" required>
                </label>
                <button type="submit" class="btn-lead w-full disabled:cursor-not-allowed disabled:opacity-50 sm:w-auto" @disabled($balances['available'] <= 0)>{{ __('expert_wallet.withdraw') }}</button>
            </form>
        @else
            <a href="{{ route('expert.wallet.methods') }}" class="btn-lead mt-4">{{ __('consultancy_engagement.wallet_methods') }}</a>
        @endif
    </section>
    @include('consultancy.wallet-activity', ['heading' => __('expert_wallet.withdrawals'), 'emptyMessage' => __('expert_wallet.no_withdrawals')])
</x-consultancy-dashboard>
