<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ChambreSelectionResource\Pages;
use App\Models\Chambre;
use App\Models\ChambreUser;
use App\Models\Shotguns;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;

class ChambreSelectionResource extends Resource
{
    protected static ?string $model = Chambre::class;
    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static ?string $navigationLabel = 'Choisir ma chambre';
    protected static ?string $modelLabel = 'Chambre';
    protected static ?string $pluralModelLabel = 'Chambres';
    protected static ?string $navigationGroup = 'Réservation';

    public static function canViewAny(): bool
    {
        return true;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->contentGrid([
                'md' => 2,
                'xl' => 3,
            ])
            ->columns([
                Tables\Columns\Layout\Split::make([
                    Tables\Columns\Layout\Stack::make([
                        TextColumn::make('numero')
                            ->label('Chambre')
                            ->prefix('Chambre ')
                            ->weight('bold')
                            ->size('lg'),

                        TextColumn::make('nb_places')
                            ->label('Places')
                            ->suffix(' places'),

                        TextColumn::make('status')
                            ->label('Statut')
                            ->getStateUsing(
                                fn (Chambre $record) =>
                                $record->isLockedByOther(session('email')) ? 'En cours de réservation' : 'Disponible'
                            )
                            ->color(
                                fn (Chambre $record) =>
                                $record->isLockedByOther(session('email')) ? 'warning' : 'success'
                            ),
                    ]),
                ])
            ])
            ->query(
                Chambre::query()
                    ->whereDoesntHave('users')
            )
            ->actions([
                Action::make('select')
                ->label('Choisir cette chambre')
                ->icon('heroicon-o-check')
                ->color('success')
                ->visible(fn (Chambre $record) => !$record->isLockedByOther(session('email')))
                //->requiresConfirmation()
                ->form(fn (Chambre $record) => self::getSelectionFormSchema($record))
                ->action(function (array $data, Chambre $record) {
                    $email = session('email');

                    if (ChambreUser::where('email', $email)->exists()) {
                        Notification::make()
                            ->title('Vous êtes déjà dans une chambre.')
                            ->body('Impossible de réserver une nouvelle chambre.')
                            ->danger()
                            ->send();
                        return;
                    }

                    Chambre::where('locked_by_email', $email)
                        ->where('id', '!=', $record->id)
                        ->update(['locked_by_email' => null, 'locked_until' => null]);

                    if (!$record->lock($email)) {
                        Notification::make()
                            ->title('Chambre déjà en cours de réservation')
                            ->danger()
                            ->send();
                        return;
                    }

                    self::handleSelection($data, $record);
                })
            ])
            ->emptyStateHeading('Aucune chambre disponible')
            ->emptyStateDescription('Aucune chambre n\'est disponible pour le moment')
            ->emptyStateIcon('heroicon-o-face-frown')
            ->paginated(false);
    }

    protected static function getSelectionFormSchema(?Chambre $record = null): array
    {
        $nbSlots = $record ? max($record->nb_places - 1, 0) : 0;
        $email = session('email');

        if (ChambreUser::where('email', $email)->exists()) {
            Notification::make()
                ->title('Vous êtes déjà dans une chambre.')
                ->body('Impossible de réserver une nouvelle chambre.')
                ->danger()
                ->send();
            return [];
        }

        Chambre::where('locked_by_email', $email)
            ->where('id', '!=', $record->id)
            ->update(['locked_by_email' => null, 'locked_until' => null]);

        if (!$record->lock($email)) {
            Notification::make()
                ->title('Chambre déjà en cours de réservation')
                ->danger()
                ->send();
            return [];
        }

        return [
            Section::make('Informations de la chambre')
                ->schema([
                    TextInput::make('chambre_info')
                        ->label('Chambre sélectionnée')
                        ->default(fn (Chambre $record) => "Chambre {$record->numero} - {$record->nb_places} places")
                        ->dehydrated(false),

                    Select::make('ambiance')
                        ->label('Ambiance de la chambre')
                        ->options([
                            'mega grosse night' => 'Mega grosse night',
                            'grosse night' => 'Grosse night',
                            'petite night' => 'Petite night',
                            'calme' => 'Calme',
                        ])
                        ->default(fn (Chambre $record) => $record->ambiance)
                        ->required(),
                ])
                ->columns(2),

            Section::make('Participant·e·s')
                ->schema([
                    Select::make('responsable_email')
                        ->label('Responsable de la chambre')
                        ->options(fn () => Shotguns::pluck('email', 'email'))
                        ->searchable()
                        ->required()
                        ->default(session('email')),

                    Grid::make(3)
                        ->schema(array_map(
                            fn ($i) => Select::make("participants.$i.email")
                                ->label('Participant·e ' . ($i + 1))
                                ->options(fn () => Shotguns::pluck('email', 'email'))
                                ->searchable()
                                ->required(),
                            range(0, $nbSlots - 1)
                        )),
                ]),
        ];
    }

    protected static function handleSelection(array $data, Chambre $record): void
    {
        try {
            if (ChambreUser::where('email', session('email'))->exists()) {
                Notification::make()
                    ->title('Vous êtes déjà dans une chambre.')
                    ->body('Vous êtes déjà inscrit·e dans une chambre. Impossible d’en choisir une autre.')
                    ->danger()
                    ->send();
                return;
            }

            $chambre = Chambre::find($record->id);
            if (!$chambre->isAvailable()) {
                Notification::make()
                    ->title('Cette chambre n\'est plus disponible.')
                    ->body('Cette chambre n\'est plus disponible pour le moment.')
                    ->danger()
                    ->send();
                return;
            }
            $emails = collect($data['participants'])->pluck('email')->push($data['responsable_email']);
            $existing = Shotguns::whereIn('email', $emails)->pluck('email');
            $missing = $emails->diff($existing);
            if ($missing->isNotEmpty()) {
                Notification::make()
                    ->title('Emails non inscrits : ' . $missing->implode(', '))
                    ->body('Les emails suivants ne sont pas inscrits : ' . $missing->implode(', '))
                    ->danger()
                    ->send();
                return;
            }

            // Vérification du nombre d'emails
            if ($emails->count() !== $chambre->nb_places) {
                Notification::make()
                    ->title("Il faut exactement {$chambre->nb_places} participant.e.s.")
                    ->body("Il faut exactement {$chambre->nb_places} participant.e.s.")
                    ->danger()
                    ->send();
                return;
            }

            // Ajout resp
            ChambreUser::create([
                'chambre_id' => $chambre->id,
                'email' => $data['responsable_email'],
            ]);

            // Ajout des autres
            foreach ($data['participants'] as $p) {
                ChambreUser::create([
                    'chambre_id' => $chambre->id,
                    'email' => $p['email'],
                ]);
            }

            // Mise à jour de la chambre
            $chambre->update([
                'responsable_chambre' => $data['responsable_email'],
                'ambiance' => $data['ambiance'],
                'locked_until' => null,
                'locked_by_email' => null,
            ]);

            DB::commit();
            $chambre->unlock();

            Notification::make()
                ->title('Réservation confirmée')
                ->success()
                ->send();
        } catch (\Exception $e) {
            DB::rollBack();
            $chambre->unlock();

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
            'index' => Pages\ListChambreSelections::route('/'),
        ];
    }
}
