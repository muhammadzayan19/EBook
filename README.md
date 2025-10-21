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
EBOOK/
│
├── config/
│ └── db.php # Database connection
│
├── includes/
│ ├── header.php # Navigation / layout header
│ ├── footer.php # Footer template
│ └── functions.php # Common helper functions
│
├── admin/
│ ├── login.php # Admin login page
│ ├── index.php # Admin dashboard
│ ├── manage_books.php # CRUD for books
│ ├── manage_users.php # View/edit users
│ ├── manage_orders.php # Manage orders & payments
│ ├── manage_competitions.php # Add/edit competitions
│ ├── winners.php # Declare & view winners
│ └── logout.php
│
├── user/
│ ├── register.php # User registration
│ ├── login.php # User login
│ ├── profile.php # Profile dashboard
│ ├── books.php # Browse all books
│ ├── book_details.php # Single book info
│ ├── order.php # Order placement
│ ├── competition.php # Competition landing
│ ├── upload_essay.php # Essay upload page
│ └── logout.php
│
├── uploads/
│ ├── books/ # Uploaded PDF files
│ └── essays/ # Uploaded user essays
│
├── assets/
│ ├── css/
│ ├── js/
│ └── images/
│
├── setup_database.php # Auto-creates DB & tables
├── index.php # Home page
├── about.php # About the publisher
├── contact.php # Contact information
└── README.md


---

## 🧠 Database Schema

### 1️⃣ `users`
| Field | Type | Key | Description |
|-------|------|-----|-------------|
| user_id | INT | PK | Unique ID |
| full_name | VARCHAR(100) | | |
| email | VARCHAR(100) | UNIQUE | Login email |
| password | VARCHAR(255) | | Hashed password |
| address | TEXT | | Delivery address |
| phone | VARCHAR(15) | | |
| registered_at | DATETIME | | Timestamp |

### 2️⃣ `admins`
| admin_id | username | password |

### 3️⃣ `books`
| book_id | title | author | category | description | price | subscription_price | type (pdf/cd/hardcopy) | file_path | stock | is_free | created_at |

### 4️⃣ `orders`
| order_id | user_id FK | book_id FK | quantity | order_type | total_amount | status (pending/paid) | order_date |

### 5️⃣ `payments`
| payment_id | order_id FK | payment_method | amount | payment_status | payment_date |

### 6️⃣ `competitions`
| comp_id | title | type (essay/story) | topic | start_date | end_date | prize | status |

### 7️⃣ `submissions`
| submission_id | comp_id FK | user_id FK | file_path | submitted_at |

### 8️⃣ `winners`
| winner_id | comp_id FK | user_id FK | position | prize | announced_at |

> All foreign keys use **ON DELETE CASCADE** for relational integrity.

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
