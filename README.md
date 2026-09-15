# HelpDesk Pro — Enterprise Support Portal

[![PHP]
[![MySQL]
[![Bootstrap]
]

An enterprise-grade, feature-rich **Helpdesk & Support Portal** built with PHP, MySQL, and modern JavaScript. Designed for high performance, maximum flexibility, and security, HelpDesk Pro offers seamless ticket lifecycle management, multi-tenant company support, interactive analytics, and a dynamic 2-tier (Role & User-Wise) permission engine.

---

## 🌟 Key Features & Functionalities

### 🎫 1. Advanced Ticket Management
- **Full Ticket Lifecycle**: Track tickets through statuses: `Open`, `Assigned`, `In Progress`, `Waiting for Client`, `Resolved`, and `Closed`.
- **Priority & Categorization**: Prioritize issues (`Low`, `Medium`, `High`, `Critical`) and organize by custom categories (Technical Support, Billing, Bug Reports, etc.).
- **Threaded Conversations**: Real-time ticket discussion threads supporting both public client replies and private internal agent notes.
- **Attachment Support**: Upload images, PDFs, and documents with file size and MIME-type validation.
- **SLA & Due Date Tracking**: Assign due dates and track resolution timelines with automated badge highlights.

### 🔐 2. Dynamic 2-Tier Permission System
- **Role-Wise Permission Matrix**: Configure baseline permission policies for predefined roles (`Super Admin`, `Employee`, `Client`).
- **User-Wise Permission Overrides**: Granular 3-way toggle per user:
  - `Inherit`: Follow baseline role permission.
  - `Allow`: Force explicit permission grant for a specific user.
  - `Deny`: Force explicit permission revocation for a specific user.
- **Real-Time Policy Enforcement**: Instant authorization check via `has_permission('module.action')` across all routes and UI controls.

### 🏢 3. Multi-Tenant & Company Management
- **Company Grouping**: Assign client users to target companies with automated domain & ticket isolation.
- **Smart Form Behavior**: Automatic selection and hiding of company fields for internal portal staff (`Super Admin` & `Employee`).
- **Company Insights**: View dedicated ticket history, active user counts, and company performance statistics.

### 👤 4. User & Account Management
- **Role-Based Access**: Pre-configured user roles with avatar uploads, account status toggling (`Active`/`Inactive`), and login timestamps.
- **Security Protections**: bcrypt password hashing, login attempt throttling, session security, and CSRF token verification.

### 📊 5. Analytics, Reporting & Exports
- **Interactive Dashboards**: Real-time KPI stat cards, ticket status distribution dough-nut charts, and 7-day volume trends powered by Chart.js.
- **Employee Leaderboards**: Track resolution velocity and ticket resolution rates per support agent.
- **Export Engine**: Export custom ticket reports directly to **PDF** or **Excel (.xlsx/.csv)** with filtered date ranges.

### 🔔 6. Notifications & Audit Logging
- **In-App Notifications**: Real-time topbar notification bell with unread counters and batch mark-as-read actions.
- **Activity Log Audit**: Comprehensive event logging tracking user actions, entity modifications, IP addresses, and timestamps.

### 🔌 7. RESTful API v1
- **Bearer Token Authentication**: Secure token generation and revocation.
- **API Endpoints**: Full CRUD support for tickets, users, companies, and summary analytics.

---

## 🛠️ Technology Stack

| Layer | Technology |
| :--- | :--- |
| **Backend Core** | PHP 8.1+ (Custom MVC Architecture, PDO Database Wrapper) |
| **Database** | MySQL 5.7+ / 8.0+ / MariaDB 10.4+ |
| **Frontend Layout** | HTML5, Vanilla CSS3 (CSS Variables, Flexbox/Grid), Bootstrap 5.3 |
| **Interactivity** | Vanilla JavaScript (ES6+ AJAX), Chart.js |
| **Iconography** | Bootstrap Icons v1.11 |

---

## 🚀 Quick Setup & Installation

### 1. Prerequisites
Ensure your local development environment has:
- PHP 8.1 or higher
- MySQL 5.7+ / 8.0+
- Composer installed

### 2. Clone & Install Dependencies
```bash
git clone https://github.com/Mohammad-Bilal554/support-portal.git
cd support-portal
composer install
```

### 3. Environment Configuration
Copy the example environment file and configure your database and app credentials:
```bash
cp .env.example .env
```
Edit `.env`:
```ini
APP_NAME="Support Portal"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost/support-portal/public

DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=support_portal
DB_USERNAME=root
DB_PASSWORD=
```

### 4. Database Initialization
Create the MySQL database and import the schema:
```bash
mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS support_portal CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u root -p support_portal < database/schema.sql
```

*(Optional)* Seed sample data for testing:
```bash
php database/seeds/DatabaseSeeder.php
```

### 5. Web Server Configuration

#### Apache
Ensure `mod_rewrite` is enabled. Point `DocumentRoot` to the `/public` directory.

#### Nginx
```nginx
server {
    listen 80;
    server_name support.local;
    root /path/to/support-portal/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.1-fpm.sock;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }
}
```

---

## 🔑 Default Login Credentials

| Role | Email | Password | Access Level |
| :--- | :--- | :--- | :--- |
| **Super Admin** | `admin@support-portal.com` | `Admin@12345` | Full Unrestricted Access |
| **Employee** | `john.smith@support.com` | `Employee@123` | Ticket Management & Reports |
| **Client** | `mike.johnson@acme.com` | `Client@123` | Client Support Portal |

---

## 📂 Project Directory Structure

```
support-portal/
├── app/
│   ├── Controllers/       # Admin, Auth, Ticket & API Controllers
│   ├── Core/              # Application Core (Router, Database, Session, CSRF, Validator)
│   ├── Middleware/        # Auth, Role & Permission Middleware
│   ├── Models/            # User, Ticket, Company, Permission, Setting Models
│   └── Services/          # UserService, TicketService, CompanyService, ReportService
├── config/                # Routes & App Configurations
├── database/              # Schema definition & Seeder scripts
├── public/                # Public Document Root (index.php, CSS, JS, Uploads)
├── resources/
│   ├── views/             # PHP View Templates (Admin, Auth, Tickets, Reports, Layouts)
│   └── emails/            # Email notification templates
├── storage/               # Application logs & generated exports
└── vendor/                # Composer dependencies
```

---

## 🛡️ Security Features

- **XSS Prevention**: Automatic HTML entity escaping on output.
- **SQL Injection Prevention**: 100% prepared statements via PDO.
- **CSRF Tokens**: Form-level token validation for all state-changing requests.
- **Password Security**: Strong `PASSWORD_BCRYPT` hashing algorithm.
- **Access Control**: Strict 2-tier role and user-wise permission gating on all sensitive endpoints.

---

## 📝 License

This project is open-source software licensed under the [MIT License](LICENSE).
