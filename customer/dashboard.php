<?php
session_start();
require_once '../php/config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

// Get user details
$user_details = null;
$user_bookings = [];
$recent_bookings = [];
$total_spent = 0;

try {
    $pdo = getPDO();
    
    // Get user information
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user_details = $stmt->fetch();
    
    // Get user bookings
    $stmt = $pdo->prepare("
        SELECT b.*, 
               CASE 
                   WHEN b.type = 'room' THEN r.title
                   WHEN b.type = 'activity' THEN a.title
                   WHEN b.type = 'event' THEN e.title
               END as item_title
        FROM bookings b
        LEFT JOIN rooms r ON b.type = 'room' AND JSON_EXTRACT(b.items_json, '$[0].room_id') = r.id
        LEFT JOIN activities a ON b.type = 'activity' AND JSON_EXTRACT(b.items_json, '$[0].activity_id') = a.id
        LEFT JOIN events e ON b.type = 'event' AND JSON_EXTRACT(b.items_json, '$[0].event_id') = e.id
        WHERE b.customer_id = ?
        ORDER BY b.created_at DESC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $user_bookings = $stmt->fetchAll();
    
    // Get recent bookings (last 5)
    $recent_bookings = array_slice($user_bookings, 0, 5);
    
    // Calculate total spent
    foreach ($user_bookings as $booking) {
        if ($booking['status'] === 'confirmed' || $booking['status'] === 'completed') {
            $total_spent += $booking['total_amount'];
        }
    }
    
} catch (Exception $e) {
    $error_message = "Error loading dashboard: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Dashboard - Mazvikadei Resort</title>
    <link rel="stylesheet" href="../styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .dashboard-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }
        
        .dashboard-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            border-radius: 12px;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }
        
        .dashboard-header h1 {
            margin: 0 0 0.5rem 0;
            font-size: 2rem;
        }
        
        .dashboard-header p {
            margin: 0;
            opacity: 0.9;
        }
        
        .dashboard-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
            transition: transform 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-card i {
            font-size: 2rem;
            margin-bottom: 1rem;
            color: #667eea;
        }
        
        .stat-card h3 {
            margin: 0 0 0.5rem 0;
            font-size: 2rem;
            color: #333;
        }
        
        .stat-card p {
            margin: 0;
            color: #666;
        }
        
        .dashboard-sections {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            margin-bottom: 2rem;
        }
        
        .dashboard-section {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .dashboard-section h2 {
            margin: 0 0 1rem 0;
            color: #333;
            border-bottom: 2px solid #667eea;
            padding-bottom: 0.5rem;
        }
        
        .booking-item {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
        }
        
        .booking-item:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .booking-header {
            display: flex;
            justify-content: between;
            align-items: center;
            margin-bottom: 0.5rem;
        }
        
        .booking-reference {
            font-weight: 600;
            color: #667eea;
        }
        
        .booking-status {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 500;
        }
        
        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }
        
        .status-confirmed {
            background: #d1fae5;
            color: #065f46;
        }
        
        .status-cancelled {
            background: #fee2e2;
            color: #991b1b;
        }
        
        .booking-details {
            color: #666;
            font-size: 0.875rem;
        }
        
        .booking-amount {
            font-weight: 600;
            color: #333;
            margin-top: 0.5rem;
        }
        
        .profile-section {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .profile-form {
            display: grid;
            gap: 1rem;
        }
        
        .form-group {
            display: flex;
            flex-direction: column;
        }
        
        .form-group label {
            font-weight: 500;
            margin-bottom: 0.5rem;
            color: #333;
        }
        
        .form-group input {
            padding: 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 1rem;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .btn-update {
            background: #667eea;
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.3s ease;
        }
        
        .btn-update:hover {
            background: #5a67d8;
        }
        
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-top: 2rem;
        }
        
        .quick-action {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1.5rem;
            border-radius: 12px;
            text-align: center;
            text-decoration: none;
            transition: transform 0.3s ease;
        }
        
        .quick-action:hover {
            transform: translateY(-3px);
            color: white;
        }
        
        .quick-action i {
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }
        
        .quick-action h3 {
            margin: 0 0 0.5rem 0;
        }
        
        .quick-action p {
            margin: 0;
            opacity: 0.9;
        }
        
        @media (max-width: 768px) {
            .dashboard-sections {
                grid-template-columns: 1fr;
            }
            
            .dashboard-stats {
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            }
        }
    </style>
</head>
<body>
    <header class="site-header">
        <nav class="navbar">
            <div class="nav-brand">
                <a href="../index.php">
                    <i class="fas fa-mountain"></i>
                    <span>Mazvikadei Resort</span>
                </a>
            </div>
            <ul class="nav-menu">
                <li><a href="../index.php">Home</a></li>
                <li><a href="../rooms.php">Rooms</a></li>
                <li><a href="../activities.php">Activities</a></li>
                <li><a href="../events.php">Events</a></li>
                <li><a href="../bookings.php">Book Now</a></li>
                <li><a href="../contact.php">Contact</a></li>
                <li><a href="dashboard.php" class="active">My Dashboard</a></li>
                <li><a href="../auth/logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>

    <main class="dashboard-container">
        <div class="dashboard-header">
            <h1>Welcome back, <?php echo htmlspecialchars($user_details['name']); ?>!</h1>
            <p>Manage your bookings and profile</p>
        </div>

        <div class="dashboard-stats">
            <div class="stat-card">
                <i class="fas fa-calendar-check"></i>
                <h3><?php echo count($user_bookings); ?></h3>
                <p>Total Bookings</p>
            </div>
            <div class="stat-card">
                <i class="fas fa-check-circle"></i>
                <h3><?php echo count(array_filter($user_bookings, function($b) { return $b['status'] === 'confirmed'; })); ?></h3>
                <p>Confirmed</p>
            </div>
            <div class="stat-card">
                <i class="fas fa-clock"></i>
                <h3><?php echo count(array_filter($user_bookings, function($b) { return $b['status'] === 'pending'; })); ?></h3>
                <p>Pending</p>
            </div>
            <div class="stat-card">
                <i class="fas fa-dollar-sign"></i>
                <h3>$<?php echo number_format($total_spent, 2); ?></h3>
                <p>Total Spent</p>
            </div>
        </div>

        <div class="dashboard-sections">
            <div class="dashboard-section">
                <h2><i class="fas fa-history"></i> Recent Bookings</h2>
                <?php if (empty($recent_bookings)): ?>
                    <p>No bookings yet. <a href="../bookings.php">Make your first booking!</a></p>
                <?php else: ?>
                    <?php foreach ($recent_bookings as $booking): ?>
                        <div class="booking-item">
                            <div class="booking-header">
                                <span class="booking-reference">#<?php echo htmlspecialchars($booking['booking_reference']); ?></span>
                                <span class="booking-status status-<?php echo $booking['status']; ?>">
                                    <?php echo ucfirst($booking['status']); ?>
                                </span>
                            </div>
                            <div class="booking-details">
                                <strong><?php echo htmlspecialchars($booking['item_title'] ?? 'N/A'); ?></strong><br>
                                <?php if ($booking['check_in_date']): ?>
                                    Check-in: <?php echo date('M j, Y', strtotime($booking['check_in_date'])); ?><br>
                                <?php endif; ?>
                                <?php if ($booking['check_out_date']): ?>
                                    Check-out: <?php echo date('M j, Y', strtotime($booking['check_out_date'])); ?><br>
                                <?php endif; ?>
                                Booked: <?php echo date('M j, Y', strtotime($booking['created_at'])); ?>
                            </div>
                            <div class="booking-amount">
                                Total: $<?php echo number_format($booking['total_amount'], 2); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="profile-section">
                <h2><i class="fas fa-user"></i> Profile Information</h2>
                <form class="profile-form" id="profileForm">
                    <div class="form-group">
                        <label for="name">Full Name</label>
                        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user_details['name']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user_details['email']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="phone">Phone</label>
                        <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($user_details['phone'] ?? ''); ?>">
                    </div>
                    <button type="submit" class="btn-update">Update Profile</button>
                </form>
            </div>
        </div>

        <div class="quick-actions">
            <a href="../bookings.php" class="quick-action">
                <i class="fas fa-plus-circle"></i>
                <h3>New Booking</h3>
                <p>Book a room, activity, or event</p>
            </a>
            <a href="../rooms.php" class="quick-action">
                <i class="fas fa-bed"></i>
                <h3>Browse Rooms</h3>
                <p>Explore our accommodation options</p>
            </a>
            <a href="../activities.php" class="quick-action">
                <i class="fas fa-hiking"></i>
                <h3>Activities</h3>
                <p>Discover exciting activities</p>
            </a>
            <a href="../contact.php" class="quick-action">
                <i class="fas fa-envelope"></i>
                <h3>Contact Us</h3>
                <p>Get in touch with our team</p>
            </a>
        </div>
    </main>

    <footer class="site-footer">
        <div class="container">
            <p>&copy; <span id="year"></span> Mazvikadei Resort. All rights reserved.</p>
        </div>
    </footer>

    <script>
        // Update profile form
        document.getElementById('profileForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const data = {
                name: formData.get('name'),
                email: formData.get('email'),
                phone: formData.get('phone')
            };
            
            try {
                const response = await fetch('../php/api/update_profile.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(data)
                });
                
                const result = await response.json();
                
                if (result.success) {
                    alert('Profile updated successfully!');
                    location.reload();
                } else {
                    alert('Error updating profile: ' + (result.message || 'Unknown error'));
                }
            } catch (error) {
                console.error('Profile update error:', error);
                alert('Error updating profile. Please try again.');
            }
        });
        
        // Set current year
        document.getElementById('year').textContent = new Date().getFullYear();
    </script>
</body>
</html>
