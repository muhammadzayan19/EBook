# 📚 Online E-Book System  
A full-stack PHP + MySQL web application that allows publishers to sell and distribute books online, manage competitions, and engage readers with interactive features.

---

## 🧾 Table of Contents
1. [Overview](#overview)  
2. [Core Features](#core-features)  
3. [System Architecture](#system-architecture)  
4. [Folder Structure](#folder-structure)  
5. [Database Schema](#database-schema)  
6. [Installation Guide](#installation-guide)  
7. [Default Admin Credentials](#default-admin-credentials)  
8. [Development Workflow](#development-workflow)  
9. [API Endpoints / Page Map](#api-endpoints--page-map)  
10. [Security & Best Practices](#security--best-practices)  
11. [Future Enhancements](#future-enhancements)  
12. [Author & License](#author--license)

---

## 🧩 Overview
**Goal:** Digitize the publisher’s workflow so customers can:
- Register & purchase e-books directly.
- Download PDFs instantly after payment.
- Join essay/story competitions and upload entries.
- View winners, prizes, and upcoming contests.

**Admin Portal:** Manages books, orders, payments, and competitions from one dashboard.

---

## 🎯 Core Features

### 👥 User Side
- Registration & Login (with hashed passwords).  
- Browse books with category, author, and search filters.  
- Purchase books (PDF / CD / Hard Copy).  
- Download PDF after payment verification.  
- Join timed essay/story competitions (3-hour countdown).  
- Upload entries (.doc/.pdf).  
- View winners & competition results.

### 🧑‍💼 Admin Side
- Secure admin authentication.  
- CRUD operations on books, users, orders, competitions.  
- Upload book PDFs.  
- Track orders & payment status.  
- Manage competitions & declare winners.  
- Display notices and home-page announcements.

---

## 🏗️ System Architecture
| Layer | Components | Description |
|-------|-------------|-------------|
| **Presentation** | HTML / CSS / Bootstrap / JS | User interface |
| **Application** | PHP controllers (`user`, `admin`) | Handles logic & validation |
| **Data Access** | `config/db.php`, `includes/functions.php` | MySQL connection and helpers |
| **Storage** | MySQL (DB: `online_ebook_db`) | Persistent data storage |
| **Uploads** | `/uploads/books`, `/uploads/essays` | File storage for PDFs and entries |

---

## 🗂 Folder Structure

```text
EBOOK/
│
├── admin/
│   ├── get_user_details.php         # AJAX endpoint: fetch user details
│   ├── index.php                    # Admin dashboard
│   ├── login.php                    # Admin login page
│   ├── manage_books.php             # CRUD for books
│   ├── manage_competitions.php      # Add/edit competitions
│   ├── manage_orders.php            # Manage orders & payments
│   ├── manage_staff.php             # Manage staff (users with admin/staff roles)
│   ├── manage_subscription.php      # Manage subscription plans & subscribers
│   ├── manage_users.php             # View/edit users
│   ├── payments.php                 # View/manage payments
│   ├── save_settings.php            # Save admin settings
│   ├── settings.php                 # Admin settings UI
│   ├── winners.php                  # Declare & view competition winners
│   └── logout.php                   # Admin logout
│
├── assets/
│   ├── css/                         # Stylesheets
│   ├── js/                          # JavaScript
│   └── images/                      # Images and icons
│
├── config/
│   └── db.php                       # Database connection
│
├── includes/
│   ├── admin_header.php             # Admin header
│   ├── admin_sidebar.php            # Admin sidebar
│   ├── admin_footer.php             # Admin footer
│   ├── DocConverter.php             # Document conversion helper
│   ├── header.php                   # Public site header
│   ├── navbar.php                   # Public site navbar
│   ├── footer.php                   # Public site footer
│   ├── functions.php                # Reusable functions/helpers
│   ├── handle_contact.php           # Contact form handler
│   ├── handle_review.php            # Review submission handler
│   ├── handle_submission.php        # Essay/submission handler
│   └── subscription_helper.php      # Subscription related helpers
│
├── legal/
│   ├── privacy.php                  # Privacy policy
│   └── terms.php                    # Terms of service
│
├── uploads/
│   ├── book_covers/                 # Uploaded book covers
│   ├── books/                       # Uploaded eBook files (PDF/EPUB)
│   ├── essays/                      # Uploaded user essays
│   └── submissions/                 # Generic uploaded submissions
│
├── user/
│   ├── book_details.php             # Single book info
│   ├── books.php                    # Browse all books
│   ├── competition.php              # Competition landing page
│   ├── login.php                    # User login
│   ├── manage_subscription.php      # User subscription management
│   ├── my_books.php                 # User's purchased books
│   ├── my_orders.php                # User orders
│   ├── my_submissions.php           # User essay submissions
│   ├── order.php                    # Place an order
│   ├── payment_process.php          # Payment processing
│   ├── profile.php                  # User profile dashboard
│   ├── register.php                 # User registration
│   ├── subscription_process.php     # Subscription payment/processing
│   ├── subscription.php             # Subscription page
│   ├── update_payment.php           # Update payment details
│   ├── upload_essay.php             # Essay upload page
│   ├── writeessay.php               # Essay writing page
│   └── logout.php                   # User logout
│
├── about.php                        # About the publisher
├── contact.php                      # Contact information
├── db_setup.php                     # Script to create DB & tables
├── index.php                        # Home page
└── README.md
```

---

## 🧠 Database Schema
### 1️⃣ `users`
| Field | Type | Key | Description |
|-------|------|-----|-------------|
| user_id | INT | PK | Unique ID |
| full_name | VARCHAR(100) |  | User’s full name |
| email | VARCHAR(100) | UNIQUE | Login email |
| password | VARCHAR(255) |  | Hashed password |
| address | TEXT |  | Delivery address |
| phone | VARCHAR(15) |  | Contact number |
| registered_at | DATETIME |  | Timestamp of registration |

---

### 2️⃣ `admins`
| Field | Type | Key | Description |
|-------|------|-----|-------------|
| admin_id | INT | PK | Unique admin ID |
| username | VARCHAR(100) | UNIQUE | Admin username |
| password | VARCHAR(255) |  | Hashed password |

---

### 3️⃣ `books`
| Field | Type | Key | Description |
|-------|------|-----|-------------|
| book_id | INT | PK | Unique ID |
| title | VARCHAR(255) |  | Book title |
| author | VARCHAR(255) |  | Book author |
| category | VARCHAR(100) |  | Genre/category |
| description | TEXT |  | Book details |
| price | DECIMAL(10,2) |  | Purchase price |
| subscription_price | DECIMAL(10,2) |  | Subscription price |
| type | ENUM('pdf','cd','hardcopy') |  | Book format |
| file_path | VARCHAR(255) |  | Path to uploaded file |
| image_path | VARCHAR(255) |  | Path to uploaded book image |
| stock | INT |  | Quantity in stock |
| is_free | TINYINT(1) |  | 1 if free, else 0 |
| created_at | DATETIME |  | Created timestamp |

---

### 4️⃣ `orders`
| Field | Type | Key | Description |
|-------|------|-----|-------------|
| order_id | INT | PK | Unique ID |
| user_id | INT | FK | Linked to users.user_id |
| book_id | INT | FK | Linked to books.book_id |
| quantity | INT |  | Number of copies |
| order_type | VARCHAR(20) |  | Type of order |
| total_amount | DECIMAL(10,2) |  | Total cost |
| status | ENUM('pending','paid') |  | Order status |
| order_date | DATETIME |  | Timestamp |

---

### 5️⃣ `payments`
| Field | Type | Key | Description |
|-------|------|-----|-------------|
| payment_id | INT | PK | Unique ID |
| order_id | INT | FK | Linked to orders.order_id |
| payment_method | VARCHAR(50) |  | e.g., card, PayPal |
| amount | DECIMAL(10,2) |  | Amount paid |
| payment_status | ENUM('pending','completed') |  | Payment state |
| payment_date | DATETIME |  | Timestamp |

---

### 6️⃣ `competitions`
| Field | Type | Key | Description |
|-------|------|-----|-------------|
| comp_id | INT | PK | Unique ID |
| title | VARCHAR(255) |  | Competition title |
| type | ENUM('essay','story') |  | Type of contest |
| topic | TEXT |  | Topic description |
| start_date | DATETIME |  | Start date |
| end_date | DATETIME |  | End date |
| prize | VARCHAR(255) |  | Reward/prize |
| status | ENUM('active','closed') |  | Competition status |

---

### 7️⃣ `submissions`
| Field | Type | Key | Description |
|-------|------|-----|-------------|
| submission_id | INT | PK | Unique ID |
| comp_id | INT | FK | Linked to competitions.comp_id |
| user_id | INT | FK | Linked to users.user_id |
| file_path | VARCHAR(255) |  | Uploaded essay path |
| submitted_at | DATETIME |  | Submission timestamp |

---

### 8️⃣ `winners`
| Field | Type | Key | Description |
|-------|------|-----|-------------|
| winner_id | INT | PK | Unique ID |
| comp_id | INT | FK | Linked to competitions.comp_id |
| user_id | INT | FK | Linked to users.user_id |
| position | VARCHAR(50) |  | e.g., 1st, 2nd, 3rd |
| prize | VARCHAR(255) |  | Award received |
| remarks | VARCHAR(255) |  | Comment on Winner |
| announced_at | DATETIME |  | Announcement date |

---

> 🧩 **Note:** All foreign keys use `ON DELETE CASCADE` for relational integrity.

---

## 🚀 Installation Guide

### 1️⃣ Requirements
- XAMPP / Laragon / WAMP  
- PHP 8.0 or higher  
- MySQL 5.7 or higher  
- Web browser (Chrome, Edge, etc.)

### 2️⃣ Setup Steps
1. Copy the folder to your web root (`htdocs/online_ebook_system`).  
2. Start **Apache** and **MySQL**.  
3. Visit [`http://localhost/online_ebook_system/setup_database.php`](http://localhost/online_ebook_system/setup_database.php).  
   - This automatically creates the database `online_ebook_db` and all tables.  
   - A default admin account is also created.  
4. Once “🎉 Setup completed successfully” appears, delete `setup_database.php` for security.  
5. Access:  
   - **User Portal:** [`http://localhost/online_ebook_system/user/`](http://localhost/online_ebook_system/user/)  
   - **Admin Portal:** [`http://localhost/online_ebook_system/admin/`](http://localhost/online_ebook_system/admin/)  

---

## 🔑 Default Admin Credentials
| Field | Value |
|-------|--------|
| Username | `admin` |
| Password | `admin123` |

Change immediately after first login.

---

## 🧭 Development Workflow
1. **Frontend Pages** – design in HTML + Bootstrap.  
2. **PHP Controllers** – process forms and queries.  
3. **Database Layer** – use `mysqli` or `PDO` prepared statements.  
4. **Authentication** – password hashing + session tokens.  
5. **Admin Dashboard** – manage data using CRUD interfaces.  

---

## 🔗 API Endpoints / Page Map
| Page | Description |
|------|-------------|
| `/user/register.php` | New user registration |
| `/user/login.php` | User login |
| `/user/books.php` | Book listing page |
| `/user/book_details.php?id=` | View specific book |
| `/user/order.php` | Place book order |
| `/user/competition.php` | Join competition |
| `/user/upload_essay.php` | Upload entry (3 hour timer) |
| `/admin/login.php` | Admin login |
| `/admin/manage_books.php` | Add/Edit books |
| `/admin/manage_competitions.php` | Manage competitions |
| `/admin/winners.php` | Publish winners |

---

## 🛡️ Security & Best Practices
- Use `password_hash()` / `password_verify()` for all passwords.  
- Sanitize input via `mysqli_real_escape_string()` or prepared statements.  
- Restrict uploads to safe file types (PDF/DOCX).  
- Use sessions for authentication.  
- Delete `setup_database.php` after installation.  
- Validate timer logic server-side for competitions.

---

## 💡 Future Enhancements
- Payment Gateway Integration (Stripe / PayPal).  
- Book rating & review system.  
- Subscription auto-renewal.  
- Email verification & password reset.  
- REST API for mobile apps.  
- Admin analytics dashboard (Charts.js).

---

## 👨‍💻 Author & License
**Project By:** Zayan (Prime Creators)  
**Language:** PHP 8 + MySQL  
**License:** MIT – Free for educational and personal use.  

> _“Knowledge shared is knowledge multiplied.”_
