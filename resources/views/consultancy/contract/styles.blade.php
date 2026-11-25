<style>
    .mou-print-page { margin: 0; background: #edf1f4; }
    .mou-tools {
        display: flex; justify-content: flex-end; flex-wrap: wrap; gap: .5rem;
        max-width: 816px; margin: 0 auto; padding: .75rem 0;
        font: 600 .875rem/1.4 system-ui, sans-serif;
    }
    .mou-tools a, .mou-tools button {
        border: 1px solid #0e3158; padding: .55rem .85rem; color: #0e3158;
        background: #fff; text-decoration: none; cursor: pointer;
    }
    .mou-tools .mou-download { background: #0e3158; color: #fff; }
    .mou-document {
        --mou-navy: #0e3158; --mou-green: #17793d; --mou-gold: #c49838;
        position: relative; isolation: isolate; box-sizing: border-box;
        width: 100%; max-width: 816px; margin: 0 auto;
        padding: 64px clamp(30px, 8.15%, 67px) 72px;
        background: #fff; color: #171c21;
        font: 16px/1.58 "Noto Sans Bengali", "Times New Roman", serif;
        overflow-wrap: anywhere;
    }
    @media screen {
        .mou-document {
            border-block: 16px solid var(--mou-navy);
            box-shadow: inset 0 4px var(--mou-green), inset 0 6px var(--mou-gold),
                inset 0 -4px var(--mou-green), inset 0 -6px var(--mou-gold);
        }
        .mou-document::before {
            content: ""; position: absolute; inset: 9px clamp(12px, 4%, 32px);
            z-index: -1; border: 1px solid var(--mou-gold); outline: 2px solid var(--mou-navy);
            outline-offset: 3px;
        }
    }
    .mou-letterhead { position: relative; text-align: center; margin-bottom: 7px; }
    .mou-letterhead img { position: absolute; inset: 0 auto auto -12px; width: 78px; height: 78px; object-fit: contain; }
    .mou-letterhead p, .mou-letterhead h1 { margin: 0; font-weight: 700; line-height: 1.35; }
    .mou-letterhead .mou-institution { color: var(--mou-navy); font-size: 1.375em; margin: 0 0 .8em 76px; }
    .mou-letterhead .mou-wing, .mou-letterhead .mou-subtitle { color: var(--mou-green); font-size: 1.065em; }
    .mou-letterhead h1 { color: var(--mou-navy); font-size: 1.24em; margin: .85em 0 .5em; }
    .mou-letterhead .mou-subtitle { padding-bottom: .5em; border-bottom: 3px double var(--mou-gold); }
    .mou-parties { width: 100%; border: 1.5px solid var(--mou-navy); border-collapse: collapse; font-size: .94em; line-height: 1.28; }
    .mou-parties th, .mou-parties td { border: 1px solid #4b5864; padding: .34em .5em; text-align: left; vertical-align: top; }
    .mou-parties th { border-left: .32em solid var(--mou-green); width: 30%; background: #d5e7f3; color: var(--mou-navy); }
    .mou-parties td { background: #f3f8f4; }
    .mou-parties tr:nth-child(even) td { background: #fafaf5; }
    .mou-line { display: inline-block; min-width: 46%; min-height: .75em; border-bottom: 1px solid #526571; }
    .mou-intro { margin: 1.5em 0 .75em; text-align: center; font-style: italic; }
    .mou-section-title {
        margin: 0; padding: .2em .45em; background: #e3f0f8; color: var(--mou-navy);
        border-left: .32em solid var(--mou-green); border-bottom: 1px solid var(--mou-gold);
        font-size: 1.04em; font-weight: 700; line-height: 1.4; break-after: avoid;
    }
    .mou-clause { margin: 0 0 .72em; }
    .mou-clause-copy {
        margin: .4em 0 0; padding: 0 .55em; white-space: pre-line;
        widows: 2; orphans: 2;
    }
    .mou-clause-highlight .mou-clause-copy { background: #fcf5df; border-left: .32em solid var(--mou-gold); padding: .4em .55em; }
    .mou-schedule, .mou-signatures { margin-top: .9em; }
    .mou-panel { margin: .38em 0 0; padding: .46em .58em; border: 1px solid #b5cad9; border-left: .32em solid var(--mou-navy); background: #eaf3fa; break-inside: avoid; }
    .mou-panel-expert { background: #e8f4ea; border-left-color: var(--mou-green); }
    .mou-panel h3 { margin: 0 0 .3em; color: var(--mou-navy); font-size: 1em; font-weight: 700; }
    .mou-panel dl { margin: 0; }
    .mou-panel dl > div, dl.mou-panel > div { display: flex; align-items: baseline; flex-wrap: wrap; gap: 0 .4em; margin: .17em 0; }
    .mou-panel dd { margin: 0; min-width: min(9em, 50%); min-height: 1em; border-bottom: 1px solid #526571; }
    .mou-signature-image { display: block; width: auto; max-width: min(200px, 100%); max-height: 54px; object-fit: contain; }
    @media screen and (max-width: 560px) {
        .mou-document { padding: 24px 24px 34px; font-size: 14px; }
        .mou-letterhead img { position: static; display: block; width: 52px; height: 52px; margin: 0 auto .5em; }
        .mou-letterhead .mou-institution { margin-left: 0; }
        .mou-print-page .mou-tools { padding-inline: .75rem; }
    }
    @page {
        size: Letter;
        margin: 16mm 17.5mm;
        @top-left-corner { content: ""; margin-bottom: 7mm; background: #0e3158; border-bottom: 1mm solid #17793d; }
        @top-center { content: ""; margin-bottom: 7mm; background: #0e3158; border-bottom: 1mm solid #17793d; }
        @top-right-corner { content: ""; margin-bottom: 7mm; background: #0e3158; border-bottom: 1mm solid #17793d; }
        @bottom-left-corner { content: ""; margin-top: 7mm; background: #0e3158; border-top: 1mm solid #17793d; }
        @bottom-center { content: ""; margin-top: 7mm; background: #0e3158; border-top: 1mm solid #17793d; }
        @bottom-right-corner { content: ""; margin-top: 7mm; background: #0e3158; border-top: 1mm solid #17793d; }
        @left-middle { content: ""; margin: 0 7mm 0 8mm; border-left: 2px solid #0e3158; border-right: 1px solid #c49838; }
        @right-middle { content: ""; margin: 0 8mm 0 7mm; border-left: 1px solid #c49838; border-right: 2px solid #0e3158; }
    }
    @media print {
        html, body.mou-print-page { margin: 0; padding: 0; background: #fff; print-color-adjust: exact; -webkit-print-color-adjust: exact; }
        .mou-tools { display: none !important; }
        .mou-document { margin: 0; padding: 0; max-width: none; width: auto; font-size: 10.5pt; line-height: 1.55; print-color-adjust: exact; -webkit-print-color-adjust: exact; }
        .mou-letterhead img { left: 0; }
        .mou-letterhead, .mou-parties tr { break-inside: avoid; }
        .mou-clause { break-inside: avoid; }
        .mou-schedule { break-before: page; margin-top: 0; }
    }
</style>
