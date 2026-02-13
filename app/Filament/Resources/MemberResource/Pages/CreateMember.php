<?php

namespace App\Filament\Resources\MemberResource\Pages;

use App\Filament\Resources\MemberResource;
use App\Models\BackOfficeAdmin;
use App\Models\User;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateMember extends CreateRecord
{
    protected static string $resource = MemberResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $creationMode = $data['creation_mode'] ?? 'new';

        if ($creationMode === 'existing') {
            $existingEmail = $data['existing_user_email'] ?? null;

            if (!$existingEmail) {
                Notification::make()
                    ->danger()
                    ->title('Erreur')
                    ->body('Veuillez sélectionner un utilisateur existant.')
                    ->send();

                $this->halt();
            }

            $user = User::where('email', $existingEmail)->first();

            if (!$user) {
                Notification::make()
                    ->danger()
                    ->title('Erreur')
                    ->body('Utilisateur introuvable.')
                    ->send();

                $this->halt();
            }

            $user->update([
                'member' => true,
                'admin' => $data['admin'] ?? false,
            ]);

            if ($data['admin'] ?? false) {
                BackOfficeAdmin::firstOrCreate(['email' => $user->email]);
            }

            Notification::make()
                ->success()
                ->title('Membre ajouté')
                ->body("{$user->firstName} {$user->lastName} est maintenant membre du voyage.")
                ->send();

            $this->redirect(MemberResource::getUrl('index'));
            $this->halt();
        }

        $data['member'] = true;

        unset($data['creation_mode']);
        unset($data['existing_user_email']);

        return $data;
    }

    protected function afterCreate(): void
    {
        $user = $this->record;

        if ($user->admin) {
            BackOfficeAdmin::firstOrCreate(['email' => $user->email]);
        }

        Notification::make()
            ->success()
            ->title('Membre créé')
            ->body("{$user->firstName} {$user->lastName} a été ajouté avec succès.")
            ->send();
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
