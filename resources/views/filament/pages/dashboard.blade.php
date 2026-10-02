<x-filament-panels::page>
    @php
        $d = $this->getData();

        // Heroicon outline paths, so the dashboard needs no icon package of its own.
        $icons = [
            'briefcase' => 'M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 0 0 .75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 0 0-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0 1 12 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 0 1-.673-.38m0 0A2.18 2.18 0 0 1 3 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 0 1 3.413-.387m7.5 0V5.25A2.25 2.25 0 0 0 13.5 3h-3a2.25 2.25 0 0 0-2.25 2.25v.894m7.5 0a48.667 48.667 0 0 0-7.5 0',
            'light-bulb' => 'M12 18v-5.25m0 0a6.01 6.01 0 0 0 1.5-.189m-1.5.189a6.01 6.01 0 0 1-1.5-.189m3.75 7.478a12.06 12.06 0 0 1-4.5 0m3.75 2.383a14.406 14.406 0 0 1-3 0M14.25 18v-.192c0-.983.658-1.823 1.508-2.316a7.5 7.5 0 1 0-7.517 0c.85.493 1.509 1.333 1.509 2.316V18',
            'cog' => 'M4.5 12a7.5 7.5 0 0 0 15 0m-15 0a7.5 7.5 0 1 1 15 0m-15 0H3m16.5 0H21m-1.5 0H12m-8.457 3.077 1.41-.513m14.095-5.13 1.41-.513M5.106 17.785l1.15-.964m11.49-9.642 1.149-.964M7.501 19.795l.75-1.3m7.5-12.99.75-1.3m-6.063 16.658.26-1.477m2.605-14.772.26-1.477m0 17.726-.26-1.477M10.698 4.614l-.26-1.477M16.5 19.794l-.75-1.299M7.5 4.205 12 12m6.894 5.785-1.149-.964M6.256 7.178l-1.15-.964m15.352 8.864-1.41-.513M4.954 9.435l-1.41-.514M12.002 12l-3.75 6.495',
            'key' => 'M15.75 5.25a3 3 0 0 1 3 3m3 0a6 6 0 0 1-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1 1 21.75 8.25Z',
            'rocket' => 'M15.59 14.37a6 6 0 0 1-5.84 7.38v-4.8m5.84-2.58a14.98 14.98 0 0 0 6.16-12.12A14.98 14.98 0 0 0 9.631 8.41m5.96 5.96a14.926 14.926 0 0 1-5.841 2.58m-.119-8.54a6 6 0 0 0-7.381 5.84h4.8m2.581-5.84a14.927 14.927 0 0 0-2.58 5.84m2.699 2.7c-.103.021-.207.041-.311.06a15.09 15.09 0 0 1-2.448-2.448 14.9 14.9 0 0 1 .06-.312m-2.24 2.39a4.493 4.493 0 0 0-1.757 4.306 4.493 4.493 0 0 0 4.306-1.758M16.5 9a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Z',
            'document-text' => 'M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z',
        ];

        $stagePill = fn (?string $stage) => match ($stage) {
            'idea', 'selected' => 'amber',
            'research' => 'blue',
            'prototype', 'testing' => 'violet',
            'patent' => 'navy',
            'incubation', 'commercialization' => 'green',
            default => 'slate',
        };
    @endphp

    <style>
        .rd { --rd-green:#316d31; --rd-green-soft:#eef6ee; --rd-navy:#022251; --rd-navy-soft:#eaf0f8;
              --rd-amber:#b45309; --rd-amber-soft:#fef3c7; --rd-blue:#1d4ed8; --rd-blue-soft:#e0eaff;
              --rd-violet:#6d28d9; --rd-violet-soft:#ede9fe; --rd-slate:#475569; --rd-slate-soft:#f1f5f9;
              --rd-border:#e5e7eb; --rd-text:#0f172a; --rd-muted:#64748b;
              display:flex; flex-direction:column; gap:20px; color:var(--rd-text); }
        .dark .rd { --rd-border:rgba(255,255,255,.1); --rd-text:#f1f5f9; --rd-muted:#94a3b8; }

        .rd-card { background:#fff; border:1px solid var(--rd-border); border-radius:16px; }
        .dark .rd-card { background:rgb(24 24 27); }
        .rd-card-head { display:flex; align-items:center; justify-content:space-between; gap:12px; padding:16px 20px; border-bottom:1px solid var(--rd-border); }
        .rd-card-head h3 { font-size:14px; font-weight:700; letter-spacing:.01em; }
        .rd-card-head a { font-size:12.5px; font-weight:600; color:var(--rd-green); }
        .rd-card-body { padding:16px 20px; }

        /* KPI tiles */
        .rd-kpis { display:grid; gap:14px; grid-template-columns:repeat(6, minmax(0,1fr)); }
        @media (max-width:1280px) { .rd-kpis { grid-template-columns:repeat(3, minmax(0,1fr)); } }
        @media (max-width:640px)  { .rd-kpis { grid-template-columns:repeat(2, minmax(0,1fr)); } }
        .rd-kpi { display:flex; flex-direction:column; gap:14px; padding:18px; border-radius:16px; background:#fff;
                  border:1px solid var(--rd-border); transition:transform .25s, box-shadow .25s, border-color .25s; }
        .dark .rd-kpi { background:rgb(24 24 27); }
        .rd-kpi:hover { transform:translateY(-2px); border-color:var(--rd-green); box-shadow:0 18px 40px -28px rgba(2,34,81,.5); }
        .rd-kpi-icon { width:40px; height:40px; border-radius:12px; display:flex; align-items:center; justify-content:center; }
        .rd-kpi-icon svg { width:20px; height:20px; }
        .rd-kpi-value { font-size:30px; font-weight:800; line-height:1; font-variant-numeric:tabular-nums; }
        .rd-kpi-label { font-size:12.5px; color:var(--rd-muted); font-weight:600; }

        .rd-tone-green  { background:var(--rd-green-soft);  color:var(--rd-green); }
        .rd-tone-amber  { background:var(--rd-amber-soft);  color:var(--rd-amber); }
        .rd-tone-blue   { background:var(--rd-blue-soft);   color:var(--rd-blue); }
        .rd-tone-violet { background:var(--rd-violet-soft); color:var(--rd-violet); }
        .rd-tone-navy   { background:var(--rd-navy-soft);   color:var(--rd-navy); }
        .rd-tone-slate  { background:var(--rd-slate-soft);  color:var(--rd-slate); }

        /* Layout */
        .rd-main { display:grid; gap:20px; grid-template-columns:minmax(0,.9fr) minmax(0,1.1fr); }
        .rd-four { display:grid; gap:20px; grid-template-columns:repeat(2, minmax(0,1fr)); }
        @media (max-width:1024px) { .rd-main, .rd-four { grid-template-columns:minmax(0,1fr); } }

        /* Pipeline: a vertical chain */
        .rd-pipe { list-style:none; margin:0; padding:0; }
        .rd-pipe li { position:relative; display:grid; grid-template-columns:34px 1fr auto; gap:12px; align-items:center; padding:9px 0; }
        .rd-pipe li:not(:last-child)::after { content:''; position:absolute; left:16px; top:40px; bottom:-10px; width:2px; background:linear-gradient(var(--rd-green-soft), var(--rd-border)); }
        .rd-pipe-dot { width:34px; height:34px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:11px; font-weight:800;
                       background:var(--rd-green-soft); color:var(--rd-green); border:1px solid #d7e9d6; position:relative; z-index:1; }
        .rd-pipe-name { font-size:13.5px; font-weight:700; }
        .rd-pipe-sub { font-size:11.5px; color:var(--rd-muted); }
        .rd-pipe-bar { margin-top:5px; height:5px; border-radius:99px; background:var(--rd-slate-soft); overflow:hidden; }
        .rd-pipe-bar i { display:block; height:100%; border-radius:99px; background:var(--rd-green); }
        .rd-pipe-count { font-size:18px; font-weight:800; font-variant-numeric:tabular-nums; min-width:28px; text-align:right; }

        /* Tables and lists */
        .rd-table { width:100%; border-collapse:collapse; font-size:13px; }
        .rd-table th { text-align:left; font-size:11px; text-transform:uppercase; letter-spacing:.08em; color:var(--rd-muted); font-weight:700; padding:0 0 10px; }
        .rd-table td { padding:11px 0; border-top:1px solid var(--rd-border); vertical-align:middle; }
        .rd-table td:last-child, .rd-table th:last-child { text-align:right; }
        .rd-title { font-weight:700; }
        .rd-dept { display:inline-block; font-size:10.5px; font-weight:800; letter-spacing:.06em; padding:2px 7px; border-radius:6px; background:var(--rd-navy); color:#fff; margin-right:8px; }
        .rd-pill { display:inline-flex; align-items:center; font-size:11.5px; font-weight:700; padding:3px 9px; border-radius:99px; }
        .rd-progress { width:90px; height:5px; border-radius:99px; background:var(--rd-slate-soft); overflow:hidden; display:inline-block; vertical-align:middle; margin-right:8px; }
        .rd-progress i { display:block; height:100%; background:var(--rd-green); }

        .rd-list { list-style:none; margin:0; padding:0; }
        .rd-list li { display:flex; gap:12px; align-items:flex-start; padding:11px 0; border-top:1px solid var(--rd-border); }
        .rd-list li:first-child { border-top:0; padding-top:0; }
        .rd-date { flex:0 0 46px; text-align:center; border-radius:10px; overflow:hidden; border:1px solid var(--rd-border); }
        .rd-date b { display:block; font-size:10px; text-transform:uppercase; letter-spacing:.08em; background:var(--rd-green); color:#fff; padding:2px 0; }
        .rd-date span { display:block; font-size:16px; font-weight:800; padding:3px 0; }
        .rd-item-title { font-size:13px; font-weight:700; line-height:1.35; }
        .rd-item-sub { font-size:12px; color:var(--rd-muted); margin-top:2px; }
        .rd-empty { font-size:13px; color:var(--rd-muted); text-align:center; padding:18px 0; }
    </style>

    <div class="rd">

        {{-- Headline figures --}}
        <div class="rd-kpis">
            @foreach ($d['kpis'] as [$label, $value, $icon, $tone, $href])
                <a href="{{ $href }}" wire:navigate class="rd-kpi">
                    <span class="rd-kpi-icon rd-tone-{{ $tone }}">
                        <svg fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icons[$icon] }}"/></svg>
                    </span>
                    <span>
                        <span class="rd-kpi-value">{{ number_format($value) }}</span>
                        <span class="rd-kpi-label" style="display:block; margin-top:6px">{{ $label }}</span>
                    </span>
                </a>
            @endforeach
        </div>

        @if ($d['access']['pipeline'] || $d['access']['projects'])
        <div class="rd-main">
            {{-- Innovation pipeline --}}
            @if ($d['access']['pipeline'])
            <section class="rd-card">
                <div class="rd-card-head">
                    <h3>Innovation pipeline</h3>
                    <span style="font-size:12px; color:var(--rd-muted)">Ideas to startups</span>
                </div>
                <div class="rd-card-body">
                    <ol class="rd-pipe">
                        @foreach ($d['pipeline'] as $i => [$name, $count, $sub])
                            <li>
                                <span class="rd-pipe-dot">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</span>
                                <span>
                                    <span class="rd-pipe-name">{{ $name }}</span>
                                    <span class="rd-pipe-sub" style="display:block">{{ $sub }}</span>
                                    <span class="rd-pipe-bar"><i style="width: {{ round($count / $d['pipelineMax'] * 100) }}%"></i></span>
                                </span>
                                <span class="rd-pipe-count">{{ $count }}</span>
                            </li>
                        @endforeach
                    </ol>
                </div>
            </section>
            @endif

            {{-- Project status --}}
            @if ($d['access']['projects'])
            <section class="rd-card">
                <div class="rd-card-head">
                    <h3>Project status</h3>
                    <a href="{{ $d['links']['projects'] }}" wire:navigate>All innovation projects →</a>
                </div>
                <div class="rd-card-body">
                    @if ($d['projects']->isEmpty())
                        <p class="rd-empty">No innovation projects yet.</p>
                    @else
                        <table class="rd-table">
                            <thead>
                                <tr><th>Project</th><th>Stage</th><th>Progress</th></tr>
                            </thead>
                            <tbody>
                                @foreach ($d['projects'] as $project)
                                    <tr>
                                        <td>
                                            @if ($project->department)<span class="rd-dept">{{ $project->department }}</span>@endif
                                            @can('update', $project)
                                                <a href="{{ \App\Filament\Resources\Projects\ProjectResource::getUrl('edit', ['record' => $project]) }}" wire:navigate class="rd-title">{{ $project->title }}</a>
                                            @else
                                                <span class="rd-title">{{ $project->title }}</span>
                                            @endcan
                                        </td>
                                        <td>
                                            <span class="rd-pill rd-tone-{{ $stagePill($project->stage) }}">{{ $project->stage_label ?? '—' }}</span>
                                            @if ($project->patent_status !== 'none')
                                                <span class="rd-pill rd-tone-navy" style="margin-left:4px">{{ $project->patent_status_label }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="rd-progress"><i style="width: {{ (int) $project->progress }}%"></i></span>
                                            <span style="font-size:12px; color:var(--rd-muted); font-variant-numeric:tabular-nums">{{ (int) $project->progress }}%</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </section>
            @endif
        </div>
        @endif

        @if ($d['access']['events'] || $d['access']['publications'] || $d['access']['requests'])
        <div class="rd-four">
            {{-- Upcoming events --}}
            @if ($d['access']['events'])
            <section class="rd-card">
                <div class="rd-card-head"><h3>Upcoming events</h3><a href="{{ $d['links']['events'] }}" wire:navigate>Events & training →</a></div>
                <div class="rd-card-body">
                    @forelse ($d['events'] as $event)
                        @if ($loop->first)<ul class="rd-list">@endif
                        <li>
                            <span class="rd-date"><b>{{ $event->event_at?->format('M') }}</b><span>{{ $event->event_at?->format('d') }}</span></span>
                            <span>
                                <span class="rd-item-title" style="display:block">{{ $event->title }}</span>
                                <span class="rd-item-sub" style="display:block">{{ $event->event_at?->format('l, g:i A') }}@if ($event->location) · {{ $event->location }}@endif</span>
                            </span>
                        </li>
                        @if ($loop->last)</ul>@endif
                    @empty
                        <p class="rd-empty">No upcoming events scheduled.</p>
                    @endforelse
                </div>
            </section>
            @endif

            {{-- Recent publications --}}
            @if ($d['access']['publications'])
            <section class="rd-card">
                <div class="rd-card-head"><h3>Recent publications</h3><a href="{{ $d['links']['publications'] }}" wire:navigate>All publications →</a></div>
                <div class="rd-card-body">
                    @forelse ($d['publications'] as $item)
                        @if ($loop->first)<ul class="rd-list">@endif
                        <li>
                            <span class="rd-pill rd-tone-blue" style="flex:0 0 auto">{{ $item->year ?: '—' }}</span>
                            <span>
                                <span class="rd-item-title" style="display:block">{{ $item->title }}</span>
                                <span class="rd-item-sub" style="display:block">{{ config('rich.publication_kinds.'.$item->kind, 'Publication') }}@if ($item->venue) · {{ $item->venue }}@endif</span>
                            </span>
                        </li>
                        @if ($loop->last)</ul>@endif
                    @empty
                        <p class="rd-empty">No publications recorded yet.</p>
                    @endforelse
                </div>
            </section>

            {{-- Funding --}}
            <section class="rd-card">
                <div class="rd-card-head"><h3>Funding & grants</h3><a href="{{ $d['links']['funding'] }}" wire:navigate>All funding →</a></div>
                <div class="rd-card-body">
                    @forelse ($d['funding'] as $grant)
                        @if ($loop->first)<ul class="rd-list">@endif
                        <li>
                            <span class="rd-pill rd-tone-green" style="flex:0 0 auto">{{ $grant->year ?: '—' }}</span>
                            <span>
                                <span class="rd-item-title" style="display:block">{{ $grant->title }}</span>
                                <span class="rd-item-sub" style="display:block">{{ $grant->venue }}@if ($grant->authors) · {{ $grant->authors }}@endif</span>
                            </span>
                        </li>
                        @if ($loop->last)</ul>@endif
                    @empty
                        <p class="rd-empty">No funding recorded yet.</p>
                    @endforelse
                </div>
            </section>
            @endif

            {{-- Industry requests --}}
            @if ($d['access']['requests'])
            <section class="rd-card">
                <div class="rd-card-head"><h3>Industry requests</h3><a href="{{ $d['links']['requests'] }}" wire:navigate>All requests →</a></div>
                <div class="rd-card-body">
                    @forelse ($d['requests'] as $request)
                        @if ($loop->first)<ul class="rd-list">@endif
                        <li>
                            <span class="rd-pill rd-tone-{{ $request->status === 'new' ? 'amber' : 'slate' }}" style="flex:0 0 auto">{{ str($request->status)->replace('_', ' ')->title() }}</span>
                            <span style="min-width:0">
                                <span class="rd-item-title" style="display:block">{{ $request->organization ?: $request->name }}</span>
                                <span class="rd-item-sub" style="display:block">{{ $request->category?->name ?? $request->area_of_interest ?? 'Area to be matched' }} · {{ $request->created_at?->diffForHumans() }}</span>
                            </span>
                        </li>
                        @if ($loop->last)</ul>@endif
                    @empty
                        <p class="rd-empty">No industry requests yet.</p>
                    @endforelse
                </div>
            </section>
            @endif
        </div>
        @endif
    </div>
</x-filament-panels::page>
