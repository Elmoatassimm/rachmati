#!/bin/bash

# ============================================================================
# Backup Script for Hostinger VPS
# Hostname: srv889998.hstgr.cloud
# ============================================================================

set -e

BACKUP_DIR="/home/deployer/backups"
DATE=$(date +%Y%m%d_%H%M%S)

echo "🗄️ Starting backup process - $(date)"

# Create backup directory if it doesn't exist
mkdir -p "$BACKUP_DIR"

# Database backup
echo "📊 Backing up database..."
docker-compose --env-file .env.production exec -T mysql mysqldump \
    -u root -pRootSecure2024! \
    --single-transaction \
    --routines \
    --triggers \
    rachmat > "$BACKUP_DIR/database_$DATE.sql"

echo "✅ Database backup completed: database_$DATE.sql"

# Application files backup (excluding node_modules and vendor)
echo "📁 Backing up application files..."
tar -czf "$BACKUP_DIR/app_files_$DATE.tar.gz" \
    --exclude='node_modules' \
    --exclude='vendor' \
    --exclude='.git' \
    --exclude='storage/logs/*' \
    --exclude='bootstrap/cache/*' \
    .

echo "✅ Application files backup completed: app_files_$DATE.tar.gz"

# Docker volumes backup
echo "🐳 Backing up Docker volumes..."
docker run --rm \
    -v rachmat_mysql_data:/source:ro \
    -v "$BACKUP_DIR":/backup \
    alpine tar -czf /backup/mysql_volume_$DATE.tar.gz -C /source .

echo "✅ MySQL volume backup completed: mysql_volume_$DATE.tar.gz"

# Environment file backup
echo "⚙️ Backing up environment configuration..."
cp .env.production "$BACKUP_DIR/env_production_$DATE.backup"

echo "✅ Environment backup completed: env_production_$DATE.backup"

# Clean old backups (keep last 7 days)
echo "🧹 Cleaning old backups..."
find "$BACKUP_DIR" -name "*.sql" -mtime +7 -delete
find "$BACKUP_DIR" -name "*.tar.gz" -mtime +7 -delete
find "$BACKUP_DIR" -name "*.backup" -mtime +7 -delete

# Show backup summary
echo ""
echo "📊 Backup Summary:"
echo "=================="
ls -lh "$BACKUP_DIR" | tail -10

echo ""
echo "✅ Backup process completed - $(date)"
echo "📍 Backup location: $BACKUP_DIR" 