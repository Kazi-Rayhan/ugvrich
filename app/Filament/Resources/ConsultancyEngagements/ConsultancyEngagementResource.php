<?php

namespace App\Filament\Resources\ConsultancyEngagements;

use App\Filament\Resources\ConsultancyApplications\ConsultancyApplicationResource;
use App\Filament\Resources\ConsultancyEngagements\Pages\ListConsultancyEngagements;
use App\Models\ConsultancyApplication as Application;
use App\Services\ConsultancyEngagement;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ConsultancyEngagementResource extends Resource
{
    protected static ?string $model = Application::class;

    protected static ?string $slug = 'consultancy-engagements';

    protected static ?string $modelLabel = 'Consultancy engagement';

    public static function canViewAny(): bool
    {
        $user = auth()->user();

        return $user?->can('viewEngagement', Application::class) ?? false;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('status', Application::APPROVED);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('id')->label(__('consultancy_engagement.reference'))->sortable(),
                TextColumn::make('name')->label(__('consultancy_engagement.expert'))->searchable(),
                TextColumn::make('category.name')->label(__('consultancy_engagement.category')),
                TextColumn::make('contract_type')->label(__('consultancy_engagement.engagement_type'))
                    ->badge()->placeholder(__('consultancy_engagement.awaiting'))
                    ->formatStateUsing(fn (string $state) => Application::contractLabel($state)),
                TextColumn::make('engagement_percentage')->label(__('consultancy_engagement.percentage'))->suffix('%')->placeholder(__('consultancy_engagement.not_set')),
                TextColumn::make('engagement_term_years')->label(__('consultancy_engagement.term_years'))
                    ->placeholder(__('consultancy_engagement.not_set'))->sortable(),
                TextColumn::make('engagementAssigner.name')->label(__('consultancy_engagement.assigned_by'))->placeholder('—'),
                TextColumn::make('engagement_assigned_at')->label(__('consultancy_engagement.assigned_at'))->dateTime(),
                TextColumn::make('signed_at')->label(__('consultancy_engagement.signed_at'))->dateTime(),
            ])
            ->recordActions([
                Action::make('application')->label(__('consultancy_engagement.view_application'))->color('gray')
                    ->visible(fn (Application $record) => auth()->user()->can('view', $record))
                    ->url(fn (Application $record) => ConsultancyApplicationResource::getUrl('view', ['record' => $record])),
                Action::make('assign')
                    ->label(__('consultancy_engagement.assign'))->icon('heroicon-o-briefcase')
                    ->modalHeading(fn (Application $record) => __('consultancy_engagement.assign_heading', ['name' => $record->name]))
                    ->modalDescription(__('consultancy_engagement.assign_hint'))
                    ->authorize(fn () => ConsultancyEngagement::canAssign(auth()->user()))
                    ->visible(fn (Application $record) => $record->contract_type === null && ! $record->signed_at)
                    ->schema([
                        Select::make('contract')->label(__('consultancy_engagement.engagement_type'))
                            ->options(['long_term' => Application::contractLabel('long_term')])->default('long_term')->required(),
                        TextInput::make('percentage')->label(__('consultancy_engagement.percentage'))->required()->numeric()
                            ->minValue(0)->maxValue(100)->step(0.01)->suffix('%')->rule('decimal:0,2'),
                        TextInput::make('term_years')->label(__('consultancy_engagement.term_years'))->required()->integer()
                            ->minValue(1)->maxValue(ConsultancyEngagement::MAX_TERM_YEARS),
                    ])
                    ->action(function (Application $record, array $data): void {
                        app(ConsultancyEngagement::class)->assign($record, auth()->user(), $data['contract'], $data['percentage'] ?? null, $data['term_years'] ?? null);
                        $record->refresh();
                    })
                    ->successNotificationTitle(__('consultancy_engagement.assigned')),
                ConsultancyApplicationResource::deleteAction(),
            ]);
    }

    public static function getPages(): array
    {
        return ['index' => ListConsultancyEngagements::route('/')];
    }
}
