<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RoomShotgunSelectionResource\Pages;
use App\Models\PartialRoomShotgun;
use App\Models\RoomShotgun;
use App\Models\Shotguns;
use App\Models\UserRoomShotgun;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RoomShotgunSelectionResource extends Resource
{
    protected static ?string $model = RoomShotgun::class;
    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static ?string $navigationLabel = 'Choisir ma chambre';
    protected static ?string $modelLabel = 'Chambre';
    protected static ?string $pluralModelLabel = 'Chambres';
    protected static ?string $navigationGroup = 'Espace participant.e.s';

    public static function canViewAny(): bool
    {
        return true;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->contentGrid([
                'default' => 2,
                'md' => 2,
                'xl' => 3,
            ])
            ->columns([
                Tables\Columns\Layout\Split::make([
                    Tables\Columns\Layout\Stack::make([
                        TextColumn::make('nb_places')
                            ->label('Places')
                            ->prefix('Chambre ')
                            ->suffix(' places')
                            ->weight('bold')
                            ->size('lg')
                            ->getStateUsing(function (RoomShotgun $record) {
                                $email = session('email');

                                $userPartialRoom = UserRoomShotgun::where('email', $email)
                                    ->whereNotNull('partial_room_shotgun_id')
                                    ->first();

                                if ($userPartialRoom) {
                                    return $userPartialRoom->partialRoomShotgun->nb_personas;
                                }

                                return $record->nb_places;
                            }),

                        TextColumn::make('status')
                            ->label('Statut')
                            ->getStateUsing(function (RoomShotgun $record) {
                                $email = session('email');

                                $userPartialRoom = UserRoomShotgun::where('email', $email)
                                    ->whereNotNull('partial_room_shotgun_id')
                                    ->first();
                                if ($userPartialRoom) {
                                    return 'Vous avez une chambre personnalisée';
                                }

                                $userRoomId = UserRoomShotgun::where('email', $email)
                                    ->whereNotNull('room_shotgun_id')
                                    ->value('room_shotgun_id');

                                if ($userRoomId === $record->id) {
                                    return 'Votre chambre';
                                } elseif ($record->isLockedByOther($email)) {
                                    return 'En cours de réservation';
                                } else {
                                    return 'Disponible';
                                }
                            })
                            ->color(function (RoomShotgun $record) {
                                $email = session('email');

                                $userPartialRoom = UserRoomShotgun::where('email', $email)
                                    ->whereNotNull('partial_room_shotgun_id')
                                    ->first();
                                if ($userPartialRoom) {
                                    return 'info';
                                }

                                $userRoomId = UserRoomShotgun::where('email', $email)
                                    ->whereNotNull('room_shotgun_id')
                                    ->value('room_shotgun_id');

                                if ($userRoomId === $record->id) {
                                    return 'primary';
                                } elseif ($record->isLockedByOther($email)) {
                                    return 'warning';
                                } else {
                                    return 'success';
                                }
                            }),
                    ]),
                ])
            ])
            ->query(function () {
                $email = session('email');

                $userPartialRoom = UserRoomShotgun::where('email', $email)
                    ->whereNotNull('partial_room_shotgun_id')
                    ->first();

                if ($userPartialRoom) {
                    return RoomShotgun::query()->whereNull('responsable_chambre')->limit(1);
                }

                $userRoomId = UserRoomShotgun::where('email', $email)
                    ->whereNotNull('room_shotgun_id')
                    ->value('room_shotgun_id');

                $query = RoomShotgun::query();

                if ($userRoomId) {
                    $query->where('id', $userRoomId);
                } else {
                    $query->whereNull('responsable_chambre');
                }

                return $query;
            })
            ->actions([
                Action::make('select')
                    ->label('Choisir')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(function (RoomShotgun $record) {
                        $email = session('email');

                        $existingAssignment = UserRoomShotgun::where('email', $email)->first();
                        if ($existingAssignment) {
                            return false;
                        }

                        return !$record->isLockedByOther($email);
                    })
                    ->form(fn (RoomShotgun $record) => self::getSelectionFormSchema($record))
                    ->action(function (array $data, RoomShotgun $record) {
                        self::handleSelection($data, $record);
                    }),

                Action::make('view-info')
                    ->label('Voir les infos')
                    ->icon('heroicon-o-information-circle')
                    ->color('primary')
                    ->visible(function (RoomShotgun $record) {
                        $email = session('email');

                        $userPartialRoom = UserRoomShotgun::where('email', $email)
                            ->whereNotNull('partial_room_shotgun_id')
                            ->first();
                        if ($userPartialRoom) {
                            return true;
                        }

                        $userRoomId = UserRoomShotgun::where('email', $email)
                            ->whereNotNull('room_shotgun_id')
                            ->value('room_shotgun_id');
                        return $userRoomId === $record->id;
                    })
                    ->form(function (RoomShotgun $record) {
                        $email = session('email');

                        $userPartialRoom = UserRoomShotgun::where('email', $email)
                            ->whereNotNull('partial_room_shotgun_id')
                            ->first();

                        if ($userPartialRoom) {
                            return self::getPartialRoomInfoFormSchema($userPartialRoom->partialRoomShotgun);
                        }

                        return self::getViewInfoFormSchema($record);
                    })
                    ->modalSubmitActionLabel('Fermer')
                    ->modalCancelAction(false)
                    ->action(function (array $data, RoomShotgun $record) {
                        // No-op for viewing info
                    })
            ])
            ->emptyStateHeading('Aucune chambre disponible')
            ->emptyStateDescription('Aucune chambre n\'est disponible pour le moment')
            ->emptyStateIcon('heroicon-o-face-frown')
            ->paginated(false);
    }

    protected static function getViewInfoFormSchema(?RoomShotgun $record = null): array
    {
        $email = session('email');

        $roomMembers = UserRoomShotgun::where('room_shotgun_id', $record->id)
            ->get()
            ->map(fn ($member) => $member->email . ($member->is_vegetarian ? ' (Végé)' : ''))
            ->implode("\n");

        return [
            Section::make('Informations de votre chambre')
                ->schema([
                    TextInput::make('chambre_info')
                        ->label('Chambre')
                        ->default(fn () => "Chambre {$record->nb_places} places")
                        ->dehydrated(false)
                        ->disabled(),

                    TextInput::make('name_display')
                        ->label('Nom de la chambre')
                        ->default(fn () => $record->name)
                        ->dehydrated(false)
                        ->disabled(),

                    TextInput::make('ambiance_display')
                        ->label('Ambiance')
                        ->default(fn () => $record->ambiance ?? 'Non définie')
                        ->dehydrated(false)
                        ->disabled(),
                ])
                ->columns(3),

            Section::make('Participant·e·s de la chambre')
                ->schema([
                    Textarea::make('members_list')
                        ->label('Liste des participant·e·s')
                        ->default(fn () => $roomMembers)
                        ->dehydrated(false)
                        ->disabled()
                        ->rows(5),
                ]),
            Section::make("J'aimerai être voisin.e avec...")
                ->schema([
                    TextInput::make('firstNeighbourChoice')
                        ->label('Premier choix')
                        ->default(fn () => $record->firstNeighbourChoice)
                        ->visible(fn () => $record->firstNeighbourChoice)
                        ->dehydrated(false)
                        ->disabled(),

                    TextInput::make('secondNeighbourChoice')
                        ->label('Deuxième choix')
                        ->default(fn () => $record->secondNeighbourChoice)
                        ->visible(fn () => $record->secondNeighbourChoice)
                        ->dehydrated(false)
                        ->disabled(),
                ])
                ->visible(fn () => $record->firstNeighbourChoice || $record->secondNeighbourChoice),
        ];
    }

    protected static function getPartialRoomInfoFormSchema(?PartialRoomShotgun $record = null): array
    {
        $email = session('email');

        $roomMembers = UserRoomShotgun::where('partial_room_shotgun_id', $record->id)
            ->get()
            ->map(fn ($member) => $member->email . ($member->is_vegetarian ? ' (Végé)' : ''))
            ->implode("\n");

        return [
            Section::make('Informations de votre chambre personnalisée')
                ->schema([
                    TextInput::make('chambre_info')
                        ->label('Type de chambre')
                        ->default(fn () => "Chambre personnalisée - {$record->nb_personas} participant·e·s")
                        ->dehydrated(false)
                        ->disabled(),

                    TextInput::make('name_display')
                        ->label('Nom de la chambre')
                        ->default(fn () => $record->name)
                        ->dehydrated(false)
                        ->disabled(),

                    TextInput::make('ambiance_display')
                        ->label('Ambiance')
                        ->default(fn () => $record->ambiance ?? 'Non définie')
                        ->dehydrated(false)
                        ->disabled(),
                ])
                ->columns(3),

            Section::make('Participant·e·s de la chambre')
                ->schema([
                    Textarea::make('members_list')
                        ->label('Liste des participant·e·s')
                        ->default(fn () => $roomMembers)
                        ->dehydrated(false)
                        ->disabled()
                        ->rows(5),
                ]),
            Section::make("J'aimerai être voisin.e avec...")
                ->schema([
                    TextInput::make('firstNeighbourChoice')
                        ->label('Premier choix')
                        ->default(fn () => $record->firstNeighbourChoice)
                        ->visible(fn () => $record->firstNeighbourChoice)
                        ->dehydrated(false)
                        ->disabled(),

                    TextInput::make('secondNeighbourChoice')
                        ->label('Deuxième choix')
                        ->default(fn () => $record->secondNeighbourChoice)
                        ->visible(fn () => $record->secondNeighbourChoice)
                        ->dehydrated(false)
                        ->disabled(),
                ])
                ->visible(fn () => $record->firstNeighbourChoice || $record->secondNeighbourChoice),
        ];
    }

    protected static function getSelectionFormSchema(?RoomShotgun $record = null): array
    {
        $nbSlots = $record ? max($record->nb_places - 1, 0) : 0;
        $email = session('email');

        $existingAssignment = UserRoomShotgun::where('email', $email)->first();
        if ($existingAssignment) {
            Notification::make()
                ->title('Vous êtes déjà dans une chambre.')
                ->body('Impossible de réserver une nouvelle chambre.')
                ->danger()
                ->send();
            return [];
        }

        foreach (session()->all() as $key => $value) {
            if (strpos($key, 'room_shotgun_notification_') === 0 && strpos($key, $email) !== false) {
                session()->forget($key);
            }
        }

        RoomShotgun::where('locked_by_email', $email)
            ->where('id', '!=', $record->id)
            ->update(['locked_by_email' => null, 'locked_until' => null]);

        if (!$record->lock($email)) {
            Notification::make()
                ->title('Chambre déjà en cours de réservation')
                ->danger()
                ->send();
            return [];
        }

        $usedEmails = UserRoomShotgun::where('room_shotgun_id', $record->id)
            ->pluck('email')
            ->toArray();

        $notificationKey = 'room_shotgun_notification_' . $record->id . '_' . $email;
        if (!session()->has($notificationKey)) {
            Notification::make()
                ->title('Vous avez 5 minutes pour compléter la réservation.')
                ->body('Au delà de 5 minutes, la chambre sera libérée.')
                ->info()
                ->send();
            session()->put($notificationKey, true);
        }

        return [
            Section::make('Informations de la chambre')
                ->schema([
                    TextInput::make('chambre_info')
                        ->label('Chambre sélectionnée')
                        ->default(fn (RoomShotgun $record) => "Chambre {$record->nb_places} places")
                        ->dehydrated(false)
                        ->disabled(),

                    Select::make('ambiance')
                        ->label('Ambiance de la chambre')
                        ->options([
                            'mega grosse night' => 'Mega grosse night',
                            'grosse night' => 'Grosse night',
                            'petite night' => 'Petite night',
                            'calme' => 'Calme',
                        ])
                        ->default(fn (RoomShotgun $record) => $record->ambiance)
                        ->nullable()
                        ->dehydrated(true),

                    TextInput::make('name')
                        ->label('Nom de la chambre')
                        ->required()
                        ->dehydrated(true),
                ])
                ->columns(3),

            Section::make('Participant·e·s')
                ->schema([
                    Section::make()
                        ->schema([
                            Grid::make(6)
                                ->schema([
                                    Select::make('responsable_email')
                                        ->label('Responsable de la chambre')
                                        ->options(fn () => Shotguns::whereNotIn('email', $usedEmails)->pluck('email', 'email'))
                                        ->searchable()
                                        ->required()
                                        ->default(session('email'))
                                        ->dehydrated(true)
                                        ->columnSpan(5),

                                    Checkbox::make('responsable_is_vegetarian')
                                        ->label('Végé')
                                        ->inline(false)
                                        ->default(false)
                                        ->dehydrated(true)
                                        ->extraAttributes(['style' => 'transform: scale(1.5); margin-top: 10px;'])
                                        ->columnSpan(1),
                                ]),
                        ])
                        ->columnSpan('full'),

                    Grid::make(['default' => 1, 'lg' => 2])
                        ->schema(
                            array_map(
                                fn ($i) => Section::make()
                                    ->schema([
                                        Grid::make(6)
                                            ->schema([
                                                Select::make("participants.$i.email")
                                                    ->label('Participant·e ' . ($i + 2))
                                                    ->options(function (Get $get) use ($usedEmails) {
                                                        $responsableEmail = $get('responsable_email');
                                                        $participants = collect($get('participants') ?? [])->pluck('email')->toArray();

                                                        $excludedEmails = array_filter(array_merge($usedEmails, $participants, [$responsableEmail]));

                                                        return Shotguns::whereNotIn('email', $excludedEmails)->pluck('email', 'email');
                                                    })
                                                    ->searchable()
                                                    ->required()
                                                    ->dehydrated(true)
                                                    ->columnSpan(5),

                                                Checkbox::make("participants.$i.is_vegetarian")
                                                    ->label('Végé')
                                                    ->inline(false)
                                                    ->default(false)
                                                    ->dehydrated(true)
                                                    ->extraAttributes(['style' => 'transform: scale(1.5); margin-top: 10px;'])
                                                    ->columnSpan(1),
                                            ]),
                                    ])
                                    ->columnSpan(1),
                                range(0, $nbSlots - 1)
                            )
                        ),
                ]),
            Section::make("Si possible, j'aimerai être voisin.e avec...")
                ->schema([
                    Grid::make(['default' => 1, 'lg' => 2])
                        ->schema([
                            TextInput::make('firstNeighbourChoice')
                                ->label('Nom de chambre de mon premier choix')
                                ->default(fn () => $record->firstNeighbourChoice)
                                ->dehydrated(false)
                                ->disabled(),
                            TextInput::make('secondNeighbourChoice')
                                ->label('Nom de chambre de mon deuxième choix')
                                ->default(fn () => $record->secondNeighbourChoice)
                                ->dehydrated(false)
                                ->disabled(),
                        ]),
                ]),
        ];
    }

    protected static function handleSelection(array $data, RoomShotgun $record): void
    {
        try {
            DB::beginTransaction();

            Log::info('handleSelection called with data:', $data);

            $existingAssignment = UserRoomShotgun::where('email', session('email'))->first();
            if ($existingAssignment) {
                DB::rollBack();
                Notification::make()
                    ->title('Vous êtes déjà dans une chambre.')
                    ->body('Impossible de réserver une nouvelle chambre.')
                    ->danger()
                    ->send();
                return;
            }

            $room = RoomShotgun::find($record->id);
            if ($room->isLockedByOther(session('email'))) {
                DB::rollBack();
                Notification::make()
                    ->title('Cette chambre n\'est plus disponible.')
                    ->body('Cette chambre n\'est plus disponible pour le moment.')
                    ->danger()
                    ->send();
                return;
            }

            if (empty($data['responsable_email'])) {
                DB::rollBack();
                Notification::make()
                    ->title('Erreur')
                    ->body('Le responsable de chambre doit être sélectionné.')
                    ->danger()
                    ->send();
                return;
            }

            $participantsData = $data['participants'] ?? [];
            $emails = collect($participantsData)->pluck('email')->push($data['responsable_email'])->filter();

            $existing = Shotguns::whereIn('email', $emails)->pluck('email');
            $missing = $emails->diff($existing);
            if ($missing->isNotEmpty()) {
                DB::rollBack();
                Notification::make()
                    ->title('Emails non inscrits : ' . $missing->implode(', '))
                    ->body('Les emails suivants ne sont pas inscrits : ' . $missing->implode(', '))
                    ->danger()
                    ->send();
                return;
            }

            if ($emails->count() !== $room->nb_places) {
                DB::rollBack();
                Notification::make()
                    ->title("Il faut exactement {$room->nb_places} participant.e.s.")
                    ->body("Il faut exactement {$room->nb_places} participant.e.s.")
                    ->danger()
                    ->send();
                return;
            }

            foreach ($participantsData as $index => $participant) {
                if (empty($participant['email'])) {
                    Log::warning('Participant email is empty at index ' . $index);
                    continue;
                }

                UserRoomShotgun::create([
                    'room_shotgun_id' => $room->id,
                    'email' => $participant['email'],
                    'is_vegetarian' => $participant['is_vegetarian'] ?? false,
                ]);
            }

            UserRoomShotgun::create([
                'room_shotgun_id' => $room->id,
                'email' => $data['responsable_email'],
                'is_vegetarian' => $data['responsable_is_vegetarian'] ?? false,
            ]);

            $room->update([
                'name' => $data['name'] ?? null,
                'responsable_chambre' => $data['responsable_email'],
                'ambiance' => $data['ambiance'] ?? null,
                'locked_until' => null,
                'locked_by_email' => null,
            ]);

            DB::commit();

            $room->unlock();

            Notification::make()
                ->title('Réservation confirmée')
                ->success()
                ->send();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('HandleSelection error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $record->unlock();

            Notification::make()
                ->title('Erreur')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
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
            'index' => Pages\ListRoomShotgunSelections::route('/'),
        ];
    }
}
