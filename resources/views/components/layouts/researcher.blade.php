@props(['title' => null])

@php
    use App\Models\ResearchIdea;

    $user = auth()->user();
    $profile = $user?->researcherProfile;

    /* The portal is a place of work, so the menu is a flat list of jobs rather
       than a navigation system. Entries that are not built yet are shown but
       marked, so the shape of the thing is visible without pretending. */
    $menu = [
        ['researcher.dashboard', 'nav.dashboard', 'grid', true],
        ['researcher.profile', 'nav.profile', 'users', true],
        ['researcher.ideas.index', 'nav.ideas', 'lightbulb', true],
        ['researcher.proposals.index', 'nav.proposals', 'document', true],
        ['researcher.projects.index', 'nav.projects', 'briefcase', true],
        ['researcher.manuscripts.create', 'nav.submit_paper', 'upload', true],
        ['researcher.manuscripts.index', 'nav.papers', 'academic', true],
        ['researcher.funding.index', 'nav.opportunities', 'compass', true],
        ['researcher.notifications.index', 'nav.notifications', 'bell', true],
        ['researcher.support.index', 'nav.support', 'heart', true],
        ['researcher.settings', 'nav.settings', 'cog', true],
    ];

    $unread = $user?->unreadNotifications()->count() ?? 0;
@endphp

<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">

    <title>{{ $title ? $title.' · ' : '' }}{{ __('researcher.portal') }}</title>

    <link rel="icon" href="{{ asset('favicon.png') }}" type="image/png" sizes="64x64">

    {{ Vite::fonts(['space-grotesk', 'inter', 'noto-sans-bengali']) }}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-full bg-ink-50 font-sans text-ink-900 antialiased">

<div class="flex min-h-screen" x-data="{ sidebar: false }">

    {{-- ---------------- Sidebar ---------------- --}}
    <aside class="fixed inset-y-0 start-0 z-40 flex w-[270px] flex-col border-e border-ink-100 bg-white transition-transform duration-300 lg:translate-x-0"
           :class="sidebar ? 'translate-x-0' : '-translate-x-full rtl:translate-x-full'"
           x-cloak>

        <div class="flex h-[72px] shrink-0 items-center gap-3 border-b border-ink-100 px-5">
            <img src="{{ asset('media/logo-mark.png') }}" alt="" width="256" height="249" class="h-9 w-9 object-contain">
            <span class="font-display text-[14.5px] font-bold leading-tight text-ink-950">{{ __('researcher.portal') }}</span>
        </div>

        {{-- Who is signed in --}}
        <div class="flex items-center gap-3 border-b border-ink-100 px-5 py-4">
            @if ($profile?->photo)
                <img src="{{ Storage::url($profile->photo) }}" alt="" class="h-11 w-11 shrink-0 rounded-full object-cover ring-1 ring-ink-200">
            @else
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-brand-600 font-display text-[15px] font-bold text-white">
                    {{ \Illuminate\Support\Str::of($user?->name ?? '')->trim()->substr(0, 1)->upper() }}
                </span>
            @endif

            <span class="min-w-0">
                <span class="block truncate text-[14px] font-bold text-ink-950">{{ $user?->name }}</span>
                <span class="block text-[12px] text-ink-500">{{ __('researcher.role') }}</span>
            </span>
        </div>

        <nav class="flex-1 overflow-y-auto px-3 py-4">
            <ul class="space-y-0.5">
                @foreach ($menu as [$route, $label, $icon, $live])
                    @php $active = $route && request()->routeIs($route.'*'); @endphp

                    <li>
                        @if ($live)
                            <a href="{{ route($route) }}"
                               @class([
                                   'flex items-center gap-3 rounded-xl px-3 py-2.5 text-[14px] font-medium transition duration-200',
                                   'bg-brand-600 text-white shadow-[0_8px_20px_-12px_var(--color-brand-600)]' => $active,
                                   'text-ink-600 hover:bg-ink-50 hover:text-ink-900' => ! $active,
                               ])>
                                <x-ui-icon :name="$icon" class="h-[18px] w-[18px] shrink-0" />
                                {{ __('researcher.'.$label) }}
                            </a>
                        @else
                            <span class="flex cursor-not-allowed items-center gap-3 rounded-xl px-3 py-2.5 text-[14px] font-medium text-ink-300"
                                  title="{{ __('researcher.soon.title') }}">
                                <x-ui-icon :name="$icon" class="h-[18px] w-[18px] shrink-0" />
                                <span class="flex-1">{{ __('researcher.'.$label) }}</span>
                                <span class="rounded-full bg-ink-100 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wider text-ink-400">
                                    {{ __('researcher.nav.soon') }}
                                </span>
                            </span>
                        @endif
                    </li>
                @endforeach
            </ul>
        </nav>

        <div class="border-t border-ink-100 p-3">
            <div class="mb-2 sm:hidden">
                <x-portal-language-switch class="w-full justify-center" />
            </div>

            <a href="{{ route('research') }}" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-[13.5px] font-medium text-ink-600 transition hover:bg-ink-50">
                <x-ui-icon name="arrow-left" class="h-[18px] w-[18px]" />
                {{ __('researcher.nav.public_site') }}
            </a>

            <form method="POST" action="{{ route('researcher.logout') }}">
                @csrf
                <button type="submit" class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-[13.5px] font-medium text-ink-600 transition hover:bg-rose-50 hover:text-rose-700">
                    <x-ui-icon name="external" class="h-[18px] w-[18px]" />
                    {{ __('researcher.nav.logout') }}
                </button>
            </form>
        </div>
    </aside>

    {{-- Backdrop, narrow screens only --}}
    <div x-show="sidebar" x-cloak @click="sidebar = false"
         class="fixed inset-0 z-30 bg-ink-950/50 lg:hidden" aria-hidden="true"></div>

    {{-- ---------------- Main ---------------- --}}
    <div class="flex min-w-0 flex-1 flex-col lg:ps-[270px]">

        <header class="sticky top-0 z-20 flex h-[72px] shrink-0 items-center justify-between gap-4 border-b border-ink-100 bg-white/90 px-5 backdrop-blur-sm sm:px-8">
            <div class="flex min-w-0 items-center gap-3">
                <button type="button" @click="sidebar = ! sidebar"
                        class="flex h-10 w-10 items-center justify-center rounded-xl border border-ink-200 text-ink-700 lg:hidden"
                        aria-label="{{ __('site.nav.open_menu') }}">
                    <x-ui-icon name="menu" class="h-5 w-5" />
                </button>

                @if ($title)
                    <h1 class="truncate font-display text-[17px] font-bold text-ink-950 sm:text-[19px]">{{ $title }}</h1>
                @endif
            </div>

            <div class="flex shrink-0 items-center gap-2.5">
                <x-portal-language-switch class="hidden sm:flex" />

                <a href="{{ route('researcher.notifications.index') }}"
                   class="relative flex h-10 w-10 items-center justify-center rounded-xl border border-ink-200 text-ink-500 transition hover:border-brand-300 hover:text-brand-700"
                   title="{{ __('researcher.nav.notifications') }}">
                    <x-ui-icon name="bell" class="h-[18px] w-[18px]" />
                    @if ($unread)
                        <span class="absolute -right-1 -top-1 flex h-5 min-w-5 items-center justify-center rounded-full bg-brand-600 px-1 font-numeric text-[10.5px] font-bold text-white">{{ $unread }}</span>
                    @endif
                </a>

                <a href="{{ route('researcher.profile') }}" class="flex items-center gap-2.5 rounded-xl border border-ink-200 py-1.5 pe-3 ps-1.5 transition hover:border-brand-300">
                    @if ($profile?->photo)
                        <img src="{{ Storage::url($profile->photo) }}" alt="" class="h-7 w-7 rounded-lg object-cover">
                    @else
                        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-brand-600 font-display text-[12px] font-bold text-white">
                            {{ \Illuminate\Support\Str::of($user?->name ?? '')->trim()->substr(0, 1)->upper() }}
                        </span>
                    @endif
                    <span class="hidden text-[13px] font-semibold text-ink-800 sm:block">{{ \Illuminate\Support\Str::of($user?->name ?? '')->words(1, '') }}</span>
                </a>
            </div>
        </header>

        <main class="flex-1 px-5 py-7 sm:px-8 sm:py-9">
            @if (session('saved'))
                <div class="mb-6 flex items-center gap-3 rounded-2xl border border-brand-200 bg-brand-50 px-5 py-4 text-[14px] font-medium text-brand-900">
                    <x-ui-icon name="check" class="h-5 w-5 shrink-0 text-brand-600" stroke="2.4" />
                    {{ session('saved') }}
                </div>
            @endif

            {{ $slot }}
        </main>
    </div>
</div>

<script>
    /* Shared by every form in the portal, so a page can be reached directly
       without depending on another page having defined these first. */

    // Department -> research field -> research area, off the framework's tree.
    window.ideaForm = (tree, department, field, area) => ({
        tree,
        department: department || '',
        field: field || '',
        area: area || '',

        get row() { return this.tree.find((r) => r.department === this.department) || null },
        get fields() { return this.row ? this.row.fields : [] },
        get areas() { return this.row ? this.row.areas : [] },

        /* The goals the chosen field already serves, split off the framework's
           "SDG 4, 10" string and carried into the form as hidden inputs. */
        get sdgs() {
            const chosen = this.fields.find((f) => f.name === this.field)
            if (! chosen || ! chosen.sdgs) return []
            return chosen.sdgs.split(',').map((s) => s.trim()).filter(Boolean)
        },

        onDepartment() {
            // The old field and area belong to the old department.
            this.field = ''
            this.area = ''
        },
    })

    /* A long form taken one step at a time.

       The form is whole and ordinary underneath: every field is in the page and
       posts together, so the server sees exactly what it saw before and nothing
       is lost if JavaScript never runs — the steps simply all show at once. */
    window.stepForm = (total, start, names) => ({
        total,
        names: names || [],
        step: start || 0,
        seen: start || 0,

        get name() { return this.names[this.step] || '' },

        get first() { return this.step === 0 },
        get last() { return this.step === this.total - 1 },
        get progress() { return Math.round(((this.step + 1) / this.total) * 100) },

        panel(i) { return this.$el.querySelector('[data-step="' + i + '"]') },

        /* Moving on is allowed once the browser is happy with the step being
           left. Moving back never asks for anything. */
        go(i) {
            if (i > this.step && ! this.ready()) return

            this.step = Math.max(0, Math.min(i, this.total - 1))
            this.seen = Math.max(this.seen, this.step)

            this.$nextTick(() => window.scrollTo({ top: 0, behavior: 'smooth' }))
        },

        next() { this.go(this.step + 1) },
        back() { this.go(this.step - 1) },

        ready() {
            const panel = this.panel(this.step)
            if (! panel) return true

            for (const field of panel.querySelectorAll('input, select, textarea')) {
                if (! field.checkValidity()) {
                    field.reportValidity()
                    return false
                }
            }

            return true
        },

        /* A field the browser objects to may be on a step that is out of sight,
           where it would block the submission silently. So: find it, go to its
           step, and say what is wrong there. */
        guard(event) {
            const form = event.target

            if (form.checkValidity()) return

            event.preventDefault()

            const bad = form.querySelector('input:invalid, select:invalid, textarea:invalid')
            const panel = bad?.closest('[data-step]')

            if (panel) this.step = Number(panel.dataset.step)

            this.$nextTick(() => {
                bad?.focus()
                bad?.reportValidity()
            })
        },
    })

    /* A plain tag box.

       Enter, a comma, Tab or simply leaving the field commits what has been
       typed. Leaving matters: without it a word typed and then saved went
       nowhere, because only the committed tags have inputs to post. */
    window.tagInput = (initial) => ({
        tags: Array.isArray(initial) ? initial : [],
        draft: '',

        add() {
            // A pasted list arrives all at once, so split on the commas.
            for (const part of this.draft.split(',')) {
                const value = part.trim()
                if (value && ! this.tags.includes(value)) this.tags.push(value)
            }

            this.draft = ''
        },

        remove(i) { this.tags.splice(i, 1) },
    })
</script>

</body>
</html>
