<?php
require_once 'php/config.php';

// Get filter parameters
$category_id = $_GET['category_id'] ?? null;
$search = $_GET['search'] ?? '';
$price_max = $_GET['price_max'] ?? null;

try {
    $pdo = getPDO();
    
    // Build query for events
    $sql = "SELECT e.*, ec.name as category_name 
            FROM events e 
            LEFT JOIN event_categories ec ON e.category_id = ec.id 
            WHERE e.status = 'active'";
    $params = [];
    
    if ($category_id) {
        $sql .= " AND e.category_id = ?";
        $params[] = $category_id;
    }
    
    if ($search) {
        $sql .= " AND (e.title LIKE ? OR e.description LIKE ?)";
        $searchTerm = "%$search%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }
    
    if ($price_max) {
        $sql .= " AND e.price <= ?";
        $params[] = $price_max;
    }
    
    $sql .= " ORDER BY e.price ASC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $events = $stmt->fetchAll();
    
    // Get event categories
    $stmt = $pdo->query("SELECT * FROM event_categories ORDER BY name");
    $categories = $stmt->fetchAll();
    
} catch (Exception $e) {
    $events = [];
    $categories = [];
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Events & Venues - Mazvikadei Resort</title>
    <link rel="stylesheet" href="styles.css" />
    <style>
        .page-header {
            background: linear-gradient(135deg, #7c3aed, #a855f7);
            color: white;
            padding: 3rem 0;
            text-align: center;
        }
        .page-header h1 {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 1rem;
        }
        .page-header p {
            font-size: 1.125rem;
            opacity: 0.9;
        }
        .filter-section {
            background: white;
            padding: 2rem;
            border-radius: 16px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin: 2rem 0;
        }
        .filter-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        .form-group {
            display: flex;
            flex-direction: column;
        }
        .form-group label {
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #374151;
        }
        .form-group input,
        .form-group select {
            padding: 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }
        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #7c3aed;
            box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.1);
        }
        .search-btn {
            background: linear-gradient(90deg, #7c3aed, #a855f7);
            color: white;
            border: none;
            padding: 0.75rem 2rem;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
        }
        .search-btn:hover {
            background: linear-gradient(90deg, #6d28d9, #9333ea);
            transform: translateY(-1px);
        }
        .events-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 2rem;
            margin: 2rem 0;
        }
        .event-card {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .event-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        .event-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        .event-content {
            padding: 1.5rem;
        }
        .event-title {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            color: #1f2937;
        }
        .event-price {
            font-size: 1.5rem;
            font-weight: 800;
            color: #7c3aed;
            margin-bottom: 1rem;
        }
        .event-description {
            color: #6b7280;
            margin-bottom: 1rem;
            line-height: 1.6;
        }
        .event-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-bottom: 1rem;
            font-size: 0.875rem;
            color: #6b7280;
        }
        .event-detail {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .event-detail-icon {
            font-size: 1rem;
        }
        .event-venue {
            background: #f0f9ff;
            border: 1px solid #0ea5e9;
            border-radius: 8px;
            padding: 0.75rem;
            margin-bottom: 1rem;
            font-size: 0.875rem;
            color: #1e40af;
        }
        .event-includes {
            background: #fef3c7;
            border: 1px solid #f59e0b;
            border-radius: 8px;
            padding: 0.75rem;
            margin-bottom: 1rem;
            font-size: 0.875rem;
            color: #92400e;
        }
        .book-btn {
            background: linear-gradient(90deg, #7c3aed, #a855f7);
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
        }
        .book-btn:hover {
            background: linear-gradient(90deg, #6d28d9, #9333ea);
            transform: translateY(-1px);
        }
        .no-events {
            text-align: center;
            padding: 3rem;
            color: #6b7280;
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .category-filter {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
            margin-bottom: 1rem;
        }
        .category-btn {
            padding: 0.5rem 1rem;
            border: 1px solid #d1d5db;
            background: white;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            color: #374151;
        }
        .category-btn:hover,
        .category-btn.active {
            background: #7c3aed;
            color: white;
            border-color: #7c3aed;
        }
        .price-filter {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
            margin-bottom: 1rem;
        }
        .price-btn {
            padding: 0.5rem 1rem;
            border: 1px solid #d1d5db;
            background: white;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            color: #374151;
        }
        .price-btn:hover,
        .price-btn.active {
            background: #7c3aed;
            color: white;
            border-color: #7c3aed;
        }
        .hero-section {
            background: linear-gradient(135deg, rgba(124, 58, 237, 0.9), rgba(168, 85, 247, 0.8)), url('assets/events-hero.jpg');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 4rem 0;
            text-align: center;
            margin-bottom: 2rem;
            border-radius: 16px;
        }
        .hero-content h2 {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 1rem;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        .hero-content p {
            font-size: 1.125rem;
            margin-bottom: 2rem;
            opacity: 0.9;
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
                <a href="events.php" class="active">Events</a>
                <a href="bookings.php">Bookings</a>
                <a href="about.php">About</a>
                <a href="contact.php">Contact</a>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="customer/dashboard.php">My Dashboard</a>
                    <a href="auth/logout.php">Logout</a>
                <?php else: ?>
                    <a href="auth/login.php">Login</a>
                <?php endif; ?>
                <a href="admin/login.php">Admin</a>
            </nav>
        </div>
    </header>

    <div class="page-header">
        <div class="container">
            <h1>Events & Venues</h1>
            <p>Host your special occasions in our beautiful event spaces</p>
        </div>
    </div>

    <main class="container">
        <!-- Hero Section -->
        <div class="hero-section">
            <div class="hero-content">
                <h2>Perfect Venues for Special Moments</h2>
                <p>From intimate gatherings to grand celebrations, we provide the perfect setting for your memorable events</p>
                <a href="bookings.php" class="btn primary">Book Your Event</a>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="filter-section">
            <h3>Find Your Perfect Event Venue</h3>
            <form method="GET" action="events.php">
                <div class="filter-grid">
                    <div class="form-group">
                        <label for="category_id">Event Category</label>
                        <select id="category_id" name="category_id">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>" <?php echo $category_id == $cat['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="price_max">Max Price</label>
                        <select id="price_max" name="price_max">
                            <option value="">Any Price</option>
                            <option value="200" <?php echo $price_max == '200' ? 'selected' : ''; ?>>Under $200</option>
                            <option value="400" <?php echo $price_max == '400' ? 'selected' : ''; ?>>Under $400</option>
                            <option value="600" <?php echo $price_max == '600' ? 'selected' : ''; ?>>Under $600</option>
                            <option value="1000" <?php echo $price_max == '1000' ? 'selected' : ''; ?>>Under $1000</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="search">Search</label>
                        <input type="text" id="search" name="search" placeholder="Search events..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                </div>
                <button type="submit" class="search-btn">Search Events</button>
            </form>
            
            <!-- Category Filter Buttons -->
            <div class="category-filter">
                <a href="events.php" class="category-btn <?php echo !$category_id ? 'active' : ''; ?>">All Events</a>
                <?php foreach ($categories as $cat): ?>
                <a href="events.php?category_id=<?php echo $cat['id']; ?>" 
                   class="category-btn <?php echo $category_id == $cat['id'] ? 'active' : ''; ?>">
                    <?php echo htmlspecialchars($cat['name']); ?>
                </a>
                <?php endforeach; ?>
            </div>
            
            <!-- Price Filter Buttons -->
            <div class="price-filter">
                <a href="events.php" class="price-btn <?php echo !$price_max ? 'active' : ''; ?>">Any Price</a>
                <a href="events.php?price_max=200" class="price-btn <?php echo $price_max == '200' ? 'active' : ''; ?>">Under $200</a>
                <a href="events.php?price_max=400" class="price-btn <?php echo $price_max == '400' ? 'active' : ''; ?>">Under $400</a>
                <a href="events.php?price_max=600" class="price-btn <?php echo $price_max == '600' ? 'active' : ''; ?>">Under $600</a>
            </div>
        </div>

        <!-- Events Grid -->
        <?php if (empty($events)): ?>
        <div class="no-events">
            <h3>No events found</h3>
            <p>No events match your search criteria. Try adjusting your filters.</p>
            <a href="events.php" class="btn primary">View All Events</a>
        </div>
        <?php else: ?>
        <div class="events-grid">
            <?php foreach ($events as $event): ?>
            <div class="event-card">
                <?php if ($event['image']): ?>
                <img src="<?php echo htmlspecialchars($event['image']); ?>" 
                     alt="<?php echo htmlspecialchars($event['title']); ?>" class="event-image"
                     onerror="this.style.display='none'">
                <?php endif; ?>
                <div class="event-content">
                    <h3 class="event-title"><?php echo htmlspecialchars($event['title']); ?></h3>
                    <div class="event-price">$<?php echo number_format($event['price'], 2); ?></div>
                    
                    <?php if ($event['description']): ?>
                    <div class="event-description"><?php echo htmlspecialchars($event['description']); ?></div>
                    <?php endif; ?>
                    
                    <div class="event-details">
                        <div class="event-detail">
                            <span class="event-detail-icon">👥</span>
                            <span>Capacity: <?php echo $event['capacity']; ?> people</span>
                        </div>
                        <div class="event-detail">
                            <span class="event-detail-icon">⏱️</span>
                            <span><?php echo $event['duration_hours']; ?> hours</span>
                        </div>
                    </div>
                    
                    <?php if ($event['venue']): ?>
                    <div class="event-venue">
                        <strong>Venue:</strong> <?php echo htmlspecialchars($event['venue']); ?>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($event['includes']): ?>
                    <div class="event-includes">
                        <strong>Includes:</strong> <?php echo htmlspecialchars($event['includes']); ?>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($event['requirements']): ?>
                    <div class="event-includes">
                        <strong>Requirements:</strong> <?php echo htmlspecialchars($event['requirements']); ?>
                    </div>
                    <?php endif; ?>
                    
                    <button class="book-btn" onclick="bookEvent(<?php echo $event['id']; ?>, '<?php echo htmlspecialchars($event['title']); ?>', <?php echo $event['price']; ?>)">
                        Book Event
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </main>

    <footer class="site-footer container">© <span id="year"></span> Mazvikadei Resort</footer>
    
    <script>
        function bookEvent(eventId, title, price) {
            // Store event booking data
            const bookingData = {
                type: 'event',
                items: [{
                    id: eventId,
                    title: title,
                    price: price,
                    quantity: 1,
                    event_id: eventId
                }]
            };
            
            localStorage.setItem('pendingBooking', JSON.stringify(bookingData));
            window.location.href = 'bookings.php';
        }
        
        // Set current year
        document.getElementById('year').textContent = new Date().getFullYear();
    </script>
</body>
</html>
