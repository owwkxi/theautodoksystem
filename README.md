# theautodoksystem
School project - CS PROFESSIONAL ELECTIVE 1(CSE7)

## The Autodok - Quick Start Guide



## Prerequisites
- XAMPP/WAMP installed
- Web browser

# Step 1: Extract Files 

Extract the project to:
- **XAMPP**: `C:/xampp/htdocs/autodok/`

## Step 2: Create Database 

1. Start Apache and MySQL in XAMPP
2. Open: `http://localhost/phpmyadmin`
3. Click "New" → Database name: `autodok_db`
4. Click "Import" → Choose `database/autodok_db.sql`
5. Click "Go"

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
3. **Inventory** - Track parts and supplies
4. **Reports** - Generate income and performance reports



