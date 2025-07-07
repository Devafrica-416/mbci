<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GarageResource\Pages;
use App\Filament\Resources\GarageResource\RelationManagers;
use App\Models\Garage;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class GarageResource extends Resource
{
    protected static ?string $model = Garage::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'Garages';

    protected static ?int $navigationSort = 5 ;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nom')->label('Nom')->required(),
                Forms\Components\TextInput::make('adresse')->label('Adresse')->required(),
                Forms\Components\TextInput::make('telephone')->label('Téléphone')->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nom')->label('Nom')->searchable(),
                Tables\Columns\TextColumn::make('adresse')->label('Adresse')->searchable(),
                Tables\Columns\TextColumn::make('telephone')->label('Téléphone')->searchable(),
                Tables\Columns\TextColumn::make('created_at')->label('Créé le')->dateTime()->sortable(),
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
            'index' => Pages\ListGarages::route('/'),
            'create' => Pages\CreateGarage::route('/create'),
            'edit' => Pages\EditGarage::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        /** @var User|null $user */
        $user = Filament::auth()?->user();
        return $user?->can('manage garages') ?? false;
    }

    public static function canCreate(): bool
    {
        /** @var User|null $user */
        $user = Filament::auth()?->user();
        return $user?->can('manage garages') ?? false;
    }

    public static function canEdit($record): bool
    {
        /** @var User|null $user */
        $user = Filament::auth()?->user();
        return $user?->can('manage garages') ?? false;
    }

    public static function canDelete($record): bool
    {
        /** @var User|null $user */
        $user = Filament::auth()?->user();
        return $user?->can('manage garages') ?? false;
    }
}
