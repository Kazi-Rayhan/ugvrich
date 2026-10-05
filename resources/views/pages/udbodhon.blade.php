{{--
 | The Innovation Wing's inauguration — /udbodhon.
 |
 | A page of its own, outside the site layout: a closed stage with a ribbon
 | across it and one button. Pressing it rolls the drums, cuts the ribbon,
 | opens the curtains and lets the celebration loose — paper confetti,
 | flower petals, marigolds, fireworks, balloons and music.
 |
 | Everything is drawn and played in the browser: the confetti on a canvas,
 | the music with the Web Audio API, so the page needs no asset files and no
 | front-end build. To use a recorded track instead of the generated tune,
 | drop it at public/media/udbodhon.mp3 — the page picks it up on its own.
--}}
@php
    $track = file_exists(public_path('media/udbodhon.mp3')) ? asset('media/udbodhon.mp3') : null;
    $words = fn (string $text) => preg_split('/\s+/u', trim($text));
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#3a0710">
    <title>{{ __('udbodhon.meta_title') }} — {{ $site->name() }}</title>
    <meta name="description" content="{{ __('udbodhon.meta_description') }}">
    <meta property="og:title" content="{{ __('udbodhon.meta_title') }} — {{ __('udbodhon.title') }}">
    <meta property="og:description" content="{{ __('udbodhon.meta_description') }}">
    <link rel="icon" href="{{ asset('favicon.png') }}" type="image/png">

    {{ Vite::fonts(['plus-jakarta-sans', 'noto-sans-bengali']) }}

    <style>
        :root {
            --velvet-dark: #4a0610;
            --velvet: #8e0f1f;
            --velvet-light: #c21a32;
            --gold: #f2c14e;
            --gold-light: #ffe8a3;
            --gold-dark: #c98a12;
            --brand: #41843f;
            --brand-dark: #275727;
            --night: #021634;
            --font: 'Plus Jakarta Sans', 'Noto Sans Bengali', system-ui, sans-serif;
        }

        * { box-sizing: border-box; margin: 0; }
        html, body { height: 100%; }
        body {
            overflow: hidden;
            background: var(--night);
            color: #fff;
            font-family: var(--font);
            -webkit-font-smoothing: antialiased;
        }
        button { font: inherit; cursor: pointer; }

        .stage { position: fixed; inset: 0; overflow: hidden; }

        /* ---------- Behind the curtains: the opened stage ---------- */

        .backdrop {
            position: absolute; inset: 0;
            background:
                radial-gradient(ellipse 60% 55% at 50% 42%, rgba(242, 193, 78, .22), transparent 70%),
                radial-gradient(ellipse 90% 70% at 50% 110%, rgba(65, 132, 63, .35), transparent 70%),
                linear-gradient(180deg, #03204a 0%, var(--night) 70%);
        }
        .rays {
            position: absolute; left: 50%; top: 42%;
            width: 180vmax; height: 180vmax; margin: -90vmax 0 0 -90vmax;
            background: repeating-conic-gradient(from 0deg, rgba(255, 232, 163, .10) 0deg 6deg, transparent 6deg 18deg);
            -webkit-mask: radial-gradient(circle, #000 0, transparent 55%);
                    mask: radial-gradient(circle, #000 0, transparent 55%);
            animation: spin 60s linear infinite;
            opacity: 0; transition: opacity 2s ease 1s;
        }
        [data-state="open"] .rays { opacity: 1; }

        .reveal {
            position: absolute; inset: 0; z-index: 3;
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            padding: 12vh 24px 8vh; text-align: center;
            visibility: hidden;
        }
        [data-state="open"] .reveal { visibility: visible; }
        .reveal > * { opacity: 0; transform: translateY(28px) scale(.97); }
        [data-state="open"] .reveal > * { animation: rise 1s cubic-bezier(.2, .8, .2, 1) forwards; animation-delay: var(--d, 0s); }

        .bulb {
            width: clamp(64px, 11vmin, 104px); height: auto;
            filter: drop-shadow(0 0 22px rgba(255, 220, 120, .85)) drop-shadow(0 0 60px rgba(255, 200, 80, .5));
        }
        [data-state="open"] .bulb { animation: rise 1s cubic-bezier(.2, .8, .2, 1) forwards, glow 2.4s ease-in-out 1.8s infinite; animation-delay: var(--d), calc(var(--d) + 1s); }

        .opened-eyebrow {
            margin-top: 18px;
            display: inline-flex; align-items: center; gap: 10px;
            padding: 8px 18px; border-radius: 999px;
            background: rgba(255, 255, 255, .08); border: 1px solid rgba(255, 232, 163, .35);
            color: var(--gold-light); font-size: 14px; font-weight: 700; letter-spacing: .12em; text-transform: uppercase;
        }
        .opened-title {
            margin-top: 18px;
            font-size: clamp(44px, 9.5vw, 128px); font-weight: 800; line-height: 1.05; letter-spacing: -.02em;
            display: flex; flex-wrap: wrap; justify-content: center; gap: 0 .28em;
        }
        /* Animated word by word, never letter by letter: Bangla conjuncts
           would fall apart if the letters were split. */
        .opened-title .word {
            display: inline-block; padding: .06em 0;
            background: linear-gradient(100deg, #fff6d8 10%, var(--gold) 35%, #fff 50%, var(--gold) 65%, #fff6d8 90%);
            background-size: 220% 100%;
            -webkit-background-clip: text; background-clip: text; color: transparent;
            filter: drop-shadow(0 6px 24px rgba(242, 193, 78, .35));
            opacity: 0; transform: translateY(.5em) rotateX(70deg);
        }
        [data-state="open"] .opened-title { opacity: 1; transform: none; }
        [data-state="open"] .opened-title .word {
            animation: word-in .9s cubic-bezier(.2, .9, .25, 1.2) forwards, shine 5s linear 2.5s infinite;
            animation-delay: var(--wd), calc(var(--wd) + 1s);
        }
        .opened-lead {
            margin-top: 20px; max-width: 46rem;
            font-size: clamp(16px, 2.1vw, 21px); line-height: 1.75; color: rgba(255, 255, 255, .82);
        }
        .actions { margin-top: 34px; display: flex; flex-wrap: wrap; gap: 14px; justify-content: center; }
        .btn-enter, .btn-ghost {
            display: inline-flex; align-items: center; gap: 10px;
            padding: 15px 28px; border-radius: 999px;
            font-size: 16px; font-weight: 700; text-decoration: none;
            transition: transform .25s ease, box-shadow .25s ease, background .25s ease;
        }
        .btn-enter {
            color: #fff; background: linear-gradient(180deg, #5d9e59, var(--brand) 55%, var(--brand-dark));
            box-shadow: 0 0 0 3px rgba(143, 191, 139, .3), 0 18px 40px -14px rgba(65, 132, 63, .9);
        }
        .btn-enter:hover { transform: translateY(-2px); box-shadow: 0 0 0 5px rgba(143, 191, 139, .35), 0 24px 46px -14px rgba(65, 132, 63, 1); }
        .btn-ghost { color: #fff; background: rgba(255, 255, 255, .08); border: 1px solid rgba(255, 255, 255, .25); }
        .btn-ghost:hover { background: rgba(255, 255, 255, .16); }
        .btn-enter svg, .btn-ghost svg { width: 18px; height: 18px; }

        /* ---------- Balloons ---------- */

        .balloons { position: absolute; inset: 0; z-index: 2; pointer-events: none; }
        .balloon {
            position: absolute; bottom: -160px; left: var(--x);
            width: var(--w); height: calc(var(--w) * 1.22);
            border-radius: 50% 50% 48% 48% / 55% 55% 45% 45%;
            background: radial-gradient(circle at 32% 28%, rgba(255, 255, 255, .75) 0 6%, transparent 22%), var(--c);
            box-shadow: inset -8px -12px 22px rgba(0, 0, 0, .25);
            opacity: 0;
        }
        .balloon::before { /* knot */
            content: ''; position: absolute; left: 50%; bottom: -7px; margin-left: -6px;
            border: 6px solid transparent; border-bottom: 8px solid var(--c); transform: rotate(180deg);
        }
        .balloon::after { /* string */
            content: ''; position: absolute; left: 50%; top: 100%; width: 1px; height: 120px; margin-top: 6px;
            background: linear-gradient(rgba(255, 255, 255, .6), transparent);
        }
        [data-state="open"] .balloon { animation: float-up var(--t) linear var(--delay) infinite; }

        /* ---------- The curtains ---------- */

        .curtain {
            position: absolute; top: 0; bottom: 0; z-index: 5; width: 50.5vw;
            background:
                linear-gradient(180deg, rgba(0, 0, 0, .45), transparent 22%, transparent 78%, rgba(0, 0, 0, .55)),
                repeating-linear-gradient(90deg,
                    var(--velvet-dark) 0, var(--velvet) 1.6vw, var(--velvet-light) 2.7vw,
                    #d8344b 3.1vw, var(--velvet-light) 3.5vw, var(--velvet) 4.7vw, var(--velvet-dark) 6.4vw);
            transition: transform 3s cubic-bezier(.66, 0, .25, 1);
            will-change: transform;
        }
        .curtain::after { /* the sheen of velvet, and the shadow at the meeting edge */
            content: ''; position: absolute; inset: 0;
            background: radial-gradient(ellipse 70% 40% at 50% 35%, rgba(255, 140, 150, .12), transparent 70%);
        }
        .curtain-left { left: 0; transform-origin: 0 50%; box-shadow: inset -30px 0 40px -20px rgba(0, 0, 0, .7); }
        .curtain-right { right: 0; transform-origin: 100% 50%; box-shadow: inset 30px 0 40px -20px rgba(0, 0, 0, .7); }
        [data-state="rolling"] .curtain-left { animation: shiver-l 1.4s ease-in-out; }
        [data-state="rolling"] .curtain-right { animation: shiver-r 1.4s ease-in-out; }
        [data-state="open"] .curtain-left { transform: scaleX(.13) skewY(-1deg); }
        [data-state="open"] .curtain-right { transform: scaleX(.13) skewY(1deg); }

        .valance {
            position: absolute; top: 0; left: 0; right: 0; z-index: 7;
            height: clamp(60px, 12vh, 120px);
            background:
                linear-gradient(180deg, rgba(0, 0, 0, .35), transparent 60%),
                repeating-linear-gradient(90deg, #6d0a18 0, #a8152b 2.5vw, #c92340 3.2vw, #a8152b 3.9vw, #6d0a18 6.4vw);
            -webkit-mask:
                linear-gradient(#000 0 0) top / 100% calc(100% - 22px) no-repeat,
                radial-gradient(circle 23px at 50% 0, #000 96%, transparent 100%) bottom / 46px 22px repeat-x;
                    mask:
                linear-gradient(#000 0 0) top / 100% calc(100% - 22px) no-repeat,
                radial-gradient(circle 23px at 50% 0, #000 96%, transparent 100%) bottom / 46px 22px repeat-x;
            filter: drop-shadow(0 10px 18px rgba(0, 0, 0, .55));
        }
        .valance::after { /* gold braid */
            content: ''; position: absolute; left: 0; right: 0; bottom: 22px; height: 6px;
            background: repeating-linear-gradient(90deg, var(--gold-dark) 0 4px, var(--gold-light) 4px 7px, var(--gold) 7px 12px);
        }

        /* ---------- The closed stage: title, ribbon, button ---------- */

        .intro {
            position: absolute; inset: 0; z-index: 8;
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            padding: 14vh 24px 8vh; text-align: center;
            transition: opacity .6s ease;
        }
        [data-state="cut"] .intro .text, [data-state="open"] .intro .text { opacity: 0; transform: translateY(-20px); }
        .intro .text { transition: opacity .6s ease, transform .6s ease; }
        [data-state="open"] .intro { opacity: 0; pointer-events: none; transition-delay: 1.2s; }

        .logo {
            display: inline-flex; width: clamp(70px, 10vmin, 96px); aspect-ratio: 1;
            padding: 10px; border-radius: 22px; background: #fff;
            box-shadow: 0 0 0 4px rgba(242, 193, 78, .55), 0 20px 40px -14px rgba(0, 0, 0, .7);
        }
        .logo img { width: 100%; height: 100%; object-fit: contain; }
        .eyebrow {
            margin-top: 22px; color: var(--gold-light);
            font-size: 14px; font-weight: 700; letter-spacing: .22em; text-transform: uppercase;
            text-shadow: 0 2px 10px rgba(0, 0, 0, .6);
        }
        .title {
            margin-top: 10px; font-size: clamp(38px, 7.5vw, 92px); font-weight: 800; line-height: 1.08;
            text-shadow: 0 4px 28px rgba(0, 0, 0, .65);
        }
        .lead { margin-top: 14px; font-size: clamp(15px, 2vw, 19px); color: rgba(255, 255, 255, .85); text-shadow: 0 2px 12px rgba(0, 0, 0, .7); }

        .cut { position: relative; margin-top: 42px; }
        .ribbon {
            position: absolute; top: 50%; height: 34px; margin-top: -17px; width: 50vw;
            background: linear-gradient(180deg,
                var(--gold) 0 3px, #2c6a2b 3px, #4f9a4c 40%, #3a7f37 60%, #22501f calc(100% - 3px), var(--gold) calc(100% - 3px));
            box-shadow: 0 10px 22px -8px rgba(0, 0, 0, .7);
            transition: transform 1.3s cubic-bezier(.45, 0, .2, 1.25), opacity .8s ease 1.4s;
        }
        .ribbon-left { right: 50%; transform-origin: 0 50%; }
        .ribbon-right { left: 50%; transform-origin: 100% 50%; }
        /* Cut: each half swings down from where it is tied, at the edge of the screen. */
        [data-state="cut"] .ribbon-left, [data-state="open"] .ribbon-left { transform: rotate(78deg); }
        [data-state="cut"] .ribbon-right, [data-state="open"] .ribbon-right { transform: rotate(-78deg); }
        [data-state="open"] .ribbon { opacity: 0; }

        .btn-cut {
            position: relative; z-index: 1;
            display: inline-flex; align-items: center; gap: 12px;
            padding: 20px 42px; border: 0; border-radius: 999px;
            font-size: clamp(19px, 2.4vw, 24px); font-weight: 800; color: #3b2600;
            background: linear-gradient(180deg, var(--gold-light), var(--gold) 45%, var(--gold-dark));
            box-shadow: 0 0 0 5px rgba(255, 232, 163, .35), 0 22px 44px -12px rgba(0, 0, 0, .75), inset 0 2px 0 rgba(255, 255, 255, .7);
            transition: transform .25s ease, box-shadow .25s ease, opacity .5s ease;
        }
        .btn-cut::before { /* the pulse that says "press me" */
            content: ''; position: absolute; inset: -5px; border-radius: inherit;
            border: 2px solid var(--gold-light); animation: pulse 2s ease-out infinite;
        }
        .btn-cut:hover { transform: translateY(-3px) scale(1.03); }
        .btn-cut:focus-visible { outline: 3px solid #fff; outline-offset: 6px; }
        .btn-cut svg { width: 26px; height: 26px; }
        [data-state="rolling"] .btn-cut { animation: drum 0.12s linear infinite; }
        [data-state="rolling"] .btn-cut svg { animation: snip .3s ease-in-out infinite alternate; }
        [data-state="rolling"] .btn-cut::before, [data-state="cut"] .btn-cut::before, [data-state="open"] .btn-cut::before { display: none; }
        [data-state="cut"] .btn-cut, [data-state="open"] .btn-cut { transform: scale(.4); opacity: 0; pointer-events: none; }
        .hint { margin-top: 24px; display: inline-flex; align-items: center; gap: 8px; font-size: 13px; color: rgba(255, 255, 255, .65); }
        .hint svg { width: 16px; height: 16px; }

        /* ---------- Confetti canvas and controls ---------- */

        #fx { position: absolute; inset: 0; z-index: 9; pointer-events: none; }

        .controls {
            position: absolute; right: 18px; bottom: 18px; z-index: 10;
            display: flex; gap: 10px;
        }
        .ctl {
            display: inline-flex; align-items: center; justify-content: center;
            width: 46px; height: 46px; border-radius: 50%;
            color: #fff; background: rgba(0, 0, 0, .35); border: 1px solid rgba(255, 255, 255, .25);
            backdrop-filter: blur(6px); transition: background .2s ease;
        }
        .ctl:hover { background: rgba(0, 0, 0, .55); }
        .ctl svg { width: 20px; height: 20px; }
        .ctl[hidden] { display: none; }
        .ctl .off { display: none; }
        .ctl[aria-pressed="true"] .on { display: none; }
        .ctl[aria-pressed="true"] .off { display: block; }

        .sr-only { position: absolute; width: 1px; height: 1px; overflow: hidden; clip: rect(0 0 0 0); white-space: nowrap; }

        /* ---------- Keyframes ---------- */

        @keyframes spin { to { transform: rotate(360deg); } }
        @keyframes rise { to { opacity: 1; transform: none; } }
        @keyframes word-in { to { opacity: 1; transform: none; } }
        @keyframes shine { to { background-position: -220% 0; } }
        @keyframes glow { 50% { filter: drop-shadow(0 0 34px rgba(255, 230, 140, 1)) drop-shadow(0 0 90px rgba(255, 200, 80, .7)); } }
        @keyframes pulse { from { opacity: .9; transform: scale(1); } to { opacity: 0; transform: scale(1.35, 1.6); } }
        @keyframes drum { 25% { transform: translate(-1px, 1px) rotate(-.6deg); } 75% { transform: translate(1px, -1px) rotate(.6deg); } }
        @keyframes snip { to { transform: rotate(-22deg); } }
        @keyframes shiver-l { 30%, 70% { transform: scaleX(.985); } }
        @keyframes shiver-r { 30%, 70% { transform: scaleX(.985); } }
        @keyframes float-up {
            0% { opacity: 0; transform: translate(0, 0) rotate(-4deg); }
            8% { opacity: .95; }
            50% { transform: translate(var(--sway), -65vh) rotate(5deg); }
            92% { opacity: .95; }
            100% { opacity: 0; transform: translate(0, -125vh) rotate(-4deg); }
        }

        @media (prefers-reduced-motion: reduce) {
            .curtain, .ribbon { transition-duration: .8s; }
            .rays, .btn-cut::before { animation: none; }
            [data-state="open"] .balloon { animation-duration: calc(var(--t) * 2); }
        }
    </style>
</head>

<body>
<main class="stage" id="stage" data-state="closed">

    {{-- Behind the curtains --}}
    <div class="backdrop" aria-hidden="true"></div>
    <div class="rays" aria-hidden="true"></div>

    <section class="reveal" aria-live="polite">
        <svg class="bulb" style="--d: .2s" viewBox="0 0 64 80" aria-hidden="true">
            <defs>
                <radialGradient id="bulb-glass" cx="40%" cy="35%" r="70%">
                    <stop offset="0" stop-color="#fffbe6"/>
                    <stop offset=".55" stop-color="#ffe38a"/>
                    <stop offset="1" stop-color="#f2b32e"/>
                </radialGradient>
            </defs>
            <path d="M32 3C17.6 3 7 14 7 27.5c0 9.4 5 15.6 9.6 20.3 2.8 2.9 4.4 6 4.4 9.2V60h22v-3c0-3.2 1.6-6.3 4.4-9.2C52 43.1 57 36.9 57 27.5 57 14 46.4 3 32 3Z" fill="url(#bulb-glass)"/>
            <path d="M24 44c2-6 4-10 8-14m0 0c4 4 6 8 8 14M32 30v-6" stroke="#c98a12" stroke-width="2.4" fill="none" stroke-linecap="round"/>
            <rect x="21" y="61" width="22" height="5" rx="2.5" fill="#c9ced6"/>
            <rect x="22" y="67" width="20" height="5" rx="2.5" fill="#aab1bc"/>
            <path d="M26 73h12l-3 5h-6Z" fill="#8a929e"/>
        </svg>

        <p class="opened-eyebrow" style="--d: .5s">✦ {{ __('udbodhon.opened_eyebrow') }} ✦</p>

        <h1 class="opened-title" style="--d: .7s">
            @foreach ($words(__('udbodhon.opened_title')) as $i => $word)
                <span class="word" style="--wd: {{ .8 + $i * .22 }}s">{{ $word }}</span>
            @endforeach
        </h1>

        <p class="opened-lead" style="--d: 1.5s">{{ __('udbodhon.opened_lead') }}</p>

        <div class="actions" style="--d: 1.9s">
            <a href="{{ route('innovation.index') }}" class="btn-enter">
                {{ __('udbodhon.enter') }}
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
            </a>
            <button type="button" class="btn-ghost" data-replay>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 12a9 9 0 1 0 3-6.7L3 8"/><path d="M3 3v5h5"/></svg>
                {{ __('udbodhon.replay') }}
            </button>
        </div>
    </section>

    <div class="balloons" id="balloons" aria-hidden="true"></div>

    {{-- The curtains --}}
    <div class="curtain curtain-left" aria-hidden="true"></div>
    <div class="curtain curtain-right" aria-hidden="true"></div>
    <div class="valance" aria-hidden="true"></div>

    {{-- The closed stage --}}
    <section class="intro">
        <div class="text">
            <span class="logo"><img src="{{ asset('media/logo-mark.png') }}" alt="{{ $site->name() }}" width="256" height="249"></span>
            <p class="eyebrow">{{ __('udbodhon.eyebrow') }}</p>
            <p class="title">{{ __('udbodhon.title') }}</p>
            <p class="lead">{{ __('udbodhon.lead') }}</p>
        </div>

        <div class="cut">
            <span class="ribbon ribbon-left" aria-hidden="true"></span>
            <span class="ribbon ribbon-right" aria-hidden="true"></span>
            <button type="button" class="btn-cut" id="inaugurate">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <circle cx="6" cy="6" r="3"/><circle cx="6" cy="18" r="3"/><path d="M20 4 8.12 15.88M14.47 14.48 20 20M8.12 8.12 12 12"/>
                </svg>
                {{ __('udbodhon.button') }}
            </button>
        </div>

        <p class="hint text">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M11 5 6 9H2v6h4l5 4V5Z"/><path d="M15.5 8.5a5 5 0 0 1 0 7M19 5a10 10 0 0 1 0 14"/></svg>
            {{ __('udbodhon.sound_hint') }}
        </p>
    </section>

    <canvas id="fx" aria-hidden="true"></canvas>

    <div class="controls">
        <button type="button" class="ctl" id="mute" aria-pressed="false" aria-label="{{ __('udbodhon.mute') }}"
                data-label-mute="{{ __('udbodhon.mute') }}" data-label-unmute="{{ __('udbodhon.unmute') }}" hidden>
            <svg class="on" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M11 5 6 9H2v6h4l5 4V5Z"/><path d="M15.5 8.5a5 5 0 0 1 0 7M19 5a10 10 0 0 1 0 14"/></svg>
            <svg class="off" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M11 5 6 9H2v6h4l5 4V5Z"/><path d="m22 9-6 6M16 9l6 6"/></svg>
        </button>
        <button type="button" class="ctl" data-replay aria-label="{{ __('udbodhon.replay') }}" hidden>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 12a9 9 0 1 0 3-6.7L3 8"/><path d="M3 3v5h5"/></svg>
        </button>
    </div>
</main>

<script>
(() => {
    'use strict';

    const reduced = matchMedia('(prefers-reduced-motion: reduce)').matches;
    const rand = (a, b) => a + Math.random() * (b - a);
    const pick = list => list[Math.floor(Math.random() * list.length)];

    /* =====================================================================
     | Music — synthesised with the Web Audio API.
     |
     | A drum roll while the ribbon is cut, a cymbal crash and timpani as it
     | falls, a brass fanfare as the curtains open, then a bright loop
     | (bells, bass, pad and light drums) until the visitor mutes it.
     | A recorded track at public/media/udbodhon.mp3 replaces the loop.
     * ===================================================================== */
    const Music = (() => {
        const TRACK = @json($track);
        const VOLUME = 0.55;
        let ctx, master, reverb, noiseBuffer, timer, track;
        let step = 0, nextTime = 0, muted = false;

        const hz = midi => 440 * Math.pow(2, (midi - 69) / 12);

        function init() {
            ctx = new (window.AudioContext || window.webkitAudioContext)();

            const compressor = ctx.createDynamicsCompressor();
            master = ctx.createGain();
            master.gain.value = VOLUME;
            master.connect(compressor).connect(ctx.destination);

            // A small hall, made from decaying noise.
            reverb = ctx.createConvolver();
            const length = ctx.sampleRate * 2.4;
            const impulse = ctx.createBuffer(2, length, ctx.sampleRate);
            for (let ch = 0; ch < 2; ch++) {
                const data = impulse.getChannelData(ch);
                for (let i = 0; i < length; i++) data[i] = (Math.random() * 2 - 1) * Math.pow(1 - i / length, 3);
            }
            reverb.buffer = impulse;
            const wet = ctx.createGain();
            wet.gain.value = 0.32;
            reverb.connect(wet).connect(master);

            noiseBuffer = ctx.createBuffer(1, ctx.sampleRate, ctx.sampleRate);
            const noise = noiseBuffer.getChannelData(0);
            for (let i = 0; i < noise.length; i++) noise[i] = Math.random() * 2 - 1;

            if (TRACK) {
                track = new Audio(TRACK);
                track.loop = true;
                track.volume = VOLUME;
            }

            document.addEventListener('visibilitychange', () => {
                if (!ctx) return;
                if (document.hidden) { ctx.suspend(); track?.pause(); }
                else { ctx.resume(); if (track && timer === 'track') track.play().catch(() => {}); }
            });
        }

        function send(node, wet = 0.3) {
            node.connect(master);
            if (wet > 0) {
                const g = ctx.createGain();
                g.gain.value = wet;
                node.connect(g).connect(reverb);
            }
        }

        function envelope(t, peak, attack, duration) {
            const g = ctx.createGain();
            g.gain.setValueAtTime(0.0001, t);
            g.gain.linearRampToValueAtTime(peak, t + attack);
            g.gain.exponentialRampToValueAtTime(0.0001, t + duration);
            return g;
        }

        function tone(t, freq, duration, { type = 'sine', gain = 0.1, attack = 0.01, wet = 0.3, cutoff = 0 } = {}) {
            const osc = ctx.createOscillator();
            osc.type = type;
            osc.frequency.value = freq;
            const g = envelope(t, gain, attack, duration);
            if (cutoff) {
                const f = ctx.createBiquadFilter();
                f.type = 'lowpass';
                f.frequency.value = cutoff;
                osc.connect(f).connect(g);
            } else {
                osc.connect(g);
            }
            send(g, wet);
            osc.start(t);
            osc.stop(t + duration + 0.05);
        }

        function noise(t, duration, { type = 'highpass', freq = 6000, q = 0.8, gain = 0.2, wet = 0.15 } = {}) {
            const src = ctx.createBufferSource();
            src.buffer = noiseBuffer;
            const f = ctx.createBiquadFilter();
            f.type = type;
            f.frequency.value = freq;
            f.Q.value = q;
            const g = envelope(t, gain, 0.003, duration);
            src.connect(f).connect(g);
            send(g, wet);
            src.start(t);
            src.stop(t + duration + 0.05);
        }

        function brass(t, midi, duration, gain = 0.1) {
            const g = ctx.createGain();
            g.gain.setValueAtTime(0.0001, t);
            g.gain.linearRampToValueAtTime(gain, t + 0.05);
            g.gain.setValueAtTime(gain * 0.85, t + Math.max(0.06, duration - 0.12));
            g.gain.exponentialRampToValueAtTime(0.0001, t + duration + 0.15);

            const f = ctx.createBiquadFilter();
            f.type = 'lowpass';
            f.frequency.setValueAtTime(500, t);
            f.frequency.linearRampToValueAtTime(3200, t + 0.06);
            f.frequency.exponentialRampToValueAtTime(1500, t + duration);
            f.connect(g);
            send(g, 0.35);

            for (const detune of [-7, 7]) {
                const osc = ctx.createOscillator();
                osc.type = 'sawtooth';
                osc.frequency.value = hz(midi);
                osc.detune.value = detune;
                osc.connect(f);
                osc.start(t);
                osc.stop(t + duration + 0.2);
            }
        }

        function bell(t, midi, gain = 0.06) {
            tone(t, hz(midi), 0.9, { gain, attack: 0.004, wet: 0.4 });
            tone(t, hz(midi) * 2.01, 0.4, { gain: gain * 0.3, attack: 0.002, wet: 0.4 });
            tone(t, hz(midi) * 3.0, 0.2, { type: 'triangle', gain: gain * 0.12, attack: 0.002, wet: 0.2 });
        }

        function kick(t, gain = 0.45) {
            const osc = ctx.createOscillator();
            osc.frequency.setValueAtTime(140, t);
            osc.frequency.exponentialRampToValueAtTime(42, t + 0.14);
            const g = envelope(t, gain, 0.002, 0.32);
            osc.connect(g);
            send(g, 0);
            osc.start(t);
            osc.stop(t + 0.4);
        }

        function timpani(t) {
            const osc = ctx.createOscillator();
            osc.frequency.setValueAtTime(90, t);
            osc.frequency.exponentialRampToValueAtTime(48, t + 1.2);
            const g = envelope(t, 0.7, 0.005, 1.6);
            osc.connect(g);
            send(g, 0.4);
            osc.start(t);
            osc.stop(t + 1.7);
        }

        function drumRoll(t, duration) {
            const hits = Math.floor(duration / 0.045);
            for (let i = 0; i < hits; i++) {
                const p = i / hits;
                noise(t + i * 0.045 + rand(-0.004, 0.004), 0.09, { type: 'bandpass', freq: 2200, q: 0.7, gain: 0.03 + p * p * 0.3, wet: 0.2 });
            }
        }

        function crash(t) {
            noise(t, 2.6, { type: 'highpass', freq: 4500, gain: 0.32, wet: 0.5 });
            timpani(t);
        }

        // "Ta-ta-ta-taaa, ta-ta-ta-taaaaa" — a brass call in C, ending on a full chord.
        function fanfare(t) {
            const calls = [
                [67, 0.13], [67, 0.13], [67, 0.13], [72, 0.5],
                [67, 0.13], [72, 0.13], [76, 0.13], [79, 0.7],
            ];
            let at = t;
            for (const [midi, length] of calls) {
                brass(at, midi, length * 0.92, 0.1);
                brass(at, midi - 12, length * 0.92, 0.05);
                at += length + 0.03;
            }
            for (const midi of [48, 55, 60, 64, 67, 72, 76]) brass(at, midi, 2.0, 0.055);
            timpani(at);
            noise(at, 2.2, { type: 'highpass', freq: 5000, gain: 0.22, wet: 0.5 });
            return at + 1.6;
        }

        /* The loop: eight bars, C – G – Am – F – C – G – F – G, at 116 bpm. */
        const BARS = [
            { bass: 36, chord: [60, 64, 67], lead: [79, 76, 79, 84] },
            { bass: 43, chord: [59, 62, 67], lead: [83, 81, 79, 74] },
            { bass: 45, chord: [60, 64, 69], lead: [84, 83, 81, 76] },
            { bass: 41, chord: [60, 65, 69], lead: [77, 81, 84, 81] },
            { bass: 36, chord: [60, 64, 67], lead: [88, 86, 84, 79] },
            { bass: 43, chord: [59, 62, 67], lead: [83, 86, 91, 86] },
            { bass: 41, chord: [60, 65, 69], lead: [81, 84, 89, 88] },
            { bass: 43, chord: [59, 62, 67], lead: [86, 83, 79, null] },
        ];
        const EIGHTH = 60 / 116 / 2;
        const ARP = [0, 1, 2, 1, 0, 1, 2, 1];

        function playStep(s, t) {
            const bar = BARS[Math.floor(s / 8)];
            const e = s % 8;

            bell(t, bar.chord[ARP[e]] + (e >= 4 ? 12 : 0), 0.035);
            if (e % 2 === 0) {
                const note = bar.lead[e / 2];
                if (note) {
                    tone(t, hz(note), EIGHTH * 1.9, { type: 'triangle', gain: 0.075, attack: 0.02, wet: 0.35 });
                    tone(t, hz(note) * 2, EIGHTH * 1.2, { gain: 0.015, attack: 0.02, wet: 0.35 });
                }
            }
            if (e === 0 || e === 4) tone(t, hz(bar.bass), EIGHTH * 3.6, { type: 'triangle', gain: 0.16, attack: 0.01, wet: 0.05 });
            if (e === 0) for (const midi of bar.chord) tone(t, hz(midi - 12), EIGHTH * 8, { type: 'sawtooth', gain: 0.014, attack: 0.25, wet: 0.5, cutoff: 1100 });

            if (e === 0 || e === 4) kick(t);
            if (e === 2 || e === 6) noise(t, 0.16, { type: 'bandpass', freq: 1800, q: 0.9, gain: 0.12, wet: 0.25 });
            if (e % 2 === 1) noise(t, 0.04, { type: 'highpass', freq: 8000, gain: 0.045, wet: 0 });
        }

        function scheduler() {
            while (nextTime < ctx.currentTime + 0.15) {
                playStep(step, nextTime);
                nextTime += EIGHTH;
                step = (step + 1) % (BARS.length * 8);
            }
        }

        return {
            start() {
                if (!ctx) init();
                ctx.resume();
                this.stop(true);
                master.gain.cancelScheduledValues(ctx.currentTime);
                master.gain.setValueAtTime(muted ? 0 : VOLUME, ctx.currentTime);

                const t = ctx.currentTime + 0.05;
                drumRoll(t, 1.45);
                crash(t + 1.5);
                const loopAt = fanfare(t + 1.9) + 0.25;

                if (track) {
                    timer = setTimeout(() => {
                        track.currentTime = 0;
                        track.volume = muted ? 0 : VOLUME;
                        track.play().catch(() => {});
                        timer = 'track';
                    }, (loopAt - ctx.currentTime) * 1000);
                } else {
                    step = 0;
                    nextTime = loopAt;
                    timer = setInterval(scheduler, 25);
                }
            },
            stop(immediate = false) {
                if (!ctx) return;
                clearInterval(timer);
                clearTimeout(timer);
                timer = null;
                track?.pause();
                if (!immediate) {
                    master.gain.cancelScheduledValues(ctx.currentTime);
                    master.gain.setTargetAtTime(0, ctx.currentTime, 0.25);
                }
            },
            toggleMute() {
                muted = !muted;
                if (ctx) master.gain.setTargetAtTime(muted ? 0 : VOLUME, ctx.currentTime, 0.1);
                if (track) track.volume = muted ? 0 : VOLUME;
                return muted;
            },
        };
    })();

    /* =====================================================================
     | Effects — paper confetti, streamers, rose petals, marigolds and
     | fireworks, all on one canvas in front of the stage.
     * ===================================================================== */
    const FX = (() => {
        const canvas = document.getElementById('fx');
        const c = canvas.getContext('2d');
        const PAPER = ['#41843f', '#5d9e59', '#8fbf8b', '#f2c14e', '#ffe08a', '#ffffff', '#2a5f9c', '#5183bd', '#e8505b', '#ff9f43'];
        const PETAL = ['#ff8fab', '#ffb3c6', '#ffc8dd', '#ff6b8b', '#e85d75', '#fff0f3'];
        const MARIGOLD = [['#ffb703', '#e85d04'], ['#ffd166', '#f4a261'], ['#fb8500', '#c1440e']];
        const SPARK = ['#ffe8a3', '#f2c14e', '#ffffff', '#8fbf8b', '#ff8fab', '#89aed8'];

        let particles = [];
        let W = 0, H = 0, density = 1, frame = 0, last = 0, clock = 0;
        let rain = 0, rainUntil = 0, drizzle = 0, fireworksAt = 0, fireworksFast = 0, active = false;

        function resize() {
            const dpr = Math.min(devicePixelRatio || 1, 2);
            W = innerWidth;
            H = innerHeight;
            canvas.width = W * dpr;
            canvas.height = H * dpr;
            c.setTransform(dpr, 0, 0, dpr, 0, 0);
            density = Math.min(1.3, Math.max(0.45, (W * H) / (1440 * 900))) * (reduced ? 0.3 : 1);
        }
        addEventListener('resize', resize);
        resize();

        const make = {
            paper: (x, y, vx, vy) => ({ k: 'paper', x, y, vx, vy, w: rand(7, 14), h: rand(4, 8), rot: rand(0, 6.3), vr: rand(-0.15, 0.15),
                tilt: rand(0, 6.3), vt: rand(0.06, 0.2), color: pick(PAPER), g: 0.11, drag: 0.983, max: 3.4, sway: rand(0.2, 0.8), phase: rand(0, 6.3) }),
            streamer: (x, y, vx, vy) => ({ k: 'streamer', x, y, vx, vy, len: rand(36, 70), rot: rand(0, 6.3), vr: rand(-0.06, 0.06),
                phase: rand(0, 6.3), color: pick(PAPER), g: 0.09, drag: 0.98, max: 2.6, sway: rand(0.3, 0.9) }),
            petal: (x, y, vx, vy) => ({ k: 'petal', x, y, vx, vy, s: rand(6, 11), rot: rand(0, 6.3), vr: rand(-0.05, 0.05),
                tilt: rand(0, 6.3), vt: rand(0.03, 0.09), color: pick(PETAL), g: 0.035, drag: 0.99, max: 1.7, sway: rand(0.6, 1.6), phase: rand(0, 6.3) }),
            marigold: (x, y, vx, vy) => { const [petal, core] = pick(MARIGOLD);
                return { k: 'marigold', x, y, vx, vy, r: rand(7, 12), rot: rand(0, 6.3), vr: rand(-0.04, 0.04),
                    petal, core, g: 0.04, drag: 0.99, max: 2.1, sway: rand(0.4, 1.1), phase: rand(0, 6.3) }; },
            spark: (x, y, vx, vy, color) => ({ k: 'spark', x, y, vx, vy, color, life: 1, decay: rand(0.011, 0.02), g: 0.035, drag: 0.972, max: 9, size: rand(1.4, 2.6) }),
        };

        function randomPiece(x, y, vx, vy, mix) {
            const r = Math.random();
            let acc = 0;
            for (const [kind, share] of mix) {
                acc += share;
                if (r < acc) return make[kind](x, y, vx, vy);
            }
            return make.paper(x, y, vx, vy);
        }

        function cannon(side) {
            const left = side === 'left';
            const n = Math.round(150 * density);
            const power = Math.max(0.75, H / 900);
            for (let i = 0; i < n; i++) {
                const angle = -rand(1.0, 1.35);                         // upwards, leaning in
                const speed = rand(11, 23) * power;
                const vx = Math.cos(angle) * speed * (left ? 1 : -1);
                const vy = Math.sin(angle) * speed;
                particles.push(randomPiece(left ? -10 : W + 10, H + 10, vx, vy, [['paper', .66], ['streamer', .12], ['petal', .14], ['marigold', .08]]));
            }
        }

        function firework(x = rand(W * 0.15, W * 0.85), y = rand(H * 0.12, H * 0.45)) {
            const n = Math.round(rand(60, 95) * (reduced ? 0.4 : 1));
            const colour = pick(SPARK);
            const second = pick(SPARK);
            for (let i = 0; i < n; i++) {
                const a = (i / n) * Math.PI * 2 + rand(-0.05, 0.05);
                const s = rand(2.2, 6.2);
                particles.push(make.spark(x, y, Math.cos(a) * s, Math.sin(a) * s, i % 3 ? colour : second));
            }
        }

        function spawnRain(rate) {
            let count = rate;
            while (count > 0) {
                if (Math.random() < count) {
                    particles.push(randomPiece(rand(-20, W + 20), -24, rand(-0.6, 0.6), rand(0.5, 2), [['petal', .5], ['marigold', .12], ['paper', .38]]));
                }
                count -= 1;
            }
        }

        function update(f, now) {
            clock += f;
            if (now < rainUntil) spawnRain(rain * density * f);
            else if (drizzle) spawnRain(drizzle * density * f);

            if (fireworksAt && now >= fireworksAt) {
                firework();
                fireworksAt = now + (now < fireworksFast ? rand(650, 1300) : rand(2600, 4600));
            }

            for (const p of particles) {
                const drag = Math.pow(p.drag, f);
                p.vx *= drag;
                p.vy = p.vy * drag + p.g * f;
                if (p.vy > p.max) p.vy = p.max;
                p.x += (p.vx + (p.sway ? Math.sin(clock * 0.05 + p.phase) * p.sway : 0)) * f;
                p.y += p.vy * f;
                if (p.vr) p.rot += p.vr * f;
                if (p.vt) p.tilt += p.vt * f;
                if (p.life !== undefined) p.life -= p.decay * f;
            }
            particles = particles.filter(p => p.y < H + 60 && p.x > -120 && p.x < W + 120 && (p.life === undefined || p.life > 0));
        }

        function draw() {
            c.clearRect(0, 0, W, H);
            for (const p of particles) {
                c.save();
                c.translate(p.x, p.y);
                switch (p.k) {
                    case 'paper': {
                        const flip = Math.cos(p.tilt);
                        c.rotate(p.rot);
                        c.scale(1, flip);
                        c.globalAlpha = 0.75 + 0.25 * Math.abs(flip);
                        c.fillStyle = p.color;
                        c.fillRect(-p.w / 2, -p.h / 2, p.w, p.h);
                        break;
                    }
                    case 'streamer': {
                        c.rotate(p.rot);
                        c.strokeStyle = p.color;
                        c.lineWidth = 3;
                        c.lineCap = 'round';
                        c.beginPath();
                        for (let i = 0; i <= 12; i++) {
                            const y = (i / 12 - 0.5) * p.len;
                            const x = Math.sin(i * 0.9 + p.phase + clock * 0.12) * 6;
                            i ? c.lineTo(x, y) : c.moveTo(x, y);
                        }
                        c.stroke();
                        break;
                    }
                    case 'petal': {
                        const s = p.s;
                        c.rotate(p.rot);
                        c.scale(Math.cos(p.tilt), 1);
                        c.fillStyle = p.color;
                        c.beginPath();
                        c.moveTo(0, -s);
                        c.bezierCurveTo(s * 0.95, -s * 0.55, s * 0.6, s * 0.7, 0, s);
                        c.bezierCurveTo(-s * 0.6, s * 0.7, -s * 0.95, -s * 0.55, 0, -s);
                        c.fill();
                        c.globalAlpha = 0.35;
                        c.strokeStyle = '#fff';
                        c.lineWidth = 0.8;
                        c.beginPath();
                        c.moveTo(0, -s * 0.6);
                        c.lineTo(0, s * 0.6);
                        c.stroke();
                        break;
                    }
                    case 'marigold': {
                        c.rotate(p.rot);
                        for (let ring = 0; ring < 2; ring++) {
                            const r = p.r * (ring ? 0.68 : 1);
                            c.fillStyle = ring ? p.core : p.petal;
                            for (let i = 0; i < 10; i++) {
                                c.rotate(Math.PI / 5);
                                c.beginPath();
                                c.ellipse(0, -r * 0.55, r * 0.3, r * 0.55, 0, 0, Math.PI * 2);
                                c.fill();
                            }
                        }
                        c.fillStyle = '#8a3b00';
                        c.beginPath();
                        c.arc(0, 0, p.r * 0.22, 0, Math.PI * 2);
                        c.fill();
                        break;
                    }
                    case 'spark': {
                        c.globalCompositeOperation = 'lighter';
                        c.globalAlpha = Math.max(0, p.life);
                        c.fillStyle = p.color;
                        c.beginPath();
                        c.arc(0, 0, p.size, 0, Math.PI * 2);
                        c.fill();
                        c.globalAlpha = Math.max(0, p.life) * 0.35;
                        c.beginPath();
                        c.arc(-p.vx * 1.5, -p.vy * 1.5, p.size * 0.8, 0, Math.PI * 2);
                        c.fill();
                        break;
                    }
                }
                c.restore();
            }
        }

        function loop(now) {
            const f = last ? Math.min((now - last) / 16.667, 3) : 1;
            last = now;
            update(f, now);
            draw();
            if (active || particles.length) {
                frame = requestAnimationFrame(loop);
            } else {
                frame = 0;
                last = 0;
            }
        }

        function wake() {
            if (!frame) frame = requestAnimationFrame(loop);
        }

        return {
            celebrate() {
                const now = performance.now();
                active = true;
                cannon('left');
                cannon('right');
                setTimeout(() => { cannon('left'); cannon('right'); }, 900);
                rain = 1.4;
                rainUntil = now + 7000;
                drizzle = 0.35;
                fireworksAt = now + 400;
                fireworksFast = now + 14000;
                wake();
            },
            stop() {
                active = false;
                rain = drizzle = 0;
                rainUntil = fireworksAt = 0;
            },
        };
    })();

    /* =====================================================================
     | Balloons — a dozen, rising on a loop once the curtains are open.
     * ===================================================================== */
    (() => {
        const holder = document.getElementById('balloons');
        const colours = ['#41843f', '#5d9e59', '#f2c14e', '#e8505b', '#2a5f9c', '#ff9f43', '#ff8fab', '#5183bd'];
        const count = reduced ? 5 : 12;
        for (let i = 0; i < count; i++) {
            const b = document.createElement('span');
            b.className = 'balloon';
            b.style.setProperty('--x', `${(i / count) * 92 + rand(0, 6)}%`);
            b.style.setProperty('--w', `${rand(42, 66)}px`);
            b.style.setProperty('--c', colours[i % colours.length]);
            b.style.setProperty('--t', `${rand(11, 17)}s`);
            b.style.setProperty('--delay', `${2.4 + rand(0, 9)}s`);
            b.style.setProperty('--sway', `${rand(-40, 40)}px`);
            holder.appendChild(b);
        }
    })();

    /* =====================================================================
     | The ceremony.
     * ===================================================================== */
    const stage = document.getElementById('stage');
    const button = document.getElementById('inaugurate');
    const mute = document.getElementById('mute');
    const replays = document.querySelectorAll('[data-replay]');
    let timers = [];
    const later = (ms, fn) => timers.push(setTimeout(fn, ms));

    button.addEventListener('click', () => {
        if (stage.dataset.state !== 'closed') return;

        stage.dataset.state = 'rolling';
        try { Music.start(); } catch (e) { /* no audio — the show goes on */ }
        mute.hidden = false;

        later(1500, () => { stage.dataset.state = 'cut'; });
        later(1900, () => {
            stage.dataset.state = 'open';
            FX.celebrate();
        });
        later(3200, () => replays.forEach(r => r.hidden = false));
    });

    replays.forEach(r => r.addEventListener('click', () => {
        timers.forEach(clearTimeout);
        timers = [];
        Music.stop();
        FX.stop();
        replays.forEach(el => { if (el.classList.contains('ctl')) el.hidden = true; });
        stage.dataset.state = 'closed';
        setTimeout(() => button.focus(), 600);
    }));

    mute.addEventListener('click', () => {
        const isMuted = Music.toggleMute();
        mute.setAttribute('aria-pressed', String(isMuted));
        mute.setAttribute('aria-label', isMuted ? mute.dataset.labelUnmute : mute.dataset.labelMute);
    });
})();
</script>
</body>
</html>
