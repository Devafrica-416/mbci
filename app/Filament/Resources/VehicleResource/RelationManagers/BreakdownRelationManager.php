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

class BreakdownRelationManager extends RelationManager
{
    protected static string $relationship = 'breakdowns';

    public static function getRelationshipName(): string
    {
        return 'breakdowns';
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('BreakdownRelationManager')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('description')
            ->columns([
                Tables\Columns\TextColumn::make('description')->limit(30)->label('Description'),
                Tables\Columns\TextColumn::make('statut')->badge()->color(fn($state) => match($state) {
                    'declaree' => 'warning',
                    'en_cours' => 'primary',
                    'reparee' => 'success',
                    'cloturee' => 'gray',
                    default => 'gray',
                }),
                Tables\Columns\TextColumn::make('garage.nom')->label('Garage')->sortable(),
                Tables\Columns\TextColumn::make('date_declaration')->dateTime()->label('Déclarée le'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('affecterGarage')
                    ->label('Affecter à un garage')
                    ->icon('heroicon-o-building-storefront')
                    ->color('primary')
                    ->visible(fn($record) => (
                        in_array($record->statut, ['declaree', 'reparee']) &&
                        ($user = \Filament\Facades\Filament::auth()?->user()) instanceof \App\Models\User &&
                        method_exists($user, 'getRoleNames') &&
                        collect($user->getRoleNames())->intersect(['manager', 'administrateur', 'gestionnaire'])->isNotEmpty()
                    ))
                    ->form([
                        Forms\Components\Select::make('garage_id')
                            ->label('Garage')
                            ->relationship('garage', 'nom')
                            ->searchable()
                            ->required(),
                    ])
                    ->action(function ($record, $data) {
                        $old = $record->replicate();
                        $record->update([
                            'garage_id' => $data['garage_id'],
                        ]);
                        History::create([
                            'action' => 'affectation_garage',
                            'entity_type' => 'Breakdown',
                            'entity_id' => $record->id,
                            'user_id' => Auth::id(),
                            'old_values' => json_encode($old->only(['garage_id'])),
                            'new_values' => json_encode($record->only(['garage_id'])),
                            'comment' => 'Affectation à un garage',
                        ]);
                    }),
                Tables\Actions\Action::make('mettreEnReparation')
                    ->label('Mettre en réparation')
                    ->icon('heroicon-o-wrench-screwdriver')
                    ->color('warning')
                    ->visible(fn($record) => (
                        in_array($record->statut, ['declaree', 'reparee']) &&
                        $record->garage_id &&
                        ($user = \Filament\Facades\Filament::auth()?->user()) instanceof \App\Models\User &&
                        method_exists($user, 'getRoleNames') &&
                        collect($user->getRoleNames())->intersect(['manager', 'administrateur', 'gestionnaire'])->isNotEmpty()
                    ))
                    ->action(function ($record) {
                        $old = $record->replicate();
                        $record->update([
                            'statut' => 'en_cours',
                        ]);
                        History::create([
                            'action' => 'mise_en_reparation',
                            'entity_type' => 'Breakdown',
                            'entity_id' => $record->id,
                            'user_id' => Auth::id(),
                            'old_values' => json_encode($old->only(['statut'])),
                            'new_values' => json_encode($record->only(['statut'])),
                            'comment' => 'Mise en réparation',
                        ]);
                    }),
                Tables\Actions\Action::make('cloturerReparation')
                    ->label('Clôturer la réparation')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn($record) => (
                        $record->statut === 'en_cours' &&
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
                            'statut' => 'reparee',
                            'cout' => $data['cout'],
                            'date_fin' => $data['date_fin'],
                            'description' => $record->description . ($data['commentaire'] ? "\n\n[Clôture] " . $data['commentaire'] : ''),
                        ]);
                        $record->vehicle?->update(['statut' => 'disponible']);
                        History::create([
                            'action' => 'cloture_reparation',
                            'entity_type' => 'Breakdown',
                            'entity_id' => $record->id,
                            'user_id' => Auth::id(),
                            'old_values' => json_encode($old->only(['statut', 'cout', 'date_fin'])),
                            'new_values' => json_encode($record->only(['statut', 'cout', 'date_fin'])),
                            'comment' => $data['commentaire'] ?? null,
                        ]);
                    }),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
