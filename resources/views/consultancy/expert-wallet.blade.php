@php
    use App\Support\Money;
@endphp

<x-consultancy-dashboard :application="$application" page="wallet">
    @include('consultancy.wallet-navigation')

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

    @include('consultancy.wallet-activity', ['heading' => __('expert_wallet.history'), 'emptyMessage' => __('expert_wallet.no_activity')])
</x-consultancy-dashboard>
