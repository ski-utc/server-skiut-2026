# Bienvenue sur le serveur de Ski'UT 2025 en Laravel

## Introduction
Ce serveur est fait pour tourner avec l'application expo de Ski'UT développée en 2025, puis mise à jour en 2026.

Ce serveur expose à la fois 
* Une API utilisée par l'application mobile
* Un back-office (interface admin pour navigateur web) développée avec le framework Filament PHP

Pour dev sur le backend, tu as deux options : 
1. Faire tourner le serveur avec Docker [cf. ci-dessous](#pour-commencer--docker)
2. Faire tourner le serveur avec PHP [cf ci-dessous](#pour-commencer--php-classique)

Personnelement, je te recommande d'utiliser Docker car ça te permet de faire également tourner une base de données MySQL ainsi qu'une interface PHPMyAdmin pour gérer tes données de test plus facilement.

---

## Structure du projet

```bash
├── app
├── bootstrap
├── config
├── database
├── docker                 # Config utilisée par Docker : il y a une config pour Nginx, et une pour supervisord
├── public
├── resources
├── routes
├── storage
├── tests                  # Série de tests Laravel qui permettent de s'assurer que tous les endpoints fonctionnent bien (ex. GET anecdotes renvoie bien les anecdotes)
├── artisan
├── composer.json
├── composer.lock
├── CONTRIBUTING.md
├── docker-commands.md     # Un petit mémo des commandes Docker utiles
├── docker-compose.yml     # Fichier de configuration pour Docker Compose : s'occupe de lancer le serveur, une BDD et PhpMyAdmin
├── Dockerfile             # Fichier de configuration pour Docker : s'occupe de construire une "image" du serveur
├── laravel-start.sh       # Script utilisé lorsqu'une image Docker est lancée pour finir de setup le serveur Larave
├── LICENSE
├── package.json
├── package-lock.json
├── phpunit.xml
├── postcss.config.cjs
├── tailwind.config.js
└── vite.config.js
```

Je n'ai volontairement pas trop parlé des éléments qui font parti de n'importe quel projet Laravel (`app`, `config`, etc.) pour éviter de rendre ça trop lourd. Si jamais tu as des doutes sur qui fait quoi là-dedans, je t'invite à regarder quelques petits tuto sur Laravel en ligne :)

---

## Pour commener : Docker

Pour permettre de dev sur toutes les OS, avec un déploiement facile, et une infra proche de la production, une alternative Docker a été proposée pour le serveur Ski'ut.
Attention, c'est bien une alternative, rien n'oblige de l'utiliser, mais c'est "mieux" (et surtout c'est super utile d'apprendre à utiliser Docker).

### Avant toute chose, c'est quoi Docker ?

En informatique, il arrive souvent qu’une application fonctionne parfaitement sur un ordinateur… mais qu’une fois installée ailleurs (par exemple sur un serveur en ligne), elle ne marche plus.
Pourquoi ? Parce que l’environnement n’est pas le même :
* Sur ton PC, tu as peut-être PHP 9.0,
* alors que sur le serveur, il n’y a que PHP 6.0.
* Tu pourrais installer PHP 9.0 sur ton serveur, mais qui te dit que tu ne vas pas fumer un autre serveur qui lui tourne sur du PPHP 6.0 ?

#### La solution à ce problème : Docker

Docker sert à éviter ce genre de problème.
Il permet de créer un environnement indépendant, qui contient tout ce dont ton application a besoin pour fonctionner, peu importe la machine sur laquelle tu l’exécutes.

#### Concrètement, Docker :
* Crée un espace isolé (comme un dossier spécial).
* Copie ton application dedans.
* Installe tout ce qui est nécessaire :
  * la bonne version de PHP,
  * les outils en ligne de commande,
  * les dépendances (par exemple ce qui est listé dans composer.json).
  * Prépare un point d’accès via un port.

#### Exemple avec un port

Un port, c’est comme une porte d’entrée vers ton application.

Si Docker ouvre le port 8000, tu peux accéder à ton serveur en tapant dans ton navigateur : `http://localhost:8000`
Grâce à ça, le serveur de Ski'ut tournera aussi bien en ligne que sur nos PCs !

### 1. Installer Docker
Pour installer Docker, suivre les instructions [ici](https://docs.docker.com/engine/install/).

### 2. Installer Docker Compose
Docker Compose permet de lancer plusieurs containers en même temps. Il est donc nécessaire de le faire pour pouvoir faire tourner le serveur, la base de données MySQL et phpMyAdmin.

Pour installer Docker Compose, suivre les instructions [ici](https://docs.docker.com/compose/install/).

### 3. Lancer tout ça
Pour lancer tous les containers, il faut d'abord modifier la config url de laravel pour que le serveur soit accessible depuis l'extérieur. 

Pour cela, il faut modifier la variable APP_URL pour y mettre son IP (hostname -I) dans le fichier .env.local. Le docker-compose utilise ensuite ce fichier pour injecter les variables dans le container php (par défaut l'image Docker a utilisé .env.ci pour construire l'image dans la pipeline GitLab, mais comme l'url est sur localhost on utilise .env.local pour corriger ça. t'inquiètes pas pour la pipeline, on en reparle plus tard).

Ensuite, place-toi dans le dossier du serveur, et lance :
```bash
docker compose up -d
```
Tu trouveras dans docker-commands.md toutes les commandes essentielles avec docker (notamment parce qu'il faut redémarrer les containers pour que les changements dans le code soient pris en compte).

Bilan, tu auras maintenant : 
* http://localhost:8000 pour le serveur
* http://localhost:8081 pour phpMyAdmin

Pour permettre à l'application de contacter le serveur, il faudra modifier le fichier `constants/api/apiConfig.ts` pour contacter ton adresse ip (celle que tu obtiens faisant `ip address` ou `ifconfig`).

### Quelques mots sur la config Docker

Bon... cette partie va nécessairement être un peu abstraite.

Grosso modo, on a deux moyen de faire tourner du PHP : 
* Soit on a un serveur Apache, qui compile le code PHP, et le sert
  * C'est pas hyper puissant, ça plante facilement, bref c'est pas ouf
* Soit on utilise 3 instances : 
  * PHP-FPM qui compile le code PHP
  * Nginx qui sert le code compilé par PHP-FPM
  * Supervisor qui lance Nginx et PHP-FPM
  * La bonne nouvelle avec cette deuxième approche, c'est qu'on peut custom les paramètres de PHP-FPM dans `/usr/local/etc/php-fpm.d/www.conf` pour qu'il soit boosté de malade.

Tout ça pour dire que dans notre cas, on utilise Nginx et PHP-FPM, parce que même si c'est un poil plus complexe, ça tourne beaucoup mieux.

---

## Pour commencer : Php Classique
### 1. Installer PHP

Il y a 1 milliard de façon d'installer PHP : avec homebrew sur MAC/Linux, avec chocolatey ou un executable sur Windows...

- Installer avec Homebrew :
``` bash
/bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)" 
brew install php
```

- Installer avec un executable Windows
[Lien vers la page de download](https://windows.php.net/download/)

### 2. Installer composer pour gérer les deps

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

### 3. Cloner les packages de deps avec composer
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

### 4. Base de données
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
php artisan db:seed
```
_En théorie vous pouvez directement faire $ php artisan migrate sans créer la BDD, il vous proposera de le faire_

### 3. Générer les clés RSA pour les JWT

Pour générer les clés RSA pour les JWT, une commande Artisan a été créée, fait :
```bash
php artisan jwt:generate
```

### 4. Lance le serveur

- Si tu veux tester des routes sur le serveur dans ton navigateur (en localhost), fait
```bash
php artisan serve --port=8000
```
- Si tu veux faire des requêtes sur ton serveur depuis l'app, il faut que ton serveur soit accessible, et pas sur une adresse de bouclage localhost. 
Du coup, récupère ton IP avec
```bash
 ipconfig
```
OU
```
ip a | grep inet 
```
Et entre la commande suivante en remplaçant <> par ton IP
```bash
php artisan serve --host=<> --port=8000
```
Maintenant ton serveur est déployé sur l'IP de ton PC est donc accessible aux appareils sur ton réseau.
En théorie tu aurais pu lancer un émulateur dans le navigateur et rester et localhost (les requêtes auraient juste bouclées sur ta machine). En pratique, les émulateurs web ne supportent pas la library des webview sur expo.

### 4. Lance npm pour build le CSS
Ouvre un nouvel onglet terminal, et lance :
```bash
npm run dev
```
Cette commande permet de lancer tailwind pour qu'il build bien tes views (les deux seuls étaient api-connected et api-not-connected).

### 5. En théorie le serveur tourne

**Attention** : le serveur est configuré pour tourner sur une base URL /skiutc. Concrètement, le serveur commence à te renvoyer des trucs sur http://tonIP/skiutc/.
Il en est de même pour **auth** sur /skiutc/auth et **api** sur /skiutc/api. 

La raison pour cela est tout simplement parce que sur le serveur des assos, la base url est `https://assos.utc.fr/skiutc/`.

---

## `"Mais je ne vois pas le code du Shotgun dans le serveur"`

Si tu prévois de travailler sur le shotgun du serveur, tu vas avoir besoin de cloner la view du shotgun qui est gardée secrète jusqu'au jour du shotgun.
Pour faire ça, elle est gardée secrète dans un repo priv" `game-view` qui est importé en **submodule**.
Un submodule c'est tout simplement un repo GitHub dans un repo GitHub, c'est ce qui nous permet notamment d'avoir un code backend open-source, sans pour autant se faire cramer par les gens qui voudront tricher sur le shotgun

Ainsi, pour modifier et tester le mini-jeu, tu dois récupérer le submodule :
```bash
git submodule update --init --recursive
```

Pour enregistrer tes modifications sur le mini-jeu, tu dois donc bien penser à push sur le repo privé.

---

## Authentification
### 1. Authentification avec l'OAuth
Toute l'authentification est gérée par le Auth Controller.

Globalement, le User requpete une première fois sur /auth/login. Si tu as activé le bypass login, le user recevra alors directement ses tokens d'accès (on y revient soon). Sinon, un Provider est construit à partir du format donné dans la [doc de l'OAuth du SIMDE](https://auth.assos.utc.fr/admin/implement), et un state est crée pour identifier la session qui vient d'essayer de se connecter.

Ensuite, le user est redirigé sur l'OAuth du SIMDE où il peut se connecter avec son CAS, ou par mail.  

Une fois que le user s'est connecté, le serveur récupère la requête avec la fonction callback qui vérifie qu'on est toujours sur la session qui a essayé de se login, et récupères les info sur le user. Il y a ensuite une série de vérification pour vérifier que le compte est existant, actif, mais surtout que l'adresse mail est déjà dans la BDD (qui a été préalablement seed avec les adresses mails des personnes qui ont payé sur Wooch). Grâce à celà, l'app n'est accessible qu'à celleux connecté.e.s.

Finalement, la fonctionn callback construit un accessToken et un refreshToken à partir du userID et d'un temps d'expiration, le tout chiffré en SHA-256. L'accessToken permet d'accéder directement aux données sur le serveur (de s'identifier), et le refreshToken de refresh son accessToken (il ne donne pas directement accès au serveur, mais permet de refresh l'accessToken sans avoir à se reconnecter. Cela permet de gérer le nombre de user co en même temps au serveur). Ces tokens sont ensuite stockés dans les headers des requêtes.

La fonction refresh permet justement de faire se travail de déchiffrer le refreshToken pour reconstruire un accessToken.

### Schéma de fonctionnement complet de l'Authentification avec l'OAtuh du SIMDE :
![Schéma de fonctionnement de l'OAuth](https://auth.assos.utc.fr/img/oauth-flow.png)
_Merci le SIMDE pour le schéma_

### 2. Middleware
Une fois le user authentifié, toutes ses requêtes vont passer par un middleware. Le middleware c'est ce qui va filtrer les requêtes pour ne donner accès au serveur qu'aux users identifiés, tout en ayant leur ID. 

Toute route sur laquelle tu veux appliquer ce "filtre" doit avoir la forme : 
```php
Route::get('/nomUrl', [\App\Http\Controllers\monController::class, 'maFonction'])->middleware(\App\Http\Middleware\EnsureTokenIsValid::class);
```

La fonction EnsureTokenIsValid s'occupe de récupérer le token en header, le déchiffre, réalise une série de test dessus (inutile de détailler), récupère le user correspondant dans la BDD et ajoute un champ 'user' qui contient un array avec toutes les infos du user à la requête.

### 3. Controllers
Les controllers sont normalement assez bien organisés et assez clairs (il me semble). Tu trouveras dans Admin tout ce qui touche l'onglet admin de l'app, dans Skinder tout ce qui touche à Skinder etc...

S'il y a peut-être un point important à mentionner c'est que grâce au middleware, on peut accéder à toutes les infos du User comme ceci : $user = $request->user;

---

## Bypass l'OAuth

Pour éviter de constamment rentrer son CAS pour dev, j'ai mis un système de bypass dans le serveur.
Pour utiliser ça : 

1. Vérifie que APP_NO_LOGIN=true dans ton .env (.env.local si t'es en mode Docker) si tu ne veux pas t'occuper de l'Auth
2. Créé un User dans la base de données : c'est le user que te donneras par défaut le AuthController (cf. AuthController ligne 53). Par défaut j'ai mis '1' partout
3. Défini l'ID que tu viens de mettre dans ta BDD, dans ton .env sur la variable "USER_ID"

---

## Autres trucs mis en place
### 1. Tests
Des tests ont été mis en place pour tester tous les endpoints de l'API ainsi que le Middleware. Ils sont dans le dossier tests/Feature/ et peuvent être lancés avec la commande :
```bash
php artisan test
```

Il pourrait être intéressant de faire des tests pour les Models aussi.

### 2. Seeder et Factory
Les seeders sont dans le dossier database/seeders/ et permettent de créer des données de base. Ils sont utiles pour créér des données fakes pour le dev.
Les factories sont dans le dossier database/factories/ et permettent de créer des données fakes pour les tests. (ils sont appelés par les seeders).

### 3. Pipeline GitLab
Une pipeline GitLab a été mise en place sur le repo.
Elle s'occupe de : 
- Lancer tous les tests Laravel sur chaque push sur chaque branche
- Build et push l'image docker sur le registry gitlab.utc.fr sur chaque push sur la branche main
- Tester l'intégration du serveur dans le container docker sur le registry gitlab.utc.fr sur chaque push sur la branche main

Pour plus d'informations, tu peux voir le fichier .gitlab-ci.yml

---

## Déployer le serveur 
Avant toute chose, push tout ce que tu dois push pour préparer la version de production du serveur à déployer.

Dans cette version, veille à bien passer le serveur en production, le bypass du login à false, modifier le domain à assos.utc.fr, et modifier les accès dans la BDD pour utiliser la BDD MySQL du SIMDE dans le fichier .env

Ensuite, créé de nouvelles clés de chiffrement (le serveur ne pouvant tourner sans, on en a laissé dans le repo, mais il faut éviter d'utiliser les mêmes sur le serveur (sinon tout le monde peut chiffrer ses requêtes et se faire passer pour qlq d'autre)) : 
```sh
mkdir -p storage/app/public storage/app/private/keys
openssl genrsa -out storage/app/private/keys/private.pem 2048
openssl rsa -in storage/app/private/keys/private.pem -pubout -out storage/app/private/keys/public.pem
```

Une fois ça fait, il va falloir préparer des routes pour éxecuter des commandes. En fait le SIMDE ne donne que des accès SFTP et SSH, mais en SSH on a des droits limités. Du coup pour la plupart des commandes tu auras deux solutions : 
1. Run la commande en SSH si tu peux
2. Run la commande par URL
Rien d'incroyable pour la deuxième option, il suffit de faire des routes éphémères du type :

```php
use Illuminate\Support\Facades\Artisan;

Route::get('/migrate', function () {
    Artisan::call('migrate');
})->name('home');
```
Bien évidemment, il faut rajouter quelques sécurités là-dessus avec une clé privée par exemple.

Finalement, tu peux déployer ton serveur en SFTP sur les serveurs du SIMDE (avec FileZilla par exemple) (dans public_html). Tu peux suivre [ce tuto](https://assos.utc.fr/wiki/Acc%C3%A9der_%C3%A0_ses_donn%C3%A9es).

Ensuite, connectes-toi en SSH et fini le nécessaire pour que ton serveur serve bien (genre migrate la BDD, update les deps composer, ...).

Ici, pas besoin de faire un php artisan serve : les serveurs du SIMDE vont directement récup ton code sur files.mde.utc et le faire tourner sur leur serveur Apache.

---

## Des petits trucs pratiques

### Pour analyser la mise en forme du code tu peux faire :
```bash
./vendor/bin/php-cs-fixer fix --dry-run --diff
```

Et pour appliquer les corrections :
```bash
./vendor/bin/php-cs-fixer fix
```

### Pour faciliter la vie, créé des alias : 
Comme ça au lieu de taper `git commit -m "message"` tu peux taper `gcm "message"` ou `php artisan serve` tu peux taper `serve`

```bash
echo '########## Alias for git ##########
alias gs="git status"
alias ga="git add"
alias gaa="git add ."
alias gb="git branch"
alias gsw="git switch"
alias gco="git checkout"
alias gcb="git checkout -b"
alias gl="git log --oneline --graph --decorate"
alias gcm="git commit -m"
alias gca="git commit -am"
alias gpl="git pull"
alias gp="git push"
alias gpo="git push origin"
alias gcl="git clone"
alias gdf="git diff"
alias gstash="git stash"
alias gsta="git stash apply"

########## Alias for docker ##########
alias d="docker"
alias dps="docker ps"
alias dpsa="docker ps -a"
alias di="docker images"
alias drm="docker rm"
alias drmi="docker rmi"
alias dstop="docker stop"
alias dstart="docker start"
alias dexec="docker exec -it"
alias dlogs="docker logs -f"
alias dc="docker compose"
alias dcu="docker compose up -d"
alias dcub="docker compose up --build -d"
alias dcd="docker compose down"
alias dcb="docker compose build"

########## Alias for Laravel ##########
if [ -f "./artisan" ]; then
    alias art="php artisan"
    alias tinker="php artisan tinker"
    alias serve="php artisan serve"
    alias migrate="php artisan migrate"
    alias seed="php artisan db:seed"
    alias fresh="php artisan migrate:fresh --seed"
    alias clear="php artisan config:clear && php artisan cache:clear && php artisan route:clear && php artisan view:clear"
    alias route="php artisan route:list"
fi' >> ~/.bashrc
```
