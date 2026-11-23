<x-researcher-auth :title="__('researcher.auth.login_title')" :lead="__('researcher.auth.login_lead')">
    <x-slot:header>
        <header>
            <div class="mb-5 flex justify-end">
                <x-language-switch class="min-h-10 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-600" />
            </div>
            <h1 class="font-display text-[clamp(2.25rem,4vw,3.25rem)] leading-tight font-extrabold tracking-tight text-ink-950">{{ __('researcher.auth.login_title') }}</h1>
            <p class="mt-2 text-sm leading-relaxed text-ink-600">{{ __('researcher.auth.login_lead') }}</p>
        </header>
    </x-slot:header>

    @if (session('status'))
        <div class="mt-7 flex items-start gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-[13.5px] leading-relaxed text-emerald-800" role="status">
            <x-ui-icon name="check" class="mt-0.5 h-4 w-4 shrink-0" />
            <span>{{ session('status') }}</span>
        </div>
    @endif

    <form method="POST" action="{{ route('login.store') }}" class="mt-6 space-y-4">
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

        <button type="submit" class="group flex min-h-13 w-full cursor-pointer items-center justify-between gap-4 rounded-xl bg-brand-600 px-5 py-3 text-sm font-bold text-white transition-colors hover:bg-brand-700 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-brand-600">
            <span>{{ __('researcher.auth.login') }}</span>
            <x-ui-icon name="arrow-right" class="size-[18px] motion-safe:transition-transform motion-safe:group-hover:translate-x-1" />
        </button>
    </form>

    <x-slot:options>
        <aside aria-labelledby="login-options" class="login-options min-w-0 px-5 py-6 text-white sm:px-8 md:col-start-1 md:row-start-1 md:py-7 lg:px-10">
            <img src="{{ asset('media/auth/portal-atlas.svg') }}" alt="" width="448" height="190" class="mx-auto mb-5 hidden h-auto w-full max-w-[28rem] md:block">
            <h2 id="login-options" class="mb-2 font-display text-2xl font-bold tracking-tight !text-auth-heading">{{ __('researcher.auth.options_heading') }}</h2>

        @php
            $options = [
                ['icon' => 'academic', 'href' => route('researcher.register'), 'title' => __('researcher.auth.option_researcher_title'), 'body' => __('researcher.auth.option_researcher_body')],
                ['icon' => 'briefcase', 'href' => route('consultancy.registration.register'), 'title' => __('researcher.auth.option_expert_title'), 'body' => __('researcher.auth.option_expert_body')],
                ['icon' => 'lightbulb', 'href' => null, 'title' => __('researcher.auth.option_innovation_title'), 'body' => __('researcher.auth.option_innovation_body')],
            ];
        @endphp

        <div class="login-options-list divide-y divide-white/15">
            @foreach ($options as $option)
                @if ($option['href'])
                    <a href="{{ $option['href'] }}" class="group flex min-h-16 items-center gap-3 py-3 transition-colors hover:bg-white/10 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-auth-accent">
                @else
                    <div class="flex min-h-16 items-center gap-3 py-3" aria-disabled="true">
                @endif
                    <span @class(['grid size-10 shrink-0 place-items-center rounded-xl', 'bg-brand-100 text-brand-800' => $option['href'], 'bg-white/10 text-white/80' => ! $option['href']])>
                        <x-ui-icon :name="$option['icon']" class="h-5 w-5" />
                    </span>
                    <span class="min-w-0 flex-1">
                        <span @class(['block text-[14.5px] font-semibold', 'text-white' => $option['href'], 'text-white/80' => ! $option['href']])>{{ $option['title'] }}</span>
                        <span class="mt-0.5 block text-[13px] leading-relaxed text-auth-muted">{{ $option['body'] }}</span>
                    </span>
                @if ($option['href'])
                    <x-ui-icon name="arrow-up-right" class="size-4 shrink-0 text-auth-accent motion-safe:transition-transform motion-safe:group-hover:-translate-y-0.5 motion-safe:group-hover:translate-x-0.5" />
                    </a>
                @else
                    </div>
                @endif
            @endforeach
        </div>
        </aside>
    </x-slot:options>
</x-researcher-auth>
