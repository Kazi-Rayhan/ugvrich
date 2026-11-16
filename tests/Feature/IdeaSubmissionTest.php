<?php

namespace Tests\Feature;

use App\Models\IdeaSubmission;
use App\Models\User;
use App\Filament\Resources\IdeaSubmissions\Pages\ListIdeaSubmissions;
use App\Filament\Resources\IdeaSubmissions\Pages\EditIdeaSubmission;
use Livewire\Livewire;
use Database\Seeders\RichContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/** Startup & Incubation: the journey page and the idea form behind it. */
class IdeaSubmissionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RichContentSeeder::class);
    }

    public function test_startup_page_lists_every_stage(): void
    {
        $response = $this->get(route('startup'))->assertOk();

        $response->assertSee('From Idea to', false);

        foreach (config('rich.startup_stages') as $label) {
            $response->assertSee($label, false);
        }
    }

    public function test_admin_selects_an_idea_in_two_stages(): void
    {
        $this->actingAs(User::factory()->create());
        $idea = IdeaSubmission::create([
            'name' => 'Test team', 'email' => 'team@example.com',
            'phone' => '01700000000', 'title' => 'Test idea',
            'role' => 'student', 'stage' => 'idea', 'status' => 'new',
        ]);

        Livewire::test(ListIdeaSubmissions::class)
            ->assertTableActionVisible('primarySelect', $idea)
            ->assertTableActionHidden('finalSelect', $idea)
            ->callTableAction('primarySelect', $idea);

        $this->assertSame('primary_selected', $idea->fresh()->status);
        $this->assertNotNull($idea->fresh()->reviewed_at);

        Livewire::test(ListIdeaSubmissions::class)
            ->assertTableActionVisible('finalSelect', $idea)
            ->callTableAction('finalSelect', $idea);

        $this->assertSame('final_selected', $idea->fresh()->status);
    }

    public function test_editing_cannot_skip_primary_selection(): void
    {
        $this->actingAs(User::factory()->create());
        $idea = IdeaSubmission::create([
            'name' => 'Test team', 'email' => 'team@example.com',
            'phone' => '01700000000', 'title' => 'Test idea',
            'role' => 'student', 'stage' => 'idea', 'status' => 'new',
        ]);

        Livewire::test(EditIdeaSubmission::class, ['record' => $idea->getRouteKey()])
            ->fillForm(['status' => 'final_selected'])
            ->call('save')
            ->assertHasFormErrors(['status']);

        $this->assertSame('new', $idea->fresh()->status);
    }

    public function test_idea_form_renders(): void
    {
        $this->get(route('ideas.create'))->assertOk()->assertSee('Submit your idea', false);
    }

    public function test_an_idea_is_stored_with_its_attachment(): void
    {
        Storage::fake('public');

        // Step one registers (name or team, email, phone, title); step two is the file.
        $this->post(route('ideas.store'), [
            'name' => 'Team Green Campus',
            'email' => 'rafiq@example.com',
            'phone' => '01700000000',
            'title' => 'Campus waste sorting assistant',
            'category' => 'engineering',
            'document' => UploadedFile::fake()->create('sketch.pdf', 80, 'application/pdf'),
        ])->assertRedirect(route('ideas.thanks'));

        $idea = IdeaSubmission::sole();

        $this->assertSame('Campus waste sorting assistant', $idea->title);
        $this->assertSame('student', $idea->role);
        $this->assertSame('idea', $idea->stage);
        $this->assertSame('new', $idea->status);
        Storage::disk('public')->assertExists($idea->document);
    }

    public function test_an_idea_needs_the_registration_details_and_a_file(): void
    {
        $this->post(route('ideas.store'), [
            'name' => '',
            'email' => 'not-an-email',
            'title' => '',
        ])->assertSessionHasErrors(['name', 'email', 'phone', 'title', 'document']);

        $this->assertSame(0, IdeaSubmission::count());
    }

    public function test_honeypot_submissions_are_rejected(): void
    {
        $this->post(route('ideas.store'), [
            'name' => 'Spam Bot',
            'email' => 'spam@example.com',
            'phone' => '01700000000',
            'title' => 'Buy cheap things',
            'document' => UploadedFile::fake()->create('spam.pdf', 10, 'application/pdf'),
            'website' => 'http://spam.example',
        ])->assertSessionHasErrors('website');

        $this->assertSame(0, IdeaSubmission::count());
    }

    public function test_thank_you_page_is_not_reachable_directly(): void
    {
        $this->get(route('ideas.thanks'))->assertRedirect(route('startup'));
    }
}
