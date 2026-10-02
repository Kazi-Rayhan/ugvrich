<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\Role;
use App\Models\User;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\HtmlString;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Account')
                    ->description('Everyone listed here can sign in to the management dashboard.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')->required()->maxLength(190),
                        TextInput::make('email')->email()->required()->maxLength(190)->unique(ignoreRecord: true),

                        // Blank on edit keeps the current password.
                        TextInput::make('password')
                            ->password()
                            ->revealable()
                            ->minLength(8)
                            ->required(fn (string $operation) => $operation === 'create')
                            ->dehydrated(fn ($state) => filled($state))
                            ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                            ->helperText(fn (string $operation) => $operation === 'edit' ? 'Leave blank to keep the current password.' : null),

                        Select::make('role')
                            ->label('Kind of account')
                            ->options([
                                User::ADMIN => 'Staff',
                                User::RESEARCHER => 'Researcher',
                            ])
                            ->default(User::ADMIN)
                            ->required()
                            ->native(false)
                            ->helperText('Staff work in this dashboard; researchers use the Researcher Portal.'),
                    ]),

                Section::make('Roles and permissions')
                    ->description('What this person may do. A role is a named set of permissions; the permissions themselves are listed under Settings → Permissions.')
                    ->columns(2)
                    ->schema([
                        Select::make('role_id')
                            ->label('Primary role')
                            ->relationship('primaryRole', 'display_name')
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->helperText('The one role this account mainly works under.'),

                        CheckboxList::make('roles')
                            ->label('Additional roles')
                            ->relationship('roles', 'display_name')
                            ->descriptions(fn () => Role::query()->pluck('description', 'id')->all())
                            ->columns(2)
                            ->columnSpanFull()
                            ->helperText('Anything extra on top of the primary role. What the person may do is the two added together.'),

                        /* What all that adds up to. Shown rather than chosen,
                           because permissions are granted through roles — one
                           account edited by hand would drift from every other. */
                        Text::make(function (?User $record) {
                            if (! $record) {
                                return new HtmlString('<span class="text-sm text-gray-500">Save the account first; its permissions will be listed here.</span>');
                            }

                            $record->forgetPermissions();
                            $names = $record->permissionNames();

                            if ($record->isAdmin() && $names->isEmpty()) {
                                return new HtmlString('<span class="text-sm text-gray-500">The Administrator role grants full dashboard access.</span>');
                            }

                            if ($names->isEmpty()) {
                                return new HtmlString('<span class="text-sm text-gray-500">No permissions yet — this account cannot open the dashboard.</span>');
                            }

                            $grouped = \App\Models\Permission::query()
                                ->whereIn('name', $names)
                                ->orderBy('group')
                                ->orderBy('name')
                                ->get()
                                ->groupBy(fn ($permission) => $permission->group ?? 'Other');

                            $html = '<div class="text-sm"><p class="mb-2 font-semibold">'.$names->count().' permissions in all</p>';

                            foreach ($grouped as $group => $permissions) {
                                $html .= '<p class="mt-2"><span class="font-medium">'.e($group).':</span> <span class="text-gray-500">'
                                    .e($permissions->pluck('name')->join(', '))
                                    .'</span></p>';
                            }

                            return new HtmlString($html.'</div>');
                        })->columnSpanFull(),
                    ]),
            ]);
    }
}
