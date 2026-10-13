<x-researcher-auth :title="__('researcher.auth.reset_title')" :lead="__('researcher.auth.reset_lead')">
    <form method="POST" action="{{ route('password.store') }}" class="mt-8 space-y-5">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">

        <div>
            <label for="email" class="field-label">{{ __('researcher.auth.email') }}</label>
            <input id="email" name="email" type="email" required value="{{ old('email', $email) }}" autocomplete="email" class="field-input mt-2">
        </div>

        <div>
            <label for="password" class="field-label">{{ __('researcher.auth.new_password') }}</label>
            <input id="password" name="password" type="password" required minlength="8" autofocus autocomplete="new-password" class="field-input mt-2">
        </div>

        <div>
            <label for="password_confirmation" class="field-label">{{ __('researcher.auth.password_confirm') }}</label>
            <input id="password_confirmation" name="password_confirmation" type="password" required minlength="8" autocomplete="new-password" class="field-input mt-2">
        </div>

        <button type="submit" class="btn-lead group w-full">
            <span class="relative">{{ __('researcher.auth.reset_submit') }}</span>
            <x-ui-icon name="arrow-right" class="relative h-[18px] w-[18px] transition-transform duration-300 group-hover:translate-x-1" />
        </button>

        <a href="{{ route('login') }}" class="group flex items-center justify-center gap-2 pt-1 text-[13.5px] font-semibold text-brand-700 hover:text-brand-600">
            <x-ui-icon name="arrow-left" class="h-4 w-4 transition-transform duration-300 group-hover:-translate-x-1" />
            {{ __('researcher.auth.back_to_login') }}
        </a>
    </form>
</x-researcher-auth>
