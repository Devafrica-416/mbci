<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BreakdownPhotoResource\Pages;
use App\Filament\Resources\BreakdownPhotoResource\RelationManagers;
use App\Models\BreakdownPhoto;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\User;
use Filament\Facades\Filament;

class BreakdownPhotoResource extends Resource
{
    protected static ?string $model = BreakdownPhoto::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'Images';
    protected static ?int $navigationSort = 6;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('breakdown_id')
                    ->relationship('breakdown', 'id')
                    ->searchable()
                    ->required(),
                Forms\Components\FileUpload::make('chemin_fichier')
                    ->label('Photo')
                    ->directory('breakdown-photos')
                    ->required(),
                Forms\Components\TextInput::make('description'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('breakdown.id')->label('Panne'),
                Tables\Columns\ImageColumn::make('chemin_fichier')->label('Photo'),
                Tables\Columns\TextColumn::make('description')->limit(30),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->label('Créé le')->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBreakdownPhotos::route('/'),
            'create' => Pages\CreateBreakdownPhoto::route('/create'),
            'edit' => Pages\EditBreakdownPhoto::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        /** @var User|null $user */
        $user = Filament::auth()?->user();
        return $user?->can('manage breakdown photos') ?? false;
    }

    public static function canCreate(): bool
    {
        /** @var User|null $user */
        $user = Filament::auth()?->user();
        return $user?->can('manage breakdown photos') ?? false;
    }

    public static function canEdit($record): bool
    {
        /** @var User|null $user */
        $user = Filament::auth()?->user();
        return $user?->can('manage breakdown photos') ?? false;
    }

    public static function canDelete($record): bool
    {
        /** @var User|null $user */
        $user = Filament::auth()?->user();
        return $user?->can('manage breakdown photos') ?? false;
    }
}
