#!/bin/bash

# Setup S3 backups for Know API PostgreSQL
# Run this once to configure the backup system

set -e

S3_BUCKET="know-api-backups"
BACKUP_SCRIPT="/home/ec2-user/backup-postgres.sh"
RESTORE_SCRIPT="/home/ec2-user/restore-postgres.sh"

echo "🔧 Setting up S3 backups for Know API..."

# Create S3 bucket
echo "☁️ Creating S3 bucket..."
aws s3 mb s3://$S3_BUCKET --region us-west-1 || echo "Bucket may already exist"

# Set bucket policy for lifecycle management
cat > /tmp/lifecycle-policy.json << 'EOF'
{
    "Rules": [
        {
            "ID": "KnowAPIBackupLifecycle",
            "Status": "Enabled",
            "Filter": {
                "Prefix": "daily/"
            },
            "Transitions": [
                {
                    "Days": 7,
                    "StorageClass": "STANDARD_IA"
                },
                {
                    "Days": 30,
                    "StorageClass": "GLACIER"
                },
                {
                    "Days": 90,
                    "StorageClass": "DEEP_ARCHIVE"
                }
            ],
            "Expiration": {
                "Days": 365
            }
        }
    ]
}
EOF

echo "📋 Setting up S3 lifecycle policy..."
aws s3api put-bucket-lifecycle-configuration \
    --bucket $S3_BUCKET \
    --lifecycle-configuration file:///tmp/lifecycle-policy.json

# Create backup encryption key
echo "🔐 Setting up backup encryption..."
ENCRYPTION_KEY=$(openssl rand -base64 32)
echo "export BACKUP_ENCRYPTION_KEY='$ENCRYPTION_KEY'" >> ~/.bashrc

# Copy backup scripts to server
echo "📁 Deploying backup scripts..."
ssh -i ~/.ssh/jordanpartridge-containers.pem ec2-user@54.193.154.122 << 'EOF'
    # Create backup directory
    mkdir -p /home/ec2-user/scripts
    
    # Set environment variables
    echo "export BACKUP_ENCRYPTION_KEY='$ENCRYPTION_KEY'" >> ~/.bashrc
    echo "export AWS_DEFAULT_REGION='us-west-1'" >> ~/.bashrc
    
    # Source the bashrc
    source ~/.bashrc
EOF

# Copy scripts
scp -i ~/.ssh/jordanpartridge-containers.pem \
    scripts/backup-postgres.sh \
    ec2-user@54.193.154.122:/home/ec2-user/scripts/

scp -i ~/.ssh/jordanpartridge-containers.pem \
    scripts/restore-postgres.sh \
    ec2-user@54.193.154.122:/home/ec2-user/scripts/

# Set up cron job
ssh -i ~/.ssh/jordanpartridge-containers.pem ec2-user@54.193.154.122 << 'EOF'
    # Make scripts executable
    chmod +x /home/ec2-user/scripts/*.sh
    
    # Add cron job for daily backups at 2 AM
    (crontab -l 2>/dev/null || echo "") | grep -v "backup-postgres" | crontab -
    (crontab -l 2>/dev/null; echo "0 2 * * * /home/ec2-user/scripts/backup-postgres.sh >> /var/log/backup.log 2>&1") | crontab -
    
    # Create log file
    sudo touch /var/log/backup.log
    sudo chown ec2-user:ec2-user /var/log/backup.log
    
    echo "📋 Cron job added:"
    crontab -l | grep backup-postgres
EOF

echo "✅ Backup system setup complete!"
echo ""
echo "📊 Summary:"
echo "- S3 Bucket: s3://$S3_BUCKET"
echo "- Daily backups at 2:00 AM UTC"
echo "- Lifecycle: 7d → IA, 30d → Glacier, 90d → Deep Archive, 365d → Delete"
echo "- Encrypted backups with auto-generated key"
echo ""
echo "🔧 Manual commands:"
echo "- Test backup: ssh know-ssh 'cd scripts && ./backup-postgres.sh'"
echo "- List backups: aws s3 ls s3://$S3_BUCKET/daily/"
echo "- Restore: ssh know-ssh 'cd scripts && ./restore-postgres.sh --latest'"