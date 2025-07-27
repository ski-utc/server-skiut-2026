Lancer tous les containers
```bash
docker compose up -d
```

Relancer tous les containers
```bash
docker compose down --volumes --remove-orphans
docker compose up -d --build
```

Relancer juste le container php
```bash
docker compose down php --volumes --remove-orphans
docker compose up -d php
```

Lancer un bash dans le container php
```bash
docker exec -it laravel-app bash
```