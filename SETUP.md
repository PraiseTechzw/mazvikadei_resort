# Mazvikadei Resort - Complete Setup Guide

## Overview
This is a comprehensive resort management system built with PHP, MySQL, and modern web technologies. The system includes a complete booking system, admin panel, and responsive frontend.

## Features

### 🏨 **Accommodation Management**
- Multiple room types with detailed descriptions
- Real-time availability checking
- Room categories and pricing
- Image galleries and amenities

### 🎯 **Activities & Events**
- Activity booking system
- Event venue management
- Category-based filtering
- Pricing and scheduling

### 📅 **Booking System**
- Multi-step booking process
- Real-time availability
- Payment integration ready
- Email notifications
- Booking confirmations

### 👨‍💼 **Admin Panel**
- Dashboard with statistics
- Room management
- Booking management
- Customer management
- Contact message handling
- Reports and analytics

### 🎨 **Modern Design**
- Responsive design
- Mobile-first approach
- Beautiful UI/UX
- Accessibility features
- Dark mode support

## Installation

### Prerequisites
- XAMPP/WAMP/LAMP server
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Modern web browser

### Step 1: Download and Extract
1. Download the project files
2. Extract to your web server directory (e.g., `htdocs` for XAMPP)
3. Ensure the folder is named `mazvikadei_resort`

### Step 2: Database Setup
1. Open phpMyAdmin or MySQL command line
2. Import the enhanced database schema:
   ```sql
   -- Run the enhanced_setup.sql file
   source sql/enhanced_setup.sql;
   ```

### Step 3: Configuration
1. Update database credentials in `php/config.php`:
   ```php
   define('DB_HOST','127.0.0.1');
   define('DB_NAME','mazvikadei');
   define('DB_USER','root');
   define('DB_PASS','');
   ```

2. Create uploads directory:
   ```bash
   mkdir uploads
   chmod 755 uploads
   ```

### Step 4: Access the Application
1. Start your web server (XAMPP/WAMP)
2. Navigate to: `http://localhost/mazvikadei_resort`
3. Admin access: `http://localhost/mazvikadei_resort/admin/login.php`

### Default Admin Credentials
- **Email:** admin@mazvikadei.local
- **Password:** Admin@123

⚠️ **Important:** Change these credentials immediately after first login!

## File Structure

```
mazvikadei_resort/
├── index.php                 # Homepage
├── rooms.php                 # Room listings
├── activities.php            # Activity listings
├── events.php                # Event listings
├── bookings.php              # Booking system
├── about.php                 # About page
├── contact.php               # Contact page
├── booking_confirmation.php  # Booking confirmation
├── styles.css                # Main stylesheet
├── app.js                    # JavaScript functionality
├── php/
│   ├── config.php            # Database configuration
│   ├── enhanced_book.php     # Enhanced booking API
│   └── api/
│       ├── rooms.php         # Rooms API
│       ├── activities.php    # Activities API
│       ├── events.php        # Events API
│       ├── bookings.php      # Bookings API
│       ├── dashboard.php     # Dashboard API
│       └── contact.php       # Contact API
├── admin/
│   ├── login.php             # Admin login
│   ├── enhanced_dashboard.php # Admin dashboard
│   ├── manage_rooms.php      # Room management
│   └── contact_messages.php  # Message management
├── sql/
│   └── enhanced_setup.sql    # Database schema
└── assets/
    └── [images and media files]
```

## API Endpoints

### Public APIs
- `GET /php/api/rooms.php` - Get rooms with filters
- `GET /php/api/activities.php` - Get activities with filters
- `GET /php/api/events.php` - Get events with filters
- `POST /php/enhanced_book.php` - Create booking
- `POST /php/api/contact.php` - Submit contact form

### Admin APIs
- `GET /php/api/dashboard.php` - Dashboard statistics
- `GET /php/api/bookings.php` - Get bookings
- `PUT /php/api/bookings.php` - Update booking status
- `DELETE /php/api/bookings.php` - Cancel booking

## Database Schema

### Core Tables
- `users` - User accounts (admin, staff, customers)
- `rooms` - Room information and pricing
- `activities` - Activity listings
- `events` - Event venues and packages
- `bookings` - Booking records
- `payments` - Payment tracking
- `contact_messages` - Contact form submissions

### Supporting Tables
- `room_categories` - Room type categories
- `activity_categories` - Activity categories
- `event_categories` - Event categories
- `room_availability` - Room availability calendar
- `reviews` - Customer reviews
- `settings` - System settings

## Customization

### Adding New Room Types
1. Add category to `room_categories` table
2. Add rooms to `rooms` table
3. Update room images in `assets/rooms/`

### Adding New Activities
1. Add category to `activity_categories` table
2. Add activity to `activities` table
3. Update activity images in `assets/activities/`

### Payment Integration
The system is ready for payment gateway integration:
- EcoCash integration
- Paynow integration
- PayPal integration
- Bank transfer processing

### Email Configuration
Update email settings in booking files:
```php
// In php/enhanced_book.php
@mail($to, $subject, $message);
```

For production, use SMTP:
```php
// Use PHPMailer or similar
$mail = new PHPMailer();
// Configure SMTP settings
```

## Security Features

- SQL injection prevention with PDO prepared statements
- XSS protection with htmlspecialchars()
- CSRF protection ready
- Input validation and sanitization
- Secure file upload handling
- Session management

## Performance Optimization

- Database indexing for fast queries
- Optimized images and assets
- Minified CSS and JavaScript
- Caching strategies
- CDN ready

## Browser Support

- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+
- Mobile browsers

## Troubleshooting

### Common Issues

1. **Database Connection Error**
   - Check database credentials in `php/config.php`
   - Ensure MySQL is running
   - Verify database exists

2. **Permission Errors**
   - Set correct permissions on uploads directory
   - Check file ownership

3. **Booking Not Working**
   - Check JavaScript console for errors
   - Verify API endpoints are accessible
   - Check database connection

4. **Admin Login Issues**
   - Verify admin user exists in database
   - Check password hash
   - Clear browser cache

### Debug Mode
Enable debug mode in `php/config.php`:
```php
define('DEBUG', true);
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

## Support

For technical support or questions:
- Email: support@mazvikadei.com
- Phone: +263 77 123 4567
- Documentation: Check this README file

## License

This project is proprietary software. All rights reserved.

## Version History

- **v2.0** - Enhanced version with full functionality
- **v1.0** - Initial release

---

**Mazvikadei Resort Management System**  
*Luxury Accommodation & Activities*
