<?php

namespace App\Filament\Resources\VehicleResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\History;
use Illuminate\Support\Facades\Auth;

class MaintenanceRelationManager extends RelationManager
{
    protected static string $relationship = 'maintenances';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('MaintenanceRelationManager')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('MaintenanceRelationManager')
            ->columns([
                Tables\Columns\TextColumn::make('type')->label('Type'),
                Tables\Columns\TextColumn::make('garage.nom')->label('Garage'),
                Tables\Columns\TextColumn::make('statut')->badge()->color(fn($state) => match($state) {
                    'en_cours' => 'warning',
                    'terminee' => 'success',
                    default => 'gray',
                }),
                Tables\Columns\TextColumn::make('cout')->money('EUR', true)->label('Coût'),
                Tables\Columns\TextColumn::make('date_debut')->date()->label('Début'),
                Tables\Columns\TextColumn::make('date_fin')->date()->label('Fin'),
                Tables\Columns\TextColumn::make('description')->limit(30),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('cloturer')
                    ->label('Clôturer la réparation')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn($record) => (
                        $record->statut !== 'terminee' &&
                        ($user = \Filament\Facades\Filament::auth()?->user()) instanceof \App\Models\User &&
                        method_exists($user, 'getRoleNames') &&
                        collect($user->getRoleNames())->intersect(['manager', 'administrateur', 'gestionnaire'])->isNotEmpty()
                    ))
                    ->form([
                        Forms\Components\TextInput::make('cout')->label('Coût')->numeric()->prefix('€')->required(),
                        Forms\Components\DatePicker::make('date_fin')->label('Date de fin')->required(),
                        Forms\Components\Textarea::make('commentaire')->label('Commentaire')->nullable(),
                    ])
                    ->action(function ($record, $data) {
                        $old = $record->replicate();
                        $record->update([
                            'statut' => 'terminee',
                            'cout' => $data['cout'],
                            'date_fin' => $data['date_fin'],
                            'description' => $record->description . ($data['commentaire'] ? "\n\n[Clôture] " . $data['commentaire'] : ''),
                        ]);
                        $record->vehicle?->update(['statut' => 'disponible']);
                        History::create([
                            'action' => 'cloture_maintenance',
                            'entity_type' => 'Maintenance',
                            'entity_id' => $record->id,
                            'user_id' => Auth::id(),
                            'old_values' => $old->only(['statut', 'cout', 'date_fin']),
                            'new_values' => $record->only(['statut', 'cout', 'date_fin']),
                            'comment' => $data['commentaire'] ?? null,
                        ]);
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelationshipName(): string
    {
        return 'maintenances';
    }
}
