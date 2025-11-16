<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PackageResource\Pages;
use App\Filament\Resources\PackageResource\RelationManagers;
use App\Models\Package;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PackageResource extends Resource
{
    protected static ?string $model = Package::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Package';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),

                Forms\Components\Select::make('type')
                    ->options([
                        'quota' => 'Quota',
                        'monthly' => 'Monthly',
                    ])
                    ->required()
                    ->reactive()   // <--- WAJIB agar UI bisa update
                    ->native(false),

                Forms\Components\TextInput::make('quota_classes')
                    ->numeric()
                    ->minValue(0)
                    ->nullable()
                    ->visible(fn ($get) => $get('type') === 'quota'),

                Forms\Components\TextInput::make('price')
                    ->numeric()
                    ->minValue(0)
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('type')
                    ->colors([
                        'primary' => 'quota',
                        'success' => 'monthly',
                    ])
                    ->sortable(),

                Tables\Columns\TextColumn::make('quota_classes')
                    ->label('Quota Classes')
                    ->sortable(),

                Tables\Columns\TextColumn::make('price')
                    ->money('idr') // atau remove jika tidak pakai money
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'quota' => 'Quota',
                        'monthly' => 'Monthly',
                    ]),
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
                // 1. Menggunakan Table\Actions\EditAction bawaan dan memastikan routing Edit masih ada
                Tables\Actions\EditAction::make()
                    ->mutateRecordDataUsing(function (array $data): array {
                        return $data;
                    })
                    // Anda juga bisa mengatur tampilan modal seperti ini:
                    ->modalWidth('xl'),
                    // Atau
                    // ->slideOver()

                // 2. Anda bisa menambahkan ViewAction juga jika diperlukan:
                // Tables\Actions\ViewAction::make()->slideOver(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListPackages::route('/'),
            // 'create' => Pages\CreatePackage::route('/create'),
            // 'edit' => Pages\EditPackage::route('/{record}/edit'),
        ];
    }
}
