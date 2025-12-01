<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TeacherResource\Pages;
use App\Filament\Resources\TeacherResource\RelationManagers;
use App\Filament\Resources\TeacherResource\RelationManagers\ClassScheduleRelationManager;
use App\Models\Teacher;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TeacherResource extends Resource
{
    protected static ?string $model = Teacher::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Account';
    protected static ?int $navigationSort = 1;



    public static function form(Form $form): Form
    {
        return $form
           ->schema([
                // User fields (for auto create)
                Forms\Components\Section::make('User Account')
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
                            ->required()
                            ->visibleOn('create'), // ⬅⬅⬅ penting!

                    ])->columns(3), // <<<<<<<<<< GRID 2 KOLOM

                // Teacher fields
                Forms\Components\Section::make('Teacher Details')
                    ->schema([
                        Forms\Components\TextInput::make('expertise')
                            ->required(),

                        Forms\Components\TextInput::make('curriculum')
                            ->required(),

                        Forms\Components\Select::make('teaching_type')
                            ->label('Teaching Type')
                            ->options([
                                'private' => 'Private Class',
                                'group'   => 'Group Class'
                            ])
                            ->preload()
                            ->default(fn ($record) => $record?->teaching_type) // Penting
                            ->multiple()        // <--- ini bikin bisa pilih lebih dari satu
                            ->required()
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Name')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('teaching_type')
                    ->badge() // Wajib menggunakan badge() untuk mewarnai teks di dalamnya
                    ->color(fn (string $state): string => match ($state) {
                        'private' => 'success', // Hijau
                        'group' => 'primary', // Biru (Warna default utama Filament)
                        'both' => 'secondary' // Biru (Warna default utama Filament)
                    }),

                Tables\Columns\TextColumn::make('user.email')
                    ->label('Email'),

                Tables\Columns\TextColumn::make('expertise'),

                Tables\Columns\TextColumn::make('curriculum'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                // -----------------------------------------------------
                // 1. ACTION: Deactivate User (Nonaktifkan Pengguna)
                // -----------------------------------------------------
                Tables\Actions\Action::make('deactivate')
                    ->label('Nonaktifkan')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn ($record): bool => $record->user->isActive ?? true) // Tampilkan jika status aktif
                    ->requiresConfirmation()
                    ->modalHeading('Nonaktifkan Akun Pengguna?')
                    ->modalDescription('Apakah Anda yakin ingin menonaktifkan akun ini? Pengguna tidak akan bisa login.')
                    ->action(function ($record) {
                        // Asumsi: status active disimpan di model User
                        $record->user->update(['isActive' => false]);

                        Notification::make()
                            ->title('Pengguna Berhasil Dinonaktifkan')
                            ->success()
                            ->send();
                    }),

                // -----------------------------------------------------
                // 2. ACTION: Activate User (Aktifkan Pengguna)
                // -----------------------------------------------------
                Tables\Actions\Action::make('activate')
                    ->label('Aktifkan')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record): bool => !($record->user->isActive ?? true)) // Tampilkan jika status tidak aktif
                    ->requiresConfirmation()
                    ->modalHeading('Aktifkan Akun Pengguna?')
                    ->modalDescription('Apakah Anda yakin ingin mengaktifkan akun ini? Pengguna akan dapat login kembali.')
                    ->action(function ($record) {
                        // Asumsi: status active disimpan di model User
                        $record->user->update(['isActive' => true]);

                        Notification::make()
                            ->title('Pengguna Berhasil Diaktifkan')
                            ->success()
                            ->send();
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
            ClassScheduleRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTeachers::route('/'),
            'create' => Pages\CreateTeacher::route('/create'),
            'edit' => Pages\EditTeacher::route('/{record}/edit'),
        ];
    }
}
