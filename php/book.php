<?php
require_once __DIR__ . '/config.php';
header('Content-Type: application/json; charset=utf-8');

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) { http_response_code(400); echo json_encode(['error'=>'no_input']); exit; }

$type = $input['type'] ?? 'room'; // room|activity|event
$items = $input['items'] ?? null;
$customer = $input['customer'] ?? null;
$extras = $input['extras'] ?? '';
$attachment = null;

if (!$items || !$customer || empty($customer['fullname']) || empty($customer['email'])){
    http_response_code(400); echo json_encode(['error'=>'missing_fields']); exit;
}

// handle uploaded file if present (base64 from client) - OPTIONAL
if (!empty($input['attachment_base64'])) {
    $data = $input['attachment_base64'];
    if (preg_match('/^data:(.*);base64,(.*)$/', $data, $m)) {
        $ext = explode('/', $m[1])[1] ?? 'bin';
        $decoded = base64_decode($m[2]);
        $fname = uniqid('attach_') . '.' . $ext;
        $path = UPLOAD_DIR . '/' . $fname;
        file_put_contents($path, $decoded);
        $attachment = 'uploads/' . $fname;
    }
}

try {
    $pdo = getPDO();
    $id = time();
    $stmt = $pdo->prepare('INSERT INTO bookings (id, type, items_json, customer_name, customer_email, customer_phone, extras, attachment) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
    $stmt->execute([ $id, $type, json_encode($items, JSON_UNESCAPED_UNICODE), $customer['fullname'], $customer['email'], $customer['phone'] ?? '', $extras, $attachment ]);

    // send simple email notification (placeholder)
    $to = $customer['email'];
    $subject = 'Mazvikadei Resort - Booking Received #' . $id;
    $message = "Thank you " . $customer['fullname'] . "\nYour booking request (#$id) has been received. We'll confirm shortly.";
    // In production use SMTP or PHPMailer
    @mail($to, $subject, $message);

    echo json_encode(['ok'=>true,'id'=>$id]);
} catch (Exception $e){
    http_response_code(500);
    echo json_encode(['error'=>'db','message'=>$e->getMessage()]);
}
?>