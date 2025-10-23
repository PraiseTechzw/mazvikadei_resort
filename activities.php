<?php
require_once 'php/config.php';

// Get filter parameters
$category_id = $_GET['category_id'] ?? null;
$search = $_GET['search'] ?? '';
$price_max = $_GET['price_max'] ?? null;

try {
    $pdo = getPDO();
    
    // Build query for activities
    $sql = "SELECT a.*, ac.name as category_name 
            FROM activities a 
            LEFT JOIN activity_categories ac ON a.category_id = ac.id 
            WHERE a.status = 'active'";
    $params = [];
    
    if ($category_id) {
        $sql .= " AND a.category_id = ?";
        $params[] = $category_id;
    }
    
    if ($search) {
        $sql .= " AND (a.title LIKE ? OR a.description LIKE ?)";
        $searchTerm = "%$search%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }
    
    if ($price_max) {
        $sql .= " AND a.price <= ?";
        $params[] = $price_max;
    }
    
    $sql .= " ORDER BY a.price ASC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $activities = $stmt->fetchAll();
    
    // Get activity categories
    $stmt = $pdo->query("SELECT * FROM activity_categories ORDER BY name");
    $categories = $stmt->fetchAll();
    
} catch (Exception $e) {
    $activities = [];
    $categories = [];
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Activities - Mazvikadei Resort</title>
    <link rel="stylesheet" href="styles.css" />
    <style>
        .page-header {
            background: linear-gradient(135deg, #059669, #10b981);
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
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        .search-btn {
            background: linear-gradient(90deg, #059669, #10b981);
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
            background: linear-gradient(90deg, #047857, #059669);
            transform: translateY(-1px);
        }
        .activities-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 2rem;
            margin: 2rem 0;
        }
        .activity-card {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .activity-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        .activity-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        .activity-content {
            padding: 1.5rem;
        }
        .activity-title {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            color: #1f2937;
        }
        .activity-price {
            font-size: 1.5rem;
            font-weight: 800;
            color: #059669;
            margin-bottom: 1rem;
        }
        .activity-description {
            color: #6b7280;
            margin-bottom: 1rem;
            line-height: 1.6;
        }
        .activity-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-bottom: 1rem;
            font-size: 0.875rem;
            color: #6b7280;
        }
        .activity-detail {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .activity-detail-icon {
            font-size: 1rem;
        }
        .activity-schedule {
            background: #f0f9ff;
            border: 1px solid #0ea5e9;
            border-radius: 8px;
            padding: 0.75rem;
            margin-bottom: 1rem;
            font-size: 0.875rem;
            color: #1e40af;
        }
        .activity-requirements {
            background: #fef3c7;
            border: 1px solid #f59e0b;
            border-radius: 8px;
            padding: 0.75rem;
            margin-bottom: 1rem;
            font-size: 0.875rem;
            color: #92400e;
        }
        .book-btn {
            background: linear-gradient(90deg, #059669, #10b981);
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
            background: linear-gradient(90deg, #047857, #059669);
            transform: translateY(-1px);
        }
        .no-activities {
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
            background: #059669;
            color: white;
            border-color: #059669;
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
            background: #059669;
            color: white;
            border-color: #059669;
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
                <a href="activities.php" class="active">Activities</a>
                <a href="events.php">Events</a>
                <a href="bookings.php">Bookings</a>
                <a href="about.php">About</a>
                <a href="contact.php">Contact</a>
                <a href="admin/login.php">Admin</a>
            </nav>
        </div>
    </header>

    <div class="page-header">
        <div class="container">
            <h1>Activities & Experiences</h1>
            <p>Discover exciting activities and create unforgettable memories at Mazvikadei Resort</p>
        </div>
    </div>

    <main class="container">
        <!-- Filter Section -->
        <div class="filter-section">
            <h3>Find Your Perfect Activity</h3>
            <form method="GET" action="activities.php">
                <div class="filter-grid">
                    <div class="form-group">
                        <label for="category_id">Activity Category</label>
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
                            <option value="20" <?php echo $price_max == '20' ? 'selected' : ''; ?>>Under $20</option>
                            <option value="50" <?php echo $price_max == '50' ? 'selected' : ''; ?>>Under $50</option>
                            <option value="100" <?php echo $price_max == '100' ? 'selected' : ''; ?>>Under $100</option>
                            <option value="150" <?php echo $price_max == '150' ? 'selected' : ''; ?>>Under $150</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="search">Search</label>
                        <input type="text" id="search" name="search" placeholder="Search activities..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                </div>
                <button type="submit" class="search-btn">Search Activities</button>
            </form>
            
            <!-- Category Filter Buttons -->
            <div class="category-filter">
                <a href="activities.php" class="category-btn <?php echo !$category_id ? 'active' : ''; ?>">All Activities</a>
                <?php foreach ($categories as $cat): ?>
                <a href="activities.php?category_id=<?php echo $cat['id']; ?>" 
                   class="category-btn <?php echo $category_id == $cat['id'] ? 'active' : ''; ?>">
                    <?php echo htmlspecialchars($cat['name']); ?>
                </a>
                <?php endforeach; ?>
            </div>
            
            <!-- Price Filter Buttons -->
            <div class="price-filter">
                <a href="activities.php" class="price-btn <?php echo !$price_max ? 'active' : ''; ?>">Any Price</a>
                <a href="activities.php?price_max=20" class="price-btn <?php echo $price_max == '20' ? 'active' : ''; ?>">Under $20</a>
                <a href="activities.php?price_max=50" class="price-btn <?php echo $price_max == '50' ? 'active' : ''; ?>">Under $50</a>
                <a href="activities.php?price_max=100" class="price-btn <?php echo $price_max == '100' ? 'active' : ''; ?>">Under $100</a>
            </div>
        </div>

        <!-- Activities Grid -->
        <?php if (empty($activities)): ?>
        <div class="no-activities">
            <h3>No activities found</h3>
            <p>No activities match your search criteria. Try adjusting your filters.</p>
            <a href="activities.php" class="btn primary">View All Activities</a>
        </div>
        <?php else: ?>
        <div class="activities-grid">
            <?php foreach ($activities as $activity): ?>
            <div class="activity-card">
                <?php if ($activity['image']): ?>
                <img src="<?php echo htmlspecialchars($activity['image']); ?>" 
                     alt="<?php echo htmlspecialchars($activity['title']); ?>" class="activity-image"
                     onerror="this.style.display='none'">
                <?php endif; ?>
                <div class="activity-content">
                    <h3 class="activity-title"><?php echo htmlspecialchars($activity['title']); ?></h3>
                    <div class="activity-price">$<?php echo number_format($activity['price'], 2); ?></div>
                    
                    <?php if ($activity['description']): ?>
                    <div class="activity-description"><?php echo htmlspecialchars($activity['description']); ?></div>
                    <?php endif; ?>
                    
                    <div class="activity-details">
                        <div class="activity-detail">
                            <span class="activity-detail-icon">⏱️</span>
                            <span><?php echo htmlspecialchars($activity['duration'] ?? 'Flexible'); ?></span>
                        </div>
                        <div class="activity-detail">
                            <span class="activity-detail-icon">👥</span>
                            <span>Max <?php echo $activity['max_participants']; ?> people</span>
                        </div>
                    </div>
                    
                    <?php if ($activity['schedule']): ?>
                    <div class="activity-schedule">
                        <strong>Schedule:</strong> <?php echo htmlspecialchars($activity['schedule']); ?>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($activity['requirements']): ?>
                    <div class="activity-requirements">
                        <strong>Requirements:</strong> <?php echo htmlspecialchars($activity['requirements']); ?>
                    </div>
                    <?php endif; ?>
                    
                    <button class="book-btn" onclick="bookActivity(<?php echo $activity['id']; ?>, '<?php echo htmlspecialchars($activity['title']); ?>', <?php echo $activity['price']; ?>)">
                        Book Activity
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </main>

    <footer class="site-footer container">© <span id="year"></span> Mazvikadei Resort</footer>
    
    <script>
        function bookActivity(activityId, title, price) {
            // Store activity booking data
            const bookingData = {
                type: 'activity',
                items: [{
                    id: activityId,
                    title: title,
                    price: price,
                    quantity: 1,
                    activity_id: activityId
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
