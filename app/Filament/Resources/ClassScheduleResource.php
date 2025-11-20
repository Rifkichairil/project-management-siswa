<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClassScheduleResource\Pages;
use App\Filament\Resources\ClassScheduleResource\RelationManagers;
use App\Models\ClassSchedule;
use App\Rules\NoTeacherOverlap;
use Closure;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\TimePicker;

class ClassScheduleResource extends Resource
{
    protected static ?string $model = ClassSchedule::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Class';
    protected static ?int $navigationSort = 2;


   public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(2)
                    ->schema([
                        Select::make('student_id')
                            ->label('Student')
                            ->searchable()
                            ->options(function () {
                                return \App\Models\Student::with('user')
                                    ->orderBy(
                                        \App\Models\User::select('name')
                                            ->whereColumn('users.id', 'students.user_id')
                                            ->limit(1)
                                    )
                                    ->limit(3)
                                    ->get()
                                    ->mapWithKeys(fn ($student) => [
                                        $student->id => $student->user->name,
                                    ]);
                            })
                            ->getSearchResultsUsing(function (string $search) {
                                return \App\Models\Student::whereHas('user', function ($q) use ($search) {
                                    $q->where('name', 'like', "%{$search}%");
                                })
                                ->with('user')
                                ->limit(20)
                                ->get()
                                ->mapWithKeys(function ($student) {
                                    return [
                                        $student->id => $student->user->name,
                                    ];
                                });
                            })
                            ->getOptionLabelUsing(function ($value) {
                                $student = \App\Models\Student::with('user')->find($value);
                                return $student?->user?->name ?? 'Unknown';
                            })
                            ->required(),
                        Select::make('teacher_id')
                            ->label('Teacher')
                            ->searchable()
                            ->options(function () {
                                return \App\Models\Teacher::with('user')
                                    ->orderBy(
                                        \App\Models\User::select('name')
                                            ->whereColumn('users.id', 'teachers.user_id')
                                            ->limit(1)
                                    )
                                    ->limit(3)
                                    ->get()
                                    ->mapWithKeys(fn ($student) => [
                                        $student->id => $student->user->name,
                                    ]);
                            })
                            ->getSearchResultsUsing(function (string $search) {
                                return \App\Models\Teacher::whereHas('user', function ($q) use ($search) {
                                        $q->where('name', 'like', "%{$search}%");
                                    })
                                    ->with('user')
                                    ->limit(20)
                                    ->get()
                                    ->mapWithKeys(function ($teacher) {
                                        return [
                                            $teacher->id => $teacher->user->name,
                                        ];
                                    });
                            })
                            ->getOptionLabelUsing(function ($value) {
                                $teacher = \App\Models\Teacher::with('user')->find($value);
                                return $teacher?->user?->name ?? 'Unknown';
                            })
                            ->required(),

                        Select::make('subject_id')
                            ->label('Subject')
                            ->relationship('subject', 'name')
                            ->searchable()
                            ->required(),
                        DatePicker::make('date')
                            ->default(now())
                            ->required(),
                        Grid::make(3)
                            ->schema([
                                TimePicker::make('time_start')
                                    ->label('Time Start')
                                    ->native(false)
                                    ->seconds(false)
                                    ->format('H:i')
                                    ->displayFormat('H:i')
                                    ->required(),

                                TimePicker::make('time_end')
                                    ->label('Time End')
                                    ->native(false)
                                    ->seconds(false)
                                    ->format('H:i')
                                    ->displayFormat('H:i')
                                    ->required()
                                    ->rule(function ($get) {
                                        $teacherId = $get('teacher_id');
                                        $date      = $get('date');
                                        $start     = $get('time_start');
                                        $end       = $get('time_end');
                                        $currentId = request()->route('record');

                                        if (!$teacherId || !$date || !$start || !$end) {
                                            return [];
                                        }

                                        return new NoTeacherOverlap(
                                            teacherId: $teacherId,
                                            date: $date,
                                            timeStart: $start,
                                            timeEnd: $end,
                                            ignoreId: $currentId,
                                        );
                                    }),
                                    Select::make('status')
                                        ->options([
                                            'scheduled' => 'Scheduled',
                                            'completed' => 'Completed',
                                            'cancelled' => 'Cancelled',
                                        ])
                                        ->required(),
                                ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
    return $table
        ->columns([
            TextColumn::make('student.user.name')->label('Student')->searchable(),
            TextColumn::make('teacher.user.name')->label('Teacher')->searchable(),
            TextColumn::make('subject.name')->label('Subject')->searchable(),
            TextColumn::make('date')->date(),
            TextColumn::make('time_start')->label('Start'),
            TextColumn::make('time_end')->label('End'),
            TextColumn::make('status')->badge(),
        ])
        ->filters([])
        ->actions([
            Tables\Actions\EditAction::make(),
        ])
        ->bulkActions([
            Tables\Actions\DeleteBulkAction::make(),
        ]);
    }


    public static function getPages(): array
    {
        return [
            'index' => Pages\ListClassSchedules::route('/'),
            // 'create' => Pages\CreateClassSchedule::route('/create'),
            // 'edit' => Pages\EditClassSchedule::route('/{record}/edit'),
        ];
    }
}
