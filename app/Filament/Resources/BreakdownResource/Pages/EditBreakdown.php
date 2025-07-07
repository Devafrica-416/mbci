<?php

namespace App\Filament\Resources\BreakdownResource\Pages;

use App\Filament\Resources\BreakdownResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBreakdown extends EditRecord
{
    protected static string $resource = BreakdownResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
