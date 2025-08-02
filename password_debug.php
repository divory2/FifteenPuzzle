<?php
session_start();
require_once 'db_config.php';

// Security check - only allow in development
$isDevelopment = true; // Set to false in production
if (!$isDevelopment) {
    die('Access denied. Development mode only.');
}

echo "<h1>🔐 Password Debug Tool</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
    .debug-section { background: white; padding: 20px; margin: 10px 0; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
    .success { color: #27ae60; background: #d5f4e6; padding: 10px; border-radius: 4px; }
    .error { color: #e74c3c; background: #fdeaea; padding: 10px; border-radius: 4px; }
    .warning { color: #f39c12; background: #fef9e7; padding: 10px; border-radius: 4px; }
    table { width: 100%; border-collapse: collapse; margin: 10px 0; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    th { background: #3498db; color: white; }
    .password-hash { font-family: monospace; font-size: 12px; word-break: break-all; }
</style>";

try {
    $conn = getDBConnection();
    
    echo "<div class='debug-section'>";
    echo "<h2>📊 Password Analysis for All Users</h2>";
    
    // Get all users with their passwords
    $result = $conn->query("SELECT id, player, player_password, player_role FROM PLAYER ORDER BY id");
    
    if ($result->num_rows > 0) {
        echo "<table>";
        echo "<tr><th>ID</th><th>Username</th><th>Role</th><th>Password Hash</th><th>Hash Length</th><th>Hash Starts With</th><th>Test Passwords</th></tr>";
        
        while ($row = $result->fetch_assoc()) {
            $passwordHash = $row['player_password'];
            $hashLength = strlen($passwordHash);
            $hashStart = substr($passwordHash, 0, 10);
            
            echo "<tr>";
            echo "<td>" . $row['id'] . "</td>";
            echo "<td><strong>" . htmlspecialchars($row['player']) . "</strong></td>";
            echo "<td>" . $row['player_role'] . "</td>";
            echo "<td class='password-hash'>" . htmlspecialchars($passwordHash) . "</td>";
            echo "<td>" . $hashLength . "</td>";
            echo "<td>" . htmlspecialchars($hashStart) . "</td>";
            
            // Test common passwords for this user
            echo "<td>";
            $testPasswords = [];
            
            // Common passwords based on username
            if ($row['player'] === 'admin') {
                $testPasswords = ['admin123', 'adminpass', 'admin', 'password'];
            } elseif ($row['player'] === 'player1') {
                $testPasswords = ['mypassword', 'player1', 'password123', 'password'];
            } else {
                $testPasswords = ['password123', 'password', $row['player'] . '123', $row['player']];
            }
            
            $foundPassword = false;
            foreach ($testPasswords as $testPass) {
                if (password_verify($testPass, $passwordHash)) {
                    echo "<span class='success'>✅ '$testPass' works!</span><br>";
                    $foundPassword = true;
                } else {
                    echo "<span class='error'>❌ '$testPass' failed</span><br>";
                }
            }
                        
            if (!$foundPassword) {
                echo "<span class='warning'>⚠️ None of the test passwords worked!</span>";
            }
            
            echo "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p class='warning'>No users found in database!</p>";
    }
    
    echo "</div>";
    
    // Test password hashing and verification
    echo "<div class='debug-section'>";
    echo "<h2>🧪 Password Hashing Test</h2>";
    
    $testPassword = "mypassword";
    $hashedTest = password_hash($testPassword, PASSWORD_DEFAULT);
    
    echo "<p><strong>Original Password:</strong> $testPassword</p>";
    echo "<p><strong>Generated Hash:</strong> <code class='password-hash'>$hashedTest</code></p>";
    echo "<p><strong>Hash Length:</strong> " . strlen($hashedTest) . "</p>";
    echo "<p><strong>Verification Test:</strong> ";
    
    if (password_verify($testPassword, $hashedTest)) {
        echo "<span class='success'>✅ Hash verification works correctly!</span>";
    } else {
        echo "<span class='error'>❌ Hash verification failed!</span>";
    }
    echo "</p>";
    
    echo "</div>";
    
    // Manual password update section
    echo "<div class='debug-section'>";
    echo "<h2>🔧 Manual Password Reset</h2>";
    
    if (isset($_POST['reset_password'])) {
        $userId = $_POST['user_id'];
        $newPassword = $_POST['new_password'];
        
        if (!empty($userId) && !empty($newPassword)) {
            $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
            $updateStmt = $conn->prepare("UPDATE PLAYER SET player_password = ? WHERE id = ?");
            $updateStmt->bind_param("si", $newHash, $userId);
            
            if ($updateStmt->execute()) {
                echo "<div class='success'>✅ Password updated successfully for user ID $userId!</div>";
                echo "<p><strong>New hash:</strong> <code class='password-hash'>$newHash</code></p>";
                
                // Test the new password
                if (password_verify($newPassword, $newHash)) {
                    echo "<div class='success'>✅ New password verification test passed!</div>";
                } else {
                    echo "<div class='error'>❌ New password verification test failed!</div>";
                }
            } else {
                echo "<div class='error'>❌ Failed to update password: " . $updateStmt->error . "</div>";
            }
            $updateStmt->close();
        }
    }
    
    echo "<form method='POST'>";
    echo "<p><strong>Reset Password for User:</strong></p>";
    echo "<select name='user_id' required>";
    echo "<option value=''>Select User...</option>";
    
    // Get users again for dropdown
    $result = $conn->query("SELECT id, player FROM PLAYER ORDER BY player");
    while ($row = $result->fetch_assoc()) {
        echo "<option value='" . $row['id'] . "'>" . htmlspecialchars($row['player']) . " (ID: " . $row['id'] . ")</option>";
    }
    
    echo "</select><br><br>";
    echo "<input type='text' name='new_password' placeholder='New Password' required><br><br>";
    echo "<input type='submit' name='reset_password' value='Reset Password' onclick='return confirm(\"Are you sure you want to reset this password?\")'>";
    echo "</form>";
    
    echo "</div>";
    
    // Seed default users section
    echo "<div class='debug-section'>";
    echo "<h2>🌱 Recreate Default Users</h2>";
    
    if (isset($_POST['create_defaults'])) {
        $defaultUsers = [
            ['username' => 'admin', 'password' => 'admin123', 'role' => 'admin'],
            ['username' => 'player1', 'password' => 'mypassword', 'role' => 'player']
        ];
        
        foreach ($defaultUsers as $user) {
            // Delete existing user first
            $deleteStmt = $conn->prepare("DELETE FROM PLAYER WHERE player = ?");
            $deleteStmt->bind_param("s", $user['username']);
            $deleteStmt->execute();
            $deleteStmt->close();
            
            // Create new user
            $hashedPassword = password_hash($user['password'], PASSWORD_DEFAULT);
            $insertStmt = $conn->prepare("INSERT INTO PLAYER (player, player_password, player_role) VALUES (?, ?, ?)");
            $insertStmt->bind_param("sss", $user['username'], $hashedPassword, $user['role']);
            
            if ($insertStmt->execute()) {
                echo "<div class='success'>✅ Created user: {$user['username']} with password: {$user['password']}</div>";
            } else {
                echo "<div class='error'>❌ Failed to create user: {$user['username']}</div>";
            }
            $insertStmt->close();
        }
    }
    
    echo "<form method='POST'>";
    echo "<p>This will recreate the default admin and player1 accounts with known passwords:</p>";
    echo "<ul>";
    echo "<li><strong>admin</strong> / admin123</li>";
    echo "<li><strong>player1</strong> / mypassword</li>";
    echo "</ul>";
    echo "<input type='submit' name='create_defaults' value='Recreate Default Users' onclick='return confirm(\"This will delete and recreate admin and player1 accounts. Continue?\")'>";
    echo "</form>";
    
    echo "</div>";
    
    $conn->close();
    
} catch (Exception $e) {
    echo "<div class='error'>Database error: " . htmlspecialchars($e->getMessage()) . "</div>";
}

echo "<div class='debug-section'>";
echo "<h2>🔗 Quick Links</h2>";
echo "<a href='debug_tables.php'>← Back to Debug Tables</a> | ";
echo "<a href='login.php'>Login Page</a> | ";
echo "<a href='game.php'>Game</a>";
echo "</div>";
?>
