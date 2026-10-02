@php
    $editing = $idea->exists;
    $action = $editing ? route('researcher.ideas.update', $idea) : route('researcher.ideas.store');

    /* Three sittings rather than one scroll, the same as the proposal. The
       steps are presentation only: every field stays in the page and the form
       posts as a whole.

       Each step lists its fields, so a rejected submission opens on the step
       the complaint belongs to. */
    $steps = [
        'where' => ['title', 'department', 'research_field', 'research_area', 'sdgs'],
        'about' => ['problem', 'description', 'motivation', 'expected_contribution'],
        'extras' => ['keywords', 'proposed_team', 'collaboration_requirement', 'document'],
    ];

    $stepNames = array_map(fn (string $key) => __('researcher.ideas.steps.'.$key), array_keys($steps));

    $openStep = 0;

    foreach (array_values($steps) as $i => $names) {
        if ($errors->hasAny($names)) {
            $openStep = $i;
            break;
        }
    }
@endphp

<x-layouts.researcher :title="$editing ? __('researcher.ideas.edit_title') : __('researcher.ideas.new')">

    <x-portal-heading :title="$editing ? __('researcher.ideas.edit_title') : __('researcher.ideas.new')"
                      :lead="__('researcher.ideas.lead')" />

    <div x-data="stepForm({{ count($steps) }}, {{ $openStep }}, @js($stepNames))">

        <x-portal-steps :names="$stepNames" />

    <form method="POST" action="{{ $action }}" enctype="multipart/form-data" class="space-y-6"
          @submit="guard($event)"
          @keydown.enter="if ($event.target.tagName !== 'TEXTAREA' && ! last) { $event.preventDefault(); next() }"
          x-data="ideaForm(@js($tree), @js(old('department', $idea->department)), @js(old('research_field', $idea->research_field)), @js(old('research_area', $idea->research_area)))">
        @csrf
        @if ($editing) @method('PUT') @endif

        @if ($errors->any())
            <div class="rounded-2xl border border-rose-200 bg-rose-50 p-5" role="alert">
                <ul class="space-y-1 text-[13.5px] leading-relaxed text-rose-800">
                    @foreach ($errors->all() as $message)
                        <li>{{ $message }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- ---------------------------------------- where it sits --}}
        <fieldset class="field-group" data-step="0" x-show="step === 0">
            <legend class="field-legend">{{ __('researcher.ideas.form.where') }}</legend>

            <div class="mt-6 grid gap-5 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label for="title" class="field-label">{{ __('researcher.ideas.form.title') }} <span class="text-brand-600">*</span></label>
                    <input id="title" name="title" type="text" required value="{{ old('title', $idea->title) }}" class="field-input mt-2">
                </div>

                <div class="grid gap-5 sm:col-span-2 sm:grid-cols-3">
                    <div>
                        <label for="department" class="field-label">{{ __('research_hub.forms.department') }} <span class="text-brand-600">*</span></label>
                        <select id="department" name="department" required x-model="department" @change="onDepartment()" class="field-input mt-2">
                            <option value="">{{ __('research_hub.forms.choose') }}</option>
                            <template x-for="row in tree" :key="row.department">
                                <option :value="row.department" x-text="row.department"></option>
                            </template>
                        </select>
                    </div>

                    <div>
                        <label for="research_field" class="field-label">{{ __('research_hub.forms.proposal.field') }} <span class="text-brand-600">*</span></label>
                        <select id="research_field" name="research_field" required :disabled="! department" x-model="field" class="field-input mt-2">
                            <option value="">{{ __('research_hub.forms.choose') }}</option>
                            <template x-for="f in fields" :key="f.name">
                                <option :value="f.name" x-text="f.name"></option>
                            </template>
                        </select>
                    </div>

                    <div>
                        <label for="research_area" class="field-label">{{ __('research_hub.forms.proposal.area') }}</label>
                        <select id="research_area" name="research_area" :disabled="! department" x-model="area" class="field-input mt-2">
                            <option value="">{{ __('research_hub.forms.choose') }}</option>
                            <template x-for="a in areas" :key="a">
                                <option :value="a" x-text="a"></option>
                            </template>
                        </select>
                    </div>
                </div>

                {{-- The goals the chosen field already serves, carried forward --}}
                <div x-show="sdgs.length" x-cloak class="rounded-2xl border border-ink-100 bg-ink-50/70 p-4 sm:col-span-2">
                    <p class="text-[10.5px] font-semibold uppercase tracking-[0.16em] text-ink-400">{{ __('research_hub.forms.proposal.sdgs') }}</p>
                    <div class="mt-2.5 flex flex-wrap gap-1.5">
                        <template x-for="sdg in sdgs" :key="sdg">
                            <span class="rounded-lg border border-navy-100 bg-white px-2.5 py-1 text-[12.5px] font-semibold text-navy-700" x-text="sdg"></span>
                        </template>
                    </div>
                    <template x-for="sdg in sdgs" :key="'in-' + sdg">
                        <input type="hidden" name="sdgs[]" :value="sdg">
                    </template>
                </div>
            </div>
        </fieldset>

        {{-- ---------------------------------------------- the idea --}}
        <fieldset class="field-group" data-step="1" x-show="step === 1" x-cloak>
            <legend class="field-legend">{{ __('researcher.ideas.form.about') }}</legend>

            <div class="mt-6 grid gap-5 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label for="problem" class="field-label">{{ __('researcher.ideas.form.problem') }} <span class="text-brand-600">*</span></label>
                    <textarea id="problem" name="problem" rows="4" required class="field-input mt-2">{{ old('problem', $idea->problem) }}</textarea>
                </div>

                <div class="sm:col-span-2">
                    <label for="description" class="field-label">{{ __('researcher.ideas.form.description') }} <span class="text-brand-600">*</span></label>
                    <textarea id="description" name="description" rows="6" required class="field-input mt-2">{{ old('description', $idea->description) }}</textarea>
                </div>

                <div class="contents">
                    <div>
                        <label for="motivation" class="field-label">{{ __('researcher.ideas.form.motivation') }}</label>
                        <textarea id="motivation" name="motivation" rows="4" class="field-input mt-2">{{ old('motivation', $idea->motivation) }}</textarea>
                    </div>
                    <div>
                        <label for="expected_contribution" class="field-label">{{ __('researcher.ideas.form.contribution') }}</label>
                        <textarea id="expected_contribution" name="expected_contribution" rows="4" class="field-input mt-2">{{ old('expected_contribution', $idea->expected_contribution) }}</textarea>
                    </div>
                </div>
            </div>
        </fieldset>

        {{-- ------------------------------------------ supporting --}}
        <fieldset class="field-group" data-step="2" x-show="step === 2" x-cloak>
            <legend class="field-legend">{{ __('researcher.ideas.form.extras') }}</legend>

            <div class="mt-6 grid gap-5 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <x-portal-tag-box name="keywords" :label="__('researcher.ideas.form.keywords')"
                                      :value="old('keywords', $idea->keywords ?? [])" />
                </div>

                <div class="contents">
                    <div>
                        <label for="proposed_team" class="field-label">{{ __('researcher.ideas.form.team') }}</label>
                        <textarea id="proposed_team" name="proposed_team" rows="3" class="field-input mt-2">{{ old('proposed_team', $idea->proposed_team) }}</textarea>
                    </div>
                    <div>
                        <label for="collaboration_requirement" class="field-label">{{ __('researcher.ideas.form.collaboration') }}</label>
                        <input id="collaboration_requirement" name="collaboration_requirement" type="text"
                               value="{{ old('collaboration_requirement', $idea->collaboration_requirement) }}" class="field-input mt-2">
                    </div>
                </div>

                <div class="sm:col-span-2">
                    <label for="document" class="field-label">{{ __('researcher.ideas.form.document') }}</label>
                    <input id="document" name="document" type="file" class="field-input mt-2 file:mr-3 file:rounded-lg file:border-0 file:bg-brand-50 file:px-3 file:py-1.5 file:text-[13px] file:font-semibold file:text-brand-700">
                    <p class="field-help">{{ __('research_hub.forms.document_help') }}</p>
                </div>
            </div>
        </fieldset>

        <div class="sticky bottom-0 z-10 flex flex-wrap items-center gap-3 rounded-[1.25rem] border border-ink-100 bg-white/95 p-4 shadow-[0_-18px_44px_-44px_rgba(2,34,81,0.6)] backdrop-blur-sm">
            <button type="button" x-show="! first" x-cloak @click="back()" class="btn-ghost">
                <x-ui-icon name="arrow-left" class="h-4 w-4" />
                {{ __('researcher.step.back') }}
            </button>

            <span class="flex-1"></span>

            <button type="submit" name="action" value="draft" class="btn-ghost">
                {{ __('researcher.ideas.save_draft') }}
            </button>

            <button type="button" x-show="! last" @click="next()" class="btn-lead group">
                <span class="relative">{{ __('researcher.step.next') }}</span>
                <x-ui-icon name="arrow-right" class="relative h-[18px] w-[18px] transition-transform duration-300 group-hover:translate-x-1" />
            </button>

            <button type="submit" name="action" value="submit" x-show="last" x-cloak class="btn-lead group">
                <span class="relative">{{ __('researcher.ideas.submit') }}</span>
                <x-ui-icon name="arrow-right" class="relative h-[18px] w-[18px] transition-transform duration-300 group-hover:translate-x-1" />
            </button>
        </div>
    </form>
    </div>

</x-layouts.researcher>
