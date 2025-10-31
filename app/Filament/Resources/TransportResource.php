<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransportResource\Pages;
use App\Models\Transport;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TransportResource extends Resource
{
    protected static ?string $model = Transport::class;
    protected static ?string $navigationIcon = 'heroicon-o-truck';
    protected static ?string $navigationLabel = 'Transports';
    protected static ?string $modelLabel = 'Transport';
    protected static ?string $pluralModelLabel = 'Transports';
    protected static ?string $navigationGroup = 'Gestion du voyage';

    public static function canViewAny(): bool
    {
        return session('admin') === true;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informations du transport')
                    ->schema([
                        TextInput::make('departure')
                            ->label('Départ')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('arrival')
                            ->label('Arrivée')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('colour')
                            ->label('Couleur (code)')
                            ->maxLength(255)
                            ->nullable(),

                        TextInput::make('colourName')
                            ->label('Nom de la couleur')
                            ->maxLength(255)
                            ->nullable(),

                        Select::make('type')
                            ->label('Type')
                            ->options([
                                'aller' => 'Aller',
                                'retour' => 'Retour',
                            ])
                            ->required(),

                        TimePicker::make('horaire_depart')
                            ->label('Heure de départ')
                            ->native(false)
                            ->nullable(),

                        TimePicker::make('horaire_arrivee')
                            ->label('Heure d\'arrivée')
                            ->native(false)
                            ->nullable(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('departure')
                    ->label('Départ')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('arrival')
                    ->label('Arrivée')
                    ->searchable()
                    ->sortable(),

                BadgeColumn::make('type')
                    ->label('Type')
                    ->formatStateUsing(fn (string $state): string => $state === 'aller' ? 'Aller' : 'Retour')
                    ->colors([
                        'primary' => 'aller',
                        'warning' => 'retour',
                    ]),

                TextColumn::make('colourName')
                    ->label('Couleur')
                    ->searchable()
                    ->badge()
                    ->color('info'),

                TextColumn::make('horaire_depart')
                    ->label('Départ')
                    ->time('H:i')
                    ->sortable(),

                TextColumn::make('horaire_arrivee')
                    ->label('Arrivée')
                    ->time('H:i')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y H:i')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Type')
                    ->options([
                        'aller' => 'Aller',
                        'retour' => 'Retour',
                    ]),
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
            ->defaultSort('horaire_depart')
            ->emptyStateHeading('Aucun transport')
            ->emptyStateDescription('Ajoutez les transports pour le voyage')
            ->emptyStateIcon('heroicon-o-truck');
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
            'index' => Pages\ListTransports::route('/'),
            'create' => Pages\CreateTransport::route('/create'),
            'edit' => Pages\EditTransport::route('/{record}/edit'),
        ];
    }
}
