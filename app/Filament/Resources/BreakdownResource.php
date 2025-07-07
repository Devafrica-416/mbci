<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BreakdownResource\Pages;
use App\Filament\Resources\BreakdownResource\RelationManagers;
use App\Models\Breakdown;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\User;
use Filament\Facades\Filament;

class BreakdownResource extends Resource
{
    protected static ?string $model = Breakdown::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'Réparations';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('vehicle_id')
                    ->label('Véhicule')
                    ->relationship('vehicle', 'immatriculation')
                    ->searchable()
                    ->required(),
                Forms\Components\Select::make('user_id')
                    ->label('Déclarant')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->required(),
                Forms\Components\Textarea::make('description')->label('Description')->required(),
                Forms\Components\Select::make('statut')
                    ->label('Statut')
                    ->options([
                        'declaree' => 'Déclarée',
                        'en_cours' => 'En cours',
                        'reparee' => 'Réparée',
                        'cloturee' => 'Clôturée',
                    ])->required(),
                Forms\Components\DateTimePicker::make('date_declaration')->label('Date de déclaration')->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('vehicle.immatriculation')->label('Véhicule')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('user.name')->label('Déclarant')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('description')->label('Description')->limit(30)->sortable(),
                Tables\Columns\TextColumn::make('statut')->label('Statut')->sortable(),
                Tables\Columns\TextColumn::make('date_declaration')->label('Date de déclaration')->dateTime()->sortable(),
                Tables\Columns\TextColumn::make('created_at')->label('Créé le')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('statut')
                    ->label('Statut')
                    ->options([
                        'declaree' => 'Déclarée',
                        'en_cours' => 'En cours',
                        'reparee' => 'Réparée',
                        'cloturee' => 'Clôturée',
                    ]),
                Tables\Filters\Filter::make('date_declaration')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label('Du'),
                        Forms\Components\DatePicker::make('to')->label('Au'),
                    ])
                    ->query(function ($query, $data) {
                        return $query
                            ->when($data['from'], fn($q) => $q->where('date_declaration', '>=', $data['from']))
                            ->when($data['to'], fn($q) => $q->where('date_declaration', '<=', $data['to']));
                    }),
                Tables\Filters\SelectFilter::make('vehicle_id')
                    ->label('Véhicule')
                    ->relationship('vehicle', 'immatriculation'),
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
                        \Filament\Forms\Components\Select::make('garage_id')
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
                        \App\Models\History::create([
                            'action' => 'affectation_garage',
                            'entity_type' => 'Breakdown',
                            'entity_id' => $record->id,
                            'user_id' => \Illuminate\Support\Facades\Auth::id(),
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
                        \App\Models\History::create([
                            'action' => 'mise_en_reparation',
                            'entity_type' => 'Breakdown',
                            'entity_id' => $record->id,
                            'user_id' => \Illuminate\Support\Facades\Auth::id(),
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
                        \Filament\Forms\Components\TextInput::make('cout')->label('Coût')->numeric()->prefix('FCFA')->required(),
                        \Filament\Forms\Components\DatePicker::make('date_fin')->label('Date de fin')->required(),
                        \Filament\Forms\Components\Textarea::make('commentaire')->label('Commentaire')->nullable(),
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
                        \App\Models\History::create([
                            'action' => 'cloture_reparation',
                            'entity_type' => 'Breakdown',
                            'entity_id' => $record->id,
                            'user_id' => \Illuminate\Support\Facades\Auth::id(),
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
            ])
            ->defaultSort('date_declaration', 'desc');
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
            'index' => Pages\ListBreakdowns::route('/'),
            'create' => Pages\CreateBreakdown::route('/create'),
            'edit' => Pages\EditBreakdown::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        /** @var User|null $user */
        $user = Filament::auth()?->user();
        return $user?->can('manage breakdowns') ?? false;
    }

    public static function canCreate(): bool
    {
        /** @var User|null $user */
        $user = Filament::auth()?->user();
        return $user?->can('manage breakdowns') ?? false;
    }

    public static function canEdit($record): bool
    {
        /** @var User|null $user */
        $user = Filament::auth()?->user();
        return $user?->can('manage breakdowns') ?? false;
    }

    public static function canDelete($record): bool
    {
        /** @var User|null $user */
        $user = Filament::auth()?->user();
        return $user?->can('manage breakdowns') ?? false;
    }
}
