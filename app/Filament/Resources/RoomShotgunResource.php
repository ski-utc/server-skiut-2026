<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RoomShotgunResource\Pages;
use App\Models\RoomShotgun;
use App\Models\UserRoomShotgun;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
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
    protected static ?string $model = RoomShotgun::class;
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
                        TextInput::make('nb_places')
                            ->label('Nombre de places')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(10),

                        TextInput::make('name')
                            ->label('Nom de la chambre')
                            ->maxLength(255)
                            ->nullable(),

                        Select::make('ambiance')
                            ->label('Ambiance')
                            ->options([
                                'mega grosse night' => 'Mega grosse night',
                                'grosse night' => 'Grosse night',
                                'petite night' => 'Petite night',
                                'calme' => 'Calme',
                            ])
                            ->nullable()
                            ->native(false),

                        TextInput::make('responsable_chambre')
                            ->label('Responsable de chambre')
                            ->email()
                            ->maxLength(255)
                            ->nullable(),
                    ])
                    ->columns(2),

                Section::make('Participant·e·s de la chambre')
                    ->schema(function ($record) {
                        if (!$record) {
                            return [
                                Placeholder::make('no_participants')
                                    ->label('')
                                    ->content('Aucun participant pour le moment. Les participants apparaîtront après la création de la chambre.')
                            ];
                        }

                        $members = UserRoomShotgun::where('room_shotgun_id', $record->id)->get();

                        if ($members->isEmpty()) {
                            return [
                                Placeholder::make('no_participants')
                                    ->label('')
                                    ->content('Aucun participant dans cette chambre pour le moment.')
                            ];
                        }

                        $membersArray = $members->toArray();

                        return [
                            Grid::make(['default' => 1, 'lg' => 2])
                                ->schema(
                                    array_map(
                                        function ($index) use ($membersArray) {
                                            $member = $membersArray[$index];
                                            return Section::make()
                                                ->schema([
                                                    Grid::make(6)
                                                        ->schema([
                                                            TextInput::make("participant_{$index}_email")
                                                                ->label('Participant·e ' . ($index + 1))
                                                                ->default($member['email'])
                                                                ->disabled()
                                                                ->dehydrated(false)
                                                                ->afterStateHydrated(function ($component) use ($member) {
                                                                    $component->state($member['email']);
                                                                })
                                                                ->columnSpan(5),

                                                            Checkbox::make("participant_{$index}_is_vegetarian")
                                                                ->label('Végé')
                                                                ->default((bool) $member['is_vegetarian'])
                                                                ->disabled()
                                                                ->dehydrated(false)
                                                                ->inline(false)
                                                                ->afterStateHydrated(function ($component) use ($member) {
                                                                    $component->state((bool) $member['is_vegetarian']);
                                                                })
                                                                ->extraAttributes(['style' => 'transform: scale(1.5); margin-top: 10px;'])
                                                                ->columnSpan(1),
                                                        ]),
                                                ])
                                                ->columnSpan(1);
                                        },
                                        range(0, count($membersArray) - 1)
                                    )
                                )
                        ];
                    })
                    ->visible(fn ($record) => $record !== null)
                    ->collapsed(false)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nom')
                    ->searchable()
                    ->placeholder('Non défini'),

                TextColumn::make('nb_places')
                    ->label('Places')
                    ->sortable(),

                TextColumn::make('users_count')
                    ->label('Occupées')
                    ->counts('users')
                    ->sortable(),

                BadgeColumn::make('ambiance')
                    ->label('Ambiance')
                    ->colors([
                        'danger' => 'mega grosse night',
                        'warning' => 'grosse night',
                        'info' => 'petite night',
                        'success' => 'calme',
                    ])
                    ->formatStateUsing(function (?string $state): string {
                        return $state ? ucfirst($state) : 'Non défini';
                    }),

                TextColumn::make('responsable_chambre')
                    ->label('Responsable')
                    ->searchable()
                    ->placeholder('Non défini')
                    ->copyable(),

                BadgeColumn::make('status')
                    ->label('Statut')
                    ->getStateUsing(function (RoomShotgun $record): string {
                        if ($record->users_count === $record->nb_places) {
                            return 'Complète';
                        } elseif ($record->users_count > 0) {
                            return 'En Cours';
                        }
                        return 'Disponible';
                    })
                    ->colors([
                        'primary' => 'En Cours',
                        'warning' => 'Disponible',
                        'success' => 'Complète',
                    ]),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('ambiance')
                    ->label('Ambiance')
                    ->options([
                        'mega grosse night' => 'Mega grosse night',
                        'grosse night' => 'Grosse night',
                        'petite night' => 'Petite night',
                        'calme' => 'Calme',
                    ])
                    ->native(false),

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

                Tables\Filters\SelectFilter::make('status')
                    ->label('Statut')
                    ->options([
                        'complete' => 'Complète',
                        'in_progress' => 'En cours',
                        'available' => 'Disponible',
                    ])
                    ->query(function ($query, $state) {
                        if ($state['value'] === 'complete') {
                            return $query->whereRaw('(SELECT COUNT(*) FROM user_room_shotguns WHERE room_shotgun_id = room_shotguns.id) = room_shotguns.nb_places');
                        } elseif ($state['value'] === 'in_progress') {
                            return $query->whereRaw('(SELECT COUNT(*) FROM user_room_shotguns WHERE room_shotgun_id = room_shotguns.id) > 0')
                                ->whereRaw('(SELECT COUNT(*) FROM user_room_shotguns WHERE room_shotgun_id = room_shotguns.id) < room_shotguns.nb_places');
                        } elseif ($state['value'] === 'available') {
                            return $query->whereDoesntHave('users');
                        }
                    })
                    ->native(false),
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
            ->emptyStateHeading('Aucune chambre')
            ->emptyStateDescription('Commencez par créer des chambres')
            ->emptyStateIcon('heroicon-o-home');
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
