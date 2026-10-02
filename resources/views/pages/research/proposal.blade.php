<x-layouts.app :title="__('research_hub.forms.proposal.title')" :description="__('research_hub.forms.proposal.lead')">

    @php
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

            <form method="POST" action="{{ route('research.proposal.store') }}" class="space-y-8"
                  x-data="proposalForm(@js($tree), @js(old('department')), @js(old('research_field')), @js(old('research_area')), @js(old('research_type')), @js(old('researcher_type')))">
                @csrf

                <div class="hidden" aria-hidden="true">
                    <label>Website <input type="text" name="website" tabindex="-1" autocomplete="off"></label>
                </div>

                <fieldset class="field-group">
                    <legend class="{{ $legend }}">{{ __('research_hub.forms.proposal.researcher_information') }}</legend>
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
                            <label for="researcher_type" class="{{ $labelCls }}">{{ __('research_hub.forms.proposal.researcher_type') }} <span class="text-brand-600">*</span></label>
                            <select id="researcher_type" name="researcher_type" required x-model="researcherType" class="mt-2 {{ $field }}">
                                <option value="">{{ __('research_hub.forms.choose') }}</option>
                                @foreach (__('research_hub.forms.proposal.researcher_types') as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('researcher_type') <p class="{{ $error }}">{{ $message }}</p> @enderror
                        </div>
                        <div x-show="researcherType === 'student'" x-cloak>
                            <label for="researcher_department" class="{{ $labelCls }}">{{ __('research_hub.forms.proposal.researcher_department') }} <span class="text-brand-600">*</span></label>
                            <input id="researcher_department" name="researcher_department" type="text" value="{{ old('researcher_department') }}" :required="researcherType === 'student'" class="mt-2 {{ $field }}">
                            @error('researcher_department') <p class="{{ $error }}">{{ $message }}</p> @enderror
                        </div>
                        <div x-show="researcherType === 'professor' || researcherType === 'other'" x-cloak>
                            <label for="designation" class="{{ $labelCls }}">{{ __('research_hub.forms.proposal.designation') }} <span class="text-brand-600">*</span></label>
                            <input id="designation" name="designation" type="text" value="{{ old('designation') }}" :required="researcherType === 'professor' || researcherType === 'other'" class="mt-2 {{ $field }}">
                            @error('designation') <p class="{{ $error }}">{{ $message }}</p> @enderror
                        </div>
                        <div x-show="researcherType === 'other'" x-cloak>
                            <label for="researcher_type_other" class="{{ $labelCls }}">{{ __('research_hub.forms.proposal.researcher_type_other') }} <span class="text-brand-600">*</span></label>
                            <input id="researcher_type_other" name="researcher_type_other" type="text" value="{{ old('researcher_type_other') }}" :required="researcherType === 'other'" class="mt-2 {{ $field }}">
                            @error('researcher_type_other') <p class="{{ $error }}">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="institution" class="{{ $labelCls }}">{{ __('research_hub.forms.proposal.institution') }}</label>
                            <input id="institution" name="institution" type="text" value="{{ old('institution') }}" class="mt-2 {{ $field }}">
                        </div>
                    </div>
                </fieldset>

                <fieldset class="field-group">
                    <legend class="{{ $legend }}">{{ __('research_hub.forms.proposal.research_team') }}</legend>
                    <p class="{{ $help }}">{{ __('research_hub.forms.proposal.team_help') }}</p>

                    <div x-show="studyType === 'collaborative'" x-cloak>
                        <div class="mt-6">
                            <label for="principal_investigator" class="{{ $labelCls }}">{{ __('research_hub.forms.proposal.principal_investigator') }}</label>
                            <input id="principal_investigator" name="principal_investigator" type="text" value="{{ old('principal_investigator') }}" class="mt-2 {{ $field }}">
                        </div>

                        <div class="mt-6" x-data="{ members: @js(old('co_researchers', [])) }">
                            <div class="flex items-center justify-between gap-3">
                                <p class="{{ $labelCls }}">{{ __('research_hub.forms.proposal.co_researchers') }}</p>
                                <button type="button" @click="members.push({name: '', designation: '', department: ''})" class="btn-ghost">{{ __('research_hub.forms.proposal.add_co_researcher') }}</button>
                            </div>
                            <template x-for="(member, index) in members" :key="index">
                                <div class="mt-3 grid gap-3 rounded-2xl border border-ink-100 bg-ink-50/60 p-4 sm:grid-cols-3">
                                    <input :name="`co_researchers[${index}][name]`" x-model="member.name" type="text" placeholder="{{ __('research_hub.forms.name') }}" class="{{ $field }}">
                                    <input :name="`co_researchers[${index}][designation]`" x-model="member.designation" type="text" placeholder="{{ __('research_hub.forms.proposal.designation') }}" class="{{ $field }}">
                                    <div class="flex gap-2">
                                        <input :name="`co_researchers[${index}][department]`" x-model="member.department" type="text" placeholder="{{ __('research_hub.forms.department') }}" class="{{ $field }}">
                                        <button type="button" @click="members.splice(index, 1)" class="btn-ghost" aria-label="{{ __('research_hub.forms.proposal.remove_co_researcher') }}">×</button>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div class="mt-6 grid gap-5 sm:grid-cols-2" x-show="studyType === 'collaborative' || studyType === 'interdisciplinary'" x-cloak>
                        <div>
                            <label for="external_collaborator" class="{{ $labelCls }}">{{ __('research_hub.forms.proposal.external_collaborator') }}</label>
                            <input id="external_collaborator" name="external_collaborator" type="text" value="{{ old('external_collaborator') }}" class="mt-2 {{ $field }}">
                        </div>
                        <div>
                            <label for="external_department" class="{{ $labelCls }}">{{ __('research_hub.forms.proposal.external_department') }}</label>
                            <input id="external_department" name="external_department" type="text" value="{{ old('external_department') }}" class="mt-2 {{ $field }}">
                        </div>
                        <div class="sm:col-span-2">
                            <label for="external_institution" class="{{ $labelCls }}">{{ __('research_hub.forms.proposal.external_institution') }}</label>
                            <input id="external_institution" name="external_institution" type="text" value="{{ old('external_institution') }}" class="mt-2 {{ $field }}">
                        </div>
                    </div>
                </fieldset>

                <fieldset class="field-group">
                    <legend class="{{ $legend }}">{{ __('research_hub.forms.proposal.category') }}</legend>
                    <div class="mt-6 grid gap-5 sm:grid-cols-2">
                        <div>
                            <label for="department" class="{{ $labelCls }}">{{ __('research_hub.forms.department') }} <span class="text-brand-600">*</span></label>
                            <select id="department" name="department" required x-model="department" @change="onDepartment()" class="mt-2 {{ $field }}">
                                <option value="">{{ __('research_hub.forms.choose') }}</option>
                                <template x-for="row in tree" :key="row.department">
                                    <option :value="row.department" x-text="row.department"></option>
                                </template>
                            </select>
                            @error('department') <p class="{{ $error }}">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="research_field" class="{{ $labelCls }}">{{ __('research_hub.forms.proposal.field') }} <span class="text-brand-600">*</span></label>
                            <select id="research_field" name="research_field" required :disabled="! department" x-model="field" class="mt-2 {{ $field }}">
                                <option value="">{{ __('research_hub.forms.choose') }}</option>
                                <template x-for="f in fields" :key="f.name">
                                    <option :value="f.name" x-text="f.name"></option>
                                </template>
                            </select>
                            <p class="{{ $help }}" x-show="! department">{{ __('research_hub.forms.proposal.field_help') }}</p>
                            @error('research_field') <p class="{{ $error }}">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="research_area" class="{{ $labelCls }}">{{ __('research_hub.forms.proposal.area') }}</label>
                            <select id="research_area" name="research_area" :disabled="! department" x-model="area" class="mt-2 {{ $field }}">
                                <option value="">{{ __('research_hub.forms.choose') }}</option>
                                <template x-for="a in areas" :key="a">
                                    <option :value="a" x-text="a"></option>
                                </template>
                            </select>
                        </div>
                        <div class="sm:col-span-2">
                            <label for="research_type" class="{{ $labelCls }}">{{ __('research_hub.forms.proposal.research_type') }} <span class="text-brand-600">*</span></label>
                            <select id="research_type" name="research_type" required x-model="studyType" class="mt-2 {{ $field }}">
                                <option value="">{{ __('research_hub.forms.choose') }}</option>
                                @foreach (__('research_hub.forms.proposal.research_types') as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('research_type') <p class="{{ $error }}">{{ $message }}</p> @enderror
                        </div>
                        <div class="rounded-2xl border border-ink-100 bg-ink-50/70 p-5 sm:col-span-2">
                            <p class="{{ $labelCls }}">{{ __('research_hub.forms.proposal.sdg_alignment') }}</p>
                            <p class="{{ $help }}">{{ __('research_hub.forms.proposal.sdg_alignment_help') }}</p>
                            <div class="mt-4 grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                                @foreach (__('research_hub.sdg.goals') as $number => $goal)
                                    <label class="flex items-start gap-2 rounded-xl border border-ink-100 bg-white p-3 text-[12.5px] leading-snug text-ink-700">
                                        <input type="checkbox" name="sdgs[]" value="SDG {{ $number }}" @checked(in_array('SDG '.$number, old('sdgs', []), true)) class="mt-0.5 rounded border-ink-300 text-brand-600 focus:ring-brand-500">
                                        <span><strong class="text-ink-950">SDG {{ $number }}:</strong> {{ $goal }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </fieldset>

                <fieldset class="field-group">
                    <legend class="{{ $legend }}">{{ __('research_hub.forms.proposal.about') }}</legend>
                    <div class="mt-6 space-y-5">
                        <div>
                            <label for="title" class="{{ $labelCls }}">{{ __('research_hub.forms.proposal.proposal_title') }} <span class="text-brand-600">*</span></label>
                            <input id="title" name="title" type="text" required value="{{ old('title') }}" class="mt-2 {{ $field }}">
                            @error('title') <p class="{{ $error }}">{{ $message }}</p> @enderror
                        </div>
                        @foreach ([
                            'background' => ['background', 5],
                            'research_gap' => ['research_gap', 4],
                            'objectives' => ['objectives', 4],
                            'research_questions' => ['research_questions', 4],
                            'methodology' => ['methodology', 4],
                            'expected_outcome' => ['expected_outcome', 4],
                            'expected_impact' => ['expected_impact', 4],
                            'innovation_novelty' => ['innovation_novelty', 3],
                            'timeline' => ['timeline', 3],
                        ] as $name => [$label, $rows])
                            <div>
                                <label for="{{ $name }}" class="{{ $labelCls }}">{{ __('research_hub.forms.proposal.'.$label) }}</label>
                                <textarea id="{{ $name }}" name="{{ $name }}" rows="{{ $rows }}" class="mt-2 {{ $field }}">{{ old($name) }}</textarea>
                                @error($name) <p class="{{ $error }}">{{ $message }}</p> @enderror
                            </div>
                        @endforeach
                    </div>
                </fieldset>

                <fieldset class="field-group" x-data="{ fundingRequired: @js(old('funding_required', '')) }">
                    <legend class="{{ $legend }}">{{ __('research_hub.forms.proposal.practicalities') }}</legend>
                    <div class="mt-6 grid gap-5 sm:grid-cols-2">
                        <div>
                            <label for="funding_required" class="{{ $labelCls }}">{{ __('research_hub.forms.proposal.funding_required') }}</label>
                            <select id="funding_required" name="funding_required" x-model="fundingRequired" class="mt-2 {{ $field }}">
                                <option value="">{{ __('research_hub.forms.choose') }}</option>
                                <option value="1" @selected(old('funding_required') === '1')>{{ __('research_hub.forms.proposal.yes') }}</option>
                                <option value="0" @selected(old('funding_required') === '0')>{{ __('research_hub.forms.proposal.no') }}</option>
                            </select>
                        </div>
                        <div x-show="fundingRequired === '1'" x-cloak>
                            <label for="budget" class="{{ $labelCls }}">{{ __('research_hub.forms.proposal.estimated_budget') }}</label>
                            <input id="budget" name="budget" type="text" value="{{ old('budget') }}" class="mt-2 {{ $field }}">
                        </div>
                        <div class="sm:col-span-2">
                            <label for="budget_breakdown" class="{{ $labelCls }}">{{ __('research_hub.forms.proposal.budget_breakdown') }}</label>
                            <textarea id="budget_breakdown" name="budget_breakdown" rows="3" class="mt-2 {{ $field }}">{{ old('budget_breakdown') }}</textarea>
                        </div>
                        <div>
                            <label for="funding_source" class="{{ $labelCls }}">{{ __('research_hub.forms.proposal.funding_source') }}</label>
                            <input id="funding_source" name="funding_source" type="text" value="{{ old('funding_source') }}" class="mt-2 {{ $field }}">
                        </div>
                        <div>
                            <label for="external_funding_applied" class="{{ $labelCls }}">{{ __('research_hub.forms.proposal.external_funding_applied') }}</label>
                            <select id="external_funding_applied" name="external_funding_applied" class="mt-2 {{ $field }}">
                                <option value="">{{ __('research_hub.forms.choose') }}</option>
                                <option value="1" @selected(old('external_funding_applied') === '1')>{{ __('research_hub.forms.proposal.yes') }}</option>
                                <option value="0" @selected(old('external_funding_applied') === '0')>{{ __('research_hub.forms.proposal.no') }}</option>
                            </select>
                        </div>
                    </div>
                </fieldset>

                <fieldset class="field-group">
                    <legend class="{{ $legend }}">{{ __('research_hub.forms.proposal.ethical_information') }}</legend>
                    <div class="mt-6 grid gap-5 sm:grid-cols-2">
                        @foreach (['human_participants', 'sensitive_data', 'ethical_approval_required', 'informed_consent_required', 'ai_used'] as $name)
                            <div>
                                <label for="{{ $name }}" class="{{ $labelCls }}">{{ __('research_hub.forms.proposal.'.$name) }}</label>
                                <select id="{{ $name }}" name="{{ $name }}" class="mt-2 {{ $field }}">
                                    <option value="">{{ __('research_hub.forms.choose') }}</option>
                                    <option value="1" @selected(old($name) === '1')>{{ __('research_hub.forms.proposal.yes') }}</option>
                                    <option value="0" @selected(old($name) === '0')>{{ __('research_hub.forms.proposal.no') }}</option>
                                </select>
                            </div>
                        @endforeach
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
            window.proposalForm = (tree, department, field, area, studyType, researcherType) => ({
                tree,
                department: department || '',
                field: field || '',
                area: area || '',
                studyType: studyType || '',
                researcherType: researcherType || '',

                get row() {
                    return this.tree.find((r) => r.department === this.department) || null
                },

                get fields() {
                    return this.row ? this.row.fields : []
                },

                get areas() {
                    return this.row ? this.row.areas : []
                },

                onDepartment() {
                    this.field = ''
                    this.area = ''
                },

            })
        </script>
    @endonce
</x-layouts.app>
