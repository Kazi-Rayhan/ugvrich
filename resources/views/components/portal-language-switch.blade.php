@php
    use App\Http\Middleware\SetLocale;

    /* The public site switches language by changing the address, because every
       page exists twice. The portal exists once, so the choice is kept in the
       session and this just posts the other language back to the same page. */
    $current = app()->getLocale();
    $other = $current === 'bn' ? 'en' : 'bn';
@endphp

<form method="POST" action="{{ route('researcher.locale') }}" class="shrink-0">
    @csrf
    <input type="hidden" name="locale" value="{{ $other }}">

    <button type="submit"
            title="{{ __('site.language.switch_to', ['language' => __('site.language.'.$other)]) }}"
            {{ $attributes->merge(['class' => 'flex h-10 items-center gap-1.5 rounded-xl border border-ink-200 px-3 text-[12.5px] font-semibold text-ink-600 transition hover:border-brand-300 hover:text-brand-700']) }}>
        <x-ui-icon name="globe" class="h-4 w-4" />
        {{ __('site.language.'.$other) }}
    </button>
</form>
