<x-consultancy-dashboard :application="$application" page="wallet_methods">
    @include('consultancy.wallet-navigation')

    <section>
        <h2 class="font-display text-[17px] font-bold">{{ __('expert_wallet.saved_methods') }}</h2>
        <div class="mt-4 grid items-start gap-4 sm:grid-cols-2">
            @forelse ($destinations as $id => ['method' => $method, 'details' => $details])
                <details @if (old('destination_id') === $id) open @endif class="group min-w-0 overflow-hidden rounded-2xl border border-ink-200 bg-white shadow-sm open:border-teal-200">
                    <summary class="flex min-h-22 cursor-pointer list-none items-center gap-3 px-4 py-3.5 focus-visible:outline-2 focus-visible:-outline-offset-2 focus-visible:outline-teal-700 sm:px-5 [&::-webkit-details-marker]:hidden">
                        <span class="grid size-10 shrink-0 place-items-center rounded-xl bg-teal-50 text-teal-800" aria-hidden="true">
                            <x-ui-icon :name="$method === 'bank' ? 'briefcase' : 'wallet'" class="size-5" />
                        </span>
                        <span class="min-w-0 flex-1">
                            <span class="block text-sm font-semibold wrap-anywhere text-navy-900">{{ $method === 'bank' ? $details['bank_name'] : __('expert_wallet.bkash') }}</span>
                            <span class="mt-1 block text-[13px] wrap-anywhere text-ink-600">
                                @if ($method === 'bank' && filled($details['branch_name'] ?? null))
                                    {{ $details['branch_name'] }} <span aria-hidden="true">&middot;</span>
                                @endif
                                <span class="font-numeric tabular-nums">&bull;&bull;&bull;&bull; {{ substr($details[$method === 'bank' ? 'account_number' : 'bkash_number'], -4) }}</span>
                            </span>
                        </span>
                        <x-ui-icon name="chevron-down" class="size-4 shrink-0 text-ink-600 group-open:rotate-180" />
                    </summary>
                    <div class="border-t border-ink-100">
                        @include('consultancy.wallet-method-form', ['method' => $method, 'id' => $id, 'details' => $details])
                    </div>
                </details>
            @empty
                <p class="rounded-xl border border-dashed border-ink-200 bg-white p-4 text-sm text-ink-600 sm:col-span-2">{{ __('expert_wallet.no_methods') }}</p>
            @endforelse
        </div>
    </section>

    <div class="grid items-start gap-5 xl:grid-cols-[minmax(0,1.5fr)_minmax(0,1fr)]">
        @foreach (['bank' => 'bangladesh_bank', 'bkash' => 'bkash'] as $method => $label)
            <section class="min-w-0 overflow-hidden rounded-2xl border border-ink-200 bg-white shadow-sm">
                <h2 class="border-b border-ink-100 bg-teal-50/60 px-5 py-4 font-display text-[17px] font-bold text-navy-900">{{ __('expert_wallet.'.$label) }}</h2>
                @include('consultancy.wallet-method-form', ['method' => $method, 'id' => null, 'details' => []])
            </section>
        @endforeach
    </div>
</x-consultancy-dashboard>
