<style>
    .order-workspace {
        --order-panel: #fff;
        --order-soft: #f2f8f7;
        --order-border: #dbe6e5;
        --order-ink: #152d31;
        --order-muted: #50676a;
        --order-accent: #14776d;
        --order-accent-soft: #d8f1eb;
        --order-alert: #9b4d0d;
        color: var(--order-ink);
    }
    .dark .order-workspace {
        --order-panel: #172429;
        --order-soft: #1b3034;
        --order-border: #30464a;
        --order-ink: #e9f6f4;
        --order-muted: #adc7c4;
        --order-accent: #6dd6c2;
        --order-accent-soft: #233f41;
        --order-alert: #f9be78;
    }
    .order-overview {
        position: relative; isolation: isolate; overflow: hidden;
        border: 1px solid var(--order-border); border-radius: 1rem;
        background: var(--order-soft); padding: clamp(1rem, 3vw, 1.625rem);
    }
    .order-overview-header { display: flex; flex-wrap: wrap; align-items: center; gap: .65rem 1rem; margin-bottom: 1rem; }
    .order-overview h2 { font-size: 1.125rem; font-weight: 700; }
    .order-overview-facts { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: .75rem; max-width: 42rem; }
    .order-fact { display: flex; gap: .7rem; padding: .85rem; min-width: 0; background: var(--order-panel); border: 1px solid var(--order-border); border-radius: .75rem; }
    .order-fact-icon { width: 1.25rem; height: 1.25rem; flex: none; color: var(--order-accent); }
    .order-fact dt, .order-muted { font-size: .8125rem; line-height: 1.45; color: var(--order-muted); }
    .order-fact dd { font-weight: 650; overflow-wrap: anywhere; }
    .order-overview-art { display: none; position: absolute; bottom: 0; inset-inline-end: 1.65rem; z-index: -1; color: var(--order-accent); }
    .order-art-route { stroke-dasharray: 100; animation: order-route-reveal 700ms ease-out both; }
    .order-delivery-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(min(100%, 24rem), 1fr)); gap: 1rem; align-items: start; }
    .order-delivery-panel { min-width: 0; border: 1px solid var(--order-border); border-radius: .85rem; padding: 1rem; background: var(--order-panel); }
    .order-delivery-panel h3 { font-weight: 700; }
    .order-delivery-heading { display: flex; gap: .85rem; align-items: center; margin-bottom: .875rem; }
    .order-delivery-ring { width: 3rem; height: 3rem; flex: none; color: var(--order-accent); }
    .order-ring-value { animation: order-progress-reveal 600ms ease-out both; }
    .order-milestones { list-style: none; padding: 0; margin: 0; }
    .order-milestones li { position: relative; padding: .6rem 0 .6rem 1rem; border-inline-start: 1px solid var(--order-border); margin-inline-start: .26rem; }
    .order-milestones li::before { content: ""; position: absolute; inset-inline-start: -.27rem; top: 1rem; width: .5rem; height: .5rem; border-radius: 50%; border: 1.5px solid var(--order-accent); background: var(--order-panel); }
    .order-milestones li[data-complete="true"]::before { background: var(--order-accent); }
    .order-delivery-row { display: flex; align-items: start; justify-content: space-between; gap: .6rem 1rem; flex-wrap: wrap; }
    .order-delivery-row strong { overflow-wrap: anywhere; font-size: .875rem; }
    .order-pill { padding: .15rem .5rem; border: 1px solid var(--order-border); border-radius: 999px; font-size: .75rem; color: var(--order-muted); }
    .order-pill[data-complete="true"] { background: var(--order-accent-soft); color: var(--order-accent); border-color: transparent; }
    .order-expert { padding: .7rem 0; border-top: 1px solid var(--order-border); }
    .order-expert:first-of-type { margin-top: .65rem; }
    .order-amount { font-variant-numeric: tabular-nums; }
    .order-credit-warning { color: var(--order-alert); }
    .order-delivery-empty { padding: .8rem; background: var(--order-soft); border-radius: .55rem; margin-top: .65rem; }
    @media (min-width: 1100px) {
        .order-overview-art { display: block; }
        .order-overview-facts { max-width: min(42rem, calc(100% - 15rem)); }
    }
    @media (max-width: 520px) {
        .order-overview-header { flex-direction: column; align-items: flex-start; }
        .order-overview-facts { grid-template-columns: 1fr; }
    }
    @keyframes order-route-reveal { from { stroke-dashoffset: 100; } to { stroke-dashoffset: 0; } }
    @keyframes order-progress-reveal { from { stroke-dasharray: 0 100; } }
    @media (prefers-reduced-motion: reduce) {
        .order-art-route, .order-ring-value { animation: none; }
    }
</style>
