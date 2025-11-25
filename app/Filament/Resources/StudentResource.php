<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StudentResource\Pages;
use App\Filament\Resources\StudentResource\RelationManagers;
use App\Models\Student;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Barryvdh\DomPDF\Facade\Pdf;

use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class StudentResource extends Resource
{
    protected static ?string $model = Student::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Account';
    protected static ?int $navigationSort = 2;



    public static function form(Form $form): Form
    {
        return $form
           ->schema([
                // User fields (for auto create)
                Forms\Components\Section::make('Users Account')
                    ->schema([
                       Forms\Components\TextInput::make('name')
                            ->label('Name')
                            ->required(),

                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required(),
                        Forms\Components\TextInput::make('password')
                            ->label('Password')
                            ->password()
                            ->required(fn ($livewire) => $livewire instanceof Pages\CreateStudent)
                            ->hidden(fn ($livewire) => $livewire instanceof Pages\EditStudent)
                            ->visibleOn('create') // ⬅⬅⬅ penting!
                            ->maxLength(255),

                    ])->columns(3), // <<<<<<<<<< GRID 2 KOLOM

                // student fields
                Forms\Components\Section::make('Student Details')
                    ->schema([
                       Forms\Components\TextInput::make('school')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('grade')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('parent_name')
                            ->label('Parent Name')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('parent_contact')
                            ->label('Parent Contact')
                            ->maxLength(255),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')->label('Name')->searchable(),
                Tables\Columns\TextColumn::make('school')
                    ->searchable(),
                Tables\Columns\TextColumn::make('grade')
                    ->searchable(),
                Tables\Columns\TextColumn::make('parent_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('parent_contact')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Action::make('downloadPdf')
                    ->label('PDF')
                    ->icon('heroicon-o-document-text')
                    ->action(function ($record) {
                        // dd($record->user->name); // test dulu
                        $totalPackage   = $record->studentPackages?->sum('total_quota') ?? 0;
                        $usedQuota      = $record->studentPackages?->sum('used_quota') ?? 0;
                        $remainingQuota = $totalPackage - $usedQuota;

                        $classHistory = $record->classSchedules()
                            ->with(['classReport', 'teacher', 'subject'])
                            ->where('status', 'completed')
                            ->get();

                        $pdf = Pdf::loadView('reports.student', [
                            'student'        => $record,
                            'classHistory'   => $classHistory,
                            'totalPackage'   => $totalPackage,
                            'usedQuota'      => $usedQuota,
                            'remainingQuota' => $remainingQuota,
                        ]);

                        return response()->streamDownload(
                            fn () => print($pdf->output()),
                            'Report-' . $record->user->name . '-' . now()->format('Y-m-d') . '.pdf'
                        );
                    }),



            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
            'index' => Pages\ListStudents::route('/'),
            // 'create' => Pages\CreateStudent::route('/create'),
            // 'edit' => Pages\EditStudent::route('/{record}/edit'),
        ];
    }
}
