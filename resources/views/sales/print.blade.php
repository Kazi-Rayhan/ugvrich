@php
    use App\Models\Order;
    use App\Models\Payment;
    use App\Support\Money;

    $site = app(\App\Support\Site::class);
    $isOrder = $kind === 'order';
    $title = $isOrder ? 'Order' : 'Proposal';
    $client = $document->client;
    $taxRate = rtrim(rtrim((string) $document->tax_rate, '0'), '.');
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $title }} {{ $document->number }} · {{ $site->name() }}</title>
    <style>
        :root { --ink: #0f172a; --muted: #64748b; --line: #e2e8f0; --brand: #0b7a4b; --soft: #f8fafc; }
        * { box-sizing: border-box; }
        body { margin: 0; background: #eef2f6; color: var(--ink); font: 13.5px/1.55 "Inter", "Segoe UI", Arial, sans-serif; }
        .page { max-width: 820px; margin: 24px auto; background: #fff; padding: 44px 48px; border-radius: 10px; box-shadow: 0 10px 40px -20px rgba(15, 23, 42, .35); }
        .toolbar { max-width: 820px; margin: 20px auto 0; display: flex; justify-content: flex-end; gap: 8px; }
        .toolbar button { border: 0; border-radius: 8px; padding: 9px 16px; font-weight: 600; cursor: pointer; background: var(--brand); color: #fff; }
        header { display: flex; justify-content: space-between; align-items: flex-start; gap: 24px; border-bottom: 3px solid var(--brand); padding-bottom: 18px; }
        header img { height: 52px; }
        .doc-title { text-align: right; }
        .doc-title h1 { margin: 0; font-size: 26px; letter-spacing: .02em; color: var(--brand); }
        .doc-title p { margin: 2px 0 0; color: var(--muted); }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin: 24px 0; }
        .label { font-size: 11px; text-transform: uppercase; letter-spacing: .08em; color: var(--muted); font-weight: 600; margin-bottom: 4px; }
        h2 { font-size: 15px; margin: 0 0 6px; }
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; font-size: 11px; text-transform: uppercase; letter-spacing: .06em; color: var(--muted); background: var(--soft); padding: 9px 10px; border-bottom: 1px solid var(--line); }
        td { padding: 10px; border-bottom: 1px solid var(--line); vertical-align: top; }
        .num { text-align: right; white-space: nowrap; }
        .desc { color: var(--muted); font-size: 12.5px; margin-top: 2px; white-space: pre-line; }
        .totals { margin-left: auto; width: 320px; margin-top: 14px; }
        .totals td { border: 0; padding: 5px 10px; }
        .totals .grand td { border-top: 2px solid var(--ink); font-size: 16px; font-weight: 700; padding-top: 10px; }
        .section { margin-top: 26px; }
        .section p { margin: 0; white-space: pre-line; }
        .cond-heading { font-size: 13.5px; margin: 14px 0 6px; color: var(--brand); }
        .conditions th { width: 32%; background: none; text-transform: none; letter-spacing: 0; font-size: 13px; color: var(--ink); font-weight: 600; vertical-align: top; }
        .conditions td { white-space: pre-line; }
        .conditions tr { page-break-inside: avoid; }
        .badge { display: inline-block; padding: 2px 9px; border-radius: 999px; font-size: 11.5px; font-weight: 600; background: var(--soft); border: 1px solid var(--line); }
        .muted { color: var(--muted); }
        footer { margin-top: 36px; padding-top: 14px; border-top: 1px solid var(--line); color: var(--muted); font-size: 12px; text-align: center; }
        @media print {
            body { background: #fff; }
            .toolbar { display: none; }
            .page { margin: 0; box-shadow: none; border-radius: 0; padding: 0; max-width: none; }
            @page { margin: 16mm; }
        }
    </style>
</head>
<body>
<div class="toolbar"><button type="button" onclick="window.print()">Print / Save as PDF</button></div>

<div class="page">
    <header>
        <div>
            <img src="{{ asset('media/logo-full.png') }}" alt="{{ $site->name() }}">
            <div class="muted" style="margin-top:8px">
                {{ $site->get('contact_address') }}<br>
                {{ collect([$site->get('contact_phone'), $site->get('contact_email')])->filter()->implode(' · ') }}
            </div>
        </div>
        <div class="doc-title">
            <h1>{{ strtoupper($title) }}</h1>
            <p><strong>{{ $document->number }}</strong></p>
            @if ($isOrder)
                <p>Order date: {{ $document->order_date?->format('j F Y') }}</p>
                @if ($document->expected_delivery_date)
                    <p>Expected delivery: {{ $document->expected_delivery_date->format('j F Y') }}</p>
                @endif
                <p><span class="badge">{{ Order::STATUSES[$document->status] ?? $document->status }}</span>
                   <span class="badge">{{ Order::PAYMENT_STATUSES[$document->payment_status] }}</span></p>
            @else
                <p>Date: {{ $document->issue_date?->format('j F Y') }}</p>
                @if ($document->valid_until)
                    <p>Valid until: {{ $document->valid_until->format('j F Y') }}</p>
                @endif
            @endif
        </div>
    </header>

    <div class="grid">
        <div>
            <div class="label">{{ $isOrder ? 'Client' : 'Prepared for' }}</div>
            <h2>{{ $client->name }}</h2>
            @if ($client->organization)<div>{{ $client->organization }}</div>@endif
            @if ($client->address)<div class="muted">{{ $client->address }}</div>@endif
            <div class="muted">{{ collect([$client->phone, $client->email])->filter()->implode(' · ') }}</div>
        </div>
        <div>
            <div class="label">{{ $isOrder ? 'Reference' : 'Subject' }}</div>
            @if ($isOrder)
                <h2>{{ $document->proposal?->title ?? 'Consultancy services' }}</h2>
                @if ($document->proposal)<div class="muted">Proposal {{ $document->proposal->number }}</div>@endif
            @else
                <h2>{{ $document->title }}</h2>
                @if ($document->delivery_timeline)<div class="muted">Estimated delivery: {{ $document->delivery_timeline }}</div>@endif
            @endif
            @if ($document->categories->isNotEmpty())
                <div class="muted">{{ $document->categories->pluck('name')->implode(', ') }}</div>
            @endif
        </div>
    </div>

    <table>
        <thead>
        <tr>
            <th style="width:36px">#</th>
            <th>Service</th>
            <th class="num">Qty</th>
            <th class="num">Unit price</th>
            <th class="num">Discount</th>
            <th class="num">Amount</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($document->items as $item)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td><strong>{{ $item->service_name }}</strong>@if ($item->description)<div class="desc">{{ $item->description }}</div>@endif</td>
                <td class="num">{{ rtrim(rtrim((string) $item->quantity, '0'), '.') }}</td>
                <td class="num">{{ Money::format($item->unit_price) }}</td>
                <td class="num">{{ Money::toMinor($item->discount) > 0 ? Money::format($item->discount) : '—' }}</td>
                <td class="num">{{ Money::format($item->line_total) }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <table class="totals">
        <tr><td>Subtotal</td><td class="num">{{ Money::format($document->subtotal) }}</td></tr>
        @if (Money::toMinor($document->discount_total) > 0)
            <tr><td>Discounts</td><td class="num">− {{ Money::format($document->discount_total) }}</td></tr>
        @endif
        @if (Money::toMinor($document->tax_amount) > 0)
            <tr><td>Tax ({{ $taxRate }}%)</td><td class="num">{{ Money::format($document->tax_amount) }}</td></tr>
        @endif
        <tr class="grand"><td>Total</td><td class="num">{{ Money::format($document->total) }}</td></tr>
        @if ($isOrder)
            <tr><td>Paid</td><td class="num">{{ Money::format($document->amount_paid) }}</td></tr>
            <tr><td><strong>Balance due</strong></td><td class="num"><strong>{{ Money::format($document->amount_outstanding) }}</strong></td></tr>
        @endif
    </table>

    @if ($isOrder && $document->payments->where('status', Payment::CONFIRMED)->isNotEmpty())
        <div class="section">
            <div class="label">Payments received</div>
            <table>
                <thead><tr><th>Reference</th><th>Date</th><th>Method</th><th>Transaction ID</th><th class="num">Amount</th></tr></thead>
                <tbody>
                @foreach ($document->payments->where('status', Payment::CONFIRMED) as $payment)
                    <tr>
                        <td>{{ $payment->reference }}</td>
                        <td>{{ $payment->paid_on->format('j M Y') }}</td>
                        <td>{{ Payment::METHODS[$payment->method] ?? $payment->method }}</td>
                        <td>{{ $payment->transaction_id ?: '—' }}</td>
                        <td class="num">{{ Money::format($payment->amount) }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif

    @if ($groups = \App\Filament\Support\SalesConditions::grouped($document->conditions))
        <div class="section">
            <div class="label">Scope &amp; conditions</div>
            @foreach ($groups as $heading => $rows)
                <h3 class="cond-heading">{{ $heading }}</h3>
                <table class="conditions">
                    @foreach ($rows as $row)
                        <tr><th>{{ $row['label'] }}</th><td>{{ $row['details'] }}</td></tr>
                    @endforeach
                </table>
            @endforeach
        </div>
    @endif

    @if ($document->payment_terms)
        <div class="section"><div class="label">Payment terms</div><p>{{ $document->payment_terms }}</p></div>
    @endif

    @if ($document->terms)
        <div class="section"><div class="label">Terms and conditions</div><p>{{ $document->terms }}</p></div>
    @endif

    <footer>{{ $site->name() }} · All amounts in Bangladeshi Taka ({{ Money::CURRENCY }}) · Printed {{ now()->format('j F Y') }}</footer>
</div>
</body>
</html>
