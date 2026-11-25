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
            TextInput::make('title')->label('Title / designation (English)')->required()->maxLength(180),
            TextInput::make('title_bn')->label('Title / designation (বাংলা)')->maxLength(180),
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
                ->getUploadedFileUsing(function (FileUpload $component, string $file, ?Signature $record): ?array {
                    if ($record?->image !== $file) {
                        return null;
                    }

                    $upload = $component->getUploadedFile($file, null);
                    if ($upload !== null) {
                        $upload['url'] = route('admin.signatures.image', $record, absolute: false);
                    }

                    return $upload;
                })
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('key')
            ->columns([
                ImageColumn::make('image_preview')->label('Signature')->imageHeight(48)
                    ->defaultImageUrl(fn (Signature $record) => route('admin.signatures.image', $record, absolute: false)),
                TextColumn::make('key')->searchable()->sortable(),
                TextColumn::make('title')->label('Title / designation (English)')->searchable()->sortable(),
                TextColumn::make('title_bn')->label('Title / designation (বাংলা)')->searchable()->sortable(),
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
