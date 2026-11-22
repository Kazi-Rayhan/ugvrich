<nav class="flex flex-wrap gap-x-5 gap-y-1 border-b border-ink-200" aria-label="{{ __('expert_wallet.title') }}">
    @foreach (['show' => __('expert_wallet.overview'), 'methods' => __('consultancy_engagement.wallet_methods'), 'withdrawals' => __('expert_wallet.withdrawals')] as $page => $label)
        @php($active = request()->routeIs('expert.wallet.'.$page, 'en.expert.wallet.'.$page))
        <a href="{{ route('expert.wallet.'.$page) }}" @if ($active) aria-current="page" @endif
           @class(['inline-flex min-h-11 items-center border-b-2 px-1 text-[14px] font-semibold focus-visible:outline-2 focus-visible:outline-teal-700', 'border-teal-700 text-navy-900' => $active, 'border-transparent text-ink-600 hover:text-brand-700' => ! $active])>
            {{ $label }}
        </a>
    @endforeach
</nav>
