#!/bin/bash

# ============================================================================
# Laravel Production Entrypoint Script
# Handles Laravel initialization and production optimizations
# ============================================================================

set -e

echo "🚀 Starting Laravel Application with FrankenPHP..."

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

# Check if we're in production
if [ "${APP_ENV}" = "production" ]; then
    PRODUCTION_MODE=true
    print_status "Running in PRODUCTION mode"
else
    PRODUCTION_MODE=false
    print_status "Running in DEVELOPMENT mode"
fi

# Wait for database to be ready
wait_for_database() {
    if [ "${DB_CONNECTION:-mysql}" = "mysql" ]; then
        print_step "Waiting for database to be ready..."
        
        local max_attempts=10  # Reduced from 30
        local attempt=1
        
        while [ $attempt -le $max_attempts ]; do
            # Simple connectivity test instead of complex Laravel command
            if mysqladmin ping -h "${DB_HOST:-mysql}" -u "${DB_USERNAME:-root}" -p"${DB_PASSWORD:-}" --silent >/dev/null 2>&1; then
                print_status "Database connection established"
                return 0
            fi
            
            print_warning "Database not ready, attempt $attempt/$max_attempts"
            sleep 3
            attempt=$((attempt + 1))
        done
        
        print_warning "Database connection failed after $max_attempts attempts, but continuing startup..."
        print_warning "Laravel will handle database connectivity issues internally"
    fi
}

# Wait for Redis to be ready (if configured)
wait_for_redis() {
    if [ "${CACHE_STORE}" = "redis" ] || [ "${SESSION_DRIVER}" = "redis" ]; then
        print_step "Waiting for Redis connection..."
        local max_attempts=15
        local attempt=1
        
        while [ $attempt -le $max_attempts ]; do
            if redis-cli -h "${REDIS_HOST:-redis}" -p "${REDIS_PORT:-6379}" ping >/dev/null 2>&1; then
                print_status "Redis connection established"
                return 0
            fi
            
            print_warning "Redis not ready, attempt $attempt/$max_attempts"
            sleep 2
            attempt=$((attempt + 1))
        done
        
        print_warning "Redis connection failed, continuing without Redis"
    fi
}

# Initialize Laravel application
initialize_laravel() {
    print_step "Initializing Laravel application..."
    
    # Generate application key if not set
    if [ -z "${APP_KEY}" ] || [ "${APP_KEY}" = "base64:your_32_character_app_key_here" ]; then
        print_warning "Generating new application key..."
        php artisan key:generate --force
    fi
    
    # Generate JWT secret if not set and JWT is used
    if [ -z "${JWT_SECRET}" ] || [ "${JWT_SECRET}" = "your_jwt_secret_key_here" ]; then
        if php artisan | grep -q "jwt:secret"; then
            print_warning "Generating JWT secret..."
            php artisan jwt:secret --force
        fi
    fi
    
    # Create storage directories
    print_step "Creating storage directories..."
    mkdir -p storage/app/public
    mkdir -p storage/framework/cache
    mkdir -p storage/framework/sessions
    mkdir -p storage/framework/views
    mkdir -p storage/logs
    mkdir -p bootstrap/cache
    
    # Set proper permissions
    print_step "Setting proper permissions..."
    chown -R www-data:www-data storage bootstrap/cache
    chmod -R 775 storage bootstrap/cache
}

# Run database migrations
run_migrations() {
    print_step "Running database migrations..."
    
    # Check if we should run migrations
    if [ "${AUTO_MIGRATE:-true}" = "true" ]; then
        # Try to run migrations but don't fail if they don't work
        if php artisan migrate:status >/dev/null 2>&1 && php artisan migrate --force >/dev/null 2>&1; then
            print_status "Database migrations completed successfully"
        else
            print_warning "Migration skipped - database not ready or migration failed"
            print_warning "Migrations can be run manually later: 'php artisan migrate --force'"
        fi
    else
        print_status "Auto-migration disabled"
    fi
}

# Optimize Laravel for production
optimize_laravel() {
    if [ "$PRODUCTION_MODE" = true ]; then
        print_step "Optimizing Laravel for production..."
        
        # Clear all caches first
        php artisan cache:clear
        php artisan config:clear
        php artisan route:clear
        php artisan view:clear
        
        # Cache configuration
        print_status "Caching configuration..."
        php artisan config:cache
        
        # Cache routes
        print_status "Caching routes..."
        php artisan route:cache
        
        # Cache views
        print_status "Caching views..."
        php artisan view:cache
        
        # Cache events (if available)
        if php artisan | grep -q "event:cache"; then
            print_status "Caching events..."
            php artisan event:cache
        fi
        
        # Note: Composer autoloader optimization was done during build process
        print_status "Composer autoloader already optimized during build"
        
        print_status "Laravel optimization completed"
    else
        print_status "Skipping optimization in development mode"
    fi
}

# Start background services
start_services() {
    print_step "Starting background services..."
    
    # Start supervisor for queue workers (if configured)
    if [ "${QUEUE_CONNECTION}" != "sync" ] && [ -f "/etc/supervisor/conf.d/supervisord.conf" ]; then
        print_status "Starting Supervisor for queue workers..."
        supervisord -c /etc/supervisor/conf.d/supervisord.conf &
    fi
}

# Health check function
create_health_endpoint() {
    print_step "Setting up health check endpoint..."
    
    # Create a simple health check endpoint
    cat > /app/public/health.php << 'EOF'
<?php
header('Content-Type: application/json');

$health = [
    'status' => 'healthy',
    'timestamp' => date('Y-m-d H:i:s'),
    'services' => []
];

// Check database connection
try {
    $pdo = new PDO(
        'mysql:host=' . getenv('DB_HOST') . ';dbname=' . getenv('DB_DATABASE'),
        getenv('DB_USERNAME'),
        getenv('DB_PASSWORD')
    );
    $health['services']['database'] = 'connected';
} catch (Exception $e) {
    $health['services']['database'] = 'failed';
    $health['status'] = 'unhealthy';
}

// Check Redis connection (if configured)
if (getenv('CACHE_STORE') === 'redis') {
    try {
        $redis = new Redis();
        $redis->connect(getenv('REDIS_HOST'), getenv('REDIS_PORT'));
        if (getenv('REDIS_PASSWORD')) {
            $redis->auth(getenv('REDIS_PASSWORD'));
        }
        $redis->ping();
        $health['services']['redis'] = 'connected';
        $redis->close();
    } catch (Exception $e) {
        $health['services']['redis'] = 'failed';
        $health['status'] = 'unhealthy';
    }
}

http_response_code($health['status'] === 'healthy' ? 200 : 503);
echo json_encode($health, JSON_PRETTY_PRINT);
EOF
}

# Main execution
main() {
    print_status "Laravel Production Container Starting..."
    
    # Wait for dependencies
    wait_for_database
    wait_for_redis
    
    # Initialize application
    initialize_laravel
    
    # Run migrations
    run_migrations
    
    # Optimize for production
    optimize_laravel
    
    # Start background services
    start_services
    
    # Create health endpoint
    create_health_endpoint
    
    print_status "Initialization completed successfully!"
    print_status "Starting FrankenPHP server..."
    
    # Execute the main command
    exec "$@"
}

# Handle signals gracefully
trap 'echo "Received termination signal, shutting down gracefully..."; exit 0' SIGTERM SIGINT

# Run main function
main "$@" 