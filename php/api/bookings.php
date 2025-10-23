<?php
require_once __DIR__ . '/../config.php';
header('Content-Type: application/json; charset=utf-8');

$method = $_SERVER['REQUEST_METHOD'];

try {
    $pdo = getPDO();
    
    switch ($method) {
        case 'GET':
            // Get bookings with filters
            $status = $_GET['status'] ?? null;
            $type = $_GET['type'] ?? null;
            $limit = $_GET['limit'] ?? 50;
            $offset = $_GET['offset'] ?? 0;
            
            $sql = "SELECT b.*, u.name as customer_name 
                    FROM bookings b 
                    LEFT JOIN users u ON b.customer_id = u.id 
                    WHERE 1=1";
            $params = [];
            
            if ($status) {
                $sql .= " AND b.status = ?";
                $params[] = $status;
            }
            
            if ($type) {
                $sql .= " AND b.type = ?";
                $params[] = $type;
            }
            
            $sql .= " ORDER BY b.created_at DESC LIMIT ? OFFSET ?";
            $params[] = (int)$limit;
            $params[] = (int)$offset;
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $bookings = $stmt->fetchAll();
            
            // Get total count
            $count_sql = "SELECT COUNT(*) as total FROM bookings";
            $count_params = [];
            if ($status) {
                $count_sql .= " WHERE status = ?";
                $count_params[] = $status;
            }
            if ($type) {
                $count_sql .= ($status ? " AND" : " WHERE") . " type = ?";
                $count_params[] = $type;
            }
            
            $stmt = $pdo->prepare($count_sql);
            $stmt->execute($count_params);
            $total = $stmt->fetch()['total'];
            
            echo json_encode([
                'bookings' => $bookings,
                'total' => $total,
                'limit' => (int)$limit,
                'offset' => (int)$offset
            ]);
            break;
            
        case 'PUT':
            // Update booking status
            $input = json_decode(file_get_contents('php://input'), true);
            $booking_id = $input['booking_id'] ?? null;
            $status = $input['status'] ?? null;
            $notes = $input['notes'] ?? null;
            
            if (!$booking_id || !$status) {
                http_response_code(400);
                echo json_encode(['error'=>'missing_fields']);
                exit;
            }
            
            $sql = "UPDATE bookings SET status = ?, updated_at = NOW()";
            $params = [$status];
            
            if ($notes) {
                $sql .= ", extras = CONCAT(IFNULL(extras, ''), '\n', ?)";
                $params[] = $notes;
            }
            
            $sql .= " WHERE id = ?";
            $params[] = $booking_id;
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            
            if ($stmt->rowCount() > 0) {
                echo json_encode(['ok' => true, 'message' => 'Booking updated successfully']);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'booking_not_found']);
            }
            break;
            
        case 'DELETE':
            // Cancel booking
            $input = json_decode(file_get_contents('php://input'), true);
            $booking_id = $input['booking_id'] ?? null;
            $reason = $input['reason'] ?? null;
            
            if (!$booking_id) {
                http_response_code(400);
                echo json_encode(['error'=>'missing_booking_id']);
                exit;
            }
            
            $stmt = $pdo->prepare("
                UPDATE bookings 
                SET status = 'cancelled', 
                    cancellation_reason = ?, 
                    cancelled_at = NOW(),
                    updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$reason, $booking_id]);
            
            if ($stmt->rowCount() > 0) {
                echo json_encode(['ok' => true, 'message' => 'Booking cancelled successfully']);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'booking_not_found']);
            }
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['error'=>'method_not_allowed']);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error'=>'db','message'=>$e->getMessage()]);
}
?>
