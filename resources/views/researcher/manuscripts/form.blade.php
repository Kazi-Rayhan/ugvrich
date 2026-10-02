@php
    use App\Models\ResearchManuscript;

    $editing = $manuscript->exists;
    $action = $editing ? route('researcher.manuscripts.update', $manuscript) : route('researcher.manuscripts.store');

    $f = fn (string $key) => __('researcher.manuscripts.form.'.$key);

    /* Plain text inputs, grouped by the section they belong to, so the markup
       is written once rather than forty near-identical times. */
    $groups = [
        'authors' => [
            ['primary_author', 'primary_author'], ['corresponding_author', 'corresponding'],
            ['affiliation', 'affiliation'], ['orcid', 'orcid'],
        ],
        'journal' => [
            ['target_journal', 'target_journal'], ['publisher', 'publisher'], ['issn', 'issn'],
            ['journal_url', 'journal_url'], ['quartile', 'quartile'], ['impact_factor', 'impact_factor'],
        ],
        'publication' => [
            ['doi', 'doi'], ['volume', 'volume'], ['issue', 'issue'],
            ['pages', 'pages'], ['publication_url', 'publication_url'],
        ],
    ];
@endphp

<x-layouts.researcher :title="$editing ? __('researcher.manuscripts.edit_title') : __('researcher.manuscripts.new')">

    <x-portal-heading :title="$editing ? __('researcher.manuscripts.edit_title') : __('researcher.manuscripts.new')"
                      :lead="__('researcher.manuscripts.lead')" />

    <form method="POST" action="{{ $action }}" enctype="multipart/form-data" class="space-y-6">
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

        {{-- ------------------------------------------- the paper --}}
        <fieldset class="field-group">
            <legend class="field-legend">{{ $f('paper') }}</legend>

            <div class="mt-6 grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
                <div class="sm:col-span-2 xl:col-span-3">
                    <label for="title" class="field-label">{{ $f('title') }} <span class="text-brand-600">*</span></label>
                    <input id="title" name="title" type="text" required value="{{ old('title', $manuscript->title) }}" class="field-input mt-2">
                </div>

                <div class="sm:col-span-2 xl:col-span-3">
                    <label for="abstract" class="field-label">{{ $f('abstract') }} <span class="text-brand-600">*</span></label>
                    <textarea id="abstract" name="abstract" rows="6" required class="field-input mt-2">{{ old('abstract', $manuscript->abstract) }}</textarea>
                </div>

                {{-- Keywords, as tags. The component is defined in the layout. --}}
                <div class="sm:col-span-2 xl:col-span-3">
                    <x-portal-tag-box name="keywords" :label="$f('keywords')"
                                      :value="old('keywords', $manuscript->keywords ?? [])" />
                </div>

                <div class="contents">
                    <div>
                        <label for="manuscript_type" class="field-label">{{ $f('type') }}</label>
                        <select id="manuscript_type" name="manuscript_type" class="field-input mt-2">
                            <option value="">{{ __('research_hub.forms.choose') }}</option>
                            @foreach (ResearchManuscript::types() as $value => $label)
                                <option value="{{ $value }}" @selected(old('manuscript_type', $manuscript->manuscript_type) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="project_id" class="field-label">{{ $f('project') }}</label>
                        <select id="project_id" name="project_id" class="field-input mt-2">
                            <option value="">{{ __('researcher.manuscripts.no_project') }}</option>
                            @foreach ($projects as $project)
                                <option value="{{ $project->id }}" @selected((int) old('project_id', $manuscript->project_id) === $project->id)>{{ $project->title }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="department" class="field-label">{{ $f('department') }}</label>
                        <input id="department" name="department" type="text" value="{{ old('department', $manuscript->department) }}" class="field-input mt-2">
                    </div>

                    <div>
                        <label for="research_field" class="field-label">{{ $f('field') }}</label>
                        <input id="research_field" name="research_field" type="text" value="{{ old('research_field', $manuscript->research_field) }}" class="field-input mt-2">
                    </div>
                </div>
            </div>
        </fieldset>

        {{-- -------------------- authors, journal, publication --}}
        @foreach ($groups as $section => $fields)
            <fieldset class="field-group">
                <legend class="field-legend">{{ $f($section) }}</legend>

                @if ($section === 'publication')
                    <p class="field-help">{{ $f('publication_help') }}</p>
                @endif

                <div class="mt-6 grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
                    @foreach ($fields as [$name, $label])
                        <div>
                            <label for="{{ $name }}" class="field-label">{{ $f($label) }}</label>
                            <input id="{{ $name }}" name="{{ $name }}" type="text" value="{{ old($name, $manuscript->{$name}) }}" class="field-input mt-2">
                        </div>
                    @endforeach

                    @if ($section === 'authors')
                        <div class="sm:col-span-2 xl:col-span-3">
                            <label for="co_authors" class="field-label">{{ $f('co_authors') }}</label>
                            <textarea id="co_authors" name="co_authors" rows="3" class="field-input mt-2">{{ old('co_authors', $manuscript->co_authors) }}</textarea>
                        </div>
                    @endif

                    @if ($section === 'journal')
                        <div class="sm:col-span-2 xl:col-span-3">
                            <label for="journal_scope" class="field-label">{{ $f('journal_scope') }}</label>
                            <textarea id="journal_scope" name="journal_scope" rows="3" class="field-input mt-2">{{ old('journal_scope', $manuscript->journal_scope) }}</textarea>
                        </div>
                    @endif

                    @if ($section === 'publication')
                        <div>
                            <label for="published_on" class="field-label">{{ $f('published_on') }}</label>
                            <input id="published_on" name="published_on" type="date" value="{{ old('published_on', $manuscript->published_on?->toDateString()) }}" class="field-input mt-2">
                        </div>
                    @endif
                </div>
            </fieldset>
        @endforeach

        {{-- ------------------------------------- declarations --}}
        <fieldset class="field-group">
            <legend class="field-legend">{{ $f('declarations') }}</legend>

            <div class="mt-6 grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
                @foreach ([['funding_source', 'funding_source'], ['ethics_approval', 'ethics']] as [$name, $label])
                    <div>
                        <label for="{{ $name }}" class="field-label">{{ $f($label) }}</label>
                        <input id="{{ $name }}" name="{{ $name }}" type="text" value="{{ old($name, $manuscript->{$name}) }}" class="field-input mt-2">
                    </div>
                @endforeach

                @foreach ([['conflict_of_interest', 'conflict'], ['ai_use_declaration', 'ai_use']] as [$name, $label])
                    <div class="sm:col-span-2 xl:col-span-3">
                        <label for="{{ $name }}" class="field-label">{{ $f($label) }}</label>
                        <textarea id="{{ $name }}" name="{{ $name }}" rows="3" class="field-input mt-2">{{ old($name, $manuscript->{$name}) }}</textarea>
                    </div>
                @endforeach
            </div>
        </fieldset>

        {{-- -------------------------------------- bibliography --}}
        <fieldset class="field-group">
            <legend class="field-legend">{{ $f('bibliography') }}</legend>

            <div class="mt-6 grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="citation_style" class="field-label">{{ $f('citation_style') }}</label>
                    <select id="citation_style" name="citation_style" class="field-input mt-2">
                        <option value="">{{ __('research_hub.forms.choose') }}</option>
                        @foreach (ResearchManuscript::citationStyles() as $value => $label)
                            <option value="{{ $value }}" @selected(old('citation_style', $manuscript->citation_style) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="sm:col-span-2">
                    <label for="references_list" class="field-label">{{ $f('references') }}</label>
                    <textarea id="references_list" name="references_list" rows="8" class="field-input mt-2">{{ old('references_list', $manuscript->references_list) }}</textarea>
                </div>
            </div>
        </fieldset>

        {{-- --------------------------------------------- files --}}
        <fieldset class="field-group">
            <legend class="field-legend">{{ $f('files') }}</legend>
            <p class="field-help">{{ __('researcher.manuscripts.private_note') }}</p>

            <div class="mt-6 grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
                @foreach ([['manuscript_file', 'manuscript_file'], ['supplementary_file', 'supplementary'], ['similarity_report', 'similarity']] as [$name, $label])
                    <div>
                        <label for="{{ $name }}" class="field-label">{{ $f($label) }}</label>
                        <input id="{{ $name }}" name="{{ $name }}" type="file"
                               class="field-input mt-2 file:mr-3 file:rounded-lg file:border-0 file:bg-brand-50 file:px-3 file:py-1.5 file:text-[13px] file:font-semibold file:text-brand-700">
                        @if ($manuscript->{$name})
                            <p class="field-help">{{ basename($manuscript->{$name}) }}</p>
                        @endif
                    </div>
                @endforeach

                <div class="sm:col-span-2 xl:col-span-3">
                    <label for="notes" class="field-label">{{ $f('notes') }}</label>
                    <textarea id="notes" name="notes" rows="3" class="field-input mt-2">{{ old('notes', $manuscript->notes) }}</textarea>
                </div>
            </div>
        </fieldset>

        <div class="flex flex-wrap items-center gap-4">
            <button type="submit" name="action" value="submit" class="btn-lead group">
                <span class="relative">{{ __('researcher.manuscripts.submit') }}</span>
                <x-ui-icon name="arrow-right" class="relative h-[18px] w-[18px] transition-transform duration-300 group-hover:translate-x-1" />
            </button>

            <button type="submit" name="action" value="draft" class="btn-ghost">
                {{ __('researcher.manuscripts.save_draft') }}
            </button>
        </div>
    </form>
</x-layouts.researcher>
