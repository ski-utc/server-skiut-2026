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

### 3. Lance le serveur

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
Il en est de même pour **auth** sur /skiutc/auth et **api** sur /skiutc/api


## On rentre dans le détail du fonctionnement
### 1. Authentification
Toute l'authentification est gérée par le Auth Controller.
Globalement, le User requpete une première fois sur /auth/login. Si tu as activé le bypass login, le user recevra alors directement ses tokens d'accès (on y revient soon). Sinon, un Provider est construit à partir du format donné dans la doc de l'OAuth du SIMDE, et un state est crée pour identifier la session qui vient d'essayer de se connecter.
Ensuite, le user est redirigé sur l'OAuth du SIMDE où il peut se connecter avec son CAS, ou par mail. Les champs récupérés sont .................................................................................................. 
Une fois que le user s'est connecté, le serveur récupère la requête avec la fonction callback qui vérifie qu'on est toujours sur la session qui a essayé de se login, et récupères les info sur le user. Il y a ensuite une série de vérification pour vérifier que le compte est existant, actif, mais surtout que l'adresse mail est déjà dans la BDD (qui a été préalablement seed avec les adresses mails des personnes qui ont payé sur Wooch). Grâce à celà, l'app n'est accessible qu'à celleux connecté.e.s.
Finalement, la fonctionn callback construit un accessToken et un refreshToken à partir du userID et d'un temps d'expiration, le tout chiffré en SHA-256. L'accessToken permet d'accéder directement aux données sur le serveur (de s'identifier), et le refreshToken de refresh son accessToken (il ne donne pas directement accès au serveur, mais permet de refresh l'accessToken sans avoir à se reconnecter. Cela permet de gérer le nombre de user co en même temps au serveur). Ces tokens sont ensuite stockés dans les headers des requêtes.
La fonction refresh permet justement de faire se travail de déchiffrer le refreshToken pour reconstruire un accessToken.

### 2. Middleware
Une fois le user authentifié, toutes ses requêtes vont passer par un middleware. Le middleware c'est ce qui va filtrer les requêtes pour ne donner accès au serveur qu'aux users identifiés, tout en ayant leur ID. 
Toute route sur laquelle tu veux appliquer ce "filtre" doit avoir la forme : Route::get('/nomUrl', [\App\Http\Controllers\monController::class, 'maFonction'])->middleware(\App\Http\Middleware\EnsureTokenIsValid::class);
La fonction EnsureTokenIsValid s'occupe de récupérer le token en header, le déchiffre, réalise une série de test dessus (inutile de détailler), récupère le user correspondant dans la BDD et ajoute un champ 'user' qui contient un array avec toutes les infos du user à la requête.

### 3. Controllers
Les controllers sont normalement assez bien organisés et assez clairs (il me semble). Tu trouveras dans Admin tout ce qui touche l'onglet admin de l'app, dans Skinder tout ce qui touche à Skinder etc...
S'il y a peut-être un point important à mentionner c'est que grâce au middleware, on peut accéder à toutes les infos du User comme ceci : $user = $request->user;

## Déployer le serveur 


## Post-Scriptum
Pour celleux qui reprendront/s'inspireront de ce serveur, pensez bien à modifier les clés de chiffrement public.pem et private.pem : celles présentent dans le repo ne servent qu'à simuler l'utilisation du clé pour plus tard
