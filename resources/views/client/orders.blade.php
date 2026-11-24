@php
    use App\Models\Order;
    use App\Support\Money;
@endphp

<x-client-portal :client="$client" :title="$overview ? 'Account overview' : 'Orders'" :section="$overview ? 'dashboard' : 'orders'">
    @if ($overview)
        <dl class="overview-metrics">
            <div><dt>Total orders</dt><dd>{{ $orderCount }}</dd></div>
            <div><dt>Active orders</dt><dd>{{ $activeCount }}</dd></div>
            <div><dt>Completed orders</dt><dd>{{ $completedCount }}</dd></div>
        </dl>
    @endif
    <section class="panel" aria-labelledby="orders-heading">
        <div class="panel-heading">
            <h2 id="orders-heading">{{ $overview ? 'Recent orders' : 'Order history' }}</h2>
            @if ($overview && $orderCount > 5)
                <a class="text-link" href="{{ route('client.orders') }}">View all orders</a>
            @endif
        </div>
        @if ($orders->isEmpty())
            <div class="empty-state">
                <h3>No orders yet</h3>
                <p>Your orders will appear here once RICH creates them.</p>
            </div>
        @else
            <table class="data-table">
                <thead>
                    <tr><th scope="col">Order</th><th scope="col">Date</th><th scope="col">Work status</th><th scope="col">Payment status</th><th scope="col" class="numeric">Balance due</th><th scope="col"><span class="visually-hidden">Actions</span></th></tr>
                </thead>
                <tbody>
                    @foreach ($orders as $order)
                        <tr>
                            <th scope="row"><a class="text-link" href="{{ route('client.orders.show', $order) }}">{{ $order->number }}</a></th>
                            <td data-label="Date">{{ $order->order_date?->format('j M Y') }}</td>
                            <td data-label="Work status"><span class="status" data-status="{{ $order->status }}">{{ Order::STATUSES[$order->status] ?? $order->status }}</span></td>
                            <td data-label="Payment status"><span class="status" data-status="{{ $order->payment_status }}">{{ Order::PAYMENT_STATUSES[$order->payment_status] }}</span></td>
                            <td class="numeric" data-label="Balance due">{{ Money::format($order->amount_outstanding) }}</td>
                            <td class="table-action"><a class="text-link" href="{{ route('client.orders.show', $order) }}" aria-label="View {{ $order->number }}">View order <span aria-hidden="true">→</span></a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </section>
    @if (! $overview && $orders->hasPages())
        <nav class="pagination" aria-label="Order pages">
            @if ($orders->previousPageUrl())<a class="button secondary" href="{{ $orders->previousPageUrl() }}">Previous</a>@endif
            <span>Page {{ $orders->currentPage() }} of {{ $orders->lastPage() }}</span>
            @if ($orders->nextPageUrl())<a class="button secondary" href="{{ $orders->nextPageUrl() }}">Next</a>@endif
        </nav>
    @endif
</x-client-portal>
