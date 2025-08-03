<?php
session_start();
if (isset($_SESSION['player'])) {
    header("Location: game.php");
    exit();
}

// Get error message if any
$error_message = '';
$success_message = '';

if (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case 'missing_credentials':
            $error_message = 'Please enter both username and password.';
            break;
        case 'username_too_long':
            $error_message = 'Username must be 30 characters or less.';
            break;
        case 'password_too_short':
            $error_message = 'Password must be at least 6 characters long.';
            break;
        case 'invalid_credentials':
            $error_message = 'Invalid username or password.';
            break;
        case 'user_exists':
            $error_message = 'Username already exists. Please choose a different username or try logging in.';
            break;
        case 'registration_failed':
            $error_message = 'Registration failed. Please try again.';
            break;
        case 'system_error':
            $error_message = 'System error. Please try again later.';
            break;
        case 'database_error':
            $error_message = 'Database error. Please contact support.';
            break;
        case 'invalid_request':
            $error_message = 'Invalid request method.';
            break;
        default:
            $error_message = 'An error occurred. Please try again.';
    }
} elseif (isset($_GET['success'])) {  
    switch ($_GET['success']) {
        case 'registration':
            $success_message = 'Registration successful! Please log in with your new account.';
            break;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fifteen Puzzle - Login</title>
    <link rel="stylesheet" href="login.css">
</head>
<body>
    <div><h1>Fifteen Puzzle</h1></div>
    
    <?php if ($success_message): ?>
    <div class="success-message" style="
        background-color: #d4edda;
        color: #155724;
        padding: 10px;
        margin: 10px auto;
        border: 1px solid #c3e6cb;
        border-radius: 4px;
        width: 400px;
        text-align: center;
    ">
        <?php echo htmlspecialchars($success_message); ?>
    </div>
    <?php endif; ?>
    
    <?php if ($error_message): ?>
    <div class="error-message" style="
        background-color: #f8d7da;
        color: #721c24;
        padding: 10px;
        margin: 10px auto;
        border: 1px solid #f5c6cb;
        border-radius: 4px;
        width: 400px;
        text-align: center;
    ">
        <?php echo htmlspecialchars($error_message); ?>
    </div>
    <?php endif; ?>
    
    <div class="login-form-container">
        <form name="loginForm" class="form-fields" onsubmit="return validateForm(event)" action="loginForm.php" method="post">
            <div class="password-container">
                <h1 id="playerHeader">Player Name</h1>
                <input id="playerName" type="text" name="Player" required maxlength="30" 
                       value="<?php echo isset($_GET['username']) ? htmlspecialchars($_GET['username']) : ''; ?>">
            </div>
            
            <div class="password-container">
                <h1 id="passwordHeader">Password</h1>
                <input id="password" type="password" name="password" required>
            </div>
            
            <div>
                <input type="submit" name="login" id="login" value="Login" data-value="login">
            </div>
            <br>
            <div>
                <a href="register.php" style="text-decoration: none;">
                    <input type="button" name="register" id="register" value="Register">
                </a>
            </div>
        </form>
        
        <script type="text/javascript" src="validation.js"></script>
    </div>
</body>
</html>