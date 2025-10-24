# 🔧 Admin Login Fix Guide - Mazvikadei Resort

## 🚨 **Problem**: Admin login not working

## ✅ **Solution Steps**:

### **Step 1: Run the Admin Fix SQL**
```sql
-- Run this in phpMyAdmin or MySQL command line
source sql/admin_fix.sql;
```

### **Step 2: Use the Admin Setup Page**
1. Go to: `admin/setup.php`
2. Click "Create Admin User"
3. Verify the admin user is created

### **Step 3: Test the Login**
1. Go to: `admin/test_login.php`
2. Test the credentials
3. Verify login works

### **Step 4: Access Admin Dashboard**
1. Go to: `admin/login.php`
2. Login with credentials
3. Access the dashboard

## 📋 **Admin Credentials**:
- **Email**: `admin@mazvikadei.local`
- **Password**: `Admin@123`
- **Role**: `admin`

## 🔍 **Troubleshooting**:

### **If login still fails**:
1. Check database connection in `php/config.php`
2. Verify the `users` table exists
3. Check if the admin user was created
4. Verify password hash is correct

### **Database Check**:
```sql
-- Check if admin user exists
SELECT * FROM users WHERE email = 'admin@mazvikadei.local';

-- Check user table structure
DESCRIBE users;
```

### **Files to Check**:
- `php/config.php` - Database connection
- `admin/login.php` - Login logic
- `admin/enhanced_dashboard.php` - Dashboard access
- `sql/admin_fix.sql` - Admin user creation

## 🎯 **Quick Fix Commands**:

### **Option 1: Use Setup Page**
1. Visit: `http://localhost/mazvikadei_resort/admin/setup.php`
2. Click "Create Admin User"
3. Go to: `http://localhost/mazvikadei_resort/admin/login.php`

### **Option 2: Manual SQL**
```sql
USE mazvikadei;
DELETE FROM users WHERE email = 'admin@mazvikadei.local';
INSERT INTO users (email, password_hash, name, role, status) VALUES
('admin@mazvikadei.local', '$2y$10$UQAJPP.uRQ8a1aORK744Rux9oEdwUZKQqL6z4QcyWDDJGfSfbxK4K', 'Admin', 'admin', 'active');
```

## 🚀 **Expected Results**:
- ✅ Admin user created successfully
- ✅ Login works with correct credentials
- ✅ Dashboard accessible
- ✅ All admin features functional

## 📞 **Support**:
If issues persist, check:
1. Database connection
2. PHP error logs
3. Web server configuration
4. File permissions
