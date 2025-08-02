<?php
session_start();
ob_start();  // Start output buffering
require_once 'db_config.php';

$isvalid = true;
 
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $player = $_POST['Player'];
    $password = $_POST['password'];
   
    try {
        $conn = getDBConnection();
        
        // Create table if it doesn't exist
        $sql = "CREATE TABLE IF NOT EXISTS PLAYER (
            id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            player VARCHAR(30) NOT NULL,
            player_password VARCHAR(255) NOT NULL,
            player_role VARCHAR(10) NOT NULL,
            login_date DATE DEFAULT NULL,
            logout_date DATE DEFAULT NULL
        )";
        
        if ($conn->query($sql) === TRUE) {
            //echo "Table Player created successfully\n";
        } else {
           // echo "Error creating table: " . $conn->error . "\n";
        }

        $currentTime = date("Y-m-d H:i:s");
        $stmt = $conn->prepare("SELECT player, player_password, player_role, id FROM PLAYER WHERE player = ?");
        $stmt->bind_param("s", $player);
        $stmt->execute();
        $result = $stmt->get_result();

if ($result) {
    if ($result->num_rows > 0) {
        echo"query for player if player exists <br>";
        // Loop through results (should really be only 1 if player names are unique)
        $row = $result->fetch_assoc();
        echo"Player name". $row["player"];
        echo "Player name from user: $player";

        if ($row && $row["player"] == $player) {
            if (password_verify($password, $row["player_password"])) {
                $_SESSION["player"] = $player;
                 $_SESSION["role"] = $row["player_role"];
                 $_SESSION["gameStart"] = "true";
                 $_SESSION["playerId"] = $row["id"];
                 // query to update login date
                //  $loginTime = "UPDATE PLAYER SET login_date = '$currentTime' WHERE id = '{$_SESSION["playerId"]}'";
                // // if($conn->query($loginTime) === TRUE) {
                    
                // // }else{
                // //     echo "Error running query: " . $conn->error;
                // // }
                echo"password is correct";
                header("Location: game.php?player=" . urlencode($player) . "&role=" . urlencode($row["player_role"]));
                exit();

            } else {
                header("Location: login.php?error=password_incorrect");
                exit();
            }
        }
        else{
            header("Location: login.php?error=Wrong_player_name");
            exit();
        }
        
 } else {
        // No player found
       // echo "Player not found";
        header("Location: login.php?error=player_not_registered");
        exit();
    }
        } else {
            echo "Error running query: " . $conn->error;
        }
        
    } catch (Exception $e) {
        error_log("Login error: " . $e->getMessage());
        header("Location: login.php?error=database_error");
        exit();
    }

    $conn->close();
}
ob_end_flush(); // Flush output at the end
?>