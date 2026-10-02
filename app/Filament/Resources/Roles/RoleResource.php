<?php

namespace App\Filament\Resources\Roles;

use App\Filament\Resources\Roles\Pages\CreateRole;
use App\Filament\Resources\Roles\Pages\EditRole;
use App\Filament\Resources\Roles\Pages\ListRoles;
use App\Models\Permission;
use App\Models\Role;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;

/**
 * Roles: what somebody is allowed to do, named.
 *
 * A role is a bag of permissions, and a user carries one or more of them. The
 * permissions are generated from the policies by the seeder, so this screen is
 * about which of them a role holds rather than about inventing new ones.
 *
 * The permission checklist is split by group and each group is saved under its
 * own field, so an administrator sees "Research" and "Funding" rather than one
 * list of sixty names. {@see CreateRole} and {@see EditRole} put the pieces
 * back together.
 */
class RoleResource extends Resource
{
    protected static ?string $model = Role::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldCheck;

    protected static string|\UnitEnum|null $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'display_name';

    /** The form field one permission group is edited under. */
    public static function fieldFor(string $group): string
    {
        return 'group_'.Str::slug($group, '_');
    }

    /** @return \Illuminate\Support\Collection<string, \Illuminate\Support\Collection<int, Permission>> */
    public static function permissionGroups(): \Illuminate\Support\Collection
    {
        return Permission::query()
            ->where('name', '!=', \App\Models\User::ACCESS_PANEL)
            ->orderBy('group')
            ->orderBy('name')
            ->get()
            ->groupBy(fn (Permission $permission) => $permission->group ?? 'Other');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('The role')
                ->description('What this role is called, and where the people who carry it work.')
                ->columns(2)
                ->schema([
                    TextInput::make('display_name')
                        ->label('Name')
                        ->required()
                        ->maxLength(120)
                        ->placeholder('Research Officer')
                        ->live(onBlur: true)
                        // The key is written from the name, so nobody has to
                        // think about it; it stays editable all the same.
                        ->afterStateUpdated(function ($state, $set, $get, string $operation) {
                            if ($operation === 'create' && blank($get('name'))) {
                                $set('name', Str::snake(Str::lower($state ?? '')));
                            }
                        }),

                    TextInput::make('name')
                        ->label('Key')
                        ->required()
                        ->maxLength(120)
                        ->unique(ignoreRecord: true)
                        ->rule('regex:/^[a-z][a-z0-9_]*$/')
                        ->helperText('Used in code: lower case, words joined by underscores.')
                        ->disabled(fn (?Role $record) => $record?->isAdmin())
                        ->dehydrated(),

                    Radio::make('group')
                        ->label('Where this role works')
                        ->options(Role::groups())
                        ->descriptions([
                            Role::DASHBOARD => 'Signs in to this dashboard. The permission to open it is added automatically.',
                            Role::PORTAL => 'Uses the Researcher Portal instead, where what they may do follows from what they own.',
                        ])
                        ->default(Role::DASHBOARD)
                        ->required()
                        ->live()
                        ->columnSpanFull(),

                    Textarea::make('description')
                        ->rows(2)
                        ->columnSpanFull()
                        ->placeholder('Reviews submissions and records decisions and funding.')
                        ->helperText('One sentence, so the next person knows what this role is for.'),
                ]),

            // A portal role holds no dashboard permissions at all, so there is
            // nothing to show and saying so is clearer than an empty list.
            Section::make('Permissions')
                ->visible(fn ($get) => $get('group') === Role::PORTAL)
                ->schema([
                    Text::make('A Researcher Portal role carries no dashboard permissions. What somebody may do there follows from what they own: their own ideas, proposals, projects and papers.'),
                ]),

            Section::make('Permissions')
                ->description('What anybody with this role may do. Each group can be ticked as a whole.')
                ->visible(fn ($get) => $get('group') !== Role::PORTAL)
                ->schema(
                    static::permissionGroups()
                        ->map(fn ($permissions, string $group) => Section::make($group)
                            ->compact()
                            ->collapsible()
                            ->collapsed(fn (?Role $record) => $record !== null && ! $record->isAdmin())
                            ->schema([
                                CheckboxList::make(static::fieldFor($group))
                                    ->hiddenLabel()
                                    ->options($permissions->pluck('name', 'id')->map(
                                        fn (string $name) => Str::of($name)->replace('_', ' ')->ucfirst()->toString(),
                                    ))
                                    ->descriptions($permissions->pluck('description', 'id')->all())
                                    ->bulkToggleable()
                                    ->searchable()
                                    ->columns(2)
                                    // Put together by the page, not by the form.
                                    ->dehydrated(false)
                                    ->disabled(fn (?Role $record) => $record?->isAdmin()),
                            ]))
                        ->values()
                        ->all(),
                ),

            Section::make()
                ->visible(fn (?Role $record) => $record?->isAdmin())
                ->schema([
                    Text::make('The Administrator role is not edited here: the seeder gives it every permission there is, including any added later.'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('display_name')
            ->columns([
                TextColumn::make('display_name')
                    ->label('Role')
                    ->searchable()
                    ->weight('semibold')
                    ->description(fn (Role $record) => $record->description),

                TextColumn::make('group')
                    ->label('Works in')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => Role::groups()[$state] ?? Role::groups()[Role::DASHBOARD])
                    ->color(fn (?string $state) => $state === Role::PORTAL ? 'gray' : 'success'),

                TextColumn::make('name')->label('Key')->badge()->color('gray')->searchable()->toggleable(),

                TextColumn::make('permissions_count')
                    ->counts('permissions')
                    ->label('Permissions')
                    ->alignCenter()
                    ->badge()
                    ->color(fn (int $state) => $state > 0 ? 'success' : 'gray'),

                TextColumn::make('primary_users_count')
                    ->counts('primaryUsers')
                    ->label('People')
                    ->alignCenter(),
            ])
            ->filters([
                SelectFilter::make('group')->label('Works in')->options(Role::groups()),
            ])
            ->recordActions([
                EditAction::make(),
                // The Administrator role is what everything else is measured
                // against; removing it would leave nobody able to put it back.
                DeleteAction::make()->hidden(fn (Role $record) => $record->isAdmin()),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRoles::route('/'),
            'create' => CreateRole::route('/create'),
            'edit' => EditRole::route('/{record}/edit'),
        ];
    }
}
