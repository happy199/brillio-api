#!/bin/bash

# Script de backup automatique (DB + Fichiers)
# À exécuter via cron : 0 2 * * * /path/to/backup-mysql.sh

set -e

# Configuration
BACKUP_DIR="/var/backups/brillio"
DB_NAME="${DB_DATABASE:-brillioapi}"
DB_USER="${DB_USERNAME:-root}"
DB_PASS="${DB_PASSWORD}"
DB_HOST="${DB_HOST:-localhost}"
RETENTION_DAYS=30
DATE=$(date +%Y%m%d_%H%M%S)

# Fichiers de backup
BACKUP_SQL="${BACKUP_DIR}/backup_${DB_NAME}_${DATE}.sql.gz"
BACKUP_FILES="${BACKUP_DIR}/backup_files_${DATE}.tar.gz"

# Répertoires à sauvegarder
APP_ROOT="/opt/brillio-api"
STORAGE_DIR="${APP_ROOT}/storage/app"

# Créer le répertoire de backup s'il n'existe pas
mkdir -p "$BACKUP_DIR"

# 1. Backup de la Base de Données
echo "🔄 Début du backup de la base de données ${DB_NAME}..."
mysqldump -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" \
    --single-transaction \
    --quick \
    --lock-tables=false \
    --routines \
    --triggers \
    --events \
    | gzip > "$BACKUP_SQL"

if [ $? -eq 0 ]; then
    echo "✅ Backup SQL créé : $BACKUP_SQL ($(du -h "$BACKUP_SQL" | cut -f1))"
else
    echo "❌ Erreur lors du backup SQL"
    exit 1
fi

# 2. Backup des Fichiers (LinkedIn PDFs, Photos, etc.)
echo "📂 Début du backup des fichiers de stockage..."
if [ -d "$STORAGE_DIR" ]; then
    tar -czf "$BACKUP_FILES" -C "$STORAGE_DIR" .
    echo "✅ Backup fichiers créé : $BACKUP_FILES ($(du -h "$BACKUP_FILES" | cut -f1))"
else
    echo "⚠️ Répertoire de stockage non trouvé : $STORAGE_DIR"
fi

# 3. Nettoyage des anciens backups
echo "🗑️  Suppression des backups de plus de ${RETENTION_DAYS} jours..."
find "$BACKUP_DIR" -name "backup_*.gz" -mtime +$RETENTION_DAYS -delete

# Liste récapitulative
echo "📋 Backups disponibles dans $BACKUP_DIR :"
ls -lh "$BACKUP_DIR" | grep "backup_"

echo "✅ Sauvegarde terminée avec succès"
