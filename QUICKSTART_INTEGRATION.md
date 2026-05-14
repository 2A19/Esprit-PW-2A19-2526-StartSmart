# 🚀 Quick Start - Integrated StartSmart

## 1️⃣ Start Your Server
- Open **XAMPP Control Panel**
- Click **Start** on Apache
- Click **Start** on MySQL
- Wait for them to show "Running" (green)

## 2️⃣ Test Integration
Open this in your browser:
```
http://localhost/StartSmartIntegrated/test-integration.php
```

If you see green checkmarks ✅ - you're good to go!

## 3️⃣ Access the Application

### Login Page
```
http://localhost/StartSmartIntegrated/login.php
```

**Test Credentials:**
- `admin@startsmart.com` / `admin123`
- `ahmed@email.com` / `user123`
- `contact@techtunisia.tn` / `startup123`

### Home Page  
```
http://localhost/StartSmartIntegrated/
```

After logging in, you should see:
- Projects catalog
- Forum posts
- User profiles
- Admin panels (if you're admin)

## 4️⃣ Key Features

✅ **Login System** - Works locally (no proxy needed)
✅ **Projects** - Create, edit, delete projects
✅ **Forum** - Create and comment on posts  
✅ **Reactions** - Like/dislike projects and posts
✅ **User Profiles** - View user stats and activity
✅ **Admin Panels** - Manage projects and posts (if admin)

## 5️⃣ Database Info

**Database Name:** `startsmart_db`
**Host:** `localhost`
**User:** `root`
**Password:** (empty)

To view/manage database:
- Use **phpMyAdmin** (usually at `http://localhost/phpmyadmin`)
- Or your MySQL client

## 🔍 Troubleshooting

### Login page won't load
- Check Apache is running
- Verify URL is correct (with spaces encoded as %20)

### Login fails
- Check user exists in `startsmart_db.users`
- Verify password is correct
- Check password hash in database

### Projects/posts don't show
- Check database connection in test page
- Ensure `startsmart_db` database exists
- Check required tables (projets, post, users, etc.)

### XAMPP issues
- Restart XAMPP completely
- Check Apache/MySQL logs in XAMPP Control Panel
- Verify port 3306 (MySQL) and 80 (Apache) are free

## 📞 Key Endpoints

| URL | Purpose |
|-----|---------|
| `/login.php` | User login |
| `/index.php` | Home page |
| `/index.php?controller=projet&action=index` | Projects |
| `/index.php?controller=post&action=index` | Forum |
| `/index.php?controller=profile&action=index` | User profile |
| `/api/auth/login` | Login API (POST) |
| `/api/auth/me` | Current user (GET) |
| `/test-integration.php` | Test page |

## 💡 Tips

- **Cleaner URL:** Bookmark `http://localhost/StartSmartIntegrated/` as home
- **Browser Storage:** App uses localStorage for some settings
- **Console:** Press F12 to see any JavaScript errors
- **Session:** Session lasts until you logout or browser closes

---

**That's it!** Your integrated StartSmart application is ready to use. 🎉
