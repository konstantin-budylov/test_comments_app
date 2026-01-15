# Comments Application API

A Laravel-based REST API for managing entities (News, Video Posts) with a hierarchical commenting system. Features polymorphic relationships, nested comments with path-based ordering, and cursor pagination.

## Quick Start

### Prerequisites

- PHP 8.2+
- Composer
- Node.js & npm
- Docker and Docker Compose
- Make utility

### Installation

#### 1. Clone and Install Dependencies

```bash
# Install PHP dependencies
composer install

# Install JavaScript dependencies
npm install

# Build frontend assets
npm run build
```

#### 2. Environment Configuration

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Configure database connection in .env:
# DB_CONNECTION=mysql
# DB_HOST=mysql          # or 127.0.0.1 for local
# DB_PORT=3306
# DB_DATABASE=laravel
# DB_USERNAME=test
# DB_PASSWORD=password
```

#### 3. Database Setup

```bash
# Run migrations to create tables
php artisan migrate

# (Optional) Seed database with sample data
php artisan db:seed
```

#### 4. Start Development Server

**Option A: Using PHP Built-in Server**
```bash
php artisan serve
# API will be available at http://localhost:8000
```

**Option B: Using Docker-Compose (Recommended)**
```bash

docker-compose up -d --build


# API will be available at https://localhost (or configured domain)
```

### Testing the API

```bash
# Check health endpoint
curl https://localhost/api/v1/health

# View API documentation
# Open https://localhost/docs in your browser
```

## API Endpoints

Base URL: `/api/v1`

All endpoints return JSON responses. Cursor pagination is used for list endpoints.

### Health Check

Check API status and connectivity.

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/health` | Returns API health status |

**Response Example:**
```json
{
  "status": "ok"
}
```

---

### Entities

Manage polymorphic entities (News, Video Posts).

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/entities` | List all entities with cursor pagination |
| GET | `/entities/{entity_id}` | Get single entity with comments |
| POST | `/entities` | Create new entity |
| POST | `/entities/{entity_id}/comment` | Add comment to entity |

**Create Entity:**
```bash
POST /api/v1/entities
Content-Type: application/json

{
  "entity_type": 1,           # 1 = News, 2 = VideoPost
  "title": "Sample Title",
  "description": "This is a description"
}
```

**Response:**
```json
{
  "entity_id": 1,
  "entity_type": "news",
  "data": {
    "id": 1,
    "title": "Sample Title",
    "description": "This is a description",
    "created_at": "2026-01-15T12:00:00.000000Z"
  }
}
```

**List Entities (with cursor pagination):**
```bash
GET /api/v1/entities?entitiesCursor=eyJpZCI6NiwgImNyZWF0ZWRfYXQiOiIyMDI2LTAxLTE1VDEyOjM0OjQyLjAwMDAwMFoifQ==
```

**Get Entity with Comments:**
```bash
GET /api/v1/entities/1?commentsCursor=eyJpZCI6MiwgImNyZWF0ZWRfYXQiOiIyMDI2LTAxLTE1VDEyOjIyOjQ5LjAwMDAwMFoifQ==
```

---

### News

Filter entities by News type.

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/news` | List all news entities with cursor pagination |

**Query Parameters:**
- `newsCursor` - Cursor for pagination

**Example:**
```bash
GET /api/v1/news?newsCursor=eyJpZCI6NiwgImNyZWF0ZWRfYXQiOiIyMDI2LTAxLTE1VDEyOjM0OjQyLjAwMDAwMFoifQ==
```

---

### Video Posts

Filter entities by Video Post type.

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/video` | List all video post entities with cursor pagination |

**Query Parameters:**
- `videoPostCursor` - Cursor for pagination

**Example:**
```bash
GET /api/v1/video?videoPostCursor=eyJpZCI6NiwgImNyZWF0ZWRfYXQiOiIyMDI2LTAxLTE1VDEyOjM0OjQyLjAwMDAwMFoifQ==
```

---

### Comments

Manage hierarchical comments on entities.

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/comments/{entity_id}` | List all comments for entity (tree structure) |
| POST | `/entities/{entity_id}/comment` | Create comment on entity |
| PUT | `/comments/{comment}` | Update existing comment |
| DELETE | `/comments/{comment}` | Delete comment (soft delete if has children) |

**Create Comment:**
```bash
POST /api/v1/entities/1/comment
Content-Type: application/json

{
  "user_id": 1,
  "text": "This is a comment.",
  "parent_id": 2              # Optional - for nested replies
}
```

**Response:**
```json
{
  "data": {
    "id": 3,
    "entity_id": 1,
    "user_id": 1,
    "parent_id": 2,
    "text": "This is a comment.",
    "path": "0001.0002.0003",
    "created_at": "2026-01-15T12:00:00.000000Z"
  }
}
```

**Update Comment:**
```bash
PUT /api/v1/comments/3
Content-Type: application/json

{
  "user_id": 1,
  "text": "Updated comment text."
}
```

**Delete Comment:**
```bash
DELETE /api/v1/comments/3
Content-Type: application/json

{
  "user_id": 1
}
```

**Response (soft delete if has children):**
```json
{
  "data": {
    "id": 3,
    "deleted": true,
    "soft": true              # true if comment has children, false if fully deleted
  }
}
```

**List Comments (tree structure):**
```bash
POST /api/v1/comments/1
```

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "user_id": 1,
      "text": "Root comment",
      "created_at": "2026-01-15T12:00:00.000000Z",
      "children": [
        {
          "id": 2,
          "user_id": 2,
          "text": "Reply to root",
          "created_at": "2026-01-15T12:05:00.000000Z",
          "children": []
        }
      ]
    }
  ]
}
```

---

### Error Handling

The API uses standard HTTP status codes:

- `200` - Success
- `201` - Created
- `400` - Bad Request
- `403` - Forbidden (e.g., updating another user's comment)
- `404` - Not Found
- `422` - Unprocessable Entity (validation errors)
- `500` - Internal Server Error

**Error Response Format:**
```json
{
  "error": "error_code",
  "message": "Human-readable error message"
}
```

---

### Cursor Pagination

All list endpoints use cursor-based pagination for efficient data retrieval:

**Response Format:**
```json
{
  "data": [...],
  "meta": {
    "next_cursor": "eyJpZCI6MTAsICJjcmVhdGVkX2F0IjoiMjAyNi0wMS0xNVQxMjo0MDowMC4wMDAwMDBaIn0=",
    "prev_cursor": "eyJpZCI6MSwgImNyZWF0ZWRfYXQiOiIyMDI2LTAxLTE1VDEyOjAwOjAwLjAwMDAwMFoifQ==",
    "per_page": 15
  }
}
```

Use the `next_cursor` or `prev_cursor` values in subsequent requests to navigate through pages.

---

## Database Schema & Architecture

### Entity Types

The application uses polymorphic relationships to support multiple entity types:

| Entity Type | Value | Model | Description |
|------------|-------|-------|-------------|
| News | 1 | `App\Models\News` | News articles |
| Video Post | 2 | `App\Models\VideoPost` | Video content posts |

### Database Tables

#### `entities`
Central polymorphic entity table linking all commentable content.
- `id` - Primary key
- `entityable_type` - Entity type enum (1=News, 2=VideoPost)
- `entityable_id` - Foreign key to specific entity table
- Timestamps

#### `news`
News-specific content.
- `id` - Primary key
- `title` - News title
- `description` - News content
- Timestamps

#### `video_posts`
Video-specific content.
- `id` - Primary key
- `title` - Video title
- `description` - Video description
- Timestamps

#### `comments`
Hierarchical comment system with path-based ordering.
- `id` - Primary key
- `entity_id` - Foreign key to entities table
- `user_id` - Foreign key to users table
- `parent_id` - Self-referencing foreign key for nested comments
- `text` - Comment content
- `path` - Materialized path for efficient tree queries (e.g., "0001.0002.0003")
- Timestamps

### Path-Based Ordering

Comments use a materialized path pattern for efficient hierarchical queries:
- Root comment: `0001`
- First reply: `0001.0002`
- Reply to first reply: `0001.0002.0003`

This allows:
- Fast retrieval of entire comment threads
- Efficient sorting by tree order
- Quick parent-child relationship queries

### Soft Delete Logic

When deleting a comment:
- **Has children**: Comment text is replaced with "Comment has been deleted" (soft delete)
- **No children**: Comment is permanently deleted from database

This preserves thread structure while allowing cleanup of leaf comments.

---

### Laravel Artisan Commands

```bash
# Run migrations
php artisan migrate

# Rollback migrations
php artisan migrate:rollback

# Seed database with test data
php artisan db:seed

# Clear application cache
php artisan cache:clear

# Clear route cache
php artisan route:clear

# Clear config cache
php artisan config:clear

# Generate IDE helper files (if installed)
php artisan ide-helper:generate
```

---

## API Documentation

The API documentation is auto-generated using [Scribe](https://scribe.knuckles.wtf/).

### Viewing Documentation

- **HTML Documentation**: Available at `/docs` when the server is running
- **OpenAPI/Swagger**: Available at `/docs/openapi.yaml`
- **Postman Collection**: Available at `/docs/collection.json`

### Regenerating Documentation

```bash
# Generate API documentation from controller annotations
php artisan scribe:generate
```

Documentation is generated from PHPDoc comments in controllers. Example:

```php
/**
 * Create entity comment
 * @group Comments
 *
 * @bodyParam user_id integer required user_id. Example: 1
 * @bodyParam text string required comment text. Example: This is a comment.
 * @bodyParam parent_id integer parent comment id. Example: 2
 */
public function create(int $entity_id, StoreCommentRequest $request): JsonResponse
{
    // ...
}
```

---

## Project Structure

```
test_comments_app/
├── app/
│   ├── Enums/
│   │   └── EntityTypes.php           # Entity type enumeration
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── Api/
│   │   │       └── V1/               # API v1 controllers
│   │   ├── Requests/                 # Form request validation
│   │   └── Resources/                # API response resources
│   ├── Models/
│   │   ├── Comment.php               # Comment model with path logic
│   │   ├── Entity.php                # Polymorphic entity model
│   │   ├── News.php                  # News entity model
│   │   ├── VideoPost.php             # Video post entity model
│   │   └── User.php                  # User model
│   └── Providers/
├── config/                            # Application configuration
├── database/
│   ├── migrations/                    # Database migrations
│   ├── seeders/                       # Database seeders
│   └── factories/                     # Model factories
├── deployment/
│   ├── scripts/                       # Deployment automation scripts
│   └── services/                      # Docker service configurations
├── public/
│   └── docs/                          # Generated API documentation
├── routes/
│   ├── api.php                        # API routes
│   ├── web.php                        # Web routes
│   └── console.php                    # Console routes
├── tests/                             # Test files
├── docker-compose.yml                 # Docker compose configuration
└── README.md                          # This file
```

---

## Environment Variables

Key environment variables in `.env`:

```bash
# Application
APP_NAME=Laravel
APP_ENV=local
APP_DEBUG=true
APP_URL=https://localhost

# Database (MySQL)
DB_CONNECTION=mysql
DB_HOST=mysql              # Use 'mysql' for Docker, '127.0.0.1' for local
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=test
DB_PASSWORD=password

# Cache & Session
CACHE_STORE=file
SESSION_DRIVER=file

# Queue
QUEUE_CONNECTION=database

# Redis (optional)
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

---

## Author

Konstantin Budylov: k.budylov@gmail.com


