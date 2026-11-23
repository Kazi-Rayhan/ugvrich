<?php

namespace App\Filament\Resources\ConsultancyApplications;

use App\Filament\Resources\ConsultancyApplications\Pages\ListConsultancyApplications;
use App\Filament\Resources\ConsultancyApplications\Pages\ViewConsultancyApplication;
use App\Filament\Resources\ConsultancyApplications\Pages\ViewConsultancyChain;
use App\Models\ConsultancyApplication as Application;
use App\Services\ConsultancyReview;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ConsultancyApplicationResource extends Resource
{
    protected static ?string $model = Application::class;

    protected static string|\UnitEnum|null $navigationGroup = 'People & Partners';

    public static function canViewAny(): bool
    {
        $user = auth()->user();

        return $user?->can('viewAny', Application::class) ?? false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->recordUrl(fn (Application $record) => self::getUrl('view', ['record' => $record]))
            ->columns([
                TextColumn::make('id')->label('Reference')->sortable(),
                TextColumn::make('name')->searchable(),
                TextColumn::make('email')->searchable(),
                TextColumn::make('category.name'),
                TextColumn::make('expertiseServices.name')->label('Areas of expertise')->placeholder('—'),
                TextColumn::make('status')->badge()->color(fn (string $state) => match ($state) {
                    Application::APPROVED => 'success',
                    Application::DECLINED => 'danger',
                    Application::DIRECTOR => 'info',
                    default => 'warning',
                })->formatStateUsing(fn (string $state) => Application::STATUSES[$state]),
                TextColumn::make('signed_at')->dateTime(),
                TextColumn::make('created_at')->label('Submitted')->dateTime()->sortable(),
            ])
            ->filters([SelectFilter::make('status')->options(Application::STATUSES)])
            ->recordActions([
                Action::make('details')
                    ->label('View application')
                    ->icon('heroicon-o-arrow-up-right')
                    ->url(fn (Application $record) => self::getUrl('view', ['record' => $record])),
                self::decisionAction(true),
                self::decisionAction(false),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                DeleteBulkAction::make()->authorizeIndividualRecords(),
            ]);
    }

    public static function canView(Model $record): bool
    {
        return auth()->user()?->can('view', $record) ?? false;
    }

    public static function decisionAction(bool $approve): Action
    {
        return Action::make($approve ? 'approve' : 'decline')
            ->color($approve ? 'success' : 'danger')
            ->authorize(fn (Application $record) => auth()->user()->can('review', $record))
            ->schema(fn (Application $record) => [
                Textarea::make('note')->label($approve ? 'Internal note' : 'Reason shared with the applicant')
                    ->required(! $approve)->maxLength(1500),
            ])
            ->action(function (Application $record, array $data) use ($approve): void {
                app(ConsultancyReview::class)->decide($record, auth()->user(), $approve, $data['note'] ?? null);
                $record->refresh();
            })
            ->successNotificationTitle('Decision recorded');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListConsultancyApplications::route('/'),
            'chain' => ViewConsultancyChain::route('/chain-of-command'),
            'view' => ViewConsultancyApplication::route('/{record}'),
        ];
    }
}
