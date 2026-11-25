@php
    $order = $getRecord();
    $estimate = $order->expected_delivery_date === null ? $order->workOrderDeliveryDate() : null;
@endphp

@include('filament.orders.delivery-styles')

<section class="order-workspace order-overview" aria-label="Order ownership and schedule">
    <div class="order-overview-header">
        <h2>Ownership and schedule</h2>
        @can('update', $order)
            {{ $getLivewire()->getAction('deliveryDetails', isMounting: false) }}
        @endcan
    </div>

    <dl class="order-overview-facts">
        <div class="order-fact">
            <x-filament::icon icon="heroicon-o-user-circle" class="order-fact-icon" />
            <div>
                <dt>Assigned to</dt>
                <dd>{{ $order->assignee?->name ?? 'Unassigned' }}</dd>
                <p class="order-muted">Responsible staff</p>
            </div>
        </div>
        <div class="order-fact">
            <x-filament::icon icon="heroicon-o-calendar-days" class="order-fact-icon" />
            <div>
                <dt>Expected delivery</dt>
                <dd>{{ ($order->expected_delivery_date ?? $estimate)?->format('j M Y') ?? 'Unscheduled' }}</dd>
                @if ($estimate)
                    <p class="order-muted">Work order estimate</p>
                @endif
            </div>
        </div>
    </dl>

    <svg class="order-overview-art" width="232" height="144" viewBox="0 0 232 144" fill="none" aria-hidden="true" focusable="false">
        <path d="M7 107h38c19 0 16-52 36-52h80c16 0 17-27 36-27h29" stroke="currentColor" stroke-opacity=".2" stroke-width="2"/>
        <path class="order-art-route" d="M7 107h38c19 0 16-52 36-52h80c16 0 17-27 36-27h29" stroke="currentColor" stroke-width="2" stroke-linecap="round" pathLength="100"/>
        <rect x="26" y="86" width="36" height="39" rx="8" fill="var(--order-panel)" stroke="currentColor"/>
        <path d="M37 98h14m-14 7h14m-14 7h9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
        <circle cx="112" cy="55" r="22" fill="var(--order-panel)" stroke="currentColor"/>
        <path d="M103 55l6 6 12-13" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        <rect x="182" y="9" width="37" height="37" rx="8" fill="var(--order-panel)" stroke="currentColor"/>
        <path d="M191 22h19m-16-7v6m13-6v6m-16 3v13h19V24m-14 6h3m3 0h3" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
        <path d="M85 112h25m-12-12v24m57-26h15m-7.5-7.5v15" stroke="currentColor" stroke-opacity=".33" stroke-linecap="round"/>
    </svg>
</section>
