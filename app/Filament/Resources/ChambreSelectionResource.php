<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ChambreSelectionResource\Pages;
use App\Models\Room;
use App\Models\Shotguns;
use App\Models\User;
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
    protected static ?string $model = Room::class;
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
                        TextColumn::make('roomNumber')
                            ->label('Chambre')
                            ->prefix('Chambre ')
                            ->weight('bold')
                            ->size('lg'),

                        TextColumn::make('capacity')
                            ->label('Places')
                            ->suffix(' places'),

                        TextColumn::make('status')
                            ->label('Statut')
                            ->getStateUsing(
                                fn (Room $record) =>
                                $record->isLockedByOther(session('email')) ? 'En cours de réservation' : 'Disponible'
                            )
                            ->color(
                                fn (Room $record) =>
                                $record->isLockedByOther(session('email')) ? 'warning' : 'success'
                            ),
                    ]),
                ])
            ])
            ->query(
                Room::query()
                    ->whereDoesntHave('users')
            )
            ->actions([
                Action::make('select')
                ->label('Choisir cette chambre')
                ->icon('heroicon-o-check')
                ->color('success')
                ->visible(fn (Room $record) => !$record->isLockedByOther(session('email')))
                //->requiresConfirmation()
                ->form(fn (Room $record) => self::getSelectionFormSchema($record))
                ->action(function (array $data, Room $record) {
                    $email = session('email');

                    $user = User::where('email', $email)->first();
                    if ($user && $user->roomID) {
                        Notification::make()
                            ->title('Vous êtes déjà dans une chambre.')
                            ->body('Impossible de réserver une nouvelle chambre.')
                            ->danger()
                            ->send();
                        return;
                    }

                    Room::where('locked_by_email', $email)
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

    protected static function getSelectionFormSchema(?Room $record = null): array
    {
        $nbSlots = $record ? max($record->capacity - 1, 0) : 0;
        $email = session('email');

        $user = User::where('email', $email)->first();
        if ($user && $user->roomID) {
            Notification::make()
                ->title('Vous êtes déjà dans une chambre.')
                ->body('Impossible de réserver une nouvelle chambre.')
                ->danger()
                ->send();
            return [];
        }

        Room::where('locked_by_email', $email)
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
                        ->default(fn (Room $record) => "Chambre {$record->roomNumber} - {$record->capacity} places")
                        ->dehydrated(false),

                    Select::make('mood')
                        ->label('Ambiance de la chambre')
                        ->options([
                            'mega grosse night' => 'Mega grosse night',
                            'grosse night' => 'Grosse night',
                            'petite night' => 'Petite night',
                            'calme' => 'Calme',
                        ])
                        ->default(fn (Room $record) => $record->mood)
                        ->nullable(),

                    TextInput::make('name')
                        ->label('Nom de la chambre')
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
                                ->label('Participant·e ' . ($i + 2))
                                ->options(fn () => Shotguns::pluck('email', 'email'))
                                ->searchable()
                                ->required(),
                            range(0, $nbSlots - 1)
                        )),
                ]),
        ];
    }

    protected static function handleSelection(array $data, Room $record): void
    {
        try {
            $currentUser = User::where('email', session('email'))->first();
            if ($currentUser && $currentUser->roomID) {
                Notification::make()
                    ->title('Vous êtes déjà dans une chambre.')
                    ->body('Vous êtes déjà inscrit(e) dans une chambre. Impossible d\'en choisir une autre.')
                    ->danger()
                    ->send();
                return;
            }

            $room = Room::find($record->id);
            if (!$room->isAvailable()) {
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
            if ($emails->count() !== $room->capacity) {
                Notification::make()
                    ->title("Il faut exactement {$room->capacity} participant.e.s.")
                    ->body("Il faut exactement {$room->capacity} participant.e.s.")
                    ->danger()
                    ->send();
                return;
            }

            // Mise à jour des utilisateurs avec cette room
            foreach ($emails as $email) {
                $user = User::where('email', $email)->first();
                if ($user) {
                    $user->update(['roomID' => $room->id]);
                }
            }

            // Mise à jour du responsable dans la room
            $responsibleUser = User::where('email', $data['responsable_email'])->first();

            // Mise à jour de la chambre
            $room->update([
                'userID' => $responsibleUser ? $responsibleUser->id : null,
                'mood' => $data['mood'] ?? null,
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
