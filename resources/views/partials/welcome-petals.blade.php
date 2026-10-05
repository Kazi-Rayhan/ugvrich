{{--
 | The flowers that follow a visitor home from the inauguration (/udbodhon).
 |
 | The inauguration leaves a note in sessionStorage just before it sends the
 | visitor here. If the note is there, this picks up where that page ended —
 | on an ivory veil — fades the veil away and lets petals, marigolds and gold
 | confetti fall over the home page for a few seconds, then removes itself.
 | Anyone arriving any other way sees nothing.
--}}
<script>
(() => {
    try {
        if (sessionStorage.getItem('rich-welcome') !== '1') return;
        sessionStorage.removeItem('rich-welcome');
    } catch (e) {
        return;
    }

    const reduced = matchMedia('(prefers-reduced-motion: reduce)').matches;
    const rand = (a, b) => a + Math.random() * (b - a);
    const pick = list => list[Math.floor(Math.random() * list.length)];

    const veil = document.createElement('div');
    veil.setAttribute('aria-hidden', 'true');
    veil.style.cssText = 'position:fixed;inset:0;z-index:9998;pointer-events:none;background:radial-gradient(ellipse at 50% 45%,#ffffff,#f1f8f0);transition:opacity 1.4s ease';

    const canvas = document.createElement('canvas');
    canvas.setAttribute('aria-hidden', 'true');
    canvas.style.cssText = 'position:fixed;inset:0;z-index:9999;pointer-events:none;transition:opacity 1.2s ease';

    document.body.append(veil, canvas);
    requestAnimationFrame(() => requestAnimationFrame(() => { veil.style.opacity = '0'; }));
    setTimeout(() => veil.remove(), 1600);

    const c = canvas.getContext('2d');
    let W, H;
    const resize = () => {
        const dpr = Math.min(devicePixelRatio || 1, 2);
        W = innerWidth; H = innerHeight;
        canvas.width = W * dpr; canvas.height = H * dpr;
        c.setTransform(dpr, 0, 0, dpr, 0, 0);
    };
    resize();
    addEventListener('resize', resize);

    const PETAL = ['#ffffff', '#fff7ec', '#fde2e4', '#f9c6cf', '#f4a7b5'];
    const MARIGOLD = [['#ffb703', '#e85d04'], ['#ffd166', '#f4a261'], ['#f6c343', '#d98b0b']];
    const PAPER = ['#316d31', '#41843f', '#8fbf8b', '#c3881a', '#e3ab2e', '#f2cd6b'];
    const density = Math.min(1.2, Math.max(0.4, (innerWidth * innerHeight) / (1440 * 900))) * (reduced ? 0.3 : 1);

    const make = (y) => {
        const r = Math.random();
        const base = { x: rand(-20, W + 20), y, vx: rand(-0.5, 0.5), vy: rand(0.6, 2), rot: rand(0, 6.3), vr: rand(-0.05, 0.05),
                       tilt: rand(0, 6.3), vt: rand(0.03, 0.12), sway: rand(0.5, 1.4), phase: rand(0, 6.3) };
        if (r < 0.55) return { ...base, k: 'petal', s: rand(6, 11), color: pick(PETAL), g: 0.03, max: 1.8 };
        if (r < 0.72) { const [petal, core] = pick(MARIGOLD); return { ...base, k: 'marigold', r: rand(7, 11), petal, core, g: 0.035, max: 2.1 }; }
        return { ...base, k: 'paper', w: rand(7, 12), h: rand(4, 7), color: pick(PAPER), g: 0.08, max: 3 };
    };

    // A first handful already mid-air, as if they came through with the visitor.
    let particles = Array.from({ length: Math.round(110 * density) }, () => make(rand(-H * 0.2, H * 0.9)));
    const started = performance.now();
    const RAIN_FOR = 4500, FADE_AT = 7000, END_AT = 8400;
    let last = 0, clock = 0;

    function frame(now) {
        const f = last ? Math.min((now - last) / 16.667, 3) : 1;
        last = now;
        clock += f;
        const age = now - started;

        if (age < RAIN_FOR) {
            for (let n = 1.6 * density * f; n > 0; n -= 1) if (Math.random() < n) particles.push(make(-24));
        }
        if (age > FADE_AT) canvas.style.opacity = '0';

        c.clearRect(0, 0, W, H);
        for (const p of particles) {
            p.vy = Math.min(p.vy + p.g * f, p.max);
            p.x += (p.vx + Math.sin(clock * 0.05 + p.phase) * p.sway) * f;
            p.y += p.vy * f;
            p.rot += p.vr * f;
            p.tilt += p.vt * f;

            c.save();
            c.translate(p.x, p.y);
            c.rotate(p.rot);
            if (p.k === 'petal') {
                const s = p.s;
                c.scale(Math.cos(p.tilt), 1);
                c.fillStyle = p.color;
                c.beginPath();
                c.moveTo(0, -s);
                c.bezierCurveTo(s * 0.95, -s * 0.55, s * 0.6, s * 0.7, 0, s);
                c.bezierCurveTo(-s * 0.6, s * 0.7, -s * 0.95, -s * 0.55, 0, -s);
                c.fill();
                c.strokeStyle = 'rgba(190,150,120,.35)';
                c.lineWidth = 0.7;
                c.stroke();
            } else if (p.k === 'marigold') {
                for (let ring = 0; ring < 2; ring++) {
                    const r = p.r * (ring ? 0.68 : 1);
                    c.fillStyle = ring ? p.core : p.petal;
                    for (let i = 0; i < 10; i++) {
                        c.rotate(Math.PI / 5);
                        c.beginPath();
                        c.ellipse(0, -r * 0.55, r * 0.3, r * 0.55, 0, 0, Math.PI * 2);
                        c.fill();
                    }
                }
                c.fillStyle = '#8a3b00';
                c.beginPath();
                c.arc(0, 0, p.r * 0.22, 0, Math.PI * 2);
                c.fill();
            } else {
                c.scale(1, Math.cos(p.tilt));
                c.fillStyle = p.color;
                c.fillRect(-p.w / 2, -p.h / 2, p.w, p.h);
            }
            c.restore();
        }
        particles = particles.filter(p => p.y < H + 40);

        if (age < END_AT) requestAnimationFrame(frame);
        else { removeEventListener('resize', resize); canvas.remove(); }
    }
    requestAnimationFrame(frame);
})();
</script>
