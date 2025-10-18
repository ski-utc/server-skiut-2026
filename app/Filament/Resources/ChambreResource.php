<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ChambreResource\Pages;
use App\Models\Chambre;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Forms\Components\Section;

class ChambreResource extends Resource
{
    protected static ?string $model = Chambre::class;

    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected static ?string $navigationLabel = 'Gestion des chambres';

    protected static ?string $modelLabel = 'Chambre';

    protected static ?string $pluralModelLabel = 'Chambres';

    protected static ?string $navigationGroup = 'Administration';

    public static function canViewAny(): bool
    {
        return session('admin') === true;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informations de la chambre')
                    ->schema([
                        TextInput::make('numero')
                            ->label('Numéro de chambre')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        
                        TextInput::make('nb_places')
                            ->label('Nombre de places')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(10)
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('numero')
                    ->label('Numéro')
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('nb_places')
                    ->label('Places')
                    ->sortable(),
                
                // TextColumn::make('users_count')
                //     ->label('Occupées')
                //     ->counts('users')
                //     ->sortable(),
                
                BadgeColumn::make('ambiance')
                    ->label('Ambiance')
                    ->colors([
                        'danger' => 'mega grosse night',
                        'warning' => 'grosse night',
                        'info' => 'petite night',
                        'success' => 'calme',
                    ])
                    ->formatStateUsing(function (string $state): string {
                        return ucfirst($state);
                    })
                    ->placeholder('Non défini'),
                
                TextColumn::make('responsable_chambre')
                    ->label('Responsable')
                    ->searchable()
                    ->placeholder('Non défini'),
                
                BadgeColumn::make('status')
                    ->label('Statut')
                    ->getStateUsing(function (Chambre $record): string {
                        if ($record->isLocked()) {
                            return 'Bloquée';
                        }
                        if ($record->isFull()) {
                            return 'Complète';
                        }
                        return 'Disponible';
                    })
                    ->colors([
                        'danger' => 'Bloquée',
                        'warning' => 'Complète',
                        'success' => 'Disponible',
                    ]),
                
                TextColumn::make('locked_until')
                    ->label('Bloquée jusqu\'à')
                    ->dateTime()
                    ->placeholder('Non bloquée')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('ambiance')
                    ->options([
                        'mega grosse night' => 'Mega grosse night',
                        'grosse night' => 'Grosse night',
                        'petite night' => 'Petite night',
                        'calme' => 'Calme',
                    ]),
                
                Tables\Filters\TernaryFilter::make('is_available')
                    ->label('Disponible')
                    ->queries(
                        true: fn ($query) => $query->whereDoesntHave('users')->where(function ($q) {
                            $q->whereNull('locked_until')->orWhere('locked_until', '<=', now());
                        }),
                        false: fn ($query) => $query->where(function ($q) {
                            $q->whereHas('users')->orWhere('locked_until', '>', now());
                        }),
                    ),
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
            'index' => Pages\ListChambres::route('/'),
            'create' => Pages\CreateChambre::route('/create'),
            'view' => Pages\ViewChambre::route('/{record}'),
            'edit' => Pages\EditChambre::route('/{record}/edit'),
        ];
    }
}
