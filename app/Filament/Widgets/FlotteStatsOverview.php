<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use App\Models\Vehicle;
use App\Models\Breakdown;
use App\Models\Maintenance;
use Filament\Forms\Components\Select;
use Filament\Widgets\Widget;
use Illuminate\Support\Carbon;

class FlotteStatsOverview extends StatsOverviewWidget
{
    public ?string $periode = 'mois';
    public ?string $sortBy = 'date_fin';
    public ?string $sortDirection = 'desc';

    protected function getCards(): array
    {
        $totalVehicles = Vehicle::count();
        $disponibles = Vehicle::where('statut', 'disponible')
            ->whereDoesntHave('breakdowns', function ($query) {
                $query->whereIn('statut', ['declaree', 'en_cours']);
            })
            ->count();
        $enPanne = Vehicle::where('statut', 'en_panne')->count();
        $enReparation = Vehicle::where('statut', 'en_reparation')->count();
        $enMaintenance = Vehicle::where('statut', 'en_maintenance')->count();

        $tauxDisponibilite = $totalVehicles > 0 ? round(($disponibles / $totalVehicles) * 100, 1) : 0;
        
        // Filtres dynamiques sur les réparations
        $breakdowns = Breakdown::query()->whereNotNull('cout');
        if ($this->periode !== 'tout') {
            $date = match($this->periode) {
                'jour' => Carbon::now()->subDay(),
                'semaine' => Carbon::now()->subWeek(),
                'mois' => Carbon::now()->subMonth(),
                'annee' => Carbon::now()->subYear(),
                default => null,
            };
            if ($date) {
                $breakdowns->where('date_fin', '>=', $date);
            }
        }
        $breakdowns = $breakdowns->orderBy($this->sortBy, $this->sortDirection);
        $coutTotalReparations = $breakdowns->sum('cout');
        $pannesDeclarees = Breakdown::where('statut', 'declaree')->count();

        return [
            StatsOverviewWidget\Card::make('Total Véhicules', $totalVehicles)
                ->description('Parc automobile total')
                ->color('primary'),
            StatsOverviewWidget\Card::make('Véhicules Disponibles', $disponibles)
                ->description('Prêts à l\'utilisation')
                ->color('success'),
            StatsOverviewWidget\Card::make('En Panne', $enPanne)
                ->description('Nécessitent une intervention')
                ->color('danger'),
            StatsOverviewWidget\Card::make('En Réparation', $enReparation)
                ->description('En cours de réparation')
                ->color('warning'),
            StatsOverviewWidget\Card::make('En Maintenance', $enMaintenance)
                ->description('Maintenance préventive')
                ->color('info'),
            StatsOverviewWidget\Card::make('Taux de Disponibilité', $tauxDisponibilite . '%')
                ->description('Pourcentage de véhicules opérationnels')
                ->color($tauxDisponibilite >= 80 ? 'success' : ($tauxDisponibilite >= 60 ? 'warning' : 'danger')),
            StatsOverviewWidget\Card::make('Coût total des réparations', number_format($coutTotalReparations, 0, ',', ' ') . ' FCFA')
                ->description('Somme des coûts de réparation selon filtre')
                ->color('info'),
            StatsOverviewWidget\Card::make('Pannes déclarées non traitées', $pannesDeclarees)
                ->description('Pannes en attente de traitement')
                ->color('danger'),
        ];
    }

    public static function getFormSchema(): array
    {
        return [
            Select::make('periode')
                ->label('Période')
                ->options([
                    'jour' => 'Aujourd\'hui',
                    'semaine' => 'Cette semaine',
                    'mois' => 'Ce mois',
                    'annee' => 'Cette année',
                    'tout' => 'Tout',
                ])->default('mois'),
            Select::make('sortBy')
                ->label('Trier par')
                ->options([
                    'date_fin' => 'Date de fin',
                    'cout' => 'Coût',
                ])->default('date_fin'),
            Select::make('sortDirection')
                ->label('Ordre')
                ->options([
                    'desc' => 'Décroissant',
                    'asc' => 'Croissant',
                ])->default('desc'),
        ];
    }
} 