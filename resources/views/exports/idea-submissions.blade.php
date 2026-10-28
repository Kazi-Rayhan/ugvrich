@php
    $site = app(\App\Support\Site::class);

    $statuses = [
        'new' => ['New', '#b45309', '#fef3c7'],
        'in_review' => ['In review', '#1d4ed8', '#dbeafe'],
        'accepted' => ['Accepted', '#047857', '#d1fae5'],
        'on_hold' => ['On hold', '#475569', '#e2e8f0'],
        'declined' => ['Declined', '#b91c1c', '#fee2e2'],
    ];

    $label = fn (string $list, ?string $key) => $key ? config("rich.{$list}.{$key}", $key) : null;
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Idea submissions · {{ now()->format('j M Y') }} · {{ $site->name() }}</title>
    <style>
        :root { --ink:#0f172a; --muted:#64748b; --line:#e5e9f0; --brand:#0b7a4b; --brand-soft:#e7f5ee; --navy:#0b2545; --zebra:#f8fafc; }
        * { box-sizing:border-box; }
        body { margin:0; background:#eef2f6; color:var(--ink); font:12.5px/1.5 "Inter","Segoe UI",Arial,sans-serif; -webkit-print-color-adjust:exact; print-color-adjust:exact; }

        .toolbar { max-width:1180px; margin:20px auto 0; display:flex; justify-content:space-between; align-items:center; gap:12px; color:var(--muted); }
        .toolbar button { border:0; border-radius:10px; padding:10px 18px; font-weight:600; font-size:13px; cursor:pointer; background:var(--brand); color:#fff; box-shadow:0 8px 20px -10px rgba(11,122,75,.7); }

        .page { max-width:1180px; margin:16px auto 32px; background:#fff; border-radius:14px; overflow:hidden; box-shadow:0 20px 50px -30px rgba(15,23,42,.45); }

        .head { display:flex; justify-content:space-between; align-items:center; gap:24px; padding:26px 32px; background:linear-gradient(120deg, var(--navy) 0%, #0e3a5f 55%, var(--brand) 140%); color:#fff; }
        .head .brand { display:flex; align-items:center; gap:14px; }
        .head .logo { width:52px; height:52px; border-radius:14px; background:#fff; padding:6px; display:flex; align-items:center; justify-content:center; }
        .head .logo img { width:100%; height:100%; object-fit:contain; }
        .head h1 { margin:0; font-size:21px; letter-spacing:-.01em; }
        .head .sub { margin-top:2px; font-size:12px; color:rgba(255,255,255,.7); }
        .head .meta { text-align:right; font-size:12px; color:rgba(255,255,255,.75); }
        .head .meta strong { display:block; color:#fff; font-size:13px; }

        .scope { padding:10px 32px; font-size:12px; color:var(--muted); border-bottom:1px solid var(--line); }
        .scope b { color:var(--ink); }

        .wrap { padding:4px 20px 20px; }
        table { width:100%; border-collapse:separate; border-spacing:0; }
        thead th { position:sticky; top:0; text-align:left; font-size:10.5px; font-weight:700; text-transform:uppercase; letter-spacing:.07em; color:var(--muted); background:#fff; padding:14px 12px 10px; border-bottom:2px solid var(--ink); white-space:nowrap; }
        tbody td { padding:11px 12px; border-bottom:1px solid var(--line); vertical-align:top; }
        tbody tr:nth-child(even) td { background:var(--zebra); }
        tbody tr { page-break-inside:avoid; break-inside:avoid; }
        .num { color:var(--muted); font-variant-numeric:tabular-nums; width:28px; }
        .ref { font-weight:700; font-family:ui-monospace,"Cascadia Mono",Consolas,monospace; font-size:11.5px; white-space:nowrap; }
        .date { white-space:nowrap; }
        .date small, .muted { color:var(--muted); font-size:11px; display:block; }
        .title { font-weight:700; font-size:13px; line-height:1.35; }
        .problem { color:var(--muted); font-size:11.5px; margin-top:3px; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; }
        .who { display:flex; gap:10px; align-items:flex-start; min-width:220px; }
        .who-name { font-weight:700; font-size:13px; line-height:1.3; }
        .who-team { display:inline-block; margin-top:3px; padding:1px 8px; border-radius:999px; background:var(--brand-soft); color:var(--brand); font-size:10.5px; font-weight:600; }
        .contact { margin-top:5px; display:grid; gap:3px; }
        .contact span { display:flex; align-items:center; gap:6px; font-size:11.5px; color:#334155; word-break:break-all; }
        .contact svg { flex:none; width:12px; height:12px; color:var(--muted); }
        .chip { display:inline-block; padding:2px 9px; border-radius:999px; font-size:11px; font-weight:600; background:#eef2f7; color:#334155; white-space:nowrap; margin:0 3px 3px 0; }
        .chip.cat { background:var(--brand-soft); color:var(--brand); }
        .status { display:inline-flex; align-items:center; gap:6px; padding:3px 10px; border-radius:999px; font-size:11px; font-weight:700; white-space:nowrap; }
        .status::before { content:""; width:6px; height:6px; border-radius:50%; background:currentColor; }
        .empty { padding:48px; text-align:center; color:var(--muted); }

        .foot { display:flex; justify-content:space-between; padding:14px 32px; border-top:1px solid var(--line); color:var(--muted); font-size:11px; }

        @media print {
            @page { size:A4 landscape; margin:10mm; }
            body { background:#fff; }
            .toolbar { display:none; }
            .page { margin:0; max-width:none; border-radius:0; box-shadow:none; }
            thead { display:table-header-group; }
            .head { border-radius:10px; }
        }
    </style>
</head>
<body>
<div class="toolbar">
    <span>Tip: choose <b>“Save as PDF”</b> as the printer, landscape.</span>
    <button type="button" onclick="window.print()">Download PDF</button>
</div>

<div class="page">
    <div class="head">
        <div class="brand">
            <span class="logo"><img src="{{ asset('media/logo-mark.png') }}" alt=""></span>
            <div>
                <h1>Idea Submissions Report</h1>
                <div class="sub">{{ $site->name() }} · Innovation Wing</div>
            </div>
        </div>
        <div class="meta">
            <strong>{{ now()->format('j F Y') }}</strong>
            Generated {{ now()->format('g:i A') }} by {{ auth()->user()?->name }}
        </div>
    </div>

    <div class="scope">Showing: <b>{{ $scope }}</b></div>

    <div class="wrap">
        @if ($ideas->isEmpty())
            <div class="empty">No idea submissions match this export.</div>
        @else
            <table>
                <thead>
                <tr>
                    <th>#</th>
                    <th>Reference</th>
                    <th>Received</th>
                    <th style="width:38%">Idea</th>
                    <th>Submitted by</th>
                    <th>Category</th>
                    <th>Status</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($ideas as $idea)
                    @php
                        [$statusName, $statusColor, $statusBg] = $statuses[$idea->status] ?? [str($idea->status)->replace('_', ' ')->title(), '#475569', '#e2e8f0'];
                    @endphp
                    <tr>
                        <td class="num">{{ $loop->iteration }}</td>
                        <td class="ref">{{ $idea->reference }}</td>
                        <td class="date">{{ $idea->created_at?->format('j M Y') }}<small>{{ $idea->created_at?->format('g:i A') }}</small></td>
                        <td>
                            <div class="title">{{ $idea->title }}</div>
                            @if ($idea->problem)<div class="problem">{{ $idea->problem }}</div>@endif
                        </td>
                        <td>
                            {{-- Who sent it: name, then each way to reach them on its own line. --}}
                            <div class="who">
                                <div class="who-body">
                                    <div class="who-name">{{ $idea->name }}</div>
                                    @if ($idea->team_size)<div class="who-team">Team · {{ $idea->team_size }}</div>@endif
                                    <div class="contact">
                                        @if ($idea->phone)
                                            <span><svg viewBox="0 0 20 20" fill="currentColor"><path d="M2 3.5A1.5 1.5 0 0 1 3.5 2h1.15a1.5 1.5 0 0 1 1.46 1.15l.6 2.42a1.5 1.5 0 0 1-.6 1.6l-.94.66a10.5 10.5 0 0 0 5 5l.66-.94a1.5 1.5 0 0 1 1.6-.6l2.42.6A1.5 1.5 0 0 1 18 15.35v1.15A1.5 1.5 0 0 1 16.5 18H15C7.82 18 2 12.18 2 5V3.5Z"/></svg>{{ $idea->phone }}</span>
                                        @endif
                                        @if ($idea->email)
                                            <span><svg viewBox="0 0 20 20" fill="currentColor"><path d="M3 4a2 2 0 0 0-2 2v.4l9 5.1 9-5.1V6a2 2 0 0 0-2-2H3Z"/><path d="M19 8.7l-8.5 4.8a1 1 0 0 1-1 0L1 8.7V14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V8.7Z"/></svg>{{ $idea->email }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td>@if ($idea->category)<span class="chip cat">{{ $label('idea_categories', $idea->category) }}</span>@else<span class="muted">—</span>@endif</td>
                        <td><span class="status" style="color:{{ $statusColor }};background:{{ $statusBg }}">{{ $statusName }}</span></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <div class="foot">
        <span>{{ $site->name() }} · Confidential: for internal use</span>
        <span>{{ $ideas->count() }} {{ \Illuminate\Support\Str::plural('record', $ideas->count()) }}</span>
    </div>
</div>
</body>
</html>
