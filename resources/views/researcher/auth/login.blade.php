<x-researcher-auth :title="__('researcher.auth.login_title')" :lead="__('researcher.auth.login_lead')">
    @if (session('status'))
        <div class="mt-7 flex items-start gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-[13.5px] leading-relaxed text-emerald-800" role="status">
            <x-ui-icon name="check" class="mt-0.5 h-4 w-4 shrink-0" />
            <span>{{ session('status') }}</span>
        </div>
    @endif

    <form method="POST" action="{{ route('login.store') }}" class="mt-8 space-y-5">
        @csrf

        <div>
            <label for="email" class="field-label">{{ __('researcher.auth.email') }}</label>
            <input id="email" name="email" type="email" required autofocus value="{{ old('email') }}" autocomplete="email" class="field-input mt-2">
        </div>

        <div>
            <div class="flex items-baseline justify-between gap-3">
                <label for="password" class="field-label">{{ __('researcher.auth.password') }}</label>
                <a href="{{ route('password.request') }}" class="text-[13px] font-semibold text-brand-700 transition-colors hover:text-brand-600">{{ __('researcher.auth.forgot_link') }}</a>
            </div>
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
    </form>

    {{-- Who else comes through this door, each with the way in that fits them. --}}
    <div class="mt-9">
        <div class="flex items-center gap-3 text-[12px] font-semibold uppercase tracking-[0.14em] text-ink-400">
            <span class="h-px flex-1 bg-ink-100"></span>
            {{ __('researcher.auth.options_heading') }}
            <span class="h-px flex-1 bg-ink-100"></span>
        </div>

        @php
            $options = [
                ['icon' => 'academic', 'href' => route('researcher.register'), 'title' => __('researcher.auth.option_researcher_title'), 'body' => __('researcher.auth.option_researcher_body')],
                ['icon' => 'lightbulb', 'href' => route('ideas.create'), 'title' => __('researcher.auth.option_innovator_title'), 'body' => __('researcher.auth.option_innovator_body')],
                ['icon' => 'shield', 'href' => url('/admin/login'), 'title' => __('researcher.auth.option_staff_title'), 'body' => __('researcher.auth.option_staff_body')],
            ];
        @endphp

        <div class="mt-5 space-y-3">
            @foreach ($options as $option)
                <a href="{{ $option['href'] }}" class="group flex items-center gap-4 rounded-2xl border border-ink-100 bg-white p-4 transition-all duration-300 hover:-translate-y-0.5 hover:border-brand-200 hover:shadow-[0_18px_40px_-28px_rgba(7,20,38,0.45)]">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-700 transition-colors duration-300 group-hover:bg-brand-600 group-hover:text-white">
                        <x-ui-icon :name="$option['icon']" class="h-5 w-5" />
                    </span>
                    <span class="min-w-0 flex-1">
                        <span class="block text-[14.5px] font-semibold text-ink-950">{{ $option['title'] }}</span>
                        <span class="mt-0.5 block text-[13px] leading-relaxed text-ink-500">{{ $option['body'] }}</span>
                    </span>
                    <x-ui-icon name="arrow-right" class="h-4 w-4 shrink-0 text-ink-300 transition-all duration-300 group-hover:translate-x-1 group-hover:text-brand-600" />
                </a>
            @endforeach
        </div>
    </div>
</x-researcher-auth>
