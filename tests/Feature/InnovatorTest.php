<?php

namespace Tests\Feature;

use App\Models\IdeaSubmission;
use App\Models\User;
use App\Notifications\SetInnovatorPassword;
use Database\Seeders\RichContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/** Submitting an idea makes an innovator account, and the dashboard follows it. */
class InnovatorTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RichContentSeeder::class);
        Storage::fake('public');
        Notification::fake();
    }

    protected function submit(array $overrides = [])
    {
        return $this->post(route('ideas.store'), $overrides + [
            'name' => 'Team Green Campus',
            'email' => 'green@example.com',
            'phone' => '01700000000',
            'title' => 'Campus waste sorting assistant',
            'category' => 'engineering',
            'document' => UploadedFile::fake()->create('idea.pdf', 80, 'application/pdf'),
        ]);
    }

    public function test_a_new_email_becomes_an_innovator_and_is_sent_a_password_link(): void
    {
        $this->submit()->assertRedirect(route('ideas.thanks'));

        $user = User::where('email', 'green@example.com')->sole();

        $this->assertTrue($user->isInnovator());
        $this->assertSame('engineering', IdeaSubmission::sole()->category);
        $this->assertSame($user->id, IdeaSubmission::sole()->user_id);
        Notification::assertSentTo($user, SetInnovatorPassword::class);
    }

    public function test_the_link_sets_the_password_and_opens_the_dashboard(): void
    {
        $this->submit();
        $user = User::where('email', 'green@example.com')->sole();

        $token = null;
        Notification::assertSentTo($user, SetInnovatorPassword::class, function ($notification) use (&$token, $user) {
            $token = (fn () => $this->token)->call($notification);

            return true;
        });

        $this->get(route('innovator.password.set', ['token' => $token, 'email' => $user->email]))->assertOk();

        $this->post(route('innovator.password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'a-good-password',
            'password_confirmation' => 'a-good-password',
        ])->assertRedirect(route('innovator.dashboard'));

        $this->assertAuthenticatedAs($user);
        $this->get(route('innovator.dashboard'))->assertOk()->assertSee('Campus waste sorting assistant');
    }

    public function test_signing_in_takes_an_innovator_to_their_dashboard(): void
    {
        $user = User::factory()->create(['role' => User::INNOVATOR, 'password' => 'secret-pass']);

        $this->post(route('login.store'), ['email' => $user->email, 'password' => 'secret-pass'])
            ->assertRedirect(route('innovator.dashboard', absolute: false));
    }

    public function test_a_second_idea_from_the_same_email_joins_the_same_account(): void
    {
        $this->submit();
        $this->submit(['title' => 'Second idea', 'category' => 'business']);

        $this->assertSame(1, User::where('email', 'green@example.com')->count());
        $this->assertSame(2, User::where('email', 'green@example.com')->sole()->ideaSubmissions()->count());
        Notification::assertSentTimes(SetInnovatorPassword::class, 1);
    }

    public function test_a_staff_or_researcher_email_is_never_turned_into_an_innovator(): void
    {
        $researcher = User::factory()->create(['role' => User::RESEARCHER, 'email' => 'res@example.com']);

        $this->submit(['email' => 'res@example.com'])->assertRedirect(route('ideas.thanks'));

        $this->assertSame(User::RESEARCHER, $researcher->fresh()->role);
        $this->assertNull(IdeaSubmission::sole()->user_id);
        Notification::assertNothingSent();
    }

    public function test_only_innovators_reach_the_dashboard(): void
    {
        $this->get(route('innovator.dashboard'))->assertRedirect(route('login'));

        $researcher = User::factory()->create(['role' => User::RESEARCHER]);
        $this->actingAs($researcher)->get(route('innovator.dashboard'))
            ->assertRedirect(route('researcher.dashboard', absolute: false));
    }

    public function test_a_category_is_required(): void
    {
        $this->submit(['category' => ''])->assertSessionHasErrors('category');
        $this->assertSame(0, IdeaSubmission::count());
    }
}
