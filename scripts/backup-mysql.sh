#!/bin/bash

# Script de backup MySQL automatique
# À exécuter via cron : 0 2 * * * /path/to/backup-mysql.sh

set -e

# Configuration
BACKUP_DIR="/var/backups/mysql"
DB_NAME="${DB_DATABASE:-brillioapi}"
DB_USER="${DB_USERNAME:-root}"
DB_PASS="${DB_PASSWORD}"
DB_HOST="${DB_HOST:-localhost}"
RETENTION_DAYS=30
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_FILE="${BACKUP_DIR}/backup_${DB_NAME}_${DATE}.sql.gz"

# Créer le répertoire de backup s'il n'existe pas
mkdir -p "$BACKUP_DIR"

# Effectuer le backup
echo "🔄 Début du backup de la base de données ${DB_NAME}..."
mysqldump -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" \
    --single-transaction \
    --quick \
    --lock-tables=false \
    --routines \
    --triggers \
    --events \
    | gzip > "$BACKUP_FILE"

# Vérifier que le backup a réussi
if [ $? -eq 0 ]; then
    echo "✅ Backup créé avec succès : $BACKUP_FILE"
    FILE_SIZE=$(du -h "$BACKUP_FILE" | cut -f1)
    echo "📦 Taille du fichier : $FILE_SIZE"
else
    echo "❌ Erreur lors du backup"
    exit 1
fi

# Supprimer les backups de plus de X jours
echo "🗑️  Suppression des backups de plus de ${RETENTION_DAYS} jours..."
find "$BACKUP_DIR" -name "backup_*.sql.gz" -mtime +$RETENTION_DAYS -delete

# Lister les backups restants
echo "📋 Backups disponibles :"
ls -lh "$BACKUP_DIR"/backup_*.sql.gz 2>/dev/null || echo "Aucun backup trouvé"

echo "✅ Backup terminé avec succès"
