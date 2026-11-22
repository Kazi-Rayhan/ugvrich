@props(['application', 'page' => 'overview'])

@php
    $links = ['expert.dashboard' => ['grid', 'overview']];
    if ($application->signed_at) {
        $links += [
            'expert.profile.show' => ['user', 'profile'],
            'expert.portfolio.index' => ['document', 'portfolio'],
            'expert.contract.show' => ['briefcase', 'details'],
            'expert.wallet.show' => ['wallet', 'wallet'],
        ];
    } else {
        $links['expert.onboarding'] = ['shield', 'onboarding_nav'];
    }

    $profileName = $application->expert?->name ?? $application->name;
    $profilePhoto = $application->expert?->photo;
    $pageTitle = __('consultancy_engagement.'.$page);
    $localPath = preg_replace('#^en/#', '', request()->path());
@endphp

<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $pageTitle }} | UGV RICH</title>
    <link rel="icon" href="{{ asset('favicon.png') }}" type="image/png" sizes="64x64">
    {{ Vite::fonts(['plus-jakarta-sans', 'inter', 'noto-sans-bengali']) }}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#f4f7fa] font-sans text-ink-900 antialiased">
    <div class="grid min-h-screen lg:grid-cols-[15.5rem_minmax(0,1fr)] xl:grid-cols-[17rem_minmax(0,1fr)]">
        <aside class="flex min-w-0 flex-col border-b border-ink-200 bg-white px-4 py-4 lg:sticky lg:top-0 lg:h-svh lg:overflow-y-auto lg:border-r lg:border-b-0 lg:px-5 lg:py-6">
            <a href="{{ route('expert.dashboard') }}" class="flex items-center gap-3 rounded-xl px-1.5 py-1 focus-visible:outline-2 focus-visible:outline-teal-600">
                <span class="grid size-11 shrink-0 place-items-center rounded-xl border border-ink-100 bg-white p-2 shadow-sm">
                    <img src="{{ asset('media/logo-mark.png') }}" alt="" width="256" height="249" class="size-full object-contain">
                </span>
                <span class="min-w-0">
                    <span class="block font-display text-[14.5px] font-bold leading-tight text-ink-950">UGV RICH</span>
                    <span class="mt-1 block text-[12px] leading-normal text-ink-600">{{ __('consultancy_engagement.wing') }}</span>
                </span>
            </a>

            <nav id="expert-sidebar" class="mt-4 flex flex-wrap gap-1.5 lg:mt-9 lg:flex-col" aria-label="{{ __('consultancy_engagement.dashboard_navigation') }}">
                @foreach ($links as $routeName => [$icon, $label])
                    <a href="{{ route($routeName) }}" @if (request()->routeIs($routeName, 'en.'.$routeName)) aria-current="page" @endif
                       class="group flex items-center gap-3 rounded-xl border border-transparent px-3 py-2.5 text-[14px] font-medium text-ink-700 transition-colors hover:bg-ink-50 hover:text-ink-950 focus-visible:outline-2 focus-visible:outline-teal-600 aria-[current=page]:border-teal-100 aria-[current=page]:bg-teal-50 aria-[current=page]:text-teal-900 lg:px-3.5">
                        <x-ui-icon :name="$icon" class="size-[18px] shrink-0 text-ink-500 group-aria-[current=page]:text-teal-700" />
                        {{ __('consultancy_engagement.'.$label) }}
                    </a>
                @endforeach
            </nav>

            <div class="mt-3 flex flex-wrap items-center gap-3 border-t border-ink-100 pt-3 lg:mt-auto lg:block lg:pt-4">
                <div class="hidden min-w-0 items-center gap-3 rounded-xl bg-ink-50 p-2.5 lg:flex">
                    <span class="grid size-9 shrink-0 place-items-center overflow-hidden rounded-lg bg-teal-100 text-teal-800" aria-hidden="true">
                        @if ($profilePhoto)
                            <img src="{{ Storage::disk('public')->url($profilePhoto) }}" alt="" class="size-full object-cover">
                        @else
                            <x-ui-icon name="user" class="size-5" />
                        @endif
                    </span>
                    <span class="min-w-0 truncate text-[14px] font-bold text-ink-950">{{ $profileName }}</span>
                </div>
                @if (auth()->user()->isResearcher() || auth()->user()->isInnovator())
                    <a class="rounded-xl px-3 py-2 text-[13.5px] font-medium text-ink-600 hover:bg-ink-50 lg:mt-3 lg:block" href="{{ auth()->user()->isResearcher() ? route('researcher.dashboard') : route('innovator.dashboard') }}">
                        {{ __('consultancy_engagement.original_dashboard') }}
                    </a>
                @endif
                <form method="POST" action="{{ route('researcher.logout') }}" class="lg:mt-1">
                    @csrf
                    <button type="submit" class="flex items-center gap-2 rounded-xl px-3 py-2 text-[13.5px] font-medium text-ink-600 hover:bg-ink-50 focus-visible:outline-2 focus-visible:outline-teal-600 lg:w-full">
                        <x-ui-icon name="arrow-left" class="size-4" />
                        {{ __('consultancy_engagement.sign_out') }}
                    </button>
                </form>
            </div>
        </aside>

        <main class="min-w-0">
            <header class="border-b border-ink-200 bg-white">
                <div class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-4 px-5 py-5 sm:px-8 xl:px-10">
                    <h1 class="font-display text-[17px] font-bold text-ink-950 sm:text-[19px]">{{ $pageTitle }}</h1>
                    <a class="rounded-lg border border-ink-200 px-3 py-1.5 text-[13px] font-semibold text-ink-700 hover:bg-ink-50 focus-visible:outline-2 focus-visible:outline-teal-600"
                       lang="{{ app()->isLocale('bn') ? 'en' : 'bn' }}" href="{{ url((app()->isLocale('bn') ? '/en/' : '/').$localPath) }}">
                        {{ app()->isLocale('bn') ? 'English' : __('consultancy_engagement.language_bn') }}
                    </a>
                </div>
            </header>

            <div class="mx-auto max-w-7xl space-y-6 px-5 py-6 sm:px-8 sm:py-8 xl:px-10">
                @if (session('status'))
                    <p role="status" class="rounded-xl border border-emerald-100 bg-emerald-50 p-4 text-sm text-emerald-900">{{ session('status') }}</p>
                @endif

                @if ($errors->any())
                    <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800" role="alert">
                        <ul class="space-y-1">
                            @foreach ($errors->all() as $message)
                                <li>{{ $message }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{ $slot }}
            </div>
        </main>
    </div>

</body>
</html>
