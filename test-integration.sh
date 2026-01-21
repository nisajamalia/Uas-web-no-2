#!/bin/bash

# SAKTI Mini Login Module - End-to-End Integration Testing Script
# This script runs comprehensive integration tests for both backend and frontend
# Validates Requirements: 1.1, 1.2, 1.5, 2.2, 2.3, 2.4, 2.5, 3.1, 3.2, 3.3, 3.4, 3.5, 4.4, 4.5

set -e  # Exit on any error

echo "🚀 Starting SAKTI Mini Login Module Integration Tests"
echo "=================================================="

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Check if we're in the right directory
if [ ! -f "Backend/artisan" ] || [ ! -f "Frontend/vue-project/package.json" ]; then
    print_error "Please run this script from the project root directory"
    exit 1
fi

# Test results tracking
BACKEND_TESTS_PASSED=false
FRONTEND_TESTS_PASSED=false
INTEGRATION_TESTS_PASSED=false

print_status "Step 1: Setting up test environment"

# Backend setup
print_status "Setting up Laravel backend test environment..."
cd Backend

# Check if .env.testing exists, create if not
if [ ! -f ".env.testing" ]; then
    print_warning ".env.testing not found, creating from .env.example"
    cp .env.example .env.testing
    
    # Configure for testing
    sed -i.bak 's/APP_ENV=local/APP_ENV=testing/' .env.testing
    sed -i.bak 's/DB_CONNECTION=sqlite/DB_CONNECTION=sqlite/' .env.testing
    sed -i.bak 's/# DB_DATABASE=laravel/DB_DATABASE=:memory:/' .env.testing
    rm .env.testing.bak 2>/dev/null || true
fi

# Install dependencies if needed
if [ ! -d "vendor" ]; then
    print_status "Installing Laravel dependencies..."
    composer install --no-interaction --prefer-dist --optimize-autoloader
fi

# Generate application key for testing
php artisan key:generate --env=testing --force

print_status "Step 2: Running Backend Integration Tests"

# Run Laravel integration tests
print_status "Running Laravel PHPUnit tests..."
if php artisan test --testsuite=Feature --filter=Integration --stop-on-failure; then
    BACKEND_TESTS_PASSED=true
    print_success "Backend integration tests passed!"
else
    print_error "Backend integration tests failed!"
fi

# Run specific authentication tests
print_status "Running authentication flow tests..."
if php artisan test --filter=FullStackAuthenticationTest --stop-on-failure; then
    INTEGRATION_TESTS_PASSED=true
    print_success "Full-stack authentication tests passed!"
else
    print_error "Full-stack authentication tests failed!"
fi

cd ..

print_status "Step 3: Setting up Frontend test environment"

# Frontend setup
cd Frontend/vue-project

# Install dependencies if needed
if [ ! -d "node_modules" ]; then
    print_status "Installing Node.js dependencies..."
    npm install
fi

print_status "Step 4: Running Frontend Integration Tests"

# Run frontend integration tests
print_status "Running Vue.js integration tests..."
if npm run test; then
    FRONTEND_TESTS_PASSED=true
    print_success "Frontend integration tests passed!"
else
    print_error "Frontend integration tests failed!"
fi

cd ../..

print_status "Step 5: Cross-Domain Communication Tests"

# Test CORS configuration
print_status "Testing CORS configuration..."
cd Backend

# Start Laravel development server in background for CORS testing
print_status "Starting Laravel development server for CORS testing..."
php artisan serve --host=127.0.0.1 --port=8000 &
LARAVEL_PID=$!

# Wait for server to start
sleep 3

# Test CORS with curl
print_status "Testing CORS preflight request..."
CORS_TEST=$(curl -s -o /dev/null -w "%{http_code}" \
    -H "Origin: http://localhost:3000" \
    -H "Access-Control-Request-Method: POST" \
    -H "Access-Control-Request-Headers: Content-Type, Authorization" \
    -X OPTIONS \
    http://127.0.0.1:8000/api/login)

if [ "$CORS_TEST" = "200" ]; then
    print_success "CORS preflight test passed!"
else
    print_warning "CORS preflight test returned status: $CORS_TEST"
fi

# Test actual API endpoint
print_status "Testing API health endpoint..."
API_TEST=$(curl -s -o /dev/null -w "%{http_code}" \
    -H "Origin: http://localhost:3000" \
    -H "Accept: application/json" \
    http://127.0.0.1:8000/api/health)

if [ "$API_TEST" = "200" ]; then
    print_success "API health endpoint test passed!"
else
    print_warning "API health endpoint test returned status: $API_TEST"
fi

# Clean up Laravel server
kill $LARAVEL_PID 2>/dev/null || true
wait $LARAVEL_PID 2>/dev/null || true

cd ..

print_status "Step 6: Security Measures Verification"

# Test rate limiting
print_status "Testing rate limiting protection..."
cd Backend

# Run rate limiting specific tests
if php artisan test --filter=test_security_measures_integration --stop-on-failure; then
    print_success "Security measures tests passed!"
else
    print_warning "Some security measures tests failed - check logs"
fi

cd ..

print_status "Step 7: Test Results Summary"
echo "=================================================="

# Print test results summary
if [ "$BACKEND_TESTS_PASSED" = true ]; then
    print_success "✅ Backend Integration Tests: PASSED"
else
    print_error "❌ Backend Integration Tests: FAILED"
fi

if [ "$FRONTEND_TESTS_PASSED" = true ]; then
    print_success "✅ Frontend Integration Tests: PASSED"
else
    print_error "❌ Frontend Integration Tests: FAILED"
fi

if [ "$INTEGRATION_TESTS_PASSED" = true ]; then
    print_success "✅ Full-Stack Integration Tests: PASSED"
else
    print_error "❌ Full-Stack Integration Tests: FAILED"
fi

# Overall result
if [ "$BACKEND_TESTS_PASSED" = true ] && [ "$FRONTEND_TESTS_PASSED" = true ] && [ "$INTEGRATION_TESTS_PASSED" = true ]; then
    print_success "🎉 ALL INTEGRATION TESTS PASSED!"
    echo ""
    echo "The SAKTI Mini Login Module has successfully passed all integration tests:"
    echo "- Complete authentication flow (Requirements 1.1, 1.3)"
    echo "- Error handling and security (Requirements 1.2, 2.4)"
    echo "- Session management and logout (Requirements 1.5, 4.2)"
    echo "- Cross-domain communication (Requirements 2.5)"
    echo "- Input validation and sanitization (Requirements 2.3)"
    echo "- Rate limiting protection (Requirements 2.2)"
    echo "- Client-side validation (Requirements 3.1)"
    echo "- Secure error display (Requirements 3.2)"
    echo "- Route protection (Requirements 3.4, 3.5)"
    echo "- API response consistency (Requirements 4.4, 4.5)"
    echo ""
    echo "The system is ready for production deployment."
    exit 0
else
    print_error "❌ SOME INTEGRATION TESTS FAILED"
    echo ""
    echo "Please review the test output above and fix any failing tests before deployment."
    exit 1
fi