<?php
require_once '../php/config.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

$booking_id = $_GET['booking_id'] ?? null;
$payment_method = $_GET['method'] ?? null;

if (!$booking_id || !$payment_method) {
    header('Location: ../bookings.php');
    exit;
}

// Get booking details
try {
    $pdo = getPDO();
    $stmt = $pdo->prepare("SELECT * FROM bookings WHERE id = ? AND customer_id = ?");
    $stmt->execute([$booking_id, $_SESSION['user_id']]);
    $booking = $stmt->fetch();
    
    if (!$booking) {
        header('Location: ../bookings.php');
        exit;
    }
} catch (Exception $e) {
    header('Location: ../bookings.php');
    exit;
}

$payment_methods = [
    'ecocash' => [
        'name' => 'EcoCash',
        'icon' => 'fas fa-mobile-alt',
        'color' => '#00b894',
        'description' => 'Mobile Money Payment',
        'instructions' => 'Send money to +263 77 123 4567'
    ],
    'paynow' => [
        'name' => 'Paynow',
        'icon' => 'fas fa-credit-card',
        'color' => '#0984e3',
        'description' => 'Online Banking',
        'instructions' => 'Use your bank\'s online platform'
    ],
    'paypal' => [
        'name' => 'PayPal',
        'icon' => 'fab fa-paypal',
        'color' => '#003087',
        'description' => 'International Payment',
        'instructions' => 'Login to your PayPal account'
    ],
    'bank' => [
        'name' => 'Bank Transfer',
        'icon' => 'fas fa-university',
        'color' => '#6c5ce7',
        'description' => 'Direct Bank Transfer',
        'instructions' => 'Transfer to our bank account'
    ]
];

$method_info = $payment_methods[$payment_method] ?? null;
if (!$method_info) {
    header('Location: ../bookings.php');
    exit;
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Payment - Mazvikadei Resort</title>
    <link rel="stylesheet" href="../styles.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Inter', sans-serif;
        }
        
        .payment-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 3rem;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 500px;
            animation: slideUp 0.6s ease-out;
        }
        
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .payment-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .payment-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, <?php echo $method_info['color']; ?>, <?php echo $method_info['color']; ?>aa);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
        
        .payment-icon i {
            font-size: 2rem;
            color: white;
        }
        
        .payment-title {
            font-size: 2rem;
            font-weight: 800;
            color: #1f2937;
            margin-bottom: 0.5rem;
        }
        
        .payment-subtitle {
            color: #6b7280;
            font-size: 1rem;
        }
        
        .booking-summary {
            background: #f8fafc;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            border-left: 4px solid <?php echo $method_info['color']; ?>;
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
            padding: 0.5rem 0;
        }
        
        .summary-row:last-child {
            border-top: 1px solid #e5e7eb;
            font-weight: 700;
            font-size: 1.125rem;
            color: #1f2937;
        }
        
        .payment-method-info {
            background: linear-gradient(135deg, <?php echo $method_info['color']; ?>20, <?php echo $method_info['color']; ?>10);
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            border: 1px solid <?php echo $method_info['color']; ?>30;
        }
        
        .method-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
        }
        
        .method-icon {
            width: 50px;
            height: 50px;
            background: <?php echo $method_info['color']; ?>;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
        }
        
        .method-details h3 {
            margin: 0;
            color: #1f2937;
            font-size: 1.25rem;
        }
        
        .method-details p {
            margin: 0;
            color: #6b7280;
            font-size: 0.875rem;
        }
        
        .payment-amount {
            text-align: center;
            margin: 1.5rem 0;
        }
        
        .amount-label {
            color: #6b7280;
            font-size: 0.875rem;
            margin-bottom: 0.5rem;
        }
        
        .amount-value {
            font-size: 3rem;
            font-weight: 800;
            color: #1f2937;
            margin-bottom: 0.5rem;
        }
        
        .amount-currency {
            color: #6b7280;
            font-size: 1rem;
        }
        
        .payment-steps {
            margin-bottom: 2rem;
        }
        
        .step {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            margin-bottom: 0.5rem;
            background: white;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
            transition: all 0.3s ease;
        }
        
        .step.active {
            border-color: <?php echo $method_info['color']; ?>;
            background: <?php echo $method_info['color']; ?>10;
        }
        
        .step.completed {
            border-color: #059669;
            background: #05966910;
        }
        
        .step-number {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.875rem;
        }
        
        .step.active .step-number {
            background: <?php echo $method_info['color']; ?>;
            color: white;
        }
        
        .step.completed .step-number {
            background: #059669;
            color: white;
        }
        
        .step:not(.active):not(.completed) .step-number {
            background: #e5e7eb;
            color: #9ca3af;
        }
        
        .step-content {
            flex: 1;
        }
        
        .step-title {
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 0.25rem;
        }
        
        .step-description {
            color: #6b7280;
            font-size: 0.875rem;
        }
        
        .btn-pay {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, <?php echo $method_info['color']; ?>, <?php echo $method_info['color']; ?>cc);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .btn-pay:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        }
        
        .btn-pay:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }
        
        .btn-pay::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }
        
        .btn-pay:hover::before {
            left: 100%;
        }
        
        .payment-status {
            text-align: center;
            margin-top: 1rem;
            padding: 1rem;
            border-radius: 8px;
            display: none;
        }
        
        .status-processing {
            background: #dbeafe;
            color: #1e40af;
            border: 1px solid #93c5fd;
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
        
        .floating-shapes {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: -1;
        }
        
        .shape {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            animation: float 6s ease-in-out infinite;
        }
        
        .shape:nth-child(1) {
            width: 80px;
            height: 80px;
            top: 20%;
            left: 10%;
            animation-delay: 0s;
        }
        
        .shape:nth-child(2) {
            width: 120px;
            height: 120px;
            top: 60%;
            right: 10%;
            animation-delay: 2s;
        }
        
        .shape:nth-child(3) {
            width: 60px;
            height: 60px;
            bottom: 20%;
            left: 20%;
            animation-delay: 4s;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(180deg); }
        }
        
        .progress-bar {
            width: 100%;
            height: 4px;
            background: #e5e7eb;
            border-radius: 2px;
            overflow: hidden;
            margin-bottom: 2rem;
        }
        
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, <?php echo $method_info['color']; ?>, <?php echo $method_info['color']; ?>cc);
            border-radius: 2px;
            transition: width 0.5s ease;
        }
    </style>
</head>
<body>
    <div class="floating-shapes">
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
    </div>
    
    <div class="payment-container">
        <div class="payment-header">
            <div class="payment-icon">
                <i class="<?php echo $method_info['icon']; ?>"></i>
            </div>
            <h1 class="payment-title">Complete Payment</h1>
            <p class="payment-subtitle">Secure payment with <?php echo $method_info['name']; ?></p>
        </div>
        
        <div class="progress-bar">
            <div class="progress-fill" id="progressFill" style="width: 0%"></div>
        </div>
        
        <div class="booking-summary">
            <h3 style="margin: 0 0 1rem 0; color: #1f2937;">Booking Summary</h3>
            <div class="summary-row">
                <span>Booking Reference:</span>
                <span><?php echo htmlspecialchars($booking['booking_reference']); ?></span>
            </div>
            <div class="summary-row">
                <span>Customer:</span>
                <span><?php echo htmlspecialchars($booking['customer_name']); ?></span>
            </div>
            <div class="summary-row">
                <span>Total Amount:</span>
                <span>$<?php echo number_format($booking['total_amount'], 2); ?></span>
            </div>
        </div>
        
        <div class="payment-amount">
            <div class="amount-label">Amount to Pay</div>
            <div class="amount-value">$<?php echo number_format($booking['total_amount'], 2); ?></div>
            <div class="amount-currency">USD</div>
        </div>
        
        <div class="payment-method-info">
            <div class="method-header">
                <div class="method-icon">
                    <i class="<?php echo $method_info['icon']; ?>"></i>
                </div>
                <div class="method-details">
                    <h3><?php echo $method_info['name']; ?></h3>
                    <p><?php echo $method_info['description']; ?></p>
                </div>
            </div>
            <p style="margin: 0; color: #6b7280; font-size: 0.875rem;">
                <i class="fas fa-info-circle"></i>
                <?php echo $method_info['instructions']; ?>
            </p>
        </div>
        
        <div class="payment-steps">
            <div class="step" id="step1">
                <div class="step-number">1</div>
                <div class="step-content">
                    <div class="step-title">Initiate Payment</div>
                    <div class="step-description">Click the pay button to start</div>
                </div>
            </div>
            
            <div class="step" id="step2">
                <div class="step-number">2</div>
                <div class="step-content">
                    <div class="step-title">Processing</div>
                    <div class="step-description">Payment is being processed</div>
                </div>
            </div>
            
            <div class="step" id="step3">
                <div class="step-number">3</div>
                <div class="step-content">
                    <div class="step-title">Verification</div>
                    <div class="step-description">Verifying payment details</div>
                </div>
            </div>
            
            <div class="step" id="step4">
                <div class="step-number">4</div>
                <div class="step-content">
                    <div class="step-title">Complete</div>
                    <div class="step-description">Payment successful!</div>
                </div>
            </div>
        </div>
        
        <button class="btn-pay" id="payButton" onclick="processPayment()">
            <i class="fas fa-credit-card"></i>
            Pay $<?php echo number_format($booking['total_amount'], 2); ?>
        </button>
        
        <div class="payment-status" id="paymentStatus"></div>
        
        <div style="text-align: center; margin-top: 2rem;">
            <a href="../bookings.php" style="color: #6b7280; text-decoration: none;">
                <i class="fas fa-arrow-left"></i> Back to Bookings
            </a>
        </div>
    </div>
    
    <script>
        let currentStep = 0;
        const steps = ['step1', 'step2', 'step3', 'step4'];
        
        function processPayment() {
            const payButton = document.getElementById('payButton');
            const paymentStatus = document.getElementById('paymentStatus');
            
            // Disable button and show processing
            payButton.disabled = true;
            payButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
            
            // Show processing status
            paymentStatus.className = 'payment-status status-processing';
            paymentStatus.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing your payment...';
            paymentStatus.style.display = 'block';
            
            // Simulate payment process
            simulatePaymentProcess();
        }
        
        function simulatePaymentProcess() {
            const stepInterval = setInterval(() => {
                if (currentStep < steps.length) {
                    // Mark current step as active
                    if (currentStep > 0) {
                        document.getElementById(steps[currentStep - 1]).classList.remove('active');
                        document.getElementById(steps[currentStep - 1]).classList.add('completed');
                    }
                    
                    document.getElementById(steps[currentStep]).classList.add('active');
                    
                    // Update progress bar
                    const progress = ((currentStep + 1) / steps.length) * 100;
                    document.getElementById('progressFill').style.width = progress + '%';
                    
                    currentStep++;
                } else {
                    clearInterval(stepInterval);
                    completePayment();
                }
            }, 1500);
        }
        
        async function completePayment() {
            const paymentStatus = document.getElementById('paymentStatus');
            const payButton = document.getElementById('payButton');
            
            // Mark all steps as completed
            steps.forEach(stepId => {
                const step = document.getElementById(stepId);
                step.classList.remove('active');
                step.classList.add('completed');
            });
            
            // Show processing status
            paymentStatus.className = 'payment-status status-processing';
            paymentStatus.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Finalizing payment...';
            
            try {
                // Process actual payment
                const response = await fetch('process_payment.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        booking_id: <?php echo $booking['id']; ?>,
                        payment_method: '<?php echo $payment_method; ?>',
                        amount: <?php echo $booking['total_amount']; ?>
                    })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    // Show success status
                    paymentStatus.className = 'payment-status status-success';
                    paymentStatus.innerHTML = `
                        <i class="fas fa-check-circle"></i> Payment successful!<br>
                        <small>Reference: ${result.payment_reference}</small>
                    `;
                    
                    // Update button
                    payButton.innerHTML = '<i class="fas fa-check"></i> Payment Complete';
                    payButton.style.background = '#059669';
                    
                    // Redirect to confirmation page
                    setTimeout(() => {
                        window.location.href = '../booking_confirmation.php?ref=<?php echo $booking['booking_reference']; ?>';
                    }, 2000);
                } else {
                    // Show error status
                    paymentStatus.className = 'payment-status status-error';
                    paymentStatus.innerHTML = `<i class="fas fa-exclamation-circle"></i> Payment failed: ${result.message}`;
                    
                    // Reset button
                    payButton.disabled = false;
                    payButton.innerHTML = '<i class="fas fa-credit-card"></i> Try Again';
                    payButton.style.background = '#dc2626';
                }
                
            } catch (error) {
                console.error('Payment processing error:', error);
                
                // Show error status
                paymentStatus.className = 'payment-status status-error';
                paymentStatus.innerHTML = '<i class="fas fa-exclamation-circle"></i> Payment processing failed. Please try again.';
                
                // Reset button
                payButton.disabled = false;
                payButton.innerHTML = '<i class="fas fa-credit-card"></i> Try Again';
                payButton.style.background = '#dc2626';
            }
        }
        
        // Add some interactive effects
        document.querySelectorAll('.step').forEach(step => {
            step.addEventListener('mouseenter', function() {
                if (!this.classList.contains('completed')) {
                    this.style.transform = 'translateX(5px)';
                }
            });
            
            step.addEventListener('mouseleave', function() {
                this.style.transform = 'translateX(0)';
            });
        });
    </script>
</body>
</html>
