<x-layouts.app :title="__('research_hub.forms.proposal.title')" :description="__('research_hub.forms.proposal.lead')">

    @php
        // Defined once in app.css, so every field on both forms matches.
        $field = 'field-input';
        $labelCls = 'field-label';
        $help = 'field-help';
        $legend = 'field-legend';
        $error = 'field-error';
    @endphp

    <section class="relative isolate overflow-hidden bg-navy-700 py-14 text-white sm:py-20">
        <div class="pointer-events-none absolute inset-0 -z-10 text-white grid-overlay opacity-[0.12]" aria-hidden="true"></div>

        <div class="container-rich">
            <nav class="flex flex-wrap items-center gap-2 text-[13px] text-white/60" aria-label="Breadcrumb">
                <a href="{{ route('research') }}" class="transition hover:text-white">{{ __('site.nav.research') }}</a>
                <span aria-hidden="true">/</span>
                <span class="text-white/85">{{ __('research_hub.forms.proposal.eyebrow') }}</span>
            </nav>

            <h1 class="mt-6 max-w-3xl font-display text-[32px] font-bold leading-[1.05] tracking-[-0.02em] !text-white sm:text-[46px]">
                {{ __('research_hub.forms.proposal.title') }}
            </h1>
            <p class="mt-5 max-w-2xl text-[16.5px] leading-[1.8] text-white/75">{{ __('research_hub.forms.proposal.lead') }}</p>
        </div>
    </section>

    <section class="bg-white py-14 sm:py-20">
        <div class="container-rich max-w-3xl">
            @if ($errors->any())
                <div class="mb-8 flex gap-4 rounded-2xl border border-rose-200 bg-rose-50 p-5" role="alert">
                    <span class="mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-rose-600 text-white">
                        <x-ui-icon name="x" class="h-4 w-4" stroke="2.4" />
                    </span>
                    <ul class="space-y-1.5 text-[14px] leading-relaxed text-rose-800">
                        @foreach ($errors->all() as $message)
                            <li>{{ $message }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- The cascade runs in the browser off the framework's own tree, so
                 choosing a department narrows the fields and the areas without a
                 round trip. The pairing is checked again on the server — these
                 are select boxes, but the second one is filled by script. --}}
            <form method="POST" action="{{ route('research.proposal.store') }}" enctype="multipart/form-data" class="space-y-12"
                  x-data="proposalForm(@js($tree), @js(old('department')), @js(old('research_field')), @js(old('research_area')))">
                @csrf

                <div class="hidden" aria-hidden="true">
                    <label>Website <input type="text" name="website" tabindex="-1" autocomplete="off"></label>
                </div>

                {{-- -------------------------------------- where it sits --}}
                <fieldset class="field-group">
                    <legend class="{{ $legend }}">{{ __('research_hub.forms.proposal.where') }}</legend>
                    <p class="{{ $help }}">{{ __('research_hub.forms.proposal.where_help') }}</p>

                    <ol class="relative mt-7 space-y-7">
                        {{-- A line down the three steps, so they read as one path
                             rather than three separate questions. --}}
                        <span class="pointer-events-none absolute bottom-6 left-4 top-6 w-px bg-ink-100" aria-hidden="true"></span>

                        {{-- 1. Department --}}
                        <li class="relative ps-12">
                            <span class="absolute left-0 top-1.5 flex h-8 w-8 items-center justify-center rounded-full bg-brand-600 font-numeric text-[13px] font-bold text-white shadow-[0_6px_16px_-6px_var(--color-brand-600)]">1</span>

                            <label for="department" class="{{ $labelCls }}">{{ __('research_hub.forms.department') }} <span class="text-brand-600">*</span></label>
                            <select id="department" name="department" required x-model="department" @change="onDepartment()" class="mt-2 {{ $field }}">
                                <option value="">{{ __('research_hub.forms.choose') }}</option>
                                <template x-for="row in tree" :key="row.department">
                                    <option :value="row.department" x-text="row.department"></option>
                                </template>
                            </select>
                            @error('department') <p class="{{ $error }}">{{ $message }}</p> @enderror
                        </li>

                        {{-- 2. Research field --}}
                        <li class="relative ps-12">
                            <span class="absolute left-0 top-1.5 flex h-8 w-8 items-center justify-center rounded-full font-numeric text-[13px] font-bold transition duration-300"
                                  :class="department ? 'bg-brand-600 text-white shadow-[0_6px_16px_-6px_var(--color-brand-600)]' : 'bg-ink-100 text-ink-400'">2</span>

                            <label for="research_field" class="{{ $labelCls }}">{{ __('research_hub.forms.proposal.field') }} <span class="text-brand-600">*</span></label>
                            <select id="research_field" name="research_field" required :disabled="! department" x-model="field" @change="onField()" class="mt-2 {{ $field }}">
                                <option value="">{{ __('research_hub.forms.choose') }}</option>
                                <template x-for="f in fields" :key="f.name">
                                    <option :value="f.name" x-text="f.name"></option>
                                </template>
                            </select>
                            <p class="{{ $help }}" x-show="! department">{{ __('research_hub.forms.proposal.field_help') }}</p>
                            @error('research_field') <p class="{{ $error }}">{{ $message }}</p> @enderror
                        </li>

                        {{-- 3. Research area --}}
                        <li class="relative ps-12">
                            <span class="absolute left-0 top-1.5 flex h-8 w-8 items-center justify-center rounded-full font-numeric text-[13px] font-bold transition duration-300"
                                  :class="field ? 'bg-brand-600 text-white shadow-[0_6px_16px_-6px_var(--color-brand-600)]' : 'bg-ink-100 text-ink-400'">3</span>

                            <label for="research_area" class="{{ $labelCls }}">{{ __('research_hub.forms.proposal.area') }}</label>
                            <select id="research_area" name="research_area" :disabled="! department" x-model="area" class="mt-2 {{ $field }}">
                                <option value="">{{ __('research_hub.forms.choose') }}</option>
                                <template x-for="a in areas" :key="a">
                                    <option :value="a" x-text="a"></option>
                                </template>
                            </select>
                            <p class="{{ $help }}">{{ __('research_hub.forms.proposal.area_help') }}</p>
                        </li>
                    </ol>

                    {{-- The SDGs the chosen field already serves, from the framework --}}
                    <div x-show="sdgs.length" x-cloak class="mt-6 rounded-2xl border border-ink-100 bg-ink-50/70 p-5">
                        <p class="text-[10.5px] font-semibold uppercase tracking-[0.16em] text-ink-400">{{ __('research_hub.forms.proposal.sdgs') }}</p>
                        <div class="mt-3 flex flex-wrap gap-1.5">
                            <template x-for="sdg in sdgs" :key="sdg">
                                <span class="rounded-lg border border-navy-100 bg-white px-2.5 py-1 text-[12.5px] font-semibold text-navy-700" x-text="sdg"></span>
                            </template>
                        </div>
                        <template x-for="sdg in sdgs" :key="'input-' + sdg">
                            <input type="hidden" name="sdgs[]" :value="sdg">
                        </template>
                        <p class="{{ $help }}">{{ __('research_hub.forms.proposal.sdgs_help') }}</p>
                    </div>
                </fieldset>

                {{-- ------------------------------------------ the work --}}
                <fieldset class="field-group">
                    <legend class="{{ $legend }}">{{ __('research_hub.forms.proposal.about') }}</legend>

                    <div class="mt-6 space-y-5">
                        <div>
                            <label for="title" class="{{ $labelCls }}">{{ __('research_hub.forms.proposal.proposal_title') }} <span class="text-brand-600">*</span></label>
                            <input id="title" name="title" type="text" required value="{{ old('title') }}" class="mt-2 {{ $field }}">
                            @error('title') <p class="{{ $error }}">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="summary" class="{{ $labelCls }}">{{ __('research_hub.forms.proposal.summary') }} <span class="text-brand-600">*</span></label>
                            <textarea id="summary" name="summary" rows="6" required class="mt-2 {{ $field }}">{{ old('summary') }}</textarea>
                            <p class="{{ $help }}">{{ __('research_hub.forms.proposal.summary_help') }}</p>
                            @error('summary') <p class="{{ $error }}">{{ $message }}</p> @enderror
                        </div>

                        <div class="grid gap-5 sm:grid-cols-2">
                            <div>
                                <label for="objectives" class="{{ $labelCls }}">{{ __('research_hub.forms.proposal.objectives') }}</label>
                                <textarea id="objectives" name="objectives" rows="4" class="mt-2 {{ $field }}">{{ old('objectives') }}</textarea>
                            </div>
                            <div>
                                <label for="methodology" class="{{ $labelCls }}">{{ __('research_hub.forms.proposal.methodology') }}</label>
                                <textarea id="methodology" name="methodology" rows="4" class="mt-2 {{ $field }}">{{ old('methodology') }}</textarea>
                            </div>
                        </div>
                    </div>
                </fieldset>

                {{-- -------------------------------------- practicalities --}}
                <fieldset class="field-group">
                    <legend class="{{ $legend }}">{{ __('research_hub.forms.proposal.practicalities') }}</legend>

                    <div class="mt-6 grid gap-5 sm:grid-cols-3">
                        <div>
                            <label for="duration" class="{{ $labelCls }}">{{ __('research_hub.forms.proposal.duration') }}</label>
                            <input id="duration" name="duration" type="text" value="{{ old('duration') }}" class="mt-2 {{ $field }}">
                        </div>
                        <div>
                            <label for="collaborators_needed" class="{{ $labelCls }}">{{ __('research_hub.forms.proposal.collaborators') }}</label>
                            <input id="collaborators_needed" name="collaborators_needed" type="text" value="{{ old('collaborators_needed') }}" class="mt-2 {{ $field }}">
                        </div>
                        <div>
                            <label for="funding_needed" class="{{ $labelCls }}">{{ __('research_hub.forms.proposal.funding') }}</label>
                            <input id="funding_needed" name="funding_needed" type="text" value="{{ old('funding_needed') }}" class="mt-2 {{ $field }}">
                        </div>
                    </div>

                    <div class="mt-5">
                        <label for="document" class="{{ $labelCls }}">{{ __('research_hub.forms.document') }}</label>
                        <input id="document" name="document" type="file" class="mt-2 {{ $field }} file:mr-3 file:rounded-lg file:border-0 file:bg-brand-50 file:px-3 file:py-1.5 file:text-[13px] file:font-semibold file:text-brand-700">
                        <p class="{{ $help }}">{{ __('research_hub.forms.document_help') }}</p>
                        @error('document') <p class="{{ $error }}">{{ $message }}</p> @enderror
                    </div>
                </fieldset>

                {{-- --------------------------------------------- about you --}}
                <fieldset class="field-group">
                    <legend class="{{ $legend }}">{{ __('research_hub.forms.you') }}</legend>

                    <div class="mt-6 grid gap-5 sm:grid-cols-2">
                        <div>
                            <label for="name" class="{{ $labelCls }}">{{ __('research_hub.forms.name') }} <span class="text-brand-600">*</span></label>
                            <input id="name" name="name" type="text" required value="{{ old('name') }}" class="mt-2 {{ $field }}">
                            @error('name') <p class="{{ $error }}">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="email" class="{{ $labelCls }}">{{ __('research_hub.forms.email') }} <span class="text-brand-600">*</span></label>
                            <input id="email" name="email" type="email" required value="{{ old('email') }}" class="mt-2 {{ $field }}">
                            @error('email') <p class="{{ $error }}">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="phone" class="{{ $labelCls }}">{{ __('research_hub.forms.phone') }}</label>
                            <input id="phone" name="phone" type="tel" value="{{ old('phone') }}" class="mt-2 {{ $field }}">
                        </div>
                        <div>
                            <label for="role" class="{{ $labelCls }}">{{ __('research_hub.forms.role') }}</label>
                            <select id="role" name="role" class="mt-2 {{ $field }}">
                                <option value="">{{ __('research_hub.forms.choose') }}</option>
                                @foreach (__('research_hub.forms.roles') as $role)
                                    <option value="{{ $role }}" @selected(old('role') === $role)>{{ $role }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </fieldset>

                <div class="flex flex-wrap items-center gap-5 pt-2">
                    <button type="submit" class="btn-lead group">
                        {{ __('research_hub.forms.proposal.submit') }} <x-ui-icon name="arrow-right" class="h-[18px] w-[18px] transition-transform duration-300 group-hover:translate-x-1" />
                    </button>
                    <a href="{{ route('research.support') }}" class="text-[13.5px] font-semibold text-brand-700 transition hover:text-brand-600">
                        {{ __('research_hub.forms.thanks.other_support') }}
                    </a>
                </div>
            </form>
        </div>
    </section>

    @once
        <script>
            /* Department → field → area, off the framework's own tree.
               Defined as a global before Alpine starts, rather than on
               alpine:init, so it is there whichever order the scripts land. */
            window.proposalForm = (tree, department, field, area) => ({
                tree,
                department: department || '',
                field: field || '',
                area: area || '',

                get row() {
                    return this.tree.find((r) => r.department === this.department) || null
                },

                get fields() {
                    return this.row ? this.row.fields : []
                },

                get areas() {
                    return this.row ? this.row.areas : []
                },

                /* The goals the chosen field already serves, split off the
                   framework's "SDG 4, 10" string. */
                get sdgs() {
                    const chosen = this.fields.find((f) => f.name === this.field)

                    if (! chosen || ! chosen.sdgs) {
                        return []
                    }

                    return chosen.sdgs.split(',').map((s) => s.trim()).filter(Boolean)
                },

                onDepartment() {
                    // The old field and area belong to the old department.
                    this.field = ''
                    this.area = ''
                },

                onField() {
                    // Nothing to reset; the areas follow the department, not the field.
                },
            })
        </script>
    @endonce
</x-layouts.app>
