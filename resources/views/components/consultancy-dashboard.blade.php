@props(['application', 'page' => 'overview', 'fullWidthOnMobile' => false])

@php
    $links = ['expert.dashboard' => ['grid', 'overview']];
    $canWorkOnOrders = $application->canWorkOnOrders();
    if ($canWorkOnOrders) {
        $links['expert.orders.index'] = ['briefcase', 'assignments'];
    }
    if ($application->signed_at) {
        $links += [
            'expert.profile.show' => ['user', 'profile'],
            'expert.portfolio.index' => ['document', 'portfolio'],
        ];
    } else {
        $links['expert.onboarding'] = ['shield', 'onboarding_nav'];
    }
    $walletActive = request()->routeIs('expert.wallet.*', 'en.expert.wallet.*');
    $contractActive = request()->routeIs('expert.contract.show', 'en.expert.contract.show');
    $portfolioActive = request()->routeIs('expert.portfolio.index', 'en.expert.portfolio.index');
    $isCurrent = fn ($routeName) => request()->routeIs($routeName, 'en.'.$routeName)
        || ($routeName === 'expert.orders.index' && request()->routeIs('expert.orders.*', 'en.expert.orders.*'));
    $walletLinks = ['show' => __('expert_wallet.overview'), 'methods' => __('consultancy_engagement.wallet_methods'), 'withdrawals' => __('expert_wallet.withdrawals')];
    $originalDashboard = match (true) {
        auth()->user()->isResearcher() => route('researcher.dashboard'),
        auth()->user()->isInnovator() => route('innovator.dashboard'),
        default => null,
    };
    $linkClasses = 'group flex items-center gap-2.5 rounded-lg px-3 py-2 text-[14px] font-medium text-ink-700 hover:bg-ink-50 focus-visible:outline-2 focus-visible:outline-teal-600 aria-[current=page]:bg-teal-50 aria-[current=page]:font-semibold aria-[current=page]:text-teal-900';

    $profileName = $application->expert?->name ?? $application->name;
    $profilePhoto = $application->expert?->photo;
    $pageTitle = __('consultancy_engagement.'.$page);
    $localPath = preg_replace('#^en/#', '', request()->path());
@endphp

<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $pageTitle }} | UGV RICH</title>
    <link rel="icon" href="{{ asset('favicon.png') }}" type="image/png" sizes="64x64">
    {{ Vite::fonts(['plus-jakarta-sans', 'inter', 'noto-sans-bengali']) }}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .expert-mobile-nav, .expert-mobile-mark { display:none; }
        #expert-mobile-menu:not(:popover-open) { display:none; }
        @media (max-width:63.999rem) {
            .expert-shell { --expert-nav-height:calc(4.5rem + env(safe-area-inset-bottom)); }
            .expert-sidebar { display:none; }
            .expert-main { padding-bottom:var(--expert-nav-height); }
            .expert-main .expert-content-full-width { padding-inline:0; }
            .expert-topbar { padding-top:env(safe-area-inset-top); }
            .expert-mobile-mark { display:block; width:28px; height:28px; object-fit:contain; }
            .expert-mobile-nav {
                display:grid; grid-auto-flow:column; grid-auto-columns:minmax(0,1fr); gap:2px;
                position:fixed; z-index:40; inset:auto 0 0; min-height:var(--expert-nav-height);
                padding:5px max(8px,env(safe-area-inset-right)) calc(5px + env(safe-area-inset-bottom)) max(8px,env(safe-area-inset-left));
                background:#fff; border-top:1px solid #dce5ec;
            }
            .expert-nav-item {
                min-height:52px; display:flex; flex-direction:column; align-items:center; justify-content:center; gap:3px;
                padding:3px 1px; border:0; border-radius:9px; background:transparent; color:#46596d; cursor:pointer;
                font-size:.69rem; font-weight:650; line-height:1.15; text-align:center; overflow-wrap:anywhere;
            }
            .expert-nav-item svg { display:block; width:19px; height:19px; }
            .expert-nav-icon { display:grid; place-items:center; width:44px; height:26px; border-radius:20px; }
            .expert-nav-item:hover, .expert-nav-item[aria-current="page"], .expert-nav-item.is-active { color:#105c53; }
            .expert-nav-item[aria-current="page"] .expert-nav-icon, .expert-nav-item.is-active .expert-nav-icon,
            .expert-mobile-nav:has(#expert-mobile-menu:popover-open) .expert-nav-toggle .expert-nav-icon { background:#e2f3ed; }
            .expert-nav-item:focus-visible, #expert-mobile-menu button:focus-visible, #expert-mobile-menu a:focus-visible { outline:2px solid #13786b; outline-offset:1px; }
            #expert-mobile-menu {
                position:fixed; inset:auto max(12px,env(safe-area-inset-right)) calc(var(--expert-nav-height) + 8px) auto;
                margin:0; width:min(22rem,calc(100vw - 24px)); max-height:calc(100dvh - var(--expert-nav-height) - 24px);
                overflow:auto; padding:12px; border:1px solid #dce5ec; border-radius:15px;
                background:#fff; color:#12253a; box-shadow:0 10px 32px #12253a28;
            }
            .expert-mobile-menu-heading { display:flex; justify-content:space-between; align-items:center; gap:12px; padding:0 3px 7px 10px; font-weight:800; }
            .expert-mobile-menu-close { display:grid; place-items:center; width:38px; height:38px; border-radius:8px; cursor:pointer; }
            .expert-mobile-menu-close:hover { background:#e2f3ed; }
            .expert-mobile-menu-links > * + * { margin-top:2px; }
        }
        @media (min-width:64rem) { #expert-mobile-menu { display:none; } }
    </style>
</head>
<body class="min-h-screen bg-[#f4f7fa] font-sans text-ink-900 antialiased">
    <div class="expert-shell grid min-h-screen lg:grid-cols-[15.5rem_minmax(0,1fr)] xl:grid-cols-[17rem_minmax(0,1fr)]">
        <aside class="expert-sidebar flex min-w-0 flex-col border-b border-ink-200 bg-white px-4 py-4 lg:sticky lg:top-0 lg:h-svh lg:overflow-y-auto lg:border-r lg:border-b-0 lg:px-5 lg:py-6">
            <a href="{{ route('expert.dashboard') }}" class="flex items-center gap-3 rounded-xl px-1.5 py-1 focus-visible:outline-2 focus-visible:outline-teal-600">
                <span class="grid size-11 shrink-0 place-items-center rounded-xl border border-ink-100 bg-white p-2 shadow-sm">
                    <img src="{{ asset('media/logo-mark.png') }}" alt="" width="256" height="249" class="size-full object-contain">
                </span>
                <span class="min-w-0">
                    <span class="block font-display text-[14.5px] font-bold leading-tight text-ink-950">UGV RICH</span>
                    <span class="mt-1 block text-[12px] leading-normal text-ink-600">{{ __('consultancy_engagement.wing') }}</span>
                </span>
            </a>

            <nav id="expert-sidebar" class="mt-4 space-y-3 lg:mt-8 lg:space-y-5" aria-label="{{ __('consultancy_engagement.dashboard_navigation') }}">
                <div class="space-y-1">
                    <h2 class="px-3 text-[12px] font-semibold text-ink-600">{{ __('consultancy_engagement.nav_workspace') }}</h2>
                    <div class="flex flex-wrap gap-1 lg:flex-col">
                        @foreach ($links as $routeName => [$icon, $label])
                            <a href="{{ route($routeName) }}" @if ($isCurrent($routeName)) aria-current="page" @endif class="{{ $linkClasses }}">
                                <x-ui-icon :name="$icon" class="size-[18px] shrink-0 text-ink-500 group-aria-[current=page]:text-teal-700" />
                                {{ __('consultancy_engagement.'.$label) }}
                            </a>
                        @endforeach
                    </div>
                </div>

                @if ($canWorkOnOrders)
                    <div class="space-y-1 border-t border-ink-100 pt-3 lg:pt-4">
                        <h2 class="px-3 text-[12px] font-semibold text-ink-600">{{ __('consultancy_engagement.nav_engagement') }}</h2>
                        @if ($application->signed_at)
                        <a href="{{ route('expert.contract.show') }}" @if ($contractActive) aria-current="page" @endif class="{{ $linkClasses }}">
                            <x-ui-icon name="briefcase" class="size-[18px] shrink-0 text-ink-500 group-aria-[current=page]:text-teal-700" />
                            {{ __('consultancy_engagement.details') }}
                        </a>
                        @endif
                        <details @if ($walletActive) open @endif class="group/wallet">
                            <summary @class([$linkClasses, 'cursor-pointer list-none [&::-webkit-details-marker]:hidden', 'bg-teal-50 text-teal-900' => $walletActive])>
                                <x-ui-icon name="wallet" class="size-[18px] shrink-0 text-teal-700" />
                                <span class="flex-1">{{ __('consultancy_engagement.wallet') }}</span>
                                <x-ui-icon name="chevron-down" class="size-4 shrink-0 text-ink-500 group-open/wallet:rotate-180" />
                            </summary>
                            <ul class="mt-1 ml-5 space-y-0.5 border-l border-ink-200 pl-2">
                                @foreach ($walletLinks as $key => $label)
                                    <li>
                                        <a href="{{ route('expert.wallet.'.$key) }}" @if (request()->routeIs('expert.wallet.'.$key, 'en.expert.wallet.'.$key)) aria-current="page" @endif class="{{ $linkClasses }}">
                                            {{ $label }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </details>
                    </div>
                @endif
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
                @if ($originalDashboard)
                    <a class="rounded-xl px-3 py-2 text-[13.5px] font-medium text-ink-600 hover:bg-ink-50 lg:mt-3 lg:block" href="{{ $originalDashboard }}">
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

        <main class="expert-main min-w-0">
            <header class="expert-topbar border-b border-ink-200 bg-white">
                <div class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-4 px-5 py-5 sm:px-8 xl:px-10">
                    <div class="flex min-w-0 items-center gap-2.5">
                        <img src="{{ asset('media/logo-mark.png') }}" alt="" width="28" height="28" class="expert-mobile-mark shrink-0">
                        <h1 class="font-display text-[17px] font-bold text-ink-950 sm:text-[19px]">{{ $pageTitle }}</h1>
                    </div>
                    <a class="rounded-lg border border-ink-200 px-3 py-1.5 text-[13px] font-semibold text-ink-700 hover:bg-ink-50 focus-visible:outline-2 focus-visible:outline-teal-600"
                       lang="{{ app()->isLocale('bn') ? 'en' : 'bn' }}" href="{{ url((app()->isLocale('bn') ? '/en/' : '/').$localPath) }}">
                        {{ app()->isLocale('bn') ? 'English' : __('consultancy_engagement.language_bn') }}
                    </a>
                </div>
            </header>

            <div @class(['mx-auto max-w-7xl space-y-6 px-5 py-6 sm:px-8 sm:py-8 xl:px-10', 'expert-content-full-width' => $fullWidthOnMobile])>
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

        <nav class="expert-mobile-nav" aria-label="{{ __('consultancy_engagement.dashboard_navigation') }}">
            @foreach ($links as $routeName => [$icon, $label])
                @continue($routeName === 'expert.portfolio.index')
                <a class="expert-nav-item" href="{{ route($routeName) }}" @if ($isCurrent($routeName)) aria-current="page" @endif>
                    <span class="expert-nav-icon"><x-ui-icon :name="$icon" /></span>
                    <span>{{ __('consultancy_engagement.mobile_nav.'.$label) }}</span>
                </a>
            @endforeach
            <button type="button" @class(['expert-nav-item expert-nav-toggle', 'is-active' => $walletActive || $contractActive || $portfolioActive]) popovertarget="expert-mobile-menu">
                <span class="expert-nav-icon"><x-ui-icon name="menu" /></span>
                <span>{{ __('consultancy_engagement.mobile_nav.menu') }}</span>
            </button>

            <div id="expert-mobile-menu" popover aria-labelledby="expert-mobile-menu-title">
                <div class="expert-mobile-menu-heading">
                    <span id="expert-mobile-menu-title">{{ __('consultancy_engagement.mobile_nav.menu') }}</span>
                    <button type="button" class="expert-mobile-menu-close" popovertarget="expert-mobile-menu" popovertargetaction="hide" aria-label="{{ __('consultancy_engagement.mobile_nav.close') }}">
                        <x-ui-icon name="x" class="size-5" />
                    </button>
                </div>
                <div class="expert-mobile-menu-links">
                    @if ($application->signed_at)
                        <a href="{{ route('expert.portfolio.index') }}" @if ($portfolioActive) aria-current="page" @endif class="{{ $linkClasses }}">
                            <x-ui-icon name="document" class="size-5 shrink-0" /> {{ __('consultancy_engagement.portfolio') }}
                        </a>
                        <a href="{{ route('expert.contract.show') }}" @if ($contractActive) aria-current="page" @endif class="{{ $linkClasses }}">
                            <x-ui-icon name="briefcase" class="size-5 shrink-0" /> {{ __('consultancy_engagement.details') }}
                        </a>
                    @endif
                    @if ($canWorkOnOrders)
                        @foreach ($walletLinks as $key => $label)
                            @php($routeName = 'expert.wallet.'.$key)
                            <a href="{{ route($routeName) }}" @if ($isCurrent($routeName)) aria-current="page" @endif class="{{ $linkClasses }}">
                                <x-ui-icon name="wallet" class="size-5 shrink-0" /> {{ $label }}
                            </a>
                        @endforeach
                    @endif
                    @if ($originalDashboard)
                        <a class="{{ $linkClasses }}" href="{{ $originalDashboard }}"><x-ui-icon name="grid" class="size-5 shrink-0" /> {{ __('consultancy_engagement.original_dashboard') }}</a>
                    @endif
                    <form method="POST" action="{{ route('researcher.logout') }}">
                        @csrf
                        <button type="submit" class="{{ $linkClasses }} w-full cursor-pointer text-left"><x-ui-icon name="arrow-left" class="size-5 shrink-0" /> {{ __('consultancy_engagement.sign_out') }}</button>
                    </form>
                </div>
            </div>
        </nav>
    </div>

</body>
</html>
