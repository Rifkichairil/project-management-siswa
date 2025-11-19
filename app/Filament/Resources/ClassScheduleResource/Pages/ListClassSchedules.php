<?php

namespace App\Filament\Resources\ClassScheduleResource\Pages;

use App\Filament\Resources\ClassScheduleResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\CreateAction;

class ListClassSchedules extends ListRecords
{
    protected static string $resource = ClassScheduleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Gunakan CreateAction untuk menampilkan formulir dalam modal
            CreateAction::make()
                // Opsi tambahan (opsional):
                ->modalWidth('5xl')
        ];
    }
}
