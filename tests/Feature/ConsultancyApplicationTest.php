<?php

namespace Tests\Feature;

use App\Filament\Resources\ConsultancyApplications\Pages\ListConsultancyApplications;
use App\Filament\Resources\ConsultancyApplications\Pages\ViewConsultancyApplication;
use App\Filament\Resources\ConsultancyApplications\Pages\ViewConsultancyChain;
use App\Filament\Resources\ConsultancyEngagementPolicies\Pages\ListConsultancyEngagementPolicies;
use App\Filament\Resources\ConsultancyEngagements\Pages\ListConsultancyEngagements;
use App\Models\ConsultancyApplication as Application;
use App\Models\ConsultancyEngagementPolicy;
use App\Models\ConsultancyReviewStage;
use App\Models\Expert;
use App\Models\Role;
use App\Models\ServiceCategory;
use App\Models\User;
use App\Notifications\ConsultancyUpdate;
use App\Services\ConsultancyReview;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class ConsultancyApplicationTest extends TestCase
{
    use RefreshDatabase;

    public function test_application_moves_through_both_reviewers_and_publishes_only_after_signing(): void
    {
        Notification::fake();
        Storage::fake('local');
        Storage::fake('public');
        $wing = User::factory()->create(['role' => 'consultancy_staff']);
        $backup = User::factory()->create(['role' => 'consultancy_staff']);
        $director = User::factory()->create(['role' => 'consultancy_staff']);
        $this->actingAs(User::factory()->create());
        $adminPage = $this->get('/admin/consultancy-applications/chain-of-command')->assertOk();
        $this->assertSame(2, substr_count($adminPage->getContent(), 'Add user'));
        Livewire::test(ViewConsultancyChain::class)
            ->assertActionVisible('addWingUser')
            ->assertActionVisible('addDirectorUser')
            ->callAction('addWingUser', ['user_ids' => [$wing->id, $backup->id]])
            ->assertHasNoErrors()
            ->assertSee($wing->email)->assertSee($backup->email)
            ->callAction('addDirectorUser', ['user_ids' => [$director->id]])
            ->assertHasNoErrors()->assertSee($director->email);
        $this->assertTrue($wing->consultancyReviewStages()->where('key', Application::WING)->exists());
        $this->assertNull($wing->fresh()->role_id);
        $this->assertSame(0, $wing->roles()->count());
        $this->assertSame([Application::DIRECTOR], $director->consultancyReviewStages()->pluck('key')->all());
        $this->post(route('researcher.logout'));
        $category = ServiceCategory::create(['name' => 'Advisory', 'slug' => 'advisory', 'is_active' => true]);
        $expertise = $category->services()->create(['name' => 'Energy planning', 'slug' => 'energy-planning', 'is_active' => true]);
        $secondExpertise = $category->services()->create(['name' => 'Grid advisory', 'slug' => 'grid-advisory', 'is_active' => true]);
        $otherCategory = ServiceCategory::create(['name' => 'Design', 'slug' => 'design', 'is_active' => true]);
        $otherExpertise = $otherCategory->services()->create(['name' => 'Building design', 'slug' => 'building-design', 'is_active' => true]);
        $category->services()->create(['name' => 'Retired advisory service', 'slug' => 'retired-advisory', 'is_active' => false]);

        $this->get('/login')->assertOk()->assertSee(route('consultancy.registration.register'))->assertSee(__('researcher.auth.option_innovation_title'));
        $this->assertSame(url('/consultancy-request'), route('consultancy.store'));
        $this->get('/consultancy/expert-registration')->assertOk()
            ->assertSee('lang="en"', false)
            ->assertSee('Consultancy Wing')
            ->assertSee('Advisory')
            ->assertSee('Areas of expertise')
            ->assertSee('Energy planning')
            ->assertDontSee('Retired advisory service')
            ->assertSee('Occupation history')
            ->assertDontSee('name="current_job"', false)
            ->assertDontSee('name="current_designation"', false);

        $form = [
            'name' => 'Example Expert',
            'email' => 'Expert@Example.org',
            'phone' => '+8801234567',
            'location' => 'Barishal, Bangladesh',
            'affiliation_type' => 'external',
            'current_job' => 'Gridworks',
            'occupations' => [
                ['title' => 'Engineer', 'institution' => 'Example', 'start_date' => '2022-01-01', 'end_date' => null],
                ['title' => 'Research Assistant', 'institution' => 'UGV', 'start_date' => '2020-01-01', 'end_date' => '2021-12-31'],
            ],
            'service_category_id' => $category->id,
            'education' => [
                ['qualification' => 'BSc', 'institution' => 'UGV', 'year' => 2019],
                ['qualification' => 'MSc', 'institution' => 'UGV', 'year' => 2022],
            ],
            'experience' => [
                ['position' => 'Engineer', 'organization' => 'Example', 'period' => '2022 to present'],
            ],
            'skills' => [['name' => 'Design'], ['name' => 'Planning']],
        ];
        $this->post(route('consultancy.registration.store'), $form)->assertSessionHasErrors('profile_photo');
        $this->post(route('consultancy.registration.store'), $form)->assertSessionHasErrors('expertise_service_ids');
        $this->post(route('consultancy.registration.store'), [
            ...$form, 'expertise_service_ids' => [$expertise->id, $otherExpertise->id],
        ])->assertSessionHasErrors('expertise_service_ids.1');
        $form['expertise_service_ids'] = [$expertise->id, $secondExpertise->id];
        $invalid = $form;
        $invalid['occupations'][1]['end_date'] = '2019-12-31';
        $invalid['profile_photo'] = UploadedFile::fake()->create('resume.pdf', 20, 'application/pdf');
        $this->post(route('consultancy.registration.store'), $invalid)->assertSessionHasErrors(['occupations.1.end_date', 'profile_photo']);
        $this->post(route('consultancy.registration.store'), [
            ...$form, 'profile_photo' => UploadedFile::fake()->image('portrait.png'),
        ])->assertRedirect(route('consultancy.registration.thanks'));
        $this->get(route('consultancy.registration.thanks'))->assertOk()->assertSee('Application reference')->assertSee('We will email you when there is an update on your application.');

        $application = Application::sole();
        $this->assertSame([$expertise->id, $secondExpertise->id], $application->expertiseServices->modelKeys());
        Storage::disk('local')->assertExists($application->profile_photo_path);
        $this->assertSame([], Storage::disk('public')->allFiles());
        $this->assertSame('expert@example.org', $application->email);
        $this->assertNull($application->current_job);
        $this->assertNull($application->current_designation);
        $this->assertCount(2, $application->education);
        $this->assertSame('Engineer', $application->occupation);
        $this->assertSame($form['occupations'], $application->occupations);
        Notification::assertSentTo([$wing, $backup, $director], ConsultancyUpdate::class);
        Notification::assertSentOnDemand(ConsultancyUpdate::class, fn ($notice) => $notice->title === 'Consultancy application received');

        $this->actingAs($wing)->get('/admin/consultancy-applications')->assertOk()
            ->assertSee('Consultancy Expert Applications')->assertSee('Example Expert')->assertSee('Chain of command');
        $this->get('/admin/consultancy-applications/chain-of-command')->assertOk()
            ->assertSee($wing->email)->assertSee($director->email)->assertSee('1 pending');
        Livewire::test(ViewConsultancyChain::class)->assertActionHidden('addWingUser')->assertActionHidden('addDirectorUser');
        $this->get('/admin/consultancy-applications/'.$application->id)->assertOk()
            ->assertSee('Decision history')->assertSee('Research Assistant')->assertSee('Current stage')->assertSee('Energy planning, Grid advisory');
        $this->get(route('expert.application-photo', $application))->assertOk();
        $this->assertFalse($application->canReview($director));
        Livewire::test(ViewConsultancyApplication::class, ['record' => $application->id])
            ->assertActionVisible('approve')
            ->callAction('approve', ['note' => 'Qualified'])
            ->assertHasNoErrors();
        $this->assertSame(Application::DIRECTOR, $application->refresh()->status);
        Notification::assertSentTo($director, ConsultancyUpdate::class, fn ($notice) => str_contains($notice->title, 'ready for Director'));

        $this->actingAs($director);
        Livewire::test(ListConsultancyApplications::class)
            ->assertTableActionVisible('approve', $application)
            ->callTableAction('approve', $application)
            ->assertHasNoErrors();
        $application->refresh();
        $this->assertSame(Application::APPROVED, $application->status);
        $this->assertNull($application->contract_type);
        $this->assertCount(2, $application->decisions);
        $this->assertSame(0, Expert::count());
        $user = User::findOrFail($application->user_id);
        $this->assertSame(User::EXPERT, $user->role);
        $approval = Notification::sent(
            new \Illuminate\Notifications\AnonymousNotifiable,
            ConsultancyUpdate::class,
            fn ($notice) => $notice->title === 'Consultancy application approved',
        )->sole();
        $token = basename(parse_url($approval->url, PHP_URL_PATH));
        $this->post(route('researcher.logout'));
        $this->get($approval->url)->assertOk();
        $this->post(route('password.store'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'Example-password-2026',
            'password_confirmation' => 'Example-password-2026',
        ])->assertRedirect(route('expert.dashboard'));
        $this->assertAuthenticatedAs($user);
        $this->get('/en/expert/dashboard')->assertOk()->assertSee('Waiting for engagement assignment');
        $this->post(route('expert.sign'))->assertForbidden();

        $admin = User::factory()->create();
        $this->actingAs($admin)->get('/admin/consultancy-engagement-policies?language=bn')->assertOk()->assertSee('দীর্ঘমেয়াদি চুক্তি');
        $this->get('/admin/consultancy-engagement-policies?language=en')->assertOk()->assertSee('Long-term contract');
        $policy = ConsultancyEngagementPolicy::where('contract_type', 'long_term')->sole();
        Livewire::test(ListConsultancyEngagementPolicies::class)
            ->callTableAction('editPolicy', $policy, [
                'sections' => [
                    ['title' => 'Scope of work', 'title_bn' => 'কাজের পরিধি', 'description' => 'Complete the agreed deliverables.', 'description_bn' => 'সম্মত কাজগুলো সম্পন্ন করুন।'],
                    ['title' => '6. Revenue Sharing and Payment policies', 'title_bn' => 'প্রতিবেদন', 'description' => 'Provide a written project summary. The expert receives ___ %.', 'description_bn' => 'প্রকল্পের লিখিত সারসংক্ষেপ দিন।'],
                    ['title' => '12. Term, Renewal, and Termination', 'title_bn' => '১২. মেয়াদ, নবায়ন ও সমাপ্তি', 'description' => 'This MoU shall remain in force for the first (__________) year.', 'description_bn' => 'এই সমঝোতা স্মারক প্রথম (__________) বছর কার্যকর থাকবে।'],
                ],
            ])->assertHasNoErrors();
        $policyVersion = $policy->refresh()->version;
        $this->assertNull($policy->privacy_notice);
        $this->assertNull($policy->participation_policy);

        $this->actingAs($director)->get('/admin/consultancy-engagements')->assertOk()->assertSee('Awaiting assignment');
        $this->get('/admin/consultancy-engagement-policies')->assertForbidden();
        Livewire::test(ListConsultancyEngagements::class)
            ->callTableAction('assign', $application, ['contract' => 'long_term', 'percentage' => '101', 'term_years' => '1.5'])
            ->assertHasTableActionErrors(['percentage', 'term_years']);
        Livewire::test(ListConsultancyEngagements::class)
            ->callTableAction('assign', $application, ['contract' => 'long_term', 'percentage' => '12.50', 'term_years' => '2'])
            ->assertHasNoErrors();
        $this->assertSame($director->id, $application->refresh()->engagement_assigned_by);
        $this->assertSame('12.50', $application->engagement_percentage);
        $this->assertStringContainsString('receives 12.50%.', $application->engagement_policy['sections'][1]['description']);
        $this->assertSame(2, $application->engagement_term_years);
        $this->assertStringContainsString('the first 2 years.', $application->engagement_policy['sections'][2]['description']);
        $this->assertStringContainsString('2 বছর', $application->engagement_policy['sections'][2]['description_bn']);
        Notification::assertSentOnDemand(ConsultancyUpdate::class, fn ($notice) => str_contains($notice->title, 'Your consultancy engagement is ready'));
        $policy->update(['privacy_notice' => 'Future policy revision.']);

        $this->actingAs($user)->get('/en/expert/dashboard')->assertOk()->assertSee('Long-term contract')
            ->assertSee('Continue onboarding')->assertDontSee('Scope of work');
        $this->get('/en/expert/dashboard/onboarding')->assertOk()
            ->assertSee('Complete your onboarding')->assertDontSee('Privacy notice')->assertDontSee('Participation policy')->assertDontSee('Future policy revision.')
            ->assertSee('Scope of work')->assertSee('Provide a written project summary.')->assertSee('12.50%')
            ->assertDontSee('/expert/dashboard/portfolio');
        $this->get('/expert/dashboard/onboarding')->assertOk()->assertSee('কাজের পরিধি')->assertSee('প্রকল্পের লিখিত সারসংক্ষেপ দিন।');
        $this->get('/expert/dashboard/profile')->assertForbidden();
        $this->get('/expert/dashboard/portfolio')->assertForbidden();
        $this->get('/expert/dashboard/contract')->assertForbidden();
        $this->get('/en/expert/dashboard/contract/print')->assertForbidden();
        $this->get('/en/expert/dashboard/contract/pdf')->assertForbidden();
        $this->put(route('expert.profile'))->assertForbidden();
        $this->post(route('expert.portfolio.upload'))->assertForbidden();
        $this->postJson(route('expert.sign'), [
            'terms' => '1', 'policy_version' => $policyVersion,
            'signature' => $this->signature(),
        ])->assertUnprocessable()->assertJsonValidationErrors(['reviewed.percentage', 'reviewed.section_0', 'reviewed.section_1']);
        $reviews = ['percentage' => '1', 'section_0' => '1', 'section_1' => '1', 'section_2' => '1'];
        $this->postJson(route('expert.sign'), [
            'terms' => '1', 'policy_version' => $policyVersion,
            'reviewed' => $reviews,
            'signature' => $this->signature(false),
        ])->assertUnprocessable()->assertJsonValidationErrors('signature');

        $this->post(route('expert.sign'), [
            'terms' => '1', 'policy_version' => $policyVersion,
            'reviewed' => $reviews,
            'signature' => $this->signature(),
        ])->assertRedirect();
        $application->refresh();
        $this->assertNotNull($application->signed_at);
        $this->assertSame($policyVersion, $application->policy_version);
        Storage::disk('local')->assertExists($application->signature_path);
        Storage::disk('public')->assertExists($application->expert->photo);
        $this->get('/en/expert/dashboard')->assertOk()
            ->assertSee('/expert/dashboard/profile')->assertSee('/expert/dashboard/portfolio')
            ->assertSee('/expert/dashboard/contract')->assertSee('Onboarding complete')
            ->assertDontSee('Scope of work')->assertDontSee('Upload portfolio file');
        $this->get('/en/expert/dashboard/profile')->assertOk()
            ->assertSee('Research Assistant')->assertSee('2020-01-01')->assertSee('2021-12-31')
            ->assertSee('BSc')->assertSee('2022 to present')->assertSee('Planning')->assertSee('expert@example.org')
            ->assertSee('Areas of expertise')->assertSee('data-editor', false)
            ->assertSee('Public profile photo')->assertSee('enctype="multipart/form-data"', false);
        $this->get('/expert/dashboard/portfolio')->assertOk();
        $this->get('/en/expert/dashboard/contract')->assertOk()->assertSee('Scope of work')->assertSee('12.50%');
        $this->get('/en/expert/dashboard/contract/print')->assertOk()
            ->assertSee('Memorandum of Understanding')
            ->assertSee('the first 2 years.')
            ->assertSee('Schedule A')
            ->assertSee('data:image/png;base64,', escape: false)
            ->assertDontSee('Witness');
        $this->get('/expert/dashboard/contract/print')->assertOk()
            ->assertSee('সমঝোতা স্মারক')
            ->assertDontSee('সাক্ষী');
        $pdf = $this->mock(\App\Services\ExpertMouPdf::class);
        $pdf->shouldReceive('browser')->once()->andReturn('/test/chromium');
        $pdf->shouldReceive('render')->once()->withArgs(fn (array $data, string $browser) =>
            $data['application']->is($application) && $browser === '/test/chromium'
        )->andReturn('%PDF-test');
        $this->get('/en/expert/dashboard/contract/pdf')->assertOk()
            ->assertHeader('Content-Type', 'application/pdf')
            ->assertHeader('Content-Disposition', 'attachment; filename="UGV-RICH-MoU-'.$application->id.'-en.pdf"');

        $this->get('/experts')->assertOk()->assertSee('Example Expert');
        $this->put(route('expert.profile'), [
            'name' => 'Updated Expert', 'occupation' => 'Consultant', 'phone' => '1234567', 'location' => 'Barishal',
            'bio_html' => '<p style="color:red"><strong>Public biography</strong></p><ul><li>First item</li></ul>',
            'bio_html_bn' => '<p><em>বাংলা জীবনী</em></p>',
            'expertise' => 'Systems design, Automation, Automation',
            'professional_skills' => 'Documentation, Mentoring',
            'department' => 'Engineering',
            'current_job' => 'Gridworks',
            'research_interests' => 'Reliable infrastructure',
            'profile_photo' => UploadedFile::fake()->image('portrait.png', 160, 160),
        ])->assertRedirect();
        $expert = $application->expert()->sole();
        $this->assertSame('Updated Expert', $expert->name);
        Storage::disk('public')->assertExists($expert->photo);
        $this->assertStringStartsWith('experts/profile-photos/'.$application->id.'/', $expert->photo);
        $this->assertSame(['Systems design', 'Automation'], $expert->expertise);
        $this->assertSame(['Documentation', 'Mentoring'], $expert->professional_skills);
        $this->assertStringNotContainsString('style=', $expert->bio_html);
        $this->get('/en/experts/'.$expert->slug)->assertOk()
            ->assertSee(Storage::disk('public')->url($expert->photo))
            ->assertSee('<strong>Public biography</strong>', false)->assertSee('Mentoring')
            ->assertSee('Gridworks')->assertSee('External')->assertDontSee('Reliable infrastructure')
            ->assertDontSee('expert@example.org')->assertDontSee('1234567');
        $this->get('/experts/'.$expert->slug)->assertOk()->assertSee('বাংলা জীবনী');
        $this->post(route('expert.portfolio.upload'), [
            'title' => 'Sample', 'portfolio' => UploadedFile::fake()->image('sample.png'),
        ])->assertRedirect();
        $file = $application->portfolios()->sole();
        $this->get(route('expert.portfolio.download', $file))->assertOk();
        $publicFile = route('experts.portfolio', [$expert, $file]);
        $this->get($publicFile)->assertNotFound();
        $this->patch(route('expert.portfolio.visibility', $file), ['is_public' => '1'])->assertRedirect();
        $this->get('/en/experts/'.$expert->slug)->assertOk()->assertSee('Sample');
        $this->get($publicFile)->assertOk();
        $this->patch(route('expert.portfolio.visibility', $file), ['is_public' => '0'])->assertRedirect();
        $this->get($publicFile)->assertNotFound();
        $this->actingAs(User::factory()->create(['role' => User::RESEARCHER]))
            ->get(route('expert.portfolio.download', $file))->assertNotFound();
        $this->get(route('expert.application-photo', $application))->assertNotFound();

        $backup->consultancyReviewStages()->attach(ConsultancyReviewStage::where('key', Application::DIRECTOR)->sole());
        $this->actingAs(User::factory()->create());
        Livewire::test(ViewConsultancyChain::class)
            ->callAction('removeUser', arguments: ['stage' => Application::WING, 'user' => $backup->id])
            ->assertHasNoErrors();
        $this->assertSame([Application::DIRECTOR], $backup->consultancyReviewStages()->pluck('key')->all());
        $this->assertFalse((new Application(['status' => Application::WING]))->canReview($backup));
    }

    public function test_legacy_reviewer_role_does_not_grant_consultancy_access(): void
    {
        $user = User::factory()->create([
            'role' => 'consultancy_staff',
            'role_id' => Role::where('name', 'consultancy_wing_reviewer')->sole()->id,
        ]);
        $application = new Application(['status' => Application::WING]);

        $this->assertFalse($application->canReview($user));
        $this->actingAs($user)->get('/admin/consultancy-applications/chain-of-command')->assertForbidden();
    }

    public function test_director_decline_notifies_the_applicant_without_issuing_an_account(): void
    {
        Notification::fake();
        $wing = $this->reviewer(Application::WING);
        $director = $this->reviewer(Application::DIRECTOR);
        $category = ServiceCategory::create(['name' => 'Advisory', 'slug' => 'advisory']);
        $application = Application::create([
            'name' => 'Declined Example',
            'email' => 'declined@example.org',
            'phone' => '1234567',
            'location' => 'Barishal',
            'occupation' => 'Consultant',
            'service_category_id' => $category->id,
            'education' => [],
            'skills' => [],
            'status' => Application::WING,
        ]);

        $review = app(ConsultancyReview::class);
        $review->decide($application, $wing, true, null);
        $review->decide($application->refresh(), $director, false, 'The category is at capacity.');

        $this->assertSame(Application::DECLINED, $application->refresh()->status);
        $this->assertNull($application->user_id);
        Notification::assertSentOnDemand(ConsultancyUpdate::class, fn ($notice) =>
            $notice->title === 'Consultancy application declined' && str_contains($notice->message, 'at capacity')
        );

        $this->actingAs($director);
        Livewire::test(ViewConsultancyApplication::class, ['record' => $application->id])
            ->assertActionHidden('delete');

        $admin = User::factory()->create(['role' => User::ADMIN]);
        $this->actingAs($admin);
        $approved = $application->replicate()->fill([
            'email' => 'approved@example.org', 'status' => Application::APPROVED,
        ]);
        $approved->save();
        Livewire::test(ListConsultancyApplications::class)
            ->assertTableActionVisible('delete', $application)
            ->assertTableActionVisible('delete', $approved);
        Livewire::test(ViewConsultancyApplication::class, ['record' => $application->id])
            ->callAction('delete')
            ->assertRedirect();
        $this->assertSoftDeleted($application);
        $pending = $approved->replicate()->fill([
            'email' => 'pending@example.org', 'status' => Application::WING,
        ]);
        $pending->save();
        Livewire::test(ListConsultancyApplications::class)
            ->callTableBulkAction('delete', [$pending]);
        $this->assertSoftDeleted($pending);
        Livewire::test(ListConsultancyEngagements::class)->callTableAction('delete', $approved);
        $this->assertSoftDeleted($approved);
    }

    private function reviewer(string $stage): User
    {
        $user = User::factory()->create(['role' => 'consultancy_staff']);
        $user->consultancyReviewStages()->attach(ConsultancyReviewStage::where('key', $stage)->sole());

        return $user;
    }

    private function signature(bool $draw = true): string
    {
        $image = imagecreatetruecolor(720, 240);
        imagefill($image, 0, 0, imagecolorallocate($image, 255, 255, 255));
        if ($draw) {
            imagesetthickness($image, 7);
            imageline($image, 45, 190, 250, 60, imagecolorallocate($image, 12, 25, 40));
        }

        ob_start();
        imagepng($image);

        return 'data:image/png;base64,'.base64_encode(ob_get_clean());
    }
}
