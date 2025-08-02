<?php
session_start();
require_once 'db_config.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Security check - only allow access if logged in as admin or in development
$isDevelopment = true; // Set to false in production
// if (!$isDevelopment && (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin')) {
//     die('Access denied. Admin privileges required.');
// }

try {
    $conn = getDBConnection();
    
    // Function to display table data
    function displayTable($conn, $tableName, $title) {
        echo "<div class='table-container'>";
        echo "<h2>$title</h2>";
        
        // Check if table exists
        $checkTable = $conn->query("SHOW TABLES LIKE '$tableName'");
        if ($checkTable->num_rows == 0) {
            echo "<p class='warning'>Table '$tableName' does not exist.</p>";
            echo "</div>";
            return;
        }
        
        // Get table structure
        $structure = $conn->query("DESCRIBE $tableName");
        echo "<h3>Table Structure:</h3>";
        echo "<table class='debug-table'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        while ($row = $structure->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['Field']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Type']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Null']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Key']) . "</td>";
            echo "<td>" . htmlspecialchars($row['Default'] ?? 'NULL') . "</td>";
            echo "<td>" . htmlspecialchars($row['Extra']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Get table data
        $result = $conn->query("SELECT * FROM $tableName ORDER BY id DESC LIMIT 50");
        if ($result->num_rows > 0) {
            echo "<h3>Table Data (Last 50 records):</h3>";
            echo "<table class='debug-table'>";
            
            // Get column names
            $columns = [];
            while ($field = $result->fetch_field()) {
                $columns[] = $field->name;
            }
            
            // Table header
            echo "<tr>";
            foreach ($columns as $column) {
                echo "<th>" . htmlspecialchars($column) . "</th>";
            }
            echo "</tr>";
            
            // Table data
            $result->data_seek(0); // Reset result pointer
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                foreach ($columns as $column) {
                    $value = $row[$column];
                    if ($column === 'player_password') {
                        $value = '***HIDDEN*** (Length: ' . strlen($value) . ')';
                    } elseif (is_null($value)) {
                        $value = '<em>NULL</em>';
                    } elseif ($value === '') {
                        $value = '<em>EMPTY</em>';
                    }
                    echo "<td>" . htmlspecialchars($value) . "</td>";
                }
                echo "</tr>";
            }
            echo "</table>";
            echo "<p class='record-count'>Total records: " . $result->num_rows . "</p>";
        } else {
            echo "<p class='warning'>No data found in table '$tableName'.</p>";
        }
        
        echo "</div><hr>";
    }
    
    // Function to show session data
    function displaySessionData() {
        echo "<div class='table-container'>";
        echo "<h2>Current Session Data</h2>";
        if (empty($_SESSION)) {
            echo "<p class='warning'>No session data available.</p>";
        } else {
            echo "<table class='debug-table'>";
            echo "<tr><th>Session Key</th><th>Value</th></tr>";
            foreach ($_SESSION as $key => $value) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($key) . "</td>";
                echo "<td>" . htmlspecialchars($value) . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
        echo "</div><hr>";
    }
    
    // Function to show database info
    function displayDatabaseInfo($conn) {
        echo "<div class='table-container'>";
        echo "<h2>Database Information</h2>";
        
        // Show all tables
        $tables = $conn->query("SHOW TABLES");
        echo "<h3>Available Tables:</h3>";
        echo "<ul>";
        while ($table = $tables->fetch_array()) {
            echo "<li>" . htmlspecialchars($table[0]) . "</li>";
        }
        echo "</ul>";
        
        // Show MySQL version
        $version = $conn->query("SELECT VERSION() as version");
        $versionRow = $version->fetch_assoc();
        echo "<p><strong>MySQL Version:</strong> " . htmlspecialchars($versionRow['version']) . "</p>";
        
        echo "</div><hr>";
    }

} catch (Exception $e) {
    echo "<div class='error'>Database connection error: " . htmlspecialchars($e->getMessage()) . "</div>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Debug Tables - Fifteen Puzzle</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f5f5f5;
        }
        
        .header {
            background-color: #2c3e50;
            color: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .table-container {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .debug-table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }
        
        .debug-table th,
        .debug-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        
        .debug-table th {
            background-color: #3498db;
            color: white;
            font-weight: bold;
        }
        
        .debug-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        .debug-table tr:hover {
            background-color: #e8f4f8;
        }
        
        .warning {
            color: #e67e22;
            font-style: italic;
            background-color: #fef9e7;
            padding: 10px;
            border-left: 4px solid #f39c12;
            margin: 10px 0;
        }
        
        .error {
            color: #e74c3c;
            background-color: #fdf0ef;
            padding: 15px;
            border-left: 4px solid #e74c3c;
            margin: 10px 0;
            border-radius: 4px;
        }
        
        .record-count {
            font-weight: bold;
            color: #27ae60;
            margin-top: 10px;
        }
        
        .nav-buttons {
            margin: 20px 0;
        }
        
        .nav-buttons a {
            display: inline-block;
            padding: 10px 20px;
            background-color: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin-right: 10px;
        }
        
        .nav-buttons a:hover {
            background-color: #2980b9;
        }
        
        hr {
            border: none;
            height: 2px;
            background-color: #ecf0f1;
            margin: 30px 0;
        }
        
        h2 {
            color: #2c3e50;
            border-bottom: 2px solid #3498db;
            padding-bottom: 5px;
        }
        
        h3 {
            color: #34495e;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🐛 Debug Tables - Fifteen Puzzle Database</h1>
        <p>This page shows all database tables and their contents for debugging purposes.</p>
        <p><strong>Current Time:</strong> <?php echo date('Y-m-d H:i:s'); ?></p>
    </div>
    
    <div class="nav-buttons">
        <a href="game.php">← Back to Game</a>
        <a href="admin.php">Admin Panel</a>
        <a href="login.php">Login Page</a>
        <a href="javascript:location.reload()">🔄 Refresh</a>
    </div>

    <?php
    if (isset($conn)) {
        // Display session data first
        displaySessionData();
        
        // Display database info
        displayDatabaseInfo($conn);
        
        // Display all relevant tables
        displayTable($conn, 'PLAYER', 'PLAYER Table');
        displayTable($conn, 'GAME_SESSIONS', 'GAME_SESSIONS Table');
        displayTable($conn, 'IMAGES', 'IMAGES Table');
        
        // Close connection
        $conn->close();
    }
    ?>
    
    <div class="table-container">
        <h2>Quick Actions</h2>
        <div class="nav-buttons">
            <a href="?action=clear_sessions" onclick="return confirm('Clear all sessions? This will log out all users.')">Clear Sessions</a>
            <a href="seed_database.php">Seed Database</a>
        </div>
    </div>
    
    <div class="table-container">
        <h2>Debug Information</h2>
        <p><strong>PHP Version:</strong> <?php echo phpversion(); ?></p>
        <p><strong>Server Time:</strong> <?php echo date('Y-m-d H:i:s'); ?></p>
        <?php if (isset($_SESSION['player'])): ?>
            <p><strong>Logged in as:</strong> <?php echo htmlspecialchars($_SESSION['player']); ?> 
               (Role: <?php echo htmlspecialchars($_SESSION['role'] ?? 'unknown'); ?>)</p>
        <?php else: ?>
            <p><strong>Status:</strong> Not logged in</p>
        <?php endif; ?>
    </div>

    <?php
    // Handle quick actions
    if (isset($_GET['action']) && $_GET['action'] === 'clear_sessions') {
        session_destroy();
        echo "<script>alert('Sessions cleared!'); window.location.href = 'debug_tables.php';</script>";
    }
    ?>
</body>
</html>
