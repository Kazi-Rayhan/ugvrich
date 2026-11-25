<?php

namespace Tests\Feature;

use App\Filament\Resources\Clients\Pages\EditClient;
use App\Filament\Resources\Clients\Pages\ListClients;
use App\Models\Client;
use App\Models\FinanceTransaction;
use App\Models\User;
use App\Models\WorkOrder;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class ClientDeletionTest extends TestCase
{
    use RefreshDatabase;

    public function test_client_delete_removes_linked_sales_records(): void
    {
        $this->actingAs(User::factory()->create(['role' => User::ADMIN]));
        $client = Client::create(['name' => 'Example client']);
        $other = Client::create(['name' => 'Another client']);
        $proposal = $client->proposals()->create(['title' => 'Advisory', 'issue_date' => today()]);
        $workOrder = WorkOrder::create(['sector' => 'ict', 'proposal_id' => $proposal->id]);
        $order = $client->orders()->create(['order_date' => today(), 'work_order_id' => $workOrder->id]);
        $item = $order->items()->create(['service_name' => 'Advisory', 'unit_price' => 100]);
        $payment = $order->payments()->create(['amount' => 100, 'paid_on' => today(), 'method' => 'cash']);
        $entry = FinanceTransaction::create([
            'kind' => FinanceTransaction::INCOME,
            'payment_id' => $payment->id,
            'amount_minor' => 10000,
            'transaction_date' => today(),
            'recorded_by_name' => 'Test admin',
            'request_key' => (string) Str::uuid(),
        ]);
        $otherOrder = $other->orders()->create(['order_date' => today()]);

        Livewire::test(ListClients::class)
            ->callAction(TestAction::make('delete')->table($client))
            ->assertHasNoActionErrors();

        $this->assertModelMissing($client);
        $this->assertModelMissing($proposal);
        $this->assertModelMissing($workOrder);
        $this->assertModelMissing($order);
        $this->assertModelMissing($item);
        $this->assertModelMissing($payment);
        $this->assertNull($entry->refresh()->payment_id);
        $this->assertModelExists($otherOrder);

        Livewire::test(EditClient::class, ['record' => $other->id])
            ->callAction('delete')
            ->assertHasNoActionErrors();

        $this->assertModelMissing($other);
        $this->assertModelMissing($otherOrder);
    }
}
