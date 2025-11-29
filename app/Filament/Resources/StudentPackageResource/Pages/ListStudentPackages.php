<?php

namespace App\Filament\Resources\StudentPackageResource\Pages;

use App\Filament\Resources\StudentPackageResource;
use App\Models\StudentPackage;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class ListStudentPackages extends ListRecords
{
    protected static string $resource = StudentPackageResource::class;
    protected static ?string $tabsAlignment = 'end'; // start = kiri, center = tengah, end = kanan

    protected function getHeaderActions(): array
    {
        return [
            // Gunakan CreateAction untuk menampilkan formulir dalam modal
            CreateAction::make()
                // Opsi tambahan (opsional):
                ->modalWidth('5xl')
        ];
    }

    public function getTabs(): array
{
    return [
        'all' => Tab::make('All'),

        // === Active & Quota Aman ===
        'sufficient_quota' => Tab::make('Sufficient Quota')
            ->badge(
                StudentPackage::where('status', 'active')
                    ->whereColumn('remaining_quota', '>', DB::raw('CEIL(total_quota / 2)'))
                    ->count()
            )
            ->badgeColor('success')
            ->modifyQueryUsing(fn ($query) =>
                $query->where('status', 'active')
                      ->whereColumn('remaining_quota', '>', DB::raw('CEIL(total_quota / 2)'))
            ),

        // === Active tetapi quota Low ===
        'low_quota' => Tab::make('Low Quota')
            ->badge(
                StudentPackage::where('status', 'active')
                    ->whereColumn('remaining_quota', '<=', DB::raw('CEIL(total_quota / 2)'))
                    ->count()
            )
            ->badgeColor('danger')
            ->modifyQueryUsing(fn ($query) =>
                $query->where('status', 'active')
                      ->whereColumn('remaining_quota', '<=', DB::raw('CEIL(total_quota / 2)'))
            ),

        // === Inactive Package ===
        'inactive' => Tab::make('Inactive')
            ->badge(
                StudentPackage::where('status','inactive')->count()
            )
            ->badgeColor('secondary')
            ->modifyQueryUsing(fn ($query) =>
                $query->where('status','inactive')
            ),

        // === Expired Package ===
        'expired' => Tab::make('Expired')
            ->badge(
                StudentPackage::where('status','expired')->count()
            )
            ->badgeColor('warning')
            ->modifyQueryUsing(fn ($query) =>
                $query->where('status','expired')
            ),
    ];
}


}
