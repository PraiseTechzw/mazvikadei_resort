<?php
require_once __DIR__ . '/../config.php';
header('Content-Type: application/json; charset=utf-8');

$method = $_SERVER['REQUEST_METHOD'];

try {
    $pdo = getPDO();
    
    switch ($method) {
        case 'POST':
            // Submit contact message
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input || empty($input['name']) || empty($input['email']) || empty($input['message'])) {
                http_response_code(400);
                echo json_encode(['error'=>'missing_fields']);
                exit;
            }
            
            $stmt = $pdo->prepare("
                INSERT INTO contact_messages (name, email, phone, subject, message) 
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $input['name'],
                $input['email'],
                $input['phone'] ?? '',
                $input['subject'] ?? '',
                $input['message']
            ]);
            
            echo json_encode(['ok' => true, 'message' => 'Message sent successfully']);
            break;
            
        case 'GET':
            // Get contact messages (admin only)
            session_start();
            if (!isset($_SESSION['user_id'])) {
                http_response_code(401);
                echo json_encode(['error'=>'unauthorized']);
                exit;
            }
            
            $status = $_GET['status'] ?? null;
            $limit = $_GET['limit'] ?? 50;
            $offset = $_GET['offset'] ?? 0;
            
            $sql = "SELECT * FROM contact_messages WHERE 1=1";
            $params = [];
            
            if ($status) {
                $sql .= " AND status = ?";
                $params[] = $status;
            }
            
            $sql .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
            $params[] = (int)$limit;
            $params[] = (int)$offset;
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $messages = $stmt->fetchAll();
            
            echo json_encode(['messages' => $messages]);
            break;
            
        case 'PUT':
            // Update message status
            session_start();
            if (!isset($_SESSION['user_id'])) {
                http_response_code(401);
                echo json_encode(['error'=>'unauthorized']);
                exit;
            }
            
            $input = json_decode(file_get_contents('php://input'), true);
            $message_id = $input['message_id'] ?? null;
            $status = $input['status'] ?? null;
            
            if (!$message_id || !$status) {
                http_response_code(400);
                echo json_encode(['error'=>'missing_fields']);
                exit;
            }
            
            $stmt = $pdo->prepare("UPDATE contact_messages SET status = ? WHERE id = ?");
            $stmt->execute([$status, $message_id]);
            
            if ($stmt->rowCount() > 0) {
                echo json_encode(['ok' => true, 'message' => 'Message status updated']);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'message_not_found']);
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
