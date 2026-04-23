# STACK.md

## PHP

- **Version**: ^8.3
- **Runtime**: Laravel's Artisan tinker, server

## Laravel Framework

- **Laravel**: ^13.0
- **Fortify**: ^1.34 (authentication)
- **Tinker**: ^3.0

## Livewire & Flux UI

- **Livewire**: ^4.1
- **Flux UI**: ^2.13.1

## Development Tools

- **Pest**: ^4.6 (testing)
- **Pest Plugin Laravel**: ^4.1
- **Pint**: ^1.27 (code formatter)
- **Laravel Boost**: ^2.2
- **Laravel Sail**: ^1.53
- **Laravel Pail**: ^1.2.5 (logging)
- **FakerPHP**: ^1.24
- **Mockery**: ^1.6
- **Collision**: ^8.9.3
- **PAO**: ^1.0.3

## Frontend

- **JavaScript**: Vite (build tool)
- **Tailwind CSS**: v4 (per AGENTS.md)
- **Alpine.js**: (bundled with Livewire)

## Database

- **Default**: SQLite (per project setup)
- **Engine**: MySQL/PostgreSQL configurable via config/database.php

## Cache & Queue

- **Cache**: File/Redis/Memcached (configurable)
- **Queue**: Sync/Database/Redis (configurable)

## Session

- **Driver**: File/database/Redis (configurable)

## Mail

- **Driver**: SMTP/SendMail/Postmark/Resend/SES (configurable)

## File Storage

- **Driver**: Local/S3/FTP/SFTP/Rackspace (configurable)