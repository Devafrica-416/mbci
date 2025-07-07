<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NotificationResource\Pages;
use App\Filament\Resources\NotificationResource\RelationManagers;
use App\Models\Notification;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\User;
use Filament\Facades\Filament;

class NotificationResource extends Resource
{
    protected static ?string $model = Notification::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'Notifications';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->required(),
                Forms\Components\TextInput::make('titre')->required(),
                Forms\Components\Textarea::make('message')->required(),
                Forms\Components\Toggle::make('lu')->label('Lue ?'),
                Forms\Components\TextInput::make('type'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')->label('Utilisateur')->searchable(),
                Tables\Columns\TextColumn::make('titre')->searchable(),
                Tables\Columns\TextColumn::make('message')->limit(30),
                Tables\Columns\IconColumn::make('lu')->boolean()->label('Lue ?'),
                Tables\Columns\TextColumn::make('type'),
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
            'index' => Pages\ListNotifications::route('/'),
            'create' => Pages\CreateNotification::route('/create'),
            'edit' => Pages\EditNotification::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        /** @var User|null $user */
        $user = Filament::auth()?->user();
        return $user?->can('manage notifications') ?? false;
    }

    public static function canCreate(): bool
    {
        /** @var User|null $user */
        $user = Filament::auth()?->user();
        return $user?->can('manage notifications') ?? false;
    }

    public static function canEdit($record): bool
    {
        /** @var User|null $user */
        $user = Filament::auth()?->user();
        return $user?->can('manage notifications') ?? false;
    }

    public static function canDelete($record): bool
    {
        /** @var User|null $user */
        $user = Filament::auth()?->user();
        return $user?->can('manage notifications') ?? false;
    }
}
