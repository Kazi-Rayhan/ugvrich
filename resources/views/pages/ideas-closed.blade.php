<x-layouts.app :title="__('site.ideas.closed_title')"
               :description="__('site.ideas.meta_description')">

    <x-page-hero
        :eyebrow="__('site.ideas.hero_eyebrow')"
        :title="__('site.ideas.closed_title')"
        :lead="__('site.ideas.closed_body')"
        :breadcrumbs="[__('site.nav.startup') => route('startup'), __('site.actions.submit_idea') => null]" />

    <section class="bg-white py-14 sm:py-16">
        <div class="container-rich flex justify-center">
            <a href="{{ route('startup') }}" class="btn-primary">
                {{ __('site.nav.startup') }}
                <x-ui-icon name="arrow-right" class="h-4 w-4" />
            </a>
        </div>
    </section>
</x-layouts.app>
