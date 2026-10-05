<?php

namespace Database\Seeders;

use App\Helpers\PolicyHelper;
use App\Filament\Pages\ManageSiteSettings;
use App\Filament\Pages\ReportsAnalytics;
use App\Models\ConsultancyRequest;
use App\Models\ContactMessage;
use App\Models\CoreArea;
use App\Models\Expert;
use App\Models\Facility;
use App\Models\Faq;
use App\Models\FundingOpportunity;
use App\Models\IdeaSubmission;
use App\Models\Innovation;
use App\Models\InnovationArea;
use App\Models\Partner;
use App\Models\Permission;
use App\Models\Post;
use App\Models\Project;
use App\Models\Publication;
use App\Models\ResearchFunding;
use App\Models\ResearchIdea;
use App\Models\ResearchManuscript;
use App\Models\ResearchProposal;
use App\Models\ResearchReview;
use App\Models\ResearcherProfile;
use App\Models\ResearchSupport;
use App\Models\Role;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Setting;
use App\Models\Stat;
use App\Models\StudentTeam;
use App\Models\Subscriber;
use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * The roles and permissions the research side runs on.
 *
 * Safe to run again: everything is matched on its name, so re-running adds what
 * is new and leaves what is there. It never touches a user's own role unless
 * that user has none, so an assignment made by hand in the admin survives a
 * later deployment.
 */
class RolePermissionSeeder extends Seeder
{
    /**
     * What each model's permissions are, beyond the standard five.
     *
     * `review` is deciding on a submission; `setStatus` is RICH setting a
     * project's official standing, which is not the researcher's to set.
     *
     * @var array<class-string, array<int, string>>
     */
    protected array $models = [
        ResearchIdea::class => ['viewAny', 'view', 'create', 'update', 'delete', 'review'],
        ResearchProposal::class => ['viewAny', 'view', 'create', 'update', 'delete', 'review'],
        ResearchManuscript::class => ['viewAny', 'view', 'create', 'update', 'delete', 'review'],
        ResearchFunding::class => ['viewAny', 'view', 'create', 'update', 'delete'],
        FundingOpportunity::class => ['viewAny', 'view', 'create', 'update', 'delete'],
        ResearchSupport::class => ['viewAny', 'view', 'create', 'update', 'delete'],
        Project::class => ['viewAny', 'view', 'create', 'update', 'delete', 'setStatus'],
        ResearchReview::class => ['viewAny', 'view', 'create'],
        ResearcherProfile::class => ['viewAny', 'view'],
        ConsultancyRequest::class => ['viewAny', 'view', 'create', 'update', 'delete'],
        ContactMessage::class => ['viewAny', 'view', 'create', 'update', 'delete'],
        CoreArea::class => ['viewAny', 'view', 'create', 'update', 'delete'],
        Expert::class => ['viewAny', 'view', 'create', 'update', 'delete'],
        Facility::class => ['viewAny', 'view', 'create', 'update', 'delete'],
        Faq::class => ['viewAny', 'view', 'create', 'update', 'delete'],
        IdeaSubmission::class => ['viewAny', 'view', 'create', 'update', 'delete'],
        Innovation::class => ['viewAny', 'view', 'create', 'update', 'delete'],
        InnovationArea::class => ['viewAny', 'view', 'create', 'update', 'delete'],
        Partner::class => ['viewAny', 'view', 'create', 'update', 'delete'],
        Post::class => ['viewAny', 'view', 'create', 'update', 'delete'],
        Publication::class => ['viewAny', 'view', 'create', 'update', 'delete'],
        Service::class => ['viewAny', 'view', 'create', 'update', 'delete'],
        ServiceCategory::class => ['viewAny', 'view', 'create', 'update', 'delete'],
        Setting::class => ['viewAny', 'view', 'create', 'update', 'delete'],
        Stat::class => ['viewAny', 'view', 'create', 'update', 'delete'],
        StudentTeam::class => ['viewAny', 'view', 'create', 'update', 'delete'],
        Subscriber::class => ['viewAny', 'view', 'create', 'update', 'delete'],
        Testimonial::class => ['viewAny', 'view', 'create', 'update', 'delete'],
        User::class => ['viewAny', 'view', 'create', 'update', 'delete', 'impersonate'],
        Role::class => ['viewAny', 'view', 'create', 'update', 'delete'],
        Permission::class => ['viewAny', 'view'],
    ];

    /** Which group each model's permissions are filed under in the admin. */
    protected array $groups = [
        ResearchIdea::class => 'Research',
        ResearchProposal::class => 'Research',
        ResearchManuscript::class => 'Research',
        ResearchReview::class => 'Research',
        ResearcherProfile::class => 'Research',
        ResearchFunding::class => 'Funding',
        FundingOpportunity::class => 'Funding',
        ResearchSupport::class => 'Support',
        Project::class => 'Projects',
        ConsultancyRequest::class => 'Inbox',
        ContactMessage::class => 'Inbox',
        CoreArea::class => 'Website content',
        Expert::class => 'People',
        Facility::class => 'Website content',
        Faq::class => 'Website content',
        IdeaSubmission::class => 'Innovation',
        Innovation::class => 'Innovation',
        InnovationArea::class => 'Innovation',
        Partner::class => 'Partners',
        Post::class => 'Website content',
        Publication::class => 'Research',
        Service::class => 'Website content',
        ServiceCategory::class => 'Website content',
        Setting::class => 'Website content',
        Stat::class => 'Website content',
        StudentTeam::class => 'Innovation',
        Subscriber::class => 'Website content',
        Testimonial::class => 'Website content',
        User::class => 'People and access',
        Role::class => 'People and access',
        Permission::class => 'People and access',
    ];

    public function run(): void
    {
        $this->syncPermissions();
        $this->roles();
        $this->assignExistingUsers();
    }

    /* ------------------------------------------------------------ the names */

    public function syncPermissions(): int
    {
        // The one that opens the door at all; everything else is about a model.
        Permission::updateOrCreate(
            ['name' => User::ACCESS_PANEL],
            ['group' => 'People and access', 'description' => 'Sign in to the admin dashboard'],
        );
        Permission::updateOrCreate(
            ['name' => ReportsAnalytics::ACCESS_PERMISSION],
            ['group' => 'Reports', 'description' => 'View reports and analytics'],
        );
        Permission::updateOrCreate(
            ['name' => ManageSiteSettings::ACCESS_PERMISSION],
            ['group' => 'People and access', 'description' => 'Manage site settings'],
        );

        foreach ($this->models as $model => $actions) {
            foreach ($actions as $action) {
                Permission::updateOrCreate(
                    ['name' => PolicyHelper::name($action, $model)],
                    [
                        'group' => $this->groups[$model] ?? 'Other',
                        'description' => $this->describe($action, $model),
                    ],
                );
            }
        }

        $this->command?->info('Permissions: '.Permission::count());

        return Permission::count();
    }

    protected function describe(string $action, string $model): string
    {
        $thing = str(class_basename($model))->headline()->lower();

        return match ($action) {
            'viewAny' => "List {$thing} records",
            'view' => "Open a {$thing} record",
            'create' => "Add a {$thing} record",
            'update' => "Change a {$thing} record",
            'delete' => "Remove a {$thing} record",
            'review' => "Assign, review and decide on a {$thing}",
            'setStatus' => "Set the official status of a {$thing}",
            'impersonate' => 'Sign in as another account to see what they see',
            default => str($action)->headline()->toString()." a {$thing}",
        };
    }

    /* ------------------------------------------------------------ the roles */

    protected function roles(): void
    {
        /* An administrator holds everything, including whatever is added later:
           the seeder re-syncs this role each time it runs. */
        $this->syncAdminRolePermissions();

        /* Someone who runs the research pipeline: reads and decides on ideas,
           proposals and papers, and records funding. No access to accounts. */
        $this->role('research_officer', 'Research Officer',
            'Reviews submissions and records decisions and funding.',
            array_merge(
                [User::ACCESS_PANEL],
                PolicyHelper::forModel(ResearchIdea::class, ['viewAny', 'view', 'review']),
                PolicyHelper::forModel(ResearchProposal::class, ['viewAny', 'view', 'update', 'review']),
                PolicyHelper::forModel(ResearchManuscript::class, ['viewAny', 'view', 'review']),
                PolicyHelper::forModel(ResearchFunding::class, ['viewAny', 'view', 'create', 'update']),
                PolicyHelper::forModel(FundingOpportunity::class),
                PolicyHelper::forModel(ResearchSupport::class, ['viewAny', 'view', 'update']),
                PolicyHelper::forModel(Project::class, ['viewAny', 'view', 'update', 'setStatus']),
                PolicyHelper::forModel(ResearchReview::class, ['viewAny', 'view', 'create']),
                PolicyHelper::forModel(ResearcherProfile::class, ['viewAny', 'view']),
            ));

        /* A reviewer reads and recommends, and sees nothing of the money. */
        $this->role('reviewer', 'Reviewer',
            'Reads submissions and records a review. No funding, no accounts.',
            array_merge(
                [User::ACCESS_PANEL],
                PolicyHelper::forModel(ResearchIdea::class, ['viewAny', 'view', 'review']),
                PolicyHelper::forModel(ResearchProposal::class, ['viewAny', 'view', 'review']),
                PolicyHelper::forModel(ResearchManuscript::class, ['viewAny', 'view', 'review']),
                PolicyHelper::forModel(ResearchReview::class, ['viewAny', 'view', 'create']),
                PolicyHelper::forModel(ResearcherProfile::class, ['viewAny', 'view']),
            ));

        /* The researcher's role carries no dashboard permissions at all: what a
           researcher may do is decided by ownership, in the policies. */
        $this->role('researcher', 'Researcher',
            'Uses the Researcher Portal. No access to the dashboard.', [], Role::PORTAL);
    }

    public function syncAdminRolePermissions(): int
    {
        $admin = Role::updateOrCreate(
            ['name' => Role::ADMIN],
            [
                'display_name' => 'Administrator',
                'description' => 'Full access to the dashboard.',
                'group' => Role::DASHBOARD,
            ],
        );

        $permissionIds = Permission::query()->pluck('id')->all();
        $admin->permissions()->sync($permissionIds);

        return count($permissionIds);
    }

    /**
     * Link legacy admin accounts to the admin role without replacing roles
     * that have already been explicitly assigned through the role manager.
     */
    public function assignLegacyAdminUsers(): int
    {
        $adminRoleId = Role::query()
            ->where('name', Role::ADMIN)
            ->value('id');

        if ($adminRoleId === null) {
            return 0;
        }

        return User::query()
            ->where('role', User::ADMIN)
            ->whereNull('role_id')
            ->update(['role_id' => $adminRoleId]);
    }

    /** @param  array<int, string>  $permissions */
    protected function role(string $name, string $display, string $description, array $permissions, string $group = Role::DASHBOARD): void
    {
        $role = Role::updateOrCreate(
            ['name' => $name],
            ['display_name' => $display, 'description' => $description, 'group' => $group],
        );

        $ids = Permission::whereIn('name', $permissions)->pluck('id')->all();
        $role->permissions()->sync($ids);

        $this->command?->info(sprintf('%-18s %d permissions', $display, count($ids)));
    }

    /* ------------------------------------------------------------- the users */

    /**
     * Give existing accounts the role matching the column they already carry.
     *
     * Only where none has been set: an account whose role was chosen in the
     * admin keeps it.
     */
    protected function assignExistingUsers(): void
    {
        $roles = Role::pluck('id', 'name');

        // Innovators are not staff or researchers; they keep no role row.
        $assigned = User::whereNull('role_id')->where('role', '!=', User::INNOVATOR)->get()->filter(function (User $user) use ($roles) {
            $name = $user->role === User::ADMIN ? 'admin' : 'researcher';

            if (! isset($roles[$name])) {
                return false;
            }

            $user->forceFill(['role_id' => $roles[$name]])->save();

            return true;
        });

        $this->command?->info("Users given a role: {$assigned->count()}");
    }
}
