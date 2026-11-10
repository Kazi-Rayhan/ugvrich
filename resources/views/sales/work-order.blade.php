@php
    use App\Support\Money;
    use App\Support\Numerals;

    /** @var \App\Models\WorkOrder $order */
    $d = $order->details ?? [];
    $code = $sector['code'];
    $color = $sector['color'];
    $form = 'WO-'.$code.'-01';

    $bn = fn ($value) => Numerals::localize((string) $value, 'bn');
    $get = fn (string $key) => data_get($d, $key);

    // A written-in value, or the form's blank line when there is none.
    $fill = function ($value, string $width = 'md', bool $digits = false) use ($bn) {
        $value = is_string($value) ? trim($value) : $value;

        return filled($value)
            ? '<span class="fill">'.e($digits ? $bn($value) : $value).'</span>'
            : '<span class="blank w-'.$width.'"></span>';
    };
    $date = fn ($value) => filled($value)
        ? '<span class="fill">'.e($bn(\Illuminate\Support\Carbon::parse($value)->format('d / m / Y'))).'</span>'
        : '<span class="date-blank">___ / ___ / _____</span>';
    $box = fn (bool $on, string $label) => '<span class="opt"><i class="box'.($on ? ' on' : '').'"></i>'.e($label).'</span>';
    $money = fn ($value) => filled($value) && Money::toMinor($value) !== 0
        ? '<span class="fill">'.e($bn(trim(str_replace(Money::SYMBOL, '', Money::format($value))))).'</span>'
        : '<span class="blank w-sm"></span>';
    $num = fn (int $i) => $bn(sprintf('%02d', $i));

    $services = (array) $get('services');
    $attachments = (array) $get('attachments');
    $checklist = (array) $get('checklist');
    $info = (array) $get('info');
    $grand = $get('grand_total');
    $words = $get('amount_words') ?: $get('amount_words_auto');

    $conditions = collect($sector['conditions'])->map(fn ($c) => [$c[0], str_replace(
        '{revisions}',
        filled($get('revisions')) ? $bn($get('revisions')) : '____',
        $c[1],
    )]);

    $pages = 4;
@endphp
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    {{-- The browser names the saved PDF after the title. --}}
    <title>{{ str_replace('/', '-', $order->number) }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Bengali:wght@400;500;600;700&family=Noto+Serif:wght@400;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --c: {{ $color }};
            --tint: color-mix(in srgb, var(--c) 8%, #ffffff);
            --tint-2: color-mix(in srgb, var(--c) 4%, #ffffff);
            --ink: #1b2a3a;
            --navy: #1f3a5f;
            --muted: #6b7785;
            --line: #dfe5ea;
        }
        * { box-sizing: border-box; }
        html { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        body {
            margin: 0;
            background: #e9edf1;
            color: var(--ink);
            font: 13px/1.62 "Noto Serif", "Noto Sans Bengali", serif;
        }

        /* --- screen toolbar ---------------------------------------------- */
        .toolbar { position: sticky; top: 0; z-index: 5; display: flex; justify-content: center; gap: 10px; padding: 12px; background: rgba(233,237,241,.92); backdrop-filter: blur(6px); }
        .toolbar a, .toolbar button { font: 600 13px "Noto Sans Bengali", sans-serif; border-radius: 9px; padding: 9px 16px; cursor: pointer; text-decoration: none; border: 1px solid #c9d2da; background: #fff; color: var(--ink); }
        .toolbar .primary { background: var(--c); border-color: var(--c); color: #fff; }

        /* --- the A4 sheet ------------------------------------------------- */
        .sheet {
            width: 210mm; min-height: 297mm; margin: 14px auto; background: #fff;
            padding: 13mm 16mm 10mm; display: flex; flex-direction: column;
            box-shadow: 0 10px 30px -18px rgba(15,23,42,.45);
        }
        .sheet > .content { flex: 1; }

        .head { display: flex; justify-content: space-between; align-items: flex-start; }
        .brand { display: flex; gap: 12px; align-items: center; }
        .brand img { width: 68px; height: auto; }
        .brand .t1 { font-weight: 700; font-size: 15px; color: var(--navy); line-height: 1.25; }
        .brand .t2 { color: #2f7d4f; font-family: "Noto Sans Bengali", sans-serif; font-size: 13px; line-height: 1.5; }
        .brand .t3 { color: var(--muted); font-family: "Noto Sans Bengali", sans-serif; font-size: 12.5px; }
        .form-id { text-align: right; font-family: "Noto Sans Bengali", sans-serif; font-size: 13px; line-height: 1.9; }
        .form-id .k { color: var(--c); }
        .form-id .s { color: var(--navy); font-weight: 600; }
        .form-id .s b, .form-id .f b { font-family: "Noto Serif", serif; }
        .form-id .f { color: var(--muted); }
        .form-id .f b { color: var(--muted); }
        .rule { height: 3px; background: var(--c); margin: 14px 0 12px; }

        .crumb { color: var(--c); font-size: 12.5px; font-family: "Noto Sans Bengali", sans-serif; }
        .crumb b { font-family: "Noto Serif", serif; }
        h1 { margin: 8px 0 2px; font-size: 16.5px; font-weight: 600; color: var(--navy); font-family: "Noto Sans Bengali", sans-serif; }
        h1 b { font-family: "Noto Serif", serif; font-size: 17.5px; }
        .sub { color: var(--muted); font-size: 12.5px; margin-bottom: 8px; font-family: "Noto Sans Bengali", sans-serif; }

        .sec { margin: 12px 0 6px; color: var(--c); font-family: "Noto Sans Bengali", sans-serif; font-size: 13px; }
        .sec .n { margin-right: 6px; }
        .kicker { color: var(--c); font-size: 12.5px; margin-top: 10px; font-family: "Noto Sans Bengali", sans-serif; }
        .lead { font-weight: 600; margin: 6px 0 2px; font-family: "Noto Sans Bengali", sans-serif; font-size: 13px; }
        .note { color: var(--muted); font-size: 12.5px; margin: 4px 0 6px; font-family: "Noto Sans Bengali", sans-serif; }

        table { width: 100%; border-collapse: collapse; font-family: "Noto Serif", "Noto Sans Bengali", serif; }
        td, th { padding: 7px 10px; vertical-align: top; text-align: left; font-weight: 400; }
        .grid td { border-bottom: 1px solid var(--line); }
        .grid tr:nth-child(odd) td { background: var(--tint-2); }
        .head-row td, .head-row th { background: var(--tint) !important; color: var(--ink); }
        .meta td { border-bottom: 1px solid #fff; }
        .meta .lbl { background: var(--tint); color: var(--muted); width: 22%; }
        .meta .val { background: var(--tint-2); }
        .meta .num { font-weight: 700; color: var(--navy); }
        .parties td { width: 50%; padding: 4px 10px; background: var(--tint-2); }
        .parties .head-row td { padding: 7px 10px; }
        .lbl-col { width: 26%; background: var(--tint) !important; }
        .r { text-align: right; }
        .total-row td { background: var(--tint) !important; font-weight: 600; }

        .fill { display: inline-block; min-width: 40px; border-bottom: 1px solid #7a8794; padding: 0 3px; line-height: 1.35; font-weight: 500; color: #0f2133; }
        .blank { display: inline-block; border-bottom: 1px solid #333; height: 1em; vertical-align: -2px; }
        .w-xs { width: 40px; } .w-sm { width: 70px; } .w-md { width: 95px; } .w-lg { width: 120px; } .w-xl { width: 160px; } .w-xxl { width: 330px; }
        .date-blank { letter-spacing: .5px; }

        .opt { display: inline-flex; align-items: center; gap: 6px; margin-right: 14px; }
        .box { display: inline-block; width: 11px; height: 11px; border: 1px solid #333; position: relative; flex: none; }
        .box.on::after { content: ""; position: absolute; left: 3px; top: 0; width: 3px; height: 7px; border: solid #0f2133; border-width: 0 2px 2px 0; transform: rotate(45deg); }

        .lines { min-height: calc(1.95em * 3); line-height: 1.95em; white-space: pre-line; padding: 0 2px;
            background-image: repeating-linear-gradient(to bottom, transparent 0, transparent calc(1.95em - 1px), #9aa6b1 calc(1.95em - 1px), #9aa6b1 1.95em);
            background-size: 100% 1.95em; }
        .dots { border-bottom: 1px dotted #8f9aa5; height: 1.95em; }

        .clause { margin: 6px 0 8px; font-size: 13px; }
        .clause b { font-weight: 700; }
        .bullet { margin: 0 0 8px; }
        .bullet b { font-weight: 700; }

        .signs td { width: 50%; background: var(--tint-2); padding: 5px 10px; }
        .signs .head-row td { padding: 7px 10px; }
        .pre-check { color: var(--c); margin-top: 8px; font-family: "Noto Sans Bengali", sans-serif; }
        .pre-check { font-size: 12.5px; white-space: nowrap; }
        .pre-check .opt { margin-right: 8px; gap: 4px; }
        .pre-check .box { border-color: var(--c); }

        .foot { margin-top: 10px; border-top: 1px solid var(--line); padding-top: 8px; text-align: center; color: var(--muted); font-size: 12px; font-family: "Noto Serif", "Noto Sans Bengali", serif; }
        .foot b { color: var(--ink); }

        @page { size: A4; margin: 0; }
        @media print {
            body { background: #fff; }
            .toolbar { display: none; }
            .sheet { margin: 0; box-shadow: none; width: auto; min-height: 297mm; break-after: page; }
            .sheet:last-child { break-after: auto; }
            tr, .clause, .bullet { break-inside: avoid; }
        }
    </style>
</head>
<body>

<div class="toolbar">
    <a href="{{ \App\Filament\Resources\WorkOrders\WorkOrderResource::getUrl('edit', ['record' => $order]) }}">← সম্পাদনা (Edit)</a>
    <button type="button" class="primary" onclick="window.print()">PDF ডাউনলোড / প্রিন্ট</button>
</div>

{{-- The header and footer every page of the form carries. --}}
@php
    $header = function () use ($form, $code) {
        return '<div class="head">
            <div class="brand">
                <img src="'.e(asset('media/logo-full.png')).'" alt="UGV RICH">
                <div>
                    <div class="t1">UGV RICH</div>
                    <div class="t2">গবেষণা / উদ্ভাবন / পরামর্শ</div>
                    <div class="t3">ইউনিভার্সিটি অব গ্লোবাল ভিলেজ &nbsp;|&nbsp; বরিশাল</div>
                </div>
            </div>
            <div class="form-id">
                <div class="k">গ্রাহক কার্যাদেশ</div>
                <div class="s">সেবা খাত <b>'.e($code).'</b></div>
                <div class="f">ফরম &nbsp;|&nbsp; <b>'.e($form).'</b></div>
            </div>
        </div>
        <div class="rule"></div>';
    };
    $footer = fn (int $page) => '<div class="foot">UGV RICH &nbsp;•&nbsp; rich.ugv.edu.bd &nbsp;|&nbsp; নিয়ন্ত্রিত কার্যাদেশ নমুনা &nbsp;|&nbsp; '.e($code).' &nbsp;•&nbsp; পৃষ্ঠা <b>'.Numerals::localize($page, 'bn').'</b></div>';
    $text = fn ($value) => filled($value)
        ? '<div class="lines">'.e($value).'</div>'
        : '<div class="dots"></div><div class="dots"></div><div class="dots"></div>';
@endphp

{{-- ============================================================ পৃষ্ঠা ১ --}}
<section class="sheet">
    <div class="content">
        {!! $header() !!}

        <div class="crumb">সেবা খাত &nbsp;/&nbsp; <b>{{ $code }}</b> &nbsp;/&nbsp; {{ $sector['department'] }}</div>
        <h1>সেবা কার্যাদেশ <b>(WORK ORDER)</b></h1>
        <div class="sub">{{ $sector['subtitle'] }}</div>

        <table class="meta">
            <tr>
                <td class="lbl">কার্যাদেশ নম্বর</td>
                <td class="val num">{{ $order->number }}</td>
                <td class="lbl">ইস্যুর তারিখ</td>
                <td class="val">{!! $date($order->issue_date) !!}</td>
            </tr>
            <tr>
                <td class="lbl">প্রস্তাব / মূল্যপ্রস্তাব নম্বর</td>
                <td class="val">{!! $fill($get('quotation_number'), 'xl') !!}</td>
                <td class="lbl">সংশোধন / সংস্করণ</td>
                <td class="val"><b>সংস্করণ</b> {!! $fill($get('revision'), 'sm') !!}</td>
            </tr>
        </table>

        <div class="sec"><span class="n">{{ $num(1) }}</span> চুক্তিবদ্ধ পক্ষসমূহ</div>
        <table class="parties">
            <tr class="head-row"><td>গ্রাহক / কার্যাদেশ প্রদানকারী</td><td>সেবা প্রদানকারী / বাস্তবায়নকারী পক্ষ</td></tr>
            <tr><td>নাম / প্রতিষ্ঠান: {!! $fill($get('client.name'), 'xl') !!}</td><td>{{ config('work_orders.provider.name') }}</td></tr>
            <tr><td>যোগাযোগের ব্যক্তি: {!! $fill($get('client.contact'), 'xl') !!}</td><td>{{ config('work_orders.provider.university') }}</td></tr>
            <tr><td>ফোন / ইমেইল: {!! $fill($get('client.phone_email'), 'xl') !!}</td><td>দায়িত্বপ্রাপ্ত বিভাগ: {{ $sector['department'] }}</td></tr>
            <tr><td>ঠিকানা: {!! $fill($get('client.address'), 'xl') !!}</td><td>যোগাযোগ / সমন্বয়কারী: {!! $fill($get('coordinator'), 'lg') !!}</td></tr>
        </table>

        <div class="sec"><span class="n">{{ $num(2) }}</span> নির্বাচিত পরামর্শসেবা</div>
        <table class="grid">
            @foreach (collect($sector['services'])->chunk($sector['service_columns']) as $row)
                <tr>
                    @foreach ($row as $key => $label)
                        <td>{!! $box(in_array($key, $services, true), $label) !!}</td>
                    @endforeach
                    @for ($i = $row->count(); $i < $sector['service_columns']; $i++)<td></td>@endfor
                </tr>
            @endforeach
        </table>
        <div class="note" style="border-top:1px solid var(--line);padding-top:4px">নির্বাচিত সেবার চূড়ান্ত পরিধি, মূল্য ও হস্তান্তরযোগ্য কাজ অনুমোদিত প্রস্তাব ও সংযুক্তি অনুযায়ী নির্ধারিত হবে।</div>

        <div class="sec"><span class="n">{{ $num(3) }}</span> অর্পিত কাজের পরিধি</div>
        <div class="lead">কাজ / সমস্যার সারসংক্ষেপ &nbsp; <span style="font-weight:400;color:var(--ink)">{{ $sector['summary_hint'] }}</span></div>
        {!! $text($get('summary')) !!}
        <div class="lead">অন্তর্ভুক্ত কাজ ও সীমানা &nbsp; <span style="font-weight:400">সাইট, কাজ, যন্ত্রপাতি, ব্যক্তি এবং সম্মত কার্যক্রম উল্লেখ করুন।</span></div>
        {!! $text($get('included')) !!}
        <div class="lead">বহির্ভূত কাজ / নির্ভরশীলতা &nbsp; <span style="font-weight:400">অন্তর্ভুক্ত নয় এমন কাজ ও গ্রাহক-সরবরাহকৃত উপকরণ/অনুমোদন লিখুন।</span></div>
        {!! $text($get('excluded')) !!}
    </div>
    {!! $footer(1) !!}
</section>

{{-- ============================================================ পৃষ্ঠা ২ --}}
<section class="sheet">
    <div class="content">
        {!! $header() !!}

        <div class="sec" style="margin-top:4px"><span class="n">{{ $num(4) }}</span> সেবাভিত্তিক প্রয়োজনীয় তথ্য</div>
        <table class="grid">
            @foreach ($sector['info'] as [$label, $parts])
                <tr>
                    <td class="lbl-col">{{ $label }}</td>
                    <td>
                        @foreach ($parts as $part)
                            @if ($part[0] === 'text')
                                {{ $part[1] }}
                            @elseif ($part[0] === 'field')
                                {!! $fill($info[$part[1]] ?? null, $part[2] ?? 'md') !!}
                            @else
                                {!! $box((bool) ($info[$part[1]] ?? false), $part[2]) !!}
                            @endif
                        @endforeach
                    </td>
                </tr>
            @endforeach
        </table>

        <div class="kicker">সম্পাদন ও নিয়ন্ত্রণ</div>
        <div class="lead">কার্যসম্পাদন ও আর্থিক বিবরণ</div>
        <div class="note">ইস্যুর আগে গ্রহণযোগ্যতার মান, তারিখ, মূল্য ও গ্রাহকের দায়িত্ব যাচাই ও পূরণ করুন।</div>

        <div class="sec"><span class="n">{{ $num(5) }}</span> হস্তান্তরযোগ্য কাজ ও গ্রহণযোগ্যতার শর্ত</div>
        <table class="grid">
            <tr class="head-row"><td style="width:24%">হস্তান্তরযোগ্য কাজ</td><td>ন্যূনতম চুক্তিভুক্ত আউটপুট</td><td style="width:32%">গ্রাহকের গ্রহণের ভিত্তি</td></tr>
            @foreach ($sector['deliverables'] as [$what, $output, $basis])
                <tr><td>{{ $what }}</td><td>{{ $output }}</td><td>{{ $basis }}</td></tr>
            @endforeach
        </table>

        <div class="sec"><span class="n">{{ $num(6) }}</span> কাজের ধাপ ও সময়সূচি</div>
        <table class="grid">
            <tr class="head-row"><td style="width:28%">কাজের ধাপ</td><td>যাচাই / সিদ্ধান্তের বিষয়</td><td style="width:24%">লক্ষ্য তারিখ</td></tr>
            @foreach ($sector['stages'] as $i => [$stage, $check])
                <tr><td>{{ $stage }}</td><td>{{ $check }}</td><td class="r">{!! $date($get("stage_dates.{$i}")) !!}</td></tr>
            @endforeach
        </table>
        <div style="margin-top:6px">
            কাজ শুরুর তারিখ: {!! $date($get('start_date')) !!} &nbsp;&nbsp;
            সম্ভাব্য সমাপ্তি: {!! $date($get('end_date')) !!} &nbsp;&nbsp;
            মাধ্যম:
            @foreach (config('work_orders.modes') as $key => $label)
                {!! $box($get('mode') === $key, $label) !!}
            @endforeach
        </div>
    </div>
    {!! $footer(2) !!}
</section>

{{-- ============================================================ পৃষ্ঠা ৩ --}}
<section class="sheet">
    <div class="content">
        {!! $header() !!}

        <div class="sec" style="margin-top:4px"><span class="n">{{ $num(7) }}</span> কাজের মূল্য ও পরিশোধ অনুমোদন</div>
        <table class="grid">
            <tr class="head-row"><td>ফি / বিলযোগ্য খাত</td><td style="width:17%;white-space:nowrap">পরিমাণ / একক</td><td style="width:14%">হার (টাকা)</td><td style="width:15%">মোট (টাকা)</td></tr>
            @foreach (config('work_orders.fee_lines') as $i => $label)
                @php
                    $qty = trim(collect([$get("fees.{$i}.qty"), $get("fees.{$i}.unit")])->filter()->implode(' '));
                @endphp
                <tr>
                    <td>{{ $label }}</td>
                    <td>{!! $fill($qty, 'sm') !!}</td>
                    <td>{!! $money($get("fees.{$i}.rate")) !!}</td>
                    <td>{!! $money($get("fees.{$i}.total")) !!}</td>
                </tr>
            @endforeach
            <tr><td colspan="3">উপমোট</td><td>{!! $money($get('subtotal')) !!}</td></tr>
            <tr><td colspan="3">ভ্যাট / কর / উৎসে কর্তন (প্রযোজ্য ক্ষেত্রে){{ filled($get('tax_amount')) && $get('tax_mode') === 'deduct' ? ' (−)' : '' }}</td><td>{!! $money($get('tax_amount')) !!}</td></tr>
            <tr class="total-row"><td colspan="3">সর্বমোট কার্যাদেশ মূল্য (টাকা)</td><td>{!! $money($grand) !!}</td></tr>
        </table>
        <div style="margin-top:6px">টাকার পরিমাণ কথায় (BDT): {!! $fill($words, 'xxl') !!}</div>

        <div class="sec"><span class="n">{{ $num(8) }}</span> কিস্তিতে পরিশোধ ও বাণিজ্যিক শর্ত</div>
        <table class="grid">
            <tr class="head-row"><td style="width:34%">অর্থপ্রদানের ধাপ</td><td style="width:33%">প্রাপ্য / শতাংশ</td><td>প্রমাণ / অনুমোদন</td></tr>
            @foreach (config('work_orders.payment_steps') as $i => $step)
                <tr>
                    <td>{{ $step['label'] }}</td>
                    <td><span style="white-space:nowrap">{!! $fill($get("installments.{$i}.percent"), 'xs', true) !!} % / টাকা {!! $money($get("installments.{$i}.amount")) !!}</span></td>
                    <td>{{ $step['proof'] }}</td>
                </tr>
            @endforeach
        </table>
        <div class="note">মূল্যপ্রস্তাবের মেয়াদ / বিল পরিশোধের সময়: {!! $fill($get('payment_due'), 'lg') !!} &nbsp;•&nbsp; পরিশোধের মাধ্যম ও প্রাপক অনুমোদিত চালান অনুযায়ী।</div>

        <div class="kicker">প্রশাসন ও অনুমোদন</div>
        <div class="lead">বাস্তবায়নের শর্ত ও গ্রহণ</div>
        <div class="note">নিচের প্রস্তাবিত শর্তগুলো চূড়ান্ত স্বাক্ষরিত মূল্যপ্রস্তাব বা মূল চুক্তির সঙ্গে সামঞ্জস্য রেখে অনুমোদন করতে হবে।</div>

        <div class="sec"><span class="n">{{ $num(9) }}</span> সেবাভিত্তিক বিশেষ শর্ত</div>
        @foreach ($conditions->take(4) as $i => [$title, $body])
            <div class="clause">{{ $num($i + 1) }} <b>{{ $title }}</b> &nbsp;{{ $body }}</div>
        @endforeach
    </div>
    {!! $footer(3) !!}
</section>

{{-- ============================================================ পৃষ্ঠা ৪ --}}
<section class="sheet">
    <div class="content">
        {!! $header() !!}

        @foreach ($conditions->slice(4) as $i => [$title, $body])
            <div class="clause" style="margin-top:4px">{{ $num($i + 1) }} <b>{{ $title }}</b> &nbsp;{{ $body }}</div>
        @endforeach

        <div class="sec"><span class="n">{{ $bn(10) }}</span> সাধারণ প্রশাসন ও বিরোধ নিষ্পত্তি</div>
        @foreach (config('work_orders.general_terms') as [$title, $body])
            <div class="bullet">• <b>{{ $title }}</b> {{ $body }}</div>
        @endforeach

        <div class="sec"><span class="n">{{ $bn(11) }}</span> সংযুক্ত ও রেফারেন্স নথি</div>
        <table class="grid">
            @foreach (collect($sector['attachments'])->chunk(2) as $row)
                <tr>
                    @foreach ($row as $key => $label)
                        <td style="width:50%">{!! $box(in_array($key, $attachments, true), $label) !!}</td>
                    @endforeach
                </tr>
            @endforeach
        </table>
        <div style="margin-top:6px">অন্যান্য সংযুক্তি / পৃষ্ঠাসংখ্যা: {!! $fill($get('other_attachments'), 'xxl') !!}</div>

        <div class="sec"><span class="n">{{ $bn(12) }}</span> আনুষ্ঠানিক গ্রহণ ও অনুমোদন</div>
        <div style="margin-bottom:6px">স্বাক্ষরের মাধ্যমে উভয় পক্ষ এই কার্যাদেশে উল্লিখিত কাজ, হস্তান্তর, সময়সীমা, গ্রহণযোগ্যতার শর্ত ও মূল্য অনুমোদন করছেন; স্বাক্ষরিত চুক্তি ও সংযুক্তি প্রযোজ্য।</div>
        <table class="signs">
            <tr class="head-row"><td>গ্রাহকের পক্ষে / অনুমোদিত স্বাক্ষরকারী</td><td><b style="font-family:'Noto Serif',serif">UGV RICH</b>-এর পক্ষে / অনুমোদিত স্বাক্ষরকারী</td></tr>
            <tr><td>স্বাক্ষর: <span class="blank w-xxl" style="width:190px"></span></td><td>স্বাক্ষর: <span class="blank" style="width:190px"></span></td></tr>
            <tr><td>নাম / পদবি: {!! $fill($get('client_signatory'), 'xl') !!}</td><td>নাম / পদবি: {!! $fill($get('rich_signatory'), 'xl') !!}</td></tr>
            <tr>
                <td>তারিখ: {!! filled($get('client_sign_date')) ? $date($get('client_sign_date')) : '<span class="blank w-md"></span>' !!} &nbsp; সিল: <span class="blank w-md"></span></td>
                <td>তারিখ: {!! filled($get('rich_sign_date')) ? $date($get('rich_sign_date')) : '<span class="blank w-md"></span>' !!} &nbsp; সিল: <span class="blank w-md"></span></td>
            </tr>
        </table>
        <div class="note" style="margin-top:6px">
            অতিরিক্ত প্রযুক্তিগত যাচাই / প্রকল্প ব্যবস্থাপকের অনুমোদন (প্রযোজ্য হলে): {!! $fill($get('tech_approval'), 'xl') !!}
            &nbsp; তারিখ: {!! filled($get('tech_approval_date')) ? $date($get('tech_approval_date')) : '<span class="blank w-md"></span>' !!}
        </div>
        <div class="pre-check">
            ইস্যুর আগে যাচাই &nbsp;
            @foreach (config('work_orders.checklist') as $key => $label)
                {!! $box(in_array($key, $checklist, true), $label) !!}
            @endforeach
        </div>
    </div>
    {!! $footer(4) !!}
</section>

@if (request()->boolean('download'))
    <script>window.addEventListener('load', () => setTimeout(() => window.print(), 400));</script>
@endif
</body>
</html>
