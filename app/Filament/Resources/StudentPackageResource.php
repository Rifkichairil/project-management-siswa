<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StudentPackageResource\Pages;
use App\Filament\Resources\StudentPackageResource\RelationManagers;
use App\Models\Package;
use App\Models\StudentPackage;
use Carbon\Carbon;
use Filament\Forms;
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
        return $form->schema([
            Forms\Components\Select::make('student_id')
                ->relationship('student.user', 'name')
                ->required()
                ->searchable(),

            Forms\Components\Select::make('package_id')
                ->relationship('package', 'name')
                ->required()
                ->searchable()
                ->reactive()
                ->afterStateUpdated(function ($state, callable $set, callable $get) {
                    if (!$state) {
                        return;
                    }

                    // Ambil data package
                    $package = \App\Models\Package::find($state);

                    if (!$package) {
                        return;
                    }

                    if ($package->type === 'quota') {
                        // auto ambil quota
                        $quotaValue = $package->quota_classes;

                        $set('total_quota', $package->quota_classes);
                        $set('type', true);    // tanda untuk disabled di field
                        $set('remaining_quota', $package->quota_classes);
                        $set('end_date', null); // Kosongkan end_date jika tipe quota
                    } else {
                        // monthly → manual input
                        $set('total_quota', null);
                        $set('type', false);

                        // Jika sudah ada start_date, hitung end_date-nya
                        if ($get('start_date')) {
                            try {
                                $startDate = Carbon::parse($get('start_date'));
                                $endDate = $startDate->copy()->addMonth(); // Tambah 1 bulan
                                $set('end_date', $endDate->format('Y-m-d'));
                            } catch (\Exception $e) {
                                // Ignore error
                            }
                        }
                    }
                })
                ->rules([
                    function ($get) {
                        return function (string $attribute, $value, \Closure $fail) use ($get) {
                            $studentId = $get('student_id');
                            $packageId = $value;

                            // Pastikan studentId sudah dipilih
                            if (!$studentId || !$packageId) {
                                return;
                            }

                            $newPackageType = \App\Models\Package::find($packageId)?->type;

                            if (!$newPackageType) {
                                return;
                            }

                            $hasExistingPackage = \App\Models\StudentPackage::where('student_id', $studentId)
                                ->whereHas('package', function ($query) use ($newPackageType) {
                                    $query->where('type', $newPackageType);
                                })
                                ->exists();

                            if ($hasExistingPackage) {
                                $typeName = $newPackageType === 'quota' ? 'Quota' : 'Monthly';
                                $fail("Student ini sudah memiliki package dengan tipe {$typeName}.");
                            }
                        };
                    },
                ]),

           Forms\Components\DatePicker::make('start_date')
                ->required()
                ->reactive() // WAJIB agar end_date merespons perubahan ini
                ->afterStateUpdated(function ($state, callable $set, callable $get) {
                    if (!$state) {
                        $set('end_date', null); // Kosongkan jika start_date dihapus
                        return;
                    }

                    $packageId = $get('package_id');

                    if (!$packageId) {
                        return;
                    }

                    $package = \App\Models\Package::find($packageId);

                    // Logika Auto-Fill end_date
                    if ($package && $package->type === 'monthly') {
                        try {
                            $startDate = Carbon::parse($state);
                            $endDate = $startDate->copy()->addMonth(); // Tambah 1 bulan
                            $set('end_date', $endDate->format('Y-m-d'));
                        } catch (\Exception $e) {
                            $set('end_date', null);
                        }
                    } else {
                        // Jika quota, biarkan end_date null
                        $set('end_date', null);
                    }
                }),

            Forms\Components\DatePicker::make('end_date')
                ->nullable()
                // Disabled jika package_id terisi dan type-nya monthly
                ->disabled(fn ($get) =>
                    ($packageId = $get('package_id')) &&
                    ($package = \App\Models\Package::find($packageId)) &&
                    $package->type === 'monthly'
                ),
            Forms\Components\Hidden::make('type')->default(false),

            Forms\Components\TextInput::make('total_quota')
                ->numeric()
                ->disabled(fn ($get) => $get('type') === true)
                ->dehydrated(true)    // tetap dikirim ke server
                ->minValue(0),

            Forms\Components\TextInput::make('remaining_quota')
                ->numeric()
                ->disabled()       // user tidak bisa ubah
                ->dehydrated(true)    // tetap dikirim ke server
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
