# Configuration Grafana

Ce répertoire contient la configuration automatique pour Grafana.

## Structure

- `provisioning/datasources/` : Configuration des sources de données
- `provisioning/dashboards/` : Configuration des dashboards
- `dashboards/` : Fichiers JSON des dashboards

## Sources de données

La source de données Prometheus est automatiquement configurée pour pointer vers `http://prometheus:9090`.

## Dashboards

Le dashboard "Laravel Application Metrics" est automatiquement importé au démarrage.

## Utilisation

1. Démarrer les conteneurs : `docker-compose up -d`
2. Accéder à Grafana : http://localhost:3000
3. Se connecter avec admin/admin
4. La source de données Prometheus et le dashboard Laravel sont déjà configurés

## Personnalisation

Pour ajouter de nouveaux dashboards :
1. Créer un fichier JSON dans `dashboards/`
2. Redémarrer Grafana : `docker-compose restart grafana`

Pour modifier la source de données :
1. Éditer `provisioning/datasources/prometheus.yml`
2. Redémarrer Grafana : `docker-compose restart grafana`
