<?php
session_start();

// If already logged in, redirect to game
if (isset($_SESSION['player'])) {
    header("Location: game.php");
    exit();
}

// Handle error and success messages
$errorMessage = '';
$successMessage = '';

if (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case 'missing_credentials':
            $errorMessage = '⚠️ Please enter both username and password.';
            break;
        case 'password_mismatch':
            $errorMessage = '🔒 Passwords do not match. Please try again.';
            break;
        case 'username_too_long':
            $errorMessage = '📏 Username must be 30 characters or less.';
            break;
        case 'password_too_short':
            $errorMessage = '🔒 Password must be at least 6 characters long.';
            break;
        case 'user_exists':
            $errorMessage = '👤 Username already exists. Please choose a different username.';
            break;
        case 'registration_failed':
            $errorMessage = '❌ Registration failed. Please try again.';
            break;
        case 'database_error':
            $errorMessage = '💥 Database error. Please try again later.';
            break;
        case 'system_error':
            $errorMessage = '⚙️ System error. Please try again later.';
            break;
        case 'invalid_request':
            $errorMessage = '🚫 Invalid request. Please use the form below.';
            break;
        default:
            $errorMessage = '❓ An error occurred. Please try again.';
    }
}

if (isset($_GET['message'])) {
    switch ($_GET['message']) {
        case 'registration_success':
            $successMessage = '🎉 Registration successful! Please log in.';
            break;
    }
}

// Preserve form data on error
$username = $_GET['username'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🧩 Fifteen Puzzle - Register</title>
    <link rel="stylesheet" href="login.css">
</head>
<body>
    <div class="login-form-container">
        <div style="width: 100%; max-width: 350px;">
            <h2 style="text-align: center; color: #2c3e50; margin-bottom: 10px;">
                🎮 Create Account
            </h2>
            <p style="text-align: center; color: #7f8c8d; margin-bottom: 20px; font-size: 14px;">
                Join the Fifteen Puzzle community!
            </p>

            <?php if ($errorMessage): ?>
                <div style="background: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin-bottom: 15px; text-align: center; font-size: 14px;">
                    <?php echo htmlspecialchars($errorMessage); ?>
                </div>
            <?php endif; ?>

            <?php if ($successMessage): ?>
                <div style="background: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin-bottom: 15px; text-align: center; font-size: 14px;">
                    <?php echo htmlspecialchars($successMessage); ?>
                </div>
            <?php endif; ?>

            <form action="registerForm.php" method="POST" style="display: flex; flex-direction: column; gap: 15px;">
                <div style="display: flex; flex-direction: column;">
                    <label for="username" style="margin-bottom: 5px; font-weight: 600; color: #34495e; font-size: 14px;">
                        👤 Username:
                    </label>
                    <input 
                        type="text" 
                        id="username" 
                        name="Player" 
                        value="<?php echo htmlspecialchars($username); ?>"
                        required 
                        maxlength="30"
                        style="width: 100%; padding: 10px; border: 2px solid #bdc3c7; border-radius: 8px; font-size: 14px; transition: all 0.3s ease; box-sizing: border-box;"
                        placeholder="Enter your username"
                        onfocus="this.style.borderColor='#3498db'; this.style.boxShadow='0 0 10px rgba(52, 152, 219, 0.2)'"
                        onblur="this.style.borderColor='#bdc3c7'; this.style.boxShadow='none'"
                    >
                    <small style="color: #7f8c8d; font-size: 12px; margin-top: 2px;">Max 30 characters</small>
                </div>

                <div style="display: flex; flex-direction: column;">
                    <label for="password" style="margin-bottom: 5px; font-weight: 600; color: #34495e; font-size: 14px;">
                        🔒 Password:
                    </label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        required 
                        minlength="6"
                        style="width: 100%; padding: 10px; border: 2px solid #bdc3c7; border-radius: 8px; font-size: 14px; transition: all 0.3s ease; box-sizing: border-box;"
                        placeholder="Enter your password"
                        onfocus="this.style.borderColor='#3498db'; this.style.boxShadow='0 0 10px rgba(52, 152, 219, 0.2)'"
                        onblur="this.style.borderColor='#bdc3c7'; this.style.boxShadow='none'"
                    >
                    <small style="color: #7f8c8d; font-size: 12px; margin-top: 2px;">Min 6 characters</small>
                </div>

                <div style="display: flex; flex-direction: column;">
                    <label for="confirmPassword" style="margin-bottom: 5px; font-weight: 600; color: #34495e; font-size: 14px;">
                        🔒 Confirm Password:
                    </label>
                    <input 
                        type="password" 
                        id="confirmPassword" 
                        name="confirmPassword" 
                        required 
                        minlength="6"
                        style="width: 100%; padding: 10px; border: 2px solid #bdc3c7; border-radius: 8px; font-size: 14px; transition: all 0.3s ease; box-sizing: border-box;"
                        placeholder="Confirm your password"
                        onfocus="this.style.borderColor='#3498db'; this.style.boxShadow='0 0 10px rgba(52, 152, 219, 0.2)'"
                        onblur="this.style.borderColor='#bdc3c7'; this.style.boxShadow='none'"
                    >
                </div>

                <div style="display: flex; flex-direction: column; gap: 10px; margin-top: 10px;">
                    <button 
                        type="submit" 
                        style="width: 100%; padding: 12px; background: linear-gradient(135deg, #27ae60, #2ecc71); color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; font-size: 16px; transition: all 0.3s ease;"
                        onmouseover="this.style.background='linear-gradient(135deg, #2ecc71, #27ae60)'; this.style.transform='translateY(-2px)'; this.style.boxShadow='0 5px 15px rgba(46, 204, 113, 0.3)'"
                        onmouseout="this.style.background='linear-gradient(135deg, #27ae60, #2ecc71)'; this.style.transform='translateY(0)'; this.style.boxShadow='none'"
                    >
                        ✨ Create Account
                    </button>

                    <a 
                        href="login.php" 
                        style="width: 100%; padding: 12px; background: linear-gradient(135deg, #95a5a6, #7f8c8d); color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; font-size: 16px; transition: all 0.3s ease; text-decoration: none; text-align: center; display: block; box-sizing: border-box;"
                        onmouseover="this.style.background='linear-gradient(135deg, #7f8c8d, #95a5a6)'; this.style.transform='translateY(-2px)'; this.style.boxShadow='0 5px 15px rgba(149, 165, 166, 0.3)'"
                        onmouseout="this.style.background='linear-gradient(135deg, #95a5a6, #7f8c8d)'; this.style.transform='translateY(0)'; this.style.boxShadow='none'"
                    >
                        🔐 Back to Login
                    </a>
                </div>
            </form>

            <div style="text-align: center; margin-top: 15px; padding-top: 15px; border-top: 1px solid #eee;">
                <p style="color: #7f8c8d; margin-bottom: 8px; font-size: 14px;">Already have an account?</p>
                <a href="login.php" style="color: #3498db; text-decoration: none; font-weight: 500; font-size: 14px;">
                    Sign in here
                </a>
                <br><br>
                <a href="index.php" style="color: #3498db; text-decoration: none; font-weight: 500; font-size: 14px;">
                    🏠 Back to Home
                </a>
            </div>
        </div>
    </div>

    <script>
        // Password confirmation validation
        const form = document.querySelector('form');
        const password = document.getElementById('password');
        const confirmPassword = document.getElementById('confirmPassword');

        function validatePasswords() {
            if (password.value !== confirmPassword.value) {
                confirmPassword.setCustomValidity('Passwords do not match');
                confirmPassword.style.borderColor = '#e74c3c';
            } else {
                confirmPassword.setCustomValidity('');
                confirmPassword.style.borderColor = '#27ae60';
            }
        }

        password.addEventListener('input', validatePasswords);
        confirmPassword.addEventListener('input', validatePasswords);

        // Form submission with loading state
        form.addEventListener('submit', function(e) {
            if (password.value !== confirmPassword.value) {
                e.preventDefault();
                alert('❌ Passwords do not match!');
                return;
            }

            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '⏳ Creating Account...';
            submitBtn.style.opacity = '0.7';
        });

        // Auto-hide messages after 5 seconds
        setTimeout(() => {
            const messages = document.querySelectorAll('div[style*="background: #f8d7da"], div[style*="background: #d4edda"]');
            messages.forEach(msg => {
                msg.style.opacity = '0';
                msg.style.transform = 'translateY(-20px)';
                setTimeout(() => msg.remove(), 300);
            });
        }, 5000);
    </script>
</body>
</html>
