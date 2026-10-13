<x-researcher-auth :title="__('researcher.auth.forgot_title')" :lead="__('researcher.auth.forgot_lead')">
    @if (session('status'))
        <div class="mt-7 flex items-start gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-[13.5px] leading-relaxed text-emerald-800" role="status">
            <x-ui-icon name="mail" class="mt-0.5 h-4 w-4 shrink-0" />
            <span>{{ session('status') }}</span>
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}" class="mt-8 space-y-5">
        @csrf

        <div>
            <label for="email" class="field-label">{{ __('researcher.auth.email') }}</label>
            <input id="email" name="email" type="email" required autofocus value="{{ old('email') }}" autocomplete="email" class="field-input mt-2">
        </div>

        <button type="submit" class="btn-lead group w-full">
            <span class="relative">{{ __('researcher.auth.forgot_submit') }}</span>
            <x-ui-icon name="arrow-right" class="relative h-[18px] w-[18px] transition-transform duration-300 group-hover:translate-x-1" />
        </button>

        <a href="{{ route('login') }}" class="group flex items-center justify-center gap-2 pt-1 text-[13.5px] font-semibold text-brand-700 hover:text-brand-600">
            <x-ui-icon name="arrow-left" class="h-4 w-4 transition-transform duration-300 group-hover:-translate-x-1" />
            {{ __('researcher.auth.back_to_login') }}
        </a>
    </form>
</x-researcher-auth>
