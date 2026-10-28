<?php

namespace App\Filament\Resources\Proposals\Pages;

use App\Filament\Resources\Proposals\ProposalResource;
use App\Models\Client;
use App\Models\ConsultancyRequest;
use App\Models\Proposal;
use App\Support\Sales\ClientResolver;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\CreateRecord\Concerns\HasWizard;
use Illuminate\Database\Eloquent\Model;

/**
 * A new proposal, and, when the client is new, the client with it.
 *
 * Client, proposal, lines and categories are written in one database
 * transaction: if any part fails, none of it is kept.
 */
class CreateProposal extends CreateRecord
{
    use HasWizard;

    protected static string $resource = ProposalResource::class;

    protected static ?string $title = 'New proposal';

    /** Client → proposal → services & pricing → scope & conditions → terms & review. */
    public function getSteps(): array
    {
        return ProposalResource::steps();
    }

    protected ?bool $hasDatabaseTransactions = true;

    /** Opened from a consultancy request (?request=ID): start from what the requester sent. */
    public function mount(): void
    {
        parent::mount();

        $request = ConsultancyRequest::find(request()->integer('request'));

        if (! $request) {
            return;
        }

        $existing = (filled($request->email) ? Client::where('email', mb_strtolower(trim($request->email)))->first() : null)
            ?? (filled($request->phone) ? Client::where('phone_normalized', Client::normalizePhone($request->phone))->first() : null);

        $this->form->fill([
            'client_mode' => $existing ? 'existing' : 'new',
            'client_id' => $existing?->id,
            'new_client' => $existing ? [] : [
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'organization_type' => in_array($request->organization, ConsultancyRequest::ORGANIZATION_TYPES, true) ? $request->organization : null,
                'organization' => in_array($request->organization, ConsultancyRequest::ORGANIZATION_TYPES, true) ? null : $request->organization,
            ],
            'consultancy_request_id' => $request->id,
            'title' => 'Consultancy proposal for '.($request->name ?: 'client'),
            'issue_date' => now()->toDateString(),
            'valid_until' => now()->addDays(30)->toDateString(),
            'categories' => array_filter([$request->service_category_id]),
            'conditions' => \App\Filament\Support\SalesConditions::sync([], array_filter([$request->service_category_id]), []),
            'extra_discount' => 0,
            'tax_rate' => 0,
            'notes' => $request->requirement ? 'Request: '.$request->requirement : null,
        ]);
    }

    protected function handleRecordCreation(array $data): Model
    {
        $mode = $data['client_mode'] ?? 'existing';
        $newClient = (array) ($data['new_client'] ?? []);
        unset($data['client_mode'], $data['new_client']);

        if ($mode === 'new') {
            $data['client_id'] = ClientResolver::resolve($newClient, auth()->id())->id;
        }

        return Proposal::create($data + ['status' => 'draft', 'created_by' => auth()->id()]);
    }

    protected function afterCreate(): void
    {
        $this->record->recalculate();
    }

    protected function getRedirectUrl(): string
    {
        return ProposalResource::getUrl('view', ['record' => $this->record]);
    }
}
