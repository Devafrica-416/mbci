<?php

namespace App\Filament\Resources\VehicleResource\Pages;

use App\Filament\Resources\VehicleResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Widgets\Widget;
use Filament\Widgets\StatsOverviewWidget;
use App\Models\Vehicle;
use App\Models\Breakdown;
use App\Models\Maintenance;
use App\Filament\Widgets\FlotteStatsOverview;

class ListVehicles extends ListRecords
{
    protected static string $resource = VehicleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            FlotteStatsOverview::class,
        ];
    }
}

class VehicleStatsOverview extends StatsOverviewWidget
{
    protected function getCards(): array
    {
        $totalVehicles = Vehicle::count();
        $disponibles = Vehicle::where('statut', 'disponible')->count();
        $enPanne = Vehicle::where('statut', 'en_panne')->count();
        $enReparation = Vehicle::where('statut', 'en_reparation')->count();
        $enMaintenance = Vehicle::where('statut', 'en_maintenance')->count();

        $tauxDisponibilite = $totalVehicles > 0 ? round(($disponibles / $totalVehicles) * 100, 1) : 0;
        $tauxPanne = $totalVehicles > 0 ? round(($enPanne / $totalVehicles) * 100, 1) : 0;
        $tauxReparation = $totalVehicles > 0 ? round(($enReparation / $totalVehicles) * 100, 1) : 0;
        $tauxMaintenance = $totalVehicles > 0 ? round(($enMaintenance / $totalVehicles) * 100, 1) : 0;

        return [
            StatsOverviewWidget\Card::make('Véhicules', Vehicle::count()),
            StatsOverviewWidget\Card::make('Pannes en cours', Breakdown::where('statut', 'en_cours')->count()),
            StatsOverviewWidget\Card::make('Maintenances en cours', Maintenance::where('statut', 'en_cours')->count()),
            StatsOverviewWidget\Card::make('Véhicules disponibles', Vehicle::where('statut', 'disponible')->count()),
            StatsOverviewWidget\Card::make('Taux de disponibilité', $tauxDisponibilite . '%'),
            StatsOverviewWidget\Card::make('Taux de pannes', $tauxPanne . '%'),
            StatsOverviewWidget\Card::make('Taux de réparation', $tauxReparation . '%'),
            StatsOverviewWidget\Card::make('Taux de maintenance', $tauxMaintenance . '%'),
        ];
    }
}
