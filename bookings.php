<?php
session_start();
require_once 'php/config.php';

// Check if user is logged in, redirect to login if not
if (!isset($_SESSION['user_id'])) {
    header('Location: auth/login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit;
}

// Get booking data from localStorage (passed via JavaScript)
$booking_data = null;
if (isset($_GET['data'])) {
    $booking_data = json_decode($_GET['data'], true);
}

// Get user details from database
$user_details = null;
try {
    $pdo = getPDO();
    
    // Get user information
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user_details = $stmt->fetch();
    
    // Get rooms, activities, and events for selection
    $stmt = $pdo->query("SELECT * FROM rooms WHERE available = 1 ORDER BY price ASC");
    $rooms = $stmt->fetchAll();
    
    $stmt = $pdo->query("SELECT * FROM activities WHERE available = 1 ORDER BY price ASC");
    $activities = $stmt->fetchAll();
    
    $stmt = $pdo->query("SELECT * FROM events WHERE available = 1 ORDER BY price ASC");
    $events = $stmt->fetchAll();
    
} catch (Exception $e) {
    $user_details = null;
    $rooms = [];
    $activities = [];
    $events = [];
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Bookings - Mazvikadei Resort</title>
    <link rel="stylesheet" href="styles.css" />
    <style>
        .page-header {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
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
        .booking-container {
            max-width: 1000px;
            margin: 0 auto;
        }
        .booking-step {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .user-info-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }
        
        .user-info-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
        }
        
        .user-info-header i {
            font-size: 2rem;
        }
        
        .user-info-header h3 {
            margin: 0;
            font-size: 1.25rem;
            font-weight: 600;
        }
        
        .user-info-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 0.75rem;
        }
        
        .user-info-details p {
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
            opacity: 0.9;
        }
        
        .user-info-details i {
            width: 16px;
            text-align: center;
        }
        .step-header {
            display: flex;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        .step-number {
            background: #3b82f6;
            color: white;
            width: 2rem;
            height: 2rem;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-right: 1rem;
        }
        .step-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: #1f2937;
        }
        .selection-tabs {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 2rem;
            border-bottom: 2px solid #e5e7eb;
        }
        .tab-btn {
            padding: 0.75rem 1.5rem;
            border: none;
            background: transparent;
            color: #6b7280;
            font-weight: 600;
            cursor: pointer;
            border-bottom: 2px solid transparent;
            transition: all 0.3s ease;
        }
        .tab-btn.active {
            color: #3b82f6;
            border-bottom-color: #3b82f6;
        }
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
        }
        .items-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        .item-card {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 1.5rem;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        .item-card:hover {
            border-color: #3b82f6;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .item-card.selected {
            border-color: #3b82f6;
            background: #eff6ff;
        }
        .item-title {
            font-size: 1.125rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            color: #1f2937;
        }
        .item-price {
            font-size: 1.25rem;
            font-weight: 800;
            color: #059669;
            margin-bottom: 0.5rem;
        }
        .item-description {
            color: #6b7280;
            font-size: 0.875rem;
            margin-bottom: 1rem;
            line-height: 1.5;
        }
        .item-details {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.875rem;
            color: #6b7280;
            margin-bottom: 1rem;
        }
        .quantity-controls {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .quantity-btn {
            width: 2rem;
            height: 2rem;
            border: 1px solid #d1d5db;
            background: white;
            border-radius: 4px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .quantity-input {
            width: 3rem;
            text-align: center;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            padding: 0.25rem;
        }
        .selected-items {
            background: #f0f9ff;
            border: 1px solid #0ea5e9;
            border-radius: 12px;
            padding: 1.5rem;
            margin: 1rem 0;
        }
        .selected-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem 0;
            border-bottom: 1px solid #e5e7eb;
        }
        .selected-item:last-child {
            border-bottom: none;
        }
        .remove-btn {
            background: #ef4444;
            color: white;
            border: none;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.75rem;
        }
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
            margin-bottom: 1rem;
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
        .form-group select,
        .form-group textarea {
            padding: 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        .summary-card {
            background: #f0f9ff;
            border: 1px solid #0ea5e9;
            border-radius: 12px;
            padding: 1.5rem;
            margin: 1rem 0;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
            padding: 0.5rem 0;
        }
        .summary-total {
            font-weight: 700;
            font-size: 1.25rem;
            color: #1f2937;
            border-top: 2px solid #0ea5e9;
            padding-top: 0.5rem;
        }
        .payment-methods {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
            margin: 1rem 0;
        }
        .payment-method {
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            padding: 1rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .payment-method:hover {
            border-color: #3b82f6;
        }
        .payment-method.selected {
            border-color: #3b82f6;
            background: #eff6ff;
        }
        .btn-primary {
            background: linear-gradient(90deg, #3b82f6, #1d4ed8);
            color: white;
            border: none;
            padding: 1rem 2rem;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
            font-size: 1rem;
        }
        .btn-primary:hover {
            background: linear-gradient(90deg, #2563eb, #1e40af);
            transform: translateY(-1px);
        }
        .btn-primary:disabled {
            background: #9ca3af;
            cursor: not-allowed;
            transform: none;
        }
        .status-message {
            padding: 1rem;
            border-radius: 8px;
            margin: 1rem 0;
            font-weight: 600;
        }
        .status-success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }
        .status-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fca5a5;
        }
        .status-info {
            background: #dbeafe;
            color: #1e40af;
            border: 1px solid #93c5fd;
        }
        .no-items {
            text-align: center;
            padding: 2rem;
            color: #6b7280;
            background: #f9fafb;
            border-radius: 12px;
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
                <a href="bookings.php" class="active">Bookings</a>
                <a href="about.php">About</a>
                <a href="contact.php">Contact</a>
                <a href="admin/login.php">Admin</a>
            </nav>
        </div>
    </header>

    <div class="page-header">
        <div class="container">
            <h1>Make a Booking</h1>
            <p>Select your accommodation, activities, or events and complete your booking</p>
        </div>
    </div>

    <main class="container">
        <div class="booking-container">
            <!-- Step 1: Select Items -->
            <div class="booking-step">
                <div class="step-header">
                    <div class="step-number">1</div>
                    <div class="step-title">Select Items</div>
                </div>
                
                <div class="selection-tabs">
                    <button class="tab-btn active" onclick="showTab('rooms')">Rooms</button>
                    <button class="tab-btn" onclick="showTab('activities')">Activities</button>
                    <button class="tab-btn" onclick="showTab('events')">Events</button>
                </div>
                
                <!-- Rooms Tab -->
                <div id="rooms-tab" class="tab-content active">
                    <div class="items-grid">
                        <?php foreach ($rooms as $room): ?>
                        <div class="item-card" data-type="room" data-id="<?php echo $room['id']; ?>" 
                             data-title="<?php echo htmlspecialchars($room['title']); ?>" 
                             data-price="<?php echo $room['price']; ?>">
                            <div class="item-title"><?php echo htmlspecialchars($room['title']); ?></div>
                            <div class="item-price">$<?php echo number_format($room['price'], 2); ?>/night</div>
                            <div class="item-description"><?php echo htmlspecialchars($room['amenities'] ?? 'Standard amenities'); ?></div>
                            <div class="item-details">
                                <span>Max <?php echo $room['max_occupancy']; ?> guests</span>
                                <div class="quantity-controls">
                                    <button class="quantity-btn" onclick="changeQuantity(this, -1)">-</button>
                                    <input type="number" class="quantity-input" value="1" min="1" max="10">
                                    <button class="quantity-btn" onclick="changeQuantity(this, 1)">+</button>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <!-- Activities Tab -->
                <div id="activities-tab" class="tab-content">
                    <div class="items-grid">
                        <?php foreach ($activities as $activity): ?>
                        <div class="item-card" data-type="activity" data-id="<?php echo $activity['id']; ?>" 
                             data-title="<?php echo htmlspecialchars($activity['title']); ?>" 
                             data-price="<?php echo $activity['price']; ?>">
                            <div class="item-title"><?php echo htmlspecialchars($activity['title']); ?></div>
                            <div class="item-price">$<?php echo number_format($activity['price'], 2); ?></div>
                            <div class="item-description"><?php echo htmlspecialchars($activity['description'] ?? 'Exciting activity'); ?></div>
                            <div class="item-details">
                                <span><?php echo htmlspecialchars($activity['duration'] ?? 'Flexible'); ?></span>
                                <div class="quantity-controls">
                                    <button class="quantity-btn" onclick="changeQuantity(this, -1)">-</button>
                                    <input type="number" class="quantity-input" value="1" min="1" max="10">
                                    <button class="quantity-btn" onclick="changeQuantity(this, 1)">+</button>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <!-- Events Tab -->
                <div id="events-tab" class="tab-content">
                    <div class="items-grid">
                        <?php foreach ($events as $event): ?>
                        <div class="item-card" data-type="event" data-id="<?php echo $event['id']; ?>" 
                             data-title="<?php echo htmlspecialchars($event['title']); ?>" 
                             data-price="<?php echo $event['price']; ?>">
                            <div class="item-title"><?php echo htmlspecialchars($event['title']); ?></div>
                            <div class="item-price">$<?php echo number_format($event['price'], 2); ?></div>
                            <div class="item-description"><?php echo htmlspecialchars($event['description'] ?? 'Special event'); ?></div>
                            <div class="item-details">
                                <span>Capacity: <?php echo $event['capacity']; ?> people</span>
                                <div class="quantity-controls">
                                    <button class="quantity-btn" onclick="changeQuantity(this, -1)">-</button>
                                    <input type="number" class="quantity-input" value="1" min="1" max="10">
                                    <button class="quantity-btn" onclick="changeQuantity(this, 1)">+</button>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <div class="selected-items" id="selectedItems" style="display: none;">
                    <h4>Selected Items</h4>
                    <div id="selectedItemsList"></div>
                </div>
            </div>

            <!-- Step 2: Customer Details -->
            <div class="booking-step">
                <div class="step-header">
                    <div class="step-number">2</div>
                    <div class="step-title">Your Details</div>
                </div>
                
                <?php if ($user_details): ?>
                <div class="user-info-card">
                    <div class="user-info-header">
                        <i class="fas fa-user-circle"></i>
                        <h3>Welcome back, <?php echo htmlspecialchars($user_details['name']); ?>!</h3>
                    </div>
                    <div class="user-info-details">
                        <p><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($user_details['email']); ?></p>
                        <?php if ($user_details['phone']): ?>
                        <p><i class="fas fa-phone"></i> <?php echo htmlspecialchars($user_details['phone']); ?></p>
                        <?php endif; ?>
                        <p><i class="fas fa-calendar"></i> Member since <?php echo date('F Y', strtotime($user_details['created_at'])); ?></p>
                    </div>
                </div>
                <?php endif; ?>
                <form id="customerForm">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="fullname">Full Name *</label>
                            <input type="text" id="fullname" name="fullname" 
                                   value="<?php echo htmlspecialchars($user_details['name'] ?? ''); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email Address *</label>
                            <input type="email" id="email" name="email" 
                                   value="<?php echo htmlspecialchars($user_details['email'] ?? ''); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="phone">Phone Number</label>
                            <input type="tel" id="phone" name="phone" 
                                   value="<?php echo htmlspecialchars($user_details['phone'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label for="guests">Number of Guests</label>
                            <input type="number" id="guests" name="guests" min="1" value="2">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="check_in_date">Check-in Date (for rooms)</label>
                        <input type="date" id="check_in_date" name="check_in_date">
                    </div>
                    <div class="form-group">
                        <label for="check_out_date">Check-out Date (for rooms)</label>
                        <input type="date" id="check_out_date" name="check_out_date">
                    </div>
                    <div class="form-group">
                        <label for="special_requests">Special Requests</label>
                        <textarea id="special_requests" name="special_requests" rows="3" 
                                  placeholder="Any special requests or requirements..."></textarea>
                    </div>
                </form>
            </div>

            <!-- Step 3: Payment -->
            <div class="booking-step">
                <div class="step-header">
                    <div class="step-number">3</div>
                    <div class="step-title">Payment Information</div>
                </div>
                
                <div class="summary-card">
                    <h4>Booking Summary</h4>
                    <div id="bookingSummary">
                        <div class="summary-row">
                            <span>Subtotal:</span>
                            <span id="subtotal">$0.00</span>
                        </div>
                        <div class="summary-row">
                            <span>Deposit (20%):</span>
                            <span id="deposit">$0.00</span>
                        </div>
                        <div class="summary-row summary-total">
                            <span>Total Amount:</span>
                            <span id="total">$0.00</span>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Payment Method</label>
                    <div class="payment-methods">
                        <div class="payment-method" data-method="ecocash">
                            <div style="font-weight: 600;">EcoCash</div>
                            <div style="font-size: 0.875rem; color: #6b7280;">Mobile Money</div>
                        </div>
                        <div class="payment-method" data-method="paynow">
                            <div style="font-weight: 600;">Paynow</div>
                            <div style="font-size: 0.875rem; color: #6b7280;">Online Banking</div>
                        </div>
                        <div class="payment-method" data-method="paypal">
                            <div style="font-weight: 600;">PayPal</div>
                            <div style="font-size: 0.875rem; color: #6b7280;">International</div>
                        </div>
                        <div class="payment-method" data-method="bank">
                            <div style="font-weight: 600;">Bank Transfer</div>
                            <div style="font-size: 0.875rem; color: #6b7280;">Direct Transfer</div>
                        </div>
                    </div>
                </div>

                <button class="btn-primary" onclick="submitBooking()">
                    Complete Booking
                </button>
                
                <div id="bookingStatus"></div>
            </div>
        </div>
    </main>

    <footer class="site-footer container">© <span id="year"></span> Mazvikadei Resort</footer>
    
    <script>
        let selectedItems = [];
        let selectedPaymentMethod = null;

        // Load user details and populate form
        async function loadUserDetails() {
            try {
                const response = await fetch('php/api/user_details.php');
                const data = await response.json();
                
                if (data.user) {
                    // Update form fields with user data
                    document.getElementById('fullname').value = data.user.name || '';
                    document.getElementById('email').value = data.user.email || '';
                    document.getElementById('phone').value = data.user.phone || '';
                    
                    // Update user info card if it exists
                    const userInfoCard = document.querySelector('.user-info-card');
                    if (userInfoCard) {
                        const welcomeText = userInfoCard.querySelector('h3');
                        if (welcomeText) {
                            welcomeText.textContent = `Welcome back, ${data.user.name}!`;
                        }
                    }
                }
            } catch (error) {
                console.error('Error loading user details:', error);
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            loadSelectedItems();
            loadUserDetails();
            setupPaymentMethods();
            setupItemSelection();
            setDefaultDates();
        });

        function loadSelectedItems() {
            const pendingBooking = localStorage.getItem('pendingBooking');
            if (pendingBooking) {
                const bookingData = JSON.parse(pendingBooking);
                selectedItems = bookingData.items || [];
                displaySelectedItems();
                calculateTotal();
            }
        }

        function setupItemSelection() {
            document.querySelectorAll('.item-card').forEach(card => {
                card.addEventListener('click', function() {
                    this.classList.toggle('selected');
                    updateSelectedItems();
                });
            });
        }

        function updateSelectedItems() {
            selectedItems = [];
            document.querySelectorAll('.item-card.selected').forEach(card => {
                const quantity = parseInt(card.querySelector('.quantity-input').value);
                if (quantity > 0) {
                    selectedItems.push({
                        id: card.dataset.id,
                        type: card.dataset.type,
                        title: card.dataset.title,
                        price: parseFloat(card.dataset.price),
                        quantity: quantity
                    });
                }
            });
            displaySelectedItems();
            calculateTotal();
        }

        function displaySelectedItems() {
            const container = document.getElementById('selectedItems');
            const list = document.getElementById('selectedItemsList');
            
            if (selectedItems.length === 0) {
                container.style.display = 'none';
                return;
            }
            
            container.style.display = 'block';
            
            const itemsHTML = selectedItems.map((item, index) => `
                <div class="selected-item">
                    <div>
                        <div style="font-weight: 600;">${item.title}</div>
                        <div style="font-size: 0.875rem; color: #6b7280;">Qty: ${item.quantity}</div>
                    </div>
                    <div style="display: flex; align-items: center; gap: 1rem;">
                        <span style="font-weight: 600; color: #059669;">$${(item.price * item.quantity).toFixed(2)}</span>
                        <button class="remove-btn" onclick="removeSelectedItem(${index})">Remove</button>
                    </div>
                </div>
            `).join('');
            
            list.innerHTML = itemsHTML;
        }

        function removeSelectedItem(index) {
            selectedItems.splice(index, 1);
            displaySelectedItems();
            calculateTotal();
            
            // Update card selection
            document.querySelectorAll('.item-card').forEach(card => {
                const cardId = card.dataset.id;
                const cardType = card.dataset.type;
                const isSelected = selectedItems.some(item => item.id == cardId && item.type == cardType);
                card.classList.toggle('selected', isSelected);
            });
        }

        function calculateTotal() {
            const subtotal = selectedItems.reduce((sum, item) => {
                return sum + (item.price * item.quantity);
            }, 0);
            
            const deposit = subtotal * 0.2;
            const total = subtotal;
            
            document.getElementById('subtotal').textContent = `$${subtotal.toFixed(2)}`;
            document.getElementById('deposit').textContent = `$${deposit.toFixed(2)}`;
            document.getElementById('total').textContent = `$${total.toFixed(2)}`;
        }

        function changeQuantity(button, change) {
            const input = button.parentElement.querySelector('.quantity-input');
            const newValue = parseInt(input.value) + change;
            if (newValue >= 1 && newValue <= 10) {
                input.value = newValue;
                updateSelectedItems();
            }
        }

        function showTab(tabName) {
            // Hide all tabs
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            
            // Show selected tab
            document.getElementById(tabName + '-tab').classList.add('active');
            event.target.classList.add('active');
        }

        function setupPaymentMethods() {
            const paymentMethods = document.querySelectorAll('.payment-method');
            paymentMethods.forEach(method => {
                method.addEventListener('click', function() {
                    paymentMethods.forEach(m => m.classList.remove('selected'));
                    this.classList.add('selected');
                    selectedPaymentMethod = this.dataset.method;
                });
            });
        }

        function setDefaultDates() {
            const today = new Date();
            const tomorrow = new Date(today);
            tomorrow.setDate(tomorrow.getDate() + 1);
            
            document.getElementById('check_in_date').value = today.toISOString().split('T')[0];
            document.getElementById('check_out_date').value = tomorrow.toISOString().split('T')[0];
        }

        async function submitBooking() {
            // Validate form
            const form = document.getElementById('customerForm');
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }
            
            if (selectedItems.length === 0) {
                showStatus('Please select at least one item to book.', 'error');
                return;
            }
            
            if (!selectedPaymentMethod) {
                showStatus('Please select a payment method.', 'error');
                return;
            }
            
            // Get form data
            const formData = new FormData(form);
            const customer = {
                fullname: formData.get('fullname'),
                email: formData.get('email'),
                phone: formData.get('phone')
            };
            
            const bookingData = {
                type: selectedItems[0].type, // Use first item's type
                items: selectedItems,
                customer: customer,
                special_requests: formData.get('special_requests'),
                payment_method: selectedPaymentMethod,
                check_in_date: formData.get('check_in_date'),
                check_out_date: formData.get('check_out_date')
            };
            
            showStatus('Processing your booking...', 'info');
            
            try {
                const response = await fetch('php/enhanced_book.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(bookingData)
                });
                
                const result = await response.json();
                
                if (result.ok) {
                    showStatus(`Booking successful! Your booking reference is: ${result.booking_reference}`, 'success');
                    
                    // Clear localStorage
                    localStorage.removeItem('pendingBooking');
                    selectedItems = [];
                    
                    // Redirect to confirmation page after 3 seconds
                    setTimeout(() => {
                        window.location.href = 'booking_confirmation.php?ref=' + result.booking_reference;
                    }, 3000);
                } else {
                    showStatus(`Booking failed: ${result.message || 'Unknown error'}`, 'error');
                }
            } catch (error) {
                console.error('Booking error:', error);
                showStatus('Booking failed. Please try again.', 'error');
            }
        }

        function showStatus(message, type) {
            const statusDiv = document.getElementById('bookingStatus');
            statusDiv.innerHTML = `<div class="status-message status-${type}">${message}</div>`;
        }
        
        // Set current year
        document.getElementById('year').textContent = new Date().getFullYear();
    </script>
</body>
</html>
