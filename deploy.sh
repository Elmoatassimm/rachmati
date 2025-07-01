#!/bin/bash

# ============================================================================
# Rachmat Laravel Application Deployment Script
# Supports both local testing and production deployment
# ============================================================================

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

print_step() {
    echo -e "${BLUE}[STEP]${NC} $1"
}

# Configuration
ENV_FILE=".env"
COMPOSE_FILE="docker-compose.yml"
APP_NAME="rachmat"

# Show usage
show_usage() {
    echo "Usage: $0 [COMMAND] [OPTIONS]"
    echo ""
    echo "Commands:"
    echo "  local       Deploy for local development with self-signed certificates"
    echo "  production  Deploy for production with Let's Encrypt certificates"
    echo "  build       Build the Docker images only"
    echo "  start       Start the containers"
    echo "  stop        Stop the containers"
    echo "  restart     Restart the containers"
    echo "  logs        Show container logs"
    echo "  shell       Access the app container shell"
    echo "  clean       Clean up containers, images, and volumes"
    echo "  backup      Backup database and storage"
    echo "  restore     Restore database and storage from backup"
    echo ""
    echo "Options:"
    echo "  --no-cache  Don't use Docker build cache"
    echo "  --help      Show this help message"
}

# Check prerequisites
check_prerequisites() {
    print_step "Checking prerequisites..."
    
    # Check if Docker is installed
    if ! command -v docker &> /dev/null; then
        print_error "Docker is not installed. Please install Docker first."
        exit 1
    fi
    
    # Check if Docker Compose is installed
    if ! command -v docker-compose &> /dev/null; then
        print_error "Docker Compose is not installed. Please install Docker Compose first."
        exit 1
    fi
    
    # Check if .env file exists
    if [ ! -f "$ENV_FILE" ]; then
        print_warning "Environment file not found. Creating from template..."
        if [ -f "env.production.template" ]; then
            cp env.production.template "$ENV_FILE"
            print_warning "Please edit $ENV_FILE with your configuration before proceeding."
            exit 1
        else
            print_error "No environment template found. Please create a $ENV_FILE file."
            exit 1
        fi
    fi
    
    print_status "Prerequisites check completed"
}

# Setup environment for local development
setup_local() {
    print_step "Setting up local development environment..."
    
    # Create local .env if it doesn't exist
    if [ ! -f ".env.local" ]; then
        cp env.production.template .env.local
        
        # Customize for local development
        sed -i 's|APP_ENV=production|APP_ENV=local|' .env.local
        sed -i 's|APP_DEBUG=false|APP_DEBUG=true|' .env.local
        sed -i 's|APP_URL=https://yourdomain.com|APP_URL=https://localhost|' .env.local
        sed -i 's|DOMAIN=yourdomain.com|DOMAIN=localhost|' .env.local
        sed -i 's|TLS_EMAIL=admin@yourdomain.com|TLS_EMAIL=admin@localhost|' .env.local
        
        # Generate random passwords
        DB_PASSWORD=$(openssl rand -base64 32)
        REDIS_PASSWORD=$(openssl rand -base64 32)
        JWT_SECRET=$(openssl rand -base64 64)
        
        sed -i "s/your_secure_database_password_here/$DB_PASSWORD/" .env.local
        sed -i "s/your_redis_password_here/$REDIS_PASSWORD/" .env.local
        sed -i "s/your_jwt_secret_key_here/$JWT_SECRET/" .env.local
        
        print_status "Local environment file created: .env.local"
    fi
    
    # Use local environment
    cp .env.local .env
    
    print_status "Local environment setup completed"
}

# Build Docker images
build_images() {
    local no_cache=""
    if [ "$1" = "--no-cache" ]; then
        no_cache="--no-cache"
    fi
    
    print_step "Building Docker images..."
    docker-compose build $no_cache
    print_status "Docker images built successfully"
}

# Start containers
start_containers() {
    print_step "Starting containers..."
    
    # Start with development profile for local
    if grep -q "APP_ENV=local" .env; then
        docker-compose --profile development up -d
    else
        docker-compose up -d
    fi
    
    print_status "Containers started successfully"
    
    # Show access information
    if grep -q "localhost" .env; then
        print_status "Application is available at:"
        print_status "  - HTTPS: https://localhost"
        print_status "  - HTTP: http://localhost (redirects to HTTPS)"
        print_status "  - Adminer: http://localhost:8080 (development only)"
        print_status "  - MailHog: http://localhost:8025 (development only)"
    fi
}

# Stop containers
stop_containers() {
    print_step "Stopping containers..."
    docker-compose down
    print_status "Containers stopped successfully"
}

# Show logs
show_logs() {
    if [ -n "$1" ]; then
        docker-compose logs -f "$1"
    else
        docker-compose logs -f
    fi
}

# Access container shell
access_shell() {
    print_step "Accessing application container shell..."
    docker-compose exec app bash
}

# Clean up
cleanup() {
    print_warning "This will remove all containers, images, and volumes. Are you sure? (y/N)"
    read -r response
    if [[ "$response" =~ ^([yY][eE][sS]|[yY])$ ]]; then
        print_step "Cleaning up..."
        docker-compose down -v --rmi all
        docker system prune -f
        print_status "Cleanup completed"
    else
        print_status "Cleanup cancelled"
    fi
}

# Backup database and storage
backup_data() {
    print_step "Creating backup..."
    
    BACKUP_DIR="backups/$(date +%Y%m%d_%H%M%S)"
    mkdir -p "$BACKUP_DIR"
    
    # Backup database
    print_status "Backing up database..."
    docker-compose exec mysql mysqldump -u root -p"${DB_ROOT_PASSWORD}" rachmat > "$BACKUP_DIR/database.sql"
    
    # Backup storage
    print_status "Backing up storage..."
    docker-compose exec app tar -czf - storage | cat > "$BACKUP_DIR/storage.tar.gz"
    
    print_status "Backup created in $BACKUP_DIR"
}

# Restore database and storage
restore_data() {
    if [ -z "$1" ]; then
        print_error "Please specify backup directory: $0 restore /path/to/backup"
        exit 1
    fi
    
    BACKUP_DIR="$1"
    
    if [ ! -d "$BACKUP_DIR" ]; then
        print_error "Backup directory not found: $BACKUP_DIR"
        exit 1
    fi
    
    print_warning "This will overwrite existing data. Are you sure? (y/N)"
    read -r response
    if [[ "$response" =~ ^([yY][eE][sS]|[yY])$ ]]; then
        print_step "Restoring from backup..."
        
        # Restore database
        if [ -f "$BACKUP_DIR/database.sql" ]; then
            print_status "Restoring database..."
            cat "$BACKUP_DIR/database.sql" | docker-compose exec -T mysql mysql -u root -p"${DB_ROOT_PASSWORD}" rachmat
        fi
        
        # Restore storage
        if [ -f "$BACKUP_DIR/storage.tar.gz" ]; then
            print_status "Restoring storage..."
            cat "$BACKUP_DIR/storage.tar.gz" | docker-compose exec -T app tar -xzf - -C /
        fi
        
        print_status "Restore completed"
    else
        print_status "Restore cancelled"
    fi
}

# Main execution
main() {
    case "$1" in
        "local")
            check_prerequisites
            setup_local
            build_images "$2"
            start_containers
            ;;
        "production")
            check_prerequisites
            print_warning "Make sure you have configured your .env file for production!"
            build_images "$2"
            start_containers
            ;;
        "build")
            check_prerequisites
            build_images "$2"
            ;;
        "start")
            check_prerequisites
            start_containers
            ;;
        "stop")
            stop_containers
            ;;
        "restart")
            stop_containers
            start_containers
            ;;
        "logs")
            show_logs "$2"
            ;;
        "shell")
            access_shell
            ;;
        "clean")
            cleanup
            ;;
        "backup")
            backup_data
            ;;
        "restore")
            restore_data "$2"
            ;;
        "--help"|"help"|*)
            show_usage
            ;;
    esac
}

# Execute main function with all arguments
main "$@" 