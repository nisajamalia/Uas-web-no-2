# SAKTI Mini Login Module - Integration Testing Guide

## Overview

This document describes the comprehensive end-to-end integration testing setup for the SAKTI Mini Login Module. The testing framework validates the complete authentication flow between the Laravel backend API and Vue.js frontend SPA, including cross-domain communication and security measures.

## Test Coverage

### Requirements Validation

The integration tests validate the following requirements:

- **1.1**: Complete authentication flow with valid credentials
- **1.2**: Uniform error handling for authentication failures  
- **1.5**: Complete session invalidation on logout
- **2.2**: Rate limiting protection against brute-force attacks
- **2.3**: Input validation and sanitization against injection attacks
- **2.4**: Uniform error messages to prevent user enumeration
- **2.5**: CORS configuration for cross-domain communication
- **3.1**: Client-side validation before server requests
- **3.2**: Secure error display without sensitive information leakage
- **3.3**: Secure session storage methods
- **3.4**: Route protection for unauthenticated users
- **3.5**: Automatic logout on session expiration
- **4.4**: Consistent API response formats
- **4.5**: Proper HTTP status codes

## Test Structure

### Backend Integration Tests

Located in `Backend/tests/Integration/`:

#### FullStackAuthenticationTest.php
- **test_complete_authentication_flow()**: Tests login → profile access → logout cycle
- **test_cors_cross_domain_communication()**: Validates CORS preflight and actual requests
- **test_security_measures_integration()**: Tests rate limiting, input validation, uniform errors
- **test_session_management_lifecycle()**: Tests token creation, storage, and cleanup
- **test_multiple_concurrent_sessions()**: Tests multiple active sessions per user
- **test_api_response_consistency()**: Validates consistent JSON response formats

#### TestEnvironmentSetup.php
- **test_testing_environment_configuration()**: Validates test environment setup
- **test_cors_configuration_for_testing()**: Tests CORS configuration
- **test_rate_limiting_configuration()**: Validates rate limiting setup
- **test_security_headers_configuration()**: Tests security headers
- **test_database_setup_for_testing()**: Validates database migrations
- **test_cross_domain_request_simulation()**: Tests cross-domain scenarios

### Frontend Integration Tests

Located in `Frontend/vue-project/src/test/integration/`:

#### AuthenticationFlow.test.ts
- **should complete full login flow successfully**: Tests form submission to dashboard redirect
- **should handle login failures with uniform error messages**: Tests error handling
- **should perform client-side validation before server requests**: Tests validation
- **should protect routes and redirect unauthenticated users**: Tests route guards
- **should allow authenticated users to access protected routes**: Tests authenticated access
- **should handle logout and clean up session completely**: Tests logout functionality
- **should handle session expiration and auto-logout**: Tests session expiration
- **should configure API for cross-domain communication**: Tests API configuration
- **should not leak sensitive information in error messages**: Tests error security

## Running Integration Tests

### Prerequisites

1. **Backend Requirements**:
   - PHP 8.1+
   - Composer
   - Laravel dependencies installed
   - SQLite (for in-memory testing database)

2. **Frontend Requirements**:
   - Node.js 20.19.0+ or 22.12.0+
   - npm
   - Vue.js dependencies installed

### Quick Start

Run all integration tests with the provided script:

```bash
./test-integration.sh
```

This script will:
1. Set up test environments for both backend and frontend
2. Run Laravel PHPUnit integration tests
3. Run Vue.js Vitest integration tests
4. Test cross-domain communication with CORS
5. Verify security measures
6. Provide comprehensive test results summary

### Manual Test Execution

#### Backend Tests Only

```bash
cd Backend

# Run all integration tests
php artisan test --testsuite=Feature --filter=Integration

# Run specific test class
php artisan test --filter=FullStackAuthenticationTest

# Run with coverage (if xdebug enabled)
php artisan test --coverage --filter=Integration
```

#### Frontend Tests Only

```bash
cd Frontend/vue-project

# Install dependencies
npm install

# Run integration tests
npm run test

# Run tests in watch mode
npm run test:watch

# Run tests with UI
npm run test:ui
```

### Test Environment Configuration

#### Backend Configuration

The backend tests use:
- **Database**: In-memory SQLite (`:memory:`)
- **Cache**: Array driver
- **Session**: Array driver
- **Environment**: `testing`

Configuration is automatically set up in `phpunit.xml` and `.env.testing`.

#### Frontend Configuration

The frontend tests use:
- **Test Runner**: Vitest
- **Environment**: jsdom
- **Mocking**: Vi (Vitest's mocking library)
- **Vue Testing**: @vue/test-utils

Configuration is in `vitest.config.ts` and `src/test/setup.ts`.

## Cross-Domain Testing

### CORS Configuration

The tests validate CORS configuration for production domains:
- `http://localhost:3000` (development frontend)
- `https://app.kampus.ac.id` (production frontend)
- `http://127.0.0.1:3000` (alternative development)

### Preflight Request Testing

Tests validate that OPTIONS preflight requests are handled correctly:

```http
OPTIONS /api/login HTTP/1.1
Origin: http://localhost:3000
Access-Control-Request-Method: POST
Access-Control-Request-Headers: Content-Type, Authorization
```

Expected response headers:
- `Access-Control-Allow-Origin: http://localhost:3000`
- `Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE`
- `Access-Control-Allow-Headers: Content-Type, Authorization`

## Security Testing

### Rate Limiting Tests

Tests validate that after 5 failed login attempts, subsequent requests return HTTP 429 (Too Many Requests).

### Input Validation Tests

Tests validate protection against:
- XSS attacks (`<script>alert("xss")</script>`)
- SQL injection (`'; DROP TABLE users; --`)
- HTML injection (`<img src=x onerror=alert(1)>`)

### Error Message Security

Tests ensure error messages don't leak:
- Database connection strings
- SQL queries
- File paths
- Stack traces
- User existence information

## Continuous Integration

### GitHub Actions Example

```yaml
name: Integration Tests

on: [push, pull_request]

jobs:
  integration-tests:
    runs-on: ubuntu-latest
    
    steps:
    - uses: actions/checkout@v3
    
    - name: Setup PHP
      uses: shivammathur/setup-php@v2
      with:
        php-version: '8.1'
        extensions: mbstring, xml, ctype, iconv, intl, pdo_sqlite
    
    - name: Setup Node.js
      uses: actions/setup-node@v3
      with:
        node-version: '20'
    
    - name: Install Backend Dependencies
      run: |
        cd Backend
        composer install --no-progress --prefer-dist --optimize-autoloader
    
    - name: Install Frontend Dependencies
      run: |
        cd Frontend/vue-project
        npm ci
    
    - name: Run Integration Tests
      run: ./test-integration.sh
```

## Troubleshooting

### Common Issues

1. **Port Conflicts**: Ensure ports 8000 (Laravel) and 3000 (Vue) are available
2. **Permission Issues**: Make sure `test-integration.sh` is executable (`chmod +x`)
3. **Database Issues**: Ensure SQLite is available for in-memory testing
4. **CORS Issues**: Check that CORS middleware is properly configured

### Debug Mode

Run tests with verbose output:

```bash
# Backend
cd Backend
php artisan test --filter=Integration --verbose

# Frontend  
cd Frontend/vue-project
npm run test -- --reporter=verbose
```

### Test Data Cleanup

Tests use `RefreshDatabase` trait to ensure clean state between tests. No manual cleanup is required.

## Performance Considerations

### Test Execution Time

- Backend integration tests: ~30-60 seconds
- Frontend integration tests: ~15-30 seconds
- Cross-domain tests: ~10-15 seconds
- Total execution time: ~1-2 minutes

### Optimization Tips

1. Use `--stop-on-failure` for faster feedback during development
2. Run specific test classes instead of full suite when debugging
3. Use parallel test execution for larger test suites
4. Consider test database optimization for larger datasets

## Maintenance

### Adding New Tests

1. **Backend**: Add new test methods to existing classes or create new classes in `Backend/tests/Integration/`
2. **Frontend**: Add new test cases to `Frontend/vue-project/src/test/integration/`
3. **Update Documentation**: Update this guide when adding new test scenarios

### Test Data Management

- Use Laravel factories for consistent test data
- Use Vitest mocks for frontend API responses
- Ensure tests are isolated and don't depend on external services

### Version Updates

When updating dependencies:
1. Update test configurations if needed
2. Run full test suite to ensure compatibility
3. Update CI/CD pipelines if test commands change

## Conclusion

This comprehensive integration testing setup ensures the SAKTI Mini Login Module meets all security, functionality, and performance requirements. The tests provide confidence in the system's reliability and security for production deployment.

For questions or issues with the testing setup, refer to the project documentation or contact the development team.