{{-- The close of the home page: one clear next step, on the dark ground the
     page opened with. Copy follows the existing "Work with UGV RICH" message. --}}
<section class="relative isolate overflow-hidden bg-navy-800 py-24 text-white sm:py-32">
    <div class="pointer-events-none absolute inset-0 -z-10 text-white grid-overlay opacity-[0.07] [mask-image:radial-gradient(ellipse_at_center,black,transparent_70%)]" aria-hidden="true"></div>
    <div class="pointer-events-none absolute left-1/2 top-full -z-10 h-[38rem] w-[38rem] -translate-x-1/2 -translate-y-1/2 rounded-full bg-brand-500/25 blur-[140px]" aria-hidden="true"></div>
    <div class="pointer-events-none absolute -right-24 -top-24 -z-10 h-72 w-72 rounded-full bg-gold-300/10 blur-[110px]" aria-hidden="true"></div>
    <div class="pointer-events-none absolute left-1/2 top-1/2 -z-10 h-[34rem] w-[34rem] -translate-x-1/2 -translate-y-1/2 rounded-full border border-white/[0.06]" aria-hidden="true"></div>
    <div class="pointer-events-none absolute left-1/2 top-1/2 -z-10 h-[22rem] w-[22rem] -translate-x-1/2 -translate-y-1/2 rounded-full border border-white/[0.06]" aria-hidden="true"></div>

    <div class="container-rich text-center">
        <p class="reveal eyebrow-invert">{{ __('site.home.cta_eyebrow') }}</p>

        <h2 data-split class="reveal mx-auto mt-7 max-w-3xl font-display text-[36px] font-bold leading-[1.05] tracking-[-0.03em] !text-white sm:text-[58px]">
            {!! __('site.home.cta_title') !!}
        </h2>

        <p class="reveal mx-auto mt-6 max-w-xl text-[17px] leading-[1.8] text-white/70">{{ __('site.home.cta_body') }}</p>

        <div class="reveal mt-10 flex flex-wrap items-center justify-center gap-3">
            <a href="{{ route('consultancy.create') }}" class="btn-lead group" data-magnetic>
                <span class="relative">{{ __('site.actions.request_consultancy') }}</span>
                <x-ui-icon name="arrow-right" class="relative h-[18px] w-[18px] transition-transform duration-300 group-hover:translate-x-1" />
            </a>
            <a href="{{ route('ideas.create') }}" class="btn-invert px-7 py-4 text-[15px]" data-magnetic>
                {{ __('site.actions.submit_idea') }}
            </a>
        </div>
    </div>
</section>
