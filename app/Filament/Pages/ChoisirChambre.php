<?php

namespace App\Filament\Pages;

use App\Models\Chambre;
use App\Models\ChambreUser;
use App\Models\Shotguns;
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
    public ?int $selectedChambreId = null;

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
                                Select::make('chambre_id')
                                    ->label('Chambre')
                                    ->options(function () {
                                        return Chambre::whereDoesntHave('users')
                                            ->where(function ($query) {
                                                $query->whereNull('locked_until')
                                                    ->orWhere('locked_until', '<=', now());
                                            })
                                            ->get()
                                            ->mapWithKeys(function ($chambre) {
                                                return [$chambre->id => "Chambre {$chambre->numero} - {$chambre->nb_places} places"];
                                            });
                                    })
                                    ->required()
                                    ->searchable()
                                    ->live()
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        $this->selectedChambreId = $state;
                                        if ($state) {
                                            $chambre = Chambre::find($state);
                                            if ($chambre) {
                                                $set('nb_places', $chambre->nb_places);

                                                // Créer les champs pour les emails
                                                $emails = [];
                                                for ($i = 0; $i < $chambre->nb_places; $i++) {
                                                    $emails[] = [
                                                        'email' => '',
                                                        'is_responsable' => $i === 0,
                                                    ];
                                                }
                                                $set('emails', $emails);
                                            }
                                        }
                                    }),

                                TextInput::make('nb_places')
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
                            ->disabled(fn ($get) => $get('chambre_id')),
                    ])
                    ->visible(fn ($get) => $get('chambre_id')),

                Section::make('Configuration de la chambre')
                    ->schema([
                        Select::make('ambiance')
                            ->label('Ambiance de la chambre')
                            ->options([
                                'mega grosse night' => 'Mega grosse night',
                                'grosse night' => 'Grosse night',
                                'petite night' => 'Petite night',
                                'calme' => 'Calme',
                            ])
                            ->required()
                            ->default('calme'),
                    ])
                    ->visible(fn ($get) => $get('chambre_id')),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        try {
            DB::beginTransaction();

            // Vérifier que la chambre est toujours disponible
            $chambre = Chambre::find($data['chambre_id']);
            if (!$chambre->isAvailable()) {
                throw new \Exception('Cette chambre n\'est plus disponible');
            }

            // Bloquer la chambre
            $chambre->lock('system');

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

            // Créer les associations chambre-utilisateur
            foreach ($data['emails'] as $emailData) {
                ChambreUser::create([
                    'chambre_id' => $chambre->id,
                    'email' => $emailData['email'],
                ]);
            }

            // Mettre à jour le responsable de chambre et l'ambiance
            $responsableEmail = collect($data['emails'])->where('is_responsable', true)->first()['email'];
            $chambre->update([
                'responsable_chambre' => $responsableEmail,
                'ambiance' => $data['ambiance']
            ]);

            // Débloquer la chambre
            $chambre->unlock();

            DB::commit();

            Notification::make()
                ->title('Réservation réussie')
                ->success()
                ->send();

            $this->form->fill();
            $this->selectedChambreId = null;

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
