Lancer tous les containers (en mode daemon)
```bash
docker compose up -d
```

Relancer tous les containers
```bash
docker compose down --volumes --remove-orphans
docker compose up -d --build
```

Relancer juste le container laravel-app
```bash
docker compose down laravel-app -v --remove-orphans
docker compose up -d laravel-app --build
```

Lancer un bash dans le container php
```bash
docker exec -it laravel-app bash
```