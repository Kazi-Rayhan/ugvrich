<x-researcher-auth :title="__('researcher.auth.register_title')" :lead="__('researcher.auth.register_lead')">
    <form method="POST" action="{{ route('researcher.register.store') }}" class="mt-8 space-y-5">
        @csrf

        <div class="hidden" aria-hidden="true">
            <label>Website <input type="text" name="website" tabindex="-1" autocomplete="off"></label>
        </div>

        <div>
            <label for="name" class="field-label">{{ __('researcher.auth.name') }}</label>
            <input id="name" name="name" type="text" required autofocus value="{{ old('name') }}" autocomplete="name" class="field-input mt-2">
        </div>

        <div>
            <label for="email" class="field-label">{{ __('researcher.auth.email') }}</label>
            <input id="email" name="email" type="email" required value="{{ old('email') }}" autocomplete="email" class="field-input mt-2">
        </div>

        <div>
            <label for="password" class="field-label">{{ __('researcher.auth.password') }}</label>
            <input id="password" name="password" type="password" required autocomplete="new-password" class="field-input mt-2">
        </div>

        <div>
            <label for="password_confirmation" class="field-label">{{ __('researcher.auth.password_confirm') }}</label>
            <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password" class="field-input mt-2">
        </div>

        <button type="submit" class="btn-lead group w-full">
            <span class="relative">{{ __('researcher.auth.register') }}</span>
            <x-ui-icon name="arrow-right" class="relative h-[18px] w-[18px] transition-transform duration-300 group-hover:translate-x-1" />
        </button>

        <p class="pt-1 text-center text-[13.5px] text-ink-500">
            {{ __('researcher.auth.have_account') }}
            <a href="{{ route('researcher.login') }}" class="font-semibold text-brand-700 hover:text-brand-600">{{ __('researcher.auth.login') }}</a>
        </p>
    </form>
</x-researcher-auth>
