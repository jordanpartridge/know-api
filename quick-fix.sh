#!/bin/bash

# Quick fix for production Laravel app

echo "🔧 Quick fixing production..."

# Create minimal deployment package
tar czf quick-fix.tar.gz .env.production docker-compose.yml

# Try SSH without strict checking
ssh -o StrictHostKeyChecking=no -o ConnectTimeout=10 ubuntu@54.193.154.122 << 'EOF'
    # Stop containers
    cd /tmp && docker-compose down 2>/dev/null || true
    
    # Clear Laravel caches to fix 500 errors
    docker-compose exec -T app php artisan config:clear 2>/dev/null || true
    docker-compose exec -T app php artisan cache:clear 2>/dev/null || true
    docker-compose exec -T app php artisan route:clear 2>/dev/null || true
    
    # Restart containers
    docker-compose up -d
    
    # Wait for startup
    sleep 15
    
    # Test health
    curl -s http://localhost/health || echo "Health check failed"
EOF

echo "✅ Quick fix attempted"