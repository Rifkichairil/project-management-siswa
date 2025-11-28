<?php

namespace App\Filament\Resources\StudentPackageResource\Pages;

use App\Filament\Resources\StudentPackageResource;
use App\Models\StudentPackage;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;


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

            'sufficient_quota' => Tab::make('Sufficient Quota') // Aman
                ->badge(
                    StudentPackage::query()
                        ->where('remaining_quota', '>', 5)
                        ->count()
                )
                ->badgeColor('success')
                ->modifyQueryUsing(fn (Builder $query) =>
                    $query->where('remaining_quota', '>', 5)
                ),

            'low_quota' => Tab::make('Low Quota') // Perlu Topup
                ->badge(
                    StudentPackage::query()
                        ->where('remaining_quota', '<=', 5)
                        ->count()
                )
                ->badgeColor('danger')
                ->modifyQueryUsing(fn (Builder $query) =>
                    $query->where('remaining_quota', '<=', 5)
                ),
        ];
    }

}
