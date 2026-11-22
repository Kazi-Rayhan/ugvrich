@php
    $fields = $method === 'bank' ? ['bank_name', 'branch_name', 'district', 'account_name', 'account_number', 'routing_number'] : ['bkash_number'];
@endphp
<form method="POST" action="{{ route('expert.wallet.payout') }}" class="space-y-4 p-5">
    @csrf
    @method('PUT')
    <input type="hidden" name="method" value="{{ $method }}">
    @if (isset($id))
        <input type="hidden" name="destination_id" value="{{ $id }}">
    @endif
    <div @class(['grid min-w-0 gap-4', 'sm:grid-cols-2' => $method === 'bank'])>
        @foreach ($fields as $field)
            <label class="field-label block min-w-0">
                {{ __('expert_wallet.'.$field) }}
                <input name="{{ $field }}" class="field-input mt-2" value="{{ $details[$field] ?? '' }}"
                    inputmode="{{ in_array($field, ['account_number', 'routing_number', 'bkash_number'], true) ? 'tel' : 'text' }}"
                    maxlength="{{ in_array($field, ['account_number', 'routing_number', 'bkash_number'], true) ? 30 : 120 }}" autocomplete="off" required>
                @if ($field === 'routing_number')
                    <span class="mt-1 block text-xs font-normal text-ink-600">{{ __('expert_wallet.routing_hint') }}</span>
                @endif
            </label>
        @endforeach
    </div>
    <button type="submit" class="btn-lead w-full sm:w-auto">{{ __('expert_wallet.'.(isset($id) ? 'save_method' : 'add_method')) }}</button>
</form>
