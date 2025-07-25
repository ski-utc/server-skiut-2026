# Contributing to Ski'UTC Server

Merci de votre intérêt pour contribuer au serveur Laravel de Ski'UTC !

## À propos de Ski'UTC

Ski'UTC est une association étudiante de l'UTC qui organise des voyages au ski. Pour en savoir plus sur l'association, ses activités et son fonctionnement, consultez notre page officielle : https://assos.utc.fr/assos/skiutc

## Rejoindre l'équipe de développement

**Important :** Avant de pouvoir contribuer au projet, vous devez être membre de l'association Ski'UTC.

Pour nous rejoindre :
- Contactez-nous à **skiutc@assos.utc.fr**
- Présentez-vous et expliquez votre motivation à contribuer au projet
- Une fois accepté.e dans l'équipe, vous recevrez les accès nécessaires pour contribuer aux projets (repo privés, Slack, Issues...)

## Workflow de développement

Nous utilisons **Git Flow** pour organiser notre développement :

### Structure des branches
- `main` : Branch de production, contient uniquement du code stable
- `develop` : Branch de développement principal
- `feature/*` : Branches pour le développement de nouvelles fonctionnalités
- `hotfix/*` : Branches pour les corrections urgentes en production
- `release/*` : Branches pour préparer les nouvelles versions

### Règles de contribution

1. **Protection de la branch main**
   - Il est **interdit** de push directement sur `main`
   - Tous les changements doivent passer par une Merge Request

2. **Processus de Merge Request**
   - Créez votre branch à partir de `develop`
   - Développez votre fonctionnalité sur une branch `feature/nom-de-la-fonctionnalite`
   - Créez une Merge Request vers `develop`
   - **Au moins 1 review** est requis avant le merge
   - Assurez-vous que votre code respecte les standards du projet

3. **Pipeline CI/CD**
   - Une pipeline automatique se déclenche à chaque merge sur `main`
   - Elle build et publie automatiquement la nouvelle version sur notre registry
   - Vérifiez que votre code passe tous les tests avant de créer votre MR

## Setup du projet

Consultez le README.md pour les instructions détaillées d'installation et de configuration du serveur Laravel.

## Standards de code

- Respectez les conventions de nommage PHP/Laravel
- Commentez votre code, surtout les parties complexes
- Testez vos modifications localement avant de pusher
- Utilisez des messages de commit clairs et descriptifs

## Structure du projet

Ce serveur Laravel sert de backend pour l'application mobile Expo de Ski'UTC et gère :
- L'authentification via OAuth du SIMDE
- Les fonctionnalités de l'application (défis, anecdotes, planning, etc.)
- L'intégration avec la base de données MySQL

## Besoin d'aide ?

- Consultez la documentation dans le README.md
- Contactez l'asso sur **skiutc@assos.utc.fr**
- Participez aux discussions dans les issues du projet

## Licence

En contribuant à ce projet, vous acceptez que vos contributions soient sous la même licence que le projet.

---

Merci pour votre contribution au projet Ski'UTC !