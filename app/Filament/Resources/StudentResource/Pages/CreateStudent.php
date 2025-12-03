<?php

namespace App\Filament\Resources\StudentResource\Pages;

use App\Filament\Resources\StudentResource;
use App\Models\Package;
use App\Models\StudentPackage;
use App\Models\User;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\DB;

class CreateStudent extends CreateRecord
{
    protected static string $resource = StudentResource::class;
    protected $packageId;
    protected $totalQouta;


    protected function mutateFormDataBeforeCreate(array $data): array
    {
        DB::beginTransaction();

        try {
            // ⛳ Create User
            $user = User::create([
                'name'     => $data['name'],
                'email'    => $data['email'],
                // 'password' => bcrypt($data['password']),
            ]);

            // Inject user
            $data['user_id'] = $user->id;

            // simpan package ke property AGAR BISA DIPAKAI di afterCreate()
            $this->packageId  = $data['package_id'] ?? null;
            $this->totalQouta = $data['total_quota'] ?? null;


            // buang field yang tidak perlu masuk table students
            unset($data['name'], $data['email'], $data['password']);

            DB::commit();
            return $data;

        } catch (\Throwable $e){
            DB::rollBack();
            throw $e;
        }
    }

    protected function afterCreate(): void
    {
        // record Student sudah ke-create otomatis oleh Filament
        $student = $this->record;
        $package = Package::whereId($this->packageId)->first();
        $result = match (true) {
                $package->quota_classes === null => $this->totalQouta,
                $package->quota_classes !== null => $package->quota_classes,
                default                          => 0,
            };

        // ⬇ PAKAI PROPERTY DI SINI
        StudentPackage::create([
            'student_id'      => $student->id,
            'package_id'      => $this->packageId, // 🔥 berhasil
            'total_quota'     => $result ?? 0,
            'remaining_quota' => $result ?? 0,
            'start_date'      => now(),
            'end_date'        => now()->addMonth(),
        ]);

        Notification::make()
            ->title('Student has been successfully created!')
            ->success()
            ->send();

    }
}
