<?php

namespace Tests\Feature;

use App\Models\Innovation;
use App\Models\User;
use App\Support\InnovationFramework;
use Database\Seeders\InnovationRecordSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The two innovation lists on /innovation are maintained in the admin; the rest
 * of the page is the proposal as written, and stays in config.
 */
class InnovationRecordTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_document_is_shown_when_no_innovations_have_been_added(): void
    {
        $this->assertSame(0, Innovation::count());

        $doc = InnovationFramework::all();

        $this->assertSame(config('innovation_framework.current'), $doc['current']);
        $this->assertSame(config('innovation_framework.proposed'), $doc['proposed']);
    }

    public function test_the_seeder_loads_the_proposal_into_the_admin(): void
    {
        $this->seed(InnovationRecordSeeder::class);

        $this->assertCount(count(config('innovation_framework.current')), Innovation::phase(Innovation::CURRENT)->get());
        $this->assertCount(count(config('innovation_framework.proposed')), Innovation::phase(Innovation::PROPOSED)->get());

        $scooty = Innovation::where('name', 'Solar Scooty')->firstOrFail();

        $this->assertSame('সোলার স্কুটি', $scooty->getRawOriginal('name_bn'));
        $this->assertNotEmpty($scooty->concept);
        $this->assertNotEmpty($scooty->getRawOriginal('concept_bn'));
    }

    public function test_an_added_innovation_appears_on_the_page(): void
    {
        $this->seed(InnovationRecordSeeder::class);

        Innovation::create([
            'phase' => Innovation::CURRENT,
            'name' => 'Rice Husk Gasifier',
            'lead' => 'Mechanical (Lead)',
            'highlights' => 'Turns husk into cooking gas for a village kitchen.',
            'sort_order' => 99,
        ]);

        $this->get(route('innovation.index'))
            ->assertOk()
            ->assertSee('Rice Husk Gasifier');
    }

    public function test_a_hidden_innovation_is_left_off_the_page(): void
    {
        $this->seed(InnovationRecordSeeder::class);

        Innovation::where('name', 'Solar Scooty')->update(['is_active' => false]);

        $names = collect(InnovationFramework::all()['proposed'])->pluck('name');

        $this->assertNotContains('Solar Scooty', $names);
        $this->assertContains('Solar Powered Boat', $names);
    }

    public function test_each_language_reads_its_own_text(): void
    {
        $this->seed(InnovationRecordSeeder::class);

        $this->get(route('en.innovation.index'))
            ->assertOk()
            ->assertSee('Solar Scooty')
            ->assertSee('সোলার স্কুটি');   // the Bangla name, in brackets

        $this->get(route('innovation.index'))
            ->assertOk()
            ->assertSee('সোলার স্কুটি')
            ->assertSee('Solar Scooty');   // and the English one, the other way round
    }

    public function test_re_seeding_keeps_an_uploaded_photograph_and_the_chosen_order(): void
    {
        $this->seed(InnovationRecordSeeder::class);

        Innovation::where('name', 'Solar Scooty')->update([
            'image' => 'innovation/scooty-on-campus.jpg',
            'sort_order' => 42,
        ]);

        $this->seed(InnovationRecordSeeder::class);

        $scooty = Innovation::where('name', 'Solar Scooty')->firstOrFail();

        $this->assertSame('innovation/scooty-on-campus.jpg', $scooty->image);
        $this->assertSame(42, $scooty->sort_order);
    }

    public function test_the_admin_can_open_an_innovation_for_editing(): void
    {
        $this->seed(InnovationRecordSeeder::class);

        $record = Innovation::where('name', 'Solar Scooty')->firstOrFail();

        // The panel-wide smoke test only covers routes without a parameter, so
        // the edit form is checked here, where there is a record to open.
        $this->actingAs(User::factory()->create())
            ->get("/admin/innovations/{$record->id}/edit")
            ->assertOk()
            ->assertSee('Solar Scooty');
    }
}
