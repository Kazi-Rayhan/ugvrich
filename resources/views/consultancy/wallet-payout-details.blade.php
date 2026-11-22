<div>
    <p class="mb-3 font-semibold">{{ __('expert_wallet.'.$entry->method) }}</p>
    <dl class="space-y-2">
        @foreach ($entry->destination ?? [] as $field => $value)
            @if ($value)
                <div><dt class="font-semibold">{{ __('expert_wallet.'.$field) }}</dt><dd>{{ $value }}</dd></div>
            @endif
        @endforeach
    </dl>
    @if ($entry->note)
        <p class="mt-4">{{ $entry->note }}</p>
    @endif
</div>
