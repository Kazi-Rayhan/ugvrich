@props(['title', 'description', 'variant'])

<header class="section-intro section-intro--{{ $variant }}">
    <div class="section-intro-copy">
        @isset($breadcrumb){{ $breadcrumb }}@endisset
        <h1>{{ $title }}</h1>
        @if (filled($description))<p>{{ $description }}</p>@endif
        @isset($actions)<div class="section-intro-actions">{{ $actions }}</div>@endisset
    </div>
    <svg class="section-vector" width="228" height="126" viewBox="0 0 228 126" fill="none" aria-hidden="true">
        <path d="M26 33h165M26 63h165M26 93h165M53 15v98m29-98v98m29-98v98m29-98v98m29-98v98" stroke="currentColor" opacity=".16"/>
        <path d="M34 86h-8m4-4v8m169-51h-8m4-4v8m-27 70h-8m4-4v8" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
        @if ($variant === 'orders')
            <rect x="66" y="27" width="96" height="78" rx="9" transform="rotate(-11 66 27)" fill="#C4D9BB" stroke="#5F886A"/>
            <rect x="82" y="19" width="91" height="86" rx="9" fill="#FCFEF9" stroke="#5F886A" stroke-width="1.5"/>
            <path d="M82 39h91" stroke="#5F886A"/>
            <path d="M94 30h24m-24 22h43m-43 14h54m-54 14h36" stroke="#345D45" stroke-width="2.5" stroke-linecap="round"/>
            <rect x="145" y="68" width="48" height="35" rx="7" fill="#264F3B"/>
            <path d="m159 86 6 6 12-13" stroke="#F2F8E9" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
        @elseif ($variant === 'profile')
            <rect x="65" y="26" width="99" height="72" rx="9" transform="rotate(-10 65 26)" fill="#E0D7B9" stroke="#97865F"/>
            <rect x="77" y="23" width="111" height="76" rx="9" fill="#FFFEF9" stroke="#97865F" stroke-width="1.5"/>
            <rect x="88" y="37" width="38" height="46" rx="6" fill="#EAE1C6"/>
            <circle cx="107" cy="52" r="6.5" stroke="#6E603D" stroke-width="1.7"/>
            <path d="M97 73a10 10 0 0 1 20 0" stroke="#6E603D" stroke-width="1.7" stroke-linecap="round"/>
            <path d="M137 45h31m-31 11h23m-23 14h32m-32 10h17" stroke="#8F8056" stroke-width="2.4" stroke-linecap="round"/>
            <circle cx="184" cy="98" r="14" fill="#775D34"/>
            <path d="m179 98 3.5 3.5 6.5-7" stroke="#FFF9EA" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        @else
            <circle cx="123" cy="62" r="46" fill="#D6E3EB" stroke="#819EA9"/>
            <path d="M123 19 160 33v25c0 23-14 39-37 49-23-10-37-26-37-49V33l37-14Z" fill="#F8FCFE" stroke="#416B7C" stroke-width="1.6"/>
            <path d="M123 28 151 39v19c0 18-10 31-28 40-18-9-28-22-28-40V39l28-11Z" stroke="#ADC5CE"/>
            <rect x="111" y="54" width="24" height="21" rx="4" fill="#315E70"/>
            <path d="M116 54v-5a7 7 0 1 1 14 0v5" stroke="#315E70" stroke-width="2" stroke-linecap="round"/>
            <path d="M123 63v4" stroke="#D9E8ED" stroke-width="2" stroke-linecap="round"/>
            <circle cx="168" cy="88" r="13" fill="#315E70"/>
            <path d="m163 88 3 3 6-6" stroke="#F8FCFE" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        @endif
    </svg>
</header>
