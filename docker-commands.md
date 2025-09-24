Lancer tous les containers (serveur + bdd mysql + phpMyAdmin + Grafana + Prometheus)
```bash
docker compose up -d
```

Relancer tous les containers
```bash
docker compose down --volumes --remove-orphans
docker compose up -d --build
```

Relancer juste le container laravel-app (après une modification dans le serveur par exemple)
```bash
docker compose down laravel-app -v --remove-orphans
docker compose up -d laravel-app --build
```

Lancer un bash dans le container php
```bash
docker exec -it laravel-app bash
```

Lancer une commande php dans un container (exemple avec `php artisan db:seed`)
```bash
docker exec -it laravel-app php artisan db:seed
```