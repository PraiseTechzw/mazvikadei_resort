<?php
require_once '../php/config.php';
session_start();
header('Content-Type: application/json; charset=utf-8');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'unauthorized', 'message' => 'Please login to process payment']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'method_not_allowed', 'message' => 'Only POST method allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    http_response_code(400);
    echo json_encode(['error' => 'invalid_input', 'message' => 'Invalid JSON input']);
    exit;
}

$booking_id = $input['booking_id'] ?? null;
$payment_method = $input['payment_method'] ?? null;
$amount = $input['amount'] ?? null;

if (!$booking_id || !$payment_method || !$amount) {
    http_response_code(400);
    echo json_encode(['error' => 'missing_fields', 'message' => 'Booking ID, payment method, and amount are required']);
    exit;
}

try {
    $pdo = getPDO();
    
    // Get booking details
    $stmt = $pdo->prepare("SELECT * FROM bookings WHERE id = ? AND customer_id = ?");
    $stmt->execute([$booking_id, $_SESSION['user_id']]);
    $booking = $stmt->fetch();
    
    if (!$booking) {
        http_response_code(404);
        echo json_encode(['error' => 'booking_not_found', 'message' => 'Booking not found']);
        exit;
    }
    
    // Check if booking is already paid
    if ($booking['payment_status'] === 'paid') {
        http_response_code(400);
        echo json_encode(['error' => 'already_paid', 'message' => 'Booking is already paid']);
        exit;
    }
    
    // Generate payment reference
    $payment_reference = 'PAY_' . date('Ymd') . '_' . strtoupper(substr(uniqid(), -8));
    $transaction_id = 'TXN_' . time() . '_' . rand(1000, 9999);
    
    // Simulate payment processing (in real implementation, integrate with payment gateway)
    $payment_successful = true; // Simulate successful payment
    
    if ($payment_successful) {
        // Start transaction
        $pdo->beginTransaction();
        
        try {
            // Update booking status
            $stmt = $pdo->prepare("
                UPDATE bookings 
                SET status = 'confirmed', 
                    payment_status = 'paid',
                    updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$booking_id]);
            
            // Insert payment record
            $stmt = $pdo->prepare("
                INSERT INTO payments (
                    booking_id, amount, payment_method, payment_reference, 
                    status, transaction_id, processed_at, created_at
                ) VALUES (?, ?, ?, ?, 'completed', ?, NOW(), NOW())
            ");
            $stmt->execute([
                $booking_id, 
                $amount, 
                $payment_method, 
                $payment_reference, 
                $transaction_id
            ]);
            
            // Log admin activity
            $stmt = $pdo->prepare("
                INSERT INTO admin_logs (admin_id, action, description, created_at) 
                VALUES (?, 'payment_processed', ?, NOW())
            ");
            $stmt->execute([
                $_SESSION['user_id'], 
                "Payment processed for booking #{$booking['booking_reference']} - {$payment_method} - $" . number_format($amount, 2)
            ]);
            
            // Commit transaction
            $pdo->commit();
            
            // Send confirmation email (placeholder)
            $email_sent = sendPaymentConfirmationEmail($booking, $payment_reference, $amount);
            
            echo json_encode([
                'success' => true,
                'message' => 'Payment processed successfully',
                'payment_reference' => $payment_reference,
                'transaction_id' => $transaction_id,
                'booking_reference' => $booking['booking_reference'],
                'amount_paid' => $amount,
                'payment_method' => $payment_method,
                'email_sent' => $email_sent
            ]);
            
        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
        
    } else {
        // Payment failed
        $stmt = $pdo->prepare("
            INSERT INTO payments (
                booking_id, amount, payment_method, payment_reference, 
                status, transaction_id, created_at
            ) VALUES (?, ?, ?, ?, 'failed', ?, NOW())
        ");
        $stmt->execute([
            $booking_id, 
            $amount, 
            $payment_method, 
            $payment_reference, 
            $transaction_id
        ]);
        
        http_response_code(400);
        echo json_encode(['error' => 'payment_failed', 'message' => 'Payment processing failed']);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'database_error', 'message' => $e->getMessage()]);
}

function sendPaymentConfirmationEmail($booking, $payment_reference, $amount) {
    // Placeholder for email sending
    // In production, use PHPMailer or similar
    $to = $booking['customer_email'];
    $subject = "Payment Confirmation - {$booking['booking_reference']}";
    $message = "
    Dear {$booking['customer_name']},
    
    Your payment has been processed successfully!
    
    Booking Reference: {$booking['booking_reference']}
    Payment Reference: {$payment_reference}
    Amount Paid: $" . number_format($amount, 2) . "
    
    Your booking is now confirmed.
    
    Thank you for choosing Mazvikadei Resort!
    
    Best regards,
    Mazvikadei Resort Team
    ";
    
    // For demo purposes, just return true
    // In production, use proper email sending
    return true;
}
?>
