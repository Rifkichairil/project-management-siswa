<?php

namespace App\Filament\Resources\StudentResource\Pages;

use App\Filament\Resources\StudentResource;
use App\Models\User;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateStudent extends CreateRecord
{
    protected static string $resource = StudentResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => bcrypt($data['password']),
            'role'     => 'student',
        ]);

        $data['user_id'] = $user->id;

        unset($data['name'], $data['email'], $data['password']);

        return $data;
    }

    protected function afterCreate(): void
    {
        Notification::make()
            ->title('Student berhasil dibuat!')
            ->success()
            ->send();
    }
}
