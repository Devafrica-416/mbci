<?php

namespace App\Filament\Resources\VehicleResource\RelationManagers;

use App\Models\History;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;

class HistoryRelationManager extends RelationManager
{
    protected static string $relationship = 'histories';
    protected static ?string $title = 'Historique';

    public static function getRelationshipName(): string
    {
        return 'histories';
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')->label('Date')->dateTime()->sortable(),
                Tables\Columns\TextColumn::make('action')->label('Action')->searchable(),
                Tables\Columns\TextColumn::make('user.name')->label('Utilisateur')->searchable(),
                Tables\Columns\TextColumn::make('entity_type')->label('Type'),
                Tables\Columns\TextColumn::make('comment')->label('Commentaire')->limit(40),
            ])
            ->filters([
                // Ajoute des filtres si besoin
            ])
            ->defaultSort('created_at', 'desc');
    }

    public function getTableQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $vehicleId = $this->ownerRecord->id;
        return History::query()
            ->where(function($q) use ($vehicleId) {
                $q->where(function($q2) use ($vehicleId) {
                    $q2->where('entity_type', 'Vehicle')->where('entity_id', $vehicleId);
                })
                ->orWhere(function($q2) use ($vehicleId) {
                    $q2->whereIn('entity_type', ['Breakdown', 'Maintenance'])
                        ->whereIn('entity_id', function($sub) use ($vehicleId) {
                            $sub->select('id')
                                ->from('breakdowns')
                                ->where('vehicle_id', $vehicleId)
                                ->union(
                                    DB::table('maintenances')->select('id')->where('vehicle_id', $vehicleId)
                                );
                        });
                });
            });
    }
} 