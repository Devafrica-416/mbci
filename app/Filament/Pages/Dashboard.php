<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use App\Filament\Widgets\FlotteStatsOverview;
use App\Filament\Widgets\InterventionsChart;

class Dashboard extends BaseDashboard
{
    protected function getHeaderWidgets(): array
    {
        return [
            FlotteStatsOverview::class,
            InterventionsChart::class,
        ];
    }
} 