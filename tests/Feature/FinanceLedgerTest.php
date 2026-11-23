<?php

namespace Tests\Feature;

use App\Filament\Resources\FinanceAccounts\Pages\ListFinanceAccounts;
use App\Filament\Resources\FinanceTransactions\Pages\ListFinanceTransactions;
use App\Filament\Resources\FinanceTransactions\Pages\ListFinanceTransfers;
use App\Models\FinanceAccount;
use App\Models\FinanceTransaction;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Services\FinanceLedger;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class FinanceLedgerTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_need_finance_permissions_to_open_the_ledger(): void
    {
        $role = Role::create(['name' => 'finance_reader', 'display_name' => 'Finance reader', 'group' => Role::DASHBOARD]);
        $staff = User::factory()->create(['role_id' => $role->id]);

        $this->actingAs($staff)->get('/admin/finance-accounts')->assertForbidden();
        $this->get('/admin/finance-transactions')->assertForbidden();
        $this->get('/admin/finance-transactions/transfers')->assertForbidden();

        $permission = Permission::create(['name' => 'view_any_finance_transaction', 'group' => 'Finance']);
        $role->permissions()->attach($permission);
        $staff->forgetPermissions();

        $this->get('/admin/finance-transactions')->assertOk()->assertDontSee('Record income');
        $this->get('/admin/finance-transactions/transfers')->assertOk()->assertDontSee('Record transfer');
    }

    public function test_finance_actions_post_and_reverse_a_transfer(): void
    {
        $this->actingAs(User::factory()->create());
        Livewire::test(ListFinanceAccounts::class)
            ->callAction('create', ['name' => 'Office cash', 'type' => 'cash', 'is_active' => true])
            ->assertHasNoErrors();

        $cash = FinanceAccount::where('name', 'Office cash')->sole();
        $bank = FinanceAccount::create(['name' => 'Bank', 'type' => 'bank']);

        Livewire::test(ListFinanceTransactions::class)
            ->callAction('opening', $this->entry(['to_account_id' => $cash->id]))
            ->assertHasNoErrors();

        $opening = FinanceTransaction::sole();
        $this->assertSame(10025, $cash->refresh()->balance_minor);

        $transferPage = Livewire::test(ListFinanceTransfers::class)
            ->assertCanNotSeeTableRecords([$opening])
            ->callAction('transfer', $this->entry([
                'from_account_id' => $cash->id,
                'to_account_id' => $bank->id,
                'amount' => '40.15',
            ]))
            ->assertHasNoErrors();

        $transfer = FinanceTransaction::where('kind', FinanceTransaction::TRANSFER)->sole();
        $this->assertSame(6010, $cash->refresh()->balance_minor);
        $this->assertSame(4015, $bank->refresh()->balance_minor);

        $transferPage->assertCanSeeTableRecords([$transfer])
            ->callTableAction('reverse', $transfer, ['reason' => 'Wrong destination'])
            ->assertHasNoErrors()
            ->callAction('exportCsv')
            ->assertFileDownloaded('finance-transfers.csv');

        $this->assertSame(10025, $cash->refresh()->balance_minor);
        $this->assertSame(0, $bank->refresh()->balance_minor);
        $this->assertDatabaseHas('finance_transactions', ['reversal_of_id' => $transfer->id, 'notes' => 'Wrong destination']);

        Livewire::test(ListFinanceTransactions::class)
            ->assertCanSeeTableRecords([$opening, $transfer])
            ->filterTable('account', ['id' => $bank->id])
            ->assertCanSeeTableRecords([$transfer])
            ->assertCanNotSeeTableRecords([$opening]);
    }

    public function test_duplicate_posts_and_failed_debits_preserve_the_balance(): void
    {
        $admin = User::factory()->create();
        $cash = FinanceAccount::create(['name' => 'Office cash', 'type' => 'cash']);
        $ledger = app(FinanceLedger::class);
        $income = $this->entry(['kind' => FinanceTransaction::INCOME, 'to_account_id' => $cash->id]);

        $this->assertSame($ledger->post($admin, $income)->id, $ledger->post($admin, $income)->id);

        try {
            $ledger->post($admin, $this->entry([
                'kind' => FinanceTransaction::EXPENSE, 'from_account_id' => $cash->id, 'amount' => '100.26',
            ]));
            $this->fail('A debit over the available balance was accepted.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('amount', $exception->errors());
        }

        $this->assertDatabaseCount('finance_transactions', 1);
        $this->assertSame(10025, $cash->refresh()->balance_minor);

        $expense = $ledger->post($admin, $this->entry([
            'kind' => FinanceTransaction::EXPENSE, 'from_account_id' => $cash->id, 'amount' => '0.26',
        ]));
        $this->assertSame(9999, $cash->refresh()->balance_minor);
        $cash->update(['is_active' => false]);

        $reversal = $ledger->reverse($admin, $expense, 'Expense cancelled');
        $this->assertSame($reversal->id, $ledger->reverse($admin, $expense, 'Retried reversal')->id);
        $this->assertSame(10025, $cash->refresh()->balance_minor);
    }

    private function entry(array $fields): array
    {
        return $fields + [
            'amount' => '100.25',
            'request_key' => (string) Str::uuid(),
            'transaction_date' => today('Asia/Dhaka')->toDateString(),
        ];
    }
}
