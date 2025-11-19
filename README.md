# Ticketing System - Laravel Backend API

A comprehensive, production-ready ticketing system built with Laravel that provides complete support ticket management with role-based access control, real-time notifications, file attachments, and comprehensive API documentation.

## Table of Contents

-   [Features](#-features)
-   [Tech Stack](#-tech-stack)
-   [Database Schema](#-database-schema)
-   [API Endpoints](#-api-endpoints)
-   [Installation](#-installation)
-   [Testing](#-testing)
-   [Role-Based Access Control](#-role-based-access-control)
-   [Business Logic](#-business-logic)
-   [API Documentation](#-api-documentation)

## Features

### Authentication & Authorization

-   **JWT-based Authentication** using Laravel Sanctum
-   **Role-Based Access Control** (User, Agent, Admin)
-   **Secure password hashing** with bcrypt
-   **Token-based API authentication**

### Ticket Management

-   **Create, read, update, delete tickets**
-   **Ticket categorization** (Technical, Billing, General, Other)
-   **Priority levels** (Low, Medium, High)
-   **Status workflow** (Open → In Progress → Resolved → Closed)
-   **Smart ticket assignment** to least busy agents
-   **Advanced filtering & search** by status, priority, category
-   **Pagination** for large datasets

### Communication System

-   **Real-time comments** on tickets
-   **Comment editing** within 1-hour window
-   **Role-based comment visibility**
-   **Prevent comments on closed tickets**

### File Attachments

-   **File uploads** for tickets and comments
-   **Multiple file type support** (images, PDFs, documents, archives)
-   **File size validation** (10MB max per file)
-   **Secure file storage** with access control
-   **File download with authentication**

### Notifications & Email

-   **Automated email notifications** for key events
-   **Event-driven architecture** for extensibility
-   **Notification scenarios**:
    -   Ticket creation (notifies admins/agents)
    -   Ticket assignment (notifies assigned agent)
    -   New comments (notifies ticket owner & assignee)
    -   Status changes (notifies ticket owner)

### Analytics & Reporting

-   **Role-based dashboard statistics**
-   **Ticket metrics** by status, priority, category
-   **Agent performance tracking**
-   **Workload distribution analysis**

### Documentation

-   **Postman josn file including all the requests is in the project**
    Ticketing System API.postman_collection.json

## Tech Stack

**Backend:**

-   Laravel 10.x
-   PHP 8.1+
-   Laravel Sanctum (API Authentication)
-   Laravel Notifications (Email)

**Database:**

-   MySQL 8.0+
-   Eloquent ORM
-   Database migrations & seeders

**Testing:**

-   PHPUnit
-   Laravel Testing Helpers
-   Database transactions for test isolation

**Tools:**

-   Mailpit (Email testing)
-   Postman (Documentation)
-   Composer (Dependency management)

## Database Schema

```sql
users
├── id (PK)
├── name
├── email (unique)
├── password (hashed)
├── role (enum: user, agent, admin)
├── created_at
└── updated_at

tickets
├── id (PK)
├── user_id (FK to users)           # Ticket creator
├── assigned_to (FK to users)       # Assigned agent
├── title
├── description (TEXT)
├── category (enum: technical, billing, general, other)
├── priority (enum: low, medium, high)
├── status (enum: open, in_progress, resolved, closed)
├── created_at
└── updated_at

comments
├── id (PK)
├── ticket_id (FK to tickets)
├── user_id (FK to users)
├── comment_text (TEXT)
├── created_at
└── updated_at

attachments
├── id (PK)
├── ticket_id (FK to tickets)
├── comment_id (FK to comments, nullable)
├── user_id (FK to users)
├── filename
├── path (storage path)
├── mime_type
├── size (bytes)
├── created_at
└── updated_at
```

### Key Relationships

-   **User** → hasMany **Tickets** (as creator)
-   **User** → hasMany **Tickets** (as assignee)
-   **User** → hasMany **Comments**
-   **Ticket** → belongsTo **User** (creator)
-   **Ticket** → belongsTo **User** (assignee)
-   **Ticket** → hasMany **Comments**
-   **Ticket** → hasMany **Attachments**
-   **Comment** → belongsTo **Ticket**
-   **Comment** → belongsTo **User**
-   **Comment** → hasMany **Attachments**
-   **Attachment** → belongsTo **Ticket**
-   **Attachment** → belongsTo **Comment** (optional)
-   **Attachment** → belongsTo **User**

## API Endpoints

### Authentication

| Method | Endpoint        | Description       | Auth Required |
| ------ | --------------- | ----------------- | ------------- |
| POST   | `/api/register` | Register new user | No            |
| POST   | `/api/login`    | Login user        | No            |
| POST   | `/api/logout`   | Logout user       | Yes           |
| GET    | `/api/user`     | Get current user  | Yes           |

### Tickets

| Method | Endpoint              | Description    | Roles             |
| ------ | --------------------- | -------------- | ----------------- |
| GET    | `/api/tickets`        | List tickets   | All               |
| POST   | `/api/tickets`        | Create ticket  | User+             |
| GET    | `/api/tickets/{id}`   | Get ticket     | Owner/Agent/Admin |
| PUT    | `/api/tickets/{id}`   | Update ticket  | Owner/Agent/Admin |
| DELETE | `/api/tickets/{id}`   | Delete ticket  | Owner/Admin       |
| GET    | `/api/tickets/search` | Search tickets | All               |

### Comments

| Method | Endpoint                     | Description    | Roles             |
| ------ | ---------------------------- | -------------- | ----------------- |
| GET    | `/api/tickets/{id}/comments` | Get comments   | Owner/Agent/Admin |
| POST   | `/api/tickets/{id}/comments` | Add comment    | Owner/Agent/Admin |
| PUT    | `/api/comments/{id}`         | Update comment | Comment Author    |
| DELETE | `/api/comments/{id}`         | Delete comment | Author/Admin      |

### Attachments

| Method | Endpoint                         | Description       | Roles             |
| ------ | -------------------------------- | ----------------- | ----------------- |
| POST   | `/api/tickets/{id}/attachments`  | Upload file       | Owner/Agent/Admin |
| GET    | `/api/tickets/{id}/attachments`  | List attachments  | Owner/Agent/Admin |
| GET    | `/api/attachments/{id}/download` | Download file     | Owner/Agent/Admin |
| DELETE | `/api/attachments/{id}`          | Delete attachment | Uploader/Admin    |

### Assignment & Stats

| Method | Endpoint                        | Description     | Roles       |
| ------ | ------------------------------- | --------------- | ----------- |
| POST   | `/api/tickets/{id}/assign`      | Assign to agent | Agent/Admin |
| POST   | `/api/tickets/{id}/auto-assign` | Auto-assign     | Agent/Admin |
| POST   | `/api/tickets/{id}/unassign`    | Unassign ticket | Agent/Admin |
| GET    | `/api/stats/dashboard`          | Get statistics  | All         |

## ⚙️ Installation

### Prerequisites

-   PHP 8.1+
-   Composer
-   MySQL 8.0+

### Step-by-Step Setup

1. **Clone the repository**

```bash
git clone <repository-url>
cd ticketing-system
```

2. **Install dependencies**

```bash
composer install
```

3. **Environment configuration**

```bash
cp .env.example .env
php artisan key:generate
```

4. **Update .env file**

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ticketing_system
DB_USERNAME=your_username
DB_PASSWORD=your_password

MAIL_MAILER=smtp
MAIL_HOST=127.0.0.1
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null

```

5. **Database setup**

```bash
php artisan migrate
php artisan db:seed
```

6. **Start services**

```bash
# Start Laravel development server
php artisan serve

# Start Mailpit for email testing (in separate terminal)
docker run -d -p 1025:1025 -p 8025:8025 axllent/mailpit
```

7. **Access the application**

```
API Server: http://localhost:8000
Mailpit: http://localhost:8025
```

## Testing

### Run Test Suite

```bash
# Run all tests
php artisan test

# Run specific test group
php artisan test --filter=AuthTest
php artisan test --filter=TicketTest
php artisan test --filter=CommentTest

# Run with coverage
php artisan test --coverage
```

### Test Data

The system comes with seeded test data:

-   **Admin**: `admin@tickets.com` / `password`
-   **Agents**: 3 pre-created agents
-   **Users**: 10 test users
-   **Tickets**: 50+ tickets with various statuses

### Manual Testing with Postman

1. Import the Postman collection from `/docs/postman-collection.json`
2. Set base URL to `http://localhost:8000/api`
3. Use the provided test credentials

## Role-Based Access Control

### User Roles & Permissions

| Role      | Ticket Access         | Comment Access        | Assignment | Admin Features |
| --------- | --------------------- | --------------------- | ---------- | -------------- |
| **User**  | Own tickets only      | On own tickets        | ❌ No      | ❌ No          |
| **Agent** | Assigned + unassigned | On accessible tickets | ✅ Yes     | ❌ No          |
| **Admin** | All tickets           | On all tickets        | ✅ Yes     | ✅ Yes         |

### Detailed Permissions Matrix

| Action                | User         | Agent         | Admin |
| --------------------- | ------------ | ------------- | ----- |
| Create ticket         | ✅           | ✅            | ✅    |
| View own tickets      | ✅           | ✅            | ✅    |
| View all tickets      | ❌           | ❌            | ✅    |
| View assigned tickets | ❌           | ✅            | ✅    |
| Update ticket (basic) | ✅           | ✅            | ✅    |
| Update ticket status  | ❌           | ✅            | ✅    |
| Assign tickets        | ❌           | ✅            | ✅    |
| Delete ticket         | ✅ (own)     | ❌            | ✅    |
| Add comments          | ✅ (own)     | ✅ (assigned) | ✅    |
| Edit comments         | ✅ (own, 1h) | ✅ (own, 1h)  | ✅    |
| Delete comments       | ✅ (own)     | ✅ (own)      | ✅    |
| Upload attachments    | ✅ (own)     | ✅ (assigned) | ✅    |
| View statistics       | Basic        | Agent stats   | Full  |

## Business Logic

### Ticket Status Workflow

```
Open
  ↓
In Progress ←→ Resolved
  ↓            ↓
Closed ←───────┘
```

**Valid transitions:**

-   `open` → `in_progress`, `closed`
-   `in_progress` → `resolved`, `closed`
-   `resolved` → `closed`
-   `closed` → (no transitions)

### Auto-Assignment Algorithm

```php
User::where('role', 'agent')
    ->withCount(['assignedTickets' => function ($query) {
        $query->whereIn('status', ['open', 'in_progress']);
    }])
    ->orderBy('assigned_tickets_count')
    ->first();
```

### Comment Editing Rules

-   Users can edit their own comments within **1 hour** of creation
-   Comments cannot be edited on **closed tickets**
-   Admin can delete any comment

### File Upload Restrictions

-   **Max file size**: 10MB
-   **Allowed types**: Images, PDFs, Documents, Archives
-   **Storage**: Organized by year/month in `storage/app/attachments/`

## API Documentation

### Postman collection

just import the postman collection to the postman

## Contributing

1. Fork the repository
2. Create feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit changes (`git commit -m 'Add AmazingFeature'`)
4. Push to branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## License

This project is licensed under the MIT License - see the [LICENSE.md](LICENSE.md) file for details.

---

**Built with ❤️ using Laravel** - A production-ready ticketing system that demonstrates modern PHP development practices, comprehensive testing, and scalable architecture.
