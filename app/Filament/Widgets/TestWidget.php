<?php

namespace App\Filament\Widgets;

use App\Models\ClassSchedule;
use App\Models\Student;
use App\Models\Teacher;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TestWidget extends BaseWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 0;

    protected function getFilters(): array
    {
        return [
            DatePicker::make('startDate'),
            DatePicker::make('endDate'),
        ];
    }

    protected function getStats(): array
    {
        $filters = $this->filters;

        $startDate = $filters['startDate']
            ? Carbon::parse($filters['startDate'])->startOfDay()
            : Carbon::create(1900, 1, 1)->startOfDay();

        $endDate = $filters['endDate']
            ? Carbon::parse($filters['endDate'])->endOfDay()
            : now()->endOfDay();

        if ($startDate > $endDate) {
            $startDate = now()->subDays(7)->startOfDay();
        }

       // Counts
        $totalStudents = Student::count();
        $totalTeachers = Teacher::count();

        $scheduled = ClassSchedule::where('status', 'scheduled')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        $completed = ClassSchedule::where('status', 'completed')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        $cancelled = ClassSchedule::where('status', 'cancelled')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        // Attendance %
        $attendanceRate = ($scheduled + $completed) > 0
            ? round(($completed / ($scheduled + $completed)) * 100, 1)
            : 0;

        return [
            Stat::make('Students', $totalStudents)
                ->description('Total enrolled students')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('primary'),

            Stat::make('Teachers', $totalTeachers)
                ->description('Active instructors')
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color('success'),

            Stat::make('Scheduled Classes', $scheduled)
                ->description('Upcoming lessons')
                ->descriptionIcon('heroicon-m-calendar')
                ->color('warning'),

            Stat::make('Completed Classes', $completed)
                ->description('Finished sessions')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Cancelled Classes', $cancelled)
                ->description('Cancelled sessions')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('danger'),

            Stat::make('Attendance Rate', $attendanceRate . '%')
                ->description('Student attendance performance')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color($attendanceRate >= 80 ? 'success' : 'warning'),
        ];
    }

}
