<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StudentPackageResource\Pages;
use App\Filament\Resources\StudentPackageResource\RelationManagers;
use App\Models\StudentPackage;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class StudentPackageResource extends Resource
{
    protected static ?string $model = StudentPackage::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Package';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('student_id')
                ->relationship('student.user', 'name')
                ->required()
                ->searchable(),

            Forms\Components\Select::make('package_id')
                ->relationship('package', 'name')
                ->required()
                ->searchable(),

            Forms\Components\DatePicker::make('start_date')
                ->required(),

            Forms\Components\DatePicker::make('end_date'),

            Forms\Components\TextInput::make('total_quota')
                ->numeric()
                ->minValue(0),

            Forms\Components\TextInput::make('remaining_quota')
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
                Tables\Actions\EditAction::make(),
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
            'create' => Pages\CreateStudentPackage::route('/create'),
            'edit' => Pages\EditStudentPackage::route('/{record}/edit'),
        ];
    }
}
