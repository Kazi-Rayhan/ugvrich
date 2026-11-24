<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Validation\ValidationException;

class ClientPortalController extends Controller
{
    public function dashboard(Request $request)
    {
        $client = $this->client($request);

        return view('client.orders', [
            'client' => $client,
            'orders' => $client->orders()->with(['payments', 'proposal'])->latest('order_date')->latest('id')->limit(5)->get(),
            'featuredOrder' => $client->orders()->with(['payments', 'proposal'])
                ->whereIn('status', ['pending', 'confirmed', 'in_progress', 'on_hold'])
                ->latest('order_date')->latest('id')->first(),
            'orderCount' => $client->orders()->count(),
            'activeCount' => $client->orders()->whereIn('status', ['pending', 'confirmed', 'in_progress', 'on_hold'])->count(),
            'completedCount' => $client->orders()->where('status', 'completed')->count(),
            'overview' => true,
        ]);
    }

    public function orders(Request $request)
    {
        $client = $this->client($request);

        return view('client.orders', [
            'client' => $client,
            'orders' => $client->orders()->with(['payments', 'proposal'])->latest('order_date')->latest('id')->paginate(12),
            'overview' => false,
        ]);
    }

    public function showOrder(Request $request, int $order)
    {
        $client = $this->client($request);

        return view('client.order', [
            'client' => $client,
            'order' => $client->orders()->with(['items', 'payments.financeAccount', 'workOrder', 'proposal'])->findOrFail($order),
        ]);
    }

    public function invoice(Request $request, int $order)
    {
        $order = $this->client($request)->orders()->with([
            'client', 'items', 'workOrder',
            'payments' => fn ($query) => $query->where('status', Payment::CONFIRMED)->with('financeAccount'),
        ])->findOrFail($order);

        return view('sales.invoice', ['order' => $order, 'backUrl' => route('client.orders.show', $order)]);
    }

    public function profile(Request $request)
    {
        return view('client.settings', ['client' => $this->client($request), 'security' => false]);
    }

    public function updateProfile(Request $request)
    {
        $client = $this->client($request);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'organization' => ['nullable', 'string', 'max:200'],
            'phone' => ['nullable', 'string', 'max:30', 'regex:/^[+\d][\d\s\-()]{6,28}$/'],
            'address' => ['nullable', 'string', 'max:500'],
        ]);
        $phone = Client::normalizePhone($data['phone'] ?? null);
        if ($phone !== null && Client::where('phone_normalized', $phone)->whereKeyNot($client->id)->exists()) {
            throw ValidationException::withMessages(['phone' => 'This phone number is already in use.']);
        }

        try {
            DB::transaction(function () use ($request, $client, $data): void {
                $client->update($data);
                $request->user()->update(['name' => $client->name]);
            });
        } catch (UniqueConstraintViolationException) {
            throw ValidationException::withMessages(['phone' => 'This phone number is already in use.']);
        }

        return back()->with('status', 'Profile saved.');
    }

    public function security(Request $request)
    {
        return view('client.settings', ['client' => $this->client($request), 'security' => true]);
    }

    public function updatePassword(Request $request)
    {
        $this->client($request);
        $data = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', 'different:current_password', PasswordRule::min(8)],
        ]);

        $user = $request->user();
        $user->forceFill(['password' => $data['password'], 'remember_token' => Str::random(60)])->save();
        Password::deleteToken($user);
        $request->session()->regenerate();

        return back()->with('status', 'Password updated.');
    }

    public function logout(Request $request)
    {
        $this->client($request);
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    private function client(Request $request): Client
    {
        abort_unless($request->user()->role === User::CLIENT, 403);

        return $request->user()->client()->firstOrFail();
    }
}
