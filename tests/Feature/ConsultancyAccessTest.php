<?php

namespace Tests\Feature;

use App\Filament\Resources\ResearcherUsers\Pages\CreateResearcherUser;
use App\Models\ConsultancyApplication;
use App\Models\ExpertWalletEntry;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ConsultancyAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_permissions_allow_scoped_researcher_account_management_and_consultancy_read_access(): void
    {
        $this->artisan('permissions:sync')->assertSuccessful();

        $role = Role::create(['name' => 'research_intake', 'display_name' => 'Research intake', 'group' => Role::DASHBOARD]);
        $role->syncPermissionNames([
            User::ACCESS_PANEL, 'view_any_researcher_user', 'create_researcher_user', 'update_researcher_user',
            'view_any_consultancy_application', 'view_any_expert_wallet_entry',
        ]);
        $staff = User::factory()->create(['role' => 'consultancy_staff', 'role_id' => $role->id]);
        $admin = User::factory()->create();

        $this->actingAs($staff)->get('/admin/researcher-users')->assertOk()->assertSee('Researcher Users');
        $this->get('/admin/researcher-users/'.$admin->id.'/edit')->assertNotFound();

        Livewire::test(CreateResearcherUser::class)
            ->fillForm(['name' => 'Researcher Example', 'email' => 'researcher@example.test', 'password' => 'Example-password-2026'])
            ->call('create')->assertHasNoFormErrors();
        $researcher = User::where('email', 'researcher@example.test')->sole();
        $this->assertTrue($researcher->isResearcher());
        $this->assertNotNull($researcher->researcherProfile);
        $this->assertFalse($staff->can('review', new ConsultancyApplication(['status' => ConsultancyApplication::WING])));
        $this->assertFalse($staff->can('viewDestination', new ExpertWalletEntry));
        $this->get('/admin/consultancy-applications')->assertOk();
        $this->get('/admin/wallet-withdrawals')->assertOk();
        $this->get('/admin/expert-wallets')->assertForbidden();
    }
}
