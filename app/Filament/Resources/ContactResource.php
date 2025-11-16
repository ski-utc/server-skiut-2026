<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContactResource\Pages;
use App\Models\Contact;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ContactResource extends Resource
{
    protected static ?string $model = Contact::class;
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'Contacts';
    protected static ?string $modelLabel = 'Contact';
    protected static ?string $pluralModelLabel = 'Contacts';
    protected static ?string $navigationGroup = 'Gestion du voyage';

    public static function canViewAny(): bool
    {
        return session('admin') === true;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informations du contact')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nom')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('role')
                            ->label('Rôle/Fonction')
                            ->maxLength(255)
                            ->placeholder('Prez, Secours ...'),

                        TextInput::make('phoneNumber')
                            ->label('Téléphone')
                            ->tel()
                            ->maxLength(255),
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

                TextColumn::make('role')
                    ->label('Rôle')
                    ->searchable()
                    ->badge()
                    ->color('info'),

                TextColumn::make('phoneNumber')
                    ->label('Téléphone')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Numéro copié!'),
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
            ->defaultSort('name')
            ->emptyStateHeading('Aucun contact')
            ->emptyStateDescription('Ajoutez vos contacts importants pour le voyage')
            ->emptyStateIcon('heroicon-o-users');
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
            'index' => Pages\ListContacts::route('/'),
            'create' => Pages\CreateContact::route('/create'),
            'edit' => Pages\EditContact::route('/{record}/edit'),
        ];
    }
}
