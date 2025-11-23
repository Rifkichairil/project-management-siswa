<?php

namespace App\Filament\Resources\ClassScheduleResource\Pages;

use App\Filament\Resources\ClassScheduleResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditClassSchedule extends EditRecord
{
    protected static string $resource = ClassScheduleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        if ($this->record->status === 'completed') {

            $this->record->classReport()->updateOrCreate([], [
                'topic' => $this->data['classReport']['topic'] ?? null,
                'progress' => $this->data['classReport']['progress'] ?? null,
                'notes' => $this->data['classReport']['notes'] ?? null,
                'teacher_feedback' => $this->data['classReport']['teacher_feedback'] ?? null,
            ]);
        }
    }
}
