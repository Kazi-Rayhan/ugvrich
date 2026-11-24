@php
    use App\Models\Order;
    use App\Support\Money;
    use App\Support\Sales\Totals;

    $site = app(\App\Support\Site::class);
    $invoiceNumber = 'INV-'.str($order->number)->after('ORD-');
    $client = $order->client;
@endphp
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Invoice {{ $invoiceNumber }}</title>
    <style>
        :root { color-scheme: light; --ink: #182721; --muted: #4d5d55; --brand: #104a36; --rule: #c0ccc4; }
        * { box-sizing: border-box; }
        body { margin: 0; background: #eef1ee; color: var(--ink); font: 14px/1.5 "Segoe UI", Arial, sans-serif; }
        .toolbar, main { max-width: 210mm; margin-inline: auto; }
        .toolbar { display: flex; justify-content: space-between; align-items: center; padding: 16px; gap: 12px; }
        .toolbar a { color: var(--brand); font-weight: 600; }
        button { padding: 8px 14px; color: white; background: var(--brand); border: 1px solid var(--brand); border-radius: 4px; font: inherit; cursor: pointer; }
        main { background: white; padding: 14mm 16mm; margin-bottom: 20px; }
        header { display: flex; justify-content: space-between; align-items: start; gap: 20px; border-bottom: 3px solid var(--brand); padding-bottom: 16px; }
        .issuer { display: flex; align-items: flex-start; gap: 12px; max-width: 65%; }
        .issuer svg { flex: 0 0 36px; color: var(--brand); }
        .issuer strong { display: block; }
        .muted { color: var(--muted); }
        h1 { color: var(--brand); font-size: 32px; line-height: 1; margin: 0 0 8px; }
        h2 { font-size: 15px; margin: 0 0 8px; }
        .reference { text-align: right; }
        .parties { display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); gap: 20px; margin: 22px 0; }
        .label { font-size: 11px; text-transform: uppercase; letter-spacing: .08em; color: var(--muted); font-weight: 700; }
        dl, dd { margin: 0; }
        .meta div, .totals div { display: flex; justify-content: space-between; gap: 12px; }
        .meta div + div { margin-top: 4px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 8px 4px; border-bottom: 1px solid var(--rule); text-align: left; vertical-align: top; overflow-wrap: anywhere; }
        th { border-top: 1px solid var(--brand); color: var(--brand); }
        td small { display: block; white-space: pre-line; }
        .amount { text-align: right; white-space: nowrap; font-variant-numeric: tabular-nums; }
        .totals { max-width: 76mm; margin: 16px 0 0 auto; }
        .totals div { padding: 4px 0; }
        .totals .balance { border-block: 2px solid var(--brand); font-size: 16px; font-weight: 700; color: var(--brand); margin-top: 4px; }
        .section { margin-top: 24px; }
        .terms { white-space: pre-line; margin: 0; }
        footer { margin-top: 24px; border-top: 1px solid var(--rule); padding-top: 8px; color: var(--muted); font-size: 12px; }
        @media screen and (max-width: 600px) {
            main { padding: 20px 12px; }
            .parties { grid-template-columns: minmax(0, 1fr); gap: 12px; }
            .table-scroll { overflow-x: auto; }
        }
        @page { size: A4; margin: 14mm 16mm; }
        @media print {
            body { background: white; font-size: 11pt; }
            .toolbar { display: none; }
            main { padding: 0; margin: 0; max-width: none; }
            thead { display: table-header-group; }
            tr, header, .totals { break-inside: avoid; }
            h2 { break-after: avoid; }
        }
    </style>
</head>
<body>
<nav class="toolbar" aria-label="Invoice actions">
    <a href="{{ \App\Filament\Resources\Orders\OrderResource::getUrl('view', ['record' => $order]) }}">Back to order</a>
    <button type="button" onclick="window.print()">Print / Save as PDF</button>
</nav>
<main>
    <header>
        <div class="issuer">
            <svg viewBox="0 0 36 36" width="36" height="36" aria-hidden="true">
                <rect x="1" y="1" width="34" height="34" fill="none" stroke="currentColor" stroke-width="2"/>
                <path d="M11 9h10l4 4v14H11zM21 9v5h4M14 18h8m-8 4h8" fill="none" stroke="currentColor" stroke-width="1.5"/>
            </svg>
            <div>
                <strong>{{ config('work_order_admin.provider.name') }}</strong>
                <div>{{ config('work_order_admin.provider.university') }}</div>
                @if ($site->get('contact_address'))<div class="muted">{{ $site->get('contact_address') }}</div>@endif
                @if ($site->get('contact_email'))<div class="muted">{{ $site->get('contact_email') }}</div>@endif
                @if ($site->get('contact_phone'))<div class="muted">{{ $site->get('contact_phone') }}</div>@endif
            </div>
        </div>
        <div class="reference">
            <h1>Invoice</h1>
            <strong>{{ $invoiceNumber }}</strong>
        </div>
    </header>

    <div class="parties">
        <section aria-label="Bill to">
            <div class="label">Bill to</div>
            <strong>{{ $client->display_name }}</strong>
            @foreach (array_filter([$client->address, $client->email, $client->phone]) as $contact)
                <div>{{ $contact }}</div>
            @endforeach
        </section>
        <dl class="meta">
            <div><dt>Invoice date</dt><dd>{{ $order->order_date->format('j M Y') }}</dd></div>
            <div><dt>Order</dt><dd>{{ $order->number }}</dd></div>
            @if ($order->workOrder)
                <div><dt>Work order</dt><dd>{{ $order->workOrder->number }}</dd></div>
            @endif
            <div><dt>Payment status</dt><dd>{{ $order->status === 'cancelled' ? 'Cancelled' : Order::PAYMENT_STATUSES[$order->payment_status] }}</dd></div>
        </dl>
    </div>

    <section aria-labelledby="charges">
        <h2 id="charges">Charges <span class="muted">(BDT)</span></h2>
        <div class="table-scroll">
            <table>
                <thead>
                    <tr><th scope="col">Description</th><th scope="col" class="amount">Qty</th><th scope="col" class="amount">Unit price</th><th scope="col" class="amount">Discount</th><th scope="col" class="amount">Amount</th></tr>
                </thead>
                <tbody>
                    @forelse ($order->items as $item)
                        <tr>
                            <td><strong>{{ $item->service_name }}</strong>
                                @if (filled($item->description))<small class="muted">{{ $item->description }}</small>@endif
                            </td>
                            <td class="amount">{{ rtrim(rtrim((string) $item->quantity, '0'), '.') }}</td>
                            <td class="amount">{{ Money::format($item->unit_price) }}</td>
                            <td class="amount">{{ Money::format(Totals::line($item->quantity, $item->unit_price, $item->discount)['discount'], true) }}</td>
                            <td class="amount">{{ Money::format($item->line_total) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5">No charges recorded.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <dl class="totals">
            <div><dt>Subtotal</dt><dd class="amount">{{ Money::format($order->subtotal) }}</dd></div>
            @if (Money::toMinor($order->discount_total) > 0)
                <div><dt>Discounts and deductions</dt><dd class="amount">−{{ Money::format($order->discount_total) }}</dd></div>
            @endif
            @if (Money::toMinor($order->tax_amount) > 0)
                <div><dt>Tax / VAT</dt><dd class="amount">{{ Money::format($order->tax_amount) }}</dd></div>
            @endif
            <div><dt><strong>Invoice total</strong></dt><dd class="amount"><strong>{{ Money::format($order->total) }}</strong></dd></div>
            <div><dt>Payments received</dt><dd class="amount">{{ Money::format($order->amount_paid) }}</dd></div>
            <div class="balance"><dt>Balance due</dt><dd class="amount">{{ Money::format($order->amount_outstanding) }}</dd></div>
        </dl>
    </section>

    @if ($order->payments->isNotEmpty())
        <section class="section" aria-labelledby="payments">
            <h2 id="payments">Confirmed payments</h2>
            <div class="table-scroll">
                <table>
                    <thead><tr><th scope="col">Date</th><th scope="col">Receipt</th><th scope="col">Receiving account</th><th scope="col">Transaction reference</th><th scope="col" class="amount">Amount</th></tr></thead>
                    <tbody>
                        @foreach ($order->payments as $payment)
                            <tr>
                                <td>{{ $payment->paid_on->format('j M Y') }}</td>
                                <td>{{ $payment->reference }}</td>
                                <td>{{ $payment->receiving_account_label }}</td>
                                <td>{{ $payment->transaction_id ?: '—' }}</td>
                                <td class="amount">{{ Money::format($payment->amount) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    @endif

    @if (filled($order->payment_terms))
        <section class="section" aria-labelledby="terms">
            <h2 id="terms">Payment terms</h2>
            <p class="terms">{{ $order->payment_terms }}</p>
        </section>
    @endif
    <footer>{{ $invoiceNumber }} <span aria-hidden="true">·</span> {{ config('work_order_admin.provider.university') }}</footer>
</main>
</body>
</html>
