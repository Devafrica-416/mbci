<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MaintenanceResource\Pages;
use App\Filament\Resources\MaintenanceResource\RelationManagers;
use App\Models\Maintenance;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\User;
use Filament\Facades\Filament;

class MaintenanceResource extends Resource
{
    protected static ?string $model = Maintenance::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'Maintenances';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('vehicle_id')
                    ->label('Véhicule')
                    ->relationship('vehicle', 'immatriculation')
                    ->searchable()
                    ->required(),
                Forms\Components\Select::make('garage_id')
                    ->label('Garage')
                    ->relationship('garage', 'nom')
                    ->searchable()
                    ->required(),
                Forms\Components\Select::make('type')
                    ->label('Type de maintenance')
                    ->options([
                        'preventive' => 'Préventive',
                        'curative' => 'Curative',
                        'visite_technique' => 'Visite technique',
                    ])->required(),
                Forms\Components\TextInput::make('cout')->label('Coût')->numeric()->prefix('FCFA'),
                Forms\Components\DatePicker::make('date_debut')->label('Date début'),
                Forms\Components\DatePicker::make('date_fin')->label('Date fin'),
                Forms\Components\Textarea::make('description')->label('Description'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('vehicle.immatriculation')->label('Véhicule')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('garage.nom')->label('Garage')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('type')->label('Type')->sortable(),
                Tables\Columns\TextColumn::make('cout')->label('Coût')->money('XOF', true)->sortable(),
                Tables\Columns\TextColumn::make('date_debut')->label('Début')->date()->sortable(),
                Tables\Columns\TextColumn::make('date_fin')->label('Fin')->date()->sortable(),
                Tables\Columns\TextColumn::make('created_at')->label('Créé le')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Type')
                    ->options([
                        'preventive' => 'Préventive',
                        'curative' => 'Curative',
                        'visite_technique' => 'Visite technique',
                    ]),
                Tables\Filters\Filter::make('date_debut')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label('Du'),
                        Forms\Components\DatePicker::make('to')->label('Au'),
                    ])
                    ->query(function ($query, $data) {
                        return $query
                            ->when($data['from'], fn($q) => $q->where('date_debut', '>=', $data['from']))
                            ->when($data['to'], fn($q) => $q->where('date_debut', '<=', $data['to']));
                    }),
                Tables\Filters\SelectFilter::make('garage_id')
                    ->label('Garage')
                    ->relationship('garage', 'nom'),
                Tables\Filters\Filter::make('cout')
                    ->form([
                        Forms\Components\TextInput::make('min')->label('Coût min')->numeric(),
                        Forms\Components\TextInput::make('max')->label('Coût max')->numeric(),
                    ])
                    ->query(function ($query, $data) {
                        return $query
                            ->when($data['min'], fn($q) => $q->where('cout', '>=', $data['min']))
                            ->when($data['max'], fn($q) => $q->where('cout', '<=', $data['max']));
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('date_debut', 'desc');
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
            'index' => Pages\ListMaintenances::route('/'),
            'create' => Pages\CreateMaintenance::route('/create'),
            'edit' => Pages\EditMaintenance::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        /** @var User|null $user */
        $user = Filament::auth()?->user();
        return $user?->can('manage maintenances') ?? false;
    }

    public static function canCreate(): bool
    {
        /** @var User|null $user */
        $user = Filament::auth()?->user();
        return $user?->can('manage maintenances') ?? false;
    }

    public static function canEdit($record): bool
    {
        /** @var User|null $user */
        $user = Filament::auth()?->user();
        return $user?->can('manage maintenances') ?? false;
    }

    public static function canDelete($record): bool
    {
        /** @var User|null $user */
        $user = Filament::auth()?->user();
        return $user?->can('manage maintenances') ?? false;
    }
}
