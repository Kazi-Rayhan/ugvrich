<?php

namespace App\Filament\Resources\Users\Tables;

use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Password;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('name')
            ->columns([
                TextColumn::make('name')->searchable()->weight('semibold'),
                TextColumn::make('email')->searchable()->copyable(),
                TextColumn::make('created_at')->label('Added')->since()->sortable(),
            ])
            ->recordActions([
                Action::make('login_as')
                    ->label('Login as')
                    ->icon('heroicon-m-arrow-right-on-rectangle')
                    ->color('warning')
                    ->url(fn (User $record): string => route('impersonate.start', $record))
                    ->visible(fn (User $record): bool => auth()->user()?->can('impersonate', $record) ?? false),
                EditAction::make(),
                Action::make('sendPasswordLink')
                    ->label('Send password link')
                    ->icon('heroicon-o-envelope')
                    ->authorize('update')
                    ->requiresConfirmation()
                    ->modalDescription(fn (User $record) => "Email a secure password setup link to {$record->email}.")
                    ->action(function (User $record): void {
                        try {
                            $status = Password::sendResetLink(['email' => $record->email]);
                        } catch (TransportExceptionInterface $exception) {
                            report($exception);
                            Notification::make()->title('Password link could not be emailed')->danger()->send();

                            return;
                        }

                        if ($status === Password::RESET_LINK_SENT) {
                            Notification::make()->title('Password link emailed')->success()->send();

                            return;
                        }

                        Notification::make()->title(__($status))->warning()->send();
                    }),
                // Nobody can delete their own account from here and lock themselves out.
                DeleteAction::make()->hidden(fn ($record) => $record->is(auth()->user())),
            ]);
    }
}
