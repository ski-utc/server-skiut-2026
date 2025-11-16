<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ChallengeResource\Pages;
use App\Models\Challenge;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ChallengeResource extends Resource
{
    protected static ?string $model = Challenge::class;
    protected static ?string $navigationIcon = 'heroicon-o-trophy';
    protected static ?string $navigationLabel = 'Défis';
    protected static ?string $modelLabel = 'Défis';
    protected static ?string $pluralModelLabel = 'Défis';
    protected static ?string $navigationGroup = 'Gestion du voyage';

    public static function canViewAny(): bool
    {
        return session('admin') === true;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informations du défi')
                    ->schema([
                        TextInput::make('title')
                            ->label('Nom du défi')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('nbPoints')
                            ->label('Points attribués')
                            ->numeric()
                            ->default(0)
                            ->helperText('Nombre de points gagnés en réussissant ce défi'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Défi')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                TextColumn::make('nbPoints')
                    ->label('Points')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color('primary'),
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
            ->defaultSort('nbPoints', 'desc')
            ->emptyStateHeading('Aucun défi')
            ->emptyStateDescription('Créez votre premier défi pour le voyage')
            ->emptyStateIcon('heroicon-o-trophy');
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
            'index' => Pages\ListChallenges::route('/'),
            'create' => Pages\CreateChallenge::route('/create'),
            'edit' => Pages\EditChallenge::route('/{record}/edit'),
        ];
    }
}
