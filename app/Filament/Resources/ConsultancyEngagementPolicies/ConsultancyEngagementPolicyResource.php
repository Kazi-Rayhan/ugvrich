<?php

namespace App\Filament\Resources\ConsultancyEngagementPolicies;

use App\Filament\Resources\ConsultancyEngagementPolicies\Pages\ListConsultancyEngagementPolicies;
use App\Models\ConsultancyApplication;
use App\Models\ConsultancyEngagementPolicy;
use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ConsultancyEngagementPolicyResource extends Resource
{
    protected static ?string $model = ConsultancyEngagementPolicy::class;

    public static function canViewAny(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('contract_type')->label(__('consultancy_engagement.engagement_type'))
                    ->formatStateUsing(fn (string $state) => ConsultancyApplication::contractLabel($state)),
                TextColumn::make('version')->label(__('consultancy_engagement.version'))->badge(),
                TextColumn::make('updated_at')->label(__('consultancy_engagement.updated'))->dateTime(),
            ])
            ->recordActions([
                Action::make('editPolicy')->label(__('consultancy_engagement.edit_policies'))->icon('heroicon-o-pencil-square')
                    ->authorize(fn () => self::canViewAny())
                    ->modalHeading(fn (ConsultancyEngagementPolicy $record) => ConsultancyApplication::contractLabel($record->contract_type))
                    ->fillForm(fn (ConsultancyEngagementPolicy $record) => [
                        ...$record->only(ConsultancyEngagementPolicy::CONTENT_FIELDS),
                        'sections' => $record->sections ?? [],
                    ])
                    ->schema([
                        Section::make(__('consultancy_engagement.optional_notices'))->schema([
                            Textarea::make('privacy_notice')->label(__('consultancy_engagement.privacy').' (English)')->maxLength(12000)->rows(4),
                            Textarea::make('privacy_notice_bn')->label(__('consultancy_engagement.privacy').' (বাংলা)')->maxLength(12000)->rows(4),
                            Textarea::make('participation_policy')->label(__('consultancy_engagement.participation').' (English)')->maxLength(12000)->rows(4),
                            Textarea::make('participation_policy_bn')->label(__('consultancy_engagement.participation').' (বাংলা)')->maxLength(12000)->rows(4),
                        ])->columns(2)->collapsed(),
                        Repeater::make('sections')->label(__('consultancy_engagement.sections'))
                            ->schema([
                                TextInput::make('title')->label(__('consultancy_engagement.title').' (English)')->required()->maxLength(150),
                                TextInput::make('title_bn')->label(__('consultancy_engagement.title').' (বাংলা)')->required()->maxLength(150),
                                Textarea::make('description')->label(__('consultancy_engagement.description').' (English)')->required()->maxLength(6000)->rows(4),
                                Textarea::make('description_bn')->label(__('consultancy_engagement.description').' (বাংলা)')->required()->maxLength(6000)->rows(4),
                            ])
                            ->defaultItems(0)->maxItems(20)
                            ->addActionLabel(__('consultancy_engagement.add_section'))
                            ->itemLabel(fn (array $state): ?string => ConsultancyApplication::localizedTerm($state, 'title'))
                            ->collapsible(),
                    ])
                    ->action(function (ConsultancyEngagementPolicy $record, array $data): void {
                        abort_unless(self::canViewAny(), 403);
                        $record->update($data);
                    })
                    ->successNotificationTitle(__('consultancy_engagement.policies_saved')),
            ]);
    }

    public static function getPages(): array
    {
        return ['index' => ListConsultancyEngagementPolicies::route('/')];
    }
}
