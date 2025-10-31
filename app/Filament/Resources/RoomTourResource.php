<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RoomTourResource\Pages;
use App\Models\RoomTour;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RoomTourResource extends Resource
{
    protected static ?string $model = RoomTour::class;
    protected static ?string $navigationIcon = 'heroicon-o-home-modern';
    protected static ?string $navigationLabel = 'Tournée des chambres';
    protected static ?string $modelLabel = 'Tournée';
    protected static ?string $pluralModelLabel = 'Tournées des chambres';
    protected static ?string $navigationGroup = 'Gestion du voyage';

    public static function canViewAny(): bool
    {
        return session('admin') === true;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informations de la tournée')
                    ->schema([
                        DatePicker::make('tour_date')
                            ->label('Date de la tournée')
                            ->required()
                            ->native(false),

                        Toggle::make('is_active')
                            ->label('Tournée active')
                            ->default(false)
                            ->helperText('Une seule tournée peut être active à la fois'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('tour_date')
                    ->label('Date')
                    ->date('d/m/Y')
                    ->sortable(),

                BadgeColumn::make('is_active')
                    ->label('Statut')
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Active' : 'Inactive')
                    ->colors([
                        'success' => true,
                        'gray' => false,
                    ]),

                TextColumn::make('binomes_count')
                    ->label('Binômes')
                    ->counts('binomes')
                    ->badge()
                    ->color('info'),

                TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y H:i')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Statut')
                    ->trueLabel('Actives seulement')
                    ->falseLabel('Inactives seulement')
                    ->native(false),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('tour_date', 'desc')
            ->emptyStateHeading('Aucune tournée')
            ->emptyStateDescription('Créez votre première tournée de chambres')
            ->emptyStateIcon('heroicon-o-home-modern');
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
            'index' => Pages\ListRoomTours::route('/'),
            'create' => Pages\CreateRoomTour::route('/create'),
            'edit' => Pages\EditRoomTour::route('/{record}/edit'),
        ];
    }
}
