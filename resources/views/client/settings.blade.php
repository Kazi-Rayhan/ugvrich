<x-client-portal :client="$client" :title="$security ? 'Security' : 'Profile'" :section="$security ? 'security' : 'profile'">
    <section class="panel settings-panel" aria-labelledby="settings-heading">
        <div class="panel-heading">
            <h2 id="settings-heading">{{ $security ? 'Change password' : 'Contact and billing details' }}</h2>
        </div>
        <form method="post" action="{{ route($security ? 'client.security.update' : 'client.profile.update') }}" class="settings-form">
            @csrf
            @method('PUT')
            @if ($security)
                <div class="field">
                    <label for="current_password">Current password</label>
                    <input id="current_password" name="current_password" type="password" autocomplete="current-password" required>
                    @error('current_password')<p class="field-error" role="alert">{{ $message }}</p>@enderror
                </div>
                <div class="field">
                    <label for="password">New password</label>
                    <input id="password" name="password" type="password" autocomplete="new-password" minlength="8" required aria-describedby="password-hint">
                    <small id="password-hint">Use at least 8 characters.</small>
                    @error('password')<p class="field-error" role="alert">{{ $message }}</p>@enderror
                </div>
                <div class="field">
                    <label for="password_confirmation">Confirm new password</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" minlength="8" required>
                </div>
            @else
                <div class="form-grid">
                    <div class="field">
                        <label for="name">Contact name</label>
                        <input id="name" name="name" value="{{ old('name', $client->name) }}" autocomplete="name" maxlength="150" required>
                        @error('name')<p class="field-error" role="alert">{{ $message }}</p>@enderror
                    </div>
                    <div class="field">
                        <label for="organization">Organization</label>
                        <input id="organization" name="organization" value="{{ old('organization', $client->organization) }}" autocomplete="organization" maxlength="200">
                        @error('organization')<p class="field-error" role="alert">{{ $message }}</p>@enderror
                    </div>
                    <div class="field">
                        <label for="email">Sign-in email</label>
                        <input id="email" type="email" value="{{ auth()->user()->email }}" readonly>
                    </div>
                    <div class="field">
                        <label for="phone">Phone</label>
                        <input id="phone" name="phone" type="tel" value="{{ old('phone', $client->phone) }}" autocomplete="tel" maxlength="30">
                        @error('phone')<p class="field-error" role="alert">{{ $message }}</p>@enderror
                    </div>
                    <div class="field full-width">
                        <label for="address">Billing address</label>
                        <textarea id="address" name="address" rows="3" maxlength="500" autocomplete="street-address">{{ old('address', $client->address) }}</textarea>
                        @error('address')<p class="field-error" role="alert">{{ $message }}</p>@enderror
                    </div>
                </div>
            @endif
            <div class="form-actions">
                <button class="button" type="submit">{{ $security ? 'Update password' : 'Save profile' }}</button>
            </div>
        </form>
    </section>
</x-client-portal>
