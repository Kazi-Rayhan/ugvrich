<?php

namespace Tests\Feature;

use App\Models\StudentMembership;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Student membership applications, one per computer inside a ten-minute window. */
class MembershipApplicationTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_form_lists_every_internship_track(): void
    {
        // The Bangla address itself: route() answers in the test's default (English).
        $this->get('/membership')
            ->assertOk()
            ->assertSee('ইন্টার্নশিপ আবেদন', false)
            ->assertSee('স্মার্ট আইসিটি সেবা', false)
            ->assertDontSee('Send code', false);

        $this->get(route('en.membership.create'))
            ->assertOk()
            ->assertSee('Internship application')
            ->assertSee('Smart Electrical Systems & Automation Services')
            ->assertSee('Smart ICT Services')
            ->assertSee('Smart Infrastructure Services')
            ->assertSee('Smart Mechanical & Automobile Services')
            ->assertSee('Business Advisory & Income Tax Services')
            ->assertSee('Language Services')
            ->assertSee('Student ID')
            ->assertSee('e.g. 12222008')
            ->assertSee('you@ugv.edu.bd')
            ->assertSee('Semester 8')
            ->assertDontSee('Semester 9')
            ->assertDontSee('Membership');   // the visitor reads "Internship" throughout
    }

    public function test_the_form_answers_at_internship_and_stays_there(): void
    {
        $this->get(route('en.internship.create'))
            ->assertOk()
            ->assertSee('Internship application')
            ->assertSee(route('en.internship.store'), false);

        $this->post(route('en.internship.store'), $this->application())
            ->assertRedirect(route('en.internship.thanks'));

        $this->assertDatabaseHas(StudentMembership::class, ['student_id' => '221-115-001']);

        $this->get(route('en.internship.thanks'))->assertOk()->assertSee('Smart ICT Services');
    }

    public function test_an_application_is_stored(): void
    {
        $this->post(route('en.membership.store'), $this->application())
            ->assertRedirect(route('en.membership.thanks'));

        $this->assertDatabaseHas(StudentMembership::class, [
            'name' => 'Nadia Rahman',
            'student_id' => '221-115-001',
            'semester' => 6,
            'department' => 'CSE',
            'phone' => '01712345678',
            'email' => 'nadia@ugv.edu.bd',
            'track' => 'ict',
            'status' => 'new',
        ]);

        $this->get(route('en.membership.thanks'))
            ->assertOk()
            ->assertSee('MEM-00001')
            ->assertSee('Smart ICT Services');
    }

    public function test_a_number_with_a_country_code_is_stored_as_a_local_mobile(): void
    {
        $this->post(route('membership.store'), $this->application(['phone' => '+8801712345678']))
            ->assertRedirect(route('membership.thanks'));

        $this->assertDatabaseHas(StudentMembership::class, [
            'phone' => '01712345678',
        ]);
    }

    public function test_the_same_computer_must_wait_ten_minutes(): void
    {
        $this->post(route('membership.store'), $this->application())->assertRedirect();

        $this->post(route('membership.store'), $this->application([
            'student_id' => '221-115-002',
            'phone' => '01812345678',
            'email' => 'nadia.two@ugv.edu.bd',
        ]))->assertSessionHasErrors('form');

        $this->assertSame(1, StudentMembership::count());

        $this->travel(11)->minutes();

        $this->post(route('membership.store'), $this->application([
            'student_id' => '221-115-002',
            'phone' => '01812345678',
            'email' => 'nadia.two@ugv.edu.bd',
        ]))->assertRedirect(route('membership.thanks'));

        $this->assertSame(2, StudentMembership::count());
    }

    public function test_another_computer_can_apply_during_the_window(): void
    {
        $this->post(route('membership.store'), $this->application())->assertRedirect();

        $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.8'])
            ->post(route('membership.store'), $this->application([
                'student_id' => '221-115-002',
                'phone' => '01812345678',
                'email' => 'other@ugv.edu.bd',
            ]))
            ->assertRedirect(route('membership.thanks'));

        $this->assertSame(2, StudentMembership::count());
    }

    public function test_a_rejected_form_does_not_start_the_wait(): void
    {
        $this->post(route('membership.store'), $this->application(['track' => 'cooking']))
            ->assertSessionHasErrors('track');

        $this->post(route('membership.store'), $this->application())
            ->assertRedirect(route('membership.thanks'));

        $this->assertSame(1, StudentMembership::count());
    }

    public function test_the_same_student_id_cannot_apply_twice(): void
    {
        $this->post(route('membership.store'), $this->application())->assertRedirect();

        $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.9'])
            ->post(route('membership.store'), $this->application([
                'phone' => '01812345678',
                'email' => 'other@ugv.edu.bd',
            ]))
            ->assertSessionHasErrors('student_id');

        $this->assertSame(1, StudentMembership::count());
    }

    public function test_a_filled_honeypot_is_rejected(): void
    {
        $this->post(route('membership.store'), $this->application(['website' => 'http://spam.test']))
            ->assertSessionHasErrors('website');

        $this->assertSame(0, StudentMembership::count());
    }

    /** @param  array<string, mixed>  $overrides */
    protected function application(array $overrides = []): array
    {
        return $overrides + [
            'name' => 'Nadia Rahman',
            'student_id' => '221-115-001',
            'semester' => 6,
            'department' => 'CSE',
            'phone' => '01712345678',
            'email' => 'nadia@ugv.edu.bd',
            'track' => 'ict',
        ];
    }
}
