<?php
// Database configuration file for Fifteen Puzzle game

// Database connection parameters
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'divory2');
define('DB_PASSWORD', 'divory2');
define('DB_NAME', 'divory2');

/**
 * Get database connection
 * @return mysqli Database connection object
 * @throws Exception If connection fails
 */
function getDBConnection() {
    static $conn = null;
    
    if ($conn === null) {
        $conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
        
        // Check connection
        if ($conn->connect_error) {
            throw new Exception("Connection failed: " . $conn->connect_error);
        }
        
        // Set charset to utf8
        $conn->set_charset("utf8");
    }
    
    return $conn;
}

/**
 * Close database connection
 */
function closeDBConnection() {
    global $conn;
    if ($conn) {
        $conn->close();
        $conn = null;
    }
}
?>
