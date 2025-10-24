# Mazvikadei Resort Booking System (Frontend + PHP/MySQL templates)

This package is a working **frontend** and **PHP/MySQL backend template** for "Mazvikadei Resort".
It includes pages for rooms, activities, events, bookings, contact, an admin panel, and example PHP endpoints + SQL to set up the database.

**How to use (quick):**
1. Unzip and copy the folder into your web server document root (e.g. `htdocs` for XAMPP).
2. Import `sql/setup.sql` into MySQL to create the `mazvikadei` database and seed data.
3. Update DB credentials in `php/config.php`.
4. Ensure `uploads/` is writable by the web server (for event special requests uploads).
5. Visit the site (e.g. `http://localhost/mazvikadei_resort_system/`) and test bookings.
6. Use `admin/login.php` to access the admin area (default seeded admin: `admin@mazvikadei.local` / password: `Admin@123` - change immediately).

**Notes:**
- This package focuses on a safe starting point: PHP uses PDO and prepared statements.
- Email notifications use PHP `mail()` placeholders — configure SMTP or use libraries like PHPMailer for production.
- Payment gateways are placeholders; integrate Paynow/EcoCash/Stripe/PayPal separately.
