<?php

namespace App\Filament\Resources\TeacherResource\Pages;

use App\Filament\Resources\TeacherResource;
use App\Models\User;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateTeacher extends CreateRecord
{
    protected static string $resource = TeacherResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Create new user
        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => bcrypt($data['password']),
            'role'     => 'teacher',
        ]);

        // Move user_id to teacher table
        $data['user_id'] = $user->id;

        // Remove fields that do not belong in teacher table
        unset($data['name'], $data['email'], $data['password']);

        return $data;
    }

    protected function afterCreate(): void
    {
        Notification::make()
            ->title('Berhasil membuat teacher!')
            ->success()
            ->send();
    }
}
