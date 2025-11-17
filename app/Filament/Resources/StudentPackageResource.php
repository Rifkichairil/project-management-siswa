<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StudentPackageResource\Pages;
use App\Filament\Resources\StudentPackageResource\RelationManagers;
use App\Models\Package;
use App\Models\Student;
use App\Models\StudentPackage;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Validation\ValidationException;

class StudentPackageResource extends Resource
{
    protected static ?string $model = StudentPackage::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Package';
    protected static ?int $navigationSort = 3;

public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('student_id')
                    ->label('Student')
                    ->relationship('student.user', 'name') // 'student' = nama relasi di model StudentPackage
                    ->searchable()
                    ->required(),

                Select::make('package_id')
                    ->label('Package')
                    ->relationship('package', 'name') // 'package' = nama relasi di model StudentPackage
                    ->searchable()
                    ->required(),

                DatePicker::make('start_date')
                    ->required(),

                DatePicker::make('end_date'),

                TextInput::make('total_quota')
                    ->numeric()
                    ->minValue(0),

                TextInput::make('remaining_quota')
                    ->numeric()
                    ->minValue(0),
            ]);
    }



    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('student.user.name')
                    ->label('Student')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('package.name')
                    ->label('Package')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('start_date')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('end_date')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_quota'),

                Tables\Columns\TextColumn::make('remaining_quota'),
                Tables\Columns\BadgeColumn::make('status_topup')
                    ->label('Status')
                    ->getStateUsing(function ($record) {
                        if ($record->remaining_quota === null) {
                            return 'Tidak ada data';
                        }

                        return $record->remaining_quota <= 5
                            ? 'Perlu Topup'
                            : 'Aman';
                    })
                    ->colors([
                        'danger' => fn ($state) => $state === 'Perlu Topup',
                        'success' => fn ($state) => $state === 'Aman',
                    ])
                    ->icons([
                        'heroicon-o-exclamation-circle' => 'Perlu Topup',
                        'heroicon-o-check-circle' => 'Aman',
                    ]),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('package_id')
                    ->relationship('package', 'name'),

                Tables\Filters\TernaryFilter::make('end_date')
                    ->label('Active Only')
                    ->nullable()
                    ->trueLabel('Only Active')
                    ->falseLabel('Only Ended'),
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
                 Tables\Actions\EditAction::make()
                    ->mutateRecordDataUsing(function (array $data): array {
                        return $data;
                    })
                    ->modalWidth('5xl'),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStudentPackages::route('/'),
            // 'create' => Pages\CreateStudentPackage::route('/create'),
            // 'edit' => Pages\EditStudentPackage::route('/{record}/edit'),
        ];
    }
}
