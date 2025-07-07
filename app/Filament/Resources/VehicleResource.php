<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VehicleResource\Pages;
use App\Filament\Resources\VehicleResource\RelationManagers;
use App\Models\Vehicle;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Database\Eloquent\Model;
use Filament\Facades\Filament;
use App\Models\User;
use App\Filament\Resources\VehicleResource\RelationManagers\AssignmentsRelationManager;
use App\Filament\Resources\VehicleResource\RelationManagers\MaintenanceRelationManager;
use App\Filament\Resources\VehicleResource\RelationManagers\BreakdownRelationManager;
use App\Models\History;

class VehicleResource extends Resource
{
    protected static ?string $model = Vehicle::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'Véhicules';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('marque')->label('Marque')->required(),
                Forms\Components\TextInput::make('modele')->label('Modèle')->required(),
                Forms\Components\TextInput::make('immatriculation')->label('Immatriculation')->required()->unique(),
                Forms\Components\Select::make('statut')
                    ->label('Statut')
                    ->options([
                        'disponible' => 'Disponible',
                        'en_panne' => 'En panne',
                        'en_reparation' => 'En réparation',
                        'en_maintenance' => 'En maintenance',
                    ])->required(),
                Forms\Components\DatePicker::make('date_mise_en_service')->label('Date de mise en service'),
                Forms\Components\Select::make('garage_id')
                    ->label('Garage')
                    ->relationship('garage', 'nom')
                    ->searchable()
                    ->nullable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('marque')->label('Marque')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('modele')->label('Modèle')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('immatriculation')->label('Immatriculation')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('statut')->label('Statut')->sortable(),
                Tables\Columns\TextColumn::make('date_mise_en_service')->label('Date de mise en service')->date()->sortable(),
                Tables\Columns\TextColumn::make('garage.nom')->label('Garage')->sortable(),
                Tables\Columns\TextColumn::make('created_at')->label('Créé le')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('statut')
                    ->label('Statut')
                    ->options([
                        'disponible' => 'Disponible',
                        'en_panne' => 'En panne',
                        'en_reparation' => 'En réparation',
                        'en_maintenance' => 'En maintenance',
                    ]),
                Tables\Filters\SelectFilter::make('garage_id')
                    ->label('Garage')
                    ->relationship('garage', 'nom'),
                Tables\Filters\Filter::make('date_mise_en_service')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label('Du'),
                        Forms\Components\DatePicker::make('to')->label('Au'),
                    ])
                    ->query(function ($query, $data) {
                        return $query
                            ->when($data['from'], fn($q) => $q->where('date_mise_en_service', '>=', $data['from']))
                            ->when($data['to'], fn($q) => $q->where('date_mise_en_service', '<=', $data['to']));
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('changerStatutGarage')
                    ->label('Changer statut / Affecter garage')
                    ->icon('heroicon-o-adjustments-horizontal')
                    ->color('primary')
                    ->visible(fn($record) => (
                        ($user = Filament::auth()?->user()) &&
                        method_exists($user, 'getRoleNames') &&
                        collect($user->getRoleNames())->intersect(['manager', 'administrateur', 'gestionnaire'])->isNotEmpty()
                    ))
                    ->form([
                        Forms\Components\Select::make('statut')
                            ->label('Nouveau statut')
                            ->options([
                                'disponible' => 'Disponible',
                                'en_panne' => 'En panne',
                                'en_reparation' => 'En réparation',
                                'en_maintenance' => 'En maintenance',
                            ])->required(),
                        Forms\Components\Select::make('garage_id')
                            ->label('Garage')
                            ->relationship('garage', 'nom')
                            ->searchable()
                            ->nullable(),
                    ])
                    ->action(function ($record, $data) {
                        $record->update([
                            'statut' => $data['statut'],
                            'garage_id' => $data['garage_id'] ?? null,
                        ]);
                    }),
                Tables\Actions\Action::make('sortieReparation')
                    ->label('Sortie de réparation')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn($record) => (
                        ($user = Filament::auth()?->user()) &&
                        method_exists($user, 'getRoleNames') &&
                        collect($user->getRoleNames())->intersect(['manager', 'administrateur', 'gestionnaire'])->isNotEmpty() &&
                        $record->statut === 'en_reparation'
                    ))
                    ->action(function ($record) {
                        $record->update([
                            'statut' => 'disponible',
                            'garage_id' => null,
                        ]);
                        // Notifier le chauffeur/commercial/owner (notification DB)
                        // ... notification à implémenter ici ...
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('date_mise_en_service', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            AssignmentsRelationManager::class,
            MaintenanceRelationManager::class,
            BreakdownRelationManager::class,
            \App\Filament\Resources\VehicleResource\RelationManagers\HistoryRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVehicles::route('/'),
            'create' => Pages\CreateVehicle::route('/create'),
            'edit' => Pages\EditVehicle::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        /** @var User|null $user */
        $user = Filament::auth()?->user();
        return $user?->can('manage vehicles') ?? false;
    }

    public static function canCreate(): bool
    {
        /** @var User|null $user */
        $user = Filament::auth()?->user();
        return $user?->can('manage vehicles') ?? false;
    }

    public static function canEdit(Model $record): bool
    {
        /** @var User|null $user */
        $user = Filament::auth()?->user();
        return $user?->can('manage vehicles') ?? false;
    }

    public static function canDelete(Model $record): bool
    {
        /** @var User|null $user */
        $user = Filament::auth()?->user();
        return $user?->can('manage vehicles') ?? false;
    }
}
