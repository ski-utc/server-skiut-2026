<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransportResource\Pages;
use App\Models\Transport;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
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
                Forms\Components\Section::make('Informations du transport')
                    ->schema([
                        Forms\Components\Select::make('type')
                            ->label('Type de trajet')
                            ->options([
                                'aller' => 'Aller',
                                'retour' => 'Retour',
                            ])
                            ->required()
                            ->native(false)
                            ->live()
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                if ($state === 'aller') {
                                    $set('departure', 'Compiègne');
                                    $set('arrival', 'Pas de la Casa');
                                } elseif ($state === 'retour') {
                                    $set('departure', 'Pas de la Casa');
                                    $set('arrival', 'Compiègne');
                                }
                            }),

                        Forms\Components\ColorPicker::make('colour')
                            ->label('Couleur')
                            ->required()
                            ->helperText('Couleur d\'identification du transport'),

                        Forms\Components\TextInput::make('colourName')
                            ->label('Nom du transport')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Bleu, Rouge...'),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Départ')
                    ->schema([
                        Forms\Components\Select::make('departure')
                            ->label('Lieu de départ')
                            ->required()
                            ->options(function (Forms\Get $get) {
                                $type = $get('type');
                                if ($type === 'aller') {
                                    return ['Compiègne' => 'Compiègne', 'Paris' => 'Paris'];
                                } elseif ($type === 'retour') {
                                    return ['Pas de la Casa' => 'Pas de la Casa'];
                                }
                                return ['Compiègne' => 'Compiègne', 'Paris' => 'Paris', 'Pas de la Casa' => 'Pas de la Casa'];
                            })
                            ->native(false)
                            ->live(),

                        Forms\Components\TimePicker::make('horaire_depart')
                            ->label('Heure de départ')
                            ->seconds(false)
                            ->native(false),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Arrivée')
                    ->schema([
                        Forms\Components\Select::make('arrival')
                            ->label('Lieu d\'arrivée')
                            ->required()
                            ->options(function (Forms\Get $get) {
                                $type = $get('type');
                                if ($type === 'aller') {
                                    return ['Pas de la Casa' => 'Pas de la Casa'];
                                } elseif ($type === 'retour') {
                                    return ['Compiègne' => 'Compiègne', 'Paris' => 'Paris'];
                                }
                                return ['Compiègne' => 'Compiègne', 'Paris' => 'Paris', 'Pas de la Casa' => 'Pas de la Casa'];
                            })
                            ->native(false)
                            ->live(),

                        Forms\Components\TimePicker::make('horaire_arrivee')
                            ->label('Heure d\'arrivée')
                            ->seconds(false)
                            ->native(false)
                            ->after('horaire_depart'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('colourName')
                    ->label('Transport')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->formatStateUsing(fn (string $state): string => match($state) {
                        'Compiègne' => 'Compiègne',
                        'Paris' => 'Paris',
                        'Pas de la Casa' => 'Pas de la Casa',
                        default => $state
                    }),

                Tables\Columns\ColorColumn::make('colour')
                    ->label('Couleur')
                    ->sortable(),

                Tables\Columns\TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'aller' => 'success',
                        'retour' => 'warning',
                    })
                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                    ->sortable(),

                Tables\Columns\TextColumn::make('departure')
                    ->label('Départ')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-map-pin'),

                Tables\Columns\TextColumn::make('horaire_depart')
                    ->label('Heure départ')
                    ->time('H:i')
                    ->sortable()
                    ->placeholder('Non défini'),

                Tables\Columns\TextColumn::make('arrival')
                    ->label('Arrivée')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-flag'),

                Tables\Columns\TextColumn::make('horaire_arrivee')
                    ->label('Heure arrivée')
                    ->time('H:i')
                    ->sortable()
                    ->placeholder('Non défini'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Type de trajet')
                    ->options([
                        'aller' => 'Aller',
                        'retour' => 'Retour',
                    ])
                    ->native(false),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('type', 'asc')
            ->emptyStateHeading('Aucun transport')
            ->emptyStateDescription('Commencez par créer des trajets')
            ->emptyStateIcon('heroicon-o-truck');
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
