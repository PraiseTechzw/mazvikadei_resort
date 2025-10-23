<?php
require_once 'php/config.php';

// Get filter parameters
$category_id = $_GET['category_id'] ?? null;
$check_in = $_GET['check_in'] ?? null;
$check_out = $_GET['check_out'] ?? null;
$guests = $_GET['guests'] ?? 2;
$search = $_GET['search'] ?? '';

try {
    $pdo = getPDO();
    
    // Build query for rooms
    $sql = "SELECT r.*, rc.name as category_name 
            FROM rooms r 
            LEFT JOIN room_categories rc ON r.category_id = rc.id 
            WHERE r.status = 'available'";
    $params = [];
    
    if ($category_id) {
        $sql .= " AND r.category_id = ?";
        $params[] = $category_id;
    }
    
    if ($search) {
        $sql .= " AND (r.title LIKE ? OR r.amenities LIKE ? OR r.description LIKE ?)";
        $searchTerm = "%$search%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }
    
    // Check availability for specific dates
    if ($check_in && $check_out) {
        $sql .= " AND r.id NOT IN (
            SELECT DISTINCT ra.room_id 
            FROM room_availability ra 
            WHERE ra.date >= ? AND ra.date < ? AND ra.status = 'booked'
        )";
        $params[] = $check_in;
        $params[] = $check_out;
    }
    
    $sql .= " ORDER BY r.price ASC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rooms = $stmt->fetchAll();
    
    // Get room categories
    $stmt = $pdo->query("SELECT * FROM room_categories ORDER BY name");
    $categories = $stmt->fetchAll();
    
} catch (Exception $e) {
    $rooms = [];
    $categories = [];
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Rooms & Accommodation - Mazvikadei Resort</title>
    <link rel="stylesheet" href="styles.css" />
    <style>
        .page-header {
            background: linear-gradient(135deg, #086c6b, #0ea5e9);
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
            background: linear-gradient(90deg, #3b82f6, #1d4ed8);
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
            background: linear-gradient(90deg, #2563eb, #1e40af);
            transform: translateY(-1px);
        }
        .rooms-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 2rem;
            margin: 2rem 0;
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
            line-height: 1.5;
        }
        .room-description {
            color: #374151;
            margin-bottom: 1rem;
            line-height: 1.6;
        }
        .room-details {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            font-size: 0.875rem;
            color: #6b7280;
        }
        .book-btn {
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
        .book-btn:hover {
            background: linear-gradient(90deg, #2563eb, #1e40af);
            transform: translateY(-1px);
        }
        .book-btn:disabled {
            background: #9ca3af;
            cursor: not-allowed;
            transform: none;
        }
        .no-rooms {
            text-align: center;
            padding: 3rem;
            color: #6b7280;
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .loading {
            text-align: center;
            padding: 2rem;
            color: #6b7280;
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
            background: #3b82f6;
            color: white;
            border-color: #3b82f6;
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
                <a href="rooms.php" class="active">Rooms</a>
                <a href="activities.php">Activities</a>
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
            <h1>Rooms & Accommodation</h1>
            <p>Choose from our selection of comfortable and luxurious rooms with modern amenities</p>
        </div>
    </div>

    <main class="container">
        <!-- Filter Section -->
        <div class="filter-section">
            <h3>Find Your Perfect Room</h3>
            <form method="GET" action="rooms.php">
                <div class="filter-grid">
                    <div class="form-group">
                        <label for="check_in">Check-in Date</label>
                        <input type="date" id="check_in" name="check_in" value="<?php echo htmlspecialchars($check_in); ?>">
                    </div>
                    <div class="form-group">
                        <label for="check_out">Check-out Date</label>
                        <input type="date" id="check_out" name="check_out" value="<?php echo htmlspecialchars($check_out); ?>">
                    </div>
                    <div class="form-group">
                        <label for="guests">Number of Guests</label>
                        <input type="number" id="guests" name="guests" min="1" max="10" value="<?php echo htmlspecialchars($guests); ?>">
                    </div>
                    <div class="form-group">
                        <label for="category_id">Room Category</label>
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
                        <label for="search">Search</label>
                        <input type="text" id="search" name="search" placeholder="Search rooms or amenities..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                </div>
                <button type="submit" class="search-btn">Search Rooms</button>
            </form>
            
            <!-- Category Filter Buttons -->
            <div class="category-filter">
                <a href="rooms.php" class="category-btn <?php echo !$category_id ? 'active' : ''; ?>">All Rooms</a>
                <?php foreach ($categories as $cat): ?>
                <a href="rooms.php?category_id=<?php echo $cat['id']; ?>" 
                   class="category-btn <?php echo $category_id == $cat['id'] ? 'active' : ''; ?>">
                    <?php echo htmlspecialchars($cat['name']); ?>
                </a>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Rooms Grid -->
        <?php if (empty($rooms)): ?>
        <div class="no-rooms">
            <h3>No rooms found</h3>
            <p>No rooms match your search criteria. Try adjusting your filters or dates.</p>
            <a href="rooms.php" class="btn primary">View All Rooms</a>
        </div>
        <?php else: ?>
        <div class="rooms-grid">
            <?php foreach ($rooms as $room): ?>
            <div class="room-card">
                <img src="<?php echo htmlspecialchars($room['image'] ?? 'assets/rooms/default.jpg'); ?>" 
                     alt="<?php echo htmlspecialchars($room['title']); ?>" class="room-image"
                     onerror="this.src='assets/rooms/default.jpg'">
                <div class="room-content">
                    <h3 class="room-title"><?php echo htmlspecialchars($room['title']); ?></h3>
                    <div class="room-price">$<?php echo number_format($room['price'], 2); ?>/night</div>
                    <div class="room-amenities"><?php echo htmlspecialchars($room['amenities'] ?? 'Standard amenities'); ?></div>
                    <?php if ($room['description']): ?>
                    <div class="room-description"><?php echo htmlspecialchars($room['description']); ?></div>
                    <?php endif; ?>
                    <div class="room-details">
                        <span>Max Occupancy: <?php echo $room['max_occupancy']; ?> guests</span>
                        <span><?php echo htmlspecialchars($room['category_name'] ?? 'Standard'); ?></span>
                    </div>
                    <button class="book-btn" onclick="bookRoom(<?php echo $room['id']; ?>, '<?php echo htmlspecialchars($room['title']); ?>', <?php echo $room['price']; ?>)">
                        Book Now
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </main>

    <footer class="site-footer container">© <span id="year"></span> Mazvikadei Resort</footer>
    
    <script>
        function bookRoom(roomId, title, price) {
            // Get current filter values
            const checkIn = document.getElementById('check_in').value;
            const checkOut = document.getElementById('check_out').value;
            const guests = document.getElementById('guests').value;
            
            if (!checkIn || !checkOut) {
                alert('Please select check-in and check-out dates.');
                return;
            }
            
            // Store booking data
            const bookingData = {
                type: 'room',
                items: [{
                    id: roomId,
                    title: title,
                    price: price,
                    quantity: 1,
                    room_id: roomId
                }],
                check_in_date: checkIn,
                check_out_date: checkOut,
                guests: guests
            };
            
            localStorage.setItem('pendingBooking', JSON.stringify(bookingData));
            window.location.href = 'bookings.php';
        }
        
        // Set default dates
        document.addEventListener('DOMContentLoaded', function() {
            const today = new Date();
            const tomorrow = new Date(today);
            tomorrow.setDate(tomorrow.getDate() + 1);
            
            if (!document.getElementById('check_in').value) {
                document.getElementById('check_in').value = today.toISOString().split('T')[0];
            }
            if (!document.getElementById('check_out').value) {
                document.getElementById('check_out').value = tomorrow.toISOString().split('T')[0];
            }
        });
        
        // Set current year
        document.getElementById('year').textContent = new Date().getFullYear();
    </script>
</body>
</html>
