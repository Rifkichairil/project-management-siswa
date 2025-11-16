<?php

namespace App\Filament\Resources\StudentPackageResource\Pages;

use App\Filament\Resources\StudentPackageResource;
use App\Models\Package;
use App\Models\StudentPackage;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Validation\ValidationException;

class CreateStudentPackage extends CreateRecord
{
    protected static string $resource = StudentPackageResource::class;

    protected function beforeCreate(): void
    {
        $data = $this->form->getState();

        $package = Package::find($data['package_id']);
        if (!$package) return;

        // Cek apakah student sudah punya package dengan type yang sama
        $exists = StudentPackage::where('student_id', $data['student_id'])
            ->whereHas('package', fn ($q) => $q->where('type', $package->type))
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'package_id' => "Student ini sudah memiliki package dengan type '{$package->type}'.",
            ]);
        }

        // Kalau type = quota, auto-set total_quota
        if ($package->type === 'quota') {
            $this->form->fill([
                'total_quota' => $package->quota_classes,
                'remaining_quota' => $package->quota_classes,
            ]);
        }
    }
}
