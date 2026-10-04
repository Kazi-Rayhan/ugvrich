@props(['title', 'lead'])

{{-- Registration and sign-in share one frame: the brand on the left, the form
     on the right, so the portal says where you are before it asks anything. --}}
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $title }} · {{ __('researcher.portal') }}</title>
    <link rel="icon" href="{{ asset('favicon.png') }}" type="image/png" sizes="64x64">
    {{ Vite::fonts(['plus-jakarta-sans', 'inter', 'noto-sans-bengali']) }}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-full bg-white font-sans text-ink-900 antialiased">
<div class="grid min-h-screen lg:grid-cols-2">

    {{-- The side that says where you are --}}
    <div class="relative isolate hidden overflow-hidden bg-navy-700 p-12 text-white lg:flex lg:flex-col lg:justify-between">
        <div class="pointer-events-none absolute inset-0 -z-10 text-white grid-overlay opacity-[0.12]" aria-hidden="true"></div>
        <div class="pointer-events-none absolute -right-32 -top-32 -z-10 h-96 w-96 rounded-full bg-brand-600/30 blur-[110px]" aria-hidden="true"></div>

        <a href="{{ route('research') }}" class="flex items-center gap-3">
            <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white p-2">
                <img src="{{ asset('media/logo-mark.png') }}" alt="" width="256" height="249" class="h-full w-full object-contain">
            </span>
            <span class="font-display text-[15px] font-bold !text-white">{{ __('researcher.portal') }}</span>
        </a>

        <div>
            <h2 class="max-w-md font-display text-[34px] font-bold leading-[1.1] !text-white">
                {!! __('research_hub.hero.title') !!}
            </h2>
            <p class="mt-5 max-w-sm text-[14.5px] leading-[1.85] text-white/65">{{ __('research_hub.hero.lead') }}</p>
        </div>

        <p class="text-[12.5px] text-white/40">{{ __('researcher.auth.staff_note') }}</p>
    </div>

    {{-- The side that asks --}}
    <div class="flex items-center justify-center px-5 py-12 sm:px-10">
        <div class="w-full max-w-md">
            <a href="{{ route('research') }}" class="mb-8 inline-flex items-center gap-2.5 lg:hidden">
                <img src="{{ asset('media/logo-mark.png') }}" alt="" width="256" height="249" class="h-10 w-10 object-contain">
                <span class="font-display text-[14px] font-bold text-ink-950">{{ __('researcher.portal') }}</span>
            </a>

            <h1 class="font-display text-[28px] font-bold leading-tight tracking-tight text-ink-950 sm:text-[32px]">{{ $title }}</h1>
            <p class="mt-3 text-[14.5px] leading-[1.8] text-ink-600">{{ $lead }}</p>

            @if ($errors->any())
                <div class="mt-7 rounded-2xl border border-rose-200 bg-rose-50 p-4" role="alert">
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
</div>
</body>
</html>
