<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MemberResource\Pages;
use App\Models\BackOfficeAdmin;
use App\Models\Room;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class MemberResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'Membres';
    protected static ?string $modelLabel = 'Membre';
    protected static ?string $pluralModelLabel = 'Membres';
    protected static ?string $navigationGroup = 'Gestion Pré-voyage';

    public static function canViewAny(): bool
    {
        return session('admin') === true;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Création de membre')
                    ->description('Choisissez un utilisateur existant ou créez-en un nouveau')
                    ->schema([
                        Forms\Components\Radio::make('creation_mode')
                            ->label('Mode de création')
                            ->options([
                                'existing' => 'Sélectionner un utilisateur existant',
                                'new' => 'Créer un nouveau compte',
                            ])
                            ->default('existing')
                            ->live()
                            ->hiddenOn('edit'),

                        Forms\Components\Select::make('existing_user_email')
                            ->label('Utilisateur existant')
                            ->options(function () {
                                return User::where('member', false)
                                    ->pluck('email', 'email');
                            })
                            ->searchable()
                            ->native(false)
                            ->visible(fn(Forms\Get $get) => $get('creation_mode') === 'existing')
                            ->hiddenOn('edit')
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                if ($state) {
                                    $user = User::where('email', $state)->first();
                                    if ($user) {
                                        $set('email', $user->email);
                                    }
                                }
                            }),
                    ])
                    ->hiddenOn('edit')
                    ->columnSpanFull(),

                Forms\Components\Section::make('Informations personnelles')
                    ->schema([
                        Forms\Components\TextInput::make('cas')
                            ->label('Identifiant CAS')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->visible(fn(Forms\Get $get) => $get('creation_mode') === 'new' || request()->routeIs('*.edit')),

                        Forms\Components\TextInput::make('firstName')
                            ->label('Prénom')
                            ->required()
                            ->maxLength(255)
                            ->visible(fn(Forms\Get $get) => $get('creation_mode') === 'new' || request()->routeIs('*.edit')),

                        Forms\Components\TextInput::make('lastName')
                            ->label('Nom')
                            ->required()
                            ->maxLength(255)
                            ->visible(fn(Forms\Get $get) => $get('creation_mode') === 'new' || request()->routeIs('*.edit')),

                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->visible(fn(Forms\Get $get) => $get('creation_mode') === 'new' || request()->routeIs('*.edit')),

                        Forms\Components\Select::make('room_id')
                            ->label('Chambre')
                            ->relationship('room', 'roomNumber')
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->getOptionLabelFromRecordUsing(fn(Room $record) => "Chambre {$record->roomNumber}" . ($record->name ? " - {$record->name}" : ''))
                            ->visible(fn(Forms\Get $get) => $get('creation_mode') === 'new' || request()->routeIs('*.edit'))
                            ->createOptionForm([
                                Forms\Components\TextInput::make('roomNumber')
                                    ->label('Numéro de chambre')
                                    ->required()
                                    ->numeric()
                                    ->unique(),
                                Forms\Components\TextInput::make('capacity')
                                    ->label('Capacité')
                                    ->required()
                                    ->numeric()
                                    ->default(1),
                                Forms\Components\TextInput::make('name')
                                    ->label('Nom de la chambre')
                                    ->maxLength(255),
                            ]),
                    ])
                    ->columns(2)
                    ->visible(fn(Forms\Get $get) => $get('creation_mode') === 'new' || request()->routeIs('*.edit')),

                Forms\Components\Section::make('Rôles')
                    ->schema([
                        Forms\Components\Toggle::make('member')
                            ->label('Membre du voyage')
                            ->default(true)
                            ->disabled()
                            ->dehydrated(),

                        Forms\Components\Toggle::make('admin')
                            ->label('Administrateur')
                            ->default(false)
                            ->helperText('Si activé, l\'utilisateur aure également accès au back-office'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(User::where('member', true))
            ->columns([
                Tables\Columns\TextColumn::make('full_name')
                    ->label('Nom complet')
                    ->getStateUsing(fn(User $record) => "{$record->firstName} {$record->lastName}")
                    ->searchable(['firstName', 'lastName'])
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->icon('heroicon-o-envelope'),

                Tables\Columns\TextColumn::make('room.roomNumber')
                    ->label('Chambre')
                    ->sortable()
                    ->searchable()
                    ->badge()
                    ->default('Non assigné'),

                Tables\Columns\TextColumn::make('role')
                    ->label('Rôle')
                    ->badge()
                    ->getStateUsing(fn(User $record) => $record->admin ? 'Admin' : 'Membre')
                    ->color(fn(string $state): string => match ($state) {
                        'Admin' => 'danger',
                        'Membre' => 'success',
                    }),
            ])
            ->filters([
                SelectFilter::make('admin')
                    ->label('Rôle')
                    ->options([
                        1 => 'Administrateur',
                        0 => 'Membre',
                    ])
                    ->native(false),

                SelectFilter::make('room_id')
                    ->label('Chambre')
                    ->relationship('room', 'roomNumber')
                    ->searchable()
                    ->preload()
                    ->native(false)
                    ->getOptionLabelFromRecordUsing(fn(Room $record) => "Chambre {$record->roomNumber}"),

                SelectFilter::make('alumniOrExte')
                    ->label('Type')
                    ->options([
                        1 => 'Alumni/Externe',
                        0 => 'Membre standard',
                    ])
                    ->native(false),
            ])
            ->actions([
                Tables\Actions\Action::make('toggle_admin')
                    ->label(fn(User $record) => $record->admin ? 'Retirer admin' : 'Rendre admin')
                    ->icon(fn(User $record) => $record->admin ? 'heroicon-o-shield-exclamation' : 'heroicon-o-shield-check')
                    ->color(fn(User $record) => $record->admin ? 'warning' : 'success')
                    ->requiresConfirmation()
                    ->modalHeading(fn(User $record) => $record->admin ? 'Retirer les droits administrateur' : 'Accorder les droits administrateur')
                    ->modalDescription(fn(User $record) => $record->admin
                        ? 'Cette action retirera les droits administrateur et supprimera l\'email de la table back_office_admin.'
                        : 'Cette action accordera les droits administrateur et ajoutera l\'email à la table back_office_admin.')
                    ->action(function (User $record) {
                        if ($record->admin) {
                            $adminCount = User::where('admin', true)->count();
                            if ($adminCount <= 1) {
                                Notification::make()
                                    ->warning()
                                    ->title('Action impossible')
                                    ->body('Vous ne pouvez pas retirer le dernier administrateur.')
                                    ->send();
                                return;
                            }

                            $record->update(['admin' => false]);
                            BackOfficeAdmin::where('email', $record->email)->delete();

                            Notification::make()
                                ->success()
                                ->title('Droits retirés')
                                ->body("{$record->firstName} {$record->lastName} n'est plus administrateur.")
                                ->send();
                        } else {
                            $record->update(['admin' => true]);
                            BackOfficeAdmin::firstOrCreate(['email' => $record->email]);

                            Notification::make()
                                ->success()
                                ->title('Droits accordés')
                                ->body("{$record->firstName} {$record->lastName} est maintenant administrateur.")
                                ->send();
                        }
                    }),

                Tables\Actions\EditAction::make(),

                Tables\Actions\DeleteAction::make()
                    ->before(function (User $record) {
                        if ($record->admin) {
                            $adminCount = User::where('admin', true)->count();
                            if ($adminCount <= 1) {
                                Notification::make()
                                    ->warning()
                                    ->title('Suppression impossible')
                                    ->body('Vous ne pouvez pas supprimer le dernier administrateur.')
                                    ->send();
                                return false;
                            }
                            BackOfficeAdmin::where('email', $record->email)->delete();
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->before(function ($records) {
                            $adminRecords = $records->filter(fn($r) => $r->admin);
                            if ($adminRecords->isNotEmpty()) {
                                $totalAdmins = User::where('admin', true)->count();
                                if ($totalAdmins - $adminRecords->count() < 1) {
                                    Notification::make()
                                        ->warning()
                                        ->title('Suppression impossible')
                                        ->body('Vous devez conserver au moins un administrateur.')
                                        ->send();
                                    return false;
                                }
                            }

                            foreach ($adminRecords as $record) {
                                BackOfficeAdmin::where('email', $record->email)->delete();
                            }
                        }),
                ]),
            ])
            ->defaultSort('firstName', 'asc')
            ->emptyStateHeading('Aucun membre')
            ->emptyStateDescription('Les membres de l\'association apparaîtront ici')
            ->emptyStateIcon('heroicon-o-users');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMembers::route('/'),
            'create' => Pages\CreateMember::route('/create'),
            'edit' => Pages\EditMember::route('/{record}/edit'),
        ];
    }
}
