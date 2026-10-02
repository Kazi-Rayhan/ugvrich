<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Dashboard;
use App\Filament\Pages\ManageSiteSettings;
use App\Filament\Pages\ReportsAnalytics;
use App\Filament\Resources\ConsultancyRequests\ConsultancyRequestResource;
use App\Filament\Resources\ContactMessages\ContactMessageResource;
use App\Filament\Resources\CoreAreas\CoreAreaResource;
use App\Filament\Resources\Experts\ExpertResource;
use App\Filament\Resources\Facilities\FacilityResource;
use App\Filament\Resources\Faqs\FaqResource;
use App\Filament\Resources\IdeaSubmissions\IdeaSubmissionResource;
use App\Filament\Resources\Innovations\InnovationResource;
use App\Filament\Resources\InnovationAreas\InnovationAreaResource;
use App\Filament\Resources\Partners\PartnerResource;
use App\Filament\Resources\FundingOpportunities\FundingOpportunityResource;
use App\Filament\Resources\ResearchFundings\ResearchFundingResource;
use App\Models\ResearchFunding;
use App\Filament\Resources\ResearchManuscripts\ResearchManuscriptResource;
use App\Models\ResearchManuscript;
use App\Filament\Resources\ResearchIdeas\ResearchIdeaResource;
use App\Models\ResearchIdea;
use App\Filament\Resources\ResearchProposals\ResearchProposalResource;
use App\Filament\Resources\ResearchSupports\ResearchSupportResource;
use App\Models\ResearchProposal;
use App\Models\ResearchSupport;
use App\Filament\Resources\Posts\PostResource;
use App\Filament\Resources\Projects\ProjectResource;
use App\Filament\Resources\Publications\PublicationResource;
use App\Filament\Resources\ServiceCategories\ServiceCategoryResource;
use App\Filament\Resources\Services\ServiceResource;
use App\Filament\Resources\Settings\SettingResource;
use App\Filament\Resources\Stats\StatResource;
use App\Filament\Resources\StudentTeams\StudentTeamResource;
use App\Filament\Resources\Subscribers\SubscriberResource;
use App\Filament\Resources\Testimonials\TestimonialResource;
use App\Filament\Resources\Permissions\PermissionResource;
use App\Filament\Resources\Roles\RoleResource;
use App\Filament\Resources\Users\UserResource;
use App\Models\ConsultancyRequest;
use App\Models\ContactMessage;
use App\Models\IdeaSubmission;
use Filament\Navigation\NavigationBuilder;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use Filament\Support\Icons\Heroicon;

/**
 * The management dashboard's left menu, in the order the RICH office works.
 *
 * Several entries open the same resource pre-filtered — Research, Innovation
 * and Consultancy projects are all Projects, Funding is a kind of Publication,
 * Events are Posts — so each item decides for itself when it is the active one.
 */
class AdminNavigation
{
    public function __invoke(NavigationBuilder $builder): NavigationBuilder
    {
        return $builder->groups([
            NavigationGroup::make()->items([
                NavigationItem::make('Dashboard')
                    ->icon(Heroicon::OutlinedHome)
                    ->url(fn () => Dashboard::getUrl())
                    ->isActiveWhen(fn () => request()->routeIs(Dashboard::getRouteName())),

                $this->filtered('Research Projects', Heroicon::OutlinedBeaker, ProjectResource::class, 'type', 'research'),
                $this->filtered('Innovation Projects', Heroicon::OutlinedLightBulb, ProjectResource::class, 'type', 'innovation'),

                $this->resource('Idea Submission', Heroicon::OutlinedInboxArrowDown, IdeaSubmissionResource::class)
                    ->badge(fn () => ($n = IdeaSubmission::where('status', 'new')->count()) ? (string) $n : null, 'warning'),

                $this->resource('Researchers', Heroicon::OutlinedAcademicCap, ExpertResource::class),
                $this->resource('Students / Teams', Heroicon::OutlinedUserGroup, StudentTeamResource::class),
                $this->filtered('Publications', Heroicon::OutlinedDocumentText, PublicationResource::class, 'kind', null),
                $this->filtered('Patent & IP', Heroicon::OutlinedKey, ProjectResource::class, 'ip', 'protected'),
                $this->filtered('Prototype Management', Heroicon::OutlinedCog6Tooth, ProjectResource::class, 'stage', 'prototype'),
                $this->filtered('Startup & Incubation', Heroicon::OutlinedRocketLaunch, ProjectResource::class, 'commercialization_status', 'incubating'),
                $this->filtered('Consultancy Projects', Heroicon::OutlinedBriefcase, ProjectResource::class, 'type', 'consultancy'),
                $this->resource('Industry Partners', Heroicon::OutlinedBuildingOffice2, PartnerResource::class),
                $this->resource('Labs & Equipment', Heroicon::OutlinedWrenchScrewdriver, FacilityResource::class),
                $this->filtered('Funding & Grants', Heroicon::OutlinedBanknotes, PublicationResource::class, 'kind', 'funded-project'),
                $this->filtered('Events & Training', Heroicon::OutlinedCalendarDays, PostResource::class, 'type', 'event'),

                NavigationItem::make('Reports & Analytics')
                    ->icon(Heroicon::OutlinedChartBar)
                    ->url(fn () => ReportsAnalytics::getUrl())
                    ->visible(fn () => ReportsAnalytics::canAccess())
                    ->isActiveWhen(fn () => request()->routeIs(ReportsAnalytics::getRouteName())),
            ]),

            NavigationGroup::make('Website Content')
                ->icon(Heroicon::OutlinedGlobeAlt)
                ->collapsible()
                ->collapsed()
                ->items([
                    $this->filtered('News', null, PostResource::class, 'type', 'news'),
                    $this->resource('Innovations', null, InnovationResource::class),
                    $this->resource('Innovation Areas', null, InnovationAreaResource::class),
                    $this->resource('Service Categories', null, ServiceCategoryResource::class),
                    $this->resource('Services', null, ServiceResource::class),
                    $this->resource('Core Areas', null, CoreAreaResource::class),
                    $this->resource('Testimonials', null, TestimonialResource::class),
                    $this->resource('FAQs', null, FaqResource::class),
                    $this->resource('Homepage Figures', null, StatResource::class),
                ]),

            NavigationGroup::make('Users & Permissions')->items([
                $this->resource('Users', Heroicon::OutlinedUsers, UserResource::class),
                $this->resource('Roles', Heroicon::OutlinedShieldCheck, RoleResource::class),
                $this->resource('Permissions', Heroicon::OutlinedKey, PermissionResource::class),
                NavigationItem::make('Settings')
                    ->icon(Heroicon::OutlinedCog8Tooth)
                    ->url(fn () => ManageSiteSettings::getUrl())
                    ->visible(fn () => ManageSiteSettings::canAccess())
                    ->isActiveWhen(fn () => request()->routeIs(ManageSiteSettings::getRouteName())
                        || request()->routeIs(SettingResource::getRouteBaseName().'.*')),
            ]),

            // Submissions from the public site that are not ideas.
            NavigationGroup::make('Inbox')
                ->icon(Heroicon::OutlinedInbox)
                ->collapsible()
                ->items([
                    $this->resource('Consultancy Requests', null, ConsultancyRequestResource::class)
                        ->badge(fn () => ($n = ConsultancyRequest::where('status', 'new')->count()) ? (string) $n : null, 'warning'),
                    $this->resource('Contact Messages', null, ContactMessageResource::class)
                        ->badge(fn () => ($n = ContactMessage::where('status', 'new')->count()) ? (string) $n : null, 'warning'),
                    $this->resource('Research Ideas', null, ResearchIdeaResource::class)
                        ->badge(fn () => ($n = ResearchIdea::whereIn('status', ['submitted', 'under_review'])->count()) ? (string) $n : null, 'warning'),
                    $this->resource('Manuscripts', null, ResearchManuscriptResource::class)
                        ->badge(fn () => ($n = ResearchManuscript::whereIn('status', ['submitted', 'internal_review'])->count()) ? (string) $n : null, 'warning'),
                    $this->resource('Research Support', null, ResearchSupportResource::class)
                        ->badge(fn () => ($n = ResearchSupport::where('status', 'new')->count()) ? (string) $n : null, 'warning'),
                    $this->resource('Research Proposals', null, ResearchProposalResource::class)
                        ->badge(fn () => ($n = ResearchProposal::whereIn('status', ['submitted', 'under_review'])->count()) ? (string) $n : null, 'warning'),
                    $this->resource('Funding Decisions', null, ResearchFundingResource::class)
                        ->badge(fn () => ($n = ResearchFunding::where('status', 'pending')->count()) ? (string) $n : null, 'warning'),
                    $this->resource('Funding Opportunities', null, FundingOpportunityResource::class),
                    $this->resource('Subscribers', null, SubscriberResource::class),
                ]),
        ]);
    }

    /** A plain link to a resource's list, active anywhere inside that resource. */
    protected function resource(string $label, $icon, string $resource): NavigationItem
    {
        return NavigationItem::make($label)
            ->icon($icon)
            ->url(fn () => $resource::getUrl('index'))
            ->visible(fn () => $resource::canViewAny())
            ->isActiveWhen(fn () => request()->routeIs($resource::getRouteBaseName().'.*'));
    }

    /**
     * A link to a resource's list with one filter applied. It is active only
     * while that filter is the one in the address, so sibling items that open
     * the same resource do not all light up together.
     */
    protected function filtered(string $label, $icon, string $resource, string $filter, ?string $value): NavigationItem
    {
        return NavigationItem::make($label)
            ->icon($icon)
            ->url(fn () => $resource::getUrl('index', $value === null ? [] : ['filters' => [$filter => ['value' => $value]]]))
            ->visible(fn () => $resource::canViewAny())
            ->isActiveWhen(fn () => request()->routeIs($resource::getRouteBaseName().'.*')
                && request()->input("filters.{$filter}.value") === $value);
    }
}
