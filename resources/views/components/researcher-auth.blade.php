@props(['title', 'lead', 'consultancy' => false, 'wide' => false])

@php($portal = $consultancy ? 'UGV RICH · '.__('consultancy_engagement.wing') : __('researcher.portal'))
@php($compact = isset($options))
@php($contained = $compact || $wide)
@php($fullWidthOnMobile = isset($sidebar))

{{-- Registration and sign-in share one frame: the brand on the left, the form
     on the right, so the portal says where you are before it asks anything. --}}
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $title }} · {{ $portal }}</title>
    <link rel="icon" href="{{ asset('favicon.png') }}" type="image/png" sizes="64x64">
    {{ Vite::fonts(['plus-jakarta-sans', 'inter', 'noto-sans-bengali']) }}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body @class(['min-h-full font-sans text-ink-900 antialiased', 'login-background' => $compact, 'bg-ink-50' => $wide && ! isset($sidebar), 'bg-auth-canvas' => isset($sidebar), 'bg-white' => ! $contained])>
@if ($contained)
    <main @class(['mx-auto sm:px-8 sm:py-10 lg:py-14', 'px-4 py-6' => ! $fullWidthOnMobile, 'max-sm:pt-4' => $fullWidthOnMobile, 'flex min-h-svh w-full max-w-6xl flex-col justify-center' => $compact, 'max-w-7xl' => $wide])>
        <a href="{{ $consultancy ? route('experts.index') : route('research') }}" @class(['mb-5 inline-flex items-center gap-3 rounded-xl focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-brand-600 sm:mb-7', 'max-sm:mx-4 max-sm:mb-4' => $fullWidthOnMobile])>
            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border border-ink-100 bg-white p-2 shadow-sm">
                <img src="{{ asset('media/logo-mark.png') }}" alt="" width="256" height="249" class="h-full w-full object-contain">
            </span>
            <span class="font-display text-sm font-bold text-ink-950">{{ $portal }}</span>
        </a>
        <div @class([
            'bg-white',
            'rounded-3xl border border-ink-200 shadow-[0_24px_64px_-48px_rgba(7,20,38,0.45)]' => ! $fullWidthOnMobile,
            'sm:rounded-3xl sm:border sm:border-ink-200 sm:shadow-[0_24px_64px_-48px_rgba(7,20,38,0.45)]' => $fullWidthOnMobile,
            'overflow-hidden' => ! isset($sidebar),
            'grid lg:grid-cols-[minmax(0,0.75fr)_minmax(0,1.45fr)]' => isset($sidebar),
            'login-shell grid md:grid-cols-[minmax(0,1.02fr)_minmax(0,0.98fr)]' => $compact,
        ])>
@else
    <div class="grid min-h-screen lg:grid-cols-2">

    {{-- The side that says where you are --}}
    <div class="relative isolate hidden overflow-hidden bg-navy-700 p-12 text-white lg:flex lg:flex-col lg:justify-between">
        <div class="pointer-events-none absolute inset-0 -z-10 text-white grid-overlay opacity-[0.12]" aria-hidden="true"></div>
        <div class="pointer-events-none absolute -right-32 -top-32 -z-10 h-96 w-96 rounded-full bg-brand-600/30 blur-[110px]" aria-hidden="true"></div>

        <a href="{{ $consultancy ? route('experts.index') : route('research') }}" class="flex items-center gap-3">
            <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white p-2">
                <img src="{{ asset('media/logo-mark.png') }}" alt="" width="256" height="249" class="h-full w-full object-contain">
            </span>
            <span class="font-display text-[15px] font-bold !text-white">{{ $portal }}</span>
        </a>

        <div>
            <h2 class="max-w-md font-display text-[34px] font-bold leading-[1.1] !text-white">
                @if ($consultancy)
                    Join the consultancy expert network
                @else
                    {!! __('research_hub.hero.title') !!}
                @endif
            </h2>
            <p class="mt-5 max-w-sm text-[14.5px] leading-[1.85] text-white/65">{{ $consultancy ? 'Bring your professional experience to consultancy projects with UGV RICH.' : __('research_hub.hero.lead') }}</p>
        </div>

        <a href="{{ url('/admin/login') }}" class="inline-flex items-center gap-1.5 self-start text-[12.5px] text-white/50 transition-colors hover:text-white">
            {{ __('researcher.auth.staff_note') }}
            <x-ui-icon name="arrow-up-right" class="h-3.5 w-3.5" />
        </a>
    </div>
@endif

    {{ $sidebar ?? '' }}
    <div @class([
        'flex items-center justify-center',
        'px-5 py-9 sm:px-10 md:col-start-2 md:row-start-1 lg:px-14' => $compact,
        'p-0' => $wide,
        'min-w-0 items-start' => isset($sidebar),
        'px-5 py-12 sm:px-10' => ! $contained,
    ])>
        <div @class(['w-full', 'max-w-lg' => $consultancy && ! $wide, 'max-w-md' => ! $consultancy && ! $wide])>
            @unless ($contained)
            <a href="{{ $consultancy ? route('experts.index') : route('research') }}" class="mb-8 inline-flex items-center gap-2.5 lg:hidden">
                <img src="{{ asset('media/logo-mark.png') }}" alt="" width="256" height="249" class="h-10 w-10 object-contain">
                <span class="font-display text-[14px] font-bold text-ink-950">{{ $portal }}</span>
            </a>
            @endunless

            @isset($header)
                {{ $header }}
            @else
                <header @class(['border-b border-ink-100 bg-linear-to-b from-brand-50/80 to-white px-5 py-8 text-center sm:px-9 sm:py-11' => $wide])>
                    <h1 @class(['font-display text-3xl font-bold leading-tight tracking-tight text-ink-950', 'sm:text-4xl' => $contained])>{{ $title }}</h1>
                    <p @class(['mt-2 text-[14.5px] leading-relaxed text-ink-600', 'mx-auto max-w-xl sm:mt-3 sm:text-base' => $wide])>{{ $lead }}</p>
                </header>
            @endisset

            @if ($errors->any())
                <div @class(['mt-6 rounded-2xl border border-rose-200 bg-rose-50 p-4', 'mx-5 sm:mx-9' => $wide && ! $fullWidthOnMobile, 'mx-4 sm:mx-9 xl:mx-12' => $fullWidthOnMobile]) role="alert">
                    <ul class="space-y-1 text-[13.5px] leading-relaxed text-rose-800">
                        @foreach ($errors->all() as $message)
                            <li>{{ $message }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{ $slot }}
        </div>
    </div>
    @if ($contained)
        {{ $options ?? '' }}
        </div>
    </main>
    @else
        </div>
    @endif
</body>
</html>
