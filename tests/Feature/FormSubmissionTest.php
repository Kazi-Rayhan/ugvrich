<?php

namespace Tests\Feature;

use App\Models\ConsultancyRequest;
use App\Models\ContactMessage;
use App\Models\ServiceCategory;
use Database\Seeders\RichContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Setting;
use App\Support\MeetingSlots;
use App\Support\Site;
use Carbon\CarbonImmutable;
use Tests\TestCase;

/** The two public forms are separate: general messages and consultancy requests. */
class FormSubmissionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RichContentSeeder::class);
    }

    public function test_both_form_pages_render(): void
    {
        $this->get(route('contact'))->assertOk()->assertSee('Send a message');
        $this->get(route('consultancy.create'))->assertOk()->assertSee('Request consultancy', false);
    }

    public function test_contact_message_is_stored(): void
    {
        $this->post(route('contact.store'), [
            'name' => 'Test Person',
            'email' => 't@example.com',
            'subject' => 'Hello',
            'message' => 'This is a test enquiry message body.',
        ])->assertRedirect();

        $this->assertDatabaseHas(ContactMessage::class, [
            'email' => 't@example.com',
            'status' => 'new',
        ]);
    }

    public function test_contact_message_rejects_a_short_body(): void
    {
        $this->post(route('contact.store'), [
            'name' => 'Test Person',
            'email' => 't@example.com',
            'message' => 'too short',
        ])->assertSessionHasErrors('message');

        $this->assertSame(0, ContactMessage::count());
    }

    public function test_consultancy_request_is_stored_with_its_service(): void
    {
        $category = ServiceCategory::with('services')->first();
        $service = $category->services->first();

        $this->post(route('consultancy.store'), [
            'name' => 'Client X',
            'email' => 'c@example.com',
            'service_category_id' => $category->id,
            'area_of_interest' => $service->name,
            'requirement' => 'We need a structural assessment of two buildings before the monsoon.',
        ])->assertRedirect(route('consultancy.thanks'));

        $this->assertDatabaseHas(ConsultancyRequest::class, [
            'email' => 'c@example.com',
            'service_category_id' => $category->id,
            'area_of_interest' => $service->name,
        ]);
        $this->assertSame(0, ContactMessage::count());
    }

    public function test_a_consultancy_request_needs_only_a_name(): void
    {
        $this->post(route('consultancy.store'), [
            'name' => 'Walk-in Client',
            'organization' => 'ngo',
        ])->assertRedirect(route('consultancy.thanks'));

        $this->assertDatabaseHas(ConsultancyRequest::class, [
            'name' => 'Walk-in Client',
            'organization' => 'ngo',
            'email' => null,
            'requirement' => null,
        ]);

        $this->post(route('consultancy.store'), [
            'name' => 'Walk-in Client',
            'organization' => 'Acme Ltd',
        ])->assertSessionHasErrors('organization');
    }

    public function test_a_consultancy_request_can_name_a_meeting_slot(): void
    {
        $open = CarbonImmutable::now()->next(CarbonImmutable::TUESDAY)->toDateString();

        $this->post(route('consultancy.store'), [
            'name' => 'Nadia Rahman',
            'email' => 'nadia@example.test',
            'requirement' => 'We would like an energy audit of our two production lines before the next quarter.',
            'preferred_date' => $open,
            'preferred_slot' => '16:00-16:30',
        ])->assertRedirect(route('consultancy.thanks'));

        $request = ConsultancyRequest::where('email', 'nadia@example.test')->sole();

        $this->assertSame($open, $request->preferred_date->toDateString());
        $this->assertSame('16:00-16:30', $request->preferred_slot);
    }

    public function test_the_office_refuses_a_meeting_on_a_closed_day(): void
    {
        foreach ([CarbonImmutable::THURSDAY, CarbonImmutable::FRIDAY] as $closed) {
            $this->post(route('consultancy.store'), [
                'name' => 'Nadia Rahman',
                'email' => 'nadia@example.test',
                'requirement' => 'We would like an energy audit of our two production lines before the next quarter.',
                'preferred_date' => CarbonImmutable::now()->next($closed)->toDateString(),
                'preferred_slot' => '16:00-16:30',
            ])->assertSessionHasErrors('preferred_date');
        }
    }

    public function test_a_slot_needs_a_date_and_must_be_one_the_office_keeps(): void
    {
        $base = [
            'name' => 'Nadia Rahman',
            'email' => 'nadia@example.test',
            'requirement' => 'We would like an energy audit of our two production lines before the next quarter.',
        ];

        $this->post(route('consultancy.store'), $base + ['preferred_slot' => '16:00-16:30'])
            ->assertSessionHasErrors('preferred_date');

        $this->post(route('consultancy.store'), $base + [
            'preferred_date' => CarbonImmutable::now()->next(CarbonImmutable::TUESDAY)->toDateString(),
            'preferred_slot' => '03:00-03:30',
        ])->assertSessionHasErrors('preferred_slot');
    }

    public function test_a_slot_cannot_be_booked_twice(): void
    {
        $open = CarbonImmutable::now()->next(CarbonImmutable::TUESDAY)->toDateString();

        $booking = [
            'name' => 'Nadia Rahman',
            'email' => 'nadia@example.test',
            'requirement' => 'We would like an energy audit of our two production lines before the next quarter.',
            'preferred_date' => $open,
            'preferred_slot' => '16:00-16:30',
        ];

        $this->post(route('consultancy.store'), $booking)->assertRedirect(route('consultancy.thanks'));

        // Somebody else asking for the same half hour is turned away.
        $this->post(route('consultancy.store'), [...$booking, 'email' => 'other@example.test'])
            ->assertSessionHasErrors('preferred_slot');

        // The next slot along is still free.
        $this->post(route('consultancy.store'), [...$booking, 'email' => 'other@example.test', 'preferred_slot' => '16:30-17:00'])
            ->assertRedirect(route('consultancy.thanks'));
    }

    public function test_a_released_request_gives_its_slot_back(): void
    {
        $open = CarbonImmutable::now()->next(CarbonImmutable::TUESDAY)->toDateString();

        ConsultancyRequest::create([
            'name' => 'Nadia Rahman',
            'email' => 'nadia@example.test',
            'requirement' => 'An energy audit of two production lines.',
            'preferred_date' => $open,
            'preferred_slot' => '16:00-16:30',
            'status' => 'declined',
        ]);

        $this->post(route('consultancy.store'), [
            'name' => 'Other Person',
            'email' => 'other@example.test',
            'requirement' => 'We would like an energy audit of our two production lines before the next quarter.',
            'preferred_date' => $open,
            'preferred_slot' => '16:00-16:30',
        ])->assertRedirect(route('consultancy.thanks'));
    }

    public function test_the_office_hours_come_from_the_settings(): void
    {
        Setting::updateOrCreate(['key' => 'booking_opens'], ['value' => '10:00', 'group' => 'booking', 'type' => 'text']);
        Setting::updateOrCreate(['key' => 'booking_closes'], ['value' => '12:00', 'group' => 'booking', 'type' => 'text']);
        Setting::updateOrCreate(['key' => 'booking_slot_minutes'], ['value' => '60', 'group' => 'booking', 'type' => 'text']);
        Setting::updateOrCreate(['key' => 'booking_closed_days'], ['value' => '[0]', 'group' => 'booking', 'type' => 'json']);
        Site::flush();

        $this->assertSame(['10:00-11:00', '11:00-12:00'], MeetingSlots::values());

        // Sunday is now the closed day, and Thursday is open.
        $this->assertFalse(MeetingSlots::isOpenOn(CarbonImmutable::now()->next(CarbonImmutable::SUNDAY)));
        $this->assertTrue(MeetingSlots::isOpenOn(CarbonImmutable::now()->next(CarbonImmutable::THURSDAY)));
    }

    public function test_the_lunch_break_is_kept_free(): void
    {
        // The seeded office breaks 13:00 to 15:00.
        $this->assertNotContains('13:00-13:30', MeetingSlots::values());
        $this->assertNotContains('14:30-15:00', MeetingSlots::values());
        $this->assertContains('12:30-13:00', MeetingSlots::values());
        $this->assertContains('15:00-15:30', MeetingSlots::values());

        $this->post(route('consultancy.store'), [
            'name' => 'Nadia Rahman',
            'email' => 'nadia@example.test',
            'requirement' => 'We would like an energy audit of our two production lines before the next quarter.',
            'preferred_date' => CarbonImmutable::now()->next(CarbonImmutable::TUESDAY)->toDateString(),
            'preferred_slot' => '13:30-14:00',
        ])->assertSessionHasErrors('preferred_slot');
    }

    public function test_the_break_can_be_turned_off(): void
    {
        Setting::updateOrCreate(['key' => 'booking_break_starts'], ['value' => '', 'group' => 'booking', 'type' => 'text']);
        Setting::updateOrCreate(['key' => 'booking_break_ends'], ['value' => '', 'group' => 'booking', 'type' => 'text']);
        Site::flush();

        $this->assertNull(MeetingSlots::breakWindow());
        $this->assertContains('13:00-13:30', MeetingSlots::values());
    }
}
