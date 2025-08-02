<?php
// Database seeding script for Fifteen Puzzle game

// Database configuration
$servername = "localhost";
$username = "divory2";
$password = "divory2";
$dbname = "divory2";

try {
    // Create connection
    $conn = new mysqli($servername, $username, $password);
    
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    echo "Connected successfully to MySQL server\n";
    
    // Create database if it doesn't exist
    $sql = "CREATE DATABASE IF NOT EXISTS $dbname";
    if ($conn->query($sql) === TRUE) {
        echo "Database '$dbname' created successfully or already exists\n";
    } else {
        throw new Exception("Error creating database: " . $conn->error);
    }
    
    // Select the database
    $conn->select_db($dbname);
    
    // Create PLAYER table
    $sql = "CREATE TABLE IF NOT EXISTS PLAYER (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        player VARCHAR(30) NOT NULL,
        player_password VARCHAR(255) NOT NULL,
        player_role VARCHAR(10) NOT NULL,
        login_date DATE DEFAULT NULL,
        logout_date DATE DEFAULT NULL
    )";
    
    if ($conn->query($sql) === TRUE) {
        echo "Table 'PLAYER' created successfully or already exists\n";
    } else {
        throw new Exception("Error creating PLAYER table: " . $conn->error);
    }
    
    // Create GAME_SESSIONS table for tracking game sessions
    $sql = "CREATE TABLE IF NOT EXISTS GAME_SESSIONS (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        player_id INT(6) UNSIGNED,
        session_start TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        session_end TIMESTAMP NULL,
        moves_count INT DEFAULT 0,
        completed BOOLEAN DEFAULT FALSE,
        background_image VARCHAR(255),
        FOREIGN KEY (player_id) REFERENCES PLAYER(id) ON DELETE CASCADE
    )";
    
    if ($conn->query($sql) === TRUE) {
        echo "Table 'GAME_SESSIONS' created successfully or already exists\n";
    } else {
        throw new Exception("Error creating GAME_SESSIONS table: " . $conn->error);
    }
    
    // Insert sample players (for testing purposes)
    $samplePlayers = [
        ['username' => 'testuser1', 'password' => 'password123', 'role' => 'player'],
        ['username' => 'player1', 'password' => 'mypassword', 'role' => 'player'],
        ['username' => 'admin', 'password' => 'adminpass', 'role' => 'admin']
    ];
    
    echo "Inserting sample players...\n";
    
    foreach ($samplePlayers as $player) {
        // Check if player already exists
        $checkStmt = $conn->prepare("SELECT id FROM PLAYER WHERE player = ?");
        $checkStmt->bind_param("s", $player['username']);
        $checkStmt->execute();
        $result = $checkStmt->get_result();
        
        if ($result->num_rows == 0) {
            // Player doesn't exist, insert new player
            $hashedPassword = password_hash($player['password'], PASSWORD_DEFAULT);
            $insertStmt = $conn->prepare("INSERT INTO PLAYER (player, player_password, player_role) VALUES (?, ?, ?)");
            $insertStmt->bind_param("sss", $player['username'], $hashedPassword, $player['role']);
            
            if ($insertStmt->execute()) {
                echo "Sample player '{$player['username']}' inserted successfully\n";
            } else {
                echo "Error inserting player '{$player['username']}': " . $insertStmt->error . "\n";
            }
            $insertStmt->close();
        } else {
            echo "Player '{$player['username']}' already exists, skipping...\n";
        }
        $checkStmt->close();
    }
    
    // Create indexes for better performance
    $indexes = [
        "CREATE INDEX IF NOT EXISTS idx_player_username ON PLAYER(player)",
        "CREATE INDEX IF NOT EXISTS idx_game_sessions_player ON GAME_SESSIONS(player_id)",
        "CREATE INDEX IF NOT EXISTS idx_game_sessions_completed ON GAME_SESSIONS(completed)"
    ];
    
    echo "Creating database indexes...\n";
    foreach ($indexes as $indexSQL) {
        if ($conn->query($indexSQL) === TRUE) {
            echo "Index created successfully\n";
        } else {
            echo "Error creating index: " . $conn->error . "\n";
        }
    }
    
    echo "\n=== Database seeding completed successfully! ===\n";
    echo "Database: $dbname\n";
    echo "Tables created: PLAYER, GAME_SESSIONS\n";
    echo "Sample players added for testing\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>
