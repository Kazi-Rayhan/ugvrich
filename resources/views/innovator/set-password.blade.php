<x-layouts.app :title="__('innovator.password.title')">

    {{-- Where the emailed link lands: choose a password, then straight into
         the dashboard. --}}
    <section class="relative isolate overflow-hidden bg-ink-50 py-16 sm:py-24">
        <div class="pointer-events-none absolute inset-0 -z-10 text-brand-700 grid-overlay opacity-30 [mask-image:radial-gradient(ellipse_at_top,black,transparent_70%)]" aria-hidden="true"></div>

        <div class="container-rich">
            <div class="mx-auto max-w-lg overflow-hidden rounded-[2rem] border border-ink-100 bg-white shadow-[0_30px_70px_-50px_rgba(7,20,38,0.45)]">
                <div class="relative isolate overflow-hidden bg-navy-800 px-8 py-8 text-white">
                    <div class="pointer-events-none absolute -right-10 -top-10 -z-10 h-40 w-40 rounded-full bg-brand-500/30 blur-3xl" aria-hidden="true"></div>
                    <p class="eyebrow-invert">{{ __('innovator.eyebrow') }}</p>
                    <h1 class="mt-5 font-display text-[28px] font-bold leading-tight !text-white">{{ __('innovator.password.title') }}</h1>
                    <p class="mt-3 text-[14.5px] leading-relaxed text-white/70">{{ __('innovator.password.lead') }}</p>
                </div>

                <form method="POST" action="{{ route('innovator.password.update') }}" class="space-y-5 p-8">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">

                    @if ($errors->any())
                        <div class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-[13.5px] text-red-700" role="alert">
                            @foreach ($errors->all() as $error)
                                <p>{{ $error }}</p>
                            @endforeach
                        </div>
                    @endif

                    <div>
                        <label for="email" class="field-label">{{ __('innovator.password.email') }}</label>
                        <input id="email" name="email" type="email" required value="{{ old('email', $email) }}" autocomplete="email" class="field-input mt-2">
                    </div>

                    <div>
                        <label for="password" class="field-label">{{ __('innovator.password.new') }}</label>
                        <input id="password" name="password" type="password" required minlength="8" autocomplete="new-password" autofocus class="field-input mt-2">
                    </div>

                    <div>
                        <label for="password_confirmation" class="field-label">{{ __('innovator.password.confirm') }}</label>
                        <input id="password_confirmation" name="password_confirmation" type="password" required minlength="8" autocomplete="new-password" class="field-input mt-2">
                    </div>

                    <button type="submit" class="btn-lead group w-full">
                        <span class="relative">{{ __('innovator.password.submit') }}</span>
                        <x-ui-icon name="arrow-right" class="relative h-[18px] w-[18px] transition-transform duration-300 group-hover:translate-x-1" />
                    </button>
                </form>
            </div>
        </div>
    </section>
</x-layouts.app>
