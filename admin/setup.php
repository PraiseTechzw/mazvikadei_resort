<?php
require_once '../php/config.php';

$setup_result = '';
$error_message = '';

if ($_POST) {
    try {
        $pdo = getPDO();
        
        // Delete existing admin
        $stmt = $pdo->prepare("DELETE FROM users WHERE email = 'admin@mazvikadei.local'");
        $stmt->execute();
        
        // Create new admin user
        $password_hash = password_hash('Admin@123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("
            INSERT INTO users (email, password_hash, name, role, status, phone, created_at) 
            VALUES (?, ?, ?, 'admin', 'active', ?, NOW())
        ");
        $stmt->execute(['admin@mazvikadei.local', $password_hash, 'System Administrator', '+263 77 123 4567']);
        
        $setup_result = "✅ Admin user created successfully!<br>";
        $setup_result .= "✅ Email: admin@mazvikadei.local<br>";
        $setup_result .= "✅ Password: Admin@123<br>";
        $setup_result .= "✅ Role: admin<br>";
        $setup_result .= "✅ Status: active<br>";
        
    } catch (Exception $e) {
        $error_message = "Setup failed: " . $e->getMessage();
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Admin Setup - Mazvikadei Resort</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            text-align: center;
        }
        button {
            background: #28a745;
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            width: 100%;
        }
        button:hover {
            background: #218838;
        }
        .result {
            margin-top: 20px;
            padding: 15px;
            border-radius: 5px;
            background: #f8f9fa;
            border: 1px solid #dee2e6;
        }
        .error {
            background: #f8d7da;
            border-color: #f5c6cb;
            color: #721c24;
        }
        .success {
            background: #d4edda;
            border-color: #c3e6cb;
            color: #155724;
        }
        .credentials {
            background: #e7f3ff;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Admin Setup</h1>
        
        <div class="credentials">
            <h3>📋 Admin Credentials:</h3>
            <p><strong>Email:</strong> admin@mazvikadei.local</p>
            <p><strong>Password:</strong> Admin@123</p>
            <p><strong>Role:</strong> admin</p>
        </div>
        
        <form method="POST">
            <button type="submit">🚀 Create Admin User</button>
        </form>
        
        <?php if ($setup_result): ?>
        <div class="result success">
            <h3>✅ Setup Complete!</h3>
            <?php echo $setup_result; ?>
            <p><a href="login.php">→ Go to Admin Login</a></p>
        </div>
        <?php endif; ?>
        
        <?php if ($error_message): ?>
        <div class="result error">
            <h3>❌ Setup Failed:</h3>
            <?php echo htmlspecialchars($error_message); ?>
        </div>
        <?php endif; ?>
        
        <div class="result">
            <h3>📝 Next Steps:</h3>
            <ol>
                <li>Click "Create Admin User" above</li>
                <li>Go to <a href="login.php">admin/login.php</a></li>
                <li>Login with the credentials shown above</li>
                <li>Access the admin dashboard</li>
            </ol>
        </div>
    </div>
</body>
</html>

