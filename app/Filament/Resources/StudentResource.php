<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StudentResource\Pages;
use App\Filament\Resources\StudentResource\RelationManagers;
use App\Models\Student;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class StudentResource extends Resource
{
    protected static ?string $model = Student::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

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

                // quota
                Forms\Components\Section::make('Student Package (Quota)')
                    ->schema([

                        Forms\Components\Select::make('package_id')
                            ->label('Package')
                            ->relationship('studentPackages.package', 'name')
                            ->required()
                            ->hidden(fn ($livewire) => $livewire instanceof Pages\EditStudent),

                        Forms\Components\DatePicker::make('start_date')
                            ->label('Start Date')
                            ->required()
                            ->hidden(fn ($livewire) => $livewire instanceof Pages\EditStudent),

                        Forms\Components\TextInput::make('total_quota')
                            ->numeric()
                            ->label('Total Quota')
                            ->required()
                            ->hidden(fn ($livewire) => $livewire instanceof Pages\EditStudent),

                        Forms\Components\TextInput::make('remaining_quota')
                            ->numeric()
                            ->label('Remaining Quota')
                            ->default(fn (callable $get) => $get('total_quota'))
                            ->required()
                            ->hidden(fn ($livewire) => $livewire instanceof Pages\EditStudent),

                    ])
                    ->visible(fn ($livewire) => $livewire instanceof Pages\CreateStudent)
                    ->columns(2), // hanya di CREATE
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
            'create' => Pages\CreateStudent::route('/create'),
            'edit' => Pages\EditStudent::route('/{record}/edit'),
        ];
    }
}
