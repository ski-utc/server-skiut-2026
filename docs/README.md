# Bienvenue sur le serveur de Ski'UT 2025 en Laravel

## Pour commencer :
### 1. Installer PHP

Il y a 1 milliard de façon d'installer PHP : avec homebrew sur MAC/Linux, avec chocolatey ou un executable sur Windows...

- Installer avec Homebrew :
``` bash
/bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)" 
brew install php
```

- Installer avec un executable Windows
[Lien vers la page de download](https://windows.php.net/download/)

### 2. Installer composer

- Mac/Linux (avec homebrew) :
``` bash
brew install composer
```

- Windows :
``` bash
  php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
  php -r "if (hash_file('sha384', 'composer-setup.php') === '55ce33d7678c5a611085589f1f3ddf8b3c52d662cd01d4ba75c0ee0459970c2200a51f492d557530c71c15d8dba01eae') { echo 'Installer     verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
  php composer-setup.php
  php -r "unlink('composer-setup.php');"
```

### 3. Cloner les packages
Pour installer les packages, il faut se placer dans la racine du projet et faire :

``` bash
composer install
```

Si ça ne fonctionne pas, faites 
``` bash
composer install --ignore-platform-reqs
composer update --ignore-platform-reqs
composer install
```

## Maintenant que ton projet est prêt, on va config Laravel
### 1.Base de données
Pour faire tourner ton serveur sur une base de données, il faut lui donner une base de données.
Pour ça, créé une base de données sqlite dans ./database
``` bash
touch ./database/database.sqlite
```
Une fois ta BDD créé, copie le fichier .env.example en un .env (c'est le fichier qui te permettra de gérer les variables d'environnement du projet)
``` bash
cp ./.env.example ./.env
```
Modifie la ligne DB_DATABASE pour lui donner le chemin vers ta BDD
Recopie le résultat de cette requête à côté de DB_DATABASE=
``` bash
ls ./database/database.sqlite
```
Enfin, remplis ta base de données avec les config de migration pré-définies : 
``` bash
php artisan migrate
```
_En théorie vous pouvez directement faire $ php artisan migrate sans créer la BDD, il vous proposera de le faire_

### 2. Bypass l'OAuth

Pour éviter de constamment rentrer son CAS pour dev, j'ai mis un système de bypass dans le serveur.
Pour utiliser ça : 

1. Vérifie que APP_NO_LOGIN=true dans ton .env si tu ne veux pas t'occuper de l'Auth

2. Créé un User dans la base de données : c'est le user que te donneras par défaut le AuthController (cf. AuthController ligne 53). Par défaut j'ai mis '1' partout

3. Défini l'ID que tu viens de mettre dans ta BDD, dans ton .env sur la variable "USER_ID"

4. Faudra penser à modifier cet id dans l'app (dans _layout, au début, il y a une fonction de bypass) de sorte à ce que tu sois bien considéré comme le user que t'as choisi

### 3. Lance le serveur

- Si tu veux tester des routes sur le serveur dans ton navigateur, fait
```bash
php artisan serve --port=8000
```
- Si tu veux faire des requêtes sur ton serveur depuis l'app, il faut que ton serveur soit accessible, et pas sur une adresse de bouclage localhost. 
Du coup, récupère ton IP avec
```bash
 ifconfig
```
OU
```
ip a | grep inet 
```
Et entre la commande suivante en remplaçant <> par ton IP
```bash
php artisan serve --host=<> --port=8000
```

### 4. Lance npm pour build le CSS
Ouvre un nouvel onglet terminal, et lance :
```bash
npm run dev
```

### 5. En théorie le serveur tourne

**Attention** : le serveur est configuré pour tourner sur une base URL /skiutc. Concrètement, le serveur commence à te renvoyer des trucs sur http://tonIP/skiutc/.
Il en est de même pour **auth** sur /skiutc/auth et **api** sur /skiutc/api


## Petit détail de la structure du projet (Pas à jour)
### Racine
- .env.example : Fichier template à dupliquer en .env pour gérer toutes les variables d'environnement du projet
- artisan : Outil en ligne de commande pour exécuter des tâches comme les migrations, les tests, les contrôleurs, ...
- composer.json : Fichier qui gère les dépendances PHP du projet via Composer, ainsi que les informations sur l'autoloading et les scripts
- composer.lock : Fichier généré automatiquement par Composer qui verrouille les versions des dépendances installées pour garantir que le projet utilise toujours les mêmes versions
- package.json : Fichier qui gère les dépendances JavaScript (via npm ou yarn), les scripts de build et autres configurations front-end
- phpunit.xml : Fichier de configuration pour PHPUnit, le framework de tests PHP utilisé pour écrire et exécuter des tests unitaires et fonctionnels
- vite.config.js : Fichier de configuration pour Vite, utilisé pour compiler les ressources front-end (CSS, JS) de manière rapide et moderne

### Dossiers
- /app : C'est le dossier le plus important de l'app, celui qui contient les Models, les Controllers, le Middleware...
  - /Console : Contient les commandes artisan personnalisées de l'application
    - Kernel.php : Enregistre et gère les commandes artisan
  - /Exceptions : Dossier de gestion des exceptions
    - Handler.php : Gère toutes les exceptions non capturées et définit la façon dont elles sont rendues ou loguées.
  - /Http :
    - /Controllers : Contient tous les Controllers de ton app (les fonctions que tu vas utiliser avec tes requêtes HTTP, et qui vont traiter tes Models)
      - Auth : C'est le controller qui s'occupe de rédiriger/contrôler les retours de oauth
    - /Middleware : Contient les fichiers qui vont contrôler les requêtes entrantes (notamment avec la gestion des JWT dans AuthApi)
      - Authenticate.php : Redirige le user sur la route login (déclarée dans /route/web.php) si nécessaire
      - RedirectIfAuthenticated.php : Redirige les requêtes des users identifiés en passant par le RouteServiceProvider (cf. Providers/)
      - TrimStrings.php : Supprime les espaces en début et en fin de chaîne pour toutes les données entrantes
      - TrustHosts.php : Spécifie les hôtes de confiance pour éviter les attaques de redirection
      - TrustProxies.php : Gère les proxies de confiance pour la gestion correcte des adresses IP et du protocole
    - Kernel.php : Enregistre les middlewares globaux et de groupe pour gérer les requêtes HTTP
  - /Models : Contient tous les Models de ton serveur (un modèle décrit comment les entrées de ta BDD seront traités comme objets)
    - User : Notre modèle basique pour gérer les utilisateurs de la BDD
  - /Providers : Contient les classes de service qui fournissent des fonctionnalités clés à l'application
    - AppServiceProvider.php : Enregistre les services globaux utilisés dans l'application
    - AuthServiceProvider : Gère les politiques d'autorisation et l'enregistrement des guards (guards = détermine comment les utilisateurs sont authentifiés à chaque requête)
- /bootstrap : C'est le dossier qui gère le boot de ton serveur
  - app.php : C'est le fichier qui va load tout ton projet et notamment les services et Kernel
  - providers.php : Charge les services de configuration du serveur
- /config : Contient tous les fichiers de config du serveur (en vrai c'est un peu redondant avec .env, mais ça permet une meilleure gestion du cache)
  - app.php : Config général (nom de l'app, URL, timezone, ...)
  - auth.php : Config de l'authentification (guards, gestion des mdp, paramètres de oauth...)
  - cache.php : Comment est géré le cache
  - cors.php : Config des CORS (Cross-Origin Ressource Sharing) (nécessaire pour faire des requêtes entre différents domaines)
  - database.php : Config BDD
  - filesystem.php : Gère la configuration des systèmes de fichiers utilisés pour le stockage
  - jwt : Config de la gestion des JWT (temps de vie, algo de chiffrement, ...)
  - logging.php : Configuration du système de logs
  - mail.php : Configuration pour l'envoi des emails via des services comme SMTP (out autre)
  - queue.php : Configuration des queues de travail pour le traitement des tâches en arrière-plan
  - sanctum.php : Gère la configuration de l'authentification API via Sanctum (alternative aux JWT)
  - services.php : Gère la configuration des services externes (genre AWS)
  - session.php : Configuration du système de gestion des sessions utilisateur
- /database : Dossier de la BDD
  - database.sqlite : Fichier à créer qui te servira de BDD local pour dév
  - /factories : Contient les modèles de données fictives pour ta BDD
  - /migrations : Contient les fichiers de création de ta BDD (1 fichier = 1 table)
  - /seeders : Contient les scripts de remplissage de ta BDD
- /public : 
  - .htaccess : Fichier qui gère la redirection et l'accès à tes pages web
  - index.php : Point d'entrée pour toutes les requêtes HTTP dans l'application Laravel
  - robots.txt : Fichier utilisé pour gérer l'accès des robots de moteurs de recherche au site
- /ressources : Contient les Views (pages), CSS et JS pour générer tes pages web
  - /css : Contient le CSS de tes views
  - /js : Contient le JS de tes views
  - /views : Contient les views (fichier qui décrit la structure d'une page php, et qui peut appeler tes Controllers (genre pour faire du Back Office))
- /routes : Contient les fichiers de config pour déclarer tes routes URL (web et API)
  - api.php : Contient toutes tes routes API, leur nom et potentiellement le passage par le Middleware Auth
  - console.php : Définit les commandes artisan personnalisées exécutables depuis la console
  - web.php : Contient toutes tes routes web, leur nom et potentiellement le passage par le Middleware AuthBackOffice
- /storage : Dossier de stockage de ton serveur (logs, stockage et ressources nécessaires à l'app)
  - /app : Contient les fichiers nécessaires au fonctionnement de ton app
    - /public/key.pub : C'est la clé publique pour chiffrer les JWT
    - /private/private_key.pem : C'est la clé privée pour déchiffrer les JWT
    - /framework : Stocke le cache, les sessions et les fichiers temporaires générés par Laravel
    - /logs : Bah c'est les logs
- /tests : Ce dossier contient les fichiers pour tester ton serveur 
  - /Feature : Contient les tests qui vérifient les fonctionnalités complètes de l'application
  - /Unit : Contient les tests unitaires qui vérifient des portions spécifiques du code (genre une fonction ou une méthode)
  - TestCase.php : Classe de base pour les tests, gérant la configuration et l'environnement des tests
  
## Post-Scriptum
Pour celleux qui reprendront/s'inspireront de ce serveur, pensez bien à modifier les clés de chiffrement public.pem et private.pem : celles présentent dans le repo ne servent qu'à simuler l'utilisation du clé pour plus tard
