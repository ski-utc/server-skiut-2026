<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PartialRoomShotgunResource\Pages;
use App\Models\PartialRoomShotgun;
use App\Models\UserRoomShotgun;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PartialRoomShotgunResource extends Resource
{
    protected static ?string $model = PartialRoomShotgun::class;
    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationLabel = 'Chambres personnalisées';
    protected static ?string $modelLabel = 'Chambre personnalisée';
    protected static ?string $pluralModelLabel = 'Chambres personnalisées';
    protected static ?string $navigationGroup = 'Gestion Pré-voyage';

    public static function canViewAny(): bool
    {
        return session('admin') === true;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informations de la chambre personnalisée')
                    ->schema([
                        TextInput::make('nb_personas')
                            ->label('Nombre de participant·e·s')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(20)
                            ->disabled(),

                        TextInput::make('name')
                            ->label('Nom de la chambre')
                            ->maxLength(255)
                            ->nullable(),

                        Select::make('ambiance')
                            ->label('Ambiance')
                            ->options([
                                'mega grosse night' => 'Mega grosse night',
                                'grosse night' => 'Grosse night',
                                'petite night' => 'Petite night',
                                'calme' => 'Calme',
                            ])
                            ->nullable()
                            ->native(false),

                        TextInput::make('responsable_chambre')
                            ->label('Responsable de chambre')
                            ->email()
                            ->maxLength(255)
                            ->nullable()
                            ->disabled(),
                    ])
                    ->columns(2),

                Section::make('Participant·e·s de la chambre')
                    ->schema(function ($record) {
                        if (!$record) {
                            return [
                                Placeholder::make('no_participants')
                                    ->label('')
                                    ->content('Aucun participant pour le moment. Les participants apparaîtront après la création de la chambre.')
                            ];
                        }

                        $members = UserRoomShotgun::where('partial_room_shotgun_id', $record->id)->get();

                        if ($members->isEmpty()) {
                            return [
                                Placeholder::make('no_participants')
                                    ->label('')
                                    ->content('Aucun participant dans cette chambre pour le moment.')
                            ];
                        }

                        $membersArray = $members->toArray();

                        return [
                            Grid::make(['default' => 1, 'lg' => 2])
                                ->schema(
                                    array_map(
                                        function ($index) use ($membersArray) {
                                            $member = $membersArray[$index];
                                            return Section::make()
                                                ->schema([
                                                    Grid::make(6)
                                                        ->schema([
                                                            TextInput::make("participant_{$index}_email")
                                                                ->label('Participant·e ' . ($index + 1))
                                                                ->default($member['email'])
                                                                ->disabled()
                                                                ->dehydrated(false)
                                                                ->afterStateHydrated(function ($component) use ($member) {
                                                                    $component->state($member['email']);
                                                                })
                                                                ->columnSpan(5),

                                                            Checkbox::make("participant_{$index}_is_vegetarian")
                                                                ->label('Végé')
                                                                ->default((bool) $member['is_vegetarian'])
                                                                ->disabled()
                                                                ->dehydrated(false)
                                                                ->inline(false)
                                                                ->afterStateHydrated(function ($component) use ($member) {
                                                                    $component->state((bool) $member['is_vegetarian']);
                                                                })
                                                                ->extraAttributes(['style' => 'transform: scale(1.5); margin-top: 10px;'])
                                                                ->columnSpan(1),
                                                        ]),
                                                ])
                                                ->columnSpan(1);
                                        },
                                        range(0, count($membersArray) - 1)
                                    )
                                )
                        ];
                    })
                    ->visible(fn($record) => $record !== null)
                    ->collapsed(false)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nom')
                    ->searchable()
                    ->placeholder('Non défini'),

                TextColumn::make('nb_personas')
                    ->label('Participant·e·s')
                    ->sortable()
                    ->suffix(' personnes'),

                BadgeColumn::make('ambiance')
                    ->label('Ambiance')
                    ->colors([
                        'danger' => 'mega grosse night',
                        'warning' => 'grosse night',
                        'info' => 'petite night',
                        'success' => 'calme',
                    ])
                    ->formatStateUsing(function (?string $state): string {
                        return $state ? ucfirst($state) : 'Non définie';
                    }),

                TextColumn::make('responsable_chambre')
                    ->label('Responsable')
                    ->searchable()
                    ->placeholder('Non défini')
                    ->copyable(),

                TextColumn::make('created_at')
                    ->label('Créée le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('ambiance')
                    ->label('Ambiance')
                    ->options([
                        'mega grosse night' => 'Mega grosse night',
                        'grosse night' => 'Grosse night',
                        'petite night' => 'Petite night',
                        'calme' => 'Calme',
                    ])
                    ->native(false),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('Aucune chambre personnalisée')
            ->emptyStateDescription('Les chambres personnalisées créées par les utilisateurs apparaîtront ici')
            ->emptyStateIcon('heroicon-o-user-group');
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
            'index' => Pages\ListPartialRoomShotguns::route('/'),
            'view' => Pages\ViewPartialRoomShotgun::route('/{record}'),
            'edit' => Pages\EditPartialRoomShotgun::route('/{record}/edit'),
        ];
    }
}
