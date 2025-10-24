<?php
require_once '../php/config.php';
session_start();

// Redirect if already logged in as admin
if (isset($_SESSION['user_id']) && $_SESSION['user_role'] === 'admin') {
    header('Location: enhanced_dashboard.php');
    exit;
}

$error_message = '';
$success_message = '';
$token = $_GET['token'] ?? '';

if (empty($token)) {
    $error_message = 'Invalid or missing reset token.';
} else {
    try {
        $pdo = getPDO();
        
        // Check if token is valid and not expired
        $stmt = $pdo->prepare("
            SELECT pr.*, u.name, u.email 
            FROM password_resets pr 
            JOIN users u ON pr.email = u.email 
            WHERE pr.token = ? AND pr.expires_at > NOW() AND u.role = 'admin' AND u.status = 'active'
        ");
        $stmt->execute([$token]);
        $reset_data = $stmt->fetch();
        
        if (!$reset_data) {
            $error_message = 'Invalid or expired reset token.';
        } elseif ($_POST) {
            $password = $_POST['password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';
            
            if (empty($password) || empty($confirm_password)) {
                $error_message = 'Please fill in all fields.';
            } elseif (strlen($password) < 8) {
                $error_message = 'Password must be at least 8 characters long.';
            } elseif ($password !== $confirm_password) {
                $error_message = 'Passwords do not match.';
            } else {
                // Update password
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET password_hash = ?, last_login = NULL WHERE email = ?");
                $stmt->execute([$password_hash, $reset_data['email']]);
                
                // Delete used reset token
                $stmt = $pdo->prepare("DELETE FROM password_resets WHERE token = ?");
                $stmt->execute([$token]);
                
                $success_message = 'Password updated successfully! You can now login with your new password.';
            }
        }
    } catch (Exception $e) {
        $error_message = 'Password reset failed. Please try again.';
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Reset Password - Mazvikadei Resort Admin</title>
    <link rel="stylesheet" href="../styles.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <style>
        body {
            background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 50%, #3b82f6 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Inter', sans-serif;
            position: relative;
            overflow: hidden;
        }
        
        .admin-bg-pattern {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: 
                radial-gradient(circle at 20% 80%, rgba(255, 255, 255, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(255, 255, 255, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 40% 40%, rgba(255, 255, 255, 0.05) 0%, transparent 50%);
            z-index: 0;
        }
        
        .admin-container {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            padding: 3rem;
            box-shadow: 
                0 25px 50px rgba(0, 0, 0, 0.15),
                0 0 0 1px rgba(255, 255, 255, 0.2);
            width: 100%;
            max-width: 450px;
            animation: adminSlideUp 0.8s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            z-index: 1;
        }
        
        @keyframes adminSlideUp {
            from {
                opacity: 0;
                transform: translateY(40px) scale(0.95);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }
        
        .admin-header {
            text-align: center;
            margin-bottom: 2.5rem;
        }
        
        .admin-logo {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #1e3a8a, #3b82f6);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            animation: adminPulse 3s ease-in-out infinite;
            box-shadow: 0 10px 30px rgba(30, 58, 138, 0.3);
        }
        
        @keyframes adminPulse {
            0%, 100% { 
                transform: scale(1);
                box-shadow: 0 10px 30px rgba(30, 58, 138, 0.3);
            }
            50% { 
                transform: scale(1.05);
                box-shadow: 0 15px 40px rgba(30, 58, 138, 0.4);
            }
        }
        
        .admin-logo i {
            font-size: 2.5rem;
            color: white;
        }
        
        .admin-title {
            font-size: 2.5rem;
            font-weight: 900;
            background: linear-gradient(135deg, #1e3a8a, #3b82f6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 0.5rem;
        }
        
        .admin-subtitle {
            color: #64748b;
            font-size: 1.1rem;
            font-weight: 500;
        }
        
        .admin-badge {
            display: inline-block;
            background: linear-gradient(135deg, #1e3a8a, #3b82f6);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 600;
            margin-top: 1rem;
            animation: adminShimmer 2s ease-in-out infinite;
        }
        
        @keyframes adminShimmer {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.8; }
        }
        
        .form-group {
            margin-bottom: 1.5rem;
            position: relative;
        }
        
        .form-group label {
            display: block;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 0.75rem;
            font-size: 1rem;
        }
        
        .input-wrapper {
            position: relative;
        }
        
        .input-wrapper i {
            position: absolute;
            left: 1.25rem;
            top: 50%;
            transform: translateY(-50%);
            color: #64748b;
            z-index: 1;
            font-size: 1.1rem;
        }
        
        .form-control {
            width: 100%;
            padding: 1.25rem 1.25rem 1.25rem 3.5rem;
            border: 2px solid #e2e8f0;
            border-radius: 16px;
            font-size: 1.1rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            background: white;
            font-weight: 500;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
            transform: translateY(-2px);
        }
        
        .password-strength {
            margin-top: 0.5rem;
            font-size: 0.875rem;
            font-weight: 600;
        }
        
        .strength-weak { color: #dc2626; }
        .strength-medium { color: #f59e0b; }
        .strength-strong { color: #10b981; }
        
        .btn-admin-reset {
            width: 100%;
            padding: 1.25rem;
            background: linear-gradient(135deg, #1e3a8a, #3b82f6);
            color: white;
            border: none;
            border-radius: 16px;
            font-size: 1.1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .btn-admin-reset:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(30, 58, 138, 0.4);
        }
        
        .btn-admin-reset:active {
            transform: translateY(-1px);
        }
        
        .btn-admin-reset::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            transition: left 0.6s;
        }
        
        .btn-admin-reset:hover::before {
            left: 100%;
        }
        
        .error-message {
            background: linear-gradient(135deg, #fee2e2, #fecaca);
            color: #dc2626;
            padding: 1.25rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            border-left: 4px solid #dc2626;
            animation: adminShake 0.6s ease-in-out;
            font-weight: 600;
        }
        
        .success-message {
            background: linear-gradient(135deg, #d1fae5, #a7f3d0);
            color: #065f46;
            padding: 1.25rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            border-left: 4px solid #10b981;
            animation: adminSlideIn 0.6s ease-in-out;
            font-weight: 600;
        }
        
        @keyframes adminShake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-8px); }
            75% { transform: translateX(8px); }
        }
        
        @keyframes adminSlideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .admin-links {
            text-align: center;
            margin-top: 2.5rem;
        }
        
        .admin-links a {
            color: #3b82f6;
            text-decoration: none;
            font-weight: 700;
            transition: all 0.3s ease;
            display: inline-block;
            margin: 0 1rem;
        }
        
        .admin-links a:hover {
            color: #1e3a8a;
            transform: translateY(-2px);
        }
        
        .security-notice {
            background: linear-gradient(135deg, #f0f9ff, #e0f2fe);
            border: 1px solid #0ea5e9;
            border-radius: 12px;
            padding: 1rem;
            margin-top: 2rem;
            text-align: center;
            color: #0c4a6e;
            font-size: 0.9rem;
            font-weight: 600;
        }
        
        .security-notice i {
            color: #0ea5e9;
            margin-right: 0.5rem;
        }
        
        .floating-admin-shapes {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: -1;
        }
        
        .admin-shape {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            animation: adminFloat 8s ease-in-out infinite;
        }
        
        .admin-shape:nth-child(1) {
            width: 100px;
            height: 100px;
            top: 15%;
            left: 8%;
            animation-delay: 0s;
        }
        
        .admin-shape:nth-child(2) {
            width: 150px;
            height: 150px;
            top: 55%;
            right: 8%;
            animation-delay: 3s;
        }
        
        .admin-shape:nth-child(3) {
            width: 80px;
            height: 80px;
            bottom: 15%;
            left: 15%;
            animation-delay: 6s;
        }
        
        @keyframes adminFloat {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-30px) rotate(180deg); }
        }
    </style>
</head>
<body>
    <div class="admin-bg-pattern"></div>
    <div class="floating-admin-shapes">
        <div class="admin-shape"></div>
        <div class="admin-shape"></div>
        <div class="admin-shape"></div>
    </div>
    
    <div class="admin-container">
        <div class="admin-header">
            <div class="admin-logo">
                <i class="fas fa-lock"></i>
            </div>
            <h1 class="admin-title">New Password</h1>
            <p class="admin-subtitle">Enter your new secure password</p>
            <div class="admin-badge">
                <i class="fas fa-shield-alt"></i>
                Secure Password Reset
            </div>
        </div>
        
        <?php if ($error_message): ?>
        <div class="error-message">
            <i class="fas fa-exclamation-triangle"></i>
            <?php echo htmlspecialchars($error_message); ?>
        </div>
        <?php endif; ?>
        
        <?php if ($success_message): ?>
        <div class="success-message">
            <i class="fas fa-check-circle"></i>
            <?php echo htmlspecialchars($success_message); ?>
        </div>
        <?php endif; ?>
        
        <?php if (empty($error_message) && !$success_message): ?>
        <form method="POST" action="reset-password.php?token=<?php echo htmlspecialchars($token); ?>">
            <div class="form-group">
                <label for="password">New Password</label>
                <div class="input-wrapper">
                    <i class="fas fa-lock"></i>
                    <input type="password" id="password" name="password" class="form-control" 
                           placeholder="Enter new password" required>
                </div>
                <div class="password-strength" id="passwordStrength"></div>
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Confirm New Password</label>
                <div class="input-wrapper">
                    <i class="fas fa-lock"></i>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" 
                           placeholder="Confirm new password" required>
                </div>
            </div>
            
            <button type="submit" class="btn-admin-reset">
                <i class="fas fa-save"></i>
                Update Password
            </button>
        </form>
        <?php endif; ?>
        
        <div class="admin-links">
            <a href="login.php">
                <i class="fas fa-sign-in-alt"></i>
                Back to Login
            </a>
            <a href="../index.php">
                <i class="fas fa-home"></i>
                Back to Site
            </a>
        </div>
        
        <div class="security-notice">
            <i class="fas fa-shield-alt"></i>
            Your new password will be encrypted and stored securely.
        </div>
    </div>
    
    <script>
        // Password strength checker
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            const strengthDiv = document.getElementById('passwordStrength');
            
            if (password.length === 0) {
                strengthDiv.innerHTML = '';
                return;
            }
            
            let strength = 0;
            let feedback = [];
            
            if (password.length >= 8) strength++;
            else feedback.push('at least 8 characters');
            
            if (/[a-z]/.test(password)) strength++;
            else feedback.push('lowercase letters');
            
            if (/[A-Z]/.test(password)) strength++;
            else feedback.push('uppercase letters');
            
            if (/[0-9]/.test(password)) strength++;
            else feedback.push('numbers');
            
            if (/[^A-Za-z0-9]/.test(password)) strength++;
            else feedback.push('special characters');
            
            let strengthText = '';
            let strengthClass = '';
            
            if (strength < 3) {
                strengthText = 'Weak password. Add: ' + feedback.join(', ');
                strengthClass = 'strength-weak';
            } else if (strength < 5) {
                strengthText = 'Medium strength password';
                strengthClass = 'strength-medium';
            } else {
                strengthText = 'Strong password!';
                strengthClass = 'strength-strong';
            }
            
            strengthDiv.innerHTML = `<i class="fas fa-shield-alt"></i> ${strengthText}`;
            strengthDiv.className = `password-strength ${strengthClass}`;
        });
        
        // Password confirmation checker
        document.getElementById('confirm_password').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirmPassword = this.value;
            
            if (confirmPassword.length > 0) {
                if (password === confirmPassword) {
                    this.style.borderColor = '#10b981';
                } else {
                    this.style.borderColor = '#dc2626';
                }
            } else {
                this.style.borderColor = '#e2e8f0';
            }
        });
        
        // Add loading animation to form submission
        document.querySelector('form').addEventListener('submit', function() {
            const btn = document.querySelector('.btn-admin-reset');
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';
            btn.disabled = true;
        });
        
        // Add focus animations
        document.querySelectorAll('.form-control').forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.style.transform = 'scale(1.02)';
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.style.transform = 'scale(1)';
            });
        });
    </script>
</body>
</html>
