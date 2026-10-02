@props(['name', 'label' => null, 'value' => []])

{{-- A box of tags: keywords, research interests, languages.

     Only a committed tag has an input to post with, so the box commits what has
     been typed on Enter, a comma, Tab, the Add button, or simply on leaving the
     field. Before, a word typed and then saved went nowhere. --}}
<div x-data="tagInput(@js(array_values((array) $value)))">
    @if ($label)
        <label for="{{ $name }}-tag" class="field-label">{{ $label }}</label>
    @endif

    <div class="field-input mt-2 flex flex-wrap items-center gap-1.5" @click="$refs.draft.focus()">
        <template x-for="(tag, i) in tags" :key="tag + i">
            <span class="inline-flex items-center gap-1.5 rounded-lg bg-brand-50 px-2.5 py-1 text-[12.5px] font-semibold text-brand-800">
                <span x-text="tag"></span>
                <button type="button" @click.stop="remove(i)" class="text-brand-500 transition hover:text-brand-800" aria-label="remove">&times;</button>
                <input type="hidden" name="{{ $name }}[]" :value="tag">
            </span>
        </template>

        <input id="{{ $name }}-tag" type="text" x-ref="draft" x-model="draft"
               @keydown.enter.prevent="add()" @keydown.comma.prevent="add()" @keydown.tab="add()" @blur="add()"
               class="min-w-[7rem] flex-1 border-0 bg-transparent p-0 text-[14px] focus:outline-none focus:ring-0"
               placeholder="{{ __('researcher.profile.tags_hint') }}">

        <button type="button" x-show="draft.trim().length" x-cloak @click="add()"
                class="rounded-lg bg-brand-600 px-2.5 py-1 text-[12px] font-semibold text-white transition hover:bg-brand-700">
            {{ __('researcher.profile.tags_add') }}
        </button>
    </div>
</div>
