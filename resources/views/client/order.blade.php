@php
    use App\Models\Order;
    use App\Models\Payment;
    use App\Services\OrderDelivery;
    use App\Support\Money;

    $progress = OrderDelivery::progress($order);
    $payments = $order->payments->where('status', Payment::CONFIRMED);
@endphp

<x-client-portal :client="$client" :title="$order->number" section="orders" :description="$order->proposal?->title">
    <x-slot:heading>
        <x-client-section-heading :title="$order->number" :description="$order->proposal?->title" variant="orders">
            <x-slot:breadcrumb>
                <a class="text-link section-breadcrumb" href="{{ route('client.orders') }}">← All orders</a>
            </x-slot:breadcrumb>
            <x-slot:actions>
                <a class="button" href="{{ route('client.orders.invoice', $order) }}"><x-ui-icon name="document" width="17" height="17" /> Print invoice</a>
            </x-slot:actions>
        </x-client-section-heading>
    </x-slot:heading>
    <dl class="overview-metrics order-metrics">
        <div><dt>Order total</dt><dd>{{ Money::format($order->total) }}</dd></div>
        <div><dt>Payments received</dt><dd>{{ Money::format($order->amount_paid) }}</dd></div>
        <div><dt>Balance due</dt><dd>{{ Money::format($order->amount_outstanding) }}</dd></div>
    </dl>
    <div class="detail-grid">
        <section class="panel" aria-labelledby="order-heading">
            <div class="panel-heading"><h2 id="order-heading">Order details</h2></div>
            <dl class="facts">
                <div><dt>Work status</dt><dd><span class="status" data-status="{{ $order->status }}">{{ Order::STATUSES[$order->status] ?? $order->status }}</span></dd></div>
                <div><dt>Payment status</dt><dd><span class="status" data-status="{{ $order->payment_status }}">{{ Order::PAYMENT_STATUSES[$order->payment_status] }}</span></dd></div>
                <div><dt>Order date</dt><dd>{{ $order->order_date?->format('j M Y') }}</dd></div>
                @if ($order->expected_delivery_date)<div><dt>Expected delivery</dt><dd>{{ $order->expected_delivery_date->format('j M Y') }}</dd></div>@endif
                @if ($order->workOrder)<div><dt>Work order</dt><dd>{{ $order->workOrder->number }}</dd></div>@endif
            </dl>
        </section>
        @if ($progress['total'])
            <section class="panel" aria-labelledby="milestones-heading">
                <div class="panel-heading"><h2 id="milestones-heading">Delivery milestones</h2><strong>{{ $progress['percent'] }}%</strong></div>
                <div class="milestones">
                    <progress value="{{ $progress['percent'] }}" max="100" aria-label="Delivery progress"></progress>
                    <ul>
                        @foreach ($order->delivery_milestones as $milestone)
                            <li>
                                <span>{{ $milestone['title'] }}
                                    @if ($milestone['due'])<small>Due {{ \Illuminate\Support\Carbon::parse($milestone['due'])->format('j M Y') }}</small>@endif
                                </span>
                                <span class="status" data-status="{{ $milestone['completed_at'] ? 'completed' : 'pending' }}">{{ $milestone['completed_at'] ? 'Complete' : 'Open' }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </section>
        @endif
    </div>
    <section class="panel order-section" aria-labelledby="services-heading">
        <div class="panel-heading"><h2 id="services-heading">Services and charges</h2></div>
        @if ($order->items->isEmpty())
            <p class="empty-state">No charges recorded.</p>
        @else
            <table class="data-table">
                <thead><tr><th scope="col">Service</th><th scope="col" class="numeric">Quantity</th><th scope="col" class="numeric">Unit price</th><th scope="col" class="numeric">Line total</th></tr></thead>
                <tbody>
                    @foreach ($order->items as $item)
                        <tr>
                            <th scope="row">{{ $item->service_name }}@if ($item->description)<small class="line-note">{{ $item->description }}</small>@endif</th>
                            <td class="numeric" data-label="Quantity">{{ $item->quantity }}</td>
                            <td class="numeric" data-label="Unit price">{{ Money::format($item->unit_price) }}</td>
                            <td class="numeric" data-label="Line total">{{ Money::format($item->line_total) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </section>
    <section class="panel order-section" aria-labelledby="payments-heading">
        <div class="panel-heading"><h2 id="payments-heading">Confirmed payments</h2></div>
        @if ($payments->isEmpty())
            <p class="empty-state">No confirmed payments yet.</p>
        @else
            <table class="data-table">
                <thead><tr><th scope="col">Receipt</th><th scope="col">Date</th><th scope="col">Transaction reference</th><th scope="col" class="numeric">Amount</th></tr></thead>
                <tbody>
                    @foreach ($payments as $payment)
                        <tr>
                            <th scope="row">{{ $payment->reference }}</th>
                            <td data-label="Date">{{ $payment->paid_on?->format('j M Y') }}</td>
                            <td data-label="Transaction reference">{{ $payment->transaction_id ?: '—' }}</td>
                            <td class="numeric" data-label="Amount">{{ Money::format($payment->amount) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </section>
    @if (filled($order->payment_terms))
        <section class="panel order-section" aria-labelledby="terms-heading">
            <div class="panel-heading"><h2 id="terms-heading">Order terms</h2></div>
            <dl class="facts">
                <div><dt>Payment terms</dt><dd class="preserve-lines">{{ $order->payment_terms }}</dd></div>
            </dl>
        </section>
    @endif
</x-client-portal>
