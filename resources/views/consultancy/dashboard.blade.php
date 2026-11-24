@php
    use App\Models\ConsultancyApplication;
    use App\Models\Order;
    use App\Services\OrderDelivery;

    $contractLabel = $application->contract_type ? ConsultancyApplication::contractLabel($application->contract_type) : __('consultancy_engagement.pending');
    $status = $application->signed_at ? 'profile_active' : ($application->engagement_policy ? 'signature_required' : 'pending');
    $taskTotal = array_sum($taskCounts);
    $chartStep = max(1, (int) ceil(max($taskCounts) / 4));
    $milestonePercent = $milestones['total'] ? (int) round(100 * $milestones['completed'] / $milestones['total']) : 0;
@endphp

<x-consultancy-dashboard :application="$application">
    <style>
        .expert-overview { --ink:#12253a; --muted:#526579; --line:#dce5ec; --navy:#12283d; --teal:#13786b; color:var(--ink); display:grid; gap:20px; }
        .expert-overview :focus-visible { outline:3px solid #278d81; outline-offset:4px; }
        .expert-hero { background:var(--navy); color:#fff; border-radius:20px; overflow:hidden; display:grid; grid-template-columns:minmax(0,1.2fr) minmax(0,1fr); align-items:center; min-height:248px; }
        .expert-hero-copy { padding:clamp(24px,4vw,48px); min-width:0; }
        .expert-hero-status { display:inline-flex; align-items:center; gap:7px; color:#b8e8db; font-size:.83rem; font-weight:700; }
        .expert-hero-status::before { content:''; width:8px; height:8px; border-radius:100%; background:#72d6ba; }
        .expert-hero-status.is-pending { color:#f4deb0; }
        .expert-hero-status.is-pending::before { background:#f3bf65; }
        .expert-hero h2 { margin-top:10px; color:#fff; font-family:var(--font-display); font-size:clamp(1.65rem,3.1vw,3rem); font-weight:800; line-height:1.12; letter-spacing:-.035em; overflow-wrap:anywhere; }
        .expert-hero p { margin-top:9px; color:#bdcedb; font-size:.9rem; overflow-wrap:anywhere; }
        .expert-hero-link { display:inline-flex; align-items:center; gap:8px; margin-top:20px; background:#a8e7d6; color:#102b35; border-radius:9px; padding:9px 13px; font-size:.86rem; font-weight:750; }
        .expert-hero-link:hover { background:#d0f4ea; }
        .expert-hero-art { width:100%; max-width:460px; height:auto; justify-self:end; align-self:end; color:#7dd7c3; }
        .expert-signal { animation:expert-signal 1.35s ease-out both; }
        .expert-panel { background:#fff; border:1px solid var(--line); border-radius:16px; min-width:0; }
        .expert-panel-head { padding:20px 22px 0; display:flex; flex-wrap:wrap; justify-content:space-between; align-items:baseline; gap:7px 14px; }
        .expert-panel h2 { font-family:var(--font-display); font-size:1.04rem; font-weight:800; letter-spacing:-.02em; }
        .expert-panel-head a, .expert-row-link { color:#12685e; font-size:.82rem; font-weight:750; text-underline-offset:3px; }
        .expert-panel-head a:hover, .expert-row-link:hover { text-decoration:underline; }
        .expert-metrics { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); }
        .expert-metrics > div { padding:15px 20px; display:grid; grid-template-columns:auto minmax(0,1fr); align-items:center; gap:3px 12px; }
        .expert-metrics > div + div { border-inline-start:1px solid var(--line); }
        .expert-metric-icon { grid-row:span 2; width:38px; height:38px; display:grid; place-items:center; border-radius:10px; background:#e4f4ef; color:#12685e; }
        .expert-metrics dt, .expert-muted { color:var(--muted); font-size:.8rem; }
        .expert-metrics dd { font-size:1.36rem; line-height:1; font-weight:800; font-variant-numeric:tabular-nums; }
        .expert-charts { display:grid; grid-template-columns:minmax(0,1.6fr) minmax(0,1fr); gap:20px; }
        .expert-chart { position:relative; margin:18px 22px 20px; display:grid; grid-template-columns:2rem minmax(0,1fr); column-gap:8px; }
        .expert-axis { height:142px; display:flex; flex-direction:column; justify-content:space-between; text-align:end; color:var(--muted); font-size:.75rem; line-height:1; font-variant-numeric:tabular-nums; }
        .expert-plot { position:relative; }
        .expert-gridlines { position:absolute; left:0; right:0; top:5px; height:132px; display:flex; flex-direction:column; justify-content:space-between; pointer-events:none; }
        .expert-gridlines i { border-top:1px dashed var(--line); }
        .expert-gridlines i:last-child { border-top-style:solid; }
        .expert-bars { position:relative; display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:8px; }
        .expert-bar-item { display:grid; grid-template-rows:137px auto; text-align:center; min-width:0; }
        .expert-bar-track { align-self:end; justify-self:center; display:flex; align-items:flex-end; justify-content:center; width:min(64%,72px); height:132px; position:relative; }
        .expert-bar-fill { position:relative; display:block; min-width:0; width:100%; height:var(--bar-size); border-radius:6px 6px 0 0; background:var(--bar-color); transform-origin:bottom; animation:expert-rise .65s cubic-bezier(.23,1,.32,1) both; }
        .expert-bar-value { position:absolute; bottom:calc(var(--bar-size) + 3px); color:var(--ink); font-weight:800; font-size:.77rem; font-variant-numeric:tabular-nums; }
        .expert-bar-label { margin-top:8px; font-size:.78rem; font-weight:650; color:var(--muted); overflow-wrap:anywhere; }
        .expert-ring { display:grid; place-items:center; justify-items:center; gap:8px; padding:14px 18px 20px; }
        .expert-ring-face { width:134px; max-width:100%; display:grid; place-items:center; }
        .expert-ring-face > * { grid-area:1/1; }
        .expert-ring-face strong { font-size:1.7rem; font-weight:800; font-variant-numeric:tabular-nums; }
        .expert-ring-fill { stroke-dasharray:var(--milestone-percent) 100; animation:expert-ring-in .85s ease-out both; }
        .expert-empty { padding:18px 22px 22px; color:var(--muted); font-size:.88rem; }
        .expert-assignment-list { margin-top:12px; }
        .expert-assignment-row { padding:13px 22px; display:grid; gap:8px 16px; grid-template-columns:minmax(0,1fr) minmax(80px,145px) auto; align-items:center; border-top:1px solid var(--line); }
        .expert-assignment-row strong { display:block; font-size:.88rem; overflow-wrap:anywhere; }
        .expert-assignment-row progress { appearance:none; display:block; width:100%; height:7px; margin-top:4px; border:0; border-radius:7px; overflow:hidden; background:#e7eef1; }
        .expert-assignment-row progress::-webkit-progress-bar { background:#e7eef1; }
        .expert-assignment-row progress::-webkit-progress-value { background:var(--teal); }
        .expert-assignment-row progress::-moz-progress-bar { background:var(--teal); }
        .expert-engagement { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:15px; padding:12px 22px 20px; }
        .expert-engagement dd { font-size:.91rem; font-weight:750; overflow-wrap:anywhere; }
        .expert-onboarding { padding:20px 22px; }
        .expert-onboarding p { margin-top:6px; }
        .expert-onboarding .expert-hero-link { margin-top:12px; }
        @keyframes expert-rise { from { transform:scaleY(0); } to { transform:scaleY(1); } }
        @keyframes expert-ring-in { from { stroke-dasharray:0 100; } to { stroke-dasharray:var(--milestone-percent) 100; } }
        @keyframes expert-signal { from { stroke-dashoffset:1; } to { stroke-dashoffset:0; } }
        @media (max-width:680px) {
            .expert-hero { grid-template-columns:minmax(0,1fr) minmax(0,.42fr); min-height:188px; }
            .expert-hero-art { width:175%; max-width:none; opacity:.55; }
            .expert-hero-copy { position:relative; z-index:1; padding:23px 0 23px 20px; }
            .expert-charts { grid-template-columns:minmax(0,1fr); }
            .expert-metrics > div { padding:12px 9px; grid-template-columns:minmax(0,1fr); }
            .expert-metric-icon { grid-row:auto; width:30px; height:30px; }
            .expert-assignment-row { padding-inline:15px; grid-template-columns:minmax(0,1fr) auto; }
            .expert-assignment-progress { grid-row:2; }
            .expert-row-link { grid-row:1 / span 2; grid-column:2; }
        }
        @media (prefers-reduced-motion:reduce) {
            .expert-signal, .expert-bar-fill, .expert-ring-fill { animation:none; }
        }
    </style>

    <div class="expert-overview">
        <section class="expert-hero" aria-labelledby="expert-overview-name">
            <div class="expert-hero-copy">
                <span @class(['expert-hero-status', 'is-pending' => ! $application->signed_at])>{{ __('consultancy_engagement.'.$status) }}</span>
                <h2 id="expert-overview-name">{{ $application->expert?->name ?? $application->name }}</h2>
                <p>{{ $application->category->name }}</p>
                @if ($application->signed_at)
                    <a class="expert-hero-link" href="{{ route('expert.orders.index') }}">
                        {{ __('consultancy_engagement.assignments') }} <x-ui-icon name="arrow-right" class="size-4" />
                    </a>
                @endif
            </div>
            <svg class="expert-hero-art" viewBox="0 0 410 240" fill="none" aria-hidden="true" focusable="false">
                <defs>
                    <pattern id="expert-blueprint-dots" width="16" height="16" patternUnits="userSpaceOnUse">
                        <circle cx="2" cy="2" r="1" fill="#36556c"/>
                    </pattern>
                </defs>
                <path d="M28 240V95L118 5H410V240Z" fill="url(#expert-blueprint-dots)"/>
                <g stroke="#3f5d73" stroke-width="1.2">
                    <path d="M63 240v-53l71-71h73m203-78H288l-39 39v27m161 106H315l-42-42m-49 72v-42l-40-40h-33"/>
                    <circle cx="238" cy="130" r="89" stroke-dasharray="3 7"/>
                    <circle cx="238" cy="130" r="65"/>
                    <path d="M238 35v17m0 156v16m-96-94h18m156 0h17"/>
                </g>
                <path d="m238 78 44 26v52l-44 26-44-26v-52z" fill="#193b51" stroke="#77cfbd" stroke-width="1.6"/>
                <path d="m194 104 44 26 44-26m-44 26v52m-22-65 44-26m-44 52 44 26" stroke="#5db8aa" stroke-width="1.1"/>
                <path class="expert-signal" d="M89 196h28l32-32h30l37-36h22l22-24h31l37-36h48" pathLength="1" stroke-dasharray="1" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/>
                <g fill="#a8e7d6">
                    <circle cx="89" cy="196" r="4"/><circle cx="149" cy="164" r="4"/><circle cx="238" cy="128" r="5"/><circle cx="328" cy="68" r="4"/><circle cx="376" cy="68" r="4"/>
                </g>
                <path d="M114 61h34v26h-34zm204 113h43v27h-43z" fill="#193b51" stroke="#56798b"/>
                <path d="M123 70h15m-15 7h9m195 10v-15m-1 118h25m-25-8h16" stroke="#7dd7c3" stroke-linecap="round"/>
            </svg>
        </section>

        @if ($application->signed_at)
            <dl class="expert-panel expert-metrics">
                @foreach ([
                    ['briefcase', __('consultancy_engagement.assignments'), $assignmentCount],
                    ['grid', __('consultancy_engagement.dashboard_ui.tasks'), $taskTotal],
                    ['shield', __('consultancy_engagement.dashboard_ui.completed_milestones'), $milestones['completed']],
                ] as [$icon, $label, $value])
                    <div>
                        <span class="expert-metric-icon" aria-hidden="true"><x-ui-icon :name="$icon" class="size-5" /></span>
                        <dt>{{ $label }}</dt>
                        <dd>{{ number_format($value) }}</dd>
                    </div>
                @endforeach
            </dl>

            <div class="expert-charts">
                <section class="expert-panel" aria-labelledby="expert-task-chart">
                    <div class="expert-panel-head">
                        <h2 id="expert-task-chart">{{ __('consultancy_engagement.dashboard_ui.task_chart') }}</h2>
                        <span class="expert-muted">{{ __('consultancy_engagement.dashboard_ui.task_chart_scope') }}</span>
                    </div>
                    @if ($taskTotal)
                        <div class="expert-chart">
                            <div class="expert-axis" aria-hidden="true">
                                @for ($tick = 4; $tick >= 0; $tick--)
                                    <span>{{ number_format($tick * $chartStep) }}</span>
                                @endfor
                            </div>
                            <div class="expert-plot">
                                <div class="expert-gridlines" aria-hidden="true"><i></i><i></i><i></i><i></i><i></i></div>
                                <ul class="expert-bars">
                                    @foreach (['todo' => '#5d7896', 'doing' => '#b56e27', 'done' => '#13786b'] as $key => $color)
                                        <li class="expert-bar-item" style="--bar-size:{{ $taskCounts[$key] / (4 * $chartStep) * 100 }}%;--bar-color:{{ $color }}">
                                            <span class="expert-bar-track">
                                                <span class="expert-bar-fill" aria-hidden="true"></span>
                                                <span class="expert-bar-value">{{ number_format($taskCounts[$key]) }}</span>
                                            </span>
                                            <span class="expert-bar-label">{{ __('consultancy_engagement.dashboard_ui.'.$key) }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @else
                        <p class="expert-empty">{{ __('consultancy_engagement.dashboard_ui.no_tasks') }}</p>
                    @endif
                </section>

                <section class="expert-panel" aria-labelledby="expert-milestone-chart">
                    <div class="expert-panel-head">
                        <h2 id="expert-milestone-chart">{{ __('consultancy_engagement.dashboard_ui.milestone_chart') }}</h2>
                    </div>
                    <div class="expert-ring">
                        <div class="expert-ring-face" style="--milestone-percent:{{ $milestonePercent }}">
                            <svg viewBox="0 0 120 120" width="134" height="134" aria-hidden="true" focusable="false">
                                <circle cx="60" cy="60" r="48" fill="none" stroke="#e7eef1" stroke-width="10"/>
                                @if ($milestonePercent > 0)
                                    <circle class="expert-ring-fill" cx="60" cy="60" r="48" pathLength="100" transform="rotate(-90 60 60)" fill="none" stroke="#13786b" stroke-width="10" stroke-linecap="round"/>
                                @endif
                            </svg>
                            <strong>{{ $milestonePercent }}%</strong>
                        </div>
                        <p class="expert-muted">{{ $milestones['total']
                            ? __('consultancy_engagement.dashboard_ui.milestone_fraction', ['done' => $milestones['completed'], 'total' => $milestones['total']])
                            : __('consultancy_engagement.dashboard_ui.no_milestones') }}</p>
                    </div>
                </section>
            </div>

            <section class="expert-panel" aria-labelledby="expert-recent-assignments">
                <div class="expert-panel-head">
                    <h2 id="expert-recent-assignments">{{ __('consultancy_engagement.dashboard_ui.recent_assignments') }}</h2>
                    <a href="{{ route('expert.orders.index') }}">{{ __('consultancy_engagement.dashboard_ui.all_assignments') }} →</a>
                </div>
                @if ($assignments->isNotEmpty())
                    <ul class="expert-assignment-list">
                        @foreach ($assignments as $order)
                            @php($progress = OrderDelivery::progress($order))
                            <li class="expert-assignment-row">
                                <div>
                                    <strong>{{ $order->proposal?->title ?? $order->number }}</strong>
                                    <span class="expert-muted">@if ($order->proposal?->title){{ $order->number }} · @endif{{ __(Order::STATUSES[$order->status] ?? $order->status) }}</span>
                                </div>
                                <div class="expert-assignment-progress expert-muted">
                                    @if ($progress['total'])
                                        {{ $progress['percent'] }}%
                                        <progress max="100" value="{{ $progress['percent'] }}" aria-label="{{ __('Delivery progress') }}"></progress>
                                    @else
                                        {{ __('consultancy_engagement.dashboard_ui.unplanned') }}
                                    @endif
                                </div>
                                <a class="expert-row-link" href="{{ route('expert.orders.tasks', $order) }}" aria-label="{{ __('consultancy_engagement.dashboard_ui.tasks_for', ['order' => $order->number]) }}">{{ __('consultancy_engagement.dashboard_ui.task_board') }} →</a>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="expert-empty">{{ __('consultancy_engagement.dashboard_ui.no_assignments') }}</p>
                @endif
            </section>
        @else
            <section class="expert-panel expert-onboarding">
                @if ($application->engagement_policy)
                    <h2>{{ __('consultancy_engagement.onboarding') }}</h2>
                    <p class="expert-muted">{{ __('consultancy_engagement.review_one_at_a_time') }}</p>
                    <a class="expert-hero-link" href="{{ route('expert.onboarding') }}">{{ __('consultancy_engagement.continue_onboarding') }} <x-ui-icon name="arrow-right" class="size-4" /></a>
                @else
                    <h2>{{ __('consultancy_engagement.waiting') }}</h2>
                    <p class="expert-muted">{{ __('consultancy_engagement.waiting_hint') }}</p>
                @endif
            </section>
        @endif

        <section class="expert-panel" aria-labelledby="engagement-summary">
            <div class="expert-panel-head">
                <h2 id="engagement-summary">{{ __('consultancy_engagement.engagement_summary') }}</h2>
                @if ($application->signed_at)
                    <a href="{{ route('expert.contract.show') }}">{{ __('consultancy_engagement.details') }} →</a>
                @endif
            </div>
            <dl class="expert-engagement">
                <div><dt class="expert-muted">{{ __('consultancy_engagement.category') }}</dt><dd>{{ $application->category->name }}</dd></div>
                <div><dt class="expert-muted">{{ __('consultancy_engagement.engagement_type') }}</dt><dd>{{ $contractLabel }}</dd></div>
            </dl>
        </section>
    </div>
</x-consultancy-dashboard>
