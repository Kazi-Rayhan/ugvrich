<?php

namespace Tests\Feature;

use App\Models\ConsultancyRequest;
use App\Models\Expert;
use App\Models\Post;
use App\Models\Project;
use App\Models\ServiceCategory;
use App\Models\Subscriber;
use Database\Seeders\RichContentSeeder;
use Database\Seeders\RichInnovationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PublicSiteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RichContentSeeder::class, RichInnovationSeeder::class]);
    }

    public function test_every_static_page_renders(): void
    {
        foreach (['home', 'about', 'research', 'contact', 'services.index', 'projects.index', 'experts.index', 'news.index'] as $name) {
            $this->get(route($name))->assertOk();
        }
    }

    public function test_detail_pages_render(): void
    {
        $this->get(route('services.show', ServiceCategory::first()))->assertOk();
        $this->get(route('projects.show', Project::first()))->assertOk();
        $this->get(route('experts.show', Expert::first()))->assertOk();
        $this->get(route('news.show', Post::published()->first()))->assertOk();
    }

    public function test_expert_directory_can_be_searched_and_filtered(): void
    {
        $expert = Expert::first();

        $this->get(route('experts.index', ['q' => $expert->name]))
            ->assertOk()
            ->assertSee($expert->name, false);

        $this->get(route('experts.index', ['q' => 'zzzznomatch']))
            ->assertOk()
            ->assertSee('No experts match your search');

        $this->get(route('experts.index', ['area' => ServiceCategory::first()->slug]))->assertOk();
    }

    public function test_projects_can_be_filtered_by_area(): void
    {
        $this->get(route('projects.index', ['area' => 'ict-digital-consultancy']))->assertOk();
    }

    public function test_unpublished_posts_are_not_reachable(): void
    {
        $draft = Post::create([
            'title' => 'Draft item',
            'slug' => 'draft-item',
            'type' => 'news',
            'published_at' => null,
        ]);

        $this->get(route('news.show', $draft))->assertNotFound();
    }

    public function test_inactive_service_area_is_not_reachable(): void
    {
        $category = ServiceCategory::first();
        $category->update(['is_active' => false]);

        $this->get(route('services.show', $category))->assertNotFound();
    }

    public function test_consultancy_request_is_stored_with_its_attachment(): void
    {
        Storage::fake('public');

        $response = $this->post(route('consultancy.store'), [
            'name' => 'Ayesha Rahman',
            'organization' => 'government',
            'email' => 'ayesha@example.org',
            'phone' => '+880 1700 000000',
            'service_category_id' => ServiceCategory::first()->id,
            'requirement' => 'We need a structural safety assessment of twelve public buildings before the next budget cycle.',
            'document' => UploadedFile::fake()->create('brief.pdf', 120, 'application/pdf'),
        ]);

        $response->assertRedirect(route('consultancy.thanks'));
        $response->assertSessionHas('consultancy_request');

        $request = ConsultancyRequest::sole();
        $this->assertSame('Ayesha Rahman', $request->name);
        $this->assertSame('new', $request->status);
        Storage::disk('public')->assertExists($request->document);
    }

    public function test_consultancy_request_validates_input(): void
    {
        $this->post(route('consultancy.store'), [
            'name' => '',
            'email' => 'not-an-email',
            'organization' => 'Somewhere Ltd',
        ])->assertSessionHasErrors(['name', 'email', 'organization']);

        $this->assertSame(0, ConsultancyRequest::count());
    }

    public function test_consultancy_request_rejects_honeypot_submissions(): void
    {
        $this->post(route('consultancy.store'), [
            'name' => 'Spam Bot',
            'email' => 'bot@example.com',
            'requirement' => 'This is a long enough requirement string to pass the minimum.',
            'website' => 'http://spam.example',
        ])->assertSessionHasErrors('website');

        $this->assertSame(0, ConsultancyRequest::count());
    }

    public function test_newsletter_subscription_is_idempotent(): void
    {
        $this->post(route('subscribe'), ['email' => 'reader@example.org'])->assertRedirect();
        $this->post(route('subscribe'), ['email' => 'reader@example.org'])->assertRedirect();

        $this->assertSame(1, Subscriber::where('email', 'reader@example.org')->count());
    }
}
