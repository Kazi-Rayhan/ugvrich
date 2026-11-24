<?php

namespace App\Support\Sales;

use App\Models\Client;
use App\Models\User;
use App\Notifications\ClientPortalWelcome;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ClientPortalAccess
{
    public static function inviteNewClient(Client $client): void
    {
        if (! $client->wasRecentlyCreated || blank($client->email)) {
            return;
        }

        $user = self::createUser($client, Str::password(48), 'data.work_order.new_client.email');
        $user->notify(new ClientPortalWelcome(Password::createToken($user)));
    }

    public static function setPassword(Client $client, string $password, User $staff): void
    {
        abort_unless($staff->isAdmin(), 403);

        if ($client->user_id === null) {
            if (blank($client->email)) {
                throw ValidationException::withMessages(['data.email' => 'Enter the client’s email to enable portal access.']);
            }

            self::createUser($client, $password, 'data.email');

            return;
        }

        $user = $client->portalUser()->lockForUpdate()->firstOrFail();
        abort_unless($user->role === User::CLIENT, 403);

        $user->forceFill(['password' => $password, 'remember_token' => Str::random(60)])->save();
        Password::deleteToken($user);
    }

    private static function createUser(Client $client, string $password, string $emailField): User
    {
        $user = User::firstOrCreate(['email' => $client->email], [
            'name' => $client->name,
            'password' => $password,
            'role' => User::CLIENT,
        ]);

        if (! $user->wasRecentlyCreated) {
            throw ValidationException::withMessages([
                $emailField => 'This email already belongs to an account. Use a different email for the client portal.',
            ]);
        }

        $client->forceFill(['user_id' => $user->id])->save();

        return $user;
    }
}
