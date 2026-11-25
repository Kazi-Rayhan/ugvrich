<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ __('expert_mou.title') }} | UGV RICH</title>
    @if (isset($fontCss))
        <style>{!! $fontCss !!}</style>
    @else
        {{ Vite::fonts(['noto-sans-bengali']) }}
    @endif
    @include('consultancy.contract.styles')
</head>
<body class="mou-print-page">
    @unless ($forPdf ?? false)
        <div class="mou-tools">
            <a href="{{ route(app()->isLocale('en') ? 'en.expert.contract.show' : 'expert.contract.show') }}">{{ __('expert_mou.back') }}</a>
            <button class="mou-download" type="button" onclick="document.fonts.ready.then(() => window.print())">{{ __('expert_mou.print_pdf') }}</button>
        </div>
    @endunless
    @include('consultancy.contract.document')
</body>
</html>
