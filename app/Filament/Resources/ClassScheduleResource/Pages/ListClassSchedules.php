<?php

namespace App\Filament\Resources\ClassScheduleResource\Pages;

use App\Filament\Resources\ClassScheduleResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\CreateAction;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;


class ListClassSchedules extends ListRecords
{
    protected static string $resource = ClassScheduleResource::class;

   protected function getListeners(): array
    {
        return array_merge(parent::getListeners(), [
            'refreshClassScheduleData' => 'refreshAllData', // Panggil method kustom
        ]);
    }

    public function refreshAllData(): void
    {
        // 1. Memuat ulang data tabs (yang berisi count)
        $this->getTabs();

        // 2. Memuat ulang tabel
        $this->dispatch('refreshTable');

        // Atur ulang active tab agar badge terupdate
        $this->activeTab = $this->getActiveTab();
    }

    protected function getHeaderActions(): array
    {
        return [
            // Gunakan CreateAction untuk menampilkan formulir dalam modal
            CreateAction::make()
                // Opsi tambahan (opsional):
                ->modalWidth('5xl')->after(function ($livewire) {
                // Memicu event refresh setelah action Create berhasil
                $livewire->dispatch('refreshTabsAndTable');
            })
        ];
    }

    // --- TAMBAHKAN ATAU MODIFIKASI METODE INI ---
    protected function applySortingToTableQuery(Builder $query): Builder
    {
        // 1. Atur Prioritas Urutan Kustom (status = 'scheduled' harus paling atas)
        $query->orderByRaw("FIELD(status, 'scheduled') DESC");

        // 2. Tambahkan urutan sekunder (misalnya berdasarkan tanggal dan waktu mulai)
        $query->orderBy('date', 'asc');
        $query->orderBy('time_start', 'asc');

        return $query;
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All Schedules')
                ->badge(fn () => static::getModel()::count()),

            'scheduled' => Tab::make('Scheduled')
                ->modifyQueryUsing(fn ($query) => $query->where('status', 'scheduled'))
                ->badge(fn () => static::getModel()::where('status', 'scheduled')->count())
                ->badgeColor('primary'),

            'completed' => Tab::make('Completed')
                ->modifyQueryUsing(fn ($query) => $query->where('status', 'completed'))
                ->badge(fn () => static::getModel()::where('status', 'completed')->count())
                ->badgeColor('success'),

            'cancelled' => Tab::make('Cancelled')
                ->modifyQueryUsing(fn ($query) => $query->where('status', 'cancelled'))
                ->badge(fn () => static::getModel()::where('status', 'cancelled')->count())
                ->badgeColor('danger'),
        ];
    }

}
