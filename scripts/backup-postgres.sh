#!/bin/bash

# PostgreSQL S3 Backup Script for Know API
# Runs daily via cron on EC2 instance

set -e

# Configuration
BACKUP_DIR="/tmp/db-backups"
S3_BUCKET="know-api-backups"
DB_CONTAINER="know-postgres"
DB_NAME="know_api"
DB_USER="know_user"
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_FILE="know_api_backup_${DATE}.sql"
ENCRYPTED_FILE="${BACKUP_FILE}.gpg"

# Retention settings
KEEP_LOCAL_DAYS=3
KEEP_S3_DAYS=30

echo "🗃️ Starting PostgreSQL backup for Know API..."

# Create backup directory
mkdir -p $BACKUP_DIR
cd $BACKUP_DIR

# Create database dump
echo "📦 Creating database dump..."
docker exec $DB_CONTAINER pg_dump -U $DB_USER -d $DB_NAME > $BACKUP_FILE

if [ ! -f $BACKUP_FILE ] || [ ! -s $BACKUP_FILE ]; then
    echo "❌ Backup failed - file is empty or doesn't exist"
    exit 1
fi

# Encrypt backup (using symmetric encryption)
echo "🔐 Encrypting backup..."
if [ -z "$BACKUP_ENCRYPTION_KEY" ]; then
    echo "⚠️ Warning: BACKUP_ENCRYPTION_KEY not set, skipping encryption"
    UPLOAD_FILE=$BACKUP_FILE
else
    gpg --batch --yes --cipher-algo AES256 --compress-algo 1 \
        --symmetric --passphrase "$BACKUP_ENCRYPTION_KEY" \
        --output $ENCRYPTED_FILE $BACKUP_FILE
    UPLOAD_FILE=$ENCRYPTED_FILE
    rm $BACKUP_FILE  # Remove unencrypted file
fi

# Upload to S3
echo "☁️ Uploading to S3..."
aws s3 cp $UPLOAD_FILE s3://$S3_BUCKET/daily/ \
    --storage-class STANDARD_IA \
    --metadata "created=$(date -Iseconds),database=$DB_NAME,version=1.0"

if [ $? -eq 0 ]; then
    echo "✅ Backup uploaded successfully: s3://$S3_BUCKET/daily/$UPLOAD_FILE"
else
    echo "❌ S3 upload failed"
    exit 1
fi

# Clean up local files older than X days
echo "🧹 Cleaning up local backups older than $KEEP_LOCAL_DAYS days..."
find $BACKUP_DIR -name "know_api_backup_*.sql*" -type f -mtime +$KEEP_LOCAL_DAYS -delete

# Clean up old S3 backups
echo "🧹 Cleaning up S3 backups older than $KEEP_S3_DAYS days..."
OLD_DATE=$(date -d "$KEEP_S3_DAYS days ago" +%Y%m%d)
aws s3 ls s3://$S3_BUCKET/daily/ | while read -r line; do
    FILE_DATE=$(echo $line | awk '{print $4}' | grep -o '[0-9]\{8\}' | head -1)
    FILE_NAME=$(echo $line | awk '{print $4}')
    
    if [ ! -z "$FILE_DATE" ] && [ "$FILE_DATE" -lt "$OLD_DATE" ]; then
        echo "Deleting old backup: $FILE_NAME"
        aws s3 rm s3://$S3_BUCKET/daily/$FILE_NAME
    fi
done

# Health check - verify backup exists in S3
echo "🔍 Verifying backup in S3..."
if aws s3 ls s3://$S3_BUCKET/daily/$UPLOAD_FILE > /dev/null 2>&1; then
    echo "✅ Backup verification successful"
    
    # Send success notification (optional)
    if command -v curl > /dev/null 2>&1 && [ ! -z "$HEALTH_CHECK_URL" ]; then
        curl -fsS -m 10 --retry 5 -o /dev/null "$HEALTH_CHECK_URL" || true
    fi
else
    echo "❌ Backup verification failed"
    exit 1
fi

echo "🎉 Backup completed successfully at $(date)"
echo "📊 Backup size: $(ls -lh $UPLOAD_FILE 2>/dev/null | awk '{print $5}' || echo 'Unknown')"