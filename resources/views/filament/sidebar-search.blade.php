{{-- A filter box at the top of the admin sidebar.

     The sidebar runs to about thirty entries across four groups, two of them
     collapsed by default, so finding "Homepage Figures" means remembering which
     group it lives in. Typing two letters is faster than remembering.

     It filters what is already on the page rather than querying anything: every
     entry is in the DOM, so matching is instant. Filament's own global search
     (the top bar) searches records — this searches the menu.

     Written against Filament's markup: `.fi-sidebar-item` for an entry,
     `.fi-sidebar-item-label` for its text, `.fi-sidebar-group` for a group.
     Groups collapse through the `sidebar` Alpine store, which is how a match
     inside a collapsed group is opened up while searching.

     Style and behaviour are inline rather than pushed to a stack: the panel
     renders `@stack('styles')` in the head, before this runs, and a script
     pushed to the foot can land after Alpine has already started. Defining the
     component as a global before the element avoids both. --}}

@once
    <style>
        /* Written out in full rather than with utility classes: the panel ships
           a fixed CSS build, so a class Filament does not itself use is absent. */
        .rich-sidebar-search {
            padding: 0 0.25rem 0.75rem;
        }

        .rich-sidebar-search-field {
            position: relative;
            display: flex;
            align-items: center;
        }

        .rich-sidebar-search-icon {
            position: absolute;
            inset-inline-start: 0.7rem;
            height: 1rem;
            width: 1rem;
            color: rgb(161 161 170);
            pointer-events: none;
        }

        .rich-sidebar-search input {
            width: 100%;
            border: 1px solid rgb(228 228 231);
            border-radius: 0.5rem;
            background-color: rgb(255 255 255);
            padding-block: 0.45rem;
            padding-inline: 2.1rem 1.9rem;
            font-size: 0.8125rem;
            line-height: 1.25rem;
            color: rgb(24 24 27);
            box-shadow: 0 1px 2px rgb(0 0 0 / 0.04);
        }

        .rich-sidebar-search input::placeholder {
            color: rgb(161 161 170);
        }

        .rich-sidebar-search input:focus {
            outline: 2px solid rgb(31 66 245);
            outline-offset: -1px;
            border-color: transparent;
        }

        /* The browser's own clear button would sit beside ours. */
        .rich-sidebar-search input::-webkit-search-cancel-button {
            display: none;
        }

        .rich-sidebar-search-clear {
            position: absolute;
            inset-inline-end: 0.5rem;
            display: flex;
            height: 1.25rem;
            width: 1.25rem;
            align-items: center;
            justify-content: center;
            border-radius: 9999px;
            font-size: 1rem;
            line-height: 1;
            color: rgb(113 113 122);
        }

        .rich-sidebar-search-clear:hover {
            background-color: rgb(244 244 245);
            color: rgb(24 24 27);
        }

        .rich-sidebar-search-empty {
            margin-top: 0.5rem;
            padding-inline: 0.15rem;
            font-size: 0.75rem;
            color: rgb(113 113 122);
        }

        .rich-search-hidden {
            display: none !important;
        }

        .dark .rich-sidebar-search input {
            border-color: rgb(63 63 70);
            background-color: rgb(39 39 42);
            color: rgb(244 244 245);
        }

        .dark .rich-sidebar-search-clear:hover {
            background-color: rgb(63 63 70);
            color: rgb(244 244 245);
        }
    </style>

    <script>
        window.richSidebarSearch = () => ({
            query: '',
            matches: 0,

            /* How the groups stood before searching. Searching opens every group
               so a match inside a collapsed one can be seen; clearing the box
               puts them back exactly as they were. */
            collapsedBefore: null,

            init() {
                // Livewire swaps the sidebar out on navigation, which drops the
                // classes below, so the filter is applied again afterwards.
                document.addEventListener('livewire:navigated', () => this.filter())
            },

            sidebar() {
                return this.$el.closest('.fi-sidebar') ?? document
            },

            filter() {
                const query = this.query.trim().toLowerCase()

                query.length ? this.openGroups() : this.restoreGroups()

                this.matches = 0

                this.sidebar().querySelectorAll('.fi-sidebar-item').forEach((item) => {
                    const label = item.querySelector('.fi-sidebar-item-label')?.textContent ?? ''
                    const hit = ! query.length || label.toLowerCase().includes(query)

                    item.classList.toggle('rich-search-hidden', ! hit)

                    if (hit && query.length) this.matches++
                })

                // A group whose entries have all gone takes its heading with it,
                // rather than leaving a label standing over nothing.
                this.sidebar().querySelectorAll('.fi-sidebar-group').forEach((group) => {
                    const items = group.querySelectorAll('.fi-sidebar-item')
                    const visible = [...items].some((item) => ! item.classList.contains('rich-search-hidden'))

                    group.classList.toggle('rich-search-hidden', items.length > 0 && ! visible)
                })
            },

            /** Enter opens the first entry still showing. */
            open() {
                this.sidebar()
                    .querySelector('.fi-sidebar-item:not(.rich-search-hidden) .fi-sidebar-item-btn')
                    ?.click()
            },

            clear() {
                this.query = ''
                this.filter()
                this.$refs.input.focus()
            },

            openGroups() {
                if (this.collapsedBefore !== null) {
                    return
                }

                this.collapsedBefore = [...(this.$store.sidebar.collapsedGroups ?? [])]
                this.$store.sidebar.collapsedGroups = []
            },

            restoreGroups() {
                if (this.collapsedBefore === null) {
                    return
                }

                this.$store.sidebar.collapsedGroups = this.collapsedBefore
                this.collapsedBefore = null
            },
        })
    </script>
@endonce

<div x-data="richSidebarSearch()" x-show="$store.sidebar.isOpen" x-cloak class="rich-sidebar-search">
    <div class="rich-sidebar-search-field">
        <svg class="rich-sidebar-search-icon" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
            <circle cx="9" cy="9" r="6" />
            <path d="m17 17-3.5-3.5" stroke-linecap="round" />
        </svg>

        <input
            type="search"
            x-model="query"
            x-ref="input"
            @input="filter()"
            @keydown.escape.stop="clear()"
            @keydown.enter.prevent="open()"
            placeholder="{{ __('Filter menu…') }}"
            aria-label="{{ __('Filter the menu') }}"
            autocomplete="off"
            spellcheck="false"
        >

        <button type="button" x-show="query.length > 0" @click="clear()" class="rich-sidebar-search-clear" aria-label="{{ __('Clear') }}">&times;</button>
    </div>

    <p x-show="query.length > 0 && matches === 0" class="rich-sidebar-search-empty">
        {{ __('Nothing matches') }} “<span x-text="query"></span>”
    </p>
</div>
