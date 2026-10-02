@props(['status', 'labels' => []])

@php
    /* One badge for every workflow status in the portal, so an idea, a proposal
       and a paper all read the same way. Colour carries the meaning: grey is
       not started, blue is with someone else, amber wants something from you,
       green is settled, rose is the end of the road. */
    $tone = match ($status) {
        'draft' => 'bg-ink-100 text-ink-600',
        'submitted', 'under_review', 'initial_review' => 'bg-sky-50 text-sky-700 ring-1 ring-sky-200',
        'revision_required' => 'bg-amber-50 text-amber-800 ring-1 ring-amber-200',
        'approved', 'accepted', 'published', 'funded' => 'bg-brand-50 text-brand-800 ring-1 ring-brand-200',
        'proposal_development' => 'bg-gold-100 text-gold-700 ring-1 ring-gold-200',
        'rejected', 'unfunded' => 'bg-rose-50 text-rose-700 ring-1 ring-rose-200',
        default => 'bg-ink-100 text-ink-600',
    };
@endphp

<span {{ $attributes->merge(['class' => "inline-flex shrink-0 items-center gap-1.5 rounded-full px-2.5 py-1 text-[11.5px] font-semibold $tone"]) }}>
    {{ $labels[$status] ?? \Illuminate\Support\Str::headline($status) }}
</span>
