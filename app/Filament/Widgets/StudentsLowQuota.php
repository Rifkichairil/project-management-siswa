<?php

namespace App\Filament\Widgets;

use App\Models\Student;
use App\Models\ClassSchedule;
use App\Models\StudentPackage;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;

class StudentsLowQuota extends BaseWidget
{
    protected static ?string $heading = 'Students Low Quota';
    protected static ?int $sort = 1;

    // Threshold: students with remaining_quota <= this number will appear
    protected int $threshold = 3;

    // Make the widget full width
    protected int|string|array $columnSpan = 'full';

    /**
     * This is required by Filament Table: return a Builder used for the table.
     */
    protected function getTableQuery(): Builder
    {
        return StudentPackage::query()
            ->with('student.user') // eager load relation(s) used in columns
            ->where('remaining_quota', '<=', $this->threshold)
            ->orderBy('remaining_quota', 'asc');
    }

    /**
     * Build the table. IMPORTANT: call ->query($this->getTableQuery()) here.
     */
    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery()) // ← required
            ->columns([
                TextColumn::make('student.user.name')
                    ->label('Student')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('student.parent_name')
                    ->label('Parent')
                    ->searchable(),

                TextColumn::make('student.parent_contact')
                    ->label('Contact')
                    ->formatStateUsing(fn ($state) => "+$state") // Optional: tambahin prefix +62 kalau perlu
                    ->url(fn ($record) => "https://wa.me/" . preg_replace('/\D/', '', $record->student->parent_contact))
                    ->openUrlInNewTab()
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->color('primary'),

                TextColumn::make('total_quota')
                    ->label('Total Quota'),

                TextColumn::make('used_quota')
                    ->label('Used')
                    ->sortable(),

                TextColumn::make('remaining_quota')
                    ->label('Remaining')
                    ->badge()
                    ->color(fn ($state) => $state == 0 ? 'danger' : ($state <= 2 ? 'danger' : 'warning'))
                    ->sortable(),

                TextColumn::make('end_date')
                    ->label('Expiry')
                    ->date('M d, Y')
                    ->sortable(),
            ])
            ->defaultSort('remaining_quota', 'asc'); // ⬅ versi terbaru untuk disable pagination

    }
}

