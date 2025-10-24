<?php
require_once __DIR__ . '/config.php';
session_start();
header('Content-Type: application/json; charset=utf-8');

$input = json_decode(file_get_contents('php://input'), true);
if (!$input){ http_response_code(400); echo json_encode(['error'=>'no_input']); exit; }

$email = $input['email'] ?? '';
$password = $input['password'] ?? '';

if (!$email || !$password){ http_response_code(400); echo json_encode(['error'=>'missing']); exit; }

try {
    $pdo = getPDO();
    $stmt = $pdo->prepare('SELECT id, email, password_hash, name, role FROM users WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    if ($user && password_verify($password, $user['password_hash'])){
        // login success
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
        echo json_encode(['ok'=>true,'role'=>$user['role'],'name'=>$user['name']]);
    } else {
        http_response_code(401);
        echo json_encode(['error'=>'invalid']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error'=>'db','message'=>$e->getMessage()]);
}
?>