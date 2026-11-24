@php
    use App\Services\OrderDelivery;
    use App\Support\Money;

    $order = $getRecord();
    $progress = OrderDelivery::progress($order);
    $assignments = $order->expertAssignments->loadMissing('application');
    $earned = app(OrderDelivery::class)->earnings($order);
@endphp

<div class="order-delivery">
    <style>
        .order-delivery .delivery-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(min(100%,20rem),1fr)); gap:1.25rem; }
        .order-delivery h3 { font-weight:700; margin-block-end:.5rem; }
        .order-delivery progress { display:block; width:100%; height:.6rem; accent-color:#0d9488; margin:.5rem 0 .75rem; }
        .order-delivery .delivery-row { display:flex; justify-content:space-between; gap:.75rem; padding:.65rem 0; border-bottom:1px solid currentColor; border-color:color-mix(in srgb,currentColor 15%,transparent); }
        .order-delivery .delivery-muted { font-size:.825rem; opacity:.78; }
        .order-delivery .delivery-amount { text-align:end; white-space:nowrap; font-variant-numeric:tabular-nums; }
    </style>
    <div class="delivery-grid">
        <section aria-label="Milestone progress">
            <h3>Milestones <span class="delivery-muted">· {{ $progress['completed'] }} of {{ $progress['total'] }} complete</span></h3>
            <progress value="{{ $progress['percent'] }}" max="100" aria-label="Delivery progress">{{ $progress['percent'] }}%</progress>
            <div class="delivery-muted">{{ $progress['percent'] }}% delivery progress</div>
            @forelse ($order->delivery_milestones ?? [] as $milestone)
                @php($taskCount = collect($milestone['tasks'])->where('status', 'done')->count())
                <div class="delivery-row">
                    <div>
                        <strong>{{ $milestone['title'] }}</strong>
                        @if ($milestone['due'])<div class="delivery-muted">Due {{ \Illuminate\Support\Carbon::parse($milestone['due'])->format('j M Y') }}</div>@endif
                    </div>
                    <div class="delivery-muted">{{ $milestone['completed_at'] ? 'Complete' : $taskCount.' / '.count($milestone['tasks']).' tasks done' }}</div>
                </div>
            @empty
                <p class="delivery-muted">No milestones planned. Add them from the Delivery menu.</p>
            @endforelse
        </section>
        <section aria-label="Expert earnings">
            <h3>Expert earnings</h3>
            @forelse ($assignments as $assignment)
                @php($available = max(0, ($earned[$assignment->id] ?? 0) - $assignment->credited_minor))
                <div class="delivery-row">
                    <div>
                        <strong>{{ $assignment->application->name }}</strong>
                        @if ($assignment->application->contract_type)
                            <div class="delivery-muted">{{ \App\Models\ConsultancyApplication::contractLabel($assignment->application->contract_type) }}</div>
                        @endif
                        <div class="delivery-muted">{{ rtrim(rtrim($assignment->engagement_percentage, '0'), '.') }}% ÷ {{ $assignments->count() }} experts</div>
                    </div>
                    <div class="delivery-amount">
                        <strong>{{ Money::format($available, true) }} ready</strong>
                        <div class="delivery-muted">{{ Money::format($assignment->credited_minor, true) }} credited</div>
                        @if ($assignment->credited_minor > ($earned[$assignment->id] ?? 0) && $order->status !== 'cancelled')
                            <div class="delivery-muted">Credits exceed current earnings</div>
                        @endif
                    </div>
                </div>
            @empty
                <p class="delivery-muted">No experts assigned.</p>
            @endforelse
        </section>
    </div>
</div>
