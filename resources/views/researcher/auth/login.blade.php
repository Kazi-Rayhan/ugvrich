<x-researcher-auth :title="__('researcher.auth.login_title')" :lead="__('researcher.auth.login_lead')">
    <form method="POST" action="{{ route('researcher.login.store') }}" class="mt-8 space-y-5">
        @csrf

        <div>
            <label for="email" class="field-label">{{ __('researcher.auth.email') }}</label>
            <input id="email" name="email" type="email" required autofocus value="{{ old('email') }}" autocomplete="email" class="field-input mt-2">
        </div>

        <div>
            <label for="password" class="field-label">{{ __('researcher.auth.password') }}</label>
            <input id="password" name="password" type="password" required autocomplete="current-password" class="field-input mt-2">
        </div>

        <label class="flex cursor-pointer items-center gap-2.5 text-[13.5px] text-ink-600">
            <input type="checkbox" name="remember" value="1" class="h-4 w-4 rounded border-ink-300 text-brand-600 focus:ring-brand-500">
            {{ __('researcher.auth.remember') }}
        </label>

        <button type="submit" class="btn-lead group w-full">
            <span class="relative">{{ __('researcher.auth.login') }}</span>
            <x-ui-icon name="arrow-right" class="relative h-[18px] w-[18px] transition-transform duration-300 group-hover:translate-x-1" />
        </button>

        <p class="pt-1 text-center text-[13.5px] text-ink-500">
            {{ __('researcher.auth.no_account') }}
            <a href="{{ route('researcher.register') }}" class="font-semibold text-brand-700 hover:text-brand-600">{{ __('researcher.auth.register') }}</a>
        </p>
    </form>
</x-researcher-auth>
