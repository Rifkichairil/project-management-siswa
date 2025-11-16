<?php

namespace App\Filament\Resources\PackageResource\Pages;

use App\Filament\Resources\PackageResource;
use Filament\Actions;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPackages extends ListRecords
{
    protected static string $resource = PackageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Gunakan CreateAction untuk menampilkan formulir dalam modal
            CreateAction::make()
                // Opsi tambahan (opsional):
                ->modalWidth('xl')
        ];
    }


}
