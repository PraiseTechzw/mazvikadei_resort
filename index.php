<?php
session_start();
require_once 'php/config.php';

// Get featured rooms
try {
    $pdo = getPDO();
    $stmt = $pdo->query("SELECT * FROM rooms WHERE status = 'available' ORDER BY price ASC LIMIT 3");
    $featured_rooms = $stmt->fetchAll();
    
    $stmt = $pdo->query("SELECT * FROM activities WHERE status = 'active' ORDER BY price ASC LIMIT 4");
    $featured_activities = $stmt->fetchAll();
    
    $stmt = $pdo->query("SELECT * FROM events WHERE status = 'active' ORDER BY price ASC LIMIT 3");
    $featured_events = $stmt->fetchAll();
} catch (Exception $e) {
    $featured_rooms = [];
    $featured_activities = [];
    $featured_events = [];
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Mazvikadei Resort - Luxury Accommodation & Activities</title>
    <link rel="stylesheet" href="styles.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <style>
        .hero-section {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.85), rgba(118, 75, 162, 0.75)), url('assets/background.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            color: white;
            padding: 8rem 0;
            text-align: center;
            position: relative;
            overflow: hidden;
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        
        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="50" cy="50" r="1" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            opacity: 0.3;
        }
        
        .hero-content {
            position: relative;
            z-index: 2;
            animation: fadeInUp 1s ease-out;
        }
        
        .hero-content h1 {
            font-size: 5rem;
            font-weight: 900;
            margin-bottom: 2rem;
            text-shadow: 3px 3px 6px rgba(0,0,0,0.4);
            background: linear-gradient(45deg, #fff, #f0f9ff, #e0f2fe);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            animation: fadeInUp 1.2s ease-out;
            line-height: 1.1;
        }
        
        .hero-content p {
            font-size: 1.75rem;
            margin-bottom: 3rem;
            opacity: 0.95;
            max-width: 700px;
            margin-left: auto;
            margin-right: auto;
            line-height: 1.7;
            text-shadow: 1px 1px 3px rgba(0,0,0,0.3);
            animation: fadeInUp 1.4s ease-out;
            font-weight: 300;
        }
        
        .hero-subtitle {
            font-size: 1.25rem;
            margin-bottom: 1rem;
            opacity: 0.9;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 2px;
            animation: fadeInUp 1s ease-out;
        }
        
        .hero-buttons {
            display: flex;
            gap: 2rem;
            justify-content: center;
            flex-wrap: wrap;
            animation: fadeInUp 1.6s ease-out;
        }
        
        .hero-btn {
            padding: 1.25rem 2.5rem;
            border-radius: 50px;
            font-size: 1.125rem;
            font-weight: 700;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            box-shadow: 0 8px 25px rgba(0,0,0,0.2);
        }
        
        .hero-btn-primary {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: 2px solid transparent;
        }
        
        .hero-btn-primary:hover {
            transform: translateY(-3px) scale(1.05);
            box-shadow: 0 15px 35px rgba(102, 126, 234, 0.4);
            color: white;
        }
        
        .hero-btn-secondary {
            background: rgba(255, 255, 255, 0.15);
            color: white;
            border: 2px solid rgba(255, 255, 255, 0.3);
            backdrop-filter: blur(10px);
        }
        
        .hero-btn-secondary:hover {
            background: rgba(255, 255, 255, 0.25);
            border-color: rgba(255, 255, 255, 0.5);
            transform: translateY(-3px) scale(1.05);
            box-shadow: 0 15px 35px rgba(255, 255, 255, 0.2);
            color: white;
        }
        
        .hero-btn-success {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            border: 2px solid transparent;
        }
        
        .hero-btn-success:hover {
            transform: translateY(-3px) scale(1.05);
            box-shadow: 0 15px 35px rgba(16, 185, 129, 0.4);
            color: white;
        }
        
        .hero-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.6s;
        }
        
        .hero-btn:hover::before {
            left: 100%;
        }
        
        .floating-elements {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 1;
        }
        
        .floating-element {
            position: absolute;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            animation: float 8s ease-in-out infinite;
            backdrop-filter: blur(1px);
        }
        
        .floating-element:nth-child(1) {
            width: 100px;
            height: 100px;
            top: 15%;
            left: 8%;
            animation-delay: 0s;
            background: linear-gradient(135deg, rgba(255,255,255,0.15), rgba(255,255,255,0.05));
        }
        
        .floating-element:nth-child(2) {
            width: 150px;
            height: 150px;
            top: 55%;
            right: 8%;
            animation-delay: 3s;
            background: linear-gradient(135deg, rgba(255,255,255,0.1), rgba(255,255,255,0.03));
        }
        
        .floating-element:nth-child(3) {
            width: 80px;
            height: 80px;
            bottom: 25%;
            left: 15%;
            animation-delay: 6s;
            background: linear-gradient(135deg, rgba(255,255,255,0.12), rgba(255,255,255,0.04));
        }
        
        .floating-element:nth-child(4) {
            width: 60px;
            height: 60px;
            top: 30%;
            right: 25%;
            animation-delay: 2s;
            background: linear-gradient(135deg, rgba(255,255,255,0.08), rgba(255,255,255,0.02));
        }
        
        .floating-element:nth-child(5) {
            width: 120px;
            height: 120px;
            bottom: 40%;
            right: 15%;
            animation-delay: 4s;
            background: linear-gradient(135deg, rgba(255,255,255,0.06), rgba(255,255,255,0.01));
        }
        
        /* Statistics Section */
        .stats-section {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-top: 1px solid rgba(255, 255, 255, 0.2);
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            padding: 3rem 0;
            margin-top: -2rem;
            position: relative;
            z-index: 3;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 2rem;
            text-align: center;
        }
        
        .stat-item {
            color: white;
            animation: fadeInUp 1.8s ease-out;
        }
        
        .stat-number {
            font-size: 3rem;
            font-weight: 900;
            margin-bottom: 0.5rem;
            background: linear-gradient(45deg, #fff, #f0f9ff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        
        .stat-label {
            font-size: 1.125rem;
            opacity: 0.9;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .stat-icon {
            font-size: 2rem;
            margin-bottom: 1rem;
            opacity: 0.8;
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .hero-content h1 {
                font-size: 3.5rem;
            }
            
            .hero-content p {
                font-size: 1.25rem;
            }
            
            .hero-buttons {
                flex-direction: column;
                align-items: center;
            }
            
            .hero-btn {
                width: 100%;
                max-width: 300px;
            }
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 1.5rem;
            }
            
            .stat-number {
                font-size: 2.5rem;
            }
        }
        
        @media (max-width: 480px) {
            .hero-content h1 {
                font-size: 2.5rem;
            }
            
            .hero-content p {
                font-size: 1.125rem;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
            justify-content: center;
            flex-wrap: wrap;
        }
        .btn-hero {
            padding: 1rem 2rem;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 700;
            transition: all 0.3s ease;
            display: inline-block;
        }
        .btn-primary-hero {
            background: rgba(255,255,255,0.2);
            color: white;
            border: 2px solid white;
        }
        .btn-primary-hero:hover {
            background: white;
            color: #086c6b;
            transform: translateY(-2px);
        }
        .btn-secondary-hero {
            background: #f59e0b;
            color: white;
            border: 2px solid #f59e0b;
        }
        .btn-secondary-hero:hover {
            background: transparent;
            color: #f59e0b;
            transform: translateY(-2px);
        }
        .features-section {
            padding: 4rem 0;
            background: #f8fafc;
        }
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-top: 3rem;
        }
        .feature-card {
            background: white;
            padding: 2rem;
            border-radius: 16px;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .feature-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        .feature-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
        .feature-card h3 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: #1f2937;
        }
        .feature-card p {
            color: #6b7280;
            line-height: 1.6;
        }
        .rooms-preview {
            padding: 4rem 0;
        }
        .section-header {
            text-align: center;
            margin-bottom: 3rem;
        }
        .section-header h2 {
            font-size: 2.5rem;
            font-weight: 800;
            color: #1f2937;
            margin-bottom: 1rem;
        }
        .section-header p {
            font-size: 1.125rem;
            color: #6b7280;
            max-width: 600px;
            margin: 0 auto;
        }
        .rooms-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 2rem;
            margin-top: 3rem;
        }
        .room-card {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .room-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        .room-image {
            width: 100%;
            height: 250px;
            object-fit: cover;
        }
        .room-content {
            padding: 1.5rem;
        }
        .room-title {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            color: #1f2937;
        }
        .room-price {
            font-size: 1.5rem;
            font-weight: 800;
            color: #059669;
            margin-bottom: 1rem;
        }
        .room-amenities {
            color: #6b7280;
            font-size: 0.875rem;
            margin-bottom: 1rem;
        }
        .room-btn {
            background: linear-gradient(90deg, #3b82f6, #1d4ed8);
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
        }
        .room-btn:hover {
            background: linear-gradient(90deg, #2563eb, #1e40af);
            transform: translateY(-1px);
        }
        .activities-preview {
            padding: 4rem 0;
            background: #f8fafc;
        }
        .activities-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-top: 3rem;
        }
        .activity-card {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        .activity-card:hover {
            transform: translateY(-2px);
        }
        .activity-title {
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 0.5rem;
        }
        .activity-price {
            font-weight: 600;
            color: #059669;
            margin-bottom: 0.5rem;
        }
        .activity-duration {
            color: #6b7280;
            font-size: 0.875rem;
        }
        .cta-section {
            background: linear-gradient(135deg, #086c6b, #0ea5e9);
            color: white;
            padding: 4rem 0;
            text-align: center;
        }
        .cta-content h2 {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 1rem;
        }
        .cta-content p {
            font-size: 1.125rem;
            margin-bottom: 2rem;
            opacity: 0.9;
        }
        .stats-section {
            background: white;
            padding: 3rem 0;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 2rem;
            text-align: center;
        }
        .stat-item h3 {
            font-size: 2.5rem;
            font-weight: 800;
            color: #3b82f6;
            margin-bottom: 0.5rem;
        }
        .stat-item p {
            color: #6b7280;
            font-weight: 600;
        }
        @media (max-width: 768px) {
            .hero-content h1 {
                font-size: 2rem;
            }
            .hero-buttons {
                flex-direction: column;
                align-items: center;
            }
            .rooms-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <header class="site-header">
        <div class="container header-inner">
            <div class="brand">
                <img src="assets/logo.jpg" style="height:42px;margin-right:.6rem;vertical-align:middle">
                Mazvikadei Resort
            </div>
            <nav class="nav">
                <a href="index.php" class="active">
                    <i class="fas fa-home"></i>
                    Home
                </a>
                <a href="rooms.php">
                    <i class="fas fa-bed"></i>
                    Rooms
                </a>
                <a href="activities.php">
                    <i class="fas fa-hiking"></i>
                    Activities
                </a>
                <a href="events.php">
                    <i class="fas fa-calendar-alt"></i>
                    Events
                </a>
                <a href="bookings.php">
                    <i class="fas fa-calendar-check"></i>
                    Bookings
                </a>
                <a href="about.php">
                    <i class="fas fa-info-circle"></i>
                    About
                </a>
                <a href="contact.php">
                    <i class="fas fa-envelope"></i>
                    Contact
                </a>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="customer/dashboard.php">
                        <i class="fas fa-user-circle"></i>
                        My Dashboard
                    </a>
                    <a href="auth/logout.php">
                        <i class="fas fa-sign-out-alt"></i>
                        Logout
                    </a>
                <?php else: ?>
                    <a href="auth/login.php">
                        <i class="fas fa-sign-in-alt"></i>
                        Login
                    </a>
                <?php endif; ?>
                <a href="admin/enhanced_dashboard.php">
                    <i class="fas fa-cog"></i>
                    Admin
                </a>
            </nav>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="floating-elements">
            <div class="floating-element"></div>
            <div class="floating-element"></div>
            <div class="floating-element"></div>
            <div class="floating-element"></div>
            <div class="floating-element"></div>
        </div>
        <div class="container">
            <div class="hero-content">
                <div class="hero-subtitle">
                    <i class="fas fa-star"></i>
                    Luxury Resort Experience
                </div>
                <h1>
                    <i class="fas fa-mountain"></i> 
                    Welcome to Mazvikadei Resort
                </h1>
                <p>Experience luxury accommodation, exciting activities, and unforgettable events in the heart of Zimbabwe's natural beauty. Your perfect getaway awaits with stunning views of Mazvikadei Dam.</p>
                <div class="hero-buttons">
                    <a href="rooms.php" class="hero-btn hero-btn-primary">
                        <i class="fas fa-bed"></i>
                        Explore Rooms
                    </a>
                    <a href="activities.php" class="hero-btn hero-btn-secondary">
                        <i class="fas fa-hiking"></i>
                        Activities
                    </a>
                    <a href="bookings.php" class="hero-btn hero-btn-success">
                        <i class="fas fa-calendar-check"></i>
                        Book Now
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Statistics Section -->
        <div class="stats-section">
            <div class="container">
                <div class="stats-grid">
                    <div class="stat-item">
                        <div class="stat-icon">
                            <i class="fas fa-bed"></i>
                        </div>
                        <div class="stat-number">50+</div>
                        <div class="stat-label">Luxury Rooms</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-number">1000+</div>
                        <div class="stat-label">Happy Guests</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-icon">
                            <i class="fas fa-star"></i>
                        </div>
                        <div class="stat-number">4.9</div>
                        <div class="stat-label">Average Rating</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-icon">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <div class="stat-number">365</div>
                        <div class="stat-label">Days Open</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features-section">
        <div class="container">
            <div class="section-header">
                <h2>Why Choose Mazvikadei Resort?</h2>
                <p>Discover what makes our resort the perfect destination for your next getaway</p>
            </div>
            <div class="features-grid">
                <div class="feature-card animate-fadeInUp">
                    <div class="feature-icon">
                        <i class="fas fa-umbrella-beach"></i>
                    </div>
                    <h3>Beachfront Location</h3>
                    <p>Enjoy stunning views of Mazvikadei Dam with direct access to pristine beaches and water activities.</p>
                </div>
                <div class="feature-card animate-fadeInUp" style="animation-delay: 0.2s;">
                    <div class="feature-icon">
                        <i class="fas fa-bed"></i>
                    </div>
                    <h3>Luxury Accommodation</h3>
                    <p>Stay in our beautifully appointed rooms and suites with modern amenities and breathtaking views.</p>
                </div>
                <div class="feature-card animate-fadeInUp" style="animation-delay: 0.4s;">
                    <div class="feature-icon">
                        <i class="fas fa-hiking"></i>
                    </div>
                    <h3>Exciting Activities</h3>
                    <p>From boat cruising and fishing to nature walks and team building, we have activities for everyone.</p>
                </div>
                <div class="feature-card animate-fadeInUp" style="animation-delay: 0.6s;">
                    <div class="feature-icon">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <h3>Event Venues</h3>
                    <p>Host your special events, weddings, and corporate functions in our beautiful venues.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Rooms -->
    <section class="rooms-preview">
        <div class="container">
            <div class="section-header">
                <h2>Featured Accommodation</h2>
                <p>Choose from our selection of comfortable and luxurious rooms</p>
            </div>
            <div class="rooms-grid">
                <?php foreach ($featured_rooms as $index => $room): ?>
                <div class="room-card animate-fadeInUp" style="animation-delay: <?php echo $index * 0.2; ?>s;">
                    <img src="<?php echo htmlspecialchars($room['image'] ?? 'assets/rooms/default.jpg'); ?>" 
                         alt="<?php echo htmlspecialchars($room['title']); ?>" class="room-image"
                         onerror="this.src='assets/rooms/default.jpg'">
                    <div class="room-content">
                        <h3 class="room-title">
                            <i class="fas fa-bed"></i>
                            <?php echo htmlspecialchars($room['title']); ?>
                        </h3>
                        <div class="room-price">
                            <i class="fas fa-dollar-sign"></i>
                            $<?php echo number_format($room['price'], 2); ?>/night
                        </div>
                        <div class="room-amenities">
                            <i class="fas fa-star"></i>
                            <?php echo htmlspecialchars($room['amenities'] ?? 'Standard amenities'); ?>
                        </div>
                        <button class="room-btn" onclick="bookRoom(<?php echo $room['id']; ?>)">
                            <i class="fas fa-calendar-check"></i>
                            Book Now
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <div style="text-align: center; margin-top: 2rem;">
                <a href="rooms.php" class="btn primary">View All Rooms</a>
            </div>
        </div>
    </section>

    <!-- Featured Activities -->
    <section class="activities-preview">
        <div class="container">
            <div class="section-header">
                <h2>Popular Activities</h2>
                <p>Make the most of your stay with our exciting activities and experiences</p>
            </div>
            <div class="activities-grid">
                <?php foreach ($featured_activities as $activity): ?>
                <div class="activity-card">
                    <h4 class="activity-title"><?php echo htmlspecialchars($activity['title']); ?></h4>
                    <div class="activity-price">$<?php echo number_format($activity['price'], 2); ?></div>
                    <div class="activity-duration"><?php echo htmlspecialchars($activity['duration'] ?? 'Flexible'); ?></div>
                </div>
                <?php endforeach; ?>
            </div>
            <div style="text-align: center; margin-top: 2rem;">
                <a href="activities.php" class="btn primary">View All Activities</a>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats-section">
        <div class="container">
            <div class="stats-grid">
                <div class="stat-item">
                    <h3>50+</h3>
                    <p>Happy Guests</p>
                </div>
                <div class="stat-item">
                    <h3>15+</h3>
                    <p>Activities</p>
                </div>
                <div class="stat-item">
                    <h3>5</h3>
                    <p>Room Types</p>
                </div>
                <div class="stat-item">
                    <h3>100%</h3>
                    <p>Satisfaction</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Call to Action -->
    <section class="cta-section">
        <div class="container">
            <div class="cta-content">
                <h2>Ready for Your Perfect Getaway?</h2>
                <p>Book your stay today and experience the magic of Mazvikadei Resort</p>
                <div class="hero-buttons">
                    <a href="bookings.php" class="btn-hero btn-primary-hero">Book Now</a>
                    <a href="contact.php" class="btn-hero btn-secondary-hero">Contact Us</a>
                </div>
            </div>
        </div>
    </section>

    <footer class="site-footer container">© <span id="year"></span> Mazvikadei Resort</footer>
    
    <script src="app.js"></script>
    <script>
        function bookRoom(roomId) {
            // Store room selection and redirect to booking page
            localStorage.setItem('selectedRoom', roomId);
            window.location.href = 'bookings.php';
        }
        
        // Set current year
        document.getElementById('year').textContent = new Date().getFullYear();
        
        // Enhanced home page interactions
        document.addEventListener('DOMContentLoaded', function() {
            // Parallax effect for hero section
            window.addEventListener('scroll', function() {
                const scrolled = window.pageYOffset;
                const heroSection = document.querySelector('.hero-section');
                const floatingElements = document.querySelectorAll('.floating-element');
                
                if (heroSection) {
                    heroSection.style.transform = `translateY(${scrolled * 0.3}px)`;
                }
                
                floatingElements.forEach((element, index) => {
                    const speed = 0.2 + (index * 0.1);
                    element.style.transform = `translateY(${scrolled * speed}px)`;
                });
            });
            
            // Animate statistics on scroll
            const observerOptions = {
                threshold: 0.5,
                rootMargin: '0px 0px -100px 0px'
            };
            
            const observer = new IntersectionObserver(function(entries) {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        animateNumbers(entry.target);
                        observer.unobserve(entry.target);
                    }
                });
            }, observerOptions);
            
            document.querySelectorAll('.stat-number').forEach(stat => {
                observer.observe(stat);
            });
            
            // Animate numbers
            function animateNumbers(element) {
                const finalNumber = element.textContent;
                const isDecimal = finalNumber.includes('.');
                const targetNumber = parseFloat(finalNumber);
                const duration = 2000;
                const startTime = performance.now();
                
                function updateNumber(currentTime) {
                    const elapsed = currentTime - startTime;
                    const progress = Math.min(elapsed / duration, 1);
                    const easeOutQuart = 1 - Math.pow(1 - progress, 4);
                    const currentNumber = targetNumber * easeOutQuart;
                    
                    if (isDecimal) {
                        element.textContent = currentNumber.toFixed(1);
                    } else {
                        element.textContent = Math.floor(currentNumber) + '+';
                    }
                    
                    if (progress < 1) {
                        requestAnimationFrame(updateNumber);
                    } else {
                        element.textContent = finalNumber;
                    }
                }
                
                requestAnimationFrame(updateNumber);
            }
            
            // Add hover effects to hero buttons
            document.querySelectorAll('.hero-btn').forEach(btn => {
                btn.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-3px) scale(1.05)';
                });
                
                btn.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0) scale(1)';
                });
            });
        });
        
        // Add ripple effect CSS
        const style = document.createElement('style');
        style.textContent = `
            .hero-btn {
                position: relative;
                overflow: hidden;
            }
            
            .ripple {
                position: absolute;
                border-radius: 50%;
                background: rgba(255, 255, 255, 0.3);
                transform: scale(0);
                animation: ripple-animation 0.6s linear;
                pointer-events: none;
            }
            
            @keyframes ripple-animation {
                to {
                    transform: scale(4);
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>
