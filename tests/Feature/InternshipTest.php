<?php

namespace Tests\Feature;

use App\Models\InternshipApplication;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/** The internship application form, its thank-you page and the rules it keeps. */
class InternshipTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
    }

    protected function apply(array $overrides = [])
    {
        return $this->post(route('internship.store'), $overrides + [
            'name' => 'Rafiq Islam',
            'email' => 'Rafiq@Example.com',
            'phone' => '+880 1712-345678',
            'university' => 'University of Global Village (UGV)',
            'department' => 'Computer Science & Engineering',
            'programme' => 'BSc in CSE',
            'year_level' => '3',
            'cgpa' => '3.45',
            'track' => 'ict',
            'duration_months' => '3',
            'start_date' => now()->addWeek()->toDateString(),
            'mode' => 'hybrid',
            'motivation' => 'I want to build real software with a team and learn how consultancy projects are run.',
            'cv' => UploadedFile::fake()->create('cv.pdf', 120, 'application/pdf'),
        ]);
    }

    public function test_the_form_renders_in_both_languages(): void
    {
        $this->get(route('en.internship.create'))->assertOk()->assertSee('Internship application');
        $this->get('/internship')->assertOk()->assertSee('ইন্টার্নশিপ আবেদন');
    }

    public function test_an_application_is_stored_with_its_cv(): void
    {
        $this->apply()->assertRedirect(route('internship.thanks'));

        $application = InternshipApplication::sole();

        $this->assertSame('rafiq@example.com', $application->email);
        $this->assertSame('01712345678', $application->phone);   // however it was typed
        $this->assertSame('ict', $application->track);
        $this->assertSame(3, $application->duration_months);
        $this->assertSame('new', $application->status);
        Storage::disk('public')->assertExists($application->cv);

        $this->get(route('internship.thanks'))->assertOk()->assertSee($application->reference);
    }

    public function test_the_essentials_are_required(): void
    {
        $this->post(route('internship.store'), [])
            ->assertSessionHasErrors(['name', 'email', 'phone', 'university', 'department', 'programme', 'year_level', 'track', 'duration_months', 'start_date', 'mode', 'motivation', 'cv']);

        $this->assertSame(0, InternshipApplication::count());
    }

    public function test_a_past_start_date_and_a_foreign_number_are_refused(): void
    {
        $this->apply([
            'start_date' => now()->subDay()->toDateString(),
            'phone' => '12345',
        ])->assertSessionHasErrors(['start_date', 'phone']);
    }

    public function test_one_computer_waits_before_a_second_application(): void
    {
        $this->apply()->assertRedirect(route('internship.thanks'));
        $this->apply(['email' => 'other@example.com'])->assertSessionHasErrors('form');

        $this->assertSame(1, InternshipApplication::count());
    }

    public function test_the_thank_you_page_is_not_reachable_on_its_own(): void
    {
        $this->get(route('internship.thanks'))->assertRedirect(route('internship.create'));
    }
}
