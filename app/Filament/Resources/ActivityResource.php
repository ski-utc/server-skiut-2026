<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ActivityResource\Pages;
use App\Models\Activity;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ActivityResource extends Resource
{
    protected static ?string $model = Activity::class;
    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?string $navigationLabel = 'Activités';
    protected static ?string $modelLabel = 'Activité';
    protected static ?string $pluralModelLabel = 'Activités';
    protected static ?string $navigationGroup = 'Gestion du voyage';

    public static function canViewAny(): bool
    {
        return session('admin') === true;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informations de l\'activité')
                    ->schema([
                        TextInput::make('text')
                            ->label('Nom de l\'activité')
                            ->required()
                            ->maxLength(255),

                        Textarea::make('description')
                            ->label('Description')
                            ->rows(3)
                            ->nullable(),

                        DatePicker::make('date')
                            ->label('Date')
                            ->required()
                            ->native(false),

                        TimePicker::make('startTime')
                            ->label('Heure de début')
                            ->required()
                            ->native(false),

                        TimePicker::make('endTime')
                            ->label('Heure de fin')
                            ->required()
                            ->native(false)
                            ->after('startTime'),

                        Toggle::make('payant')
                            ->label('Activité payante')
                            ->default(false)
                            ->helperText('Cochez si l\'activité nécessite un paiement supplémentaire'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('text')
                    ->label('Activité')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('date')
                    ->label('Date')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('startTime')
                    ->label('Début')
                    ->time('H:i')
                    ->sortable(),

                TextColumn::make('endTime')
                    ->label('Fin')
                    ->time('H:i')
                    ->sortable(),

                BadgeColumn::make('payant')
                    ->label('Type')
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Payant' : 'Gratuit')
                    ->colors([
                        'warning' => true,
                        'success' => false,
                    ]),

                TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y H:i')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('payant')
                    ->label('Type d\'activité')
                    ->options([
                        true => 'Payant',
                        false => 'Gratuit',
                    ]),

                Tables\Filters\Filter::make('date_range')
                    ->form([
                        DatePicker::make('date_from')
                            ->label('Du'),
                        DatePicker::make('date_to')
                            ->label('Au'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['date_from'], fn ($q) => $q->whereDate('date', '>=', $data['date_from']))
                            ->when($data['date_to'], fn ($q) => $q->whereDate('date', '<=', $data['date_to']));
                    }),
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
            ->defaultSort('date')
            ->emptyStateHeading('Aucune activité')
            ->emptyStateDescription('Créez votre première activité pour le voyage')
            ->emptyStateIcon('heroicon-o-calendar-days');
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
            'index' => Pages\ListActivities::route('/'),
            'create' => Pages\CreateActivity::route('/create'),
            'edit' => Pages\EditActivity::route('/{record}/edit'),
        ];
    }
}
