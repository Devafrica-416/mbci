<?php

namespace App\Filament\Resources\VehicleAssignmentResource\Pages;

use App\Filament\Resources\VehicleAssignmentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListVehicleAssignments extends ListRecords
{
    protected static string $resource = VehicleAssignmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
