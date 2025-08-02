<?php
session_start();
if (!isset($_SESSION['player'])) {
    header("Location: login.php?error=not_logged_in");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Fifteen Puzzle</title>
  <link rel="stylesheet" href="login.css">
</head>
<body>
  <h1>Fifteen Puzzle</h1>
  <div class="menu-container">
    <div class="form-fields-container">
      <form name="menu_options" onsubmit="return startGame(event)">
        <div class="Selector-container">
          <select name="selectBackground" id="backgroundSelector">
            <option value="" disabled selected hidden>Select a background</option>
            <option value="devon.heic.png">sample 1</option>
            <option value="shopping.webp">sample 2</option>
          </select>
          <br><br>
          <img id="backgroundPreview" class="image-preview" src="" alt="image preview">
        </div>

        <div class="upload-container">
          <div class="input-container">
            <input type="text" name="uploadImage" id="url" placeholder="Enter URL to upload image">
            <br><br>
            <img src="" alt="upload preview" id="uploadedPreview">
          </div>
          <div class="submit-container">
            <input type="submit" id="uploadButton" value="upload">
            <br><br>
            <input type="submit" id="add" value="add" hidden>
            <br><br>
            <input type="submit" id="no" value="no" hidden>
          </div>
        </div>

        <div class="start-container">
          <input type="submit" id="start" value="start">
        </div>
      </form>
    </div>
  </div>


  <div id="stats">
        <span id="timer">Time: 0s</span>
        &nbsp;|&nbsp;
        <span id="moveCounter">Moves: 0</span>
</div>


  <div class="game-container">
    <div id="gameBoard"></div>
  </div>

  <script src="gameboard.js"></script>
</body>
</html>
