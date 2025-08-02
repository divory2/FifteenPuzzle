<?php
session_start();

// Update logout date in database if user is logged in
if (isset($_SESSION['playerId'])) {
    $host = "localhost";
    $user = "divory2";
    $pass = "divory2";
    $dbname = "divory2";
    
    $conn = new mysqli($host, $user, $pass, $dbname);
    
    if (!$conn->connect_error) {
        $currentTime = date("Y-m-d H:i:s");
        $stmt = $conn->prepare("UPDATE PLAYER SET logout_date = ? WHERE id = ?");
        $stmt->bind_param("si", $currentTime, $_SESSION['playerId']);
        $stmt->execute();
        $stmt->close();
        $conn->close();
    }
}

// Destroy session and redirect to login page
session_destroy();
header("Location: login.php");
exit();
?>
