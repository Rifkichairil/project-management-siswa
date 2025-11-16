<?php

namespace App\Filament\Resources\StudentPackageResource\Pages;

use App\Filament\Resources\StudentPackageResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditStudentPackage extends EditRecord
{
    protected static string $resource = StudentPackageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
