# INTEGRATIONS.md

## Email Services

### Configured (via config/services.php)

| Service | Configuration | Environment Variables |
|---------|---------------|---------------------|
| Postmark | API key | POSTMARK_API_KEY |
| Resend | API key | RESEND_API_KEY |
| AWS SES | Key, secret, region | AWS_ACCESS_KEY_ID, AWS_SECRET_ACCESS_KEY, AWS_DEFAULT_REGION |

### Fallback

- Mailgun (Laravel native)
- SendMail (PHP native)

## Slack Notifications

- **Bot OAuth Token**: SLACK_BOT_USER_OAUTH_TOKEN
- **Default Channel**: SLACK_BOT_USER_DEFAULT_CHANNEL

## File Storage

### Drivers Supported

- Local (default)
- AWS S3
- FTP
- SFTP
- Rackspace

### Configuration

```php
// config/filesystems.php
'disks' => [
    'local' => [...],
    'public' => [...],
    's3' => [...],
]
```

## Queue System

### Drivers

- Sync (default, for development)
- Database
- Beanstalkd
- SQS
- Redis

### Configuration

```php
// config/queue.php
'connections' => [
    'sync' => ['driver' => 'sync'],
    'database' => ['driver' => 'database', ...],
    'redis' => ['driver' => 'redis', ...],
]
```

## Cache System

### Drivers

- File (default)
- Database
- Memcached
- Redis
- DynamoDB
- Array (testing)

## Session

### Drivers

- File (default)
- Database
- Memcached
- Redis
- Cookie

## Authentication

- **Provider**: Laravel Fortify (Breeze successor)
- **2FA**: Built-in TOTP support via Fortify
- **Password Reset**: Via email (Postmark/Resend/SES/Mailgun)

## External Services (Optional)

- **Debug**: Telescope (dev only)
- **Horizon**: For Redis queue monitoring (optional)
- **Envoyer**: Deployment (external)
- **Forge**: Server management (external)
- **Vapor**: Serverless (external, not configured)

## API Resources

- **Default**: Eloquent API Resources
- **Versioning**: Not currently configured