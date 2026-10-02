<?php

namespace App\Filament\Support;

use App\Models\Project;
use App\Models\ResearchManuscript;
use App\Notifications\ResearchEvent;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use App\Models\ResearchProposal;
use App\Models\ResearchReview;
use App\Models\User;
use App\Support\ResearchWorkflow;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;

/**
 * The three things a reviewer does, built once and shared by the ideas and
 * proposals screens so both behave identically.
 *
 * A review is never an edit. The decision is recorded as its own row and the
 * status it leads to is worked out by ResearchWorkflow, not chosen here — so a
 * reviewer cannot move a submission somewhere the workflow does not allow.
 */
class ResearchReviewActions
{
    /** Put a Research Wing member against a submission. */
    public static function assign(): Action
    {
        return Action::make('assign')
            ->authorize('review')
            ->label('Assign reviewer')
            ->icon('heroicon-o-user-plus')
            ->color('gray')
            ->schema([
                Select::make('assigned_reviewer_id')
                    ->label('Reviewer')
                    ->options(fn () => ResearchWorkflow::reviewers())
                    ->searchable()
                    ->native(false)
                    ->helperText('Staff accounts only. Leave empty to unassign.'),
            ])
            ->fillForm(fn (Model $record) => ['assigned_reviewer_id' => $record->assigned_reviewer_id])
            ->action(function (Model $record, array $data) {
                ResearchWorkflow::assign($record, $data['assigned_reviewer_id'] ?: null);

                Notification::make()->title('Reviewer updated')->success()->send();
            });
    }

    /** Say that somebody has picked it up. Writes no decision. */
    public static function startReview(): Action
    {
        return Action::make('startReview')
            ->authorize('review')
            ->label('Mark under review')
            ->icon('heroicon-o-eye')
            ->color('info')
            ->requiresConfirmation()
            ->visible(fn (Model $record) => in_array($record->status, ['submitted', 'new'], true))
            ->action(function (Model $record) {
                ResearchWorkflow::startReview($record);

                Notification::make()->title('Marked as under review')->success()->send();
            });
    }

    /** Record a decision, and move the submission with it. */
    public static function review(): Action
    {
        return Action::make('review')
            ->authorize('review')
            ->label('Record review')
            ->icon('heroicon-o-clipboard-document-check')
            ->color('primary')
            ->schema([
                Select::make('decision')
                    ->options(ResearchReview::decisions())
                    ->required()
                    ->native(false)
                    ->live()
                    ->helperText('The status follows from the decision; it is not chosen separately.'),

                Select::make('stage')
                    ->label('Review stage')
                    ->options([
                        'initial' => 'Initial review',
                        'methodological' => 'Methodological review',
                        'ethics' => 'Ethics review',
                        'wing' => 'Research Wing',
                    ])
                    ->native(false)
                    ->helperText('Optional. Lets several reviewers be told apart in the history.'),

                Textarea::make('comment')
                    ->rows(5)
                    ->required()
                    ->helperText('The researcher sees this.'),

                Textarea::make('recommendation')
                    ->rows(3)
                    ->helperText('Optional. Shown to the researcher as guidance.'),

                FileUpload::make('document')
                    ->label('Attachment')
                    ->disk('public')
                    ->directory('research-reviews'),
            ])
            ->action(function (Model $record, array $data) {
                /** @var User $reviewer */
                $reviewer = auth()->user();

                $review = ResearchWorkflow::review($record, $reviewer, $data);

                Notification::make()
                    ->title('Review recorded')
                    ->body('Status moved to '.($record->fresh()->status))
                    ->success()
                    ->send();

                return $review;
            });
    }

    /**
     * Turn an approved proposal into a research project.
     *
     * The work is ResearchWorkflow::createProject()'s, not this button's, so
     * there is one definition of what a project inherits. It is idempotent: if
     * a project already exists the action says so and creates nothing.
     */
    public static function createProject(): Action
    {
        return Action::make('createProject')
            ->authorize('review')
            ->label('Create project')
            ->icon('heroicon-o-briefcase')
            ->color('success')
            ->requiresConfirmation()
            ->modalDescription('The project inherits this proposal and starts internal — it will not appear on the public site until somebody publishes it.')
            ->visible(fn (Model $record) => $record instanceof ResearchProposal && $record->isApproved())
            ->action(function (Model $record) {
                $existed = $record->project !== null;

                $project = ResearchWorkflow::createProject($record);

                Notification::make()
                    ->title($existed ? 'This proposal already has a project' : 'Project created')
                    ->body($project->title)
                    ->success()
                    ->send();
            });
    }

    /** @return array<int, Action> */
    public static function all(): array
    {
        return [static::startReview(), static::review(), static::assign()];
    }

    /** The review actions plus the ones that belong only to proposals. */
    public static function forProposals(): array
    {
        return [...static::all(), static::createProject()];
    }

    /**
     * Publication, and whether the paper belongs in the public repository.
     *
     * Two separate decisions on purpose: a paper can be published in a journal
     * without the wing choosing to list it, and listing it is what makes it
     * visible to the world.
     */
    public static function publish(): Action
    {
        return Action::make('publish')
            ->authorize('review')
            ->label('Publication details')
            ->icon('heroicon-o-globe-alt')
            ->color('gray')
            ->schema([
                Select::make('status')
                    ->label('Status')
                    ->options(ResearchManuscript::statuses())
                    ->required()
                    ->native(false),

                TextInput::make('doi')->label('DOI'),
                DatePicker::make('published_on')->label('Publication date'),
                TextInput::make('volume'),
                TextInput::make('issue'),
                TextInput::make('pages'),
                TextInput::make('publication_url')->label('Publication URL')->url(),

                Toggle::make('is_public')
                    ->label('Show in the public research repository')
                    ->helperText('Only published papers are listed, and only when this is on. Being published does not list it by itself.'),
            ])
            ->fillForm(fn (ResearchManuscript $record) => $record->only([
                'status', 'doi', 'published_on', 'volume', 'issue', 'pages', 'publication_url', 'is_public',
            ]))
            ->action(function (ResearchManuscript $record, array $data) {
                $was = $record->status;

                $record->forceFill($data)->save();

                // Tell the researcher only when the status actually moved.
                if ($was !== $record->status && $record->user) {
                    $event = match ($record->status) {
                        ResearchManuscript::PUBLISHED => 'manuscript.published',
                        ResearchManuscript::ACCEPTED => 'manuscript.accepted',
                        default => null,
                    };

                    if ($event) {
                        $record->user->notify(new ResearchEvent(
                            $event,
                            $record->title,
                            route('researcher.manuscripts.show', $record),
                        ));
                    }
                }

                Notification::make()->title('Publication details saved')->success()->send();
            });
    }

    /** @return array<int, Action> */
    public static function forManuscripts(): array
    {
        return [...static::all(), static::publish()];
    }

    /**
     * Move a project to a new official status.
     *
     * The official status belongs to the Research Wing: there is no route in
     * the Researcher Portal that reaches this. The change is written into the
     * project's progress history and the researcher is told, so it never
     * happens quietly.
     */
    public static function setProjectStatus(): Action
    {
        return Action::make('setStatus')
            ->authorize('setStatus')
            ->label('Change status')
            ->icon('heroicon-o-flag')
            ->color('primary')
            ->schema([
                Select::make('status')
                    ->label('Official status')
                    ->options(Project::researchStatuses())
                    ->required()
                    ->native(false),

                Textarea::make('comment')
                    ->rows(3)
                    ->helperText('Recorded in the project history and shown to the researcher.'),
            ])
            ->fillForm(fn (Project $record) => ['status' => $record->status])
            ->action(function (Project $record, array $data) {
                ResearchWorkflow::setProjectStatus($record, $data['status'], auth()->user(), $data['comment'] ?? null);

                Notification::make()->title('Project status updated')->success()->send();
            });
    }
}
