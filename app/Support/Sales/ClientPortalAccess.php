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

        $user = User::firstOrCreate(['email' => $client->email], [
            'name' => $client->name,
            'password' => Str::password(48),
            'role' => User::CLIENT,
        ]);

        if (! $user->wasRecentlyCreated) {
            throw ValidationException::withMessages([
                'data.work_order.new_client.email' => 'This email already belongs to an account. Use a different email for the new client.',
            ]);
        }

        $client->forceFill(['user_id' => $user->id])->save();
        $user->notify(new ClientPortalWelcome(Password::createToken($user)));
    }
}
