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
  <div class="user-info">
    <p>Welcome, <?php echo htmlspecialchars($_SESSION['player']); ?>! 
    <span id="roleIndicator" class="role-indicator role-<?php echo $_SESSION['role'] ?? 'player'; ?>">
      Role: <?php echo ucfirst($_SESSION['role'] ?? 'Player'); ?>
    </span>
    <a href="logout.php">Logout</a></p>
  </div>
  <div class="menu-container">
    <div class="form-fields-container">
      <form name="menu_options" onsubmit="return startGame(event)">
        <div class="Selector-container" id="gameControls">
          <select name="selectBackground" id="backgroundSelector">
            <option value="" disabled selected hidden>Select a background</option>
            <option value="images/tlk1.jpg">sample 1</option>
            <option value="images/tlk2.jpg">sample 2</option>
          </select>
          <br><br>
          <img id="backgroundPreview" class="image-preview" src="" alt="image preview">
        </div>

        <div class="upload-container" id="uploadContainer">
          <div class="input-container">
            <input type="text" name="uploadImage" id="url" placeholder="Enter URL to upload image">
            <br><br>
            <img src="" alt="upload preview" id="uploadedPreview">
          </div>
          <div class="submit-container">
            <input type="submit" id="uploadButton" value="upload" class="player-only">
            <br><br>
            <input type="submit" id="add" value="add" hidden>
            <br><br>
            <input type="submit" id="no" value="no" hidden>
          </div>
        </div>

        <div class="start-container">
          <input type="submit" id="startGameBtn" value="start" class="player-only">
        </div>
      </form>
    </div>
  </div>

  <!-- Admin Panel (only visible to admins) -->
  <div id="adminPanel" class="admin-panel" style="display: none;">
    <h3>Admin Panel</h3>
    <div class="admin-controls">
      <button id="userManagement" class="admin-only">Manage Users</button>
      <button id="systemSettings" class="admin-only">System Settings</button>
      <button id="viewAllGames" class="admin-only">View All Games</button>
    </div>
  </div>

  <!-- Statistics Panel -->
  <div id="gameStatistics" class="statistics-panel" style="display: none;">
    <h3>Your Game Statistics</h3>
    <div id="playerStats"></div>
  </div>

  <div id="allGameStatistics" class="statistics-panel" style="display: none;">
    <h3>All Players Statistics</h3>
    <div id="allStats"></div>
  </div>

  <div class="game-container">
    <div id="gameBoard"></div>
  </div>

  <script src="rbac.js"></script>
  <script src="admin.js"></script>
  <script src="gameboard.js"></script>
  <script>
    // Initialize RBAC system with user data from PHP session
    document.addEventListener('DOMContentLoaded', function() {
      const userRole = '<?php echo $_SESSION['role'] ?? 'player'; ?>';
      const userName = '<?php echo htmlspecialchars($_SESSION['player']); ?>';
      
      // Initialize RBAC
      RBAC.initializeRBAC(userRole, userName);
    });
  </script>
</body>
</html>
