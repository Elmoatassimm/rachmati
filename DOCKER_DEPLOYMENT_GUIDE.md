# 🐳 Laravel + React + FrankenPHP Production Docker Guide

This guide covers deploying your Laravel 12 + React + Inertia.js application using Docker with FrankenPHP, MySQL, and Redis in a production-ready environment.

## 📋 Table of Contents

- [Prerequisites](#prerequisites)
- [Quick Start](#quick-start)
- [Local Development](#local-development)
- [Production Deployment](#production-deployment)
- [HTTPS Configuration](#https-configuration)
- [Monitoring & Maintenance](#monitoring--maintenance)
- [Troubleshooting](#troubleshooting)
- [Security Checklist](#security-checklist)

## 🔧 Prerequisites

### System Requirements
- Docker 20.10+ and Docker Compose 2.0+
- Linux VPS with at least 2GB RAM (for production)
- Domain name pointed to your server (for production HTTPS)

### Local Development
- Docker Desktop (Windows/Mac) or Docker Engine (Linux)
- Git

## 🚀 Quick Start

### 1. Clone and Configure

```bash
# Clone your repository
git clone <your-repo-url>
cd rachmat

# Copy environment template
cp env.production.template .env

# Edit environment variables (REQUIRED)
nano .env
```

### 2. Deploy Locally

```bash
# Make deployment script executable
chmod +x deploy.sh

# Deploy for local development
./deploy.sh local
```

Your application will be available at:
- **HTTPS**: https://localhost (self-signed certificate)
- **HTTP**: http://localhost (redirects to HTTPS)
- **Adminer**: http://localhost:8080 (database admin)
- **MailHog**: http://localhost:8025 (email testing)

## 🏠 Local Development

### Environment Setup

The `./deploy.sh local` command automatically:
- Creates a `.env.local` file with development settings
- Generates secure random passwords
- Enables debug mode and development tools
- Sets up self-signed HTTPS certificates

### Available Commands

```bash
# Start containers
./deploy.sh start

# Stop containers
./deploy.sh stop

# View logs
./deploy.sh logs
./deploy.sh logs app    # Specific container

# Access container shell
./deploy.sh shell

# Rebuild images
./deploy.sh build --no-cache

# Clean up everything
./deploy.sh clean
```

### Development Tools

When running locally, you get additional services:

- **Adminer** (localhost:8080): Database administration
- **MailHog** (localhost:8025): Email testing and debugging

## 🌐 Production Deployment

### 1. Server Preparation

```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install Docker
curl -fsSL https://get.docker.com -o get-docker.sh
sudo sh get-docker.sh

# Install Docker Compose
sudo curl -L "https://github.com/docker/compose/releases/latest/download/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
sudo chmod +x /usr/local/bin/docker-compose

# Add user to docker group
sudo usermod -aG docker $USER
```

### 2. Application Deployment

```bash
# Clone repository
git clone <your-repo-url>
cd rachmat

# Configure environment
cp env.production.template .env
nano .env  # Configure for production
```

### 3. Critical Environment Variables

**REQUIRED for Production:**

```env
# Application
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:your_generated_key_here
APP_URL=https://yourdomain.com

# Database
DB_PASSWORD=your_secure_database_password
DB_ROOT_PASSWORD=your_root_password

# Security
JWT_SECRET=your_jwt_secret_key

# HTTPS
DOMAIN=yourdomain.com
TLS_EMAIL=admin@yourdomain.com
ENABLE_HTTPS=true

# Cache
REDIS_PASSWORD=your_redis_password
```

### 4. Deploy to Production

```bash
# Deploy with production settings
./deploy.sh production

# Or manually:
docker-compose up -d
```

## 🔐 HTTPS Configuration

### Automatic Let's Encrypt (Production)

FrankenPHP automatically handles Let's Encrypt certificates when:

1. `DOMAIN` is set to your actual domain
2. `TLS_EMAIL` is configured
3. Domain points to your server
4. Ports 80/443 are accessible

### Manual Certificate Setup

For custom certificates, place them in `docker/certs/`:

```bash
# Create certificate directory
mkdir -p docker/certs

# Copy your certificates
cp your-cert.pem docker/certs/cert.pem
cp your-key.pem docker/certs/key.pem
```

Update `docker/frankenphp/Caddyfile`:

```caddyfile
{$DOMAIN:localhost} {
    tls /etc/certs/cert.pem /etc/certs/key.pem
    # ... rest of configuration
}
```

### Self-Signed (Development Only)

For local development, FrankenPHP generates self-signed certificates automatically.

## 📊 Monitoring & Maintenance

### Health Checks

```bash
# Application health
curl https://yourdomain.com/health

# Container health
docker-compose ps
```

### Logs

```bash
# View all logs
./deploy.sh logs

# Specific services
./deploy.sh logs app
./deploy.sh logs mysql
./deploy.sh logs redis

# Laravel logs
docker-compose exec app tail -f storage/logs/laravel.log
```

### Database Backup

```bash
# Create backup
./deploy.sh backup

# Restore from backup
./deploy.sh restore backups/20231201_120000
```

### Manual Backup

```bash
# Database backup
docker-compose exec mysql mysqldump -u root -p rachmat > backup.sql

# Storage backup
docker-compose exec app tar -czf storage-backup.tar.gz storage/
```

### Updates

```bash
# Pull latest code
git pull

# Rebuild and restart
./deploy.sh build --no-cache
./deploy.sh restart

# Run migrations (if needed)
docker-compose exec app php artisan migrate --force
```

## 🐛 Troubleshooting

### Common Issues

#### 1. Permission Errors

```bash
# Fix Laravel permissions
docker-compose exec app chown -R www-data:www-data storage bootstrap/cache
docker-compose exec app chmod -R 775 storage bootstrap/cache
```

#### 2. Database Connection Failed

```bash
# Check MySQL container
docker-compose logs mysql

# Test connection
docker-compose exec app php artisan db:show
```

#### 3. Redis Connection Issues

```bash
# Check Redis
docker-compose logs redis

# Test Redis connection
docker-compose exec redis redis-cli ping
```

#### 4. HTTPS Certificate Issues

```bash
# Check FrankenPHP logs
docker-compose logs app

# Force certificate regeneration
docker-compose restart app
```

#### 5. Frontend Assets Not Loading

```bash
# Rebuild frontend assets
docker-compose exec app npm run build

# Or rebuild entire image
./deploy.sh build --no-cache
```

### Performance Issues

#### 1. Enable OPcache

OPcache is enabled by default. Verify:

```bash
docker-compose exec app php -m | grep OPcache
```

#### 2. Optimize Laravel

```bash
# Run optimization commands
docker-compose exec app php artisan optimize
docker-compose exec app php artisan config:cache
docker-compose exec app php artisan route:cache
docker-compose exec app php artisan view:cache
```

#### 3. Database Optimization

Monitor slow queries:

```bash
# Enable slow query log (already enabled in config)
docker-compose exec mysql mysql -u root -p -e "SHOW VARIABLES LIKE 'slow_query_log';"

# View slow queries
docker-compose exec mysql tail -f /var/log/mysql/slow.log
```

## 🔒 Security Checklist

### Pre-Deployment

- [ ] Change all default passwords in `.env`
- [ ] Set strong `APP_KEY` and `JWT_SECRET`
- [ ] Configure proper `CORS_ALLOWED_ORIGINS`
- [ ] Set `APP_DEBUG=false` in production
- [ ] Configure secure `SANCTUM_STATEFUL_DOMAINS`

### Server Security

- [ ] Configure firewall (UFW/iptables)
- [ ] Enable automatic security updates
- [ ] Configure SSH key authentication
- [ ] Disable password authentication
- [ ] Set up fail2ban (optional)

### Application Security

- [ ] HTTPS enabled with valid certificates
- [ ] Security headers configured (in Caddyfile)
- [ ] File upload restrictions in place
- [ ] Database user has minimal required privileges
- [ ] Backup encryption enabled
- [ ] Log monitoring configured

### Regular Maintenance

- [ ] Update Docker images monthly
- [ ] Monitor security advisories
- [ ] Rotate JWT secrets periodically
- [ ] Review and clean old logs
- [ ] Test backup restoration process

## 🔧 Advanced Configuration

### Environment Profiles

Use Docker Compose profiles for different environments:

```bash
# Development with all tools
docker-compose --profile development up -d

# Production (default)
docker-compose up -d
```

### Scaling

Scale specific services:

```bash
# Scale queue workers
docker-compose up -d --scale app=2

# Redis replication (requires additional config)
docker-compose up -d --scale redis=2
```

### Custom Domain Setup

1. Point your domain A record to your server IP
2. Update `.env` with your domain
3. Restart containers: `./deploy.sh restart`

### SSL Certificate Verification

```bash
# Check certificate details
openssl s_client -connect yourdomain.com:443 -servername yourdomain.com

# Check certificate expiry
echo | openssl s_client -connect yourdomain.com:443 2>/dev/null | openssl x509 -noout -dates
```

## 📞 Support

For issues related to:
- **Docker**: Check Docker documentation
- **FrankenPHP**: Visit [FrankenPHP documentation](https://frankenphp.dev/)
- **Laravel**: Refer to [Laravel documentation](https://laravel.com/docs)
- **Application-specific**: Create an issue in your repository

---

**🎉 Your Laravel application is now running in a production-ready Docker environment with HTTPS support!** 