#!/bin/bash

# ============================================================================
# Deployment Validation Script
# Tests all components of the Docker deployment
# ============================================================================

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
DOMAIN=${DOMAIN:-localhost}
HTTPS_PORT=${APP_HTTPS_PORT:-443}
HTTP_PORT=${APP_PORT:-80}

# Counters
TESTS_PASSED=0
TESTS_FAILED=0

# Function to print colored output
print_status() {
    echo -e "${GREEN}[PASS]${NC} $1"
    TESTS_PASSED=$((TESTS_PASSED + 1))
}

print_error() {
    echo -e "${RED}[FAIL]${NC} $1"
    TESTS_FAILED=$((TESTS_FAILED + 1))
}

print_warning() {
    echo -e "${YELLOW}[WARN]${NC} $1"
}

print_info() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

print_header() {
    echo ""
    echo -e "${BLUE}========================================${NC}"
    echo -e "${BLUE} $1${NC}"
    echo -e "${BLUE}========================================${NC}"
}

# Test function template
run_test() {
    local test_name="$1"
    local test_command="$2"
    local expected_result="$3"
    
    print_info "Testing: $test_name"
    
    if eval "$test_command" >/dev/null 2>&1; then
        print_status "$test_name"
    else
        print_error "$test_name"
    fi
}

# Test Docker and Docker Compose
test_prerequisites() {
    print_header "Testing Prerequisites"
    
    run_test "Docker installation" "docker --version"
    run_test "Docker Compose installation" "docker-compose --version"
    run_test "Docker daemon running" "docker info"
}

# Test container status
test_containers() {
    print_header "Testing Container Status"
    
    run_test "MySQL container running" "docker-compose ps mysql | grep -q 'Up'"
    run_test "Redis container running" "docker-compose ps redis | grep -q 'Up'"
    run_test "Application container running" "docker-compose ps app | grep -q 'Up'"
}

# Test container health
test_health_checks() {
    print_header "Testing Container Health"
    
    run_test "MySQL health check" "docker-compose exec mysql mysqladmin ping -h localhost --silent"
    run_test "Redis health check" "docker-compose exec redis redis-cli ping | grep -q PONG"
    run_test "Application health endpoint" "curl -f -k https://$DOMAIN:$HTTPS_PORT/health"
}

# Test database connectivity
test_database() {
    print_header "Testing Database Connectivity"
    
    run_test "Laravel database connection" "docker-compose exec app php artisan db:show"
    run_test "Database tables exist" "docker-compose exec app php artisan migrate:status"
    
    # Test database user permissions
    print_info "Testing database user permissions..."
    if docker-compose exec mysql mysql -u rachmat_user -p"${DB_PASSWORD}" -e "SELECT 1;" rachmat >/dev/null 2>&1; then
        print_status "Database user permissions"
    else
        print_error "Database user permissions"
    fi
}

# Test Redis connectivity
test_redis() {
    print_header "Testing Redis Connectivity"
    
    run_test "Redis connection from Laravel" "docker-compose exec app php -r \"Redis::connect('redis', 6379)->ping();\""
    
    # Test cache functionality
    print_info "Testing cache functionality..."
    docker-compose exec app php artisan cache:clear >/dev/null 2>&1
    if docker-compose exec app php -r "cache(['test' => 'value']); echo cache('test');" | grep -q "value"; then
        print_status "Cache functionality"
    else
        print_error "Cache functionality"
    fi
}

# Test HTTPS and SSL
test_https() {
    print_header "Testing HTTPS and SSL"
    
    # Test HTTPS response
    run_test "HTTPS response" "curl -f -k https://$DOMAIN:$HTTPS_PORT"
    
    # Test HTTP to HTTPS redirect
    print_info "Testing HTTP to HTTPS redirect..."
    if curl -s -o /dev/null -w "%{http_code}" http://$DOMAIN:$HTTP_PORT | grep -q "301\|302"; then
        print_status "HTTP to HTTPS redirect"
    else
        print_error "HTTP to HTTPS redirect"
    fi
    
    # Test SSL certificate (skip for localhost)
    if [ "$DOMAIN" != "localhost" ]; then
        print_info "Testing SSL certificate validity..."
        if echo | openssl s_client -connect $DOMAIN:$HTTPS_PORT -servername $DOMAIN 2>/dev/null | openssl x509 -noout -checkend 86400 >/dev/null; then
            print_status "SSL certificate validity"
        else
            print_error "SSL certificate validity"
        fi
    else
        print_warning "Skipping SSL certificate validation for localhost"
    fi
}

# Test Laravel functionality
test_laravel() {
    print_header "Testing Laravel Functionality"
    
    run_test "Laravel application responding" "docker-compose exec app php artisan --version"
    run_test "Environment configuration" "docker-compose exec app php artisan env"
    run_test "Route caching" "docker-compose exec app php artisan route:list"
    run_test "Config caching" "docker-compose exec app php artisan config:show app"
    
    # Test storage permissions
    print_info "Testing storage permissions..."
    if docker-compose exec app test -w storage/logs; then
        print_status "Storage directory writable"
    else
        print_error "Storage directory writable"
    fi
    
    # Test queue system
    if [ "${QUEUE_CONNECTION}" != "sync" ]; then
        run_test "Queue system" "docker-compose exec app php artisan queue:work --stop-when-empty --quiet"
    else
        print_warning "Queue system using sync driver (not tested)"
    fi
}

# Test frontend assets
test_frontend() {
    print_header "Testing Frontend Assets"
    
    # Test if build directory exists
    print_info "Testing frontend build assets..."
    if docker-compose exec app test -d public/build; then
        print_status "Frontend build directory exists"
    else
        print_error "Frontend build directory exists"
    fi
    
    # Test static file serving
    run_test "Static file serving" "curl -f -k https://$DOMAIN:$HTTPS_PORT/favicon.ico"
    
    # Test main application page
    print_info "Testing main application page..."
    if curl -f -k https://$DOMAIN:$HTTPS_PORT | grep -q "<!DOCTYPE html>"; then
        print_status "Main application page loads"
    else
        print_error "Main application page loads"
    fi
}

# Test background services
test_background_services() {
    print_header "Testing Background Services"
    
    # Test if supervisor is running (if configured)
    if docker-compose exec app pgrep supervisord >/dev/null 2>&1; then
        print_status "Supervisor daemon running"
        
        # Test individual supervisor programs
        if docker-compose exec app supervisorctl status | grep -q "laravel-queue.*RUNNING"; then
            print_status "Queue workers running"
        else
            print_warning "Queue workers not running or not configured"
        fi
        
        if docker-compose exec app supervisorctl status | grep -q "laravel-schedule.*RUNNING"; then
            print_status "Schedule runner running"
        else
            print_warning "Schedule runner not running or not configured"
        fi
    else
        print_warning "Supervisor not running (may be intentional)"
    fi
}

# Test logs and monitoring
test_monitoring() {
    print_header "Testing Logs and Monitoring"
    
    # Test log files exist and are writable
    run_test "Laravel log file exists" "docker-compose exec app test -f storage/logs/laravel.log"
    run_test "Access log file exists" "docker-compose exec app test -f storage/logs/access.log"
    
    # Test log rotation is working
    print_info "Testing log file sizes..."
    if docker-compose exec app test -s storage/logs/laravel.log; then
        print_status "Laravel logs are being written"
    else
        print_warning "Laravel logs are empty (may be normal for new deployment)"
    fi
}

# Performance tests
test_performance() {
    print_header "Testing Performance Optimizations"
    
    # Test OPcache
    run_test "OPcache enabled" "docker-compose exec app php -m | grep -q OPcache"
    
    # Test Laravel optimizations
    run_test "Config cached" "docker-compose exec app test -f bootstrap/cache/config.php"
    run_test "Routes cached" "docker-compose exec app test -f bootstrap/cache/routes-v7.php"
    run_test "Views cached" "docker-compose exec app test -d storage/framework/views"
    
    # Test response time
    print_info "Testing response time..."
    RESPONSE_TIME=$(curl -o /dev/null -s -w '%{time_total}' -k https://$DOMAIN:$HTTPS_PORT/health)
    if (( $(echo "$RESPONSE_TIME < 2.0" | bc -l) )); then
        print_status "Response time acceptable (${RESPONSE_TIME}s)"
    else
        print_warning "Response time high (${RESPONSE_TIME}s)"
    fi
}

# Generate summary report
generate_summary() {
    print_header "Test Summary"
    
    TOTAL_TESTS=$((TESTS_PASSED + TESTS_FAILED))
    
    echo -e "Total Tests: ${BLUE}$TOTAL_TESTS${NC}"
    echo -e "Passed: ${GREEN}$TESTS_PASSED${NC}"
    echo -e "Failed: ${RED}$TESTS_FAILED${NC}"
    
    if [ $TESTS_FAILED -eq 0 ]; then
        echo ""
        echo -e "${GREEN}🎉 All tests passed! Your deployment looks good.${NC}"
        echo ""
        echo "Your application is available at:"
        echo "  • HTTPS: https://$DOMAIN:$HTTPS_PORT"
        echo "  • HTTP: http://$DOMAIN:$HTTP_PORT (redirects to HTTPS)"
        
        if [ "$DOMAIN" = "localhost" ]; then
            echo ""
            echo "Development tools:"
            echo "  • Adminer: http://localhost:8080"
            echo "  • MailHog: http://localhost:8025"
        fi
        
        return 0
    else
        echo ""
        echo -e "${RED}❌ Some tests failed. Please check the issues above.${NC}"
        echo ""
        echo "Common solutions:"
        echo "  • Wait a few minutes for containers to fully start"
        echo "  • Check container logs: ./deploy.sh logs"
        echo "  • Verify your .env configuration"
        echo "  • Restart containers: ./deploy.sh restart"
        
        return 1
    fi
}

# Main execution
main() {
    echo "🧪 Starting deployment validation..."
    echo "Testing deployment at: $DOMAIN"
    
    # Load environment variables
    if [ -f ".env" ]; then
        export $(grep -v '^#' .env | xargs)
    fi
    
    # Run all tests
    test_prerequisites
    test_containers
    test_health_checks
    test_database
    test_redis
    test_https
    test_laravel
    test_frontend
    test_background_services
    test_monitoring
    test_performance
    
    # Generate summary
    generate_summary
}

# Execute main function
main "$@" 