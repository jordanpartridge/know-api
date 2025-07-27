#!/bin/bash

# Setup GitHub Webhook Deployment for Know API

set -e

echo "🔧 Setting up GitHub webhook deployment..."

# Generate webhook secret
WEBHOOK_SECRET=$(openssl rand -base64 32)

# Update production environment
sed -i.bak "s/WEBHOOK_SECRET=/WEBHOOK_SECRET=$WEBHOOK_SECRET/" .env.production

echo "✅ Webhook secret generated and added to .env.production"

# Deploy the webhook endpoint
echo "🚀 Deploying webhook endpoint..."

# Copy files to server
scp -i ~/.ssh/jordanpartridge-containers.pem .env.production ec2-user@54.193.154.122:/tmp/
scp -i ~/.ssh/jordanpartridge-containers.pem -r app/ ec2-user@54.193.154.122:/tmp/app-update/
scp -i ~/.ssh/jordanpartridge-containers.pem -r routes/ ec2-user@54.193.154.122:/tmp/routes-update/

# Update server
ssh -i ~/.ssh/jordanpartridge-containers.pem ec2-user@54.193.154.122 << 'EOF'
    cd /tmp
    
    # Update environment
    cp .env.production .env
    
    # Update application files
    cp -r app-update/* app/ 2>/dev/null || echo "App files copied"
    cp -r routes-update/* routes/ 2>/dev/null || echo "Routes files copied"
    
    # Restart containers to load new config
    docker-compose restart app
    
    # Run any pending migrations
    docker exec know-api php artisan migrate --force
    
    # Start queue worker for deployments
    docker exec -d know-api php artisan queue:work --queue=deployments --tries=1 --timeout=300
    
    echo "✅ Webhook endpoint deployed"
EOF

echo ""
echo "🎉 Webhook deployment setup complete!"
echo ""
echo "📋 Next steps:"
echo "1. Go to GitHub: https://github.com/jordanpartridge/know/settings/hooks"
echo "2. Click 'Add webhook'"
echo "3. Set Payload URL: https://know.jordanpartridge.us/api/webhook/deploy"
echo "4. Set Content type: application/json"
echo "5. Set Secret: $WEBHOOK_SECRET"
echo "6. Select 'Just the push event'"
echo "7. Check 'Active'"
echo "8. Click 'Add webhook'"
echo ""
echo "🔒 Save this webhook secret securely: $WEBHOOK_SECRET"
echo ""
echo "🧪 Test deployment:"
echo "1. Make a change to your code"
echo "2. Push to master branch: git push origin master"
echo "3. Watch logs: know-exec 'tail -f storage/logs/laravel.log'"