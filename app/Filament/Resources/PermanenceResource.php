<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PermanenceResource\Pages;
use App\Models\Permanence;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PermanenceResource extends Resource
{
    protected static ?string $model = Permanence::class;
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
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
                Forms\Components\Section::make('Informations de la permanence')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nom de la tâche')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Ex: Courses Auchan, Préparation repas...')
                            ->columnSpanFull(),

                        Forms\Components\Select::make('responsible_user_id')
                            ->label('Responsable')
                            ->relationship('responsibleUser', 'email', fn (Builder $query) => $query->where('member', true))
                            ->getOptionLabelFromRecordUsing(fn (User $record) => "{$record->firstName} {$record->lastName} ({$record->email})")
                            ->searchable(['firstName', 'lastName', 'email'])
                            ->preload()
                            ->required()
                            ->native(false)
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('location')
                            ->label('Lieu')
                            ->maxLength(255)
                            ->placeholder('Ex: Auchan, Chalet, Station...')
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Dates et horaires')
                    ->schema([
                        Forms\Components\DateTimePicker::make('start_datetime')
                            ->label('Date et heure de début')
                            ->required()
                            ->native(false)
                            ->seconds(false)
                            ->displayFormat('d/m/Y H:i'),

                        Forms\Components\DateTimePicker::make('end_datetime')
                            ->label('Date et heure de fin')
                            ->required()
                            ->native(false)
                            ->seconds(false)
                            ->displayFormat('d/m/Y H:i')
                            ->after('start_datetime'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Notes')
                    ->schema([
                        Forms\Components\Textarea::make('notes')
                            ->label('Notes')
                            ->rows(4)
                            ->placeholder('Informations complémentaires, consignes particulières...')
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->collapsed(fn ($record) => $record && !$record->notes),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Tâche')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->wrap(),

                Tables\Columns\TextColumn::make('responsibleUser.firstName')
                    ->label('Responsable')
                    ->getStateUsing(
                        fn (Permanence $record) =>
                        $record->responsibleUser
                            ? "{$record->responsibleUser->firstName} {$record->responsibleUser->lastName}"
                            : 'Non assigné'
                    )
                    ->searchable(['firstName', 'lastName'])
                    ->sortable(),

                Tables\Columns\TextColumn::make('location')
                    ->label('Lieu')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-map-pin')
                    ->placeholder('Non défini'),

                Tables\Columns\TextColumn::make('start_datetime')
                    ->label('Début')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('end_datetime')
                    ->label('Fin')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                // Tables\Columns\TextColumn::make('status')
                //     ->label('Statut')
                //     ->badge()
                //     ->color(fn (string $state): string => match ($state) {
                //         'scheduled' => 'warning',
                //         'in_progress' => 'info',
                //         'completed' => 'success',
                //         'cancelled' => 'danger',
                //     })
                //     ->formatStateUsing(fn (string $state): string => match ($state) {
                //         'scheduled' => 'Planifiée',
                //         'in_progress' => 'En cours',
                //         'completed' => 'Terminée',
                //         'cancelled' => 'Annulée',
                //         default => $state
                //     })
                //     ->sortable(),
            ])
            ->filters([
                // SelectFilter::make('status')
                //     ->label('Statut')
                //     ->options([
                //         'scheduled' => 'Planifiée',
                //         'in_progress' => 'En cours',
                //         'completed' => 'Terminée',
                //         'cancelled' => 'Annulée',
                //     ])
                //     ->native(false),

                SelectFilter::make('responsible_user_id')
                    ->label('Responsable')
                    ->relationship('responsibleUser', 'email', fn (Builder $query) => $query->where('member', true))
                    ->getOptionLabelFromRecordUsing(fn (User $record) => "{$record->firstName} {$record->lastName}")
                    ->searchable()
                    ->preload()
                    ->native(false),

                Filter::make('dates')
                    ->form([
                        Forms\Components\DatePicker::make('start_from')
                            ->label('Du')
                            ->native(false),
                        Forms\Components\DatePicker::make('start_to')
                            ->label('Au')
                            ->native(false),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['start_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('start_datetime', '>=', $date),
                            )
                            ->when(
                                $data['start_to'],
                                fn (Builder $query, $date): Builder => $query->whereDate('start_datetime', '<=', $date),
                            );
                    }),

                SelectFilter::make('location')
                    ->label('Lieu')
                    ->options(function () {
                        return Permanence::whereNotNull('location')
                            ->distinct()
                            ->pluck('location', 'location');
                    })
                    ->searchable()
                    ->native(false),

                // Tables\Filters\TernaryFilter::make('notified')
                //     ->label('Notification envoyée')
                //     ->placeholder('Tous')
                //     ->trueLabel('Notifié')
                //     ->falseLabel('Non notifié'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),

                // Tables\Actions\Action::make('complete')
                //     ->label('Terminer')
                //     ->icon('heroicon-o-check-circle')
                //     ->color('success')
                //     ->requiresConfirmation()
                //     ->visible(fn (Permanence $record) => $record->status !== 'completed')
                //     ->action(fn (Permanence $record) => $record->update(['status' => 'completed'])),

                // Tables\Actions\Action::make('start')
                //     ->label('Démarrer')
                //     ->icon('heroicon-o-play-circle')
                //     ->color('info')
                //     ->requiresConfirmation()
                //     ->visible(fn (Permanence $record) => $record->status === 'scheduled')
                //     ->action(fn (Permanence $record) => $record->update(['status' => 'in_progress'])),
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),

                //     Tables\Actions\BulkAction::make('mark_as_completed')
                //         ->label('Marquer comme terminées')
                //         ->icon('heroicon-o-check-circle')
                //         ->color('success')
                //         ->requiresConfirmation()
                //         ->action(fn ($records) => $records->each->update(['status' => 'completed'])),
            ])
            ->defaultSort('start_datetime', 'asc')
            ->emptyStateHeading('Aucune permanence')
            ->emptyStateDescription('Commencez par créer une permanence')
            ->emptyStateIcon('heroicon-o-clipboard-document-check');
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
