# 📋 Employee Task Manager

> A modern, mobile-first task management web application built with **PHP**, **MySQL**, and **Vanilla JavaScript**. Designed to feel like a native mobile app with smooth animations, touch-optimized UI, and smart notifications.

![Version](https://img.shields.io/badge/version-1.0.0-blue)
![PHP](https://img.shields.io/badge/PHP-7.4%2B-777BB4?logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-5.7%2B-4479A1?logo=mysql&logoColor=white)
![License](https://img.shields.io/badge/license-MIT-green)

---

## 📖 Overview

**Employee Task Manager** is a lightweight, full-stack web application that helps organizations manage employees and their daily tasks efficiently. It features two distinct dashboards:

- **Admin Dashboard** — Manage employees, assign tasks, and track progress
- **Employee Dashboard** — View assigned tasks and update their status in real-time

The application is fully responsive and optimized for touch-screen devices, delivering a **native app-like experience** directly in the browser.

---

## ✨ Features

### 🔐 Authentication & Security
- Secure login system with **bcrypt password hashing** (`$2y$10$...`)
- Session-based authentication with role-based access control
- **Cross-table mobile uniqueness** enforced via MySQL triggers (mobile cannot exist in both `users` and `admin` tables)
- SQL injection prevention using **prepared statements**

### 👥 Admin Features
- **User Management** — Add, edit, and remove employees
- **Task Management** — Create, edit, delete, and assign tasks to any employee
- **Real-time Search** — Instantly filter users and tasks by name, role, mobile, or status
- **Task Count Tracking** — See how many tasks are assigned to each employee
- **Smart Notifications** — Toast messages and custom confirmation modals
- **Cascade Deletion** — Removing a user automatically removes all their tasks

### 👤 Employee Features
- View all assigned tasks in a clean card-based layout
- Update task status with one tap (**Pending** → **Started** → **Done**)
- Instant UI updates without page reloads (AJAX-powered)
- Personalized greeting header with user's name

### 📱 Mobile-First Design
- Touch-optimized interface with large tap targets
- Native app-like **bottom navigation bar**
- Smooth **bottom sheet modals** for forms
- iOS/Android home screen installation support (PWA-ready meta tags)
- Smooth momentum scrolling and tap feedback animations
- Safe area support for modern devices (iPhone notch, etc.)

---

## 🛠️ Tech Stack

| Layer | Technology |
|-------|-----------|
| **Backend** | PHP 7.4+ (Native, no frameworks) |
| **Database** | MySQL 5.7+ with Triggers & Foreign Keys |
| **Frontend** | HTML5, CSS3, Vanilla JavaScript (ES6+) |
| **Styling** | Custom CSS with CSS Variables |
| **Server** | Apache (XAMPP compatible) |
| **API** | RESTful JSON endpoints via AJAX (Fetch API) |

---

## 🗄️ Database Structure

### Tables

```
┌─────────────────┐       ┌─────────────────┐       ┌─────────────────┐
│     admin       │       │     users       │       │      task       │
├─────────────────┤       ├─────────────────┤       ├─────────────────┤
│ id (PK)         │       │ id (PK)         │◄──────│ user_id (FK)    │
│ mobile (unique) │       │ name            │       │ id (PK)         │
│ password (hash) │       │ role            │       │ name            │
│ created_at      │       │ mobile (unique) │       │ details         │
└─────────────────┘       │ password (hash) │       │ start_time      │
                          │ created_at      │       │ end_time        │
                          └─────────────────┘       │ status (ENUM)   │
                                                    │ created_at      │
                                                    └─────────────────┘
```

### Key Constraints
- `ON DELETE CASCADE` — Deleting a user removes all their tasks
- `ENUM('pending', 'started', 'done')` — Status defaults to `pending`
- `TIME` columns — Tasks store time-only (no dates)
- **4 Triggers** — Enforce mobile uniqueness across both tables

---

## 📦 Installation

### Prerequisites
- [XAMPP](https://www.apachefriends.org/) (or any LAMP/WAMP stack)
- PHP 7.4 or higher
- MySQL 5.7 or higher

### Step-by-Step Setup

1. **Clone the repository**
   ```bash
   git clone https://github.com/YOUR_USERNAME/employee-task-manager.git
   cd employee-task-manager
   ```

2. **Copy to XAMPP**
   ```bash
   # Move to your htdocs folder
   cp -r . /c/xampp/htdocs/task
   ```

3. **Import the Database**
   - Open [phpMyAdmin](http://localhost/phpmyadmin)
   - Click **Import** → Select `database.sql`
   - Or run via MySQL CLI:
     ```bash
     mysql -u root < database.sql
     ```

4. **Configure Database Connection** (if needed)
   Edit `db.php` with your MySQL credentials:
   ```php
   $host = 'localhost';
   $user = 'root';
   $pass = '';
   $dbname = 'tasks';
   ```

5. **Start XAMPP** and open your browser:
   ```
   http://localhost/task/
   ```

---

## 🔑 Default Credentials

All sample accounts use the default password: **`password`**

### 👨‍💼 Admin Accounts
| Mobile | Password |
|--------|----------|
| `1000000001` | `password` |
| `1000000002` | `password` |

### 👤 Employee Accounts
| Mobile | Password | Role |
|--------|----------|------|
| `2000000001` | `password` | Developer |
| `2000000002` | `password` | Designer |
| `2000000003` | `password` | Manager |

---

## 📁 Project Structure

```
task/
├── database.sql          # Database schema + sample data
├── db.php                # Database connection & session handler
├── index.php             # Login page
├── login_process.php     # Authentication handler (JSON API)
├── admin.php             # Admin dashboard (user management)
├── user.php              # Employee dashboard (task view)
├── user_tasks.php        # Task management page per user
├── get_users.php         # API: Fetch all users
├── save_user.php         # API: Add/Edit user
├── delete_user.php       # API: Delete user
├── get_user_tasks.php    # API: Fetch user's tasks
├── save_task.php         # API: Add/Edit task
├── delete_task.php       # API: Delete task
├── update_status.php     # API: Update task status
├── logout.php            # Session destroyer
├── style.css             # Mobile-first UI styles
└── README.md             # This file
```

---

## 🌐 API Endpoints

All API endpoints return JSON responses.

| Endpoint | Method | Description |
|----------|--------|-------------|
| `login_process.php` | POST | Authenticate user |
| `get_users.php` | GET | Fetch all users with task counts |
| `save_user.php` | POST | Create or update a user |
| `delete_user.php` | POST | Delete a user (cascades to tasks) |
| `get_user_tasks.php` | GET | Fetch tasks for a specific user |
| `save_task.php` | POST | Create or update a task |
| `delete_task.php` | POST | Delete a task |
| `update_status.php` | POST | Update task status |

### Example: Login Request
```json
POST /login_process.php
Content-Type: application/x-www-form-urlencoded

mobile=1000000001&password=password
```

### Example Response
```json
{
  "success": true,
  "role": "admin"
}
```

---

## 🎨 UI/UX Highlights

- **Native App Feel** — No page reloads, smooth transitions
- **Smart Toast Notifications** — Auto-dismissing success/error messages
- **Custom Confirmation Modals** — Replaces browser's ugly `confirm()` dialogs
- **Bottom Sheet Forms** — iOS/Android-style slide-up modals
- **Real-time Search** — Instant filtering without server requests
- **Touch Feedback** — Cards scale down on tap for physical feel
- **Responsive Layout** — Capped at 600px on desktop (phone-like preview)

---

## 🔒 Security Features

- ✅ Passwords hashed with `password_hash()` (bcrypt)
- ✅ Login verified with `password_verify()`
- ✅ Prepared statements prevent SQL injection
- ✅ Session-based authentication on all protected pages
- ✅ Role-based access control (admin vs user)
- ✅ Output escaping with `htmlspecialchars()` prevents XSS
- ✅ Database triggers enforce data integrity

---

## 🚀 Future Enhancements

- [ ] Add task deadlines with date support
- [ ] Email/SMS notifications for task assignments
- [ ] Task attachments and file uploads
- [ ] Task comments and activity log
- [ ] Admin analytics dashboard with charts
- [ ] Multi-language support (i18n)
- [ ] Progressive Web App (PWA) with offline support
- [ ] Dark mode toggle

---

## 🤝 Contributing

Contributions, issues, and feature requests are welcome!

1. Fork the project
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

---

## 📄 License

This project is licensed under the **MIT License** — see the [LICENSE](LICENSE) file for details.

---

## 👨‍💻 Author

**Abouelfetouh**  
📧 Email: abouelfetouh.97@hotmail.com 


---

## 🙏 Acknowledgments

- Inspired by modern mobile task apps like **Todoist** and **Trello**
- Icons from [EmojiOne](https://www.emojione.com/)
- Built with ❤️ using vanilla technologies

---

<div align="center">

**⭐ If you found this project useful, consider giving it a star!**

[⬆ Back to Top](#-employee-task-manager)

</div>
