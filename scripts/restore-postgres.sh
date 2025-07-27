#!/bin/bash

# PostgreSQL S3 Restore Script for Know API
# Usage: ./restore-postgres.sh [backup-filename] [--latest]

set -e

# Configuration
BACKUP_DIR="/tmp/db-restores"
S3_BUCKET="know-api-backups"
DB_CONTAINER="know-postgres"
DB_NAME="know_api"
DB_USER="know_user"

# Parse arguments
BACKUP_FILE="$1"
LATEST_FLAG="$2"

echo "🔄 Starting PostgreSQL restore for Know API..."

# Create restore directory
mkdir -p $BACKUP_DIR
cd $BACKUP_DIR

if [ "$BACKUP_FILE" = "--latest" ] || [ "$LATEST_FLAG" = "--latest" ]; then
    echo "🔍 Finding latest backup..."
    BACKUP_FILE=$(aws s3 ls s3://$S3_BUCKET/daily/ --recursive | sort | tail -n 1 | awk '{print $4}')
    if [ -z "$BACKUP_FILE" ]; then
        echo "❌ No backups found in S3"
        exit 1
    fi
    echo "📁 Latest backup: $BACKUP_FILE"
    BACKUP_FILE=$(basename "$BACKUP_FILE")
fi

if [ -z "$BACKUP_FILE" ]; then
    echo "❌ Usage: $0 <backup-filename> [--latest]"
    echo "   or: $0 --latest"
    echo ""
    echo "Available backups:"
    aws s3 ls s3://$S3_BUCKET/daily/ | awk '{print $4}'
    exit 1
fi

# Download backup from S3
echo "⬇️ Downloading backup from S3..."
aws s3 cp s3://$S3_BUCKET/daily/$BACKUP_FILE .

if [ ! -f "$BACKUP_FILE" ]; then
    echo "❌ Failed to download backup file"
    exit 1
fi

# Decrypt if needed
if [[ "$BACKUP_FILE" == *.gpg ]]; then
    echo "🔓 Decrypting backup..."
    if [ -z "$BACKUP_ENCRYPTION_KEY" ]; then
        echo "❌ BACKUP_ENCRYPTION_KEY required for encrypted backups"
        exit 1
    fi
    
    DECRYPTED_FILE="${BACKUP_FILE%.gpg}"
    gpg --batch --yes --decrypt --passphrase "$BACKUP_ENCRYPTION_KEY" \
        --output "$DECRYPTED_FILE" "$BACKUP_FILE"
    
    if [ ! -f "$DECRYPTED_FILE" ]; then
        echo "❌ Failed to decrypt backup"
        exit 1
    fi
    
    RESTORE_FILE="$DECRYPTED_FILE"
else
    RESTORE_FILE="$BACKUP_FILE"
fi

# Confirmation prompt
echo "⚠️ WARNING: This will replace the current database!"
echo "Database: $DB_NAME"
echo "Backup file: $RESTORE_FILE"
echo "Backup size: $(ls -lh $RESTORE_FILE | awk '{print $5}')"
echo ""
read -p "Are you sure you want to continue? (type 'yes' to confirm): " confirm

if [ "$confirm" != "yes" ]; then
    echo "❌ Restore cancelled"
    exit 1
fi

# Create backup of current database before restore
CURRENT_BACKUP="current_db_backup_$(date +%Y%m%d_%H%M%S).sql"
echo "💾 Creating backup of current database..."
docker exec $DB_CONTAINER pg_dump -U $DB_USER -d $DB_NAME > $CURRENT_BACKUP

# Stop application temporarily
echo "🛑 Stopping application containers..."
ssh -i ~/.ssh/jordanpartridge-containers.pem ec2-user@54.193.154.122 \
    "cd /tmp && docker-compose stop app"

# Restore database
echo "🔄 Restoring database..."

# Drop and recreate database
docker exec $DB_CONTAINER psql -U $DB_USER -d postgres -c "DROP DATABASE IF EXISTS $DB_NAME;"
docker exec $DB_CONTAINER psql -U $DB_USER -d postgres -c "CREATE DATABASE $DB_NAME;"

# Restore from backup
docker exec -i $DB_CONTAINER psql -U $DB_USER -d $DB_NAME < $RESTORE_FILE

if [ $? -eq 0 ]; then
    echo "✅ Database restored successfully"
else
    echo "❌ Database restore failed"
    echo "🔄 Attempting to restore from current backup..."
    docker exec -i $DB_CONTAINER psql -U $DB_USER -d $DB_NAME < $CURRENT_BACKUP
    exit 1
fi

# Restart application
echo "🚀 Starting application containers..."
ssh -i ~/.ssh/jordanpartridge-containers.pem ec2-user@54.193.154.122 \
    "cd /tmp && docker-compose start app"

# Run any pending migrations
echo "🔄 Running migrations..."
ssh -i ~/.ssh/jordanpartridge-containers.pem ec2-user@54.193.154.122 \
    "docker exec know-api php artisan migrate --force"

# Health check
echo "🔍 Performing health check..."
sleep 10
if curl -f https://know.jordanpartridge.us/health > /dev/null 2>&1; then
    echo "✅ Application is healthy after restore"
else
    echo "⚠️ Health check failed - check application logs"
fi

# Cleanup
rm -f $RESTORE_FILE $BACKUP_FILE $CURRENT_BACKUP

echo "🎉 Database restore completed successfully!"
echo "📊 Restore completed at $(date)"