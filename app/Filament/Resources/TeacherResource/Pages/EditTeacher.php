<?php

namespace App\Filament\Resources\TeacherResource\Pages;

use App\Filament\Resources\TeacherResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTeacher extends EditRecord
{
    protected static string $resource = TeacherResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $teacher = $this->record;

        // Update user related fields
        $teacher->user->update([
            'name'  => $data['name'],
            'email' => $data['email'],
            'password' => !empty($data['password'])
                ? bcrypt($data['password'])
                : $teacher->user->password,
        ]);

        unset($data['name'], $data['email'], $data['password']);

        return $data;
    }

    protected function fillForm(): void
    {
        $teacher = $this->record;

        $this->form->fill([
            'name'       => $teacher->user->name,
            'email'      => $teacher->user->email,
            'password'   => '',
            'expertise'  => $teacher->expertise,
            'curriculum' => $teacher->curriculum,
        ]);
    }

    protected function afterUpdate(): void
    {
        parent::afterUpdate();
        $this->notify('success', 'Teacher successfully updated!');
    }
}
