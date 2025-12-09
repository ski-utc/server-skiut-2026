<?php

namespace App\Filament\Resources\RoomShotgunSelectionResource\Pages;

use App\Filament\Resources\RoomShotgunSelectionResource;
use App\Models\PartialRoomShotgun;
use App\Models\Shotguns;
use App\Models\UserRoomShotgun;
use Filament\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ListRoomShotgunSelections extends ListRecords
{
    protected static string $resource = RoomShotgunSelectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('create_partial_room')
                ->label('Je n\'ai pas trouvé de chambre adaptée')
                ->icon('heroicon-o-face-frown')
                ->color('primary')
                ->visible(function () {
                    $email = session('email');
                    $existingAssignment = UserRoomShotgun::where('email', $email)->first();
                    return !$existingAssignment;
                })
                ->form(function () {
                    $email = session('email');

                    return [
                        Section::make('Informations de la chambre')
                            ->schema([
                                TextInput::make('name')
                                    ->label('Nom de la chambre')
                                    ->required(),

                                Select::make('ambiance')
                                    ->label('Ambiance de la chambre')
                                    ->options([
                                        'mega grosse night' => 'Mega grosse night',
                                        'grosse night' => 'Grosse night',
                                        'petite night' => 'Petite night',
                                        'calme' => 'Calme',
                                    ])
                                    ->nullable(),
                            ])
                            ->columns(2),

                        Section::make('Participant·e·s')
                            ->schema([
                                Section::make()
                                    ->schema([
                                        Grid::make(6)
                                            ->schema([
                                                Select::make('responsable_email')
                                                    ->label('Responsable de la chambre')
                                                    ->options(function () {
                                                        $usedEmails = UserRoomShotgun::pluck('email')->toArray();
                                                        return Shotguns::whereNotIn('email', $usedEmails)->pluck('email', 'email');
                                                    })
                                                    ->searchable()
                                                    ->required()
                                                    ->default(session('email'))
                                                    ->columnSpan(5),

                                                Checkbox::make('responsable_is_vegetarian')
                                                    ->label('Végé')
                                                    ->inline(false)
                                                    ->default(false)
                                                    ->extraAttributes(['style' => 'transform: scale(1.5); margin-top: 10px;'])
                                                    ->columnSpan(1),
                                            ]),
                                    ])
                                    ->columnSpan('full'),

                                Repeater::make('participants')
                                    ->label('Autres participant·e·s')
                                    ->schema([
                                        Grid::make(6)
                                            ->schema([
                                                Select::make('email')
                                                    ->label('Email du·de la participant·e')
                                                    ->options(function (Get $get) {
                                                        $responsableEmail = $get('../../responsable_email');
                                                        $participants = collect($get('../../participants') ?? [])->pluck('email')->toArray();
                                                        $usedEmails = UserRoomShotgun::pluck('email')->toArray();

                                                        $excludedEmails = array_filter(array_merge($usedEmails, $participants, [$responsableEmail]));

                                                        return Shotguns::whereNotIn('email', $excludedEmails)->pluck('email', 'email');
                                                    })
                                                    ->searchable()
                                                    ->required()
                                                    ->columnSpan(5),

                                                Checkbox::make('is_vegetarian')
                                                    ->label('Végé')
                                                    ->inline(false)
                                                    ->default(false)
                                                    ->extraAttributes(['style' => 'transform: scale(1.5); margin-top: 10px;'])
                                                    ->columnSpan(1),
                                            ]),
                                    ])
                                    ->addActionLabel('Ajouter un·e participant·e')
                                    ->collapsible()
                                    ->defaultItems(0)
                                    ->columns(1),
                            ]),
                    ];
                })
                ->action(function (array $data) {
                    self::handlePartialRoomCreation($data);
                }),
        ];
    }

    protected static function handlePartialRoomCreation(array $data): void
    {
        try {
            DB::beginTransaction();

            Log::info('handlePartialRoomCreation called with data:', $data);

            $email = session('email');
            $existingAssignment = UserRoomShotgun::where('email', $email)->first();
            if ($existingAssignment) {
                DB::rollBack();
                Notification::make()
                    ->title('Vous êtes déjà dans une chambre.')
                    ->body('Impossible de créer une nouvelle chambre.')
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

            $conflictingParticipants = [];
            foreach ($emails as $email) {
                $existingRoom = UserRoomShotgun::where('email', $email)->first();
                if ($existingRoom) {
                    $conflictingParticipants[] = $email;
                }
            }

            if (!empty($conflictingParticipants)) {
                DB::rollBack();
                foreach ($conflictingParticipants as $conflictEmail) {
                    Notification::make()
                        ->title('Participant.e déjà dans une chambre')
                        ->body("{$conflictEmail} a déjà été placé.e dans une autre chambre. Veuillez sélectionner une autre personne.")
                        ->warning()
                        ->duration(8000)
                        ->send();
                }
                return;
            }

            $partialRoom = PartialRoomShotgun::create([
                'nb_personas' => $emails->count(),
                'name' => $data['name'] ?? null,
                'responsable_chambre' => $data['responsable_email'],
                'ambiance' => $data['ambiance'] ?? null,
            ]);

            foreach ($participantsData as $participant) {
                if (empty($participant['email'])) {
                    continue;
                }

                UserRoomShotgun::create([
                    'partial_room_shotgun_id' => $partialRoom->id,
                    'email' => $participant['email'],
                    'is_vegetarian' => $participant['is_vegetarian'] ?? false,
                ]);
            }

            UserRoomShotgun::create([
                'partial_room_shotgun_id' => $partialRoom->id,
                'email' => $data['responsable_email'],
                'is_vegetarian' => $data['responsable_is_vegetarian'] ?? false,
            ]);

            DB::commit();

            Notification::make()
                ->title('Réservation confirmée')
                ->body('Nous reviendrons bientôt vers vous pour confirmer la réservation, et ferons de notre mieux pour respecter vos souhaits.')
                ->success()
                ->duration(10000)
                ->send();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('HandlePartialRoomCreation error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            Notification::make()
                ->title('Erreur')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
}
