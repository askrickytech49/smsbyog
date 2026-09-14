# Karl Peace Legacy Foundation - PHP Admin Backend Deployment Guide

This directory contains the production-ready PHP backend for the **Karl Peace Legacy Foundation** administrative portal. It requires **zero Firebase setup**, running on standard PHP 7.4+ hosting (cPanel, Apache, Nginx, LiteSpeed, XAMPP, or Docker).

---

## 📁 Architecture Overview

```text
public/
├── admin.php             # Standalone, single-file PHP Admin CMS portal
├── api/
│   ├── config.php        # CORS, authentication token verification & JSON data engine
│   ├── login.php         # Admin credentials authentication (POST)
│   ├── check-auth.php    # Bearer token validation (GET)
│   ├── data.php          # Foundation content CRUD (GET / POST)
│   ├── subscribers.php   # Scholarship alert applicant list & intake (GET / POST)
│   ├── inquiries.php     # Contact messages list & status updates
│   ├── logout.php        # Session & token destruction
│   ├── export.php        # Full JSON database backup download
│   └── .htaccess         # Apache URL rewrite and security rules
└── data/
    └── foundation_data.json # Auto-generated JSON database
```

---

## 🚀 Quick Setup on cPanel / Apache / Shared Hosting

1. **Upload Files**:
   - Upload the contents of the `/dist/` folder (or the whole project) to your website's `public_html` directory via cPanel File Manager or FTP.
   - Ensure the `/data/` folder has **write permissions** (`chmod 755` or `chmod 775`).

2. **Access the Admin Portal**:
   - Navigate to `https://yourdomain.com/admin.php` in your browser.
   - Default credentials:
     - **Email / Username**: `admin@karlpeacelegacy.org`
     - **Password**: `admin`

3. **Connecting the React Frontend to PHP**:
   - In the React Admin Portal, you can also enter your custom PHP API URL (e.g. `https://yourdomain.com/api`) under the **"PHP Backend"** tab.
   - All content modifications made in either the React CMS or `admin.php` immediately sync to `data/foundation_data.json` and reflect on the public website.

---

## 🔒 Security Best Practices for Production

1. **Change the Default Password**:
   - Open `api/config.php` or `admin.php`.
   - Update `DEFAULT_ADMIN_PASS_HASH` with your hashed password, or update `ADMIN_PASS`.
2. **Change the Secret Key**:
   - In `api/config.php`, replace `SECRET_KEY` with a random 64-character string.
3. **Protect the Data Directory**:
   - A `.htaccess` file is placed inside `/data/` to prevent direct browser downloads of the raw JSON file while allowing PHP scripts full read/write access.
