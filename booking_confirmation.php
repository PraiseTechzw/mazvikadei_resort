<?php
session_start();
require_once 'php/config.php';

// Get booking reference from URL
$booking_ref = $_GET['ref'] ?? null;
$booking_data = null;

if ($booking_ref) {
    try {
        $pdo = getPDO();
        $stmt = $pdo->prepare("SELECT * FROM bookings WHERE booking_reference = ?");
        $stmt->execute([$booking_ref]);
        $booking_data = $stmt->fetch();
    } catch (Exception $e) {
        // Handle error
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Booking Confirmation - Mazvikadei Resort</title>
    <link rel="stylesheet" href="styles.css" />
    <style>
        .confirmation-container {
            max-width: 800px;
            margin: 2rem auto;
            text-align: center;
        }
        .success-icon {
            font-size: 4rem;
            color: #059669;
            margin-bottom: 1rem;
        }
        .confirmation-card {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin: 2rem 0;
        }
        .booking-reference {
            font-size: 1.5rem;
            font-weight: 700;
            color: #3b82f6;
            margin: 1rem 0;
        }
        .booking-details {
            text-align: left;
            background: #f9fafb;
            border-radius: 12px;
            padding: 1.5rem;
            margin: 1rem 0;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
            padding: 0.5rem 0;
            border-bottom: 1px solid #e5e7eb;
        }
        .detail-row:last-child {
            border-bottom: none;
        }
        .detail-label {
            font-weight: 600;
            color: #374151;
        }
        .detail-value {
            color: #6b7280;
        }
        .action-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-top: 2rem;
            flex-wrap: wrap;
        }
        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-primary {
            background: #3b82f6;
            color: white;
        }
        .btn-primary:hover {
            background: #2563eb;
        }
        .btn-secondary {
            background: #6b7280;
            color: white;
        }
        .btn-secondary:hover {
            background: #4b5563;
        }
        .next-steps {
            background: #eff6ff;
            border: 1px solid #3b82f6;
            border-radius: 12px;
            padding: 1.5rem;
            margin: 1rem 0;
        }
        .next-steps h4 {
            margin: 0 0 1rem 0;
            color: #1e40af;
        }
        .next-steps ul {
            text-align: left;
            margin: 0;
            color: #1e40af;
            padding-left: 1.5rem;
        }
        .next-steps li {
            margin-bottom: 0.5rem;
        }
        .booking-items {
            background: #f0f9ff;
            border: 1px solid #0ea5e9;
            border-radius: 12px;
            padding: 1.5rem;
            margin: 1rem 0;
        }
        .booking-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem 0;
            border-bottom: 1px solid #e5e7eb;
        }
        .booking-item:last-child {
            border-bottom: none;
        }
        .item-details {
            flex: 1;
        }
        .item-title {
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 0.25rem;
        }
        .item-meta {
            font-size: 0.875rem;
            color: #6b7280;
        }
        .item-price {
            font-weight: 700;
            color: #059669;
        }
        .error-message {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fca5a5;
            border-radius: 8px;
            padding: 1rem;
            margin: 1rem 0;
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
                <a href="about.php">About</a>
                <a href="contact.php">Contact</a>
            </nav>
        </div>
    </header>

    <main class="container">
        <div class="confirmation-container">
            <?php if ($booking_data): ?>
            <div class="success-icon">✅</div>
            <h1>Booking Confirmed!</h1>
            <p class="muted">Thank you for choosing Mazvikadei Resort. Your booking has been successfully processed.</p>
            
            <div class="confirmation-card">
                <div class="booking-reference">Booking Reference: <?php echo htmlspecialchars($booking_data['booking_reference']); ?></div>
                <p>We have sent a confirmation email to your registered email address.</p>
                
                <div class="booking-details">
                    <div class="detail-row">
                        <span class="detail-label">Customer:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($booking_data['customer_name']); ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Email:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($booking_data['customer_email']); ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Phone:</span>
                        <span class="detail-value"><?php echo htmlspecialchars($booking_data['customer_phone'] ?? 'Not provided'); ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Total Amount:</span>
                        <span class="detail-value">$<?php echo number_format($booking_data['total_amount'], 2); ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Deposit Required:</span>
                        <span class="detail-value">$<?php echo number_format($booking_data['deposit_amount'], 2); ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Status:</span>
                        <span class="detail-value" style="color: #059669; font-weight: 600;"><?php echo ucfirst($booking_data['status']); ?></span>
                    </div>
                    <?php if ($booking_data['check_in_date']): ?>
                    <div class="detail-row">
                        <span class="detail-label">Check-in:</span>
                        <span class="detail-value"><?php echo date('M j, Y', strtotime($booking_data['check_in_date'])); ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if ($booking_data['check_out_date']): ?>
                    <div class="detail-row">
                        <span class="detail-label">Check-out:</span>
                        <span class="detail-value"><?php echo date('M j, Y', strtotime($booking_data['check_out_date'])); ?></span>
                    </div>
                    <?php endif; ?>
                </div>
                
                <?php 
                $items = json_decode($booking_data['items_json'], true);
                if ($items && count($items) > 0): 
                ?>
                <div class="booking-items">
                    <h4>Booked Items</h4>
                    <?php foreach ($items as $item): ?>
                    <div class="booking-item">
                        <div class="item-details">
                            <div class="item-title"><?php echo htmlspecialchars($item['title']); ?></div>
                            <div class="item-meta">Quantity: <?php echo $item['quantity'] ?? 1; ?></div>
                        </div>
                        <div class="item-price">$<?php echo number_format(($item['price'] ?? 0) * ($item['quantity'] ?? 1), 2); ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                
                <div class="next-steps">
                    <h4>Next Steps:</h4>
                    <ul>
                        <li>You will receive a confirmation email shortly</li>
                        <li>Payment instructions will be sent to your email</li>
                        <li>Contact us if you have any questions</li>
                        <li>Check-in time is from 2:00 PM</li>
                        <li>Check-out time is before 10:00 AM</li>
                    </ul>
                </div>
            </div>
            
            <div class="action-buttons">
                <a href="index.php" class="btn btn-primary">Back to Home</a>
                <a href="rooms.php" class="btn btn-secondary">Book Another Room</a>
            </div>
            
            <?php else: ?>
            <div class="error-message">
                <h2>Booking Not Found</h2>
                <p>The booking reference you provided could not be found. Please check your booking reference or contact us for assistance.</p>
                <div class="action-buttons">
                    <a href="bookings.php" class="btn btn-primary">Make a New Booking</a>
                    <a href="contact.php" class="btn btn-secondary">Contact Us</a>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </main>

    <footer class="site-footer container">© <span id="year"></span> Mazvikadei Resort</footer>
    
    <script>
        // Set current year
        document.getElementById('year').textContent = new Date().getFullYear();
    </script>
</body>
</html>
