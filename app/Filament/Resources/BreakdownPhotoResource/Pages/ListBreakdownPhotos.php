<?php

namespace App\Filament\Resources\BreakdownPhotoResource\Pages;

use App\Filament\Resources\BreakdownPhotoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBreakdownPhotos extends ListRecords
{
    protected static string $resource = BreakdownPhotoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
