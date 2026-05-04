# Redis Configuration Guide

## Overview

Qalcuity ERP uses Redis for high-performance session storage, caching, and queue processing in production environments. This guide explains how to properly configure Redis authentication for different deployment scenarios.

## Configuration Variables

| Variable | Description | Default | Required |
|----------|-------------|---------|----------|
| `REDIS_ENABLED` | Enable/disable Redis usage | `false` | Yes |
| `REDIS_PASSWORD` | Redis server password | `null` | When auth required |
| `REDIS_HOST` | Redis server hostname | `127.0.0.1` | Yes |
| `REDIS_PORT` | Redis server port | `6379` | Yes |
| `REDIS_CLIENT` | PHP Redis client | `phpredis` | Yes |

## Deployment Scenarios

### 1. Production with Redis Authentication

**When to use**: Production environments with Redis `requirepass` configured

**Configuration**:
```env
REDIS_ENABLED=true
REDIS_PASSWORD=your_strong_redis_password_here
SESSION_DRIVER=redis
CACHE_STORE=redis
QUEUE_CONNECTION=redis
```

**Requirements**:
- Redis server must have `requirepass` configured
- Use strong, unique passwords (minimum 32 characters)
- Consider Redis ACL for fine-grained access control
- Enable TLS/SSL for Redis connections

### 2. Development without Authentication

**When to use**: Local development environments

**Configuration**:
```env
REDIS_ENABLED=true
REDIS_PASSWORD=null
SESSION_DRIVER=redis
CACHE_STORE=redis
QUEUE_CONNECTION=redis
```

**Requirements**:
- Redis server running without `requirepass`
- Suitable for local development only
- Never use in production environments

### 3. Database Fallback (Redis Disabled)

**When to use**: Environments without Redis or during Redis maintenance

**Configuration**:
```env
REDIS_ENABLED=false
SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
```

**Behavior**:
- Sessions stored in `sessions` table
- Cache stored in `cache` table
- Queues processed via `jobs` table
- Automatic fallback when Redis is unavailable

## Security Best Practices

### Password Management
- Use passwords with minimum 32 characters
- Include uppercase, lowercase, numbers, and symbols
- Rotate passwords regularly (quarterly recommended)
- Store passwords securely (environment variables, not code)

### Network Security
- Bind Redis to specific interfaces (not 0.0.0.0)
- Use firewall rules to restrict Redis port access
- Enable TLS/SSL for production Redis connections
- Consider VPN or private networks for Redis traffic

### Access Control
- Use Redis ACL for user-based permissions
- Create separate users for different application components
- Limit command access based on application needs
- Monitor Redis access logs regularly

## Troubleshooting

### Common Issues

**NOAUTH Authentication required**
- Cause: Redis requires password but none provided
- Solution: Set correct `REDIS_PASSWORD` in environment
- Verification: Test connection with `redis-cli -a password ping`

**Connection refused**
- Cause: Redis server not running or wrong host/port
- Solution: Verify Redis service status and connection details
- Verification: Check `redis-server` process and network connectivity

**Permission denied**
- Cause: Redis ACL restrictions or insufficient permissions
- Solution: Review Redis ACL configuration and user permissions
- Verification: Check Redis logs for authentication attempts

### Health Checks

Test Redis connectivity:
```bash
# Test basic connection
redis-cli -h 127.0.0.1 -p 6379 ping

# Test with authentication
redis-cli -h 127.0.0.1 -p 6379 -a your_password ping

# Test from Laravel
php artisan tinker
>>> Redis::ping()
```

### Monitoring

Monitor Redis performance and authentication:
- Track failed authentication attempts
- Monitor connection counts and memory usage
- Set up alerts for Redis unavailability
- Log authentication events for security auditing

## Migration Guide

### From Placeholder to Production Password

1. **Backup current configuration**:
   ```bash
   cp .env .env.backup
   ```

2. **Update Redis password**:
   ```env
   REDIS_PASSWORD=your_new_secure_password
   ```

3. **Update Redis server configuration**:
   ```
   requirepass your_new_secure_password
   ```

4. **Restart services**:
   ```bash
   sudo systemctl restart redis-server
   php artisan config:clear
   php artisan cache:clear
   ```

5. **Verify connectivity**:
   ```bash
   php artisan tinker
   >>> Redis::ping()
   ```

### From Database to Redis

1. **Install Redis server**:
   ```bash
   sudo apt install redis-server
   ```

2. **Configure Redis authentication**:
   ```
   requirepass your_secure_password
   ```

3. **Update environment variables**:
   ```env
   REDIS_ENABLED=true
   REDIS_PASSWORD=your_secure_password
   SESSION_DRIVER=redis
   CACHE_STORE=redis
   QUEUE_CONNECTION=redis
   ```

4. **Clear existing sessions/cache**:
   ```bash
   php artisan session:table --drop
   php artisan cache:clear
   php artisan config:clear
   ```

5. **Restart queue workers**:
   ```bash
   php artisan queue:restart
   ```

## Environment-Specific Examples

### Production (.env)
```env
REDIS_ENABLED=true
REDIS_PASSWORD=Prod_Redis_2024_SecurePass_!@#$%
REDIS_HOST=redis.internal.company.com
REDIS_PORT=6380
SESSION_DRIVER=redis
CACHE_STORE=redis
QUEUE_CONNECTION=redis
```

### Staging (.env.staging)
```env
REDIS_ENABLED=true
REDIS_PASSWORD=Staging_Redis_2024_TestPass_!@#
REDIS_HOST=redis-staging.internal.company.com
REDIS_PORT=6379
SESSION_DRIVER=redis
CACHE_STORE=redis
QUEUE_CONNECTION=redis
```

### Development (.env.local)
```env
REDIS_ENABLED=true
REDIS_PASSWORD=null
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
SESSION_DRIVER=redis
CACHE_STORE=redis
QUEUE_CONNECTION=database
```

## Support

For additional support with Redis configuration:
- Check Laravel Redis documentation
- Review Redis official documentation
- Monitor application logs for Redis-related errors
- Contact system administrator for infrastructure issues
