@props(['name', 'label', 'tags'])

<div data-expert-tags>
    <label for="{{ $name }}" class="field-label">{{ $label }}</label>
    <p id="{{ $name }}-help" class="mt-1 text-[12.5px] text-ink-600">{{ __('consultancy_engagement.tags_hint') }}</p>
    <input id="{{ $name }}" name="{{ $name }}" value="{{ old($name, implode(', ', $tags)) }}" maxlength="4000" aria-describedby="{{ $name }}-help" class="field-input mt-2">
    <ul data-tag-preview aria-hidden="true" class="mt-2 flex flex-wrap gap-1.5 empty:hidden"></ul>
</div>

@once
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('[data-expert-tags]').forEach(field => {
                const input = field.querySelector('input');
                const preview = field.querySelector('[data-tag-preview]');

                function renderTags() {
                    const tags = [...new Set(input.value.split(/[,\uFF0C\u060C]/u).map(tag => tag.trim()).filter(Boolean))];
                    preview.replaceChildren(...tags.map(tag => {
                        const item = document.createElement('li');
                        item.className = 'max-w-full break-words rounded-lg bg-teal-50 px-2.5 py-1 text-[12.5px] font-medium text-teal-900';
                        item.textContent = tag;
                        return item;
                    }));
                }

                input.addEventListener('input', renderTags);
                renderTags();
            });
        });
    </script>
@endonce
