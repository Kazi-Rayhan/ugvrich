import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';
import intersect from '@alpinejs/intersect';

Alpine.plugin(collapse);
Alpine.plugin(intersect);

/* ---------------------------------------------------------------------
 | Header — the sliding highlight, scroll progress and the mobile menu.
 --------------------------------------------------------------------- */
Alpine.data('siteHeader', () => ({
    open: false,
    panel: null,
    progress: 0,
    ind: { left: 0, width: 0 },
    stripInset: 0,
    side: null,
    closeTimer: null,

    // Scroll bookkeeping, so the handler itself never measures the page.
    ticking: false,
    scrollable: 0,
    alignQueued: false,


    // The link the highlight is currently sitting on, so it can be measured
    // again after the bar changes size.
    marked: null,

    init() {
        this.onScroll();


        this.measurePage();

        this.$nextTick(() => this.remeasure());
        window.addEventListener('resize', () => {
            this.measurePage();
            this.remeasure();
        });
        window.addEventListener('load', () => {
            this.measurePage();
            this.remeasure();
        });
        document.fonts?.ready.then(() => this.remeasure());

        /* The first paint measures a menu that is still settling — webfonts land,
           the logo sizes itself, the links finish animating in. Watch the menu
           itself instead of guessing when it has stopped moving. Only the menu is
           observed: the strip's own padding is what we set, so observing it too
           would chase its own tail. */
        if ('ResizeObserver' in window) {
            // queueAlign, not alignStrip: the logo's height is animated, so this
            // fires on every frame of the transition. Measuring and writing on
            // each of those frames is what made scrolling stutter.
            const watcher = new ResizeObserver(() => this.queueAlign());

            // The nav box itself is flex-1, so its width never changes — the links
            // inside it are what move, so watch those and the logo beside them.
            this.$refs['nav-left']?.querySelectorAll('.nav-item').forEach((link) => watcher.observe(link));
            const logo = this.$root.querySelector('.brand-spin');
            if (logo) watcher.observe(logo);
        }


        // Lock the page behind the mobile menu.
        this.$watch('open', (isOpen) => {
            document.body.style.overflow = isOpen ? 'hidden' : '';
        });
    },

    /* How far the page can scroll. Measured on load and on resize, never in
       the scroll handler: reading scrollHeight forces the browser to lay the
       page out, and doing that on every scroll event is what buffers. */
    measurePage() {
        this.scrollable = document.documentElement.scrollHeight - window.innerHeight;
    },

    /* The bar no longer changes size as the page scrolls, so all this does
       is move the reading-progress line — one update per frame, however many
       scroll events the browser sends. */
    onScroll() {
        if (this.ticking) {
            return;
        }

        this.ticking = true;

        requestAnimationFrame(() => {
            this.ticking = false;

            this.progress = this.scrollable > 0
                ? Math.min(window.scrollY / this.scrollable, 1)
                : 0;
        });
    },

    /* Collapse a burst of alignment requests into one, at the end of the frame.
       The strip's padding is read from the menu's position, so measuring while
       the menu is still moving only produces a value that is wrong again by the
       next frame. */
    queueAlign() {
        if (this.alignQueued) {
            return;
        }

        this.alignQueued = true;

        requestAnimationFrame(() => {
            this.alignQueued = false;
            this.alignStrip();
        });
    },

    // Slide the highlight to a link and open (or close) its panel. `side` says
    // which of the two navs the link sits in.
    hover(el, key, side = null) {
        this.keep();
        this.side = side;
        this.marked = el;
        this.moveTo(el);
        this.panel = key;
    },

    moveTo(el) {
        this.ind = el ? { left: el.offsetLeft + 2, width: el.offsetWidth - 4 } : { left: 0, width: 0 };
    },

    // Rest the highlight on the current page's link while nothing is hovered.
    settle() {
        if (this.panel) return;

        const current = this.$refs.current ?? null;
        this.side = current?.dataset.side ?? null;
        this.marked = current;
        this.moveTo(current);
    },

    /* The info strip sits above a menu that is centred on the logo, so its
       first label is nowhere near the edge of the row. Line the strip's first
       label up with it by measuring both and closing the gap. */
    alignStrip() {
        const first = this.$refs['nav-left']?.querySelector('.nav-item');
        const label = this.$refs.strip?.querySelector('[data-strip-label]');

        // Below xl the menu is hidden behind the Menu button; nothing to line up with.
        if (! first || ! label || ! first.offsetParent) {
            if (this.stripInset !== 0) {
                this.stripInset = 0;
            }

            return;
        }

        const menuLabelLeft = first.getBoundingClientRect().left
            + parseFloat(getComputedStyle(first).paddingInlineStart || 0);

        // Padding moves the label one-for-one, so one pass lands it exactly.
        const inset = this.stripInset + (menuLabelLeft - label.getBoundingClientRect().left);

        const next = Math.max(0, Math.round(inset));

        // Writing the same number back would restyle the strip for nothing.
        if (next !== this.stripInset) {
            this.stripInset = next;
        }
    },

    // Re-measure wherever the highlight is, without moving it elsewhere.
    remeasure() {
        this.alignStrip();

        const el = this.marked ?? this.$refs.current ?? null;

        if (el?.isConnected) {
            this.side = el.dataset.side ?? this.side;
            this.moveTo(el);

            return;
        }

        this.settle();
    },

    keep() {
        clearTimeout(this.closeTimer);
    },

    leave() {
        this.closeTimer = setTimeout(() => {
            this.panel = null;
            this.settle();
        }, 160);
    },
}));

/* ---------------------------------------------------------------------
 | Count-up for stat figures
 --------------------------------------------------------------------- */
Alpine.data('counter', (target = 0, duration = 1500) => ({
    value: 0,
    done: false,

    start() {
        if (this.done) return;
        this.done = true;

        if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            this.value = target;
            return;
        }

        const startedAt = performance.now();
        const step = (now) => {
            const progress = Math.min((now - startedAt) / duration, 1);
            // easeOutExpo
            const eased = progress === 1 ? 1 : 1 - Math.pow(2, -10 * progress);
            this.value = Math.round(eased * target);
            if (progress < 1) requestAnimationFrame(step);
        };
        requestAnimationFrame(step);
    },
}));

window.Alpine = Alpine;
Alpine.start();

/* ---------------------------------------------------------------------
 | Reveal on scroll
 --------------------------------------------------------------------- */
const revealables = () => document.querySelectorAll('.reveal:not(.is-visible)');

if ('IntersectionObserver' in window) {
    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                }
            });
        },
        { rootMargin: '0px 0px -8% 0px', threshold: 0.06 },
    );

    const observeAll = () => revealables().forEach((el) => observer.observe(el));

    document.addEventListener('DOMContentLoaded', observeAll);
    document.addEventListener('livewire:navigated', observeAll);
} else {
    document.addEventListener('DOMContentLoaded', () =>
        revealables().forEach((el) => el.classList.add('is-visible')),
    );
}

/* ---------------------------------------------------------------------
 | Headline word reveal
 |
 | Each word is wrapped in an overflow-hidden box and slides up from below
 | on a stagger, so headings wipe into place instead of fading. The splitter
 | walks text nodes only, so inline markup (the accent <span>) survives.
 --------------------------------------------------------------------- */
const splitIntoWords = (root) => {
    if (root.dataset.split === 'done') return;

    const walk = (node) => {
        [...node.childNodes].forEach((child) => {
            if (child.nodeType === Node.TEXT_NODE) {
                if (!child.textContent.trim()) return;

                const frag = document.createDocumentFragment();
                child.textContent.split(/(\s+)/).forEach((part) => {
                    if (!part.trim()) {
                        frag.appendChild(document.createTextNode(part));
                        return;
                    }
                    const box = document.createElement('span');
                    box.className = 'word';
                    const inner = document.createElement('span');
                    inner.className = 'word-inner';
                    inner.textContent = part;
                    box.appendChild(inner);
                    frag.appendChild(box);
                });
                child.replaceWith(frag);
            } else if (child.nodeType === Node.ELEMENT_NODE && !child.classList.contains('word')) {
                walk(child);
            }
        });
    };

    walk(root);
    root.querySelectorAll('.word-inner').forEach((el, i) => el.style.setProperty('--i', i));
    root.dataset.split = 'done';
};

const prefersReducedMotion = () =>
    window.matchMedia('(prefers-reduced-motion: reduce)').matches;

const initSplitHeadings = () => {
    if (prefersReducedMotion()) return;
    document.querySelectorAll('[data-split]').forEach(splitIntoWords);
};

document.addEventListener('DOMContentLoaded', initSplitHeadings);

/* ---------------------------------------------------------------------
 | Cursor spotlight
 |
 | One delegated listener for the whole page rather than one per card; it
 | only writes two custom properties, so the highlight itself is pure CSS.
 --------------------------------------------------------------------- */
document.addEventListener(
    'pointermove',
    (event) => {
        const card = event.target.closest?.('.spotlight');
        if (!card) return;

        const rect = card.getBoundingClientRect();
        card.style.setProperty('--mx', `${event.clientX - rect.left}px`);
        card.style.setProperty('--my', `${event.clientY - rect.top}px`);
    },
    { passive: true },
);
