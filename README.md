# The Autodok - Automotive Care Management System
School project - CS PROFESSIONAL ELECTIVE 1(CSE7)

## 🚗 Overview

The Autodok is a comprehensive web-based management system for automotive care services. It provides tools for managing job orders, staff, reports, and daily operations.

## ✨ New Features - Staff Management Module

The system now includes a complete Staff Management module with:
- ✅ Complete CRUD operations for staff members
- ✅ Advanced search and filtering
- ✅ Role-based access control (Admin, Staff, Technician, Manager)
- ✅ Profile image management
- ✅ Status management (Active/Inactive)
- ✅ Real-time statistics dashboard
- ✅ Responsive design for all devices
- ✅ Secure authentication and authorization

## 📚 Complete Documentation

**New comprehensive documentation available:**
- **[INDEX.md](INDEX.md)** - Complete documentation index
- **[QUICK_START.md](QUICK_START.md)** - Get started in 5 minutes
- **[STAFF_MODULE_README.md](STAFF_MODULE_README.md)** - Complete staff module guide
- **[ARCHITECTURE.md](ARCHITECTURE.md)** - Technical architecture
- **[TESTING_GUIDE.md](TESTING_GUIDE.md)** - 100+ test cases
- **[STAFF_MODULE_SUMMARY.md](STAFF_MODULE_SUMMARY.md)** - Executive summary

---

## The Autodok - Quick Start Guide



## Prerequisites
- XAMPP/WAMP installed
- Web browser

## Step 1: Extract Files 

Extract the project to:
- **XAMPP**: `C:/xampp/htdocs/autodok/`

## Step 2: Create Database 

1. Start Apache and MySQL in XAMPP
2. Open: `http://localhost/phpmyadmin`
3. Click "New" → Database name: `autodok_db`
4. Click "Import" → Choose `database/autodok_db.sql`
5. Click "Go"
6. **NEW:** Import staff module → Choose `install_staff_module.sql`
7. Click "Go"

## Step 3: Configure 

Open `includes/config.php` and verify:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');  // Your MySQL password (usually empty for XAMPP)
define('DB_NAME', 'autodok_db');
define('APP_URL', 'http://localhost/autodok');
```

## Step 4: Access Application 

Open browser and go to:
```
http://localhost/autodok
```

You'll be redirected to the login page.

---

## Step 5: Login 

Use these demo credentials:

**Admin Account:**
- Username: `admin_owwkxi`
- Password: `helloworld!`

**Technician Account:**
- Username: `staff_owwkxi`
- Password: `hellouniverse!`


### Explore Features
1. **Dashboard** - View statistics and income charts
2. **Job Orders** - Create and manage service orders
3. **Staff Management** - ⭐ NEW! Manage staff members (Admin only)
4. **Reports** - Generate income and performance reports

---

## 🎯 Staff Management Quick Tour

### Access Staff Management
1. Login as admin
2. Click "Staff Management" in sidebar
3. View staff list with statistics

### Add New Staff
1. Click "Add New Staff" button
2. Fill in required information
3. Upload profile image (optional)
4. Click "Save Staff"

### Manage Staff
- **View** - Click eye icon to see details
- **Edit** - Click pencil icon to modify
- **Toggle Status** - Click check/x icon to activate/deactivate
- **Delete** - Click trash icon to remove

### Search & Filter
- Search by name, username, email, or staff ID
- Filter by role (Admin, Staff, Technician, Manager)
- Filter by status (Active, Inactive)

---

## 📖 Additional Resources

For complete documentation on the Staff Management module:
- Read [STAFF_MODULE_README.md](STAFF_MODULE_README.md) for detailed guide
- Check [QUICK_START.md](QUICK_START.md) for 5-minute setup
- Review [TESTING_GUIDE.md](TESTING_GUIDE.md) for testing procedures

---

## 🔐 Security Note

⚠️ **Important:** Change all default passwords before deploying to production!

Default passwords are for development/testing only.

---

**The Autodok v1.0.0** - Now with complete Staff Management!  
**Status:** ✅ Production Ready



