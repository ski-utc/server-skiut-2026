# Seeders et Factories pour Skiut

Ce dossier contient tous les seeders et factories générés pour l'application Skiut.

## Factories créées

- `UserFactory.php` - Génère des utilisateurs avec données réalistes
- `RoomFactory.php` - Génère des chambres avec ambiances et passions
- `ContactFactory.php` - Génère des contacts de l'équipe organisatrice
- `ChallengeFactory.php` - Génère des défis de ski avec points (titres uniques)
- `AnecdoteFactory.php` - Génère des anecdotes de ski réalistes
- `StatisticsFactory.php` - Génère des statistiques de ski
- `UserPerformanceFactory.php` - Génère des performances utilisateur
- `ActivityFactory.php` - Génère des activités organisées
- `NotificationFactory.php` - Génère des notifications d'app
- `TransportFactory.php` - Génère des transports disponibles
- `ChallengeProofFactory.php` - Génère des preuves de défis
- `PushTokenFactory.php` - Génère des tokens de notification
- `SkinderLikeFactory.php` - Génère des likes entre chambres
- `AnecdotesLikeFactory.php` - Génère des likes sur anecdotes (sans doublons)
- `AnecdotesWarnFactory.php` - Génère des signalements d'anecdotes (sans doublons)

## Seeders créés

- `UserSeeder.php` - 30 utilisateurs
- `RoomSeeder.php` - 15 chambres
- `ContactSeeder.php` - 8 contacts
- `ChallengeSeeder.php` - 15 défis
- `AnecdoteSeeder.php` - 50 anecdotes
- `StatisticsSeeder.php` - 60 statistiques
- `UserPerformanceSeeder.php` - 30 performances
- `ActivitySeeder.php` - 25 activités
- `NotificationSeeder.php` - 10 notifications
- `TransportSeeder.php` - 20 transports
- `ChallengeProofSeeder.php` - 40 preuves
- `PushTokenSeeder.php` - 25 tokens
- `SkinderLikeSeeder.php` - 30 likes entre chambres
- `AnecdotesLikeSeeder.php` - 50 likes sur anecdotes
- `AnecdotesWarnSeeder.php` - 15 signalements
- `RelationsSeeder.php` - Assigne les relations entre entités

## Utilisation

### Lancer tous les seeders
```bash
php artisan db:seed
```

### Lancer un seeder spécifique
```bash
php artisan db:seed --class=UserSeeder
```

### Lancer plusieurs seeders
```bash
php artisan db:seed --class=UserSeeder,RoomSeeder,ContactSeeder
```

### Utiliser une factory dans le code
```php
// Créer un utilisateur
$user = User::factory()->create();

// Créer plusieurs utilisateurs
$users = User::factory(10)->create();

// Créer avec des données spécifiques
$user = User::factory()->create([
    'firstName' => 'John',
    'lastName' => 'Doe',
    'email' => 'john@example.com'
]);
```

## Ordre d'exécution

Les seeders sont exécutés dans cet ordre pour respecter les dépendances :

1. Tables indépendantes (Contact, Challenge, Activity, Notification, Transport)
2. Tables avec dépendances (Room, User)
3. Assignation des relations (RelationsSeeder)
4. Tables avec dépendances multiples (Anecdote, Statistics, UserPerformance, ChallengeProof, PushToken)
5. Tables de relations (SkinderLike, AnecdotesLike, AnecdotesWarn)

## Corrections apportées

- **Contraintes de clé étrangère** : Corrigé les migrations pour référencer correctement les tables
- **Contraintes UNIQUE** : Évité les doublons dans les défis et les likes
- **Contraintes NOT NULL** : Géré correctement les relations entre Users et Rooms
- **Dépendances circulaires** : Résolu en créant d'abord les entités indépendantes

## Données générées

Les factories génèrent des données réalistes pour une application de ski :
- Noms et prénoms français
- Emails uniques
- Anecdotes de ski authentiques
- Défis avec points variés (titres uniques)
- Statistiques de vitesse et distance
- Activités organisées
- Transports entre stations
- Notifications pertinentes
- Relations correctes entre toutes les entités 