<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $player = $_POST['Player'];
    $password = $_POST['password'];
    // TODO: connect to DB and insert data safely
    echo "Player: $player, Password: $password";
    
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
        echo "Table Player created successfully\n";
    } else {
        echo "Error creating table: " . $conn->error . "\n";
    }
    // After successful connection ($conn) and before inserts:





    
    // Inserting data into the table
    $data = [
        ["Dee26",password_hash("devon123", PASSWORD_DEFAULT), "admin"],
       
    ];
    
    foreach ($data as $student) {
        $sql = "INSERT INTO PLAYER (player, player_password, player_role) VALUES ('{$student[0]}', '{$student[1]}', '{$student[2]}')";
        if ($conn->query($sql) === TRUE) {
            echo "New record created successfully\n";
        } else {
            echo "Error: " . $sql . "\n" . $conn->error . "\n";
        }
    }
    
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

?>