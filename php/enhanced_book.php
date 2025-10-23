<?php
require_once __DIR__ . '/config.php';
header('Content-Type: application/json; charset=utf-8');

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) { 
    http_response_code(400); 
    echo json_encode(['error'=>'no_input']); 
    exit; 
}

$type = $input['type'] ?? 'room'; // room|activity|event
$items = $input['items'] ?? null;
$customer = $input['customer'] ?? null;
$extras = $input['extras'] ?? '';
$special_requests = $input['special_requests'] ?? '';
$check_in_date = $input['check_in_date'] ?? null;
$check_out_date = $input['check_out_date'] ?? null;
$attachment = null;

// Validation
if (!$items || !$customer || empty($customer['fullname']) || empty($customer['email'])){
    http_response_code(400); 
    echo json_encode(['error'=>'missing_fields']); 
    exit;
}

// Handle uploaded file if present (base64 from client)
if (!empty($input['attachment_base64'])) {
    $data = $input['attachment_base64'];
    if (preg_match('/^data:(.*);base64,(.*)$/', $data, $m)) {
        $ext = explode('/', $m[1])[1] ?? 'bin';
        $decoded = base64_decode($m[2]);
        $fname = uniqid('attach_') . '.' . $ext;
        $path = UPLOAD_DIR . '/' . $fname;
        if (!is_dir(UPLOAD_DIR)) {
            mkdir(UPLOAD_DIR, 0755, true);
        }
        file_put_contents($path, $decoded);
        $attachment = 'uploads/' . $fname;
    }
}

try {
    $pdo = getPDO();
    
    // Generate unique booking reference
    $booking_reference = 'MZ' . date('Y') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
    
    // Calculate total amount
    $total_amount = 0;
    foreach ($items as $item) {
        $total_amount += ($item['price'] ?? 0) * ($item['quantity'] ?? 1);
    }
    
    // Calculate deposit (20% of total)
    $deposit_amount = $total_amount * 0.2;
    $balance_amount = $total_amount - $deposit_amount;
    
    // Check if customer exists, if not create them
    $customer_id = null;
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
    $stmt->execute([$customer['email']]);
    $existing_customer = $stmt->fetch();
    
    if ($existing_customer) {
        $customer_id = $existing_customer['id'];
    } else {
        // Create new customer
        $stmt = $pdo->prepare('INSERT INTO users (email, name, phone, role) VALUES (?, ?, ?, ?)');
        $stmt->execute([
            $customer['email'],
            $customer['fullname'],
            $customer['phone'] ?? '',
            'customer'
        ]);
        $customer_id = $pdo->lastInsertId();
    }
    
    // Insert booking
    $stmt = $pdo->prepare('
        INSERT INTO bookings (
            booking_reference, type, items_json, customer_id, customer_name, 
            customer_email, customer_phone, check_in_date, check_out_date,
            total_amount, deposit_amount, balance_amount, extras, special_requests, 
            attachment, status
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ');
    
    $stmt->execute([
        $booking_reference, $type, json_encode($items, JSON_UNESCAPED_UNICODE), 
        $customer_id, $customer['fullname'], $customer['email'], 
        $customer['phone'] ?? '', $check_in_date, $check_out_date,
        $total_amount, $deposit_amount, $balance_amount, $extras, 
        $special_requests, $attachment, 'pending'
    ]);
    
    $booking_id = $pdo->lastInsertId();
    
    // Update room availability if it's a room booking
    if ($type === 'room' && $check_in_date && $check_out_date) {
        foreach ($items as $item) {
            if (isset($item['room_id'])) {
                $start_date = new DateTime($check_in_date);
                $end_date = new DateTime($check_out_date);
                
                while ($start_date < $end_date) {
                    $stmt = $pdo->prepare('
                        INSERT INTO room_availability (room_id, date, status) 
                        VALUES (?, ?, ?) 
                        ON DUPLICATE KEY UPDATE status = ?
                    ');
                    $stmt->execute([
                        $item['room_id'], 
                        $start_date->format('Y-m-d'), 
                        'booked', 
                        'booked'
                    ]);
                    $start_date->add(new DateInterval('P1D'));
                }
            }
        }
    }
    
    // Send email notification
    $to = $customer['email'];
    $subject = 'Mazvikadei Resort - Booking Received #' . $booking_reference;
    $message = "Dear " . $customer['fullname'] . ",\n\n";
    $message .= "Thank you for your booking request (#$booking_reference).\n";
    $message .= "Total Amount: $" . number_format($total_amount, 2) . "\n";
    $message .= "Deposit Required: $" . number_format($deposit_amount, 2) . "\n";
    $message .= "Balance: $" . number_format($balance_amount, 2) . "\n\n";
    $message .= "We will confirm your booking shortly.\n\n";
    $message .= "Best regards,\nMazvikadei Resort Team";
    
    @mail($to, $subject, $message);
    
    echo json_encode([
        'ok' => true,
        'booking_id' => $booking_id,
        'booking_reference' => $booking_reference,
        'total_amount' => $total_amount,
        'deposit_amount' => $deposit_amount
    ]);
    
} catch (Exception $e){
    http_response_code(500);
    echo json_encode(['error'=>'db','message'=>$e->getMessage()]);
}
?>
