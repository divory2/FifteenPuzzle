<?php
session_start();
require_once 'db_config.php';

// Enable error reporting for development (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

/**
 * Secure login form processing with improved error handling
 */

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

        // Prepare secure query to fetch user data
        $stmt = $conn->prepare("SELECT id, player, player_password, player_role FROM PLAYER WHERE player = ? LIMIT 1");
        if (!$stmt) {
            error_log("Prepare failed: " . $conn->error);
            header("Location: login.php?error=database_error");
            exit();
        }
        
        $stmt->bind_param("s", $player);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $row = $result->fetch_assoc();
            
            // Verify password
            if (password_verify($password, $row["player_password"])) {
                // Successful login - regenerate session ID for security
                session_regenerate_id(true);
                
                // Set session variables
                $_SESSION["player"] = $row["player"];
                $_SESSION["role"] = $row["player_role"];
                $_SESSION["playerId"] = $row["id"];
                $_SESSION["gameStart"] = "true";
                $_SESSION["login_time"] = time();
                
                // Update login timestamp
                $updateLoginStmt = $conn->prepare("UPDATE PLAYER SET login_date = CURRENT_TIMESTAMP WHERE id = ?");
                if ($updateLoginStmt) {
                    $updateLoginStmt->bind_param("i", $row["id"]);
                    $updateLoginStmt->execute();
                    $updateLoginStmt->close();
                }
                
                // Log successful login
                error_log("Successful login for user: " . $player . " with role: " . $row["player_role"]);
                
                // Redirect based on role
                if ($row["player_role"] === 'admin') {
                    header("Location: admin.php");
                } else {
                    header("Location: game.php");
                }
                exit();
                
            } else {
                // Invalid password
                error_log("Failed login attempt for user: " . $player . " - incorrect password");
                header("Location: login.php?error=invalid_credentials");
                exit();
            }
            
        } else {
            // User not found
            error_log("Failed login attempt for non-existent user: " . $player);
            header("Location: login.php?error=invalid_credentials");
            exit();
        }
        
        $stmt->close();
        
    } catch (Exception $e) {
        error_log("Login error: " . $e->getMessage());
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