<?php

namespace App\Filament\Widgets;

use App\Models\Room;
use App\Models\Shotguns;
use App\Models\User;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\DB;

class RoomShotgunAvailableWidget extends Widget
{
    protected static string $view = 'filament.widgets.room-shotgun-available-widget';
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Room::query()->where(function ($query) {
                    $query->whereDoesntHave('users')
                        ->orWhere('locked_until', '>', now());
                })
            )
            ->columns([
                TextColumn::make('roomNumber')
                    ->label('Chambre')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->size('lg'),

                TextColumn::make('name')
                    ->label('Nom')
                    ->searchable()
                    ->placeholder('Sans nom')
                    ->color('gray'),

                TextColumn::make('capacity')
                    ->label('Places')
                    ->sortable()
                    ->badge()
                    ->color('primary'),

                BadgeColumn::make('status')
                    ->label('Statut')
                    ->getStateUsing(function (Room $record): string {
                        if ($record->isLocked()) {
                            return 'En cours de réservation';
                        }
                        return 'Disponible';
                    })
                    ->colors([
                        'warning' => 'En cours de réservation',
                        'success' => 'Disponible',
                    ]),
            ])
            ->actions([
                Action::make('select')
                    ->label('Sélectionner')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->form([
                        Section::make('Informations de la chambre')
                            ->schema([
                                TextInput::make('chambre_info')
                                    ->label('Chambre sélectionnée')
                                    ->default(fn (Room $record) => "Chambre {$record->roomNumber} - {$record->capacity} places")
                                    ->disabled()
                                    ->dehydrated(false),

                                Select::make('ambiance')
                                    ->label('Ambiance de la chambre')
                                    ->options([
                                        'mega grosse night' => 'Mega grosse night',
                                        'grosse night' => 'Grosse night',
                                        'petite night' => 'Petite night',
                                        'calme' => 'Calme',
                                    ])
                                    ->required()
                                    ->default(fn (Room $record) => $record->mood),
                            ])
                            ->columns(2),

                        Section::make('Participants de la chambre')
                            ->schema([
                                Repeater::make('emails')
                                    ->label('Emails des participants')
                                    ->schema([
                                        Select::make('email')
                                            ->label('Email')
                                            ->options(function () {
                                                return Shotguns::pluck('email', 'email');
                                            })
                                            ->searchable()
                                            ->required(),

                                        Select::make('is_responsable')
                                            ->label('Responsable')
                                            ->options([
                                                true => 'Oui',
                                                false => 'Non',
                                            ])
                                            ->required()
                                            ->default(false),
                                    ])
                                    ->columns(2)
                                    ->addActionLabel(false)
                                    ->removeActionLabel(false)
                                    ->reorderable(false)
                                    ->collapsible(false)
                                    ->itemLabel(fn (array $state): ?string => $state['email'] ?? null)
                                    ->defaultItems(fn (Room $record) => $record->capacity)
                                    ->disabled(fn (Room $record) => $record->capacity),
                            ])
                    ])
                    ->action(function (array $data, Room $record): void {
                        try {
                            DB::beginTransaction();

                            // Vérifier que la chambre est toujours disponible
                            $room = Room::find($record->id);
                            if (!$room->isAvailable()) {
                                throw new \Exception('Cette chambre n\'est plus disponible');
                            }

                            // Bloquer la chambre
                            $room->lock('system');

                            // Vérifier que tous les emails existent dans Shotguns
                            $emails = collect($data['emails'])->pluck('email');
                            $existingEmails = Shotguns::whereIn('email', $emails)->pluck('email');
                            $missingEmails = $emails->diff($existingEmails);

                            if ($missingEmails->isNotEmpty()) {
                                throw new \Exception('Les emails suivants ne sont pas inscrits : ' . $missingEmails->implode(', '));
                            }

                            // Vérifier qu'il y a exactement un responsable
                            $responsables = collect($data['emails'])->where('is_responsable', true)->count();
                            if ($responsables !== 1) {
                                throw new \Exception('Il doit y avoir exactement un responsable de chambre');
                            }

                            // Vérifier qu'il y a le bon nombre d'emails
                            if (count($data['emails']) !== $room->capacity) {
                                throw new \Exception('Il doit y avoir exactement ' . $room->capacity . ' participants');
                            }

                            // Créer les associations chambre-utilisateur
                            foreach ($data['emails'] as $emailData) {
                                User::create([
                                    'room_id' => $record->id,
                                    'email' => $emailData['email'],
                                ]);
                            }

                            // Mettre à jour le responsable de chambre et l'ambiance
                            $responsableEmail = collect($data['emails'])->where('is_responsable', true)->first()['email'];
                            $responsableUser = User::where('email', $responsableEmail)->first();
                            $room->update([
                                'user_id' => $responsableUser ? $responsableUser->id : null,
                                'mood' => $data['mood'] ?? null,
                            ]);

                            // Débloquer la chambre
                            $room->unlock();

                            DB::commit();

                            Notification::make()
                                ->title('Réservation réussie')
                                ->success()
                                ->send();

                        } catch (\Exception $e) {
                            DB::rollBack();

                            Notification::make()
                                ->title('Erreur lors de la réservation')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
            ]);
    }
}
