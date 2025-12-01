<?php

namespace App\Filament\Resources\TeacherResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ClassScheduleRelationManager extends RelationManager
{
    protected static string $relationship = 'classSchedules';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('student.user.name')->label('Student'),
                TextColumn::make('subject.name')->label('Subject'),
                TextColumn::make('date')->date(),
                TextColumn::make('time_start'),
                TextColumn::make('time_end'),
                TextColumn::make('status')->badge(),
            ])
           ->filters([
                Tables\Filters\SelectFilter::make('schedule_range')
                    ->label('Filter Schedule')
                    ->options([
                        'daily' => 'Daily',
                        'weekly' => 'Weekly',
                        'monthly' => 'Monthly',
                    ])
                    ->query(function ($query, array $data) {
                        $value = $data['value'] ?? null;

                        return match ($value) {
                            'daily' => $query->whereDate('date', today()),

                            'weekly' => $query->whereBetween('date', [
                                now()->startOfWeek(),
                                now()->endOfWeek(),
                            ]),

                            'monthly' => $query->whereMonth('date', now()->month),

                            default => $query, // ALL
                        };
                    }),
            ])
            ->headerActions([
                // Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                // Tables\Actions\ViewAction::make(),
                // Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
