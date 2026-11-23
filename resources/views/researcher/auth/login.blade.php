<x-researcher-auth :title="__('researcher.auth.login_title')" :lead="__('researcher.auth.login_lead')">
    <x-slot:header>
        <header>
            <div class="mb-4 flex items-center justify-between gap-3">
                <span class="grid size-11 place-items-center rounded-xl bg-brand-50 text-brand-700 ring-1 ring-brand-100" aria-hidden="true">
                    <x-ui-icon name="user" class="size-5" />
                </span>
                <x-language-switch class="min-h-10 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-600" />
            </div>
            <h1 class="font-display text-3xl font-extrabold tracking-tight text-ink-950 sm:text-4xl">{{ __('researcher.auth.login_title') }}</h1>
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

        <button type="submit" class="btn-lead group w-full focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-brand-600">
            <span class="relative">{{ __('researcher.auth.login') }}</span>
            <x-ui-icon name="arrow-right" class="relative h-[18px] w-[18px] motion-safe:transition-transform motion-safe:group-hover:translate-x-1" />
        </button>
    </form>

    <x-slot:options>
        <aside aria-labelledby="login-options" class="login-options relative isolate overflow-hidden px-5 py-7 text-white sm:px-7 sm:py-8 md:col-start-1 md:row-start-1 lg:px-8">
            <div aria-hidden="true" class="pointer-events-none absolute inset-0 -z-10 grid-overlay opacity-10"></div>
            <h2 id="login-options" class="mb-5 font-display text-2xl font-bold tracking-tight !text-white">{{ __('researcher.auth.options_heading') }}</h2>

        @php
            $options = [
                ['icon' => 'academic', 'href' => route('researcher.register'), 'title' => __('researcher.auth.option_researcher_title'), 'body' => __('researcher.auth.option_researcher_body')],
                ['icon' => 'briefcase', 'href' => route('consultancy.registration.register'), 'title' => __('researcher.auth.option_expert_title'), 'body' => __('researcher.auth.option_expert_body')],
                ['icon' => 'lightbulb', 'href' => null, 'title' => __('researcher.auth.option_innovation_title'), 'body' => __('researcher.auth.option_innovation_body')],
                ['icon' => 'shield', 'href' => url('/admin/login'), 'title' => __('researcher.auth.option_staff_title'), 'body' => __('researcher.auth.option_staff_body')],
            ];
        @endphp

        <div class="login-options-list space-y-2.5">
            @foreach ($options as $option)
                @if ($option['href'])
                    <a href="{{ $option['href'] }}" class="group flex items-center gap-3 rounded-2xl border border-white/20 bg-white/10 p-3.5 transition-colors hover:border-cyan-100/60 hover:bg-white/20 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white">
                @else
                    <div class="flex items-center gap-3 rounded-2xl border border-dashed border-white/25 p-3.5" aria-disabled="true">
                @endif
                    <span @class(['grid size-10 shrink-0 place-items-center rounded-xl', 'bg-cyan-100 text-indigo-950' => $option['href'], 'bg-white/10 text-white/70' => ! $option['href']])>
                        <x-ui-icon :name="$option['icon']" class="h-5 w-5" />
                    </span>
                    <span class="min-w-0 flex-1">
                        <span @class(['block text-[14.5px] font-semibold', 'text-white' => $option['href'], 'text-white/80' => ! $option['href']])>{{ $option['title'] }}</span>
                        <span class="mt-0.5 block text-[13px] leading-relaxed text-white/75">{{ $option['body'] }}</span>
                    </span>
                @if ($option['href'])
                    <x-ui-icon name="arrow-up-right" class="size-4 shrink-0 text-cyan-100 motion-safe:transition-transform motion-safe:group-hover:-translate-y-0.5 motion-safe:group-hover:translate-x-0.5" />
                    </a>
                @else
                    </div>
                @endif
            @endforeach
        </div>
        </aside>
    </x-slot:options>
</x-researcher-auth>
