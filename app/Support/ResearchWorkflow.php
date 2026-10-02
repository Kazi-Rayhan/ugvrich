<?php

namespace App\Support;

use App\Models\Project;
use App\Models\ResearchFunding;
use App\Models\ResearchIdea;
use App\Models\ResearchManuscript;
use App\Models\ResearchProposal;
use App\Models\ResearchReview;
use App\Models\User;
use App\Notifications\ResearchEvent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Recording a review, and the status change it causes.
 *
 * One place, so an idea and a proposal move the same way and the audit trail is
 * written the same way for both. A review is never an edit: each decision adds
 * a row carrying who decided, what they decided, and the statuses it moved
 * between, so the sequence stays readable afterwards.
 *
 * The status a decision leads to is decided here rather than chosen by the
 * reviewer, so the workflow cannot be stepped around from the admin screen.
 */
class ResearchWorkflow
{
    /**
     * Where each decision takes a record.
     *
     * A recommendation is not an approval: it moves an idea on to proposal
     * development, which is the point of recommending one, and leaves a
     * proposal approved, since there is nothing further to recommend it to yet.
     */
    public static function nextStatus(Model $record, string $decision): string
    {
        $isIdea = $record instanceof ResearchIdea;

        if ($record instanceof ResearchManuscript) {
            return match ($decision) {
                ResearchReview::APPROVE, ResearchReview::RECOMMEND => ResearchManuscript::APPROVED,
                ResearchReview::REVISION => ResearchManuscript::REVISION,
                ResearchReview::REJECT => ResearchManuscript::REJECTED,
                default => $record->status,
            };
        }

        return match ($decision) {
            ResearchReview::APPROVE => $isIdea ? ResearchIdea::APPROVED : ResearchProposal::APPROVED,
            ResearchReview::REVISION => $isIdea ? ResearchIdea::REVISION : ResearchProposal::REVISION,
            ResearchReview::REJECT => $isIdea ? ResearchIdea::REJECTED : ResearchProposal::REJECTED,
            ResearchReview::RECOMMEND => $isIdea ? ResearchIdea::PROPOSAL : ResearchProposal::APPROVED,
            default => $record->status,
        };
    }

    /**
     * Write one reviewer's decision and move the record with it.
     *
     * @param  array{decision: string, comment?: ?string, recommendation?: ?string, stage?: ?string, document?: ?string}  $data
     */
    public static function review(Model $record, User $reviewer, array $data): ResearchReview
    {
        $from = $record->status;
        $to = static::nextStatus($record, $data['decision']);

        return DB::transaction(function () use ($record, $reviewer, $data, $from, $to) {
            /** @var ResearchReview $review */
            $review = $record->reviews()->create([
                'reviewer_id' => $reviewer->id,
                'stage' => $data['stage'] ?? null,
                'decision' => $data['decision'],
                'comment' => $data['comment'] ?? null,
                'recommendation' => $data['recommendation'] ?? null,
                'document' => $data['document'] ?? null,
                'status_from' => $from,
                'status_to' => $to,
            ]);

            $record->forceFill([
                'status' => $to,
                'decided_at' => now(),
            ])->save();

            static::tell($record, $to, $data['comment'] ?? null);

            return $review;
        });
    }

    /**
     * Let the researcher know where their work has got to.
     *
     * Database always, email only for the decisions worth one — the choice is
     * the notification's, not this method's. A record with no owner (a proposal
     * from the public form) has nobody to tell, and is skipped.
     */
    protected static function tell(Model $record, string $status, ?string $comment = null): void
    {
        $owner = $record->user ?? null;

        if (! $owner) {
            return;
        }

        [$event, $url] = match (true) {
            $record instanceof ResearchIdea => [
                'idea.'.static::eventSuffix($status),
                route('researcher.ideas.show', $record),
            ],
            $record instanceof ResearchProposal => [
                'proposal.'.static::eventSuffix($status),
                route('researcher.proposals.show', $record),
            ],
            $record instanceof ResearchManuscript => [
                'manuscript.'.static::eventSuffix($status),
                route('researcher.manuscripts.show', $record),
            ],
            default => [null, null],
        };

        if (! $event || ! trans()->has('research.events.'.$event)) {
            return;
        }

        $owner->notify(new ResearchEvent($event, $record->title, $url, $comment));
    }

    /** The part of the event key that follows the record type. */
    protected static function eventSuffix(string $status): string
    {
        return match ($status) {
            'approved' => 'approved',
            'revision_required' => 'revision',
            'rejected' => 'rejected',
            'proposal_development' => 'recommended',
            'accepted' => 'accepted',
            'published' => 'published',
            'internal_review', 'under_review' => 'review',
            default => $status,
        };
    }

    /**
     * Move a project to a new official status.
     *
     * Only ever called from the admin side: the official status is the
     * Research Wing's, and the researcher has no route that reaches this. The
     * change is written into the progress history rather than applied quietly,
     * so the project's story stays complete.
     */
    public static function setProjectStatus(Project $project, string $status, User $by, ?string $comment = null): void
    {
        $from = $project->status;

        if ($from === $status) {
            return;
        }

        DB::transaction(function () use ($project, $status, $by, $comment, $from) {
            $project->forceFill(['status' => $status])->save();

            $project->updates()->create([
                'user_id' => $by->id,
                'status' => $status,
                'comment' => $comment ?: 'Status changed from '.$from.' to '.$status.'.',
            ]);
        });

        $project->owner?->notify(new ResearchEvent(
            'project.status',
            $project->title,
            route('researcher.projects.show', $project),
            Project::researchStatuses()[$status] ?? $status,
        ));
    }

    /**
     * Mark a submission as being looked at.
     *
     * Separate from a decision because it is not one: it tells the researcher
     * their submission has been picked up, and it writes no review row.
     */
    public static function startReview(Model $record): void
    {
        $under = match (true) {
            $record instanceof ResearchIdea => ResearchIdea::UNDER_REVIEW,
            $record instanceof ResearchManuscript => ResearchManuscript::INTERNAL_REVIEW,
            default => ResearchProposal::UNDER_REVIEW,
        };

        if ($record->status === $under) {
            return;
        }

        $record->forceFill(['status' => $under])->save();
    }

    /** Put a Research Wing member against a submission. */
    public static function assign(Model $record, ?int $reviewerId): void
    {
        $record->forceFill(['assigned_reviewer_id' => $reviewerId])->save();
    }

    /**
     * Turn an approved proposal into a research project.
     *
     * The project inherits what the proposal already settled, so nothing is
     * retyped. It starts `internal`: a project appears on the public site only
     * when somebody decides it should, never by being created.
     *
     * Returns the existing project if there already is one — this is reachable
     * from a button, and a second press must not make a second project.
     */
    public static function createProject(ResearchProposal $proposal): Project
    {
        if ($proposal->project) {
            return $proposal->project;
        }

        return DB::transaction(function () use ($proposal) {
            $funding = $proposal->fundings()->where('status', '!=', ResearchFunding::UNFUNDED)->first();

            $project = Project::create([
                'research_proposal_id' => $proposal->id,
                'user_id' => $proposal->user_id,

                'title' => $proposal->title,
                'slug' => static::uniqueSlug($proposal->title),
                'type' => 'research',
                'department' => static::departmentCode($proposal->department),

                'summary' => $proposal->summary,
                'description' => $proposal->background,
                'research_summary' => $proposal->summary,
                'problem' => $proposal->research_gap,
                'solution' => $proposal->methodology,
                'outcome' => $proposal->expected_outcome,

                'lead_name' => $proposal->principal_investigator ?: $proposal->user?->name,
                'duration' => $proposal->duration,
                'budget' => $funding?->approved_amount,

                'status' => 'planning',
                'visibility' => 'internal',
                'progress' => 0,
                'year' => now()->year,
            ]);

            $project->updates()->create([
                'user_id' => auth()->id(),
                'progress' => 0,
                'status' => 'planning',
                'comment' => 'Project created from approved proposal '.$proposal->reference().'.',
            ]);

            $project->owner?->notify(new ResearchEvent(
                'project.created',
                $project->title,
                route('researcher.projects.show', $project),
            ));

            return $project;
        });
    }

    /** Record a funding decision against a proposal. */
    public static function fund(ResearchProposal $proposal, User $decidedBy, array $data): ResearchFunding
    {
        $funding = $proposal->fundings()->create([
            ...$data,
            'decided_by' => $decidedBy->id,
            'decided_at' => ($data['status'] ?? ResearchFunding::PENDING) === ResearchFunding::PENDING ? null : now(),
        ]);

        // A pending record is not yet a decision, so it is not announced.
        if ($funding->isDecided()) {
            $proposal->user?->notify(new ResearchEvent(
                'funding.decided',
                $proposal->title,
                route('researcher.funding.index'),
                ResearchFunding::statuses()[$funding->status] ?? $funding->status,
            ));
        }

        return $funding;
    }

    /** Titles repeat across years, so the slug is made unique rather than assumed. */
    protected static function uniqueSlug(string $title): string
    {
        $base = Str::slug(Str::limit($title, 180, ''));
        $slug = $base;
        $n = 2;

        while (Project::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$n++;
        }

        return $slug;
    }

    /**
     * The department code the projects table uses.
     *
     * Proposals carry the framework's own department names ("Civil
     * Engineering"); projects carry the short codes from config/rich.php.
     */
    protected static function departmentCode(?string $name): ?string
    {
        if (blank($name)) {
            return null;
        }

        foreach (config('rich.departments', []) as $code => $label) {
            if (str_contains(mb_strtolower($label), mb_strtolower($name)) || strcasecmp($code, $name) === 0) {
                return $code;
            }
        }

        return null;
    }

    /** Staff who can be assigned a review. */
    public static function reviewers(): array
    {
        return User::query()
            ->where('role', User::ADMIN)
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all();
    }
}
