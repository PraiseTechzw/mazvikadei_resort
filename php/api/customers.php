<?php
require_once __DIR__ . '/../config.php';
header('Content-Type: application/json; charset=utf-8');

$method = $_SERVER['REQUEST_METHOD'];

try {
    $pdo = getPDO();
    
    switch ($method) {
        case 'GET':
            // Get customers with optional filtering
            $customer_id = $_GET['id'] ?? null;
            $status = $_GET['status'] ?? null;
            $role = $_GET['role'] ?? null;
            $limit = $_GET['limit'] ?? 50;
            $offset = $_GET['offset'] ?? 0;
            
            if ($customer_id) {
                // Get specific customer with bookings
                $stmt = $pdo->prepare("
                    SELECT u.*, 
                           COUNT(b.id) as booking_count,
                           COALESCE(SUM(b.total_amount), 0) as total_spent
                    FROM users u 
                    LEFT JOIN bookings b ON u.id = b.customer_id 
                    WHERE u.id = ? AND u.role = 'customer'
                    GROUP BY u.id
                ");
                $stmt->execute([$customer_id]);
                $customer = $stmt->fetch();
                
                if (!$customer) {
                    http_response_code(404);
                    echo json_encode(['error' => 'customer_not_found']);
                    exit;
                }
                
                // Get customer bookings
                $stmt = $pdo->prepare("
                    SELECT * FROM bookings 
                    WHERE customer_id = ? 
                    ORDER BY created_at DESC 
                    LIMIT 10
                ");
                $stmt->execute([$customer_id]);
                $bookings = $stmt->fetchAll();
                
                echo json_encode([
                    'customer' => $customer,
                    'bookings' => $bookings
                ]);
            } else {
                // Get all customers
                $sql = "SELECT u.*, 
                               COUNT(b.id) as booking_count,
                               COALESCE(SUM(b.total_amount), 0) as total_spent
                        FROM users u 
                        LEFT JOIN bookings b ON u.id = b.customer_id 
                        WHERE u.role = 'customer'";
                $params = [];
                
                if ($status) {
                    $sql .= " AND u.status = ?";
                    $params[] = $status;
                }
                
                if ($role) {
                    $sql .= " AND u.role = ?";
                    $params[] = $role;
                }
                
                $sql .= " GROUP BY u.id ORDER BY u.created_at DESC LIMIT ? OFFSET ?";
                $params[] = (int)$limit;
                $params[] = (int)$offset;
                
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                $customers = $stmt->fetchAll();
                
                // Get total count
                $count_sql = "SELECT COUNT(*) as total FROM users WHERE role = 'customer'";
                $count_params = [];
                
                if ($status) {
                    $count_sql .= " AND status = ?";
                    $count_params[] = $status;
                }
                
                if ($role) {
                    $count_sql .= " AND role = ?";
                    $count_params[] = $role;
                }
                
                $stmt = $pdo->prepare($count_sql);
                $stmt->execute($count_params);
                $total = $stmt->fetch()['total'];
                
                echo json_encode([
                    'customers' => $customers,
                    'total' => $total,
                    'limit' => (int)$limit,
                    'offset' => (int)$offset
                ]);
            }
            break;
            
        case 'PUT':
            // Update customer status
            $input = json_decode(file_get_contents('php://input'), true);
            $customer_id = $input['customer_id'] ?? null;
            $status = $input['status'] ?? null;
            
            if (!$customer_id || !$status) {
                http_response_code(400);
                echo json_encode(['error' => 'missing_fields']);
                exit;
            }
            
            $stmt = $pdo->prepare("UPDATE users SET status = ?, updated_at = NOW() WHERE id = ? AND role = 'customer'");
            $stmt->execute([$status, $customer_id]);
            
            if ($stmt->rowCount() > 0) {
                echo json_encode(['ok' => true, 'message' => 'Customer status updated successfully']);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'customer_not_found']);
            }
            break;
            
        case 'DELETE':
            // Delete customer (soft delete by setting status to suspended)
            $input = json_decode(file_get_contents('php://input'), true);
            $customer_id = $input['customer_id'] ?? null;
            
            if (!$customer_id) {
                http_response_code(400);
                echo json_encode(['error' => 'missing_customer_id']);
                exit;
            }
            
            $stmt = $pdo->prepare("UPDATE users SET status = 'suspended', updated_at = NOW() WHERE id = ? AND role = 'customer'");
            $stmt->execute([$customer_id]);
            
            if ($stmt->rowCount() > 0) {
                echo json_encode(['ok' => true, 'message' => 'Customer suspended successfully']);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'customer_not_found']);
            }
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['error' => 'method_not_allowed']);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'db', 'message' => $e->getMessage()]);
}
?>
