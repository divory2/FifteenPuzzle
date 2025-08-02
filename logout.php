<?php
session_start();
require_once 'db_config.php';

// Update logout date in database if user is logged in
if (isset($_SESSION['playerId'])) {
    try {
        $conn = getDBConnection();
        $currentTime = date("Y-m-d H:i:s");
        $stmt = $conn->prepare("UPDATE PLAYER SET logout_date = ? WHERE id = ?");
        $stmt->bind_param("si", $currentTime, $_SESSION['playerId']);
        $stmt->execute();
        $stmt->close();
    } catch (Exception $e) {
        error_log("Logout error: " . $e->getMessage());
    }
}

// Destroy session and redirect to login page
session_destroy();
header("Location: login.php");
exit();
?>
