@php
    use App\Models\Order;
    use App\Services\OrderDelivery;
@endphp

<x-consultancy-dashboard :application="$application" page="assignments">
    <style>
        .expert-assignments { --ea-ink:#12253a; --ea-muted:#516477; --ea-line:#dce6ec; --ea-teal:#10685e; display:grid; gap:18px; color:var(--ea-ink); }
        .expert-assignments :focus-visible { outline:3px solid #278d81; outline-offset:3px; }
        .expert-assignments-banner {
            display:grid; grid-template-columns:minmax(0,1fr) minmax(92px,36%); align-items:center; gap:10px;
            position:relative; overflow:hidden; min-height:121px; padding:15px 22px;
            color:#fff; background:#12283d; border-radius:17px;
        }
        .expert-assignments-banner strong { display:block; color:#fff; font-family:var(--font-display); font-size:clamp(1.95rem,5vw,3rem); font-weight:800; line-height:1; font-variant-numeric:tabular-nums; }
        .expert-assignments-banner span { color:#bce8dc; display:block; margin-top:7px; font-size:.84rem; font-weight:650; }
        .expert-assignments-art { display:block; width:100%; max-width:193px; height:100%; max-height:112px; justify-self:end; }
        .expert-assignments-list { display:grid; grid-template-columns:repeat(auto-fit,minmax(min(100%,24rem),1fr)); gap:14px; }
        .expert-assignment-card { min-width:0; display:flex; flex-direction:column; background:#fff; border:1px solid var(--ea-line); border-radius:15px; overflow:hidden; }
        .expert-assignment-card-body { padding:16px 17px 15px; flex:1; }
        .expert-assignment-card-top { display:flex; align-items:flex-start; justify-content:space-between; flex-wrap:wrap; gap:8px 10px; }
        .expert-assignment-reference { display:inline-flex; align-items:center; gap:7px; color:#405973; font-size:.79rem; font-weight:750; font-variant-numeric:tabular-nums; overflow-wrap:anywhere; }
        .expert-assignment-reference svg { width:17px; height:17px; flex:none; }
        .expert-assignment-status { display:inline-flex; align-items:center; gap:5px; background:#eaf0f4; color:#3b5267; border-radius:20px; padding:4px 8px; font-size:.72rem; font-weight:750; line-height:1.2; }
        .expert-assignment-status::before { content:''; width:6px; height:6px; border-radius:50%; background:currentColor; flex:none; }
        .expert-assignment-status[data-status="confirmed"], .expert-assignment-status[data-status="in_progress"] { background:#e5eef9; color:#284f81; }
        .expert-assignment-status[data-status="completed"] { background:#e2f2eb; color:#1e6251; }
        .expert-assignment-status[data-status="on_hold"] { background:#faf1df; color:#855417; }
        .expert-assignment-status[data-status="cancelled"] { background:#f9ebea; color:#963d36; }
        .expert-assignment-card h2 { margin:7px 0 0; color:var(--ea-ink); font-family:var(--font-display); font-size:1.11rem; font-weight:800; line-height:1.3; overflow-wrap:anywhere; }
        .expert-assignment-work-order { color:var(--ea-muted); margin-top:6px; font-size:.77rem; overflow-wrap:anywhere; }
        .expert-assignment-work-order strong { color:#344d64; font-weight:700; }
        .expert-assignment-delivery { margin-top:14px; padding:10px 12px; background:#f1f6f7; border-radius:10px; }
        .expert-assignment-delivery-line { display:flex; flex-wrap:wrap; align-items:baseline; justify-content:space-between; gap:3px 8px; font-size:.77rem; color:var(--ea-muted); }
        .expert-assignment-delivery-line strong { color:var(--ea-teal); font-weight:800; font-variant-numeric:tabular-nums; }
        .expert-assignment-delivery progress { appearance:none; display:block; width:100%; height:6px; border:0; margin:7px 0 0; border-radius:8px; overflow:hidden; background:#dce8eb; }
        .expert-assignment-delivery progress::-webkit-progress-bar { background:#dce8eb; }
        .expert-assignment-delivery progress::-webkit-progress-value { background:#168476; }
        .expert-assignment-delivery progress::-moz-progress-bar { background:#168476; }
        .expert-assignment-actions { display:grid; grid-template-columns:minmax(0,1fr) minmax(0,1.4fr); gap:8px; border-top:1px solid var(--ea-line); padding:10px 16px 12px; }
        .expert-assignment-actions a { display:flex; align-items:center; justify-content:center; gap:6px; min-height:44px; border:1px solid #d5e3e7; border-radius:9px; padding:5px 8px; font-size:.83rem; font-weight:750; text-align:center; }
        .expert-assignment-actions a:hover { background:#eef5f6; }
        .expert-assignment-actions .expert-assignment-primary { border-color:#10685e; background:#10685e; color:#fff; }
        .expert-assignment-actions .expert-assignment-primary:hover { background:#0d524b; }
        .expert-assignment-actions svg { width:16px; height:16px; flex:none; }
        .expert-assignments-empty { display:grid; justify-items:center; gap:8px; padding:28px 18px; background:white; border:1px solid var(--ea-line); border-radius:15px; color:var(--ea-muted); text-align:center; }
        .expert-assignments-empty svg { width:30px; height:30px; color:var(--ea-teal); }
        @media (max-width:34rem) {
            .expert-assignments { gap:12px; }
            .expert-assignments-banner { min-height:108px; padding:14px 16px; }
            .expert-assignments-list { gap:11px; }
        }
    </style>

    <section class="expert-assignments" aria-label="{{ __('consultancy_engagement.assignments') }}">
        <div class="expert-assignments-banner">
            <div>
                <strong>{{ number_format($assignments->total()) }}</strong>
                <span>{{ __('consultancy_engagement.assignment_list.total') }}</span>
            </div>
            <svg class="expert-assignments-art" viewBox="0 0 190 104" fill="none" aria-hidden="true" focusable="false">
                <path d="M0 86h37l25-25h34m31-41h25l25-17m-31 87h25l19-18M48 103V80m105-41h37" stroke="#426279" stroke-width="1.2"/>
                <circle cx="116" cy="52" r="47" stroke="#426279" stroke-dasharray="3 5"/>
                <path d="M83 32h53a5 5 0 0 1 5 5v45H78V37a5 5 0 0 1 5-5Z" fill="#1c3b51" stroke="#609594" stroke-width="1.2"/>
                <rect x="91" y="22" width="53" height="56" rx="6" fill="#244b60" stroke="#9bdfd1" stroke-width="1.5"/>
                <path d="M112 38h21m-21 12h21m-21 12h15" stroke="#8fcfca" stroke-width="1.5" stroke-linecap="round"/>
                <path d="m100 38 2 2 4-4m-6 14 2 2 4-4m-6 14 2 2 4-4" stroke="#b3eedb" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                <circle cx="37" cy="86" r="3" fill="#7dd7c3"/><circle cx="152" cy="20" r="3" fill="#7dd7c3"/><circle cx="172" cy="90" r="3" fill="#7dd7c3"/>
            </svg>
        </div>

        @if ($assignments->isNotEmpty())
            <ul class="expert-assignments-list">
                @foreach ($assignments as $assignment)
                    @php
                        $order = $assignment->order;
                        $progress = OrderDelivery::progress($order);
                        $title = $order->proposal?->title;
                    @endphp
                    <li class="expert-assignment-card">
                        <div class="expert-assignment-card-body">
                            <div class="expert-assignment-card-top">
                                @if ($title)
                                    <span class="expert-assignment-reference"><x-ui-icon name="briefcase" /> {{ $order->number }}</span>
                                @else
                                    <h2 class="m-0">{{ $order->number }}</h2>
                                @endif
                                <span class="expert-assignment-status" data-status="{{ $order->status }}">{{ __(Order::STATUSES[$order->status] ?? $order->status) }}</span>
                            </div>
                            @if ($title)
                                <h2>{{ $title }}</h2>
                            @endif
                            @if ($order->workOrder)
                                <p class="expert-assignment-work-order">{{ __('consultancy_engagement.assignment_list.work_order') }} <strong>{{ $order->workOrder->number }}</strong></p>
                            @endif
                            <div class="expert-assignment-delivery">
                                <div class="expert-assignment-delivery-line">
                                    <span>{{ $progress['total']
                                        ? __('consultancy_engagement.dashboard_ui.milestone_fraction', ['done' => $progress['completed'], 'total' => $progress['total']])
                                        : __('consultancy_engagement.dashboard_ui.no_milestones') }}</span>
                                    @if ($progress['total'])
                                        <strong>{{ $progress['percent'] }}%</strong>
                                    @endif
                                </div>
                                @if ($progress['total'])
                                    <progress aria-label="{{ __('consultancy_engagement.assignment_list.delivery_progress') }}" value="{{ $progress['percent'] }}" max="100"></progress>
                                @endif
                            </div>
                        </div>
                        <div class="expert-assignment-actions">
                            <a href="{{ route('expert.orders.show', $order) }}" aria-label="{{ __('consultancy_engagement.assignment_list.details_for', ['order' => $order->number]) }}">
                                <x-ui-icon name="document" /> {{ __('consultancy_engagement.assignment_list.details') }}
                            </a>
                            <a class="expert-assignment-primary" href="{{ route('expert.orders.tasks', $order) }}" aria-label="{{ __('consultancy_engagement.dashboard_ui.tasks_for', ['order' => $order->number]) }}">
                                {{ __('consultancy_engagement.dashboard_ui.task_board') }} <x-ui-icon name="arrow-right" />
                            </a>
                        </div>
                    </li>
                @endforeach
            </ul>
        @else
            <div class="expert-assignments-empty">
                <x-ui-icon name="briefcase" />
                <p>{{ __('consultancy_engagement.dashboard_ui.no_assignments') }}</p>
            </div>
        @endif

        @if ($assignments->hasPages())
            {{ $assignments->links() }}
        @endif
    </section>
</x-consultancy-dashboard>
