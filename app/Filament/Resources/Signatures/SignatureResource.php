<?php

namespace App\Filament\Resources\Signatures;

use App\Filament\Resources\Signatures\Pages\ManageSignatures;
use App\Models\Signature;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Auth\Access\Response;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class SignatureResource extends Resource
{
    protected static ?string $model = Signature::class;

    protected static ?string $recordTitleAttribute = 'title';

    public static function getAuthorizationResponse(string | UnitEnum $action, ?Model $record = null): Response
    {
        return auth()->user()?->isAdmin() ? Response::allow() : Response::deny();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('key')->required()->maxLength(100)->rule('alpha_dash:ascii')->unique(ignoreRecord: true),
            TextInput::make('title')->label('Title / designation')->required()->maxLength(180),
            FileUpload::make('image')
                ->label('Signature image')
                ->required()
                ->image()
                ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/webp'])
                ->maxSize(3072)
                ->disk('local')
                ->directory('signatures')
                ->visibility('private')
                ->preventFilePathTampering()
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('key')
            ->columns([
                ImageColumn::make('image')->label('Signature')->disk('local')->visibility('private')->imageHeight(48),
                TextColumn::make('key')->searchable()->sortable(),
                TextColumn::make('title')->label('Title / designation')->searchable()->sortable(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageSignatures::route('/')];
    }
}
