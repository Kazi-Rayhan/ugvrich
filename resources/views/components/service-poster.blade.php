@props([
    'sector' => null,   // CSE | EEE | CE | ME | BUS | ENG
    'seed' => '',
])

@php
    /*
     * A drawn poster for each consultancy sector.
     *
     * The generated blueprint art says "no image yet"; this says what the
     * sector does. Six flat scenes — screens for ICT, panels and sun for
     * electrical, a site for infrastructure, gears and a drawing for
     * mechanical, ledgers and growth for business, a book and letters for
     * language — drawn in the site's own greens and navy.
     *
     * Inline SVG, so it costs no request, scales to any size and stays crisp
     * on a phone. A photograph uploaded to the record replaces it entirely.
     */
    $brand50 = '#f1f8f0';
    $brand100 = '#ddeeda';
    $brand200 = '#bddcb9';
    $brand300 = '#8fbf8b';
    $brand500 = '#41843f';
    $brand600 = '#316d31';
    $brand700 = '#275727';
    $navy = '#022251';
    $navy500 = '#2a5f9c';
    $navy100 = '#dbe7f5';
    $paper = '#ffffff';
    $ink = '#071426';
    $sand = '#e8c07a';

    $id = 'poster-'.substr(md5($sector.$seed), 0, 8);
@endphp

<svg {{ $attributes->merge(['class' => 'h-full w-full']) }}
     viewBox="0 0 480 330" preserveAspectRatio="xMidYMid slice" role="img"
     aria-label="{{ $sector }}" fill="none" xmlns="http://www.w3.org/2000/svg">

    <defs>
        <linearGradient id="{{ $id }}-bg" x1="0" y1="0" x2="1" y2="1">
            <stop offset="0" stop-color="{{ $brand50 }}" />
            <stop offset="1" stop-color="{{ $navy100 }}" />
        </linearGradient>
        <clipPath id="{{ $id }}-clip"><rect width="480" height="330" /></clipPath>
    </defs>

    <g clip-path="url(#{{ $id }}-clip)">
        <rect width="480" height="330" fill="url(#{{ $id }}-bg)" />

        {{-- A soft disc behind the scene, so every poster shares one silhouette --}}
        <circle cx="392" cy="58" r="108" fill="{{ $brand100 }}" opacity="0.75" />
        <circle cx="70" cy="292" r="86" fill="{{ $brand200 }}" opacity="0.5" />

        @switch($sector)

            {{-- ------------------------------------------------ CSE: screens --}}
            @case('CSE')
                @foreach ([[54, 62], [190, 62], [326, 62], [54, 170], [190, 170], [326, 170]] as $i => [$x, $y])
                    <g transform="translate({{ $x }} {{ $y }})">
                        <rect width="100" height="74" rx="7" fill="{{ $paper }}" stroke="{{ $navy }}" stroke-width="3" />
                        <rect x="0" y="0" width="100" height="16" rx="7" fill="{{ $i % 2 ? $brand600 : $navy }}" />
                        <circle cx="12" cy="8" r="2.6" fill="{{ $paper }}" opacity="0.9" />
                        <circle cx="21" cy="8" r="2.6" fill="{{ $paper }}" opacity="0.55" />
                        @if ($i === 4)
                            <circle cx="50" cy="45" r="14" fill="{{ $brand100 }}" />
                            <path d="M46 39l12 6-12 6z" fill="{{ $brand600 }}" />
                        @elseif ($i === 1)
                            <rect x="12" y="26" width="34" height="34" rx="4" fill="{{ $brand200 }}" />
                            <rect x="54" y="26" width="34" height="8" rx="4" fill="{{ $navy100 }}" />
                            <rect x="54" y="40" width="34" height="8" rx="4" fill="{{ $navy100 }}" />
                            <rect x="54" y="54" width="22" height="6" rx="3" fill="{{ $brand300 }}" />
                        @else
                            <rect x="12" y="26" width="76" height="7" rx="3.5" fill="{{ $navy100 }}" />
                            <rect x="12" y="39" width="58" height="7" rx="3.5" fill="{{ $navy100 }}" />
                            <rect x="12" y="52" width="40" height="7" rx="3.5" fill="{{ $brand300 }}" />
                        @endif
                        <path d="M44 74h12v8h10" stroke="{{ $navy }}" stroke-width="3" stroke-linecap="round" />
                    </g>
                @endforeach
                @break

            {{-- --------------------------------------- EEE: sun, panels, meter --}}
            @case('EEE')
                <circle cx="372" cy="74" r="34" fill="{{ $sand }}" />
                @for ($a = 0; $a < 8; $a++)
                    <line x1="{{ 372 + 46 * cos(deg2rad($a * 45)) }}" y1="{{ 74 + 46 * sin(deg2rad($a * 45)) }}"
                          x2="{{ 372 + 60 * cos(deg2rad($a * 45)) }}" y2="{{ 74 + 60 * sin(deg2rad($a * 45)) }}"
                          stroke="{{ $sand }}" stroke-width="5" stroke-linecap="round" />
                @endfor

                {{-- Two solar arrays on stands --}}
                @foreach ([[40, 150], [212, 178]] as $k => [$x, $y])
                    <g transform="translate({{ $x }} {{ $y }})">
                        <path d="M0 62 L34 0 H186 L152 62 Z" fill="{{ $navy500 }}" stroke="{{ $navy }}" stroke-width="3" stroke-linejoin="round" />
                        @for ($c = 1; $c < 4; $c++)
                            <line x1="{{ $c * 38 }}" y1="62" x2="{{ $c * 38 + 34 }}" y2="0" stroke="{{ $navy100 }}" stroke-width="2.5" />
                        @endfor
                        <line x1="17" y1="31" x2="169" y2="31" stroke="{{ $navy100 }}" stroke-width="2.5" />
                        <rect x="84" y="62" width="9" height="34" fill="{{ $navy }}" />
                        <rect x="66" y="94" width="46" height="7" rx="3.5" fill="{{ $navy }}" />
                    </g>
                @endforeach

                {{-- Meter with a bolt --}}
                <g transform="translate(330 166)">
                    <rect width="104" height="126" rx="12" fill="{{ $paper }}" stroke="{{ $navy }}" stroke-width="3" />
                    <rect x="14" y="16" width="76" height="40" rx="6" fill="{{ $brand50 }}" stroke="{{ $brand300 }}" stroke-width="2" />
                    <path d="M56 22l-16 22h14l-4 16 16-22H52z" fill="{{ $brand600 }}" />
                    @foreach ([0, 1, 2] as $r)
                        <rect x="14" y="{{ 70 + $r * 18 }}" width="{{ 76 - $r * 18 }}" height="8" rx="4" fill="{{ $navy100 }}" />
                    @endforeach
                </g>
                @break

            {{-- ------------------------------ CE: buildings, crane, blueprint --}}
            @case('CE')
                <rect x="0" y="252" width="480" height="78" fill="{{ $brand200 }}" opacity="0.7" />

                {{-- Crane --}}
                <g stroke="{{ $navy }}" stroke-width="4" stroke-linecap="round">
                    <line x1="96" y1="252" x2="96" y2="48" />
                    <line x1="40" y1="60" x2="250" y2="60" />
                    <line x1="96" y1="48" x2="40" y2="60" />
                    <line x1="96" y1="48" x2="250" y2="60" />
                    <line x1="196" y1="60" x2="196" y2="120" />
                </g>
                <rect x="180" y="120" width="34" height="26" rx="4" fill="{{ $sand }}" stroke="{{ $navy }}" stroke-width="3" />

                {{-- Buildings --}}
                @foreach ([[248, 128, 78, 124, $navy500], [332, 96, 62, 156, $navy], [400, 158, 58, 94, $brand600]] as [$x, $y, $w, $h, $fill])
                    <rect x="{{ $x }}" y="{{ $y }}" width="{{ $w }}" height="{{ $h }}" rx="4" fill="{{ $fill }}" />
                    @for ($r = 0; $r < intdiv($h, 26); $r++)
                        @for ($c = 0; $c < intdiv($w, 22); $c++)
                            <rect x="{{ $x + 10 + $c * 22 }}" y="{{ $y + 14 + $r * 26 }}" width="11" height="13" rx="2"
                                  fill="{{ $paper }}" opacity="{{ ($r + $c) % 3 ? 0.85 : 0.35 }}" />
                        @endfor
                    @endfor
                @endforeach

                {{-- A drawing, half unrolled --}}
                <g transform="translate(36 186)">
                    <rect width="150" height="96" rx="8" fill="{{ $paper }}" stroke="{{ $navy }}" stroke-width="3" />
                    <path d="M22 76V38h40v38M62 38l22-16 22 16v38" stroke="{{ $brand600 }}" stroke-width="3" fill="none" stroke-linejoin="round" />
                    <line x1="14" y1="76" x2="136" y2="76" stroke="{{ $navy500 }}" stroke-width="2.5" />
                    <line x1="14" y1="86" x2="86" y2="86" stroke="{{ $navy100 }}" stroke-width="4" stroke-linecap="round" />
                </g>
                @break

            {{-- -------------------------------- ME: gears and a CAD drawing --}}
            @case('ME')
                @php
                    $gear = function (float $cx, float $cy, float $r, int $teeth, string $fill) {
                        $path = '';
                        for ($i = 0; $i < $teeth; $i++) {
                            $a0 = deg2rad($i * 360 / $teeth);
                            $a1 = deg2rad(($i + 0.28) * 360 / $teeth);
                            $a2 = deg2rad(($i + 0.5) * 360 / $teeth);
                            $a3 = deg2rad(($i + 0.78) * 360 / $teeth);
                            $out = $r * 1.28;
                            $path .= ($i ? 'L' : 'M').round($cx + $r * cos($a0), 1).' '.round($cy + $r * sin($a0), 1)
                                .'L'.round($cx + $out * cos($a1), 1).' '.round($cy + $out * sin($a1), 1)
                                .'L'.round($cx + $out * cos($a2), 1).' '.round($cy + $out * sin($a2), 1)
                                .'L'.round($cx + $r * cos($a3), 1).' '.round($cy + $r * sin($a3), 1);
                        }

                        return $path.'Z';
                    };
                @endphp

                <path d="{{ $gear(346, 108, 58, 12, $navy) }}" fill="{{ $navy }}" />
                <circle cx="346" cy="108" r="22" fill="{{ $brand50 }}" />
                <path d="{{ $gear(408, 196, 36, 10, $brand600) }}" fill="{{ $brand600 }}" />
                <circle cx="408" cy="196" r="14" fill="{{ $brand50 }}" />

                {{-- The drawing sheet --}}
                <g transform="translate(36 78)">
                    <rect width="234" height="176" rx="10" fill="{{ $paper }}" stroke="{{ $navy }}" stroke-width="3" />
                    <line x1="0" y1="140" x2="234" y2="140" stroke="{{ $navy100 }}" stroke-width="2.5" />
                    <rect x="150" y="148" width="72" height="18" rx="4" fill="{{ $navy100 }}" />

                    {{-- An orthographic part: plan, elevation, and a section --}}
                    <rect x="26" y="28" width="82" height="58" rx="6" fill="{{ $brand50 }}" stroke="{{ $brand600 }}" stroke-width="3" />
                    <circle cx="67" cy="57" r="17" fill="{{ $paper }}" stroke="{{ $brand600 }}" stroke-width="3" />
                    <circle cx="67" cy="57" r="5" fill="{{ $brand600 }}" />
                    <path d="M132 28h72v58h-72z" fill="{{ $paper }}" stroke="{{ $navy500 }}" stroke-width="3" />
                    <path d="M132 46h72M132 68h72" stroke="{{ $navy100 }}" stroke-width="2.5" />
                    <path d="M26 104h178" stroke="{{ $navy500 }}" stroke-width="2" stroke-dasharray="7 6" />
                    <path d="M26 100v8M204 100v8" stroke="{{ $navy500 }}" stroke-width="2" />
                    <path d="M26 118h54l10 12h88" stroke="{{ $brand300 }}" stroke-width="3" fill="none" stroke-linecap="round" />
                </g>
                @break

            {{-- ------------------------- BUS: ledger, growth, seal, calculator --}}
            @case('BUS')
                {{-- Growth behind --}}
                @foreach ([[300, 214, 38], [348, 178, 74], [396, 134, 118], [444, 106, 146]] as $i => [$x, $y, $h])
                    <rect x="{{ $x }}" y="{{ 252 - $h }}" width="34" height="{{ $h }}" rx="6"
                          fill="{{ $i === 3 ? $brand600 : $navy500 }}" opacity="{{ $i === 3 ? 1 : 0.35 + $i * 0.2 }}" />
                @endforeach
                <path d="M306 214 L360 176 L408 132 L456 100" stroke="{{ $brand700 }}" stroke-width="4" fill="none" stroke-linecap="round" stroke-linejoin="round" />
                <path d="M436 100h22v22" stroke="{{ $brand700 }}" stroke-width="4" fill="none" stroke-linecap="round" stroke-linejoin="round" />

                {{-- The return, with a stamp --}}
                <g transform="translate(40 62)">
                    <rect width="188" height="212" rx="12" fill="{{ $paper }}" stroke="{{ $navy }}" stroke-width="3" />
                    <rect x="22" y="26" width="92" height="12" rx="6" fill="{{ $navy }}" />
                    @foreach ([58, 80, 102, 124] as $y)
                        <rect x="22" y="{{ $y }}" width="{{ $y === 124 ? 90 : 144 }}" height="9" rx="4.5" fill="{{ $navy100 }}" />
                    @endforeach
                    <rect x="22" y="150" width="144" height="40" rx="8" fill="{{ $brand50 }}" stroke="{{ $brand300 }}" stroke-width="2" />
                    <rect x="34" y="164" width="54" height="12" rx="6" fill="{{ $brand300 }}" />
                    <rect x="112" y="164" width="42" height="12" rx="6" fill="{{ $brand600 }}" />

                    <g transform="rotate(-14 150 40)">
                        <circle cx="150" cy="40" r="30" fill="none" stroke="{{ $brand600 }}" stroke-width="4" opacity="0.85" />
                        <path d="M136 40l10 10 20-22" stroke="{{ $brand600 }}" stroke-width="4.5" fill="none"
                              stroke-linecap="round" stroke-linejoin="round" opacity="0.85" />
                    </g>
                </g>

                {{-- Calculator --}}
                <g transform="translate(206 176)">
                    <rect width="86" height="106" rx="10" fill="{{ $navy }}" />
                    <rect x="12" y="12" width="62" height="24" rx="5" fill="{{ $brand300 }}" />
                    @for ($r = 0; $r < 3; $r++)
                        @for ($c = 0; $c < 3; $c++)
                            <rect x="{{ 12 + $c * 22 }}" y="{{ 46 + $r * 20 }}" width="16" height="14" rx="3"
                                  fill="{{ $paper }}" opacity="{{ ($r + $c) % 2 ? 0.55 : 0.85 }}" />
                        @endfor
                    @endfor
                </g>
                @break

            {{-- ------------------------------- ENG: book, letters, a class --}}
            @case('ENG')
                {{-- Open book --}}
                <g transform="translate(46 128)">
                    <path d="M0 26C40 2 108 2 148 26v140C108 142 40 142 0 166Z" fill="{{ $paper }}" stroke="{{ $navy }}" stroke-width="3" stroke-linejoin="round" />
                    <path d="M148 26C188 2 256 2 296 26v140c-40-24-108-24-148 0Z" fill="{{ $brand50 }}" stroke="{{ $navy }}" stroke-width="3" stroke-linejoin="round" />
                    <line x1="148" y1="26" x2="148" y2="166" stroke="{{ $navy }}" stroke-width="3" />
                    @foreach ([56, 76, 96, 116] as $i => $y)
                        <path d="M24 {{ $y }}c34-14 72-14 104-4" stroke="{{ $navy100 }}" stroke-width="4" fill="none" stroke-linecap="round" />
                        <path d="M172 {{ $y - 4 }}c32-10 70-10 104 4" stroke="{{ $i === 3 ? $brand300 : $navy100 }}" stroke-width="4" fill="none" stroke-linecap="round" />
                    @endforeach
                </g>

                {{-- Two speech bubbles, one in each script --}}
                <g transform="translate(276 44)">
                    <rect width="120" height="66" rx="16" fill="{{ $brand600 }}" />
                    <path d="M28 66l-4 20 24-20z" fill="{{ $brand600 }}" />
                    <text x="60" y="44" text-anchor="middle" font-family="ui-sans-serif, system-ui, sans-serif"
                          font-size="30" font-weight="700" fill="{{ $paper }}">Aa</text>
                </g>
                <g transform="translate(340 122)">
                    <rect width="108" height="62" rx="16" fill="{{ $navy }}" />
                    <path d="M76 62l6 18-26-18z" fill="{{ $navy }}" />
                    <text x="54" y="42" text-anchor="middle" font-family="'Noto Sans Bengali', ui-sans-serif, sans-serif"
                          font-size="27" font-weight="700" fill="{{ $paper }}">অ আ</text>
                </g>
                @break

            {{-- ------------------------------------------------ anything else --}}
            @default
                <g transform="translate(120 70)">
                    <rect width="240" height="180" rx="16" fill="{{ $paper }}" stroke="{{ $navy }}" stroke-width="3" />
                    @foreach ([40, 72, 104, 136] as $i => $y)
                        <rect x="28" y="{{ $y }}" width="{{ 184 - $i * 28 }}" height="12" rx="6"
                              fill="{{ $i === 3 ? $brand600 : $navy100 }}" />
                    @endforeach
                </g>
        @endswitch
    </g>
</svg>
