<?php
require_once 'php/config.php';

// Get statistics from database
try {
    $pdo = getPDO();
    
    // Get total bookings
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM bookings");
    $total_bookings = $stmt->fetch()['total'];
    
    // Get total customers
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE role = 'customer'");
    $total_customers = $stmt->fetch()['total'];
    
    // Get total rooms
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM rooms");
    $total_rooms = $stmt->fetch()['total'];
    
    // Get total activities
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM activities WHERE status = 'active'");
    $total_activities = $stmt->fetch()['total'];
    
} catch (Exception $e) {
    $total_bookings = 0;
    $total_customers = 0;
    $total_rooms = 0;
    $total_activities = 0;
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>About Us - Mazvikadei Resort</title>
    <link rel="stylesheet" href="styles.css" />
    <style>
        .page-header {
            background: linear-gradient(135deg, #059669, #10b981);
            color: white;
            padding: 4rem 0;
            text-align: center;
        }
        .page-header h1 {
            font-size: 3rem;
            font-weight: 800;
            margin-bottom: 1rem;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        .page-header p {
            font-size: 1.25rem;
            opacity: 0.9;
        }
        .about-container {
            max-width: 1000px;
            margin: 0 auto;
        }
        .hero-section {
            background: white;
            border-radius: 16px;
            padding: 3rem;
            margin: 2rem 0;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .hero-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 3rem;
            align-items: center;
        }
        .hero-text h2 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: #1f2937;
        }
        .hero-text p {
            color: #6b7280;
            line-height: 1.6;
            margin-bottom: 1.5rem;
        }
        .hero-image {
            width: 100%;
            height: 300px;
            object-fit: cover;
            border-radius: 12px;
        }
        .stats-section {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            color: white;
            padding: 3rem 0;
            margin: 2rem 0;
            border-radius: 16px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 2rem;
            text-align: center;
        }
        .stat-item h3 {
            font-size: 3rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
        }
        .stat-item p {
            font-size: 1.125rem;
            opacity: 0.9;
        }
        .features-section {
            background: #f8fafc;
            padding: 3rem 0;
            margin: 2rem 0;
            border-radius: 16px;
        }
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }
        .feature-card {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        .feature-card:hover {
            transform: translateY(-4px);
        }
        .feature-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
        .feature-card h3 {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: #1f2937;
        }
        .feature-card p {
            color: #6b7280;
            line-height: 1.6;
        }
        .team-section {
            background: white;
            padding: 3rem;
            margin: 2rem 0;
            border-radius: 16px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .team-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }
        .team-member {
            text-align: center;
            padding: 1.5rem;
            background: #f9fafb;
            border-radius: 12px;
        }
        .team-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            color: white;
            font-size: 2rem;
            font-weight: bold;
        }
        .team-member h4 {
            font-weight: 700;
            margin-bottom: 0.5rem;
            color: #1f2937;
        }
        .team-member p {
            color: #6b7280;
            font-size: 0.875rem;
        }
        .mission-section {
            background: linear-gradient(135deg, #1f2937, #374151);
            color: white;
            padding: 3rem;
            margin: 2rem 0;
            border-radius: 16px;
        }
        .mission-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 3rem;
            align-items: center;
        }
        .mission-text h2 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }
        .mission-text p {
            opacity: 0.9;
            line-height: 1.6;
            margin-bottom: 1.5rem;
        }
        .mission-image {
            width: 100%;
            height: 250px;
            object-fit: cover;
            border-radius: 12px;
        }
        .values-section {
            background: white;
            padding: 3rem;
            margin: 2rem 0;
            border-radius: 16px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .values-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }
        .value-item {
            text-align: center;
            padding: 1.5rem;
        }
        .value-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }
        .value-item h3 {
            font-weight: 700;
            margin-bottom: 1rem;
            color: #1f2937;
        }
        .value-item p {
            color: #6b7280;
            line-height: 1.6;
        }
        @media (max-width: 768px) {
            .hero-content,
            .mission-content {
                grid-template-columns: 1fr;
            }
            .page-header h1 {
                font-size: 2rem;
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
                <a href="index.php">Home</a>
                <a href="rooms.php">Rooms</a>
                <a href="activities.php">Activities</a>
                <a href="events.php">Events</a>
                <a href="bookings.php">Bookings</a>
                <a href="about.php" class="active">About</a>
                <a href="contact.php">Contact</a>
                <a href="admin/login.php">Admin</a>
            </nav>
        </div>
    </header>

    <div class="page-header">
        <div class="container">
            <h1>About Mazvikadei Resort</h1>
            <p>Discover our story, values, and commitment to providing exceptional hospitality</p>
        </div>
    </div>

    <main class="container">
        <div class="about-container">
            <!-- Hero Section -->
            <div class="hero-section">
                <div class="hero-content">
                    <div class="hero-text">
                        <h2>Welcome to Paradise</h2>
                        <p>Nestled on the shores of the beautiful Mazvikadei Dam, our resort offers a perfect blend of luxury, adventure, and tranquility. Since our establishment, we have been committed to providing unforgettable experiences for our guests.</p>
                        <p>Whether you're seeking a romantic getaway, a family vacation, or a corporate retreat, Mazvikadei Resort provides the perfect setting for your needs.</p>
                        <a href="bookings.php" class="btn primary">Book Your Stay</a>
                    </div>
                    <div>
                        <img src="assets/about-hero.jpg" alt="Mazvikadei Resort" class="hero-image" 
                             onerror="this.src='assets/rooms/default.jpg'">
                    </div>
                </div>
            </div>

            <!-- Statistics -->
            <div class="stats-section">
                <div class="container">
                    <div style="text-align: center; margin-bottom: 2rem;">
                        <h2>Our Impact</h2>
                        <p>Numbers that reflect our commitment to excellence</p>
                    </div>
                    <div class="stats-grid">
                        <div class="stat-item">
                            <h3><?php echo $total_bookings; ?>+</h3>
                            <p>Successful Bookings</p>
                        </div>
                        <div class="stat-item">
                            <h3><?php echo $total_customers; ?>+</h3>
                            <p>Happy Guests</p>
                        </div>
                        <div class="stat-item">
                            <h3><?php echo $total_rooms; ?></h3>
                            <p>Luxury Rooms</p>
                        </div>
                        <div class="stat-item">
                            <h3><?php echo $total_activities; ?>+</h3>
                            <p>Activities</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Features Section -->
            <div class="features-section">
                <div class="container">
                    <div style="text-align: center; margin-bottom: 2rem;">
                        <h2>What Makes Us Special</h2>
                        <p>Discover the unique features that set us apart</p>
                    </div>
                    <div class="features-grid">
                        <div class="feature-card">
                            <div class="feature-icon">🏖️</div>
                            <h3>Prime Location</h3>
                            <p>Located on the pristine shores of Mazvikadei Dam with breathtaking views and direct water access.</p>
                        </div>
                        <div class="feature-card">
                            <div class="feature-icon">🏨</div>
                            <h3>Luxury Accommodation</h3>
                            <p>Beautifully appointed rooms and suites with modern amenities and stunning views.</p>
                        </div>
                        <div class="feature-card">
                            <div class="feature-icon">🎯</div>
                            <h3>Diverse Activities</h3>
                            <p>From water sports to nature walks, we offer activities for every interest and age group.</p>
                        </div>
                        <div class="feature-card">
                            <div class="feature-icon">🍽️</div>
                            <h3>Fine Dining</h3>
                            <p>Experience exquisite cuisine with fresh, locally sourced ingredients and international flavors.</p>
                        </div>
                        <div class="feature-card">
                            <div class="feature-icon">🎉</div>
                            <h3>Event Venues</h3>
                            <p>Perfect settings for weddings, conferences, and special celebrations with professional service.</p>
                        </div>
                        <div class="feature-card">
                            <div class="feature-icon">🌿</div>
                            <h3>Eco-Friendly</h3>
                            <p>Committed to sustainable practices and environmental conservation in all our operations.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Mission Section -->
            <div class="mission-section">
                <div class="mission-content">
                    <div class="mission-text">
                        <h2>Our Mission</h2>
                        <p>To provide exceptional hospitality experiences that create lasting memories while preserving the natural beauty of our environment.</p>
                        <p>We believe in sustainable tourism that benefits both our guests and the local community, ensuring that future generations can enjoy the same pristine beauty we cherish today.</p>
                        <a href="contact.php" class="btn primary">Get in Touch</a>
                    </div>
                    <div>
                        <img src="assets/mission.jpg" alt="Our Mission" class="mission-image" 
                             onerror="this.src='assets/rooms/default.jpg'">
                    </div>
                </div>
            </div>

            <!-- Values Section -->
            <div class="values-section">
                <div style="text-align: center; margin-bottom: 2rem;">
                    <h2>Our Values</h2>
                    <p>The principles that guide everything we do</p>
                </div>
                <div class="values-grid">
                    <div class="value-item">
                        <div class="value-icon">🤝</div>
                        <h3>Hospitality</h3>
                        <p>We treat every guest as family, ensuring their comfort and satisfaction is our top priority.</p>
                    </div>
                    <div class="value-item">
                        <div class="value-icon">🌱</div>
                        <h3>Sustainability</h3>
                        <p>We are committed to environmental conservation and sustainable tourism practices.</p>
                    </div>
                    <div class="value-item">
                        <div class="value-icon">⭐</div>
                        <h3>Excellence</h3>
                        <p>We strive for the highest standards in service, facilities, and guest experiences.</p>
                    </div>
                    <div class="value-item">
                        <div class="value-icon">🏘️</div>
                        <h3>Community</h3>
                        <p>We support local communities and contribute to regional economic development.</p>
                    </div>
                </div>
            </div>

            <!-- Team Section -->
            <div class="team-section">
                <div style="text-align: center; margin-bottom: 2rem;">
                    <h2>Meet Our Team</h2>
                    <p>The dedicated professionals who make your stay memorable</p>
                </div>
                <div class="team-grid">
                    <div class="team-member">
                        <div class="team-avatar">JD</div>
                        <h4>John Doe</h4>
                        <p>General Manager</p>
                    </div>
                    <div class="team-member">
                        <div class="team-avatar">JS</div>
                        <h4>Jane Smith</h4>
                        <p>Guest Relations Manager</p>
                    </div>
                    <div class="team-member">
                        <div class="team-avatar">MB</div>
                        <h4>Mike Brown</h4>
                        <p>Activities Coordinator</p>
                    </div>
                    <div class="team-member">
                        <div class="team-avatar">SW</div>
                        <h4>Sarah Wilson</h4>
                        <p>Head Chef</p>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <footer class="site-footer container">© <span id="year"></span> Mazvikadei Resort</footer>
    
    <script>
        // Set current year
        document.getElementById('year').textContent = new Date().getFullYear();
    </script>
</body>
</html>
