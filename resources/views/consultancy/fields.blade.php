<fieldset class="rounded-2xl border border-ink-200 bg-ink-50/50 p-4 sm:p-5">
    <legend class="sr-only">{{ $heading }} entry</legend>
    <div @class(['grid gap-4', 'sm:grid-cols-2' => $group === 'occupations', 'sm:grid-cols-3' => count($fields) === 3])>
        @foreach ($fields as $key => $label)
            <label class="field-label block">
                {{ $label }}
                <input
                    class="field-input mt-2"
                    name="{{ $group }}[{{ $index }}][{{ $key }}]"
                    value="{{ is_scalar(data_get($row, $key)) ? data_get($row, $key) : '' }}"
                    @if ($key === 'year') type="number" min="1900" max="{{ now()->year }}"
                    @elseif (in_array($key, ['start_date', 'end_date'], true)) type="date"
                    @else type="text" maxlength="150" @endif
                    @required($key !== 'end_date')
                >
            </label>
        @endforeach
    </div>
    <button type="button" hidden data-remove class="mt-3 text-sm font-semibold text-rose-700">Remove entry</button>
</fieldset>
