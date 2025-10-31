<?php

namespace App\Filament\Pages;

use App\Models\Room;
use App\Models\Shotguns;
use App\Models\User;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;

class ChoisirChambre extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-home-modern';
    protected static string $view = 'filament.pages.choisir-chambre';
    protected static ?string $title = 'Choisir ma chambre';
    protected static ?string $navigationLabel = 'Choisir ma chambre';
    protected static ?string $navigationGroup = 'Réservation';

    public ?array $data = [];
    public ?int $selectedRoomId = null;

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                        Section::make('Sélection de chambre')
                            ->schema([
                                Select::make('room_id')
                                    ->label('Chambre')
                                    ->options(function () {
                                        return Room::whereDoesntHave('users')
                                            ->where(function ($query) {
                                                $query->whereNull('locked_until')
                                                    ->orWhere('locked_until', '<=', now());
                                            })
                                            ->get()
                                            ->mapWithKeys(function ($room) {
                                                return [$room->id => "Chambre {$room->roomNumber} - {$room->capacity} places"];
                                            });
                                    })
                                    ->required()
                                    ->searchable()
                                    ->live()
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        $this->selectedRoomId = $state;
                                        if ($state) {
                                            $room = Room::find($state);
                                            if ($room) {
                                                $set('capacity', $room->capacity);

                                                // Créer les champs pour les emails
                                                $emails = [];
                                                for ($i = 0; $i < $room->capacity; $i++) {
                                                    $emails[] = [
                                                        'email' => '',
                                                        'is_responsable' => $i === 0,
                                                    ];
                                                }
                                                $set('emails', $emails);
                                            }
                                        }
                                    }),

                                TextInput::make('capacity')
                                    ->label('Nombre de places')
                                    ->disabled()
                                    ->dehydrated(false),
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
                            ->disabled(fn ($get) => $get('room_id')),
                    ])
                    ->visible(fn ($get) => $get('room_id')),

                Section::make('Configuration de la chambre')
                    ->schema([
                        Select::make('mood')
                            ->label('Ambiance de la chambre')
                            ->options([
                                'mega grosse night' => 'Mega grosse night',
                                'grosse night' => 'Grosse night',
                                'petite night' => 'Petite night',
                                'calme' => 'Calme',
                            ])
                            ->nullable()
                            ->default('calme'),
                    ])
                    ->visible(fn ($get) => $get('room_id')),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        try {
            DB::beginTransaction();

            // Vérifier que la chambre est toujours disponible
            $room = Room::find($data['room_id']);
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

            // Assigner les utilisateurs à la room
            foreach ($data['emails'] as $emailData) {
                $user = User::where('email', $emailData['email'])->first();
                if ($user) {
                    $user->update(['roomID' => $room->id]);
                }
            }

            // Mettre à jour le responsable de chambre et l'ambiance
            $responsableEmail = collect($data['emails'])->where('is_responsable', true)->first()['email'];
            $responsableUser = User::where('email', $responsableEmail)->first();
            $room->update([
                'userID' => $responsableUser ? $responsableUser->id : null,
                'mood' => $data['mood'] ?? null
            ]);

            // Débloquer la chambre
            $room->unlock();

            DB::commit();

            Notification::make()
                ->title('Réservation réussie')
                ->success()
                ->send();

            $this->form->fill();
            $this->selectedRoomId = null;

        } catch (\Exception $e) {
            DB::rollBack();

            Notification::make()
                ->title('Erreur lors de la réservation')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public static function canAccess(): bool
    {
        return true;
    }
}
