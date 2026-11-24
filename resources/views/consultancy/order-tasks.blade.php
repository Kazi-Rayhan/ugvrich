@php
    use App\Services\OrderDelivery;

    $progress = OrderDelivery::progress($order);
    $title = $order->proposal?->title ?: $order->number;
@endphp

<x-consultancy-dashboard :application="$application" page="assignments">
    <style>
        .task-workspace { --line:#dce6ec; --ink:#132a3e; --muted:#526579; --accent:#13786b; display:grid; gap:14px; color:var(--ink); }
        .task-workspace :focus-visible { outline:2px solid #13786b; outline-offset:3px; }
        .task-links { display:flex; justify-content:space-between; flex-wrap:wrap; gap:8px; }
        .task-links a { display:inline-flex; align-items:center; gap:6px; min-height:38px; color:#12685e; font-size:.82rem; font-weight:750; }
        .task-links a:hover { text-decoration:underline; }
        .task-links svg { width:16px; height:16px; }
        .task-overview { display:grid; grid-template-columns:minmax(0,1fr) auto; gap:10px; align-items:center; padding:18px 22px; background:#12283d; color:#fff; border-radius:16px; }
        .task-overview h2 { color:#fff; font-family:var(--font-display); font-weight:800; font-size:clamp(1.15rem,3vw,1.8rem); line-height:1.25; overflow-wrap:anywhere; }
        .task-overview p { color:#bad3de; font-size:.78rem; margin-top:5px; }
        .task-overview .task-order-number { margin:0 0 4px; color:#a7e4d2; font-weight:750; overflow-wrap:anywhere; }
        .task-dial { display:grid; place-items:center; width:94px; }
        .task-dial > * { grid-area:1/1; }
        .task-dial svg { width:100%; height:auto; }
        .task-dial-value { color:#fff; font-size:1.16rem; font-weight:800; font-variant-numeric:tabular-nums; }
        .task-dial-fill { transition:stroke-dasharray .4s ease; }
        .task-instructions { color:var(--muted); font-size:.8rem; }
        .task-notice { color:#105c53; font-size:.83rem; padding:8px 12px; border:1px solid var(--line); border-radius:9px; background:#fff; }
        .milestone { min-width:0; background:#fff; border:1px solid var(--line); border-radius:14px; }
        .milestone summary { display:flex; align-items:center; gap:10px; padding:13px; cursor:pointer; list-style:none; border-radius:14px; }
        .milestone summary::-webkit-details-marker { display:none; }
        .milestone-chevron { display:grid; place-items:center; width:27px; height:27px; flex:none; color:#12685e; background:#e8f4f1; border-radius:7px; }
        .milestone-chevron svg { width:15px; height:15px; }
        .milestone[open] .milestone-chevron svg { transform:rotate(90deg); }
        .milestone-name { flex:1; min-width:0; }
        .milestone-name strong { display:block; font-size:.95rem; overflow-wrap:anywhere; }
        .milestone-name small { color:var(--muted); font-size:.75rem; }
        .milestone-count { padding:4px 8px; color:#12685e; background:#e8f4f1; border-radius:7px; font-size:.71rem; font-weight:750; white-space:nowrap; }
        .board-shell { min-width:0; padding:0 12px 12px; }
        .lane-tabs { display:none; }
        .board { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:10px; }
        .lane { --tone:#4b6584; min-width:0; min-height:118px; padding:9px; background:#eef3f7; border:1px solid #e0e8ee; border-radius:11px; }
        .lane[data-lane="doing"] { --tone:#8c601f; background:#f8f3e9; border-color:#eee5d2; }
        .lane[data-lane="done"] { --tone:#166f61; background:#ecf6f1; border-color:#dcece3; }
        .lane.is-over, .lane-tab.is-over { outline:2px solid var(--accent); outline-offset:-2px; }
        .lane-heading { display:flex; align-items:center; gap:6px; margin-bottom:8px; color:var(--tone); font-size:.79rem; font-weight:800; }
        .lane-heading::before { content:''; background:currentColor; width:7px; height:7px; border-radius:50%; }
        .lane-heading span { margin-left:auto; padding:0 6px; background:#fff; border-radius:6px; font-variant-numeric:tabular-nums; }
        .lane-content { display:grid; gap:7px; min-height:54px; align-content:start; }
        .lane-content:has([data-task]) .empty-lane { display:none; }
        .empty-lane { padding:12px 5px; border:1px dashed #bdcdd6; border-radius:8px; text-align:center; color:var(--muted); font-size:.74rem; }
        .task-card { display:grid; grid-template-columns:minmax(0,1fr) auto; gap:6px; align-items:start; background:#fff; border:1px solid #dce6ec; border-radius:9px; padding:9px 7px 9px 10px; overflow-wrap:anywhere; }
        .task-card h5 { font-size:.84rem; font-weight:800; line-height:1.35; }
        .task-card p { color:var(--muted); font-size:.76rem; line-height:1.45; margin-top:4px; white-space:pre-line; }
        .task-card[draggable="true"], .task-grip { cursor:grab; }
        .task-grip { display:grid; place-items:center; width:36px; min-height:44px; color:#4d6b7d; border-radius:7px; touch-action:none; }
        .task-grip:hover { background:#edf4f4; }
        .task-grip svg { width:18px; height:18px; }
        .task-card[aria-busy="true"], .task-card.is-dragging { opacity:.55; }
        .task-card.is-picked { outline:2px solid var(--accent); }
        .task-ghost { position:fixed; z-index:100; width:min(232px,70vw); padding:10px; pointer-events:none; border:1px solid #70b9ac; border-radius:9px; color:var(--ink); background:#fff; font-size:.81rem; font-weight:750; box-shadow:0 6px 18px #132a3e35; }
        @media (max-width:57rem) {
            .board-shell { padding-inline:8px; }
            .board { grid-template-columns:none; grid-auto-flow:column; grid-auto-columns:88%; overflow-x:auto; scroll-snap-type:x mandatory; overscroll-behavior-inline:contain; padding:2px 2px 8px; scrollbar-width:thin; }
            .board.is-touch-drag { scroll-snap-type:none; }
            .lane { scroll-snap-align:start; }
            .lane-tabs { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:4px; margin:0 2px 7px; }
            .lane-tab { min-height:40px; padding:4px 2px; border:1px solid #dce6ec; border-radius:7px; color:#42596d; font-size:.73rem; font-weight:750; cursor:pointer; overflow-wrap:anywhere; }
            .lane-tab[aria-current="true"] { color:#12685e; border-color:#a5ccc5; background:#e8f4f1; }
            .task-grip { width:44px; }
        }
        @media (max-width:34rem) {
            .task-overview { padding:12px 13px; }
            .task-dial { width:76px; }
            .task-dial-value { font-size:1rem; }
            .milestone summary { padding:10px 8px; gap:6px; }
        }
        @media (prefers-reduced-motion:reduce) { .task-dial-fill { transition:none; } }
    </style>

    <section class="task-workspace" data-order-boards aria-label="{{ __('consultancy_engagement.task_board.title') }}">
        <nav class="task-links" aria-label="{{ __('consultancy_engagement.task_board.title') }}">
            <a href="{{ route('expert.orders.index') }}"><x-ui-icon name="arrow-left" />{{ __('Back to assignments') }}</a>
            <a href="{{ route('expert.orders.show', $order) }}">{{ __('Show work order') }}<x-ui-icon name="arrow-up-right" /></a>
        </nav>
        <header class="task-overview">
            <div>
                @if ($title !== $order->number)
                    <p class="task-order-number">{{ $order->number }}</p>
                @endif
                <h2>{{ $title }}</h2>
                <p>{{ __('consultancy_engagement.dashboard_ui.milestone_fraction', ['done' => $progress['completed'], 'total' => $progress['total']]) }}</p>
                @if ($order->status === 'cancelled')
                    <p>{{ __('Cancelled') }}</p>
                @endif
            </div>
            <div class="task-dial" role="img" aria-label="{{ __('consultancy_engagement.task_board.progress', ['percent' => $progress['percent']]) }}" data-delivery-dial>
                <svg viewBox="0 0 100 100" fill="none" aria-hidden="true">
                    <circle cx="50" cy="50" r="38" stroke="#3d596c" stroke-width="8"/>
                    <circle class="task-dial-fill" data-delivery-arc cx="50" cy="50" r="38" pathLength="100" transform="rotate(-90 50 50)" stroke="#8de2cb" stroke-width="8" stroke-dasharray="{{ $progress['percent'] }} 100"/>
                </svg>
                <strong class="task-dial-value" data-delivery-percent>{{ $progress['percent'] }}%</strong>
            </div>
        </header>

        @if ($order->status !== 'cancelled' && collect($order->delivery_milestones)->contains(fn (array $milestone) => ! $milestone['completed_at'] && $milestone['tasks'] !== []))
            <p class="task-instructions" id="task-instructions">
                {{ __('consultancy_engagement.task_board.instructions') }}
                <span class="sr-only">{{ __('consultancy_engagement.task_board.keyboard') }}</span>
            </p>
        @endif
        <div role="status" aria-live="polite"><p data-board-notice class="task-notice" hidden></p></div>

        @forelse ($order->delivery_milestones ?? [] as $milestone)
            @php
                $editable = $order->status !== 'cancelled' && ! $milestone['completed_at'];
                $groups = collect($milestone['tasks'])->groupBy('status');
            @endphp
            <details class="milestone" id="milestone-{{ $milestone['id'] }}" @if ($editable) open @endif>
                <summary>
                    <span class="milestone-chevron" aria-hidden="true"><x-ui-icon name="chevron-right" /></span>
                    <span class="milestone-name">
                        <strong>{{ $milestone['title'] }}</strong>
                        @if ($milestone['due'])
                            <small>{{ __('Due :date', ['date' => \Illuminate\Support\Carbon::parse($milestone['due'])->translatedFormat('j M Y')]) }}</small>
                        @endif
                    </span>
                    <span class="milestone-count">
                        @if ($milestone['completed_at'])
                            {{ __('Complete') }}
                        @else
                            <span data-done-count>{{ $groups->get('done', collect())->count() }}</span> / {{ count($milestone['tasks']) }} {{ __('tasks done') }}
                        @endif
                    </span>
                </summary>
                <div class="board-shell">
                    <div class="lane-tabs" aria-label="{{ __('consultancy_engagement.task_board.columns') }}">
                        @foreach (OrderDelivery::TASK_STATUSES as $status => $label)
                            <button type="button" class="lane-tab" data-lane-tab="{{ $status }}" aria-controls="lane-{{ $milestone['id'] }}-{{ $status }}" @if ($loop->first) aria-current="true" @endif>{{ __('consultancy_engagement.dashboard_ui.'.$status) }}</button>
                        @endforeach
                    </div>
                    <div class="board" data-milestone-board>
                        @foreach (OrderDelivery::TASK_STATUSES as $status => $label)
                            @php($tasks = $groups->get($status, collect()))
                            <div class="lane" id="lane-{{ $milestone['id'] }}-{{ $status }}" data-lane="{{ $status }}" aria-label="{{ __('consultancy_engagement.dashboard_ui.'.$status) }}">
                                <h4 class="lane-heading">
                                    {{ __('consultancy_engagement.dashboard_ui.'.$status) }}
                                    <span data-lane-count>{{ $tasks->count() }}</span>
                                </h4>
                                <div class="lane-content">
                                    <span class="empty-lane">{{ $editable ? __('Drop a task here') : __('No tasks') }}</span>
                                    @foreach ($tasks as $task)
                                        <article class="task-card" data-task draggable="{{ $editable ? 'true' : 'false' }}"
                                            @if ($editable) data-update-url="{{ route('expert.orders.tasks.update', [$order, $milestone['id'], $task['id']]) }}" @endif>
                                            <div>
                                                <h5>{{ $task['title'] }}</h5>
                                                @if ($task['description'])
                                                    <p>{{ $task['description'] }}</p>
                                                @endif
                                            </div>
                                            @if ($editable)
                                                <button type="button" class="task-grip" data-task-grip aria-pressed="false" aria-describedby="task-instructions" aria-label="{{ __('consultancy_engagement.task_board.grip', ['task' => $task['title']]) }}">
                                                    <svg viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                        <circle cx="7.5" cy="5" r="1.4"/><circle cx="12.5" cy="5" r="1.4"/>
                                                        <circle cx="7.5" cy="10" r="1.4"/><circle cx="12.5" cy="10" r="1.4"/>
                                                        <circle cx="7.5" cy="15" r="1.4"/><circle cx="12.5" cy="15" r="1.4"/>
                                                    </svg>
                                                </button>
                                            @endif
                                        </article>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </details>
        @empty
            <p class="task-notice">{{ __('The delivery team has not added any milestones yet.') }}</p>
        @endforelse
    </section>

    @include('consultancy.partials.task-board-script')
</x-consultancy-dashboard>
