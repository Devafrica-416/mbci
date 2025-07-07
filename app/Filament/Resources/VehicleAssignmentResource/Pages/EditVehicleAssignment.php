<?php

namespace App\Filament\Resources\VehicleAssignmentResource\Pages;

use App\Filament\Resources\VehicleAssignmentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditVehicleAssignment extends EditRecord
{
    protected static string $resource = VehicleAssignmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
