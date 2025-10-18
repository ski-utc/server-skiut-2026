<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ChambreSelectionResource\Pages;
use App\Models\Chambre;
use App\Models\Shotguns;
use App\Models\ChambreUser;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Section;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;

class ChambreSelectionResource extends Resource
{
    protected static ?string $model = Chambre::class;

    protected static ?string $navigationIcon = 'heroicon-o-home-modern';

    protected static ?string $navigationLabel = 'Choisir ma chambre';

    protected static ?string $modelLabel = 'Chambre';

    protected static ?string $pluralModelLabel = 'Chambres';

    protected static ?string $navigationGroup = 'Réservation';

    public static function canViewAny(): bool
    {
        return true;
    }

    public static function form(Form $form): Form
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
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('numero')
                    ->label('Chambre')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->size('lg'),
                
                TextColumn::make('nb_places')
                    ->label('Places')
                    ->sortable()
                    ->badge()
                    ->color('primary'),
                
                BadgeColumn::make('status')
                    ->label('Statut')
                    ->getStateUsing(function (Chambre $record): string {
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
            ->query(
                Chambre::query()->where(function ($query) {
                    $query->whereDoesntHave('users')
                        ->orWhere('locked_until', '>', now());
                })
            )
            ->filters([
                Tables\Filters\SelectFilter::make('ambiance')
                    ->options([
                        'mega grosse night' => 'Mega grosse night',
                        'grosse night' => 'Grosse night',
                        'petite night' => 'Petite night',
                        'calme' => 'Calme',
                    ]),
            ])
            ->actions([
                Action::make('select')
                    ->label('Sélectionner')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn (Chambre $record): bool => $record->isAvailable())
                    ->form([
                        Section::make('Informations de la chambre')
                            ->schema([
                                TextInput::make('chambre_info')
                                    ->label('Chambre sélectionnée')
                                    ->default(fn (Chambre $record) => "Chambre {$record->numero} - {$record->nb_places} places")
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
                                    ->default(fn (Chambre $record) => $record->ambiance),
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
                                    //->removeActionLabel(false)
                                    ->reorderable(false)
                                    ->collapsible(false)
                                    ->itemLabel(fn (array $state): ?string => $state['email'] ?? null)
                                    ->defaultItems(fn (Chambre $record) => $record->nb_places)
                                    ->disabled(fn (Chambre $record) => $record->nb_places),
                            ])
                    ])
                    ->action(function (array $data, Chambre $record): void {
                        try {
                            DB::beginTransaction();
                            
                            // Vérifier que la chambre est toujours disponible
                            $chambre = Chambre::find($record->id);
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
                            
                            // Vérifier qu'il y a le bon nombre d'emails
                            if (count($data['emails']) !== $chambre->nb_places) {
                                throw new \Exception('Il doit y avoir exactement ' . $chambre->nb_places . ' participants');
                            }
                            
                            // Créer les associations chambre-utilisateur
                            foreach ($data['emails'] as $emailData) {
                                ChambreUser::create([
                                    'chambre_id' => $record->id,
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
                                
                        } catch (\Exception $e) {
                            DB::rollBack();
                            
                            Notification::make()
                                ->title('Erreur lors de la réservation')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
            ])
            ->bulkActions([
                //
            ]);
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
            'index' => Pages\ListChambreSelections::route('/'),
        ];
    }
}
