{{--
 | The RICH inauguration — /inaugurate (formerly /udbodhon, which redirects here).
 |
 | A page of its own, outside the site layout, and in English only: a closed
 | stage of royal-blue velvet with a satin ribbon and one button. Pressing it
 | rolls the drums, cuts the ribbon, opens the curtains on a white stage and
 | unveils RICH — Research, Innovation and Consultancy Hub — with petals,
 | marigolds, gold confetti and music. If there is an opening film, the
 | curtains open on it first. The evening ends by carrying the flowers on
 | to the home page.
 |
 | Everything is drawn and played in the browser — the confetti on a canvas,
 | the music with the Web Audio API — so the page needs no front-end build.
 |
 | Optional files, picked up on their own when present:
 |   public/media/udbodhon.mp4   the opening film the curtains open on (a WhatsApp video works as is)
 |   public/media/udbodhon.jpg   a still shown before the video starts
 |   public/media/udbodhon.mp3   a recorded track, played from the unveiling in place of the
 |                               generated fanfare and tune (it loops)
--}}
@php
    $media = fn (string $file) => file_exists(public_path('media/'.$file)) ? asset('media/'.$file) : null;
    $video = $media('udbodhon.mp4');
    $poster = $media('udbodhon.jpg');
    $track = $media('udbodhon.mp3');
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#021634">
    <title>Grand Opening — UGV RICH</title>
    <meta name="description" content="The grand opening of RICH — the Research, Innovation and Consultancy Hub of the University of Global Village.">
    <meta property="og:title" content="Grand Opening — UGV RICH">
    <meta property="og:description" content="The Research, Innovation and Consultancy Hub of the University of Global Village opens its doors.">
    <link rel="icon" href="{{ asset('favicon.png') }}" type="image/png">

    {{ Vite::fonts(['plus-jakarta-sans', 'inter']) }}

    <style>
        /* Colours, type and buttons follow the main site (resources/css/app.css):
           brand greens, the site's golds and inks, Plus Jakarta Sans for display
           and Inter for text, and the same pill buttons as the home page. */
        :root {
            --brand-50: #f1f8f0;
            --brand-100: #ddeeda;
            --brand-300: #8fbf8b;
            --brand-400: #5d9e59;
            --brand-500: #41843f;
            --brand-600: #316d31;
            --brand-700: #275727;
            --brand-800: #204520;
            --brand-900: #1a371a;
            --brand-950: #10240f;
            --navy-400: #5183bd;
            --navy-500: #2a5f9c;
            --navy-600: #0f4280;
            --navy-700: #022251;
            --navy-800: #021c42;
            --navy-900: #021634;
            --navy-950: #010c1f;
            --gold-100: #fdf3d7;
            --gold-200: #f8e3a3;
            --gold-300: #f2cd6b;
            --gold-400: #e3ab2e;
            --gold-500: #c3881a;
            --gold-700: #8a5d0f;
            --ink-200: #e1e6ee;
            --ink-400: #97a1b4;
            --ink-500: #6b7488;
            --ink-600: #4d5668;
            --ink-800: #232a38;
            --ink-950: #071426;
            --display: 'Plus Jakarta Sans', 'Inter', system-ui, sans-serif;
            --sans: 'Inter', system-ui, sans-serif;
            --ease: cubic-bezier(.22, 1, .36, 1);
        }

        * { box-sizing: border-box; margin: 0; }
        html, body { height: 100%; }
        body { overflow: hidden; background: var(--navy-950); color: var(--ink-950); font-family: var(--sans); -webkit-font-smoothing: antialiased; }
        button { font: inherit; cursor: pointer; }

        .stage { position: fixed; inset: 0; overflow: hidden; }

        /* ---------- Buttons and pills — the home page's own ---------- */

        .btn-lead, .btn-invert, .btn-ghost {
            position: relative; overflow: hidden;
            display: inline-flex; align-items: center; justify-content: center; gap: 10px;
            white-space: nowrap; border-radius: 999px; text-decoration: none;
            font-family: var(--display); font-weight: 700;
            transition: transform .3s var(--ease), background-color .2s ease, box-shadow .3s var(--ease), border-color .2s ease, color .2s ease;
        }
        .btn-lead {
            padding: 16px 32px; border: 0; font-size: 15px; color: #fff;
            background: var(--brand-600);
            box-shadow: 0 18px 40px -14px var(--brand-600);
        }
        .btn-lead:hover { transform: translateY(-4px); background: var(--brand-500); box-shadow: 0 26px 54px -14px var(--brand-600); }
        .btn-invert {
            padding: 18px 36px; border: 0; font-size: 16px; color: var(--brand-700);
            background: #fff;
            box-shadow: 0 0 0 6px rgba(255, 255, 255, .14), 0 24px 44px -14px rgba(0, 0, 0, .7);
        }
        .btn-invert:hover { transform: translateY(-3px); background: var(--brand-50); }
        .btn-ghost {
            padding: 12px 24px; font-size: 14px; font-weight: 600; color: var(--ink-800);
            background: #fff; border: 1px solid var(--ink-200);
        }
        .btn-ghost:hover { transform: translateY(-2px); border-color: var(--brand-300); color: var(--brand-700); }
        /* The light sweep the home page's lead button has */
        .btn-lead::after, .btn-invert::after {
            content: ''; position: absolute; inset: 0; pointer-events: none;
            background: linear-gradient(110deg, transparent 20%, rgba(255, 255, 255, .28) 42%, transparent 64%);
            transform: translateX(-120%); transition: transform .7s var(--ease);
        }
        .btn-invert::after { background: linear-gradient(110deg, transparent 20%, rgba(49, 109, 49, .12) 42%, transparent 64%); }
        .btn-lead:hover::after, .btn-invert:hover::after { transform: translateX(120%); }
        .btn-lead:focus-visible, .btn-invert:focus-visible, .btn-ghost:focus-visible { outline: 2px solid var(--brand-400); outline-offset: 3px; }
        .btn-lead svg, .btn-invert svg, .btn-ghost svg { width: 18px; height: 18px; }

        .eyebrow, .eyebrow-invert {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 6px 14px; border-radius: 999px;
            font-family: var(--sans); font-size: 11px; font-weight: 600; letter-spacing: .16em; text-transform: uppercase;
        }
        .eyebrow { color: var(--brand-700); background: var(--brand-50); border: 1px solid var(--brand-100); }
        .eyebrow-invert { color: #fff; background: rgba(255, 255, 255, .1); border: 1px solid rgba(255, 255, 255, .25); }

        /* ---------- The stage behind the curtains ---------- */

        .backdrop {
            position: absolute; inset: 0;
            background:
                radial-gradient(ellipse 55% 50% at 50% 42%, #ffffff 0%, rgba(255, 255, 255, 0) 70%),
                radial-gradient(ellipse 80% 45% at 50% 108%, rgba(65, 132, 63, .16), transparent 70%),
                radial-gradient(ellipse 60% 40% at 50% -5%, rgba(242, 205, 107, .2), transparent 70%),
                linear-gradient(180deg, #ffffff 0%, var(--brand-50) 100%);
        }
        .rays {
            position: absolute; left: 50%; top: 44%;
            width: 200vmax; height: 200vmax; margin: -100vmax 0 0 -100vmax;
            background: repeating-conic-gradient(from 0deg, rgba(242, 205, 107, .16) 0deg 4deg, transparent 4deg 15deg);
            -webkit-mask: radial-gradient(circle, #000 0, transparent 48%);
                    mask: radial-gradient(circle, #000 0, transparent 48%);
            animation: spin 90s linear infinite;
            opacity: 0; transition: opacity 2.5s ease 1s;
        }
        [data-state="open"] .rays { opacity: 1; }

        .reveal {
            position: absolute; inset: 0; z-index: 3;
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            padding: 13vh max(20px, 9vw) 7vh; text-align: center;
            visibility: hidden;
            transition: opacity .8s ease, transform .8s ease, filter .8s ease;
        }
        .stage.is-unveiled .reveal { visibility: visible; }
        .reveal > * { opacity: 0; transform: translateY(26px); }
        .stage.is-unveiled .reveal > * { animation: rise 1.1s var(--ease) forwards; animation-delay: var(--d, 0s); }

        /* "Welcome to", with the home page's gold stroke under "to" */
        .welcome {
            font-family: var(--display); font-weight: 800; letter-spacing: -.02em; line-height: 1.1;
            font-size: clamp(32px, 5vw, 64px); color: var(--ink-950);
        }
        .welcome span { position: relative; display: inline-block; color: var(--brand-600); }
        .welcome span::after {
            content: ''; position: absolute; left: -.08em; right: -.08em; bottom: .02em; height: .14em; border-radius: 999px;
            background: linear-gradient(90deg, rgba(242, 205, 107, 0), rgba(242, 205, 107, .85) 12%, rgba(227, 171, 46, .95) 60%, rgba(242, 205, 107, 0));
            transform: scaleX(0); transform-origin: left; transition: transform .9s var(--ease) 1.2s;
            z-index: -1;
        }
        .stage.is-unveiled .welcome span::after { transform: scaleX(1); }

        .logo-full {
            display: block; margin-top: clamp(16px, 3vh, 30px);
            width: auto; height: clamp(240px, 52vh, 460px); max-width: 82vw; object-fit: contain;
            filter: drop-shadow(0 26px 40px rgba(7, 20, 38, .14));
        }
        .reveal > .logo-full { transform: translateY(20px) scale(.88); }


        .actions { margin-top: clamp(20px, 4vh, 34px); display: flex; flex-wrap: wrap; align-items: center; justify-content: center; gap: 12px 20px; }
        .countdown { font-size: 13px; color: var(--ink-500); min-height: 1em; }

        /* ---------- The opening video: the logo reveal the curtains open on ---------- */

        .film {
            position: absolute; inset: 0; z-index: 4;
            background: #e4ebee;              /* the video's own backdrop, for the bands on tall screens */
            opacity: 0; visibility: hidden; transition: opacity 1.4s ease, visibility 0s linear 1.4s;
        }
        .stage.is-playing .film { opacity: 1; visibility: visible; transition: opacity .5s ease; }
        /* Full width and full height on every screen, phones included. */
        .film video { display: block; width: 100%; height: 100%; object-fit: cover; }

        /* ---------- The curtains: royal-blue velvet in the site's navy ---------- */

        .curtain {
            position: absolute; top: 0; bottom: 0; z-index: 5; width: 50.5vw;
            background:
                linear-gradient(180deg, rgba(0, 0, 0, .35), transparent 20%, transparent 75%, rgba(0, 0, 0, .5)),
                repeating-linear-gradient(90deg,
                    var(--navy-950) 0, var(--navy-900) 1.2vw, var(--navy-700) 2.3vw, var(--navy-600) 2.8vw,
                    var(--navy-500) 3.1vw, #4f7fb8 3.25vw, var(--navy-600) 3.6vw, var(--navy-700) 4.2vw, var(--navy-900) 5.2vw, var(--navy-950) 6.4vw);
            transition: transform 3.2s cubic-bezier(.66, 0, .25, 1);
            will-change: transform, background-position;
            /* ঢেউ — the velvet never quite rests: its folds drift and breathe */
            animation: billow 9s ease-in-out infinite alternate;
        }
        .curtain::before { /* gold hem */
            content: ''; position: absolute; left: 0; right: 0; bottom: 0; height: 12px;
            background: linear-gradient(180deg, var(--gold-300), var(--gold-500));
            box-shadow: 0 -2px 6px rgba(0, 0, 0, .3);
        }
        .curtain::after { /* velvet sheen, and a band of light rolling across it like a wave */
            content: ''; position: absolute; inset: 0;
            background:
                linear-gradient(100deg, transparent 38%, rgba(170, 200, 240, .13) 47%, rgba(200, 222, 250, .2) 50%, rgba(170, 200, 240, .13) 53%, transparent 62%) 0 0 / 300% 100% no-repeat,
                radial-gradient(ellipse 65% 40% at 50% 38%, rgba(170, 200, 240, .12), transparent 70%);
            animation: ripple 7s ease-in-out infinite;
        }
        .curtain-left { left: 0; transform-origin: 0 50%; box-shadow: inset -34px 0 40px -20px rgba(0, 0, 0, .7); }
        .curtain-right { right: 0; transform-origin: 100% 50%; box-shadow: inset 34px 0 40px -20px rgba(0, 0, 0, .7); }
        /* The wave runs on from the left curtain into the right */
        .curtain-right { animation-delay: -4.5s; }
        .curtain-right::after { animation-delay: 3.5s; }
        [data-state="rolling"] .curtain { animation: shiver 1.4s ease-in-out, billow 9s ease-in-out infinite alternate; }
        [data-state="open"] .curtain-left { transform: scaleX(.12) skewY(-1deg); }
        [data-state="open"] .curtain-right { transform: scaleX(.12) skewY(1deg); }

        /* White satin valance with a gold braid */
        .valance {
            position: absolute; top: 0; left: 0; right: 0; z-index: 7;
            height: clamp(64px, 12.5vh, 124px);
            background:
                linear-gradient(180deg, rgba(7, 20, 38, .12), transparent 45%),
                repeating-linear-gradient(90deg, #e6ece4 0, #f8faf7 2.2vw, #ffffff 2.9vw, #f1f5ef 3.6vw, #dfe7dc 6vw);
            -webkit-mask:
                linear-gradient(#000 0 0) top / 100% calc(100% - 24px) no-repeat,
                radial-gradient(circle 25px at 50% 0, #000 96%, transparent 100%) bottom / 50px 24px repeat-x;
                    mask:
                linear-gradient(#000 0 0) top / 100% calc(100% - 24px) no-repeat,
                radial-gradient(circle 25px at 50% 0, #000 96%, transparent 100%) bottom / 50px 24px repeat-x;
            filter: drop-shadow(0 12px 18px rgba(0, 0, 0, .4));
        }
        .valance::before {
            content: ''; position: absolute; left: 0; right: 0; bottom: 0; height: 24px;
            background: radial-gradient(circle 25px at 50% 0, transparent 21px, var(--gold-300) 22px, var(--gold-500) 25px) 0 0 / 50px 24px repeat-x;
        }
        .valance::after {
            content: ''; position: absolute; left: 0; right: 0; bottom: 24px; height: 6px;
            background: repeating-linear-gradient(90deg, var(--gold-500) 0 3px, var(--gold-200) 3px 6px, var(--gold-400) 6px 11px);
        }

        /* ---------- The closed stage ---------- */

        .intro {
            position: absolute; inset: 0; z-index: 8;
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            padding: 15vh 20px 8vh; text-align: center; color: #fff;
            transition: opacity .6s ease;
        }
        .intro .text { display: flex; flex-direction: column; align-items: center; transition: opacity .6s ease, transform .6s ease; }
        [data-state="cut"] .intro .text, [data-state="open"] .intro .text { opacity: 0; transform: translateY(-18px); }
        [data-state="open"] .intro { opacity: 0; pointer-events: none; transition-delay: 1.3s; }

        /* The logo is the hero of the closed stage: large, on a white tile with a
           gold ring, lit from behind by a slow halo. */
        .logo {
            position: relative; display: inline-flex; width: clamp(92px, 13vmin, 128px); aspect-ratio: 1;
            padding: clamp(11px, 1.7vmin, 15px); border-radius: 28px; background: #fff;
            box-shadow:
                0 0 0 2px var(--gold-300),
                0 0 0 10px rgba(255, 255, 255, .07),
                0 0 70px rgba(120, 165, 225, .35),
                0 34px 60px -22px rgba(0, 0, 0, .85);
        }
        .logo::before {             /* halo */
            content: ''; position: absolute; inset: -45%; z-index: -1; border-radius: 50%;
            background: radial-gradient(circle, rgba(140, 180, 235, .35), transparent 62%);
            animation: halo 4.5s ease-in-out infinite;
        }
        .logo img { width: 100%; height: 100%; object-fit: contain; }

        /* The university's name, signed across the top of the stage under the valance */
        .signature {
            position: absolute; left: 0; right: 0; top: calc(clamp(64px, 12.5vh, 124px) + clamp(18px, 3.5vh, 34px));
            display: flex; align-items: center; justify-content: center; gap: clamp(12px, 2vw, 20px);
            font-family: var(--display); font-weight: 600; font-size: clamp(11px, 1.15vw, 14px);
            letter-spacing: .42em; text-transform: uppercase; color: rgba(255, 255, 255, .82);
            transition: opacity .6s ease, transform .6s ease;
        }
        .signature span { margin-right: -.42em; }
        .signature::before, .signature::after {
            content: ''; width: clamp(28px, 7vw, 90px); height: 1px;
            background: linear-gradient(90deg, transparent, var(--gold-300));
        }
        .signature::after { transform: scaleX(-1); }
        [data-state="cut"] .signature, [data-state="open"] .signature { opacity: 0; transform: translateY(-12px); }

        /* R I C H, and under each letter the word it stands for. The letters take
           the logo's two tones — R I in pearl white, C H in the brand green. */
        .acronym {
            margin-top: clamp(22px, 3.6vh, 34px);
            display: grid; grid-template-columns: repeat(4, auto); justify-content: center;
            column-gap: clamp(12px, 3vw, 46px);
        }
        .acronym .col { display: flex; flex-direction: column; align-items: center; }
        .acronym .letter {
            position: relative; font-family: var(--display); font-weight: 800; line-height: .9;
            font-size: clamp(66px, 10vw, 138px);
            -webkit-background-clip: text; background-clip: text; color: transparent;
            filter: drop-shadow(0 2px 0 rgba(1, 12, 31, .6)) drop-shadow(0 18px 28px rgba(0, 0, 0, .5));
            opacity: 0; transform: translateY(-.35em);
            animation: rise .9s var(--ease) forwards; animation-delay: calc(.3s + var(--i) * .14s);
        }
        .acronym .pearl { background-image: linear-gradient(176deg, #ffffff 0%, #f3f6f9 34%, #cfdbe8 62%, #f6f9fb 100%); }
        .acronym .green { background-image: linear-gradient(176deg, #ffffff 0%, #e6f3e3 26%, #bddcb9 50%, var(--brand-300) 68%, #d9ecd6 100%); }
        .acronym .letter::after {   /* a sheen passing from R to H */
            content: attr(data-l); position: absolute; inset: 0;
            background: linear-gradient(105deg, transparent 38%, rgba(255, 255, 255, .95) 50%, transparent 62%) no-repeat;
            background-size: 260% 100%; background-position: 160% 0;
            -webkit-background-clip: text; background-clip: text; color: transparent;
            animation: letter-sheen 6s var(--ease) infinite; animation-delay: calc(2s + var(--i) * .12s);
        }
        /* Each word: bold, in title case, sliding up from behind its gold line,
           then lit by a light that keeps travelling through it. */
        .acronym .word {
            position: relative; margin-top: clamp(10px, 1.6vh, 16px); padding: clamp(9px, 1.4vh, 14px) 2px 4px;
            overflow: hidden;
            font-family: var(--display); font-weight: 700; white-space: nowrap; letter-spacing: -.005em;
            font-size: clamp(14px, 1.85vw, 26px);
        }
        .acronym .word .in {
            display: inline-block;
            background: linear-gradient(90deg, #ffffff 0%, #d7e4f4 35%, #ffffff 50%, #d7e4f4 65%, #ffffff 100%) 0 0 / 200% 100%;
            -webkit-background-clip: text; background-clip: text; color: transparent;
            transform: translateY(115%);
            animation: word-up .9s var(--ease) forwards, shimmer 5s linear infinite;
            animation-delay: calc(1s + var(--i) * .15s), calc(2.4s + var(--i) * .3s);
        }
        .acronym .word b { font-weight: 800; color: var(--gold-300); text-shadow: 0 0 18px rgba(242, 205, 107, .45); }
        .acronym .word::before {    /* gold line, drawn out to the word's full width */
            content: ''; position: absolute; top: 0; left: 0; right: 0; height: 2px; border-radius: 2px;
            background: linear-gradient(90deg, transparent, var(--gold-300) 20%, var(--gold-400) 80%, transparent);
            transform: scaleX(0); animation: draw .8s var(--ease) forwards; animation-delay: calc(.8s + var(--i) * .15s);
        }

        .cut { position: relative; margin-top: 38px; }
        .ribbon {
            position: absolute; top: 50%; height: 30px; margin-top: -15px; width: 50vw;
            background: linear-gradient(180deg,
                var(--gold-400) 0 2px, #eef2ec 2px, #ffffff 35%, #f1f5ef 55%, #d7e0d4 calc(100% - 2px), var(--gold-500) calc(100% - 2px));
            box-shadow: 0 10px 22px -8px rgba(0, 0, 0, .65);
            transition: transform 1.3s cubic-bezier(.45, 0, .2, 1.25), opacity .8s ease 1.4s;
        }
        .ribbon-left { right: 50%; transform-origin: 0 50%; }
        .ribbon-right { left: 50%; transform-origin: 100% 50%; }
        [data-state="cut"] .ribbon-left, [data-state="open"] .ribbon-left { transform: rotate(78deg); }
        [data-state="cut"] .ribbon-right, [data-state="open"] .ribbon-right { transform: rotate(-78deg); }
        [data-state="open"] .ribbon { opacity: 0; }

        /* The one button: the site's green, made unmistakable — a gold hairline,
           a green glow, a pulse ring and a light that sweeps across it. */
        .btn-cut {
            z-index: 1; overflow: visible; padding: 20px 44px; font-size: clamp(16px, 1.6vw, 18px); color: #fff;
            background: linear-gradient(180deg, var(--brand-500), var(--brand-600) 55%, var(--brand-700));
            box-shadow:
                0 0 0 1px rgba(242, 205, 107, .75),
                0 0 0 6px rgba(65, 132, 63, .22),
                0 0 44px rgba(93, 158, 89, .55),
                0 24px 44px -14px rgba(0, 0, 0, .75),
                inset 0 1px 0 rgba(255, 255, 255, .25);
        }
        .btn-cut:hover { background: linear-gradient(180deg, var(--brand-400), var(--brand-500) 55%, var(--brand-600)); }
        .btn-cut .sweep { position: absolute; inset: 0; overflow: hidden; border-radius: inherit; pointer-events: none; }
        .btn-cut .sweep::after {
            content: ''; position: absolute; inset: 0;
            background: linear-gradient(110deg, transparent 25%, rgba(255, 255, 255, .45) 48%, transparent 70%);
            transform: translateX(-130%); animation: sweep 3.6s var(--ease) 1s infinite;
        }
        .btn-cut::after { display: none; }      /* replaced by the timed sweep above */
        .btn-cut::before {
            content: ''; position: absolute; inset: 0; border-radius: inherit; pointer-events: none;
            box-shadow: 0 0 0 0 rgba(93, 158, 89, .7); animation: pulse 2.2s ease-out infinite;
        }
        [data-state="rolling"] .btn-cut { animation: drum .12s linear infinite; }
        [data-state="rolling"] .btn-cut svg { animation: snip .3s ease-in-out infinite alternate; }
        [data-state="rolling"] .btn-cut::before, [data-state="cut"] .btn-cut::before, [data-state="open"] .btn-cut::before { display: none; }
        [data-state="cut"] .btn-cut, [data-state="open"] .btn-cut { transform: scale(.4); opacity: 0; pointer-events: none; }

        /* ---------- Effects, finale veil, controls ---------- */

        #fx { position: absolute; inset: 0; z-index: 9; pointer-events: none; }
        /* The simulated velvet takes the place of the CSS curtains when it runs */
        #cloth { position: absolute; inset: 0; z-index: 5; width: 100%; height: 100%; pointer-events: none; }
        .stage.has-cloth .curtain { display: none; }
        #ribbon { position: absolute; inset: 0; z-index: 6; width: 100%; height: 100%; pointer-events: none; }
        .stage.has-ribbon .ribbon { display: none; }

        .veil {
            position: absolute; inset: 0; z-index: 11;
            display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 14px;
            background: radial-gradient(ellipse at 50% 45%, #ffffff, var(--brand-50));
            opacity: 0; visibility: hidden; transition: opacity 1.6s ease, visibility 0s linear 1.6s;
        }
        .stage.is-leaving .veil { opacity: 1; visibility: visible; transition: opacity 1.6s ease; }
        .veil img {
            height: clamp(150px, 30vh, 280px); width: auto;
            filter: drop-shadow(0 18px 30px rgba(7, 20, 38, .18));
            transform: scale(.92); opacity: 0;
            transition: transform 1.8s var(--ease), opacity 1.2s ease;
        }
        .stage.is-leaving .veil img { transform: none; opacity: 1; transition-delay: .3s; }

        .controls { position: absolute; right: 18px; bottom: 18px; z-index: 12; }
        .ctl {
            display: inline-flex; align-items: center; justify-content: center;
            width: 46px; height: 46px; border-radius: 50%;
            color: var(--ink-800); background: #fff; border: 1px solid var(--ink-200);
            box-shadow: 0 10px 24px -14px rgba(7, 20, 38, .5);
            transition: color .2s ease, border-color .2s ease;
        }
        .ctl:hover { color: var(--brand-700); border-color: var(--brand-300); }
        .ctl svg { width: 20px; height: 20px; }
        .ctl[hidden] { display: none; }
        .ctl .off { display: none; }
        .ctl[aria-pressed="true"] .on { display: none; }
        .ctl[aria-pressed="true"] .off { display: block; }

        @keyframes spin { to { transform: rotate(360deg); } }
        @keyframes rise { to { opacity: 1; transform: none; } }
        @keyframes pulse { 0% { box-shadow: 0 0 0 0 rgba(93, 158, 89, .7); } 100% { box-shadow: 0 0 0 22px rgba(93, 158, 89, 0); } }
        @keyframes drum { 25% { transform: translate(-1px, 1px) rotate(-.6deg); } 75% { transform: translate(1px, -1px) rotate(.6deg); } }
        @keyframes snip { to { transform: rotate(-22deg); } }
        @keyframes shiver { 30%, 70% { transform: scaleX(.985); } }
        @keyframes halo { 50% { opacity: .55; transform: scale(.92); } }
        @keyframes letter-sheen { 0% { background-position: 160% 0; } 40%, 100% { background-position: -60% 0; } }
        @keyframes draw { to { transform: scaleX(1); } }
        @keyframes word-up { to { transform: none; } }
        @keyframes shimmer { to { background-position: -200% 0; } }
        @keyframes billow {
            0%   { background-position: 0 0, 0 0;      background-size: 100% 100%, 6.4vw 100%; }
            50%  { background-position: 0 0, .9vw 0;   background-size: 100% 100%, 6.9vw 100%; }
            100% { background-position: 0 0, -.6vw 0;  background-size: 100% 100%, 6.1vw 100%; }
        }
        @keyframes ripple {
            0%   { background-position: 130% 0, 0 0; }
            70%, 100% { background-position: -30% 0, 0 0; }
        }
        @keyframes sweep { 0% { transform: translateX(-130%); } 40%, 100% { transform: translateX(130%); } }

        @media (prefers-reduced-motion: reduce) {
            .curtain, .ribbon { transition-duration: .8s; }
            .rays, .btn-cut::before, .btn-cut .sweep::after, .logo::before, .curtain, .curtain::after, .acronym .letter::after { animation: none; }
            .acronym .word .in { animation: word-up .01s forwards; }
        }
    </style>
</head>

<body>
<main class="stage" id="stage" data-state="closed">

    {{-- Behind the curtains --}}
    <div class="backdrop" aria-hidden="true"></div>
    <div class="rays" aria-hidden="true"></div>

    <section class="reveal" aria-live="polite">
        <h1 class="welcome" style="--d: .2s">Welcome <span>to</span></h1>

        <img class="logo-full" style="--d: .55s" src="{{ asset('media/logo-full.png') }}"
             alt="UGV RICH — Research, Innovation and Consultancy Hub" width="420" height="511">

        <div class="actions" style="--d: 1.4s">
            <button type="button" class="btn-lead" data-enter>
                Enter RICH
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
            </button>
            <span class="countdown" id="countdown" aria-live="off"></span>
        </div>
    </section>

    @if ($video)
        <section class="film" aria-label="RICH opening film">
            <video id="film" src="{{ $video }}" @if ($poster) poster="{{ $poster }}" @endif playsinline preload="auto"></video>
        </section>
    @endif

    {{-- The curtains --}}
    <div class="curtain curtain-left" aria-hidden="true"></div>
    <div class="curtain curtain-right" aria-hidden="true"></div>
    <canvas id="cloth" aria-hidden="true"></canvas>
    <canvas id="ribbon" aria-hidden="true"></canvas>
    <div class="valance" aria-hidden="true"></div>

    {{-- The closed stage --}}
    <section class="intro">
        <p class="signature"><span>University of Global Village</span></p>

        <div class="text">
            <span class="logo"><img src="{{ asset('media/logo-mark.png') }}" alt="UGV RICH" width="256" height="249"></span>
            <h1 class="acronym" aria-label="RICH — Research, Innovation and Consultancy Hub">
                @foreach ([['R', 'Research', 'pearl'], ['I', 'Innovation', 'pearl'], ['C', 'Consultancy', 'green'], ['H', 'Hub', 'green']] as $i => [$letter, $word, $tone])
                    <span class="col" aria-hidden="true">
                        <span class="letter {{ $tone }}" data-l="{{ $letter }}" style="--i: {{ $i }}">{{ $letter }}</span>
                        <span class="word" style="--i: {{ $i }}"><span class="in"><b>{{ $letter }}</b>{{ substr($word, 1) }}</span></span>
                    </span>
                @endforeach
            </h1>
        </div>

        <div class="cut">
            <span class="ribbon ribbon-left" aria-hidden="true"></span>
            <span class="ribbon ribbon-right" aria-hidden="true"></span>
            <button type="button" class="btn-lead btn-cut" id="inaugurate">
                <span class="sweep" aria-hidden="true"></span>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <circle cx="6" cy="6" r="3"/><circle cx="6" cy="18" r="3"/><path d="M20 4 8.12 15.88M14.47 14.48 20 20M8.12 8.12 12 12"/>
                </svg>
                Inaugurate RICH
            </button>
        </div>
    </section>

    <canvas id="fx" aria-hidden="true"></canvas>

    <div class="veil" aria-hidden="true">
        <img src="{{ asset('media/logo-full.png') }}" alt="" width="420" height="511">
    </div>

    <div class="controls">
        <button type="button" class="ctl" id="mute" aria-pressed="false" aria-label="Mute sound" hidden>
            <svg class="on" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M11 5 6 9H2v6h4l5 4V5Z"/><path d="M15.5 8.5a5 5 0 0 1 0 7M19 5a10 10 0 0 1 0 14"/></svg>
            <svg class="off" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M11 5 6 9H2v6h4l5 4V5Z"/><path d="m22 9-6 6M16 9l6 6"/></svg>
        </button>
    </div>
</main>

<script>
(() => {
    'use strict';

    const HOME = @json(route('home'));
    const HAS_VIDEO = @json((bool) $video);
    const AUTO_ENTER_SECONDS = 12;      // without a video, how long the unveiled stage stays before moving on

    const reduced = matchMedia('(prefers-reduced-motion: reduce)').matches;
    const rand = (a, b) => a + Math.random() * (b - a);
    const pick = list => list[Math.floor(Math.random() * list.length)];

    /* =====================================================================
     | Music — synthesised with the Web Audio API: a drum roll, a cymbal and
     | timpani as the ribbon falls, a brass fanfare as the curtains open, then
     | a bright loop. A recorded track at public/media/udbodhon.mp3 replaces
     | the loop. While the opening film plays, its own sound has the stage.
     * ===================================================================== */
    const Music = (() => {
        const TRACK = @json($track);
        const VOLUME = 0.55;
        let ctx, master, reverb, noiseBuffer, timer, track;
        let step = 0, nextTime = 0, muted = false;

        const hz = midi => 440 * Math.pow(2, (midi - 69) / 12);
        const level = () => (muted ? 0 : VOLUME);

        function init() {
            ctx = new (window.AudioContext || window.webkitAudioContext)();

            const compressor = ctx.createDynamicsCompressor();
            master = ctx.createGain();
            master.gain.value = VOLUME;
            master.connect(compressor).connect(ctx.destination);

            reverb = ctx.createConvolver();
            const length = ctx.sampleRate * 2.6;
            const impulse = ctx.createBuffer(2, length, ctx.sampleRate);
            for (let ch = 0; ch < 2; ch++) {
                const data = impulse.getChannelData(ch);
                for (let i = 0; i < length; i++) data[i] = (Math.random() * 2 - 1) * Math.pow(1 - i / length, 3);
            }
            reverb.buffer = impulse;
            const wet = ctx.createGain();
            wet.gain.value = 0.34;
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
            tone(t, hz(midi), 1.0, { gain, attack: 0.004, wet: 0.45 });
            tone(t, hz(midi) * 2.01, 0.45, { gain: gain * 0.3, attack: 0.002, wet: 0.45 });
            tone(t, hz(midi) * 3.0, 0.2, { type: 'triangle', gain: gain * 0.1, attack: 0.002, wet: 0.2 });
        }

        function kick(t, gain = 0.4) {
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
            noise(t, 2.6, { type: 'highpass', freq: 4500, gain: 0.3, wet: 0.5 });
            timpani(t);
        }

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
            noise(at, 2.2, { type: 'highpass', freq: 5000, gain: 0.2, wet: 0.5 });
            return at + 1.6;
        }

        /* The loop: eight bars, C – G – Am – F – C – G – F – G, at 108 bpm. */
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
        const EIGHTH = 60 / 108 / 2;
        const ARP = [0, 1, 2, 1, 0, 1, 2, 1];

        function playStep(s, t) {
            const bar = BARS[Math.floor(s / 8)];
            const e = s % 8;

            bell(t, bar.chord[ARP[e]] + (e >= 4 ? 12 : 0), 0.032);
            if (e % 2 === 0) {
                const note = bar.lead[e / 2];
                if (note) {
                    tone(t, hz(note), EIGHTH * 1.9, { type: 'triangle', gain: 0.07, attack: 0.02, wet: 0.4 });
                    tone(t, hz(note) * 2, EIGHTH * 1.2, { gain: 0.014, attack: 0.02, wet: 0.4 });
                }
            }
            if (e === 0 || e === 4) tone(t, hz(bar.bass), EIGHTH * 3.6, { type: 'triangle', gain: 0.15, attack: 0.01, wet: 0.05 });
            if (e === 0) for (const midi of bar.chord) tone(t, hz(midi - 12), EIGHTH * 8, { type: 'sawtooth', gain: 0.013, attack: 0.3, wet: 0.55, cutoff: 1100 });

            if (e === 0 || e === 4) kick(t);
            if (e === 2 || e === 6) noise(t, 0.16, { type: 'bandpass', freq: 1800, q: 0.9, gain: 0.1, wet: 0.25 });
            if (e % 2 === 1) noise(t, 0.04, { type: 'highpass', freq: 8000, gain: 0.04, wet: 0 });
        }

        function scheduler() {
            while (nextTime < ctx.currentTime + 0.15) {
                playStep(step, nextTime);
                nextTime += EIGHTH;
                step = (step + 1) % (BARS.length * 8);
            }
        }

        function apply(time = 0.15) {
            if (ctx) master.gain.setTargetAtTime(level(), ctx.currentTime, time);
            if (track) track.volume = level();
        }

        return {
            // The drum roll while the ribbon is cut, and the crash as it falls.
            start() {
                if (!ctx) init();
                ctx.resume();
                master.gain.cancelScheduledValues(ctx.currentTime);
                master.gain.setValueAtTime(level(), ctx.currentTime);

                const t = ctx.currentTime + 0.05;
                drumRoll(t, 1.45);
                crash(t + 1.5);
            },
            // The fanfare as RICH is unveiled, then the loop.
            celebrate() {
                if (!ctx || timer) return;

                // A recorded track brings its own opening, so it starts at once in
                // place of the generated fanfare and loop.
                if (track) {
                    track.currentTime = 0;
                    track.volume = level();
                    track.play().catch(() => {});
                    timer = 'track';
                    return;
                }

                step = 0;
                nextTime = fanfare(ctx.currentTime + 0.05) + 0.25;
                timer = setInterval(scheduler, 25);
            },
            fadeOut(seconds = 1.5) {
                if (!ctx) return;
                master.gain.setTargetAtTime(0, ctx.currentTime, seconds / 3);
                if (track) {
                    const from = track.volume;
                    const started = performance.now();
                    const fade = () => {
                        const p = Math.min(1, (performance.now() - started) / (seconds * 1000));
                        track.volume = from * (1 - p);
                        if (p < 1) requestAnimationFrame(fade);
                    };
                    fade();
                }
            },
            toggleMute() { muted = !muted; apply(0.1); return muted; },
            get muted() { return muted; },
        };
    })();

    /* =====================================================================
     | Effects — petals, marigolds, gold confetti, streamers, glitter bursts
     | and rising gold dust, on one canvas in front of the stage.
     * ===================================================================== */
    const FX = (() => {
        const canvas = document.getElementById('fx');
        const c = canvas.getContext('2d');
        const PAPER = ['#204520', '#316d31', '#41843f', '#8fbf8b', '#c3881a', '#e3ab2e', '#f2cd6b', '#f8e3a3', '#ffffff', '#5d9e59'];
        const PETAL = ['#ffffff', '#fff7ec', '#fde2e4', '#f9c6cf', '#f4a7b5', '#ffffff'];
        const MARIGOLD = [['#ffb703', '#e85d04'], ['#ffd166', '#f4a261'], ['#f6c343', '#d98b0b']];
        const SPARK = ['#e3ab2e', '#f2cd6b', '#c3881a', '#316d31', '#5d9e59', '#f8e3a3'];

        let particles = [];
        let W = 0, H = 0, density = 1, frame = 0, last = 0, clock = 0;
        let rain = 0, rainUntil = 0, drizzle = 0, burstsAt = 0, burstsFast = 0, dust = 0, active = false;

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
            paper: (x, y, vx, vy) => ({ k: 'paper', x, y, vx, vy, w: rand(7, 13), h: rand(4, 7), rot: rand(0, 6.3), vr: rand(-0.15, 0.15),
                tilt: rand(0, 6.3), vt: rand(0.06, 0.2), color: pick(PAPER), g: 0.11, drag: 0.983, max: 3.2, sway: rand(0.2, 0.8), phase: rand(0, 6.3) }),
            streamer: (x, y, vx, vy) => ({ k: 'streamer', x, y, vx, vy, len: rand(36, 70), rot: rand(0, 6.3), vr: rand(-0.06, 0.06),
                phase: rand(0, 6.3), color: pick(PAPER), g: 0.09, drag: 0.98, max: 2.5, sway: rand(0.3, 0.9) }),
            petal: (x, y, vx, vy) => ({ k: 'petal', x, y, vx, vy, s: rand(6, 11), rot: rand(0, 6.3), vr: rand(-0.05, 0.05),
                tilt: rand(0, 6.3), vt: rand(0.03, 0.09), color: pick(PETAL), g: 0.035, drag: 0.99, max: 1.7, sway: rand(0.6, 1.6), phase: rand(0, 6.3) }),
            marigold: (x, y, vx, vy) => { const [petal, core] = pick(MARIGOLD);
                return { k: 'marigold', x, y, vx, vy, r: rand(7, 11), rot: rand(0, 6.3), vr: rand(-0.04, 0.04),
                    petal, core, g: 0.04, drag: 0.99, max: 2.0, sway: rand(0.4, 1.1), phase: rand(0, 6.3) }; },
            spark: (x, y, vx, vy, color) => ({ k: 'spark', x, y, vx, vy, color, life: 1, decay: rand(0.012, 0.02), g: 0.03, drag: 0.972, max: 9, size: rand(1.6, 2.8) }),
            dust: () => ({ k: 'dust', x: rand(0, W), y: H + 10, vx: rand(-0.2, 0.2), vy: rand(-0.9, -0.4), g: 0, drag: 1, max: 9,
                size: rand(1.5, 3.5), phase: rand(0, 6.3), life: 1, decay: rand(0.002, 0.004), color: pick(['#e3ab2e', '#f2cd6b', '#c3881a']) }),
        };

        function piece(x, y, vx, vy, mix) {
            const r = Math.random();
            let acc = 0;
            for (const [kind, share] of mix) {
                acc += share;
                if (r < acc) return make[kind](x, y, vx, vy);
            }
            return make.paper(x, y, vx, vy);
        }

        function cannon(side, amount = 150) {
            const left = side === 'left';
            const n = Math.round(amount * density);
            const power = Math.max(0.75, H / 900);
            for (let i = 0; i < n; i++) {
                const angle = -rand(1.0, 1.35);
                const speed = rand(11, 23) * power;
                particles.push(piece(left ? -10 : W + 10, H + 10, Math.cos(angle) * speed * (left ? 1 : -1), Math.sin(angle) * speed,
                    [['paper', .55], ['streamer', .1], ['petal', .25], ['marigold', .1]]));
            }
        }

        function glitter(x = rand(W * 0.15, W * 0.85), y = rand(H * 0.15, H * 0.45)) {
            const n = Math.round(rand(55, 85) * (reduced ? 0.4 : 1));
            const a1 = pick(SPARK), a2 = pick(SPARK);
            for (let i = 0; i < n; i++) {
                const a = (i / n) * Math.PI * 2 + rand(-0.05, 0.05);
                const s = rand(2, 5.6);
                particles.push(make.spark(x, y, Math.cos(a) * s, Math.sin(a) * s, i % 3 ? a1 : a2));
            }
        }

        function spawnRain(rate) {
            for (let count = rate; count > 0; count -= 1) {
                if (Math.random() < count) {
                    particles.push(piece(rand(-20, W + 20), -24, rand(-0.6, 0.6), rand(0.5, 2), [['petal', .55], ['marigold', .13], ['paper', .32]]));
                }
            }
        }

        function update(f, now) {
            clock += f;
            if (now < rainUntil) spawnRain(rain * density * f);
            else if (drizzle) spawnRain(drizzle * density * f);
            if (dust && Math.random() < dust * f) particles.push(make.dust());

            if (burstsAt && now >= burstsAt) {
                glitter();
                burstsAt = now + (now < burstsFast ? rand(900, 1600) : rand(3200, 5200));
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
            particles = particles.filter(p => p.y < H + 60 && p.y > -H && p.x > -120 && p.x < W + 120 && (p.life === undefined || p.life > 0));
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
                        c.globalAlpha = 0.8 + 0.2 * Math.abs(flip);
                        c.fillStyle = p.color;
                        c.fillRect(-p.w / 2, -p.h / 2, p.w, p.h);
                        if (p.color === '#ffffff') { c.strokeStyle = 'rgba(195,136,26,.45)'; c.lineWidth = 0.8; c.strokeRect(-p.w / 2, -p.h / 2, p.w, p.h); }
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
                        c.strokeStyle = 'rgba(190,150,120,.35)';     // keeps white petals visible on ivory
                        c.lineWidth = 0.7;
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
                        c.globalAlpha = Math.max(0, p.life);
                        c.fillStyle = p.color;
                        c.beginPath();
                        c.arc(0, 0, p.size, 0, Math.PI * 2);
                        c.fill();
                        c.globalAlpha = Math.max(0, p.life) * 0.35;
                        c.beginPath();
                        c.arc(-p.vx * 1.6, -p.vy * 1.6, p.size * 0.75, 0, Math.PI * 2);
                        c.fill();
                        break;
                    }
                    case 'dust': {
                        const twinkle = 0.5 + 0.5 * Math.sin(clock * 0.15 + p.phase);
                        const s = p.size * (0.6 + twinkle * 0.6);
                        c.globalAlpha = Math.max(0, p.life) * (0.35 + twinkle * 0.65);
                        c.fillStyle = p.color;
                        c.beginPath();
                        c.moveTo(0, -s * 2); c.quadraticCurveTo(0, 0, s * 2, 0); c.quadraticCurveTo(0, 0, 0, s * 2);
                        c.quadraticCurveTo(0, 0, -s * 2, 0); c.quadraticCurveTo(0, 0, 0, -s * 2);
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
        const wake = () => { if (!frame) frame = requestAnimationFrame(loop); };

        return {
            celebrate() {
                const now = performance.now();
                active = true;
                cannon('left', 120);
                cannon('right', 120);
                setTimeout(() => { cannon('left'); cannon('right'); }, 900);
                rain = 0.8;
                rainUntil = now + 7000;
                drizzle = 0.3;
                dust = 0.25;
                burstsAt = now + 500;
                burstsFast = now + 9000;
                wake();
            },
            // While the film plays: only a few petals and gold dust, nothing to cover the logo.
            drift() {
                active = true;
                drizzle = 0.1;
                dust = 0.18;
                wake();
            },
            finale() {
                const now = performance.now();
                cannon('left', 200);
                cannon('right', 200);
                rain = 3.2;
                rainUntil = now + 4000;
                for (let i = 0; i < 3; i++) setTimeout(() => glitter(), i * 350);
                wake();
            },
        };
    })();

    /* =====================================================================
     | Cloth — the curtains as real hanging fabric (Verlet integration).
     |
     | Each curtain is a grid of points held together by links. The top row is
     | pinned under the valance; gravity and a slow breeze (ঢেউ) do the rest,
     | and a passing pointer pushes the velvet aside. Opening draws the pinned
     | row along its track to the side and the lower cloth with it, so the
     | curtain parts top to bottom together, swinging as it gathers.
     | Visitors who ask for less motion keep the still CSS curtains.
     * ===================================================================== */
    const Cloth = (() => {
        const canvas = document.getElementById('cloth');
        if (reduced || !canvas || !canvas.getContext) return null;
        const c = canvas.getContext('2d');

        // The site's navy, from the deepest fold to the lit crest of a fold
        const TONES = [[1, 12, 31], [2, 22, 52], [2, 34, 81], [15, 66, 128], [42, 95, 156], [96, 140, 196]];
        const GRAVITY = 0.32, DAMPING = 0.985, SOLVES = 4, STEP = 1000 / 60;
        const GATHER = 0.065;          // the share of the screen each curtain gathers into
        const MIN_GAP = 0.12;          // folds may bunch up, but never pass through each other
        const OPEN_MS = 3200, REACH = 110;

        let W, H, top, sheets = [], shade, sheen, hem;
        let openedAt = 0, last = 0, acc = 0, clock = 0;
        const pointer = { x: -1e4, y: -1e4, vx: 0, vy: 0, fresh: 0 };

        const tone = v => {
            v = Math.min(1, Math.max(0, v)) * (TONES.length - 1);
            const i = Math.min(TONES.length - 2, Math.floor(v)), f = v - i, a = TONES[i], b = TONES[i + 1];
            return `rgb(${(a[0] + (b[0] - a[0]) * f) | 0},${(a[1] + (b[1] - a[1]) * f) | 0},${(a[2] + (b[2] - a[2]) * f) | 0})`;
        };
        const ease = p => (p < 0.5 ? 4 * p * p * p : 1 - Math.pow(-2 * p + 2, 3) / 2);
        const gathered = (side, u) => (side === 'left' ? u * W * GATHER : W - (1 - u) * W * GATHER);

        function sheet(side, opened) {
            const half = W / 2 + 24;                     // the two curtains overlap where they meet
            let strips = Math.max(8, Math.round(half / (W < 700 ? 26 : 46)));
            strips += strips % 2;                        // two strips make one fold
            const rows = W < 700 ? 16 : 22;
            const x0 = side === 'left' ? 0 : W - half;
            const restX = half / strips, restY = (H - 2 - top) / (rows - 1);
            const pts = [];
            for (let r = 0; r < rows; r++) {
                for (let i = 0; i <= strips; i++) {
                    const u = i / strips, homeX = x0 + i * restX;
                    const x = opened ? gathered(side, u) : homeX, y = top + r * restY;
                    // The outer edge hangs against the frame, so it can swing back and forth but never inward
                    const edge = side === 'left' ? i === 0 : i === strips;
                    pts.push({ x, y, px: x, py: y, homeX, u, pin: r === 0, edge });
                }
            }
            return { side, strips, cols: strips + 1, rows, restX, restY, pts };
        }

        function build() {
            const dpr = Math.min(devicePixelRatio || 1, 1.5);
            W = innerWidth;
            H = innerHeight;
            canvas.width = W * dpr;
            canvas.height = H * dpr;
            c.setTransform(dpr, 0, 0, dpr, 0, 0);
            top = Math.max(64, Math.min(H * 0.125, 124)) - 14;     // hidden just under the valance
            sheets = [sheet('left', openedAt), sheet('right', openedAt)];

            shade = c.createLinearGradient(0, 0, 0, H);
            shade.addColorStop(0, 'rgba(0,0,0,.4)');
            shade.addColorStop(0.22, 'rgba(0,0,0,0)');
            shade.addColorStop(0.72, 'rgba(0,0,0,0)');
            shade.addColorStop(1, 'rgba(0,0,0,.55)');
            sheen = c.createRadialGradient(W / 2, H * 0.38, 0, W / 2, H * 0.38, W * 0.55);
            sheen.addColorStop(0, 'rgba(170,200,240,.11)');
            sheen.addColorStop(1, 'rgba(170,200,240,0)');
            hem = c.createLinearGradient(0, H - 16, 0, H);
            hem.addColorStop(0, '#f2cd6b');
            hem.addColorStop(1, '#c3881a');
        }

        function link(a, b, rest, min) {
            const dx = b.x - a.x, dy = b.y - a.y, d = Math.sqrt(dx * dx + dy * dy) || 0.001;
            let diff;
            if (d > rest) diff = (d - rest) / d;                    // the fabric will not stretch…
            else if (min && d < min) diff = (d - min) / d;          // …nor fold through itself
            else return;
            if (a.pin && b.pin) return;
            if (a.pin) { b.x -= dx * diff; b.y -= dy * diff; return; }
            if (b.pin) { a.x += dx * diff; a.y += dy * diff; return; }
            a.x += dx * diff * 0.5; a.y += dy * diff * 0.5;
            b.x -= dx * diff * 0.5; b.y -= dy * diff * 0.5;
        }

        function simulate() {
            clock += 1;
            const progress = openedAt ? ease(Math.min(1, (performance.now() - openedAt) / OPEN_MS)) : 0;
            const pushing = pointer.fresh > 0;

            for (const s of sheets) {
                for (const p of s.pts) {
                    const track = p.homeX + (gathered(s.side, p.u) - p.homeX) * progress;
                    if (p.pin) {        // the track: pinned points slide toward the side as it opens
                        p.x = p.px = track;
                        continue;
                    }
                    const vx = (p.x - p.px) * DAMPING, vy = (p.y - p.py) * DAMPING;
                    p.px = p.x;
                    p.py = p.y;
                    const depth = (p.y - top) / (H - top);
                    // ঢেউ: phased by position on screen, so both curtains move together where they meet
                    let fx = Math.sin(clock * 0.018 + p.y * 0.012 + p.homeX * 0.006) * 0.05 * depth;
                    let fy = 0;
                    // While opening, the lower cloth is drawn aside with the top, as if by tie-backs,
                    // so the curtain parts from top to bottom together instead of in a triangle
                    if (progress > 0 && progress < 1) fx += (track - p.x) * 0.035 * depth;
                    if (pushing) {
                        const dx = p.x - pointer.x, dy = p.y - pointer.y, d2 = dx * dx + dy * dy;
                        if (d2 < REACH * REACH) {
                            const f = 1 - Math.sqrt(d2) / REACH;
                            fx += Math.max(-1.6, Math.min(1.6, pointer.vx * 0.045 * f));
                            fy += Math.max(-1.2, Math.min(1.2, pointer.vy * 0.03 * f));
                        }
                    }
                    p.x += vx + fx;
                    p.y += vy + GRAVITY + fy;
                    if (p.y > H + 40) p.y = H + 40;
                    if (p.edge) p.x = p.px = track;
                }

                const { cols, rows, pts, restX, restY } = s;
                for (let k = 0; k < SOLVES; k++) {
                    for (let r = 0; r < rows; r++) {
                        for (let i = 0; i < cols; i++) {
                            const a = pts[r * cols + i];
                            if (i < cols - 1) link(a, pts[r * cols + i + 1], restX, restX * MIN_GAP);
                            if (r < rows - 1) link(a, pts[(r + 1) * cols + i], restY, 0);
                        }
                    }
                }
            }
            if (pointer.fresh > 0) pointer.fresh -= 1;
        }

        function draw() {
            c.clearRect(0, 0, W, H);
            for (const s of [sheets[1], sheets[0]]) {    // the left curtain falls over the right
                const { cols, rows, pts } = s, mid = rows >> 1, sign = s.side === 'left' ? 1 : -1;
                for (let i = 0; i < s.strips; i++) {
                    const ml = pts[mid * cols + i], mr = pts[mid * cols + i + 1];
                    const tl = pts[i], bl = pts[(rows - 1) * cols + i];
                    const bunched = Math.min(1.2, Math.abs(mr.x - ml.x) / s.restX);    // < 1 as the folds gather
                    const swing = ((bl.x - tl.x) / (H * 0.2)) * sign;                  // catching the light as it swings
                    const lift = Math.max(0.45, Math.min(1.08, 0.6 + 0.4 * bunched + 0.12 * swing));
                    const crest = 0.92 * lift, valley = 0.04;

                    const g = c.createLinearGradient(ml.x, ml.y, mr.x, mr.y);
                    if (i % 2 === 0) {
                        g.addColorStop(0, tone(valley)); g.addColorStop(0.55, tone(0.55 * lift));
                        g.addColorStop(0.85, tone(crest)); g.addColorStop(1, tone(crest * 0.9));
                    } else {
                        g.addColorStop(0, tone(crest * 0.9)); g.addColorStop(0.15, tone(crest));
                        g.addColorStop(0.45, tone(0.55 * lift)); g.addColorStop(1, tone(valley));
                    }

                    c.beginPath();
                    for (let r = 0; r < rows; r++) { const p = pts[r * cols + i]; r ? c.lineTo(p.x, p.y) : c.moveTo(p.x, p.y); }
                    for (let r = rows - 1; r >= 0; r--) { const p = pts[r * cols + i + 1]; c.lineTo(p.x, p.y); }
                    c.closePath();
                    c.fillStyle = g;
                    c.fill();
                    c.strokeStyle = g;          // closes the hairline seams between strips
                    c.lineWidth = 1;
                    c.stroke();
                }
            }

            // Light and shade, laid over the fabric only
            c.globalCompositeOperation = 'source-atop';
            c.fillStyle = shade;
            c.fillRect(0, 0, W, H);
            c.fillStyle = sheen;
            c.fillRect(0, 0, W, H);
            c.globalCompositeOperation = 'source-over';

            // The gold hem follows the moving bottom edge
            c.lineWidth = 12;
            c.lineCap = 'round';
            c.lineJoin = 'round';
            c.strokeStyle = hem;
            for (const s of [sheets[1], sheets[0]]) {
                c.beginPath();
                for (let i = 0; i < s.cols; i++) {
                    const p = s.pts[(s.rows - 1) * s.cols + i];
                    i ? c.lineTo(p.x, p.y - 5) : c.moveTo(p.x, p.y - 5);
                }
                c.stroke();
            }
        }

        function frame(now) {
            if (!last) last = now;
            acc += Math.min(100, now - last);
            last = now;
            while (acc >= STEP) { simulate(); acc -= STEP; }
            draw();
            requestAnimationFrame(frame);
        }

        addEventListener('pointermove', e => {
            if (pointer.fresh > 0) {
                pointer.vx = Math.max(-40, Math.min(40, e.clientX - pointer.x));
                pointer.vy = Math.max(-40, Math.min(40, e.clientY - pointer.y));
            } else {
                pointer.vx = pointer.vy = 0;
            }
            pointer.x = e.clientX;
            pointer.y = e.clientY;
            pointer.fresh = 6;
        }, { passive: true });

        let resizing;
        addEventListener('resize', () => { clearTimeout(resizing); resizing = setTimeout(build, 150); });

        build();
        document.getElementById('stage').classList.add('has-cloth');
        requestAnimationFrame(frame);

        return {
            open() { if (!openedAt) openedAt = performance.now(); },
            // The drum roll: the velvet trembles
            shiver(ms = 1400) {
                const end = performance.now() + ms;
                const kick = () => {
                    for (const s of sheets) for (const p of s.pts) if (!p.pin) p.px += rand(-1, 1) * 1.3;
                    if (performance.now() < end) setTimeout(kick, 90);
                };
                kick();
            },
        };
    })();

    /* =====================================================================
     | Ribbon (ফিতা) — a satin ribbon as a hanging rope of points.
     |
     | Tied at both sides of the screen and knotted under the button, it is
     | held perfectly straight, its satin twisting so it catches the light.
     | Cutting it lets both halves fall and swing from the sides as cloth. Without motion, the CSS
     | ribbon stays in its place.
     * ===================================================================== */
    const Ribbon = (() => {
        const canvas = document.getElementById('ribbon');
        const knot = document.querySelector('.cut');
        if (reduced || !canvas || !canvas.getContext || !knot) return null;
        const c = canvas.getContext('2d');
        const STEP = 1000 / 60, GRAVITY = 0.28, DAMPING = 0.985, SOLVES = 6;
        const WIDTH = 14;              // a slimmer ribbon than before

        let W, H, pts = [], rest = 0, cut = false, fading = 0, last = 0, acc = 0, clock = 0;

        function build() {
            const dpr = Math.min(devicePixelRatio || 1, 2);
            W = innerWidth;
            H = innerHeight;
            canvas.width = W * dpr;
            canvas.height = H * dpr;
            c.setTransform(dpr, 0, 0, dpr, 0, 0);

            const box = knot.getBoundingClientRect();
            const y = box.top + box.height / 2, cx = box.left + box.width / 2;
            const n = Math.max(30, Math.round(W / 24));
            rest = W / n;                                 // pulled taut: it runs perfectly straight
            pts = [];
            for (let i = 0; i <= n; i++) {
                const x = (i / n) * W;
                // Tied at both edges, and knotted under the button while it is whole
                const tied = i === 0 || i === n || (!cut && Math.abs(x - cx) < 70);
                pts.push({ x, y, px: x, py: y, pin: tied, edge: i === 0 || i === n });
            }
        }

        function simulate() {
            clock += 1;
            if (!cut) return;              // held taut until it is cut; only the satin shimmers
            for (const p of pts) {
                if (p.pin) continue;
                const vx = (p.x - p.px) * DAMPING, vy = (p.y - p.py) * DAMPING;
                p.px = p.x;
                p.py = p.y;
                const breeze = Math.sin(clock * 0.03 + p.x * 0.02) * 0.02;
                p.x += vx;
                p.y += vy + GRAVITY + breeze;
            }
            for (let k = 0; k < SOLVES; k++) {
                for (let i = 0; i < pts.length - 1; i++) {
                    if (cut && i === (pts.length >> 1) - 1) continue;          // the cut
                    const a = pts[i], b = pts[i + 1];
                    const dx = b.x - a.x, dy = b.y - a.y, d = Math.sqrt(dx * dx + dy * dy) || 0.001;
                    if (d <= rest) continue;
                    const diff = (d - rest) / d;
                    if (a.pin && b.pin) continue;
                    if (a.pin) { b.x -= dx * diff; b.y -= dy * diff; continue; }
                    if (b.pin) { a.x += dx * diff; a.y += dy * diff; continue; }
                    a.x += dx * diff * 0.5; a.y += dy * diff * 0.5;
                    b.x -= dx * diff * 0.5; b.y -= dy * diff * 0.5;
                }
            }
        }

        // Satin: the band narrows and brightens as it twists, with a gold selvedge on each side
        function draw() {
            c.clearRect(0, 0, W, H);
            if (fading >= 1) return;
            c.globalAlpha = 1 - fading;
            const mid = pts.length >> 1;
            for (let i = 0; i < pts.length - 1; i++) {
                if (cut && i === mid - 1) continue;
                const a = pts[i], b = pts[i + 1];
                const dx = b.x - a.x, dy = b.y - a.y, len = Math.sqrt(dx * dx + dy * dy) || 1;
                const nx = -dy / len, ny = dx / len;
                const twistA = Math.cos(i * 0.32 + clock * 0.02), twistB = Math.cos((i + 1) * 0.32 + clock * 0.02);
                const wa = WIDTH * (0.72 + 0.28 * Math.abs(twistA)) / 2, wb = WIDTH * (0.72 + 0.28 * Math.abs(twistB)) / 2;

                const g = c.createLinearGradient(a.x + nx * wa, a.y + ny * wa, a.x - nx * wa, a.y - ny * wa);
                const shine = 0.75 + 0.25 * twistA;
                g.addColorStop(0, '#c3881a');
                g.addColorStop(0.12, '#f2cd6b');
                g.addColorStop(0.16, `rgb(${222 * shine + 33 | 0},${230 * shine + 25 | 0},${222 * shine + 33 | 0})`);
                g.addColorStop(0.45, '#ffffff');
                g.addColorStop(0.7, `rgb(${215 * shine + 30 | 0},${224 * shine + 28 | 0},${213 * shine + 30 | 0})`);
                g.addColorStop(0.84, '#e9efe6');
                g.addColorStop(0.88, '#f2cd6b');
                g.addColorStop(1, '#c3881a');

                c.beginPath();
                c.moveTo(a.x + nx * wa, a.y + ny * wa);
                c.lineTo(b.x + nx * wb, b.y + ny * wb);
                c.lineTo(b.x - nx * wb, b.y - ny * wb);
                c.lineTo(a.x - nx * wa, a.y - ny * wa);
                c.closePath();
                c.fillStyle = g;
                c.fill();
                c.strokeStyle = g;
                c.lineWidth = 0.8;
                c.stroke();
            }
            c.globalAlpha = 1;
        }

        function frame(now) {
            if (!last) last = now;
            acc += Math.min(100, now - last);
            last = now;
            while (acc >= STEP) { simulate(); acc -= STEP; }
            draw();
            if (fading < 1) requestAnimationFrame(frame);
        }

        let resizing;
        addEventListener('resize', () => { clearTimeout(resizing); resizing = setTimeout(build, 150); });

        build();
        document.getElementById('stage').classList.add('has-ribbon');
        requestAnimationFrame(frame);

        return {
            // Snip: the knot lets go and each half falls and swings from its side
            cut() {
                cut = true;
                const mid = pts.length >> 1;
                for (const p of pts) if (!p.edge) p.pin = false;
                pts[mid - 1].px -= 4;
                pts[mid].px += 4;
            },
            fade(ms = 1200) {
                const start = performance.now();
                const step = () => {
                    fading = Math.min(1, (performance.now() - start) / ms);
                    if (fading < 1) requestAnimationFrame(step);
                };
                step();
            },
        };
    })();

    /* =====================================================================
     | The ceremony.
     * ===================================================================== */
    const stage = document.getElementById('stage');
    const button = document.getElementById('inaugurate');
    const mute = document.getElementById('mute');
    const film = document.getElementById('film');
    const countdown = document.getElementById('countdown');
    let leaving = false, unveiled = false, ticker = null;

    function startCountdown(seconds) {
        let left = seconds;
        const show = () => { countdown.textContent = `Entering RICH in ${left}s`; };
        show();
        ticker = setInterval(() => {
            left -= 1;
            if (left <= 0) { clearInterval(ticker); enter(); } else show();
        }, 1000);
    }

    // The curtains open on the logo film; when it ends (or cannot
    // play at all) the film fades away and RICH is unveiled on the stage.
    function playFilm() {
        stage.classList.add('is-playing');
        FX.drift();
        film.currentTime = 0;
        film.muted = Music.muted;
        film.play().catch(unveil);
        film.addEventListener('ended', unveil, { once: true });
        setTimeout(unveil, 40000);       // never leave the visitor waiting on a stalled video
    }

    function unveil() {
        if (unveiled || leaving) return;
        unveiled = true;
        if (film) film.pause();
        stage.classList.remove('is-playing');
        stage.classList.add('is-unveiled');
        try { Music.celebrate(); } catch (e) { /* no audio */ }
        FX.celebrate();
        setTimeout(() => { if (!leaving) startCountdown(AUTO_ENTER_SECONDS); }, 2600);
    }

    // The finale: one last shower of flowers, an ivory veil, and on to the home page —
    // where the flowers carry on falling (see partials/welcome-petals).
    function enter() {
        if (leaving) return;
        leaving = true;
        clearInterval(ticker);
        countdown.textContent = '';
        if (film) film.pause();
        Music.fadeOut(2.2);
        FX.finale();
        try { sessionStorage.setItem('rich-welcome', '1'); } catch (e) { /* the home page simply skips the shower */ }
        setTimeout(() => stage.classList.add('is-leaving'), 1300);
        setTimeout(() => { location.href = HOME; }, 3400);
    }

    button.addEventListener('click', () => {
        if (stage.dataset.state !== 'closed') return;
        stage.dataset.state = 'rolling';
        Cloth?.shiver();
        try { Music.start(); } catch (e) { /* no audio — the show goes on */ }
        mute.hidden = false;

        // Unlock the video for playback with sound while the click still counts
        // as a user gesture (iPhones insist on it).
        if (film) film.play().then(() => film.pause()).catch(() => {});

        setTimeout(() => { stage.dataset.state = 'cut'; Ribbon?.cut(); }, 1500);
        setTimeout(() => {
            stage.dataset.state = 'open';
            Cloth?.open();
            setTimeout(() => Ribbon?.fade(), 1400);
            if (HAS_VIDEO) playFilm(); else unveil();
        }, 1900);
    });

    document.querySelectorAll('[data-enter]').forEach(el => el.addEventListener('click', enter));

    mute.addEventListener('click', () => {
        const isMuted = Music.toggleMute();
        if (film) film.muted = isMuted;
        mute.setAttribute('aria-pressed', String(isMuted));
        mute.setAttribute('aria-label', isMuted ? 'Turn sound on' : 'Mute sound');
    });
})();
</script>
</body>
</html>
