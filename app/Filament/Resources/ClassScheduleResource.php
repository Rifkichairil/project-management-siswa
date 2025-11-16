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
                    ->required(),


                Select::make('subject_id')
                    ->label('Subject')
                    ->relationship('subject', 'name')
                    ->searchable()
                    ->required(),


                DatePicker::make('date')
                    ->required(),


                TimePicker::make('time_start')
                    ->required(),


                TimePicker::make('time_end')
                    ->required(),


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
            'create' => Pages\CreateClassSchedule::route('/create'),
            'edit' => Pages\EditClassSchedule::route('/{record}/edit'),
        ];
    }
}
