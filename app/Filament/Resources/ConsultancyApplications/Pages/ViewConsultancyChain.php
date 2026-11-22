<?php

namespace App\Filament\Resources\ConsultancyApplications\Pages;

use App\Filament\Resources\ConsultancyApplications\ConsultancyApplicationResource;
use App\Models\ConsultancyApplication;
use App\Models\ConsultancyReviewStage;
use App\Models\User;
use App\Services\ConsultancyReview;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ViewConsultancyChain extends Page
{
    protected static string $resource = ConsultancyApplicationResource::class;

    protected static ?string $title = 'Chain of command';

    protected string $view = 'consultancy.chain';

    protected function getViewData(): array
    {
        $counts = ConsultancyApplication::query()
            ->whereIn('status', array_keys(ConsultancyApplication::REVIEW_STAGES))
            ->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

        $stages = [];
        foreach (ConsultancyApplication::REVIEW_STAGES as $status => $label) {
            $stages[] = [
                'key' => $status,
                'label' => $label,
                'action' => $status === ConsultancyApplication::WING ? 'addWingUser' : 'addDirectorUser',
                'reviewers' => ConsultancyReview::reviewers($status),
                'pending' => (int) ($counts[$status] ?? 0),
            ];
        }

        return ['stages' => $stages];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('applications')->label('Applications')->color('gray')->icon('heroicon-o-inbox')
                ->url(ConsultancyApplicationResource::getUrl()),
        ];
    }

    public function addWingUserAction(): Action
    {
        return $this->addUserAction('addWingUser', ConsultancyApplication::WING);
    }

    public function addDirectorUserAction(): Action
    {
        return $this->addUserAction('addDirectorUser', ConsultancyApplication::DIRECTOR);
    }

    public function removeUserAction(): Action
    {
        return Action::make('removeUser')
            ->label('Remove')
            ->icon('heroicon-o-user-minus')
            ->color('danger')
            ->link()
            ->authorize(fn () => $this->canAssignReviewers())
            ->requiresConfirmation()
            ->modalHeading('Remove reviewer from this stage?')
            ->modalDescription('They will lose review access and future notifications for this stage.')
            ->modalSubmitActionLabel('Remove reviewer')
            ->action(function (array $arguments): void {
                abort_unless($this->canAssignReviewers(), 403);
                $assignment = validator($arguments, [
                    'stage' => ['required', Rule::in(array_keys(ConsultancyApplication::REVIEW_STAGES))],
                    'user' => ['required', 'integer', 'exists:users,id'],
                ])->validate();

                DB::transaction(function () use ($assignment): void {
                    $stage = ConsultancyReviewStage::where('key', $assignment['stage'])->lockForUpdate()->sole();
                    $user = User::findOrFail($assignment['user']);

                    $stage->users()->detach($user->id);
                });
            })
            ->successNotificationTitle('Reviewer removed');
    }

    private function addUserAction(string $name, string $stageKey): Action
    {
        return Action::make($name)
            ->label('Add user')
            ->icon('heroicon-o-user-plus')
            ->modalHeading('Add reviewers to '.ConsultancyApplication::REVIEW_STAGES[$stageKey])
            ->modalSubmitActionLabel('Assign selected users')
            ->authorize(fn () => $this->canAssignReviewers())
            ->schema([
                Select::make('user_ids')->label('Users')
                    ->placeholder('Select users')
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->options(fn (): array => $this->availableUsers($stageKey))
                    ->getSearchResultsUsing(fn (string $search): array => $this->availableUsers($stageKey, $search))
                    ->getOptionLabelsUsing(function (array $values): array {
                        abort_unless($this->canAssignReviewers(), 403);

                        return User::query()->whereKey($values)->get()
                            ->mapWithKeys(fn (User $user) => [$user->id => $user->name.' ('.$user->email.')'])
                            ->all();
                    })
                    ->required(),
            ])
            ->action(function (array $data) use ($stageKey): void {
                abort_unless($this->canAssignReviewers(), 403);
                validator($data, [
                    'user_ids' => ['required', 'array', 'min:1'],
                    'user_ids.*' => ['required', 'integer', 'distinct', 'exists:users,id'],
                ])->validate();

                DB::transaction(function () use ($data, $stageKey): void {
                    $stage = ConsultancyReviewStage::where('key', $stageKey)->lockForUpdate()->sole();
                    $users = User::query()->whereKey($data['user_ids'])->get();
                    $stage->users()->syncWithoutDetaching($users->modelKeys());
                });
            })
            ->successNotificationTitle('Reviewers assigned');
    }

    private function availableUsers(string $stage, string $search = ''): array
    {
        abort_unless($this->canAssignReviewers(), 403);

        return User::query()
            ->whereDoesntHave('consultancyReviewStages', fn ($stages) => $stages->where('key', $stage))
            ->when($search !== '', fn ($query) => $query->where(
                fn ($users) => $users->whereLike('name', '%'.$search.'%')->orWhereLike('email', '%'.$search.'%'),
            ))
            ->orderBy('name')->orderBy('id')->limit(30)->get()
            ->mapWithKeys(fn (User $user) => [$user->id => $user->name.' ('.$user->email.')'])
            ->all();
    }

    private function canAssignReviewers(): bool
    {
        return auth()->user()->can('manageReviewers', ConsultancyApplication::class);
    }
}
