#!/bin/bash

# ============================================================================
# Deployment Script for Hostinger VPS
# Hostname: srv889998.hstgr.cloud
# ============================================================================

set -e

echo "🚀 Starting Hostinger VPS deployment..."

# Pull latest changes if using git
if [ -d ".git" ]; then
    echo "📥 Pulling latest changes..."
    git pull origin main
fi

# Stop existing containers
echo "🛑 Stopping existing containers..."
docker-compose --env-file .env.production down

# Build new images
echo "🔨 Building new container images..."
docker-compose build --no-cache

# Start services
echo "🚀 Starting services..."
docker-compose --env-file .env.production up -d

# Wait for services to be ready
echo "⏳ Waiting for services to start..."
sleep 60

# Health check for database
echo "🔍 Checking database connectivity..."
docker-compose --env-file .env.production exec mysql mysql -u root -pRootSecure2024! -e "SELECT 'Database is ready' as status;" || {
    echo "❌ Database not ready. Waiting longer..."
    sleep 30
    docker-compose --env-file .env.production exec mysql mysql -u root -pRootSecure2024! -e "SELECT 'Database is ready' as status;"
}

# Run migrations
echo "🗃️ Running database migrations..."
docker-compose --env-file .env.production exec app php artisan migrate --force

# Clear and cache configuration
echo "🧹 Optimizing application..."
docker-compose --env-file .env.production exec app php artisan config:clear
docker-compose --env-file .env.production exec app php artisan config:cache
docker-compose --env-file .env.production exec app php artisan route:cache
docker-compose --env-file .env.production exec app php artisan view:cache

# Create storage link
echo "🔗 Creating storage link..."
docker-compose --env-file .env.production exec app php artisan storage:link

# Check application health
echo "🩺 Performing health checks..."
sleep 15

# Test HTTP redirect
echo "Testing HTTP redirect..."
curl -I http://srv889998.hstgr.cloud || echo "⚠️ HTTP test failed"

# Test HTTPS
echo "Testing HTTPS..."
curl -I https://srv889998.hstgr.cloud || echo "⚠️ HTTPS test failed"

# Test health endpoint
echo "Testing health endpoint..."
curl -f https://srv889998.hstgr.cloud/health || echo "⚠️ Health check failed"

# Show container status
echo "📊 Container status:"
docker-compose --env-file .env.production ps

# Show recent logs
echo "📝 Recent application logs:"
docker-compose --env-file .env.production logs app --tail=10

echo "✅ Deployment completed!"
echo "🌐 Your application is available at: https://srv889998.hstgr.cloud"
echo "🔍 Health check: https://srv889998.hstgr.cloud/health" 