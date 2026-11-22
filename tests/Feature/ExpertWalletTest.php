<?php

namespace Tests\Feature;

use App\Filament\Resources\ExpertWallets\Pages\ListExpertWallets;
use App\Filament\Resources\WalletWithdrawals\Pages\ListWalletWithdrawals;
use App\Models\ConsultancyApplication as Application;
use App\Models\ExpertWalletEntry as Entry;
use App\Models\ServiceCategory;
use App\Models\User;
use App\Services\ExpertWallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class ExpertWalletTest extends TestCase
{
    use RefreshDatabase;

    public function test_wallet_holds_requested_funds_and_records_manual_payouts(): void
    {
        $expert = User::factory()->create(['role' => User::EXPERT]);
        $admin = User::factory()->create();
        $category = ServiceCategory::create(['name' => 'Advisory', 'slug' => 'advisory']);
        $application = Application::create([
            'user_id' => $expert->id, 'name' => $expert->name, 'email' => $expert->email,
            'phone' => '1234567', 'location' => 'Barishal', 'occupation' => 'Consultant',
            'service_category_id' => $category->id, 'education' => [], 'skills' => [],
            'status' => Application::APPROVED, 'signed_at' => now(),
        ]);
        $wallet = app(ExpertWallet::class);

        $this->actingAs($admin)->get('/admin/expert-wallets')->assertOk();
        Livewire::test(ListExpertWallets::class)
            ->callTableAction('credit', $application, ['amount' => '100.25', 'reference' => 'WO-123'])
            ->assertHasNoErrors();
        Livewire::test(ListExpertWallets::class)
            ->callTableAction('credit', $application, ['amount' => '100.25', 'reference' => 'WO-123'])
            ->assertHasTableActionErrors(['reference']);

        $this->actingAs($expert)->get('/en/expert/dashboard/wallet')->assertOk()->assertSee('100.25');
        $this->get('/admin/wallet-withdrawals')->assertForbidden();
        $this->put(route('expert.wallet.payout'), ['method' => 'bkash', 'bkash_number' => '01700000001'])->assertRedirect();
        $this->put(route('expert.wallet.payout'), [
            'method' => 'bank', 'bank_name' => 'Example Bank', 'account_name' => 'Example Expert', 'account_number' => '0012345678',
        ])->assertRedirect();
        $this->assertStringNotContainsString('01700000001', $application->fresh()->getRawOriginal('wallet_destinations'));

        $request = ['method' => 'bkash', 'amount' => '40.50', 'request_key' => (string) Str::uuid()];
        $this->post(route('expert.wallet.withdraw'), $request)->assertRedirect();
        $this->post(route('expert.wallet.withdraw'), $request)->assertRedirect();
        $this->assertSame(['available' => 5975, 'held' => 4050, 'paid' => 0], $wallet->balances($application));
        $this->postJson(route('expert.wallet.withdraw'), [
            ...$request, 'amount' => '60', 'request_key' => (string) Str::uuid(),
        ])->assertUnprocessable()->assertJsonValidationErrors('amount');

        $this->put(route('expert.wallet.payout'), ['method' => 'bkash', 'bkash_number' => '01700000002'])->assertRedirect();
        $withdrawal = $application->walletEntries()->where('kind', Entry::WITHDRAWAL)->sole();
        $this->assertSame('01700000001', $withdrawal->destination['bkash_number']);

        $this->actingAs($admin)->get('/admin/wallet-withdrawals')->assertOk()->assertSee('Wallet withdrawals');
        Livewire::test(ListWalletWithdrawals::class)
            ->assertCanSeeTableRecords([$withdrawal])
            ->callTableAction('approve', $withdrawal)->assertHasNoErrors()
            ->callTableAction('mark_paid', $withdrawal, ['reference' => 'TX-45'])->assertHasNoErrors();
        $this->assertSame(['available' => 5975, 'held' => 0, 'paid' => 4050], $wallet->balances($application));
        $this->assertSame('TX-45', $withdrawal->refresh()->reference);

        $this->actingAs($expert)->post(route('expert.wallet.withdraw'), [
            'method' => 'bank', 'amount' => '10', 'request_key' => (string) Str::uuid(),
        ])->assertRedirect();
        $this->assertSame(4975, $wallet->balances($application)['available']);
        $declined = $application->walletEntries()->where('status', Entry::PENDING)->sole();
        $this->actingAs($admin);
        Livewire::test(ListWalletWithdrawals::class)
            ->callTableAction('reject', $declined, ['note' => 'Check account details'])->assertHasNoErrors();
        $this->assertSame(5975, $wallet->balances($application)['available']);

        $this->actingAs(User::factory()->create(['role' => User::EXPERT]))->get(route('expert.wallet.show'))->assertNotFound();
    }
}
