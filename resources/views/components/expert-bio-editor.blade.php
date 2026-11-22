@props(['name', 'label', 'html' => null])

@php
    $value = old($name);
    $content = is_string($value) ? \App\Support\ExpertBiography::sanitize($value) : $html;
@endphp

<div data-bio-editor class="min-w-0">
    <label id="{{ $name }}-label" for="{{ $name }}" class="field-label">{{ $label }}</label>
    <div hidden data-rich-surface class="mt-2 overflow-hidden rounded-xl border border-ink-200 bg-white focus-within:border-brand-400 focus-within:ring-2 focus-within:ring-brand-100">
        <div class="flex flex-wrap gap-1 border-b border-ink-100 bg-ink-50 p-2" role="group" aria-label="{{ __('consultancy_engagement.formatting') }}">
            @foreach (['bold' => 'bold', 'italic' => 'italic', 'underline' => 'underline', 'insertUnorderedList' => 'bullet_list', 'insertOrderedList' => 'numbered_list'] as $command => $translation)
                <button type="button" data-format="{{ $command }}" class="rounded-md px-2.5 py-1.5 text-[12.5px] font-semibold text-ink-700 hover:bg-white hover:text-brand-700 focus-visible:outline-2 focus-visible:outline-brand-600">
                    {{ __('consultancy_engagement.'.$translation) }}
                </button>
            @endforeach
        </div>
        <div data-editor contenteditable="true" role="textbox" aria-multiline="true" aria-labelledby="{{ $name }}-label"
             class="expert-biography min-h-36 break-words px-4 py-3 text-[13.5px] leading-relaxed text-ink-800 outline-none">{!! $content !!}</div>
    </div>
    <textarea id="{{ $name }}" name="{{ $name }}" rows="6" maxlength="16000" class="field-input mt-2">{{ \App\Support\ExpertBiography::plainText($content) }}</textarea>
</div>

@once
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('[data-bio-editor]').forEach(container => {
                const surface = container.querySelector('[data-rich-surface]');
                const editor = container.querySelector('[data-editor]');
                const textarea = container.querySelector('textarea');
                let savedSelection;

                const sync = () => { textarea.value = editor.innerHTML; };
                surface.hidden = false;
                textarea.hidden = true;
                sync();
                editor.addEventListener('input', sync);
                editor.addEventListener('paste', event => {
                    event.preventDefault();
                    document.execCommand('insertText', false, event.clipboardData.getData('text/plain'));
                });
                editor.addEventListener('drop', event => event.preventDefault());
                document.addEventListener('selectionchange', () => {
                    const selection = window.getSelection();
                    if (selection.rangeCount && editor.contains(selection.anchorNode) && editor.contains(selection.focusNode)) {
                        savedSelection = selection.getRangeAt(0).cloneRange();
                    }
                });

                container.querySelectorAll('[data-format]').forEach(button => {
                    button.addEventListener('mousedown', event => event.preventDefault());
                    button.addEventListener('click', () => {
                        editor.focus();
                        if (savedSelection && editor.contains(savedSelection.commonAncestorContainer)) {
                            const selection = window.getSelection();
                            selection.removeAllRanges();
                            selection.addRange(savedSelection);
                        }
                        document.execCommand(button.dataset.format);
                        sync();
                    });
                });

                container.closest('form').addEventListener('submit', sync);
            });
        });
    </script>
@endonce
