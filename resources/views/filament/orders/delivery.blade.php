@php
    use App\Models\ConsultancyApplication;
    use App\Services\OrderDelivery;
    use App\Support\Money;

    $order = $getRecord();
    $progress = OrderDelivery::progress($order);
    $assignments = $order->expertAssignments->loadMissing('application');
    $earned = app(OrderDelivery::class)->earnings($order);
@endphp

<div class="order-workspace order-delivery-grid">
    <section class="order-delivery-panel" aria-label="Milestone progress">
        <div class="order-delivery-heading">
            <svg class="order-delivery-ring" viewBox="0 0 48 48" fill="none" aria-hidden="true">
                <circle cx="24" cy="24" r="19" stroke="var(--order-accent-soft)" stroke-width="5"/>
                @if ($progress['percent'] > 0)
                    <circle class="order-ring-value" cx="24" cy="24" r="19" stroke="currentColor" stroke-width="5" stroke-linecap="round"
                        pathLength="100" stroke-dasharray="{{ $progress['percent'] }} 100" transform="rotate(-90 24 24)"/>
                @endif
            </svg>
            <div>
                <h3>{{ $progress['percent'] }}% delivery progress</h3>
                <p class="order-muted">{{ $progress['completed'] }} of {{ $progress['total'] }} complete</p>
            </div>
        </div>
        @if (filled($order->delivery_milestones))
            <ol class="order-milestones" aria-label="Milestones">
                @foreach ($order->delivery_milestones as $milestone)
                    @php
                        $tasks = collect($milestone['tasks'] ?? []);
                        $complete = filled($milestone['completed_at'] ?? null);
                        $done = $tasks->where('status', 'done')->count();
                    @endphp
                    <li class="order-delivery-row" data-complete="{{ $complete ? 'true' : 'false' }}">
                        <div>
                            <strong>{{ $milestone['title'] }}</strong>
                            @if (filled($milestone['due'] ?? null))
                                <p class="order-muted">Due {{ \Illuminate\Support\Carbon::parse($milestone['due'])->format('j M Y') }}</p>
                            @endif
                        </div>
                        <span class="order-pill" data-complete="{{ $complete ? 'true' : 'false' }}">
                            {{ $complete ? 'Complete' : ($tasks->isEmpty() ? 'Pending' : $done.' / '.$tasks->count().' tasks done') }}
                        </span>
                    </li>
                @endforeach
            </ol>
        @else
            <p class="order-muted order-delivery-empty">No milestones planned. Add them from the Delivery menu.</p>
        @endif
    </section>

    <section class="order-delivery-panel" aria-label="Expert earnings">
        <h3>Expert earnings</h3>
        @forelse ($assignments as $assignment)
            @php($available = max(0, ($earned[$assignment->id] ?? 0) - $assignment->credited_minor))
            <div class="order-delivery-row order-expert">
                <div>
                    <strong>{{ $assignment->application->name }}</strong>
                    <p class="order-muted">{{ ConsultancyApplication::contractLabel($assignment->application->contract_type ?? 'freelancer') }}</p>
                    <p class="order-muted">{{ rtrim(rtrim($assignment->engagement_percentage, '0'), '.') }}% ÷ {{ $assignments->count() }} {{ $assignments->count() === 1 ? 'expert' : 'experts' }}</p>
                </div>
                <div class="order-amount">
                    <strong>{{ Money::format($available, true) }} ready</strong>
                    <p class="order-muted">{{ Money::format($assignment->credited_minor, true) }} credited</p>
                    @if ($assignment->credited_minor > ($earned[$assignment->id] ?? 0) && $order->status !== 'cancelled')
                        <p class="order-muted order-credit-warning">Credits exceed current earnings</p>
                    @endif
                </div>
            </div>
        @empty
            <p class="order-muted order-delivery-empty">No experts assigned.</p>
        @endforelse
    </section>
</div>
