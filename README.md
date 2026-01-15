# Laravel Deployment Template

A Laravel application template with Docker-based deployment infrastructure, service orchestration, and automated workflows.

## Overview

This project combines a fresh Laravel application with a flexible Docker Compose deployment system. It provides:

- **Laravel 12** - Modern PHP web application framework
- **Docker Infrastructure** - Pre-configured services (Nginx, MySQL, PostgreSQL, Redis, RabbitMQ)
- **Service Orchestration** - Automated dependency resolution and configuration merging
- **Simple Workflow** - Make commands for common operations

### Resources

- **Laravel Documentation**: [https://laravel.com/docs](https://laravel.com/docs)
- **Deployment Infrastructure Template**: [https://github.com/konstantin-budylov/laravel-deployment-template](https://github.com/konstantin-budylov/laravel-deployment-template)

## Quick Start

### Prerequisites

- PHP 8.2+
- Composer
- Node.js & npm
- Docker and Docker Compose
- Make utility

### Installation

```bash
# Install PHP dependencies
composer install

# Install JavaScript dependencies
npm install

# Build frontend assets
npm run build

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Run migrations
php artisan migrate
```

### Running with Docker

```bash
# Build and configure services (e.g., nginx, mysql, redis)
make build nginx84 mysql redis

# Start services
make up

# Check status
docker ps

# Stop services
make down

# Full cleanup
make clean
```

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. Key features include:

- [Simple, fast routing engine](https://laravel.com/docs/routing)
- [Powerful dependency injection container](https://laravel.com/docs/container)
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent)
- Database agnostic [schema migrations](https://laravel.com/docs/migrations)
- [Robust background job processing](https://laravel.com/docs/queues)
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting)

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks. You can also check out [Laravel Learn](https://laravel.com/learn).

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript.

## Deployment Infrastructure

This template includes a flexible Docker Compose deployment system with the following features:

### Available Services

- **nginx84** - Nginx web server with PHP 8.4-FPM
- **mysql** - MySQL database server
- **postgresql** - PostgreSQL database server
- **redis** - Redis cache and session store
- **rabbitmq** - RabbitMQ message broker

### Service Management Commands

#### Build and Configure Services

```bash
# Build specific services with automatic dependency resolution
make build nginx84 redis

# Services are defined in services.yaml with their dependencies
```

#### Start Services

```bash
# Start all configured services
make up

# Check running containers
docker ps
```

#### Stop Services

```bash
# Stop services and remove containers
make down
```

#### Full Cleanup

```bash
# Remove containers, volumes, networks, and merged files
make clean
```

### Project Structure

```
.
├── app/                  # Laravel application code
├── deployment/
│   ├── scripts/          # Build, up, down, clean scripts
│   ├── services/         # Service definitions
│   │   ├── nginx84/
│   │   ├── mysql/
│   │   ├── postgresql/
│   │   ├── redis/
│   │   └── rabbitmq/
│   └── temp/             # Temporary merged files
├── services.yaml         # Service definitions and dependencies
├── docker-compose.yml    # Auto-generated compose file
└── Makefile              # Command shortcuts
```

### Service Configuration

Services are defined in `services.yaml` with their dependencies:

```yaml
services:
  redis:
    compose: deployment/services/redis/docker-compose.yml
    env: deployment/services/redis/.env.dist
  
  nginx84:
    compose: deployment/services/nginx84/docker-compose.yml
    env: deployment/services/nginx84/.env.dist
    depends: [redis]  # Start redis before nginx84
```

### Advanced Usage

#### Verbose Mode

```bash
./deployment/scripts/build.sh --verbose nginx84 redis
```

#### Custom Configuration

```bash
./deployment/scripts/build.sh --yaml custom-services.yaml nginx84
```

#### Development Workflow

```bash
# Initial setup
make build nginx84 mysql redis
make up

# After config changes
make down
make build nginx84 mysql redis
make up

# Clean rebuild
make clean
make build nginx84 mysql redis
make up
```

### How It Works

1. **Service Resolution** - Automatically resolves dependencies defined in `services.yaml`
2. **Configuration Merging** - Merges Docker Compose files and environment variables
3. **File Generation** - Creates `docker-compose.yml` and `.env` in the project root
4. **Orchestration** - Manages containers, networks, and volumes



## Laravel Sponsors

We would like to extend our thanks to the Laravel sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

## Contributing

Thank you for considering contributing to this project! For Laravel framework contributions, please see the [Laravel documentation](https://laravel.com/docs/contributions).

For deployment infrastructure improvements:
1. Follow the existing service structure in `deployment/services/`
2. Add service definitions to `services.yaml`
3. Test with `make build` and `make clean`

## Code of Conduct

Please review and abide by the [Laravel Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com).

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## Author

Konstantin Budylov: k.budylov@gmail.com


