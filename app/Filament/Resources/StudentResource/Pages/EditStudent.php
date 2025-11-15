<?php

namespace App\Filament\Resources\StudentResource\Pages;

use App\Filament\Resources\StudentResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditStudent extends EditRecord
{
    protected static string $resource = StudentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $user = $this->record->user;

        $user->update([
            'name'  => $data['name'],
            'email' => $data['email'],
        ]);

        unset($data['name'], $data['email']);

        return $data;
    }

    protected function afterUpdate(): void
    {
        Notification::make()
            ->title('Student berhasil diupdate!')
            ->success()
            ->send();
    }
}
