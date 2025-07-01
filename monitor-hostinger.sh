#!/bin/bash

# ============================================================================
# Monitoring Script for Hostinger VPS
# Hostname: srv889998.hstgr.cloud
# ============================================================================

echo "🔍 Hostinger VPS Health Check - $(date)"
echo "========================================"

# Check if Docker is running
if ! docker --version &> /dev/null; then
    echo "❌ Docker is not running"
    exit 1
fi
echo "✅ Docker is running"

# Check container status
echo ""
echo "📊 Container Status:"
docker-compose --env-file .env.production ps

# Check if all containers are up
if ! docker-compose --env-file .env.production ps | grep -q "Up"; then
    echo "⚠️ Some containers are down. Attempting restart..."
    docker-compose --env-file .env.production up -d
    sleep 30
fi

# Check application response
echo ""
echo "🌐 Application Health Checks:"

# Test HTTP redirect
echo -n "HTTP redirect: "
if curl -s -o /dev/null -w "%{http_code}" http://srv889998.hstgr.cloud | grep -q "301\|302"; then
    echo "✅ Working (redirects to HTTPS)"
else
    echo "❌ Not redirecting properly"
fi

# Test HTTPS
echo -n "HTTPS response: "
HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" https://srv889998.hstgr.cloud)
if [ "$HTTP_CODE" -eq 200 ]; then
    echo "✅ Working (HTTP $HTTP_CODE)"
else
    echo "❌ Failed (HTTP $HTTP_CODE)"
fi

# Test health endpoint
echo -n "Health endpoint: "
if curl -s -f https://srv889998.hstgr.cloud/health > /dev/null; then
    echo "✅ Working"
else
    echo "❌ Failed"
    echo "🔄 Restarting app container..."
    docker-compose --env-file .env.production restart app
fi

# Check disk space
echo ""
echo "💾 Disk Usage:"
df -h | grep -E "(Filesystem|/dev/)"

# Check memory usage
echo ""
echo "🧠 Memory Usage:"
free -h

# Check Docker resource usage
echo ""
echo "🐳 Docker Resource Usage:"
docker stats --no-stream

# Check SSL certificate (if available)
echo ""
echo "🔒 SSL Certificate Status:"
echo | openssl s_client -servername srv889998.hstgr.cloud -connect srv889998.hstgr.cloud:443 2>/dev/null | openssl x509 -noout -dates 2>/dev/null || echo "Certificate info not available"

# Show recent logs
echo ""
echo "📝 Recent Application Logs (last 5 lines):"
docker-compose --env-file .env.production logs app --tail=5

echo ""
echo "✅ Health check completed - $(date)" 