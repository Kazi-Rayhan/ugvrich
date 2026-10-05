<x-layouts.app :description="$site->get('hero_subheading')">
    {{-- Rhythm: dark hero → light numbers → dark wings → light about and partners → dark close. --}}
    @include('partials.home.hero')
    @include('partials.home.dashboard')
    @include('partials.home.wings')
    @include('partials.home.about')
    @include('partials.home.marquee', ['subdued' => true])
    @include('partials.home.cta')

    {{-- Flowers that follow a visitor here from the inauguration at /udbodhon --}}
    @include('partials.welcome-petals')
</x-layouts.app>
