@props(['client', 'title', 'section', 'description' => null])

@php
    $links = [
        'Workspace' => [
            'dashboard' => ['grid', 'Overview'],
            'orders' => ['briefcase', 'Orders'],
        ],
        'Account' => [
            'profile' => ['user', 'Profile'],
            'security' => ['shield', 'Security'],
        ],
    ];
@endphp

<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $title }} | UGV RICH Client Portal</title>
    <link rel="icon" href="{{ asset('favicon.png') }}">
    {{ Vite::fonts(['plus-jakarta-sans', 'inter', 'noto-sans-bengali']) }}
    @vite('resources/css/client-portal.css')
</head>
<body>
    <a class="skip-link" href="#client-content">Skip to content</a>
    <div class="portal">
        <aside class="sidebar">
            <a class="brand" href="{{ route('client.dashboard') }}">
                <img src="{{ asset('media/logo-mark.png') }}" width="38" height="38" alt="">
                <span><strong>UGV RICH</strong><small>Client Portal</small></span>
            </a>
            <nav class="navigation" aria-label="Client portal">
                @foreach ($links as $group => $items)
                    <div class="nav-group">
                        <span class="nav-label">{{ $group }}</span>
                        @foreach ($items as $route => [$icon, $label])
                            <a href="{{ route('client.'.$route) }}" @if ($section === $route) aria-current="page" @endif>
                                <span class="nav-icon"><x-ui-icon :name="$icon" width="18" height="18" /></span>
                                <span>{{ $label }}</span>
                                @if ($section === $route)<x-ui-icon name="chevron-right" class="nav-current" width="15" height="15" />@endif
                            </a>
                        @endforeach
                    </div>
                @endforeach
            </nav>
            <div class="sidebar-account">
                <div class="identity">
                    <span class="monogram" aria-hidden="true">{{ mb_strtoupper(mb_substr($client->name, 0, 1)) }}</span>
                    <div><strong>{{ $client->name }}</strong>@if ($client->organization)<small>{{ $client->organization }}</small>@endif</div>
                </div>
                <form method="post" action="{{ route('client.logout') }}">
                    @csrf
                    <button type="submit" class="sign-out">
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M11 4H6a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h5m4-12 4 4-4 4m-6-4h10"/></svg>
                        Sign out
                    </button>
                </form>
            </div>
        </aside>
        <main id="client-content">
            @isset($heading)
                {{ $heading }}
            @else
                <header class="page-heading">
                    <h1>{{ $title }}</h1>
                    @if ($description)<p>{{ $description }}</p>@endif
                </header>
            @endisset
            @if (session('status'))
                <p class="notice" role="status">{{ session('status') }}</p>
            @endif
            {{ $slot }}
        </main>
    </div>
</body>
</html>
