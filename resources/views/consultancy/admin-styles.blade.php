@once
    <style>
        .consultancy-admin { display: grid; gap: 1.25rem; }
        .consultancy-grid { display: grid; gap: 1.25rem; align-items: start; }
        .consultancy-hero {
            display: flex; flex-wrap: wrap; align-items: center; gap: 1rem;
            border-radius: 1rem; padding: 1.4rem; color: #fff;
            background: radial-gradient(ellipse at 100% 0%, #3730a3, transparent 65%), linear-gradient(125deg, #14284e, #172554);
        }
        .consultancy-hero h2 { color: #fff; font-size: clamp(1.5rem, 4vw, 2.125rem); font-weight: 750; line-height: 1.15; overflow-wrap: anywhere; }
        .consultancy-hero p { color: #e0e7ff; margin-top: .35rem; }
        .consultancy-hero .consultancy-status { margin-left: auto; }
        .consultancy-avatar {
            display: grid; place-items: center; flex: 0 0 3.5rem; width: 3.5rem; height: 3.5rem;
            border-radius: .85rem; background: #e0e7ff; color: #312e81; font-weight: 750; overflow: hidden;
        }
        .consultancy-avatar img { width: 100%; height: 100%; object-fit: cover; }
        .consultancy-fields { display: grid; grid-template-columns: repeat(auto-fit, minmax(min(100%, 11rem), 1fr)); gap: 1rem; }
        .consultancy-fields dt, .consultancy-muted { color: #475569; font-size: .875rem; }
        .consultancy-fields dd { margin-top: .2rem; overflow-wrap: anywhere; font-weight: 550; }
        .consultancy-entries > li + li { border-top: 1px solid #e2e8f0; margin-top: .7rem; padding-top: .7rem; }
        .consultancy-entry-title { font-weight: 650; overflow-wrap: anywhere; }
        .consultancy-stage { display: grid; grid-template-columns: auto minmax(0, 1fr); align-items: start; gap: .75rem; }
        .consultancy-stage + .consultancy-stage { border-top: 1px solid #e2e8f0; margin-top: .9rem; padding-top: .9rem; }
        .consultancy-stage-number { display: grid; place-items: center; width: 1.8rem; height: 1.8rem; border-radius: .5rem; background: #eef2ff; color: #3730a3; font-weight: 750; }
        .consultancy-stage-meta { display: flex; flex-wrap: wrap; align-items: center; gap: .5rem; }
        .consultancy-note { margin-top: .55rem; border-radius: .5rem; background: #f1f5f9; padding: .65rem; font-size: .875rem; white-space: pre-wrap; overflow-wrap: anywhere; }
        .consultancy-link { color: #3730a3; text-decoration: underline; text-underline-offset: 3px; }
        .consultancy-chain-hero {
            background: radial-gradient(ellipse at 0% 100%, #155e75, transparent 65%), linear-gradient(110deg, #162b4e, #373080);
            padding: clamp(1.45rem, 4vw, 2.35rem);
        }
        .consultancy-chain-grid { align-items: stretch; }
        .consultancy-chain-card {
            --stage-accent: #0f766e;
            --stage-tint: #ecfdf5;
            overflow: hidden; border: 1px solid #e2e8f0; border-radius: 1rem;
            background: #fff; box-shadow: 0 12px 28px -24px #243657;
        }
        .consultancy-chain-card:nth-child(2) { --stage-accent: #4f46e5; --stage-tint: #eef2ff; }
        .consultancy-chain-card-header, .consultancy-chain-card-toolbar, .consultancy-chain-stage {
            display: flex; align-items: center; flex-wrap: wrap; gap: .75rem;
        }
        .consultancy-chain-card-header, .consultancy-chain-card-toolbar { justify-content: space-between; }
        .consultancy-chain-card-header { padding: 1.1rem 1.25rem; border-top: 3px solid var(--stage-accent); background: var(--stage-tint); }
        .consultancy-chain-card-header h2 { font-size: 1.15rem; font-weight: 750; }
        .consultancy-chain-stage .consultancy-stage-number { width: 2.3rem; height: 2.3rem; background: var(--stage-accent); color: #fff; }
        .consultancy-chain-card-body { padding: 1.1rem 1.25rem 1.25rem; }
        .consultancy-chain-card-toolbar h3 { font-size: .95rem; font-weight: 700; }
        .consultancy-chain-users { display: grid; gap: .45rem; margin-top: .85rem; }
        .consultancy-chain-users li { display: flex; align-items: center; gap: .65rem; min-width: 0; overflow-wrap: anywhere; padding: .55rem .65rem; border-radius: .6rem; background: #f8fafc; }
        .consultancy-chain-users .consultancy-chain-empty { border: 1px dashed #cbd5e1; padding: .75rem; }
        .consultancy-chain-user-icon { display: grid; flex: 0 0 2rem; width: 2rem; height: 2rem; place-items: center; border-radius: .5rem; background: var(--stage-tint); color: var(--stage-accent); }
        .consultancy-chain-user-action { margin-left: auto; flex: 0 0 auto; }
        .dark .consultancy-muted, .dark .consultancy-fields dt { color: #cbd5e1; }
        .dark .consultancy-entries > li + li, .dark .consultancy-stage + .consultancy-stage { border-color: #374151; }
        .dark .consultancy-note { background: #272f3e; }
        .dark .consultancy-link { color: #c7d2fe; }
        .dark .consultancy-chain-card { background: #111827; border-color: #374151; --stage-tint: #142e32; }
        .dark .consultancy-chain-card:nth-child(2) { --stage-tint: #25254a; }
        .dark .consultancy-chain-users li { background: #1f2937; }
        .dark .consultancy-chain-user-icon { color: #c7d2fe; }
        @media (min-width: 980px) {
            .consultancy-grid { grid-template-columns: minmax(0, 3fr) minmax(0, 2fr); }
            .consultancy-grid--equal { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }
    </style>
@endonce
