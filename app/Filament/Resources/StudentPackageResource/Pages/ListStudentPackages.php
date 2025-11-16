<?php

namespace App\Filament\Resources\StudentPackageResource\Pages;

use App\Filament\Resources\StudentPackageResource;
use App\Models\StudentPackage;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;


class ListStudentPackages extends ListRecords
{
    protected static string $resource = StudentPackageResource::class;
    protected static ?string $tabsAlignment = 'end'; // start = kiri, center = tengah, end = kanan

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All'),

            'perlu_topup' => Tab::make('Perlu Topup')
                ->badge(
                    StudentPackage::query()
                        ->where('remaining_quota', '<=', 5)
                        ->count()
                )
                ->badgeColor('danger') // merah
                ->modifyQueryUsing(fn (Builder $query) =>
                    $query->where('remaining_quota', '<=', 5)
                ),

            'aman' => Tab::make('Aman')
                ->badge(
                    StudentPackage::query()
                        ->where('remaining_quota', '>', 5)
                        ->count()
                )
                ->modifyQueryUsing(fn (Builder $query) =>
                    $query->where('remaining_quota', '>', 5)
                ),
        ];
    }

}
