<?php
// session_start();
// if (!isset($_SESSION['player'])) {
//     header("Location: login.php?error=not_logged_in");
//     exit();
// }
?>
<html lang="eng">
    <head>
    <link rel="stylesheet" href="login.css">
    </head>
    <body>
        <h1>Fifteen Puzzle</h1>
        <div class="menu-container">
             <!-- inside menu-container -->
             <div class="form-fields-container">
            <form  name="menu_options" onsubmit="return startGame(event)">
                
                <!-- form fields below -->
                 
                    <!-- form-fields-container below -->

                    <div class="Selector-container">
                        <!-- inside Selector-container -->
                    <select name="selectBackground" id="backgroundSelector">
                        <!-- inside select -->
                        <option value="" disabled selected hidden>Select a background</option>
                        <option value="devon.heic.png" data-preview="devon.heic.png">sample 1</option>
                        <option value="shopping.webp">sample2</option>
                    </select>
                    <br><br>
                    <img id="backgroundPreview"class="image-preview" src="" alt="image preview">

                        <!-- end of Selector-container -->
                    </div>
                    <!--form  -->
                    <div class="upload-container">
                        <div class="input-container">
                            <input type="text" name="uploadImage" id="url" placeholder="Enter URL to upload image">
                            <br><br>
                            <img src="" alt="upload preview" id="uploadedPreview" onload="handleImage()" onerror="handleImageError()">
                        </div>
                        <div class="submit-container">
                        <input type="submit" name="uploadButton" id="uploadButton" value="upload">
                        <br><br>
                        <input type="submit" name="addButton" id="add" value="add" hidden >
                        <br><br>
                        <input type="submit" name="noButton" id="no" value="no" hidden=>
                        </div>
                       
                    </div>
                    
                
                

                

            </form>
            </div>
        </div>



        <script type="text/javascript" src="gameboard.js"></script>
    </body>
</html>