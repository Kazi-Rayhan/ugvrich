@php
    $editing = $proposal->exists;
    $action = $editing ? route('researcher.proposals.update', $proposal) : route('researcher.proposals.store');

    /* Every plain text area on this form, so the markup is written once. The
       proposal is long; repeating twenty near-identical blocks would make it
       harder to read than the form itself. */
    $areas = [
        'problem' => [
            ['background', 4], ['research_gap', 3], ['objectives', 4], ['research_questions', 4], ['hypothesis', 3],
        ],
        'design' => [
            ['methodology', 5], ['population', 3], ['data_collection', 3], ['data_analysis', 3],
        ],
        'outcome' => [
            ['expected_outcome', 4], ['expected_impact', 4],
        ],
    ];

    $labels = [
        'background' => 'background', 'research_gap' => 'gap', 'objectives' => 'objectives',
        'research_questions' => 'questions', 'hypothesis' => 'hypothesis', 'methodology' => 'methodology',
        'population' => 'population', 'data_collection' => 'collection', 'data_analysis' => 'analysis',
        'expected_outcome' => 'outcome', 'expected_impact' => 'impact',
    ];

    /* A proposal asks for a great deal at once, so it is asked for in six
       sittings rather than one scroll. The steps are presentation only: every
       field stays in the page and the form posts as a whole, so the controller
       sees exactly what it saw before.

       Each step lists the fields it holds, so a submission the server turns
       down can open on the step the complaint belongs to. */
    $steps = [
        'where' => ['research_idea_id', 'title', 'department', 'research_field', 'research_area', 'summary', 'sdgs'],
        'problem' => array_column($areas['problem'], 0),
        'design' => ['study_design', ...array_column($areas['design'], 0)],
        'outcome' => array_column($areas['outcome'], 0),
        'practical' => ['duration', 'budget', 'funding_needed', 'funding_type', 'funding_organization', 'principal_investigator', 'collaborators_needed', 'timeline', 'research_team'],
        'documents' => ['proposal_document', 'document'],
    ];

    $keys = array_keys($steps);
    $stepNames = array_map(fn (string $key) => __('researcher.proposals.steps.'.$key), $keys);

    $openStep = 0;

    foreach (array_values($steps) as $i => $names) {
        if ($errors->hasAny($names)) {
            $openStep = $i;
            break;
        }
    }
@endphp

<x-layouts.researcher :title="$editing ? __('researcher.proposals.edit_title') : __('researcher.proposals.new')">

    <x-portal-heading :title="$editing ? __('researcher.proposals.edit_title') : __('researcher.proposals.new')"
                      :lead="__('researcher.proposals.lead')" />

    <div x-data="stepForm({{ count($steps) }}, {{ $openStep }}, @js($stepNames))">

        <x-portal-steps :names="$stepNames" />

        <form method="POST" action="{{ $action }}" enctype="multipart/form-data" class="space-y-6"
              @submit="guard($event)"
              @keydown.enter="if ($event.target.tagName !== 'TEXTAREA' && ! last) { $event.preventDefault(); next() }"
              x-data="ideaForm(@js($tree), @js(old('department', $proposal->department)), @js(old('research_field', $proposal->research_field)), @js(old('research_area', $proposal->research_area)))">
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

            {{-- --------------------------------------- step 1, where it sits --}}
            <fieldset class="field-group" data-step="0" x-show="step === 0">
                <legend class="field-legend">{{ __('researcher.proposals.form.where') }}</legend>
                <p class="field-help">{{ __('researcher.proposals.step_note') }}</p>

                @if ($idea && ! $editing)
                    <div class="mt-5 flex items-start gap-3.5 rounded-2xl border border-gold-200 bg-gold-100/60 p-5">
                        <x-ui-icon name="check" class="mt-0.5 h-5 w-5 shrink-0 text-gold-700" stroke="2.4" />
                        <p class="text-[13.5px] leading-relaxed text-ink-700">{{ __('researcher.proposals.carried') }}</p>
                    </div>
                @endif

                <div class="mt-6 grid gap-5 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label for="research_idea_id" class="field-label">{{ __('researcher.proposals.form.link') }}</label>
                        <select id="research_idea_id" name="research_idea_id" class="field-input mt-2">
                            <option value="">{{ __('researcher.proposals.form.link_none') }}</option>
                            @foreach ($ideas as $option)
                                <option value="{{ $option->id }}" @selected((int) old('research_idea_id', $proposal->research_idea_id) === $option->id)>{{ $option->title }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="sm:col-span-2">
                        <label for="title" class="field-label">{{ __('researcher.proposals.form.title') }} <span class="text-brand-600">*</span></label>
                        <input id="title" name="title" type="text" required value="{{ old('title', $proposal->title) }}" class="field-input mt-2">
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

                    <div class="sm:col-span-2">
                        <label for="summary" class="field-label">{{ __('researcher.proposals.form.summary') }} <span class="text-brand-600">*</span></label>
                        <textarea id="summary" name="summary" rows="5" required class="field-input mt-2">{{ old('summary', $proposal->summary) }}</textarea>
                    </div>

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

            {{-- ------------------------ steps 2-4, problem, design, outcome --}}
            @foreach ($areas as $section => $fields)
                @php $index = array_search($section, $keys, true); @endphp

                <fieldset class="field-group" data-step="{{ $index }}" x-show="step === {{ $index }}" x-cloak>
                    <legend class="field-legend">{{ __('researcher.proposals.form.'.$section) }}</legend>

                    <div class="mt-6 grid gap-5 sm:grid-cols-2">
                        @if ($section === 'design')
                            <div class="sm:col-span-2">
                                <label for="study_design" class="field-label">{{ __('researcher.proposals.form.study_design') }}</label>
                                <input id="study_design" name="study_design" type="text" value="{{ old('study_design', $proposal->study_design) }}" class="field-input mt-2">
                            </div>
                        @endif

                        @foreach ($fields as [$name, $rows])
                            {{-- Four lines or fewer sits in a column; anything
                                 longer takes the whole row. --}}
                            <div @class(['sm:col-span-2' => $rows >= 5])>
                                <label for="{{ $name }}" class="field-label">{{ __('researcher.proposals.form.'.$labels[$name]) }}</label>
                                <textarea id="{{ $name }}" name="{{ $name }}" rows="{{ $rows }}" class="field-input mt-2">{{ old($name, $proposal->{$name}) }}</textarea>
                            </div>
                        @endforeach
                    </div>
                </fieldset>
            @endforeach

            {{-- ---------------------------------------- step 5, practical --}}
            <fieldset class="field-group" data-step="4" x-show="step === 4" x-cloak>
                <legend class="field-legend">{{ __('researcher.proposals.form.practical') }}</legend>

                <div class="mt-6 grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
                    @foreach ([
                        ['duration', 'duration'], ['budget', 'budget'],
                        ['funding_needed', 'funding'], ['principal_investigator', 'pi'],
                        ['collaborators_needed', 'collaborators'],
                    ] as [$name, $label])
                        <div>
                            <label for="{{ $name }}" class="field-label">{{ __('researcher.proposals.form.'.$label) }}</label>
                            <input id="{{ $name }}" name="{{ $name }}" type="text" value="{{ old($name, $proposal->{$name}) }}" class="field-input mt-2">
                        </div>
                    @endforeach
                </div>

                {{-- Where the money would come from. The university's own
                     funds and an outside funder are asked for differently, so
                     the proposal says which it is; naming the funder is
                     optional, because often it is not settled yet. --}}
                <div class="mt-5 rounded-2xl border border-ink-100 bg-ink-50/60 p-5"
                     x-data="{ funding: @js(old('funding_type', $proposal->funding_type ?? '')) }">
                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <label for="funding_type" class="field-label">{{ __('researcher.proposals.form.funding_type') }}</label>
                            <select id="funding_type" name="funding_type" x-model="funding" class="field-input mt-2">
                                <option value="">{{ __('research_hub.forms.choose') }}</option>
                                @foreach (__('researcher.proposals.funding_types') as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div x-show="funding === 'external' || funding === 'both'" x-cloak>
                            <label for="funding_organization" class="field-label">{{ __('researcher.proposals.form.funding_organization') }}</label>
                            <input id="funding_organization" name="funding_organization" type="text" list="funders"
                                   value="{{ old('funding_organization', $proposal->funding_organization) }}" class="field-input mt-2">
                            <p class="field-help">{{ __('researcher.proposals.form.funding_organization_help') }}</p>

                            <datalist id="funders">
                                @foreach (\App\Models\ResearchFunding::organizations() as $funder)
                                    <option value="{{ $funder }}"></option>
                                @endforeach
                            </datalist>
                        </div>
                    </div>
                </div>

                <div class="mt-5 grid gap-5 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label for="timeline" class="field-label">{{ __('researcher.proposals.form.timeline') }}</label>
                        <textarea id="timeline" name="timeline" rows="4" class="field-input mt-2">{{ old('timeline', $proposal->timeline) }}</textarea>
                    </div>
                    <div class="sm:col-span-2">
                        <label for="research_team" class="field-label">{{ __('researcher.proposals.form.team') }}</label>
                        <textarea id="research_team" name="research_team" rows="3" class="field-input mt-2">{{ old('research_team', $proposal->research_team) }}</textarea>
                    </div>
                </div>
            </fieldset>

            {{-- ---------------------------------------- step 6, documents --}}
            <fieldset class="field-group" data-step="5" x-show="step === 5" x-cloak>
                <legend class="field-legend">{{ __('researcher.proposals.form.documents') }}</legend>
                <p class="field-help">{{ __('researcher.proposals.last_note') }}</p>

                <div class="mt-6 grid gap-5 sm:grid-cols-2">
                    @foreach ([['proposal_document', 'proposal_document'], ['document', 'supporting']] as [$name, $label])
                        <div>
                            <label for="{{ $name }}" class="field-label">{{ __('researcher.proposals.form.'.$label) }}</label>
                            <input id="{{ $name }}" name="{{ $name }}" type="file"
                                   class="field-input mt-2 file:mr-3 file:rounded-lg file:border-0 file:bg-brand-50 file:px-3 file:py-1.5 file:text-[13px] file:font-semibold file:text-brand-700">
                            @if ($proposal->{$name})
                                <p class="field-help">{{ basename($proposal->{$name}) }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            </fieldset>

            {{-- ----------------------------------------------- the actions --}}
            <div class="sticky bottom-0 z-10 flex flex-wrap items-center gap-3 rounded-[1.25rem] border border-ink-100 bg-white/95 p-4 shadow-[0_-18px_44px_-44px_rgba(2,34,81,0.6)] backdrop-blur-sm">
                <button type="button" x-show="! first" x-cloak @click="back()" class="btn-ghost">
                    <x-ui-icon name="arrow-left" class="h-4 w-4" />
                    {{ __('researcher.step.back') }}
                </button>

                <span class="flex-1"></span>

                <button type="submit" name="action" value="draft" class="btn-ghost">
                    {{ __('researcher.proposals.save_draft') }}
                </button>

                <button type="button" x-show="! last" @click="next()" class="btn-lead group">
                    <span class="relative">{{ __('researcher.step.next') }}</span>
                    <x-ui-icon name="arrow-right" class="relative h-[18px] w-[18px] transition-transform duration-300 group-hover:translate-x-1" />
                </button>

                <button type="submit" name="action" value="submit" x-show="last" x-cloak class="btn-lead group">
                    <span class="relative">{{ __('researcher.proposals.submit') }}</span>
                    <x-ui-icon name="arrow-right" class="relative h-[18px] w-[18px] transition-transform duration-300 group-hover:translate-x-1" />
                </button>
            </div>
        </form>
    </div>
</x-layouts.researcher>
