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

        $record = $form->getRecord();
        $recordId = $record?->id;

        return $form
            ->schema([
                Select::make('student_id')
                    ->label('Student')
                    ->relationship('student.user', 'name')
                    ->searchable()
                    ->required(),

                Select::make('package_id')
                    ->relationship('package', 'name')
                    ->required()
                    ->searchable()
                    ->reactive()
                    // ← JALAN SAAT EDIT (hydrate)
                    ->afterStateHydrated(function ($state, callable $set) {
                        if (!$state) return;
                        $package = \App\Models\Package::find($state);
                        if (!$package) return;

                        // Jika package type monthly → tampilkan total_quota
                        $set('show_total_quota', $package->type === 'monthly');
                    })
                    ->afterStateUpdated(function ($state, callable $set, $get) {
                        $package = \App\Models\Package::find($state);
                        if (!$package) return;

                        if ($package->type === 'monthly') {
                            // Jika start_date belum ada, set sekarang
                            $startDate = $get('start_date') ?: now()->format('Y-m-d');
                            $set('start_date', $startDate);
                            // end_date = start_date + 1 bulan
                            $set('end_date', Carbon::parse($startDate)->addMonth()->format('Y-m-d'));
                            // tampilkan total_quota
                            $set('show_total_quota', true);
                        } else {
                            // Hide total_quota untuk type selain monthly
                            $set('show_total_quota', false);
                            // Bisa set end_date default untuk quota (misal sama dengan start_date)
                            $set('end_date', null);
                        }
                    })
                    ->rules([
                        function ($get) use ($recordId) {
                            return function (string $attribute, $value, \Closure $fail) use ($get, $recordId) {

                                $studentId = $get('student_id');
                                $packageId = $value;

                                if (!$studentId || !$packageId) return;

                                $newPackage = \App\Models\Package::find($packageId);
                                if (!$newPackage) return;

                                $newType = $newPackage->type;

                                $query = \App\Models\StudentPackage::where('student_id', $studentId)
                                    ->whereHas('package', function ($query) use ($newType) {
                                        $query->where('type', $newType);
                                    });

                                // ⭐ SKIP DIRINYA SENDIRI SAAT EDIT
                                if ($recordId) {
                                    $query->where('id', '!=', $recordId);
                                }

                                if ($query->exists()) {
                                    $typeName = $newType === 'quota' ? 'Kuota' : 'Bulanan';
                                    $fail("Student ini sudah memiliki package dengan tipe {$typeName}.");
                                }
                            };
                        },
                    ]),

                DatePicker::make('start_date')
                    ->required()
                    ->reactive()
                    ->afterStateHydrated(function ($state, callable $set) {
                        if (!$state) {
                            $set('start_date', now()->format('Y-m-d')); // default hari ini
                        }
                    })
                    ->displayFormat('m/d/Y'),

                DatePicker::make('end_date')
                    ->reactive()
                    ->displayFormat('m/d/Y')
                    ->disabled()
                    ->dehydrated(true),

                TextInput::make('total_quota')
                    ->numeric()
                    ->minValue(0)
                    ->hidden(fn ($get) => !$get('show_total_quota')),


                TextInput::make('remaining_quota')
                    ->numeric()
                    ->minValue(0)
                    ->hidden(),
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

                Tables\Columns\TextColumn::make('used_quota'),
                Tables\Columns\TextColumn::make('remaining_quota'),
                Tables\Columns\BadgeColumn::make('status_topup')
                    ->label('Status')
                    ->getStateUsing(function ($record) {
                        if ($record->remaining_quota === null) {
                            return 'No Data';
                        }

                        return $record->remaining_quota <= 5
                            ? 'Low Quota'
                            : 'Sufficient Quota';
                    })
                    ->colors([
                        'danger' => fn ($state) => $state === 'Low Quota',
                        'success' => fn ($state) => $state === 'Sufficient Quota',
                    ])
                    ->icons([
                        'heroicon-o-exclamation-circle' => 'Low Quota',
                        'heroicon-o-check-circle' => 'Sufficient Quota'
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
