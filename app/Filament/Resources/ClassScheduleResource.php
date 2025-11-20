<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClassScheduleResource\Pages;
use App\Filament\Resources\ClassScheduleResource\RelationManagers;
use App\Models\ClassSchedule;
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
                            ->relationship('student.user', 'name')
                            ->searchable()
                            ->required(),

                        Select::make('teacher_id')
                            ->label('Teacher')
                            ->relationship('teacher.user', 'name')
                            ->searchable()
                            ->required(), // <-- Field kunci untuk validasi

                        Select::make('subject_id')
                            ->label('Subject')
                            ->relationship('subject', 'name')
                            ->searchable()
                            ->required(),

                        DatePicker::make('date')
                            ->required(), // <-- Field kunci untuk validasi

                        TimePicker::make('time_start')
                            ->label('Time Start')
                            ->required()
                            ->seconds(false)        // Opsional: Menghilangkan detik (misalnya 10:00, bukan 10:00:00)
                            ->displayFormat('H:i') // Tampilkan format 24 jam (10:00)
                            ->rules([
                                function (string $attribute, $value, Closure $fail, $get) {
                                    // Ambil nilai dari field kunci
                                    $teacherId = $get('teacher_id');
                                    $date = $get('date');
                                    $timeEnd = $get('time_end');
                                    $record = $get('id'); // ID entri saat ini (untuk edit, biarkan kosong saat create)

                                    if (!$teacherId || !$date || !$timeEnd) {
                                        // Lewati validasi jika field kunci belum diisi
                                        return;
                                    }

                                    // 1. Validasi waktu mulai tidak boleh lebih dari atau sama dengan waktu selesai
                                    if ($value >= $timeEnd) {
                                        $fail("Waktu mulai harus sebelum waktu selesai.");
                                        return;
                                    }

                                    // 2. Cek tumpang tindih (Overlap Check)
                                    $overlapExists = Schedule::query()
                                        ->where('teacher_id', $teacherId)
                                        ->where('date', $date)
                                        // Abaikan record saat ini jika sedang mode Edit
                                        ->when($record, fn (Builder $query) => $query->where('id', '!=', $record))

                                        // Logika Tumpang Tindih (Exist_end > New_start AND Exist_start < New_end)
                                        ->where(function (Builder $query) use ($value, $timeEnd) {
                                            $query->where('time_end', '>', $value)
                                                  ->where('time_start', '<', $timeEnd);
                                        })
                                        ->exists();

                                    if ($overlapExists) {
                                        $fail("Guru yang dipilih sudah memiliki jadwal yang tumpang tindih pada waktu ini.");
                                    }
                                },
                            ]),

                        TimePicker::make('time_end')
                            ->label('Time End')
                            ->seconds(false)        // Opsional: Menghilangkan detik (misalnya 10:00, bukan 10:00:00)
                            ->displayFormat('H:i') // Tampilkan format 24 jam (10:00)
                            ->required()
                            ->withoutSeconds()      // HH:MM
                            ->format('H:i') // <-- KUNCI: Mengatur format Flatpickr ke 24 jam
                            ->rules([
                                'date_format:H:i'
                            ]),

                        Select::make('status')
                            ->options([
                                'scheduled' => 'Scheduled',
                                'completed' => 'Completed',
                                'cancelled' => 'Cancelled',
                            ])
                            ->required(),
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
