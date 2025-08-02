<?php



if($_SERVER["REQUEST_METHOD"] == "POST"){



    $player = $_POST['Player'];
    $password = $_POST['password'];    
    $host = "localhost";
    $user = "divory2";
    $pass = "divory2";
    $dbname = "divory2";
    
    // Create connection
    $conn = new mysqli($host, $user, $pass, $dbname);
    
    // Check connection
    if ($conn->connect_error) {
        echo "Could not connect to server\n";
        die("Connection failed: " . $conn->connect_error);
    }




    //check to see if player is already a registered
    $stmt = $conn->prepare("SELECT player, player_password FROM PLAYER WHERE player = ?");
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
                    echo"password is correct";
                    header("Location: login.php?error=registred");
                    exit();
                } else {
                    //player is in the db wrong password
                    header("Location: login.php?error=password_incorrect_register");
                    exit();
                }
            }
            else{
                // player is in db just not the correct casing for player in db
                header("Location: login.php?error=wrong_player_casing");
                exit();
            }
            
     } else {

        // player is not registered in db so insert player into db


       //Inserting data into the table
       $currentTime = date("Y-m-d H:i:s");
       $stmt = $conn->prepare("INSERT INTO PLAYER (player, player_password, player_role, login_date) VALUES (?, ?, ?, ?)");
       $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
       $role = "player";
       $stmt->bind_param("ssss", $player, $hashedPassword, $role, $currentTime);
       
       if ($stmt->execute()) {
           session_start();
           $_SESSION["player"] = $player;
           $_SESSION["role"] = "player";
           $_SESSION["gameStart"] = "true";
           $_SESSION["playerId"] = $conn->insert_id;
           
           header("Location: game.php?player=" . urlencode($player) . "&role=player");
           exit();
       } else {
           echo "Error: " . $stmt->error;
       }

        
        }
    } else {
       echo "Error running query: " . $conn->error;
    }
    

    return $isvalid;
}
?>