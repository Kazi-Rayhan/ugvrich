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
