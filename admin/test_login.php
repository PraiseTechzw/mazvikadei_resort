<?php
require_once '../php/config.php';
session_start();

$test_result = '';
$error_message = '';

if ($_POST) {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    try {
        $pdo = getPDO();
        
        // Check if admin exists
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND role = 'admin'");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user) {
            $test_result .= "✅ Admin user found: " . $user['name'] . "<br>";
            $test_result .= "✅ Email: " . $user['email'] . "<br>";
            $test_result .= "✅ Role: " . $user['role'] . "<br>";
            $test_result .= "✅ Status: " . $user['status'] . "<br>";
            
            // Test password
            if (password_verify($password, $user['password_hash'])) {
                $test_result .= "✅ Password verification: SUCCESS<br>";
                $test_result .= "✅ Login should work!<br>";
            } else {
                $test_result .= "❌ Password verification: FAILED<br>";
                $test_result .= "❌ Password does not match<br>";
            }
        } else {
            $test_result .= "❌ Admin user not found<br>";
        }
        
    } catch (Exception $e) {
        $error_message = "Database error: " . $e->getMessage();
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Admin Login Test - Mazvikadei Resort</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
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
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="email"], input[type="password"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }
        button {
            background: #007cba;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background: #005a87;
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
        <h1>🔧 Admin Login Test</h1>
        
        <div class="credentials">
            <h3>📋 Test Credentials:</h3>
            <p><strong>Email:</strong> admin@mazvikadei.local</p>
            <p><strong>Password:</strong> Admin@123</p>
            <p><strong>Role:</strong> admin</p>
        </div>
        
        <form method="POST">
            <div class="form-group">
                <label for="email">Admin Email:</label>
                <input type="email" id="email" name="email" value="admin@mazvikadei.local" required>
            </div>
            
            <div class="form-group">
                <label for="password">Admin Password:</label>
                <input type="password" id="password" name="password" value="Admin@123" required>
            </div>
            
            <button type="submit">🧪 Test Login</button>
        </form>
        
        <?php if ($test_result): ?>
        <div class="result">
            <h3>🔍 Test Results:</h3>
            <?php echo $test_result; ?>
        </div>
        <?php endif; ?>
        
        <?php if ($error_message): ?>
        <div class="result error">
            <h3>❌ Error:</h3>
            <?php echo htmlspecialchars($error_message); ?>
        </div>
        <?php endif; ?>
        
        <div class="result">
            <h3>📝 Instructions:</h3>
            <ol>
                <li>Run the SQL script: <code>sql/admin_fix.sql</code></li>
                <li>Test the login credentials above</li>
                <li>If successful, go to <a href="login.php">admin/login.php</a></li>
                <li>If failed, check database connection and user table</li>
            </ol>
        </div>
    </div>
</body>
</html>
