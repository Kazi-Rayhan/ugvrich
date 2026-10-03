<x-layouts.app :title="__('site.thanks.consultancy_title')"
               :description="__('site.thanks.consultancy_description')">

    @php
        $firstName = \Illuminate\Support\Str::of($submission['name'] ?? '')
            ->replaceMatches('/^(Dr\.?|Prof\.?|Mr\.?|Ms\.?|Mrs\.?|Md\.?|Engr\.?)\s+/i', '')
            ->explode(' ')->first();
        $email = $site->get('contact_email');
    @endphp

    <section class="relative isolate overflow-hidden bg-ink-50 py-16 sm:py-24">
        <div class="pointer-events-none absolute inset-0 -z-10 text-brand-700 grid-overlay opacity-40 [mask-image:radial-gradient(ellipse_at_top,black,transparent_70%)]" aria-hidden="true"></div>
        <div class="pointer-events-none absolute left-1/2 top-24 -z-10 h-[34rem] w-[34rem] -translate-x-1/2 rounded-full border border-brand-100" aria-hidden="true"></div>
        <div class="pointer-events-none absolute left-1/2 top-44 -z-10 h-[22rem] w-[22rem] -translate-x-1/2 rounded-full border border-brand-100" aria-hidden="true"></div>

        <div class="container-rich">
            <div class="mx-auto max-w-3xl">
                {{-- Confirmation --}}
                <div class="text-center">
                    <span class="relative mx-auto flex h-20 w-20 items-center justify-center">
                        <span class="absolute inset-0 animate-ping rounded-full bg-brand-400 opacity-20" aria-hidden="true"></span>
                        <span class="absolute inset-0 rounded-full bg-brand-100" aria-hidden="true"></span>
                        <span class="relative flex h-14 w-14 items-center justify-center rounded-full bg-brand-600 text-white shadow-[0_14px_30px_-12px_var(--color-brand-600)]">
                            <x-ui-icon name="check" class="h-7 w-7" stroke="2.6" />
                        </span>
                    </span>

                    <span class="eyebrow mt-8">
                        <span class="h-1.5 w-1.5 rounded-full bg-current"></span>{{ __('site.thanks.consultancy_title') }}
                    </span>

                    <h1 class="mt-5 font-display text-4xl font-bold leading-[1.1] tracking-tight text-ink-950 sm:text-5xl">
                        {{ __('site.thanks.thank_you', ['name' => $firstName ? ', '.$firstName : '']) }}
                    </h1>
                    <p class="mx-auto mt-5 max-w-xl text-[17px] leading-relaxed muted">
                        {{ __('site.thanks.consultancy_body') }}
                    </p>

                    {{-- The meeting they asked for, if they named one --}}
                    @if (($submission['preferred_date'] ?? null) && ($submission['preferred_slot'] ?? null))
                        <div class="mx-auto mt-8 max-w-md rounded-[1.5rem] border border-brand-200 bg-white p-5 text-center shadow-[0_24px_50px_-36px_rgba(7,20,38,0.45)] sm:p-6">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-brand-600">{{ __('site.thanks.meeting_title') }}</p>
                            <p class="mt-3 inline-flex flex-wrap items-center justify-center gap-2 rounded-2xl bg-brand-50 px-5 py-3 text-[15px] font-semibold text-brand-800">
                                <x-ui-icon name="calendar" class="h-4 w-4 text-brand-600" />
                                {{ $submission['preferred_date'] }}
                                <span class="text-brand-300">·</span>
                                <span class="tabular-nums">{{ $submission['preferred_slot'] }}</span>
                            </p>
                            <div class="mt-4 flex items-start gap-3 rounded-2xl border border-amber-200 bg-amber-50 p-3.5 text-left">
                                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-amber-100 text-amber-600">
                                    <x-ui-icon name="bell" class="h-4 w-4" />
                                </span>
                                <p class="text-[13px] leading-relaxed text-amber-900">
                                    <span class="font-semibold">{{ __('site.thanks.reminder_label') }}</span>
                                    {{ __('site.thanks.meeting_note') }}
                                </p>
                            </div>

                            @if ($submission['calendar_url'] ?? null)
                                <a href="{{ $submission['calendar_url'] }}" target="_blank" rel="noopener noreferrer"
                                   class="btn-ghost mt-4 w-full justify-center">
                                    <x-ui-icon name="calendar" class="h-4 w-4" /> {{ __('site.thanks.add_to_calendar') }}
                                </a>
                            @endif
                        </div>
                    @endif
                </div>

                {{-- Actions --}}
                <div class="mt-10 flex flex-wrap items-center justify-center gap-3">
                    <a href="{{ route('home') }}" class="btn-primary group">
                        {{ __('site.actions.back_to_home') }} <x-ui-icon name="arrow-right" class="h-4 w-4 transition-transform duration-300 group-hover:translate-x-0.5" />
                    </a>
                    <a href="{{ route('experts.index') }}" class="btn-ghost">{{ __('site.thanks.meet_experts') }}</a>
                    <a href="{{ route('projects.index') }}" class="btn-ghost">{{ __('site.thanks.see_projects') }}</a>
                </div>

                @if ($email)
                    <p class="mt-8 text-center text-[14px] muted">
                        {{ __('site.thanks.add_something') }}
                        <a href="mailto:{{ $email }}?subject={{ rawurlencode(__('site.thanks.email_subject')) }}" class="font-semibold text-brand-700 hover:underline">{{ $email }}</a>.
                    </p>
                @endif
            </div>
        </div>
    </section>

    {{-- Celebration: confetti, paper strips and flower petals burst from both
         sides, then drift down and fade. Skipped for reduced motion. --}}
    <canvas id="celebration" class="pointer-events-none fixed inset-0 z-[60] h-full w-full" aria-hidden="true"></canvas>
    <script>
        (() => {
            const canvas = document.getElementById('celebration');
            if (! canvas || matchMedia('(prefers-reduced-motion: reduce)').matches) { canvas?.remove(); return; }

            const ctx = canvas.getContext('2d');
            const dpr = Math.min(window.devicePixelRatio || 1, 2);
            let w, h;
            const resize = () => {
                w = innerWidth; h = innerHeight;
                canvas.width = w * dpr; canvas.height = h * dpr;
                ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
            };
            resize();
            addEventListener('resize', resize);

            const colors = ['#316d31', '#4c9a4c', '#8fd18f', '#f5b700', '#ff6b6b', '#ff9ecb', '#4f8cff', '#ffffff'];
            const flowers = ['🌸', '🌼', '🌺', '💐', '🎉', '✨'];
            const rand = (a, b) => a + Math.random() * (b - a);
            const pick = (list) => list[Math.floor(Math.random() * list.length)];
            const pieces = [];

            const make = (x, y, angle, speed, kind) => ({
                x, y, kind,
                vx: Math.cos(angle) * speed, vy: Math.sin(angle) * speed,
                size: kind === 'flower' ? rand(16, 26) : rand(6, 11),
                color: pick(colors), glyph: pick(flowers),
                rot: rand(0, Math.PI * 2), spin: rand(-0.2, 0.2),
                tilt: rand(0, Math.PI * 2), wobble: rand(0.05, 0.12),
                life: 0, ttl: rand(110, 170),
            });
            const kindOf = () => { const r = Math.random(); return r < 0.18 ? 'flower' : r < 0.55 ? 'strip' : r < 0.8 ? 'rect' : 'dot'; };

            // Two cannons from the lower corners, aimed up and inwards.
            const burst = (count) => {
                for (let i = 0; i < count; i++) {
                    pieces.push(make(-10, h * 0.85, rand(-1.35, -0.75), rand(17, 27), kindOf()));
                    pieces.push(make(w + 10, h * 0.85, rand(-2.4, -1.8), rand(17, 27), kindOf()));
                }
            };
            // A gentle shower of petals and paper from the top.
            const rain = (count) => {
                for (let i = 0; i < count; i++) {
                    pieces.push(make(rand(0, w), rand(-h * 0.25, -20), rand(1.3, 1.85), rand(4, 7), Math.random() < 0.35 ? 'flower' : kindOf()));
                }
            };

            burst(70);
            setTimeout(() => rain(60), 150);
            setTimeout(() => burst(45), 450);

            const draw = (p) => {
                ctx.save();
                ctx.globalAlpha = Math.max(0, Math.min(1, (p.ttl - p.life) / 30));
                ctx.translate(p.x, p.y);
                ctx.rotate(p.rot);
                if (p.kind === 'flower') {
                    ctx.font = `${p.size}px "Apple Color Emoji","Segoe UI Emoji","Noto Color Emoji",sans-serif`;
                    ctx.textAlign = 'center'; ctx.textBaseline = 'middle';
                    ctx.fillText(p.glyph, 0, 0);
                } else {
                    ctx.fillStyle = p.color;
                    const squash = Math.abs(Math.cos(p.tilt)); // paper turning over as it falls
                    if (p.kind === 'strip') ctx.fillRect(-p.size * 0.25, -p.size, p.size * 0.5, p.size * 2 * squash + 1);
                    else if (p.kind === 'rect') ctx.fillRect(-p.size / 2, -p.size / 2 * squash, p.size, p.size * squash + 1);
                    else { ctx.beginPath(); ctx.arc(0, 0, p.size / 2.4, 0, Math.PI * 2); ctx.fill(); }
                }
                ctx.restore();
            };

            const tick = () => {
                ctx.clearRect(0, 0, w, h);
                for (let i = pieces.length - 1; i >= 0; i--) {
                    const p = pieces[i];
                    p.life++;
                    p.vx *= 0.975;
                    p.vy = Math.min(p.vy * 0.975 + 0.45, p.kind === 'flower' ? 5.5 : 8);
                    p.tilt += p.wobble;
                    p.x += p.vx + Math.sin(p.tilt) * (p.kind === 'flower' ? 1.1 : 0.6);
                    p.y += p.vy;
                    p.rot += p.spin * (p.kind === 'flower' ? 0.3 : 1);
                    if (p.life > p.ttl || p.y > h + 40) { pieces.splice(i, 1); continue; }
                    draw(p);
                }
                if (pieces.length) requestAnimationFrame(tick);
                else { removeEventListener('resize', resize); canvas.remove(); }
            };
            requestAnimationFrame(tick);
        })();
    </script>
</x-layouts.app>
