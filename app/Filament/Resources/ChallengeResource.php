<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ChallengeResource\Pages;
use App\Models\Challenge;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
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

class ChallengeResource extends Resource
{
    protected static ?string $model = Challenge::class;
    protected static ?string $navigationIcon = 'heroicon-o-trophy';
    protected static ?string $navigationLabel = 'Challenges';
    protected static ?string $modelLabel = 'Challenge';
    protected static ?string $pluralModelLabel = 'Challenges';
    protected static ?string $navigationGroup = 'Gestion du voyage';

    public static function canViewAny(): bool
    {
        return session('admin') === true;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informations du challenge')
                    ->schema([
                        TextInput::make('text')
                            ->label('Nom du challenge')
                            ->required()
                            ->maxLength(255),

                        Textarea::make('description')
                            ->label('Description détaillée')
                            ->rows(4)
                            ->helperText('Description complète du challenge et des conditions de validation'),

                        TextInput::make('points')
                            ->label('Points attribués')
                            ->numeric()
                            ->default(0)
                            ->helperText('Nombre de points gagnés en réussissant ce challenge'),

                        Toggle::make('isActive')
                            ->label('Challenge actif')
                            ->default(true)
                            ->helperText('Seuls les challenges actifs sont visibles dans l\'application'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('text')
                    ->label('Challenge')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                TextColumn::make('points')
                    ->label('Points')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color('success'),

                BadgeColumn::make('isActive')
                    ->label('Statut')
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Actif' : 'Inactif')
                    ->colors([
                        'success' => true,
                        'danger' => false,
                    ]),

                TextColumn::make('challenge_proofs_count')
                    ->label('Preuves soumises')
                    ->counts('challengeProofs')
                    ->badge()
                    ->color('info'),

                TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y H:i')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('isActive')
                    ->label('Statut')
                    ->trueLabel('Actifs seulement')
                    ->falseLabel('Inactifs seulement')
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
            ->defaultSort('points', 'desc')
            ->emptyStateHeading('Aucun challenge')
            ->emptyStateDescription('Créez votre premier challenge pour motiver les participants')
            ->emptyStateIcon('heroicon-o-trophy');
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
            'index' => Pages\ListChallenges::route('/'),
            'create' => Pages\CreateChallenge::route('/create'),
            'edit' => Pages\EditChallenge::route('/{record}/edit'),
        ];
    }
}
