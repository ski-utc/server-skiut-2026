<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PermanenceResource\Pages;
use App\Models\Permanence;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PermanenceResource extends Resource
{
    protected static ?string $model = Permanence::class;
    protected static ?string $navigationIcon = 'heroicon-o-clock';
    protected static ?string $navigationLabel = 'Permanences';
    protected static ?string $modelLabel = 'Permanence';
    protected static ?string $pluralModelLabel = 'Permanences';
    protected static ?string $navigationGroup = 'Gestion du voyage';

    public static function canViewAny(): bool
    {
        return session('admin') === true;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informations de la permanence')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nom')
                            ->required()
                            ->maxLength(255),

                        Textarea::make('description')
                            ->label('Description')
                            ->rows(3)
                            ->nullable(),

                        DateTimePicker::make('start_datetime')
                            ->label('Date et heure de début')
                            ->required()
                            ->native(false),

                        DateTimePicker::make('end_datetime')
                            ->label('Date et heure de fin')
                            ->required()
                            ->native(false)
                            ->after('start_datetime'),

                        TextInput::make('location')
                            ->label('Lieu')
                            ->maxLength(255)
                            ->nullable(),

                        Select::make('status')
                            ->label('Statut')
                            ->options([
                                'scheduled' => 'Planifiée',
                                'in_progress' => 'En cours',
                                'completed' => 'Terminée',
                                'cancelled' => 'Annulée',
                            ])
                            ->default('scheduled')
                            ->required(),

                        Select::make('responsible_user_id')
                            ->label('Responsable')
                            ->relationship('responsibleUser', 'email')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Textarea::make('notes')
                            ->label('Notes')
                            ->rows(3)
                            ->nullable(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('start_datetime')
                    ->label('Début')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('end_datetime')
                    ->label('Fin')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('location')
                    ->label('Lieu')
                    ->searchable(),

                BadgeColumn::make('status')
                    ->label('Statut')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'scheduled' => 'Planifiée',
                        'in_progress' => 'En cours',
                        'completed' => 'Terminée',
                        'cancelled' => 'Annulée',
                        default => $state,
                    })
                    ->colors([
                        'warning' => 'scheduled',
                        'info' => 'in_progress',
                        'success' => 'completed',
                        'danger' => 'cancelled',
                    ]),

                TextColumn::make('responsibleUser.email')
                    ->label('Responsable')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y H:i')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Statut')
                    ->options([
                        'scheduled' => 'Planifiée',
                        'in_progress' => 'En cours',
                        'completed' => 'Terminée',
                        'cancelled' => 'Annulée',
                    ]),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('start_datetime', 'desc')
            ->emptyStateHeading('Aucune permanence')
            ->emptyStateDescription('Créez votre première permanence')
            ->emptyStateIcon('heroicon-o-clock');
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
            'index' => Pages\ListPermanences::route('/'),
            'create' => Pages\CreatePermanence::route('/create'),
            'edit' => Pages\EditPermanence::route('/{record}/edit'),
        ];
    }
}
