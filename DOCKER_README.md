# 🐳 Laravel + React + FrankenPHP Docker Setup

> **Complete Guide**: See [DOCKER_DEPLOYMENT_GUIDE.md](./DOCKER_DEPLOYMENT_GUIDE.md) for detailed instructions.

## Quick Start

### Local Development

```bash
# 1. Copy environment template
cp env.production.template .env

# 2. Edit environment variables (minimal required)
nano .env

# 3. Deploy locally with self-signed HTTPS
./deploy.sh local

# 4. Validate deployment
./validate-deployment.sh
```

**Access your application:**
- 🌐 **HTTPS**: https://localhost
- 🔧 **Adminer**: http://localhost:8080
- 📧 **MailHog**: http://localhost:8025

### Production Deployment

```bash
# 1. Configure environment for production
cp env.production.template .env
nano .env  # Set your domain, passwords, etc.

# 2. Deploy to production
./deploy.sh production

# 3. Validate deployment
./validate-deployment.sh
```

## Architecture

```
┌─────────────────┐    ┌─────────────────┐    ┌─────────────────┐
│   FrankenPHP    │    │      MySQL      │    │      Redis      │
│   (Laravel +    │◄──►│   (Database)    │    │    (Cache)      │
│    React)       │    │                 │    │                 │
│   Port 80/443   │    │   Port 3306     │    │   Port 6379     │
└─────────────────┘    └─────────────────┘    └─────────────────┘
```

## Features

✅ **Production-Ready**
- Multi-stage Docker builds
- Optimized PHP/OPcache configuration
- Laravel caching (config, routes, views)
- Supervisor for background jobs

✅ **Security**
- HTTPS with Let's Encrypt (automatic)
- Security headers configured
- Non-root container execution
- Minimal attack surface

✅ **Performance**
- FrankenPHP with HTTP/2 & HTTP/3
- Redis for caching and sessions
- Optimized MySQL configuration
- Static file caching and compression

✅ **Development Tools**
- Self-signed certificates for local dev
- Database administration (Adminer)
- Email testing (MailHog)
- Comprehensive validation script

## File Structure

```
docker/
├── frankenphp/
│   └── Caddyfile          # FrankenPHP/Caddy configuration
├── php/
│   ├── php.ini            # PHP production settings
│   └── opcache.ini        # OPcache optimization
├── mysql/
│   ├── conf.d/mysql.cnf   # MySQL optimization
│   └── init/01-init.sql   # Database initialization
├── supervisor/
│   └── supervisord.conf   # Background job management
└── scripts/
    └── entrypoint.sh      # Container startup script

Dockerfile                 # Multi-stage build
docker-compose.yml         # Full stack orchestration
deploy.sh                  # Deployment helper script
validate-deployment.sh     # Testing and validation
env.production.template    # Environment template
```

## Commands Reference

```bash
# Deployment
./deploy.sh local          # Local development
./deploy.sh production     # Production deployment
./deploy.sh build          # Build images only
./deploy.sh start          # Start containers
./deploy.sh stop           # Stop containers
./deploy.sh restart        # Restart containers

# Maintenance
./deploy.sh logs           # View all logs
./deploy.sh logs app       # View app logs
./deploy.sh shell          # Access app container
./deploy.sh backup         # Backup database/storage
./deploy.sh clean          # Clean up everything

# Validation
./validate-deployment.sh   # Test all components
```

## Environment Variables

**Critical Production Variables:**
```env
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:your_generated_key
APP_URL=https://yourdomain.com

DOMAIN=yourdomain.com
TLS_EMAIL=admin@yourdomain.com

DB_PASSWORD=your_secure_password
REDIS_PASSWORD=your_redis_password
JWT_SECRET=your_jwt_secret
```

## Support

- 📖 **Detailed Guide**: [DOCKER_DEPLOYMENT_GUIDE.md](./DOCKER_DEPLOYMENT_GUIDE.md)
- 🔧 **Troubleshooting**: See deployment guide
- 🚀 **FrankenPHP**: https://frankenphp.dev/
- 🐳 **Docker**: https://docs.docker.com/

---

**🎉 Your Laravel + React application is ready for production!** 