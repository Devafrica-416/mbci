<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Breakdown;

class InterventionsChart extends ChartWidget
{
    protected static ?string $heading = 'Évolution des interventions';

    protected function getData(): array
    {
        $data = Breakdown::selectRaw('MONTH(created_at) as month, COUNT(*) as count')
            ->whereYear('created_at', now()->year)
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('count', 'month');

        $labels = [];
        $values = [];
        for ($i = 1; $i <= 12; $i++) {
            $labels[] = now()->startOfYear()->addMonths($i - 1)->format('M');
            $values[] = $data[$i] ?? 0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Interventions',
                    'data' => $values,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line'; // ou 'bar' si tu préfères
    }
} 