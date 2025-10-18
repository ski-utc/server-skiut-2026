# Système de Réservation de Chambres - Ski'UT

## Vue d'ensemble

Ce système permet aux participants du voyage de réserver leurs chambres via une interface Filament intégrée au serveur existant.

## Fonctionnalités

### Pour les Administrateurs
- **Gestion des chambres** : Créer, modifier, supprimer et visualiser les chambres
- **Statistiques** : Voir le nombre de chambres disponibles, complètes, bloquées
- **Accès** : Visible uniquement si `session('admin')` est à `true`

### Pour les Participants
- **Sélection de chambre** : Choisir une chambre disponible parmi celles proposées
- **Réservation** : Remplir les emails des participants et désigner un responsable
- **Validation** : Vérification automatique que les emails sont inscrits à l'événement
- **Système de verrouillage** : Les chambres sont bloquées pendant 5 minutes lors de la réservation

## Structure des Données

### Modèle Chambre
- `numero` : Numéro de la chambre
- `nb_places` : Nombre de places disponibles
- `responsable_chambre` : Email du responsable
- `ambiance` : Type d'ambiance (mega grosse night, grosse night, petite night, calme)
- `locked_until` : Date/heure de fin de verrouillage
- `locked_by_email` : Email de la personne qui a verrouillé la chambre

### Modèle ChambreUser
- `chambre_id` : ID de la chambre
- `email` : Email du participant

### Modèle Shotguns
- `email` : Email des participants inscrits
- `position` : Position dans la liste d'attente

## Interface Utilisateur

### Dashboard Principal
- Statistiques des chambres
- Liste des chambres disponibles
- Actions de réservation

### Page de Réservation
- Formulaire de sélection de chambre
- Champs pour les emails des participants
- Sélection du responsable de chambre
- Validation en temps réel

### Gestion Administrative
- CRUD complet des chambres
- Filtres par ambiance et disponibilité
- Actions en masse

## Sécurité et Validation

### Vérification des Emails
- Tous les emails doivent exister dans la table `Shotguns`
- Validation en temps réel lors de la saisie
- Messages d'erreur explicites

### Système de Verrouillage
- Verrouillage automatique pendant 5 minutes
- Nettoyage automatique des verrous expirés
- Protection contre les réservations simultanées

### Gestion des Responsables
- Un seul responsable par chambre
- Validation obligatoire
- Mise à jour automatique du champ `responsable_chambre`

## Commandes Artisan

### Nettoyage des Verrous
```bash
php artisan chambres:clean-locks
```
Cette commande nettoie automatiquement les verrous expirés et est planifiée pour s'exécuter toutes les minutes.

### Seeding des Données
```bash
php artisan db:seed --class=ChambreSeeder
```
Ajoute des chambres d'exemple pour les tests.

## Configuration

### Middleware d'Authentification
Le système utilise le middleware `EnsureBackOfficeAuthenticated` pour l'authentification.

### Panneau Filament
- ID : `shotgun-chambre`
- Chemin : `/skiutc/shotgun-chambre`
- Nom : "Ski'UT - Shotgun Chambres"

## Utilisation

### Pour les Administrateurs
1. Se connecter avec `session('admin') = true`
2. Accéder à la section "Gestion des chambres"
3. Créer/modifier les chambres selon les besoins

### Pour les Participants
1. Accéder à la page "Choisir ma chambre"
2. Sélectionner une chambre disponible
3. Remplir les emails des participants
4. Désigner un responsable
5. Valider la réservation

## Gestion des Erreurs

### Chambre Non Disponible
- Message d'erreur si la chambre est déjà réservée
- Redirection vers une autre chambre

### Email Non Inscrit
- Validation en temps réel
- Message d'erreur explicite
- Suggestion d'emails valides via un select

### Problème de Responsable
- Vérification qu'il y a exactement un responsable
- Message d'erreur si aucun ou plusieurs responsables

## Maintenance

### Nettoyage Automatique
- Les verrous expirés sont nettoyés automatiquement
- Pas d'intervention manuelle nécessaire

### Monitoring
- Statistiques en temps réel
- Suivi des réservations
- Alertes en cas de problème

## Développement

### Structure des Fichiers
```
app/Filament/
├── Resources/
│   ├── ChambreResource.php (Admin)
│   └── ChambreSelectionResource.php (Utilisateurs)
├── Pages/
│   ├── Dashboard.php
│   └── ChoisirChambre.php
└── Widgets/
    ├── ChambreStatsWidget.php
    └── ChambreDisponibleWidget.php
```

### Modèles
```
app/Models/
├── Chambre.php
├── ChambreUser.php
└── Shotguns.php
```

### Commandes
```
app/Console/Commands/
└── CleanExpiredLocks.php
```

## Support

Pour toute question ou problème, consulter les logs Laravel ou contacter l'équipe de développement.
