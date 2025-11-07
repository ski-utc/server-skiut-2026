<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RoomShotgunResource\Pages;
use App\Models\Room;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RoomShotgunResource extends Resource
{
    protected static ?string $model = Room::class;
    protected static ?string $navigationIcon = 'heroicon-o-numbered-list';
    protected static ?string $navigationLabel = 'Gestion des chambres';
    protected static ?string $modelLabel = 'Chambre';
    protected static ?string $pluralModelLabel = 'Chambres';
    protected static ?string $navigationGroup = 'Gestion Pré-voyage';

    public static function canViewAny(): bool
    {
        return session('admin') === true;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informations de la chambre')
                    ->schema([
                        TextInput::make('roomNumber')
                            ->label('Numéro de chambre')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->numeric(),

                        TextInput::make('capacity')
                            ->label('Nombre de places')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(10),

                        TextInput::make('name')
                            ->label('Nom de la chambre')
                            ->maxLength(255)
                            ->nullable(),

                        TextInput::make('mood')
                            ->label('Ambiance')
                            ->maxLength(255)
                            ->nullable()
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('roomNumber')
                    ->label('Numéro')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('name')
                    ->label('Nom')
                    ->searchable()
                    ->placeholder('Non défini'),

                TextColumn::make('capacity')
                    ->label('Places')
                    ->sortable(),

                TextColumn::make('users_count')
                    ->label('Occupées')
                    ->counts('users')
                    ->sortable(),

                BadgeColumn::make('mood')
                    ->label('Ambiance')
                    ->colors([
                        'danger' => 'mega grosse night',
                        'warning' => 'grosse night',
                        'info' => 'petite night',
                        'success' => 'calme',
                    ])
                    ->formatStateUsing(function (string $state): string {
                        return ucfirst($state);
                    })
                    ->placeholder('Non défini'),

                TextColumn::make('respUser.email')
                    ->label('Responsable')
                    ->searchable()
                    ->placeholder('Non défini'),

                BadgeColumn::make('status')
                    ->label('Statut')
                    ->getStateUsing(function (Room $record): string {
                        if ($record->isLocked()) {
                            return 'Bloquée';
                        }
                        if ($record->isFull()) {
                            return 'Complète';
                        }
                        return 'Disponible';
                    })
                    ->colors([
                        'danger' => 'Bloquée',
                        'warning' => 'Complète',
                        'success' => 'Disponible',
                    ]),

                TextColumn::make('locked_until')
                    ->label('Bloquée jusqu\'à')
                    ->dateTime()
                    ->placeholder('Non bloquée')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('mood')
                    ->options([
                        'mega grosse night' => 'Mega grosse night',
                        'grosse night' => 'Grosse night',
                        'petite night' => 'Petite night',
                        'calme' => 'Calme',
                    ]),

                Tables\Filters\TernaryFilter::make('is_available')
                    ->label('Disponible')
                    ->queries(
                        true: fn ($query) => $query->whereDoesntHave('users')->where(function ($q) {
                            $q->whereNull('locked_until')->orWhere('locked_until', '<=', now());
                        }),
                        false: fn ($query) => $query->where(function ($q) {
                            $q->whereHas('users')->orWhere('locked_until', '>', now());
                        }),
                    ),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('Aucune chambre disponible')
            ->emptyStateDescription('Aucune chambre n\'est disponible pour le moment')
            ->emptyStateIcon('heroicon-o-face-frown');
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
            'index' => Pages\ListRoomShotguns::route('/'),
            'create' => Pages\CreateRoomShotgun::route('/create'),
            'view' => Pages\ViewRoomShotgun::route('/{record}'),
            'edit' => Pages\EditRoomShotgun::route('/{record}/edit'),
        ];
    }
}
