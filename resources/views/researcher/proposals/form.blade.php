@php
    $editing = $proposal->exists;
    $action = $editing ? route('researcher.proposals.update', $proposal) : route('researcher.proposals.store');

    $steps = [
        'researcher' => ['phone', 'researcher_type', 'researcher_type_other', 'designation', 'researcher_department', 'institution', 'department', 'research_field', 'research_area', 'research_type', 'sdgs'],
        'team' => ['principal_investigator', 'co_researchers', 'external_collaborator', 'external_department', 'external_institution'],
        'proposal' => ['title', 'background', 'research_gap', 'objectives', 'research_questions', 'methodology', 'expected_outcome', 'expected_impact', 'innovation_novelty', 'timeline'],
        'funding' => ['funding_required', 'budget', 'budget_breakdown', 'funding_source', 'external_funding_applied'],
        'ethics' => ['human_participants', 'sensitive_data', 'ethical_approval_required', 'informed_consent_required', 'ai_used'],
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

    <div x-data="stepForm({{ count($steps) }}, {{ $openStep }}, @js($stepNames), true, @js(old('research_type', $proposal->research_type ?? '')))"
         @proposal-research-type-change="updateResearchType($event.detail)">
        <x-portal-steps :names="$stepNames" />

        <form method="POST" action="{{ $action }}" class="space-y-6"
              @submit="guard($event)"
              @keydown.enter="if ($event.target.tagName !== 'TEXTAREA' && ! last) { $event.preventDefault(); next() }"
              x-data="ideaForm(@js($tree), @js(old('department', $proposal->department)), @js(old('research_field', $proposal->research_field)), @js(old('research_area', $proposal->research_area)), @js(old('research_type', $proposal->research_type ?? '')), @js(old('researcher_type', $proposal->researcher_type ?? '')))">
            @csrf
            @if ($editing) @method('PUT') @endif
            @if ($proposal->research_idea_id)
                <input type="hidden" name="research_idea_id" value="{{ $proposal->research_idea_id }}">
            @endif

            @if ($errors->any())
                <div class="rounded-2xl border border-rose-200 bg-rose-50 p-5" role="alert">
                    <ul class="space-y-1 text-[13.5px] leading-relaxed text-rose-800">
                        @foreach ($errors->all() as $message)
                            <li>{{ $message }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <fieldset class="field-group" data-step="0" x-show="step === 0">
                <legend class="field-legend">{{ __('research_hub.forms.proposal.researcher_information') }}</legend>
                <div class="mt-6 grid gap-5 sm:grid-cols-2">
                    <div>
                        <label class="field-label">{{ __('research_hub.forms.name') }}</label>
                        <input type="text" value="{{ $proposal->name }}" readonly class="field-input mt-2 bg-ink-50">
                    </div>
                    <div>
                        <label class="field-label">{{ __('research_hub.forms.email') }}</label>
                        <input type="email" value="{{ $proposal->email }}" readonly class="field-input mt-2 bg-ink-50">
                    </div>
                    <div>
                        <label for="phone" class="field-label">{{ __('research_hub.forms.phone') }}</label>
                        <input id="phone" name="phone" type="tel" value="{{ old('phone', $proposal->phone) }}" class="field-input mt-2">
                    </div>
                    <div>
                        <label for="researcher_type" class="field-label">{{ __('research_hub.forms.proposal.researcher_type') }}</label>
                        <select id="researcher_type" name="researcher_type" x-model="researcherType" class="field-input mt-2">
                            <option value="">{{ __('research_hub.forms.choose') }}</option>
                            @foreach (__('research_hub.forms.proposal.researcher_types') as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div x-show="researcherType === 'student'" x-cloak>
                        <label for="researcher_department" class="field-label">{{ __('research_hub.forms.proposal.researcher_department') }}</label>
                        <input id="researcher_department" name="researcher_department" type="text" value="{{ old('researcher_department', $proposal->researcher_department) }}" :required="researcherType === 'student'" class="field-input mt-2">
                    </div>
                    <div x-show="researcherType === 'professor' || researcherType === 'other'" x-cloak>
                        <label for="designation" class="field-label">{{ __('research_hub.forms.proposal.designation') }}</label>
                        <input id="designation" name="designation" type="text" value="{{ old('designation', $proposal->designation) }}" :required="researcherType === 'professor' || researcherType === 'other'" class="field-input mt-2">
                    </div>
                    <div x-show="researcherType === 'other'" x-cloak>
                        <label for="researcher_type_other" class="field-label">{{ __('research_hub.forms.proposal.researcher_type_other') }}</label>
                        <input id="researcher_type_other" name="researcher_type_other" type="text" value="{{ old('researcher_type_other', $proposal->researcher_type_other) }}" :required="researcherType === 'other'" class="field-input mt-2">
                    </div>
                    <div>
                        <label for="institution" class="field-label">{{ __('research_hub.forms.proposal.institution') }}</label>
                        <input id="institution" name="institution" type="text" value="{{ old('institution', $proposal->institution) }}" class="field-input mt-2">
                    </div>
                </div>
            </fieldset>

            <fieldset class="field-group" data-step="0" x-show="step === 0" x-cloak>
                <legend class="field-legend">{{ __('research_hub.forms.proposal.category') }}</legend>
                <div class="mt-6 grid gap-5 sm:grid-cols-2">
                    <div>
                        <label for="department" class="field-label">{{ __('research_hub.forms.department') }}</label>
                        <select id="department" name="department" x-model="department" @change="onDepartment()" class="field-input mt-2">
                            <option value="">{{ __('research_hub.forms.choose') }}</option>
                            <template x-for="row in tree" :key="row.department">
                                <option :value="row.department" x-text="row.department"></option>
                            </template>
                        </select>
                    </div>
                    <div>
                        <label for="research_field" class="field-label">{{ __('research_hub.forms.proposal.field') }}</label>
                        <select id="research_field" name="research_field" :disabled="! department" x-model="field" class="field-input mt-2">
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
                    <div class="sm:col-span-2">
                        <label for="research_type" class="field-label">{{ __('research_hub.forms.proposal.research_type') }}</label>
                        <select id="research_type" name="research_type" x-model="researchType"
                                @change="$dispatch('proposal-research-type-change', $event.target.value)"
                                class="field-input mt-2">
                            <option value="">{{ __('research_hub.forms.choose') }}</option>
                            @foreach (__('research_hub.forms.proposal.research_types') as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="rounded-2xl border border-ink-100 bg-ink-50/70 p-4 sm:col-span-2">
                        <p class="field-label">{{ __('research_hub.forms.proposal.sdg_alignment') }}</p>
                        <p class="field-help">{{ __('research_hub.forms.proposal.sdg_alignment_help') }}</p>
                        <div class="mt-3 grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach (__('research_hub.sdg.goals') as $number => $goal)
                                <label class="flex items-start gap-2 rounded-xl border border-ink-100 bg-white p-3 text-[12px] leading-snug text-ink-700">
                                    <input type="checkbox" name="sdgs[]" value="SDG {{ $number }}"
                                           @checked(in_array('SDG '.$number, old('sdgs', $proposal->sdgs ?? []), true))
                                           class="mt-0.5 rounded border-ink-300 text-brand-600 focus:ring-brand-500">
                                    <span><strong class="text-ink-950">SDG {{ $number }}:</strong> {{ $goal }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>
            </fieldset>

            <fieldset class="field-group" data-step="1" x-show="step === 1 && teamVisible" x-cloak>
                <legend class="field-legend">{{ __('research_hub.forms.proposal.research_team') }}</legend>
                <p class="field-help" x-show="researchType !== 'individual'">{{ __('research_hub.forms.proposal.team_help') }}</p>

                <div x-show="researchType === 'collaborative'" x-cloak>
                    <div class="mt-6">
                        <label for="principal_investigator" class="field-label">{{ __('research_hub.forms.proposal.principal_investigator') }}</label>
                        <input id="principal_investigator" name="principal_investigator" type="text" value="{{ old('principal_investigator', $proposal->principal_investigator) }}" class="field-input mt-2">
                    </div>

                    <div class="mt-6" x-data="{ members: @js(old('co_researchers', $proposal->co_researchers ?? [])) }">
                        <div class="flex items-center justify-between gap-3">
                            <p class="field-label">{{ __('research_hub.forms.proposal.co_researchers') }}</p>
                            <button type="button" @click="members.push({name: '', designation: '', department: ''})" class="btn-ghost">{{ __('research_hub.forms.proposal.add_co_researcher') }}</button>
                        </div>
                        <template x-for="(member, index) in members" :key="index">
                            <div class="mt-3 grid gap-3 rounded-2xl border border-ink-100 bg-ink-50/60 p-4 sm:grid-cols-3">
                                <input :name="`co_researchers[${index}][name]`" x-model="member.name" type="text" placeholder="{{ __('research_hub.forms.name') }}" class="field-input">
                                <input :name="`co_researchers[${index}][designation]`" x-model="member.designation" type="text" placeholder="{{ __('research_hub.forms.proposal.designation') }}" class="field-input">
                                <div class="flex gap-2">
                                    <input :name="`co_researchers[${index}][department]`" x-model="member.department" type="text" placeholder="{{ __('research_hub.forms.department') }}" class="field-input">
                                    <button type="button" @click="members.splice(index, 1)" class="btn-ghost" aria-label="{{ __('research_hub.forms.proposal.remove_co_researcher') }}">×</button>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <div class="mt-6 grid gap-5 sm:grid-cols-2" x-show="researchType === 'collaborative' || researchType === 'interdisciplinary'" x-cloak>
                    @foreach (['external_collaborator', 'external_department', 'external_institution'] as $name)
                        <div @class(['sm:col-span-2' => $name === 'external_institution'])>
                            <label for="{{ $name }}" class="field-label">{{ __('research_hub.forms.proposal.'.$name) }}</label>
                            <input id="{{ $name }}" name="{{ $name }}" type="text" value="{{ old($name, $proposal->{$name}) }}" class="field-input mt-2">
                        </div>
                    @endforeach
                </div>
            </fieldset>

            <fieldset class="field-group" data-step="2" x-show="step === 2" x-cloak>
                <legend class="field-legend">{{ __('researcher.proposals.form.proposal') }}</legend>
                <div class="mt-6 space-y-5">
                    <div>
                        <label for="title" class="field-label">{{ __('researcher.proposals.form.title') }}</label>
                        <input id="title" name="title" type="text" value="{{ old('title', $proposal->title) }}" class="field-input mt-2">
                    </div>
                    @foreach ([
                        'background' => ['background', 5],
                        'research_gap' => ['gap', 4],
                        'objectives' => ['objectives', 4],
                        'research_questions' => ['questions', 4],
                        'methodology' => ['methodology', 4],
                        'expected_outcome' => ['outcome', 4],
                        'expected_impact' => ['impact', 4],
                        'innovation_novelty' => ['innovation_novelty', 3],
                        'timeline' => ['timeline', 3],
                    ] as $name => [$label, $rows])
                        <div>
                            <label for="{{ $name }}" class="field-label">{{ __('research_hub.forms.proposal.'.$label) }}</label>
                            <textarea id="{{ $name }}" name="{{ $name }}" rows="{{ $rows }}" class="field-input mt-2">{{ old($name, $name === 'background' ? ($proposal->background ?? $proposal->summary) : $proposal->{$name}) }}</textarea>
                        </div>
                    @endforeach
                </div>
            </fieldset>

            <fieldset class="field-group" data-step="3" x-show="step === 3" x-cloak
                      x-data="{ fundingRequired: @js(old('funding_required', $proposal->funding_required === null ? '' : ($proposal->funding_required ? '1' : '0'))) }">
                <legend class="field-legend">{{ __('researcher.proposals.form.funding') }}</legend>
                <div class="mt-6 grid gap-5 sm:grid-cols-2">
                    <div>
                        <label for="funding_required" class="field-label">{{ __('research_hub.forms.proposal.funding_required') }}</label>
                        <select id="funding_required" name="funding_required" x-model="fundingRequired" class="field-input mt-2">
                            <option value="">{{ __('research_hub.forms.choose') }}</option>
                            <option value="1" @selected(old('funding_required', $proposal->funding_required) === '1' || old('funding_required', $proposal->funding_required) === true)>{{ __('research_hub.forms.proposal.yes') }}</option>
                            <option value="0" @selected(old('funding_required', $proposal->funding_required) === '0' || old('funding_required', $proposal->funding_required) === false)>{{ __('research_hub.forms.proposal.no') }}</option>
                        </select>
                    </div>
                    <div x-show="fundingRequired === '1'" x-cloak>
                        <label for="budget" class="field-label">{{ __('research_hub.forms.proposal.estimated_budget') }}</label>
                        <input id="budget" name="budget" type="text" value="{{ old('budget', $proposal->budget) }}" class="field-input mt-2">
                    </div>
                    <div class="sm:col-span-2">
                        <label for="budget_breakdown" class="field-label">{{ __('research_hub.forms.proposal.budget_breakdown') }}</label>
                        <textarea id="budget_breakdown" name="budget_breakdown" rows="3" class="field-input mt-2">{{ old('budget_breakdown', $proposal->budget_breakdown) }}</textarea>
                    </div>
                    <div>
                        <label for="funding_source" class="field-label">{{ __('research_hub.forms.proposal.funding_source') }}</label>
                        <input id="funding_source" name="funding_source" type="text" value="{{ old('funding_source', $proposal->funding_source) }}" class="field-input mt-2">
                    </div>
                    <div>
                        <label for="external_funding_applied" class="field-label">{{ __('research_hub.forms.proposal.external_funding_applied') }}</label>
                        <select id="external_funding_applied" name="external_funding_applied" class="field-input mt-2">
                            <option value="">{{ __('research_hub.forms.choose') }}</option>
                            <option value="1" @selected(old('external_funding_applied', $proposal->external_funding_applied) === '1' || old('external_funding_applied', $proposal->external_funding_applied) === true)>{{ __('research_hub.forms.proposal.yes') }}</option>
                            <option value="0" @selected(old('external_funding_applied', $proposal->external_funding_applied) === '0' || old('external_funding_applied', $proposal->external_funding_applied) === false)>{{ __('research_hub.forms.proposal.no') }}</option>
                        </select>
                    </div>
                </div>
            </fieldset>

            <fieldset class="field-group" data-step="4" x-show="step === 4" x-cloak>
                <legend class="field-legend">{{ __('research_hub.forms.proposal.ethical_information') }}</legend>
                <div class="mt-6 grid gap-5 sm:grid-cols-2">
                    @foreach (['human_participants', 'sensitive_data', 'ethical_approval_required', 'informed_consent_required', 'ai_used'] as $name)
                        <div>
                            <label for="{{ $name }}" class="field-label">{{ __('research_hub.forms.proposal.'.$name) }}</label>
                            <select id="{{ $name }}" name="{{ $name }}" class="field-input mt-2">
                                <option value="">{{ __('research_hub.forms.choose') }}</option>
                                <option value="1" @selected(old($name, $proposal->{$name}) === '1' || old($name, $proposal->{$name}) === true)>{{ __('research_hub.forms.proposal.yes') }}</option>
                                <option value="0" @selected(old($name, $proposal->{$name}) === '0' || old($name, $proposal->{$name}) === false)>{{ __('research_hub.forms.proposal.no') }}</option>
                            </select>
                        </div>
                    @endforeach
                </div>
            </fieldset>

            <div class="sticky bottom-0 z-10 flex flex-wrap items-center gap-3 rounded-[1.25rem] border border-ink-100 bg-white/95 p-4 shadow-[0_-18px_44px_-44px_rgba(2,34,81,0.6)] backdrop-blur-sm">
                <button type="button" x-show="! first" x-cloak @click="back()" class="btn-ghost">
                    <x-ui-icon name="arrow-left" class="h-4 w-4" />
                    {{ __('researcher.step.back') }}
                </button>
                <span class="flex-1"></span>
                <button type="submit" name="action" value="draft" class="btn-ghost">{{ __('researcher.proposals.save_draft') }}</button>
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
