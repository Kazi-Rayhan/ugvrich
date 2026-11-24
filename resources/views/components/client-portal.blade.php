@props(['client', 'title', 'section', 'description' => null])

@php
    $links = [
        'dashboard' => ['grid', 'Overview'],
        'orders' => ['briefcase', 'Orders'],
        'profile' => ['user', 'Profile'],
        'security' => ['shield', 'Security'],
    ];
@endphp

<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $title }} | UGV RICH Client Portal</title>
    <link rel="icon" href="{{ asset('favicon.png') }}">
    <link rel="stylesheet" href="{{ asset('css/client-portal.css') }}">
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
                @foreach ($links as $route => [$icon, $label])
                    <a href="{{ route('client.'.$route) }}" @if ($section === $route) aria-current="page" @endif>
                        <x-ui-icon :name="$icon" aria-hidden="true" />
                        {{ $label }}
                    </a>
                @endforeach
            </nav>
            <div class="sidebar-account">
                <div class="identity">
                    <span class="monogram" aria-hidden="true">{{ mb_strtoupper(mb_substr($client->name, 0, 1)) }}</span>
                    <div><strong>{{ $client->name }}</strong>@if ($client->organization)<small>{{ $client->organization }}</small>@endif</div>
                </div>
                <form method="post" action="{{ route('client.logout') }}">
                    @csrf
                    <button type="submit" class="sign-out">Sign out</button>
                </form>
            </div>
        </aside>
        <main id="client-content">
            <header class="page-heading">
                <h1>{{ $title }}</h1>
                @if ($description)<p>{{ $description }}</p>@endif
            </header>
            @if (session('status'))
                <p class="notice" role="status">{{ session('status') }}</p>
            @endif
            {{ $slot }}
        </main>
    </div>
</body>
</html>
