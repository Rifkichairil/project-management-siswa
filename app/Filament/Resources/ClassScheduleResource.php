<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClassScheduleResource\Pages;
use App\Filament\Resources\ClassScheduleResource\RelationManagers;
use App\Models\ClassSchedule;
use App\Models\Subject;
use App\Rules\CheckStudentQuota;
use App\Rules\NoScheduleOverlap;
use App\Rules\NoTeacherOverlap;
use Closure;
use Filament\Forms\Get; // Tambahkan ini
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
use Carbon\Carbon;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\Action;

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
                                    ->limit(10)
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
                                    ->limit(10)
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
                            ->relationship('subject', 'name', function ($query) {
                                $query->limit(5); // langsung ambil 5 data pertama
                            })
                            ->searchable()
                            ->preload() // preload data agar langsung tampil
                            ->required(),
                        DatePicker::make('date')
                            ->default(now())
                            ->required(),
                        Grid::make(3)
                            ->schema([
                                // Kolom Time Start:
                                TimePicker::make('time_start')
                                    ->label('Time Start')
                                    ->native(false)
                                    ->seconds(false)
                                    ->format('H:i')
                                    ->displayFormat('H:i')
                                    ->required(),

                                // Kolom Time End (Pasang Validasi di sini!):
                                TimePicker::make('time_end')
                                    ->label('Time End')
                                    ->native(false)
                                    ->seconds(false)
                                    ->format('H:i')
                                    ->displayFormat('H:i')
                                    ->required()
                                    ->rules([
                                        'after:time_start', // Waktu selesai harus setelah waktu mulai

                                        // Validasi 1: Pengecekan Kuota
                                        fn (Get $get) => new CheckStudentQuota(
                                            studentId: $get('student_id'),
                                            timeStart: $get('time_start')
                                        ),

                                        // ⭐ VALIDASI GABUNGAN: Pengecekan Kesesuaian Tipe & Overlapping Jadwal (Ganti Validasi 2 & 3)
                                        fn (Get $get, string $operation) => new \App\Rules\ComprehensiveClassScheduleValidation(
                                            teacherId: $get('teacher_id'),
                                            studentId: $get('student_id'),
                                            timeStart: $get('time_start'),
                                            date: $get('date'),
                                            // Kirim ID jika sedang Edit untuk diabaikan (ignoringId)
                                            ignoringId: $operation === 'edit' ? $get('id') : null
                                        ),

                                        // // Validasi 2: Pengecekan Overlapping Jadwal
                                        // fn (Get $get, string $operation) => new NoScheduleOverlap(
                                        //     date: $get('date'),
                                        //     teacherId: $get('teacher_id'),
                                        //     studentId: $get('student_id'),
                                        //     timeStart: $get('time_start'),
                                        //     // Kirim ID jika sedang Edit untuk diabaikan
                                        //     ignoringId: $operation === 'edit' ? $get('id') : null
                                        // ),
                                        // fn (Get $get, string $operation) => new \App\Rules\ValidTeacherSchedule(
                                        //     teacherId: $get('teacher_id'),
                                        //     studentId: $get('student_id'),
                                        //     timeStart: $get('time_start'),
                                        //     timeEnd: $get('time_end'),
                                        //     date: $get('date'),
                                        //     ignoringId: $operation === 'edit' ? $get('id') : null
                                        // )
                                    ]),
                                Select::make('status')
                                    ->options([
                                        'scheduled' => 'Scheduled',
                                        'completed' => 'Completed',
                                        'cancelled' => 'Cancelled',
                                    ])
                                    ->required()
                                    ->reactive()
                                    ->hiddenOn('edit'),
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
            // 🎨 Kolom Status dengan Pewarnaan Dinamis
            TextColumn::make('status')
                ->badge() // Wajib menggunakan badge() untuk mewarnai teks di dalamnya
                ->color(fn (string $state): string => match ($state) {
                    'completed' => 'success', // Hijau
                    'cancelled' => 'danger',  // Merah
                    'scheduled' => 'primary', // Biru (Warna default utama Filament)
                    default => 'secondary', // Opsional: Untuk status yang tidak didefinisikan
                }),
        ])
        ->filters([
            Tables\Filters\Filter::make('this_week')
                ->label('Minggu Ini')
                ->query(fn ($query) =>
                    $query->whereBetween('date', [now()->startOfWeek(), now()->endOfWeek()])
                ),

            Tables\Filters\Filter::make('this_month')
                ->label('Bulan Ini')
                ->query(fn ($query) =>
                    $query->whereMonth('date', now()->month)
                        ->whereYear('date', now()->year)
                ),
        ])

        ->actions([
            Tables\Actions\EditAction::make()// ... (konfigurasi Edit Action)
                ->visible(fn (ClassSchedule $record): bool => $record->status === 'scheduled')
                ->after(function ($livewire) {
                    // Memicu event refresh setelah action Edit berhasil
                    $livewire->dispatch('refreshTabsAndTable');
                }),
                Action::make('complete_class')
                    ->label('Complete Class')
                    ->icon('heroicon-m-check-circle')
                    ->color('success')
                    ->visible(fn (ClassSchedule $record): bool => $record->status === 'scheduled')
                    // 1. Mount Data (Agar field terisi jika sudah pernah di-input sebelumnya)
                    ->mountUsing(fn (ClassSchedule $record, Forms\ComponentContainer $form) => $form->fill([
                        'status' => $record->status, // Ambil status saat ini
                        'classReport' => $record->classReport?->toArray(), // Load data report jika ada
                    ]))
                    // 2. Schema Form (Seperti kode Anda)
                    ->form([
                        Forms\Components\Select::make('status')
                            ->options([
                                'scheduled' => 'Scheduled',
                                'completed' => 'Completed',
                                'cancelled' => 'Cancelled',
                            ])
                            ->required()
                            ->live(), // Gunakan live untuk reaktivitas

                        Forms\Components\Section::make('Class Report')
                            ->schema([
                                Forms\Components\TextInput::make('classReport.topic')
                                    ->label('Topic')
                                    ->required(fn (Forms\Get $get) => $get('status') === 'completed'),

                                Forms\Components\Textarea::make('classReport.progress')
                                    ->label('Progress'),

                                Forms\Components\Textarea::make('classReport.notes')
                                    ->label('Notes'),

                                Forms\Components\Textarea::make('classReport.teacher_feedback')
                                    ->label('Teacher Feedback'),
                            ])
                            // Visible hanya jika status completed
                            ->visible(fn (Forms\Get $get) => $get('status') === 'completed')
                            ->columns(2),
        ])
        // 3. Logic Penyimpanan (Action Handler)
        ->action(function (ClassSchedule $record, array $data) {
            // CUKUP 1 BARIS INI SAJA
            $record->completeClass($data);

            // Optional: Kasih notifikasi
            Notification::make()->title('Class completed successfully')->success()->send();
        }),
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

    public static function getEloquentQuery(): Builder
    {
        $user = auth()->user();

        // Jika user punya relasi ke teacher → berarti dia role teacher
        if ($user->teacher) {
            return parent::getEloquentQuery()
                ->where('teacher_id', $user->teacher->id);
        }

        // Jika tidak punya teacher → kemungkinan admin → tampilkan semua
        return parent::getEloquentQuery();
    }
}
