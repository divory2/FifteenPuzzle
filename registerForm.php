<?php
session_start();
require_once 'db_config.php';

// Enable error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and validate input
    $player = trim($_POST['Player'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Input validation
    if (empty($player) || empty($password)) {
        header("Location: login.php?error=missing_credentials");
        exit();
    }
    
    if (strlen($player) > 30) {
        header("Location: login.php?error=username_too_long");
        exit();
    }
    
    if (strlen($password) < 6) {
        header("Location: login.php?error=password_too_short");
        exit();
    }
    
    try {
        $conn = getDBConnection();
        
        // Ensure PLAYER table exists with proper structure
        $createTableSQL = "CREATE TABLE IF NOT EXISTS PLAYER (
            id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            player VARCHAR(30) NOT NULL UNIQUE,
            player_password VARCHAR(255) NOT NULL,
            player_role ENUM('player', 'admin') NOT NULL DEFAULT 'player',
            login_date TIMESTAMP NULL DEFAULT NULL,
            logout_date TIMESTAMP NULL DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_player (player),
            INDEX idx_role (player_role)
        )";
        
        if (!$conn->query($createTableSQL)) {
            error_log("Error creating PLAYER table: " . $conn->error);
            header("Location: login.php?error=database_error");
            exit();
        }

        // Check if player already exists
        $checkStmt = $conn->prepare("SELECT id, player FROM PLAYER WHERE player = ? LIMIT 1");
        if (!$checkStmt) {
            error_log("Prepare failed: " . $conn->error);
            header("Location: login.php?error=database_error");
            exit();
        }
        
        $checkStmt->bind_param("s", $player);
        $checkStmt->execute();
        $result = $checkStmt->get_result();

        if ($result->num_rows > 0) {
            // User already exists
            error_log("Registration attempt for existing user: " . $player);
            header("Location: login.php?error=user_exists");
            exit();
        }
        
        $checkStmt->close();

        // Create new user
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $insertStmt = $conn->prepare("INSERT INTO PLAYER (player, player_password, player_role, login_date) VALUES (?, ?, 'player', CURRENT_TIMESTAMP)");
        
        if (!$insertStmt) {
            error_log("Prepare failed: " . $conn->error);
            header("Location: login.php?error=database_error");
            exit();
        }
        
        $insertStmt->bind_param("ss", $player, $hashedPassword);
        
        if ($insertStmt->execute()) {
            // Registration successful - log the user in automatically
            session_regenerate_id(true);
            
            $_SESSION["player"] = $player;
            $_SESSION["role"] = "player";
            $_SESSION["playerId"] = $conn->insert_id;
            $_SESSION["gameStart"] = "true";
            $_SESSION["login_time"] = time();
            
            error_log("Successful registration and auto-login for user: " . $player);
            header("Location: game.php");
            exit();
            
        } else {
            error_log("Registration failed for user: " . $player . " - " . $insertStmt->error);
            header("Location: login.php?error=registration_failed");
            exit();
        }
        
        $insertStmt->close();
        
    } catch (Exception $e) {
        error_log("Registration error: " . $e->getMessage());
        header("Location: login.php?error=system_error");
        exit();
    } finally {
        if (isset($conn)) {
            $conn->close();
        }
    }
} else {
    // Not a POST request
    header("Location: login.php?error=invalid_request");
    exit();
}
?>