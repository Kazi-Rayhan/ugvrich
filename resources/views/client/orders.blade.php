@php
    use App\Models\Order;
    use App\Services\OrderDelivery;
    use App\Support\Money;
@endphp

<x-client-portal :client="$client" :title="$overview ? 'Account overview' : 'Orders'" :section="$overview ? 'dashboard' : 'orders'">
    @if ($overview)
        <x-slot:heading>
            <header class="dashboard-hero">
                <div class="hero-copy">
                    <p class="hero-caption">Client overview</p>
                    <h1>Welcome, {{ $client->name }}</h1>
                    @if (filled($client->organization))
                        <p class="hero-organization">{{ $client->organization }}</p>
                    @endif
                    @if ($orderCount)
                        <a class="button hero-button" href="{{ route('client.orders') }}">Browse orders <x-ui-icon name="arrow-right" width="17" height="17" /></a>
                    @endif
                </div>
                <img class="hero-art" src="{{ asset('media/client-portal-overview.svg') }}" width="420" height="230" alt="">
            </header>
        </x-slot:heading>
        <dl class="overview-metrics dashboard-metrics">
            <div><dt><x-ui-icon name="briefcase" width="17" height="17" /> Total orders</dt><dd>{{ $orderCount }}</dd></div>
            <div><dt><x-ui-icon name="clock" width="17" height="17" /> Active orders</dt><dd>{{ $activeCount }}</dd></div>
            <div><dt><x-ui-icon name="check" width="17" height="17" /> Completed orders</dt><dd>{{ $completedCount }}</dd></div>
        </dl>

        @if ($featuredOrder)
            @php($delivery = OrderDelivery::progress($featuredOrder))
            <section class="panel featured-order" aria-labelledby="active-order">
                <div class="featured-order-main">
                    <h2 id="active-order">Latest active order</h2>
                    <a class="featured-order-title text-link" href="{{ route('client.orders.show', $featuredOrder) }}">{{ $featuredOrder->proposal?->title ?: $featuredOrder->number }}</a>
                    <div class="featured-order-meta">
                        @if ($featuredOrder->proposal?->title)<span>{{ $featuredOrder->number }}</span>@endif
                        <span class="status" data-status="{{ $featuredOrder->status }}">{{ Order::STATUSES[$featuredOrder->status] }}</span>
                        @if ($featuredOrder->expected_delivery_date)<span>Due {{ $featuredOrder->expected_delivery_date->format('j M Y') }}</span>@endif
                    </div>
                </div>
                <div class="featured-order-progress">
                    @if ($delivery['total'] > 0)
                        <div><span>Delivery progress</span><strong>{{ $delivery['percent'] }}%</strong></div>
                        <progress value="{{ $delivery['percent'] }}" max="100" aria-label="Delivery progress"></progress>
                        <small>{{ $delivery['completed'] }} of {{ $delivery['total'] }} milestones complete</small>
                    @else
                        <div><span>Balance due</span><strong>{{ Money::format($featuredOrder->amount_outstanding) }}</strong></div>
                        <small>{{ Order::PAYMENT_STATUSES[$featuredOrder->payment_status] }}</small>
                    @endif
                </div>
                <a class="button secondary featured-order-action" href="{{ route('client.orders.show', $featuredOrder) }}">View order <x-ui-icon name="arrow-right" width="17" height="17" /></a>
            </section>
        @endif
    @endif
    <section class="panel" aria-labelledby="orders-heading">
        <div class="panel-heading">
            <h2 id="orders-heading">{{ $overview ? 'Recent orders' : 'Order history' }}</h2>
            @if ($overview && $orderCount > 5)
                <a class="text-link" href="{{ route('client.orders') }}">View all orders <span aria-hidden="true">→</span></a>
            @endif
        </div>
        @if ($orders->isEmpty())
            <div class="empty-state order-empty">
                <span class="empty-icon"><x-ui-icon name="briefcase" width="22" height="22" /></span>
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
                            <th scope="row">
                                <a class="text-link" href="{{ route('client.orders.show', $order) }}">{{ $order->number }}</a>
                                @if ($order->proposal?->title)<small class="line-note">{{ $order->proposal->title }}</small>@endif
                            </th>
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
