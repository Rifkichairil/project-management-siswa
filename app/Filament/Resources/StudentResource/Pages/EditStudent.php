<?php

namespace App\Filament\Resources\StudentResource\Pages;

use App\Filament\Resources\StudentResource;
use App\Models\Package;
use App\Models\StudentPackage;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditStudent extends EditRecord
{
    protected static string $resource = StudentResource::class;
    protected $packageId;
    protected $totalQouta;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // dd($data);
        $user = $this->record->user;

        $user->update([
            'name'  => $data['name'],
            'email' => $data['email'],
        ]);

        unset($data['name'], $data['email']);
        $this->packageId  = $data['package_id'] ?? null;
        $this->totalQouta = $data['total_quota'] ?? null;

        return $data;
    }

    protected function fillForm(): void
    {
        $student = $this->record;

        $this->form->fill([
            'name'          => $student->user->name,
            'email'         => $student->user->email,
            'password'      => '',
            'school'        => $student->school,
            'payment_status'=> $student->payment_status,
            'grade'         => $student->grade,
            'parent_name'   => $student->parent_name,
            'parent_contact'=> $student->parent_contact,
        ]);
    }

    protected function afterSave(): void
    {
        // dd($this->totalQouta);
        $student = $this->record;
        if ($this->packageId) {
            $package = Package::whereId($this->packageId)->first();
            $result = match (true) {
                $package->quota_classes === null => $this->totalQouta,
                $package->quota_classes !== null => $package->quota_classes,
                default                          => 0,
            };

            // dd($result);
            // dd($package->quota_classes !== null);
            // ⬇ PAKAI PROPERTY DI SINI
            StudentPackage::create([
                'student_id'      => $student->id,
                'package_id'      => $this->packageId, // 🔥 berhasil
                'total_quota'     => $result,
                'remaining_quota' => $result,
                'start_date'      => now(),
                'end_date'        => now()->addMonth(),
            ]);

        }

        Notification::make()
            ->title('Student berhasil diupdate!')
            ->success()
            ->send();
    }
}
