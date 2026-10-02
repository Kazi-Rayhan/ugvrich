@php
    /* Imports live above the component tag: the slot compiles to a closure,
       and PHP will not accept a `use` import inside one. */
    use App\Http\Middleware\SetLocale;
@endphp

<x-layouts.researcher :title="__('researcher.settings.title')">

    <div class="space-y-6">

        <x-portal-heading :title="__('researcher.settings.title')" :lead="__('researcher.settings.lead')" class="mb-0">
            <x-slot:actions>
                <a href="{{ route('researcher.profile') }}" class="inline-flex items-center gap-2 rounded-xl border border-ink-200 px-4 py-2.5 text-[13.5px] font-semibold text-ink-700 transition hover:border-brand-300 hover:text-brand-700">
                    <x-ui-icon name="users" class="h-4 w-4" />
                    {{ __('researcher.settings.profile_link') }}
                </a>
            </x-slot:actions>
        </x-portal-heading>

        {{-- ------------------------------------------------------- account --}}
        <form method="POST" action="{{ route('researcher.settings.update') }}" class="field-group">
            @csrf
            @method('PUT')

            <p class="field-legend">{{ __('researcher.settings.account') }}</p>
            <p class="field-help">{{ __('researcher.settings.account_lead') }}</p>

            <div class="mt-6 grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="name" class="field-label">{{ __('researcher.settings.name') }} <span class="text-brand-600">*</span></label>
                    <input id="name" name="name" type="text" required value="{{ old('name', $user->name) }}" class="field-input mt-2">
                    @if ($errors->getBag('default')->has('name')) <p class="field-error">{{ $errors->getBag('default')->first('name') }}</p> @endif
                </div>

                <div>
                    <label for="email" class="field-label">{{ __('researcher.settings.email') }} <span class="text-brand-600">*</span></label>
                    <input id="email" name="email" type="email" required value="{{ old('email', $user->email) }}" class="field-input mt-2">
                    @if ($errors->getBag('default')->has('email'))
                        <p class="field-error">{{ $errors->getBag('default')->first('email') }}</p>
                    @else
                        <p class="field-help">{{ __('researcher.settings.email_help') }}</p>
                    @endif
                </div>
            </div>

            {{-- Notifications. The database record is written either way, so this
                 only decides whether an email is sent as well. --}}
            <div class="mt-7 border-t border-ink-100 pt-6">
                <p class="font-display text-[14.5px] font-bold text-ink-950">{{ __('researcher.settings.notifications') }}</p>
                <p class="mt-1.5 text-[13px] leading-relaxed text-ink-500">{{ __('researcher.settings.notifications_lead') }}</p>

                <label class="mt-4 flex cursor-pointer items-start gap-3 rounded-2xl border border-ink-200 bg-white px-4 py-3.5 transition duration-200
                              hover:border-ink-300 hover:bg-ink-50/60
                              has-[:checked]:border-brand-500 has-[:checked]:bg-brand-50">
                    <input type="checkbox" name="email_notifications" value="1"
                           @checked(old('email_notifications', $user->email_notifications))
                           class="peer sr-only">

                    <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-md border border-ink-300 bg-white transition duration-200
                                 peer-checked:border-brand-600 peer-checked:bg-brand-600">
                        <x-ui-icon name="check" class="h-3 w-3 text-white opacity-0 transition-opacity duration-200 peer-checked:opacity-100" stroke="3" />
                    </span>

                    <span>
                        <span class="block text-[13.5px] font-semibold text-ink-800">{{ __('researcher.settings.email_notifications') }}</span>
                        <span class="mt-0.5 block text-[12.5px] leading-relaxed text-ink-500">{{ __('researcher.settings.email_notifications_help') }}</span>
                    </span>
                </label>
            </div>

            {{-- Language. The portal keeps its own choice, so this is the same
                 switch as the one in the top bar, written down. --}}
            <div class="mt-7 border-t border-ink-100 pt-6">
                <label for="locale" class="field-label">{{ __('researcher.settings.language') }}</label>
                <select id="locale" name="locale" class="field-input mt-2 sm:max-w-xs">
                    @foreach (SetLocale::LOCALES as $code => $label)
                        <option value="{{ $code }}" @selected(app()->getLocale() === $code)>{{ $label }}</option>
                    @endforeach
                </select>
                <p class="field-help">{{ __('researcher.settings.language_help') }}</p>
            </div>

            <div class="mt-7">
                <button type="submit" class="btn-lead group">
                    <span class="relative">{{ __('researcher.settings.save_account') }}</span>
                    <x-ui-icon name="check" class="relative h-[18px] w-[18px]" stroke="2.4" />
                </button>
            </div>
        </form>

        {{-- ------------------------------------------------------ password --}}
        <form method="POST" action="{{ route('researcher.settings.password') }}" class="field-group">
            @csrf
            @method('PUT')

            <p class="field-legend">{{ __('researcher.settings.password') }}</p>
            <p class="field-help">{{ __('researcher.settings.password_lead') }}</p>

            {{-- Its own error bag, so a failed password change does not light up
                 the account form above it. --}}
            @php $bag = $errors->getBag('password'); @endphp

            <div class="mt-6 space-y-5">
                <div class="sm:max-w-sm">
                    <label for="current_password" class="field-label">{{ __('researcher.settings.current_password') }} <span class="text-brand-600">*</span></label>
                    <input id="current_password" name="current_password" type="password" required autocomplete="current-password" class="field-input mt-2">
                    @if ($bag->has('current_password')) <p class="field-error">{{ $bag->first('current_password') }}</p> @endif
                </div>

                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label for="password" class="field-label">{{ __('researcher.settings.new_password') }} <span class="text-brand-600">*</span></label>
                        <input id="password" name="password" type="password" required autocomplete="new-password" class="field-input mt-2">
                        @if ($bag->has('password')) <p class="field-error">{{ $bag->first('password') }}</p> @endif
                    </div>

                    <div>
                        <label for="password_confirmation" class="field-label">{{ __('researcher.settings.confirm_password') }} <span class="text-brand-600">*</span></label>
                        <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password" class="field-input mt-2">
                    </div>
                </div>
            </div>

            <p class="mt-5 text-[12.5px] leading-relaxed text-ink-500">{{ __('researcher.settings.sessions_note') }}</p>

            <div class="mt-6">
                <button type="submit" class="btn-lead group">
                    <span class="relative">{{ __('researcher.settings.save_password') }}</span>
                    <x-ui-icon name="key" class="relative h-[18px] w-[18px]" />
                </button>
            </div>
        </form>
    </div>
</x-layouts.researcher>
