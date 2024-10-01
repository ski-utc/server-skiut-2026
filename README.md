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

### 2. Lance le serveur

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
