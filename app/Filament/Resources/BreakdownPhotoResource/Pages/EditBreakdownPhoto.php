<?php

namespace App\Filament\Resources\BreakdownPhotoResource\Pages;

use App\Filament\Resources\BreakdownPhotoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBreakdownPhoto extends EditRecord
{
    protected static string $resource = BreakdownPhotoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
