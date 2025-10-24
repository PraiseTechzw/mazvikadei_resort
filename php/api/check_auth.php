<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

$logged_in = isset($_SESSION['user_id']);
$user_data = null;

if ($logged_in) {
    $user_data = [
        'id' => $_SESSION['user_id'],
        'name' => $_SESSION['user_name'] ?? '',
        'email' => $_SESSION['user_email'] ?? '',
        'role' => $_SESSION['user_role'] ?? 'customer'
    ];
}

echo json_encode([
    'logged_in' => $logged_in,
    'user' => $user_data
]);
?>
