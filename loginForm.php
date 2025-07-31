<?php
ob_start();  // Start output buffering

 $isvalid = true;
 
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $player = $_POST['Player'];
    $password = $_POST['password'];
   
    // TODO: connect to DB and insert data safely
   // echo "Player: $player, Password: $password";
    
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
    
    // SQL to create table
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
    // After successful connection ($conn) and before inserts:

 //Inserting data into the table
//  $data = [
//     ["Dee27",password_hash("devon123", PASSWORD_DEFAULT), "admin"],
   
// ];

// foreach ($data as $student) {
//     $sql = "INSERT INTO PLAYER (player, player_password, player_role) VALUES ('{$student[0]}', '{$student[1]}', '{$student[2]}')";
//     if ($conn->query($sql) === TRUE) {
//         echo "New record created successfully\n";
//     } else {
//         echo "Error: " . $sql . "\n" . $conn->error . "\n";
//     }
// }










// Allow login_date and logout_date to be NULL by default
//$alter2 = "ALTER TABLE PLAYER MODIFY logout_date DATE DEFAULT NULL";


$isplayer = "SELECT player, player_password FROM PLAYER WHERE player = '$player'";
$result = $conn->query($isplayer);

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
                header("Location: game.html");
                exit();
            } else {
                header("Location: login.html?error=password_incorrect");
                exit();
            }
        }
        else{
            header("Location: login.html?error=Wrong_player_name");
            exit();
        }
        
 } else {
        // No player found
       // echo "Player not found";
        header("Location: login.html?error=player_not_registered");
        exit();
    }
} else {
   echo "Error running query: " . $conn->error;
}


// if ($conn->query($alter2) === TRUE) {
//     echo "logout_date column altered successfully.<br>";
// } else {
//     echo "Error altering logout_date: " . $conn->error . "<br>";
// }




    
   
    
    // Function to sort data by GPA
    // $sql = "SELECT * FROM STUDENTS ORDER BY GPA DESC";
    // $result = $conn->query($sql);
    
    // if ($result->num_rows > 0) {
    //     echo "Sorted data by GPA:\n";
    //     while ($row = $result->fetch_assoc()) {
    //         echo "id: " . $row["id"] . " - Name: " . $row["firstname"] . " " . $row["lastname"] . " - GPA: " . $row["GPA"] . "\n";
    //     }
    // } else {
    //     echo "0 results\n";
    // }
    
    $conn->close();
    

}
ob_end_flush(); // Flush output at the end
?>