<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RoomShotgunSelectionResource\Pages;
use App\Models\RoomShotgun;
use App\Models\UserRoomShotgun;
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
use Filament\Forms\Components\Checkbox;

class RoomShotgunSelectionResource extends Resource
{
    protected static ?string $model = RoomShotgun::class;
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
                'default' => 2,
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
                                fn (RoomShotgun $record) =>
                                $record->isLockedByOther(session('email')) ? 'En cours de réservation' : 'Disponible'
                            )
                            ->color(
                                fn (RoomShotgun $record) =>
                                $record->isLockedByOther(session('email')) ? 'warning' : 'success'
                            ),
                    ]),
                ])
            ])
            ->query(
                RoomShotgun::query()
            )
            ->actions([
                Action::make('select')
                ->label('Choisir')
                ->icon('heroicon-o-check')
                ->color('success')
                ->visible(fn (RoomShotgun $record) => !$record->isLockedByOther(session('email')))
                //->requiresConfirmation()
                ->form(fn (RoomShotgun $record) => self::getSelectionFormSchema($record))
                ->action(function (array $data, RoomShotgun $record) {
                    \Log::info('Action called', ['data' => $data]);
                    self::handleSelection($data, $record);
                })
            ])
            ->emptyStateHeading('Aucune chambre disponible')
            ->emptyStateDescription('Aucune chambre n\'est disponible pour le moment')
            ->emptyStateIcon('heroicon-o-face-frown')
            ->paginated(false);
    }

    protected static function getSelectionFormSchema(?RoomShotgun $record = null): array
    {
        $nbSlots = $record ? max($record->nb_places - 1, 0) : 0;
        $email = session('email');

        // Vérifier si l'email est déjà dans une autre chambre shotgun
        $existingAssignment = UserRoomShotgun::where('email', $email)->first();
        if ($existingAssignment) {
            Notification::make()
                ->title('Vous êtes déjà dans une chambre.')
                ->body('Impossible de réserver une nouvelle chambre.')
                ->danger()
                ->send();
            return [];
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

        // Récupérer les emails déjà dans cette chambre
        $usedEmails = UserRoomShotgun::where('room_shotgun_id', $record->id)
            ->pluck('email')
            ->toArray();

        return [
            Section::make('Informations de la chambre')
                ->schema([
                    TextInput::make('chambre_info')
                        ->label('Chambre sélectionnée')
                        ->default(fn (RoomShotgun $record) => "Chambre {$record->numero} - {$record->nb_places} places")
                        ->dehydrated(false),

                    Select::make('ambiance')
                        ->label('Ambiance de la chambre')
                        ->options([
                            'mega grosse night' => 'Mega grosse night',
                            'grosse night' => 'Grosse night',
                            'petite night' => 'Petite night',
                            'calme' => 'Calme',
                        ])
                        ->default(fn (RoomShotgun $record) => $record->ambiance)
                        ->nullable(),

                    TextInput::make('name')
                        ->label('Nom de la chambre')
                        ->required(),
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

                    Grid::make(['default' => 1, 'lg' => 2])
                        ->schema(
                            array_map(
                                fn ($i) => Section::make()
                                    ->schema([
                                        Grid::make(6)
                                            ->schema([
                                                Select::make("participants.$i.email")
                                                    ->label('Participant·e ' . ($i + 2))
                                                    ->options(fn () => Shotguns::whereNotIn('email', $usedEmails)->pluck('email', 'email'))
                                                    ->searchable()
                                                    ->required()
                                                    ->columnSpan(5),

                                                Checkbox::make("participants.$i.is_vegetarian")
                                                    ->label('Végé')
                                                    ->inline(false)
                                                    ->default(false)
                                                    ->extraAttributes(['style' => 'transform: scale(1.5); margin-top: 10px;'])
                                                    ->columnSpan(1),
                                            ]),
                                    ])
                                    ->columnSpan(1),
                                range(0, $nbSlots - 1)
                            )
                        ),
                ]),
        ];
    }

    protected static function handleSelection(array $data, RoomShotgun $record): void
    {
        try {
            DB::beginTransaction();

            \Log::info('HandleSelection started', [
                'data' => $data,
                'room_shotgun_id' => $record->id
            ]);

            // Vérifier si l'email est déjà dans une chambre shotgun
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

            // Récupérer les emails des participants (incluant le responsable)
            $emails = collect($data['participants'] ?? [])->pluck('email')->push($data['responsable_email']);
            
            // Vérifier que tous les emails existent dans Shotguns
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

            // Vérification du nombre d'emails
            if ($emails->count() !== $room->nb_places) {
                DB::rollBack();
                Notification::make()
                    ->title("Il faut exactement {$room->nb_places} participant.e.s.")
                    ->body("Il faut exactement {$room->nb_places} participant.e.s.")
                    ->danger()
                    ->send();
                return;
            }

            // Créer les associations UserRoomShotgun
            foreach ($data['participants'] ?? [] as $index => $participant) {
                UserRoomShotgun::create([
                    'room_shotgun_id' => $room->id,
                    'email' => $participant['email'],
                    'is_vegetarian' => $participant['is_vegetarian'] ?? false,
                ]);
                \Log::info('Participant added', ['email' => $participant['email'], 'room_id' => $room->id]);
            }

            // Ajouter le responsable
            UserRoomShotgun::create([
                'room_shotgun_id' => $room->id,
                'email' => $data['responsable_email'],
                'is_vegetarian' => $data['responsable_is_vegetarian'] ?? false,
            ]);
            \Log::info('Responsable added', ['email' => $data['responsable_email'], 'room_id' => $room->id]);

            // Mise à jour de la chambre
            $room->update([
                'responsable_chambre' => $data['responsable_email'],
                'ambiance' => $data['ambiance'] ?? null,
                'locked_until' => null,
                'locked_by_email' => null,
            ]);

            \Log::info('RoomShotgun updated', [
                'room_id' => $room->id,
                'responsable_chambre' => $data['responsable_email'],
                'ambiance' => $data['ambiance'] ?? null,
            ]);

            DB::commit();
            \Log::info('Transaction committed successfully');
            
            $room->unlock();

            Notification::make()
                ->title('Réservation confirmée')
                ->success()
                ->send();
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('HandleSelection error', [
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
