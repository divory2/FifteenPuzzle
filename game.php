<?php
session_start();
require_once 'db_config.php';

// Check if user is logged in
if (!isset($_SESSION['player'])) {
    header("Location: login.php?error=not_logged_in");
    exit();
}

// Handle image upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'upload_image') {
    header('Content-Type: application/json');
    
    try {
        $conn = getDBConnection();
        
        // Validate user permission - both players and admins can upload images
        if (!isset($_SESSION['player']) || !in_array($_SESSION['role'], ['player', 'admin'])) {
            throw new Exception('You need to be logged in as a player or admin to upload images');
        }
        
        $imageName = trim($_POST['image_name'] ?? '');
        $imageUrl = trim($_POST['image_url'] ?? '');
        $playerId = $_SESSION['playerId'] ?? null;
        
        // Double-check that we have a valid player ID
        if (!$playerId) {
            throw new Exception('Invalid user session. Please log in again.');
        }
        
        if (empty($imageName) || empty($imageUrl)) {
            throw new Exception('Image name and URL are required');
        }
        
        // Validate URL format
        if (!filter_var($imageUrl, FILTER_VALIDATE_URL)) {
            throw new Exception('Invalid URL format');
        }
        
        // Download and save image
        $imageData = @file_get_contents($imageUrl);
        if ($imageData === false) {
            throw new Exception('Failed to download image from URL');
        }
        
        // Validate image
        $tempFile = tempnam(sys_get_temp_dir(), 'img_validate_');
        file_put_contents($tempFile, $imageData);
        $imageInfo = @getimagesize($tempFile);
        unlink($tempFile);
        
        if ($imageInfo === false) {
            throw new Exception('Invalid image file');
        }
        
        // Generate unique filename
        $extension = '';
        switch ($imageInfo[2]) {
            case IMAGETYPE_JPEG:
                $extension = 'jpg';
                break;
            case IMAGETYPE_PNG:
                $extension = 'png';
                break;
            case IMAGETYPE_GIF:
                $extension = 'gif';
                break;
            default:
                throw new Exception('Unsupported image format. Only JPG, PNG, and GIF are allowed.');
        }
        
        $uniqueFilename = 'img_' . uniqid() . '_' . time() . '.' . $extension;
        $uploadPath = 'images/' . $uniqueFilename;
        
        // Create images directory if it doesn't exist
        if (!is_dir('images')) {
            mkdir('images', 0755, true);
        }
        
        // Save image to server
        if (!file_put_contents($uploadPath, $imageData)) {
            throw new Exception('Failed to save image to server');
        }
        
        // Save to database
        $stmt = $conn->prepare("INSERT INTO IMAGES (image_name, original_url, file_path, uploaded_by, upload_date) VALUES (?, ?, ?, ?, NOW())");
        $stmt->bind_param("sssi", $imageName, $imageUrl, $uploadPath, $playerId);
        
        if (!$stmt->execute()) {
            // Clean up file if database insert fails
            unlink($uploadPath);
            throw new Exception('Failed to save image information to database');
        }
        
        $imageId = $conn->insert_id;
        
        echo json_encode([
            'success' => true,
            'message' => 'Image uploaded successfully',
            'image_id' => $imageId,
            'file_path' => $uploadPath,
            'image_name' => $imageName
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
    exit();
}

// Get user images for dropdown
function getUserImages($conn, $playerId) {
    $images = [];
    try {
        // Get user's uploaded images
        $stmt = $conn->prepare("SELECT id, image_name, file_path FROM IMAGES WHERE uploaded_by = ? ORDER BY upload_date DESC");
        $stmt->bind_param("i", $playerId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $images[] = $row;
        }
    } catch (Exception $e) {
        error_log("Error fetching user images: " . $e->getMessage());
    }
    return $images;
}

// Get database connection and user images
$conn = getDBConnection();
$playerId = $_SESSION['playerId'] ?? 0;
$userImages = getUserImages($conn, $playerId);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fifteen Puzzle Game</title>
    <link rel="stylesheet" href="login.css">
    <style>
        .game-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 10px;
            margin-bottom: 30px;
        }
        
        .user-info {
            background: rgba(255,255,255,0.1);
            padding: 15px;
            border-radius: 8px;
            margin-top: 15px;
        }
        
        .game-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .controls-section {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .controls-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            align-items: start;
        }
        
        .image-selection {
            border-right: 2px solid #eee;
            padding-right: 30px;
        }
        
        .image-upload {
            padding-left: 30px;
        }
        
        .section-title {
            color: #2c3e50;
            margin-bottom: 20px;
            font-size: 1.3em;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
        }
        
        .image-preview {
            max-width: 200px;
            max-height: 200px;
            border: 2px solid #ddd;
            border-radius: 8px;
            margin-top: 15px;
        }
        
        .upload-form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        
        .input-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        
        .input-group label {
            font-weight: 600;
            color: #2c3e50;
        }
        
        .game-board-container {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .game-stats {
            display: flex;
            justify-content: center;
            gap: 30px;
            margin-bottom: 20px;
        }
        
        .stat-item {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 18px 28px;
            border-radius: 12px;
            text-align: center;
            border: 1px solid #dee2e6;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
        }
        
        .stat-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .stat-item h4 {
            margin: 0 0 8px 0;
            color: #2c3e50;
            font-size: 0.9em;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .stat-value {
            font-size: 1.8em;
            font-weight: bold;
            color: #3498db;
            text-shadow: 0 1px 2px rgba(0,0,0,0.1);
            transition: transform 0.2s ease;
        }
        
        #gameBoard {
            display: grid;
            grid-template-columns: repeat(4, 120px);
            grid-template-rows: repeat(4, 120px);
            gap: 3px;
            justify-content: center;
            margin: 20px auto;
            background: #2c3e50;
            padding: 15px;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(44, 62, 80, 0.3);
        }
        
        .tile {
            background-size: 400% 400%;
            border: 2px solid #34495e;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
            user-select: none;
        }
        
        .tile:hover {
            border-color: #3498db;
            transform: scale(1.02);
            box-shadow: 0 4px 15px rgba(52, 152, 219, 0.3);
        }
        
        .tile:active {
            transform: scale(0.98);
            transition: all 0.1s ease;
        }
        
        .tile.dragging {
            z-index: 10;
            transform: rotate(2deg) scale(1.05);
            box-shadow: 0 8px 25px rgba(52, 152, 219, 0.4);
            border-color: #3498db;
        }
        
        .tile.moved {
            animation: tileMove 0.3s ease-out;
        }
        
        .tile.invalid-move {
            animation: invalidMove 0.3s ease-out;
        }
        
        .tile-number {
            position: absolute;
            top: 5px;
            left: 5px;
            background: rgba(0, 0, 0, 0.7);
            color: white;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
            z-index: 10;
            pointer-events: none;
        }
        
        @keyframes tileMove {
            0% { transform: scale(1.1); }
            50% { transform: scale(0.95) rotate(1deg); }
            100% { transform: scale(1) rotate(0deg); }
        }
        
        @keyframes invalidMove {
            0% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
            100% { transform: translateX(0); }
        }
        
        .game-completed {
            animation: gameWin 2s ease-in-out;
        }
        
        @keyframes gameWin {
            0% { box-shadow: 0 8px 25px rgba(44, 62, 80, 0.3); }
            25% { box-shadow: 0 8px 35px rgba(46, 204, 113, 0.6); }
            50% { box-shadow: 0 8px 45px rgba(241, 196, 15, 0.6); }
            75% { box-shadow: 0 8px 35px rgba(231, 76, 60, 0.6); }
            100% { box-shadow: 0 8px 35px rgba(52, 152, 219, 0.6); }
        }
        
        .game-board-container h3 {
            color: #2c3e50;
            margin-bottom: 20px;
            font-size: 1.5em;
            text-align: center;
        }
        
        .tile.empty {
            background: rgba(255,255,255,0.05);
            border: 2px dashed rgba(255,255,255,0.2);
            cursor: default;
            box-shadow: inset 0 0 10px rgba(0,0,0,0.3);
        }
        
        .tile.empty:hover {
            transform: none;
            border-color: rgba(255,255,255,0.2);
            box-shadow: inset 0 0 10px rgba(0,0,0,0.3);
        }
        
        .game-actions {
            margin-top: 20px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            justify-content: center;
        }
        
        .btn {
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #2980b9, #1f639a);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(52, 152, 219, 0.3);
        }
        
        .btn-secondary {
            background: linear-gradient(135deg, #95a5a6, #7f8c8d);
            color: white;
        }
        
        .btn-secondary:hover {
            background: linear-gradient(135deg, #7f8c8d, #6c7b7d);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(149, 165, 166, 0.3);
        }
        
        .btn-info {
            background: linear-gradient(135deg, #e67e22, #d35400);
            color: white;
        }
        
        .btn-info:hover {
            background: linear-gradient(135deg, #d35400, #ba4a00);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(230, 126, 34, 0.3);
        }
        
        .navigation {
            text-align: center;
            margin-top: 30px;
        }
        
        .navigation a {
            margin: 0 15px;
            text-decoration: none;
            color: #3498db;
            font-weight: 500;
        }
        
        .navigation a:hover {
            text-decoration: underline;
        }
        
        .upload-success {
            background: #d4edda;
            color: #155724;
            padding: 10px;
            border-radius: 5px;
            margin-top: 10px;
            display: none;
        }
        
        .upload-error {
            background: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 5px;
            margin-top: 10px;
            display: none;
        }
        
        @media (max-width: 1024px) and (min-width: 769px) {
            #gameBoard {
                grid-template-columns: repeat(4, 110px);
                grid-template-rows: repeat(4, 110px);
                gap: 2px;
            }
        }
        
        @media (max-width: 768px) {
            .controls-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .image-selection {
                border-right: none;
                border-bottom: 2px solid #eee;
                padding-right: 0;
                padding-bottom: 20px;
            }
            
            .image-upload {
                padding-left: 0;
                padding-top: 20px;
            }
            
            .game-stats {
                flex-direction: column;
                gap: 15px;
            }
            
            #gameBoard {
                grid-template-columns: repeat(4, 90px);
                grid-template-rows: repeat(4, 90px);
                gap: 2px;
                padding: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="game-container">
        <div class="game-header">
            <h1>🧩 Fifteen Puzzle Game</h1>
            <div class="user-info">
                <p>Welcome, <strong><?php echo htmlspecialchars($_SESSION['player']); ?></strong>! 
                <span id="roleIndicator" class="role-indicator role-<?php echo $_SESSION['role'] ?? 'player'; ?>">
                    Role: <?php echo ucfirst($_SESSION['role'] ?? 'Player'); ?>
                </span></p>
            </div>
        </div>

        <div class="controls-section">
            <form id="gameForm" onsubmit="return startGame(event)">
                <div class="controls-grid">
                    <!-- Image Selection Section -->
                    <div class="image-selection">
                        <h3 class="section-title">🖼️ Select Background Image</h3>
                        
                        <div class="input-group">
                            <label for="backgroundSelector">Choose from available images:</label>
                            <select name="selectBackground" id="backgroundSelector">
                                <option value="" disabled selected>Select a background image</option>
                                <optgroup label="Default Images">
                                    <option value="images/tlk1.jpg">Sample Image 1</option>
                                    <option value="images/tlk2.jpg">Sample Image 2</option>
                                    <option value="images/background.jpg">Default Background</option>
                                </optgroup>
                                <?php if (!empty($userImages)): ?>
                                <optgroup label="Your Uploaded Images">
                                    <?php foreach ($userImages as $image): ?>
                                        <option value="<?php echo htmlspecialchars($image['file_path']); ?>">
                                            <?php echo htmlspecialchars($image['image_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </optgroup>
                                <?php endif; ?>
                            </select>
                        </div>
                        
                        <img id="backgroundPreview" class="image-preview" src="" alt="Selected image preview" style="display: none;">
                        
                        <div class="game-actions">
                            <input type="submit" name="action" value="🎮 Start Game" class="btn btn-primary" id="startGameBtn">
                            <button type="button" onclick="restartGame()" class="btn btn-secondary" id="restartGameBtn" style="display: none;">🔄 Restart</button>
                            <button type="button" onclick="showSolution()" class="btn btn-info" id="solutionBtn" style="display: none;">💡 Show Solution</button>
                        </div>
                    </div>

                    <!-- Image Upload Section -->
                    <div class="image-upload">
                        <h3 class="section-title">📤 Upload New Image</h3>
                        
                        <div id="uploadForm" class="upload-form">
                            <div class="input-group">
                                <label for="imageUrl">Image URL:</label>
                                <input type="url" id="imageUrl" name="image_url" placeholder="https://example.com/image.jpg">
                            </div>
                            
                            <div class="input-group">
                                <label for="imageName">Image Name:</label>
                                <input type="text" id="imageName" name="image_name" placeholder="Enter a name for this image" maxlength="50">
                            </div>
                            
                            <img id="uploadPreview" class="image-preview" src="" alt="Upload preview" style="display: none;">
                            
                            <button type="button" id="uploadImageBtn" class="btn btn-secondary player-only">Upload Image</button>
                            
                            <div id="uploadSuccess" class="upload-success"></div>
                            <div id="uploadError" class="upload-error"></div>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="game-board-container">
            <h3>🎯 Game Board</h3>
            
            <div class="game-instructions" style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; color: #2c3e50;">
                <strong>📋 How to Play:</strong>
                <ul style="margin: 10px 0 0 20px; text-align: left;">
                    <li>Click tiles adjacent to the empty space to move them</li>
                    <li>You can also drag tiles to the empty space</li>
                    <li>Arrange the tiles in order from 1-15 to win</li>
                    <li>The empty space should be in the bottom-right corner</li>
                </ul>
            </div>
            
            <div class="game-stats">
                <div class="stat-item">
                    <h4>Time</h4>
                    <div id="timer" class="stat-value">0s</div>
                </div>
                <div class="stat-item">
                    <h4>Moves</h4>
                    <div id="moveCounter" class="stat-value">0</div>
                </div>
            </div>
            
            <div id="gameBoard"></div>
            
            <div id="gameMessage" style="margin-top: 20px; font-size: 1.2em; color: #2c3e50;"></div>
        </div>

        <div class="navigation">
            <a href="index.php">🏠 Home</a>
            <?php if ($_SESSION['role'] === 'admin'): ?>
                <a href="admin.php">👑 Admin Panel</a>
            <?php endif; ?>
            <a href="logout.php">🚪 Logout</a>
        </div>
    </div>

    <script src="rbac.js"></script>
    <script src="gameboard.js"></script>
    <script>
        // Make PHP session data immediately available to JavaScript
        window.phpSessionData = {
            role: '<?php echo $_SESSION['role'] ?? 'guest'; ?>',
            player: '<?php echo htmlspecialchars($_SESSION['player'] ?? ''); ?>',
            playerId: '<?php echo $_SESSION['playerId'] ?? ''; ?>'
        };
        
        // Initialize simplified RBAC system
        document.addEventListener('DOMContentLoaded', function() {
            const userRole = window.phpSessionData.role;
            const userName = window.phpSessionData.player;
            
            console.log('🔧 Session data:', window.phpSessionData);
            
            // Initialize RBAC (simplified - just for role display and admin panel access)
            if (typeof RBAC !== 'undefined') {
                RBAC.init(userRole, userName);
                console.log('✅ RBAC initialized for role:', userRole);
            }
            
            // Game control functions
            window.showGameControls = function() {
                document.getElementById('startGameBtn').style.display = 'none';
                document.getElementById('restartGameBtn').style.display = 'inline-block';
                document.getElementById('solutionBtn').style.display = 'inline-block';
            };
            
            window.hideGameControls = function() {
                document.getElementById('startGameBtn').style.display = 'inline-block';
                document.getElementById('restartGameBtn').style.display = 'none';
                document.getElementById('solutionBtn').style.display = 'none';
            };
            
            window.showSolution = function() {
                if (confirm('This will show you the correct arrangement. Are you sure?')) {
                    const gameMessage = document.getElementById('gameMessage');
                    gameMessage.innerHTML = `
                        <div style="margin: 10px 0; padding: 10px; background: #e8f4f8; border-radius: 5px; border-left: 4px solid #3498db;">
                            <strong>💡 Solution Hint:</strong><br>
                            The tiles should be arranged in order from 1-15, with the empty space in the bottom-right corner.<br>
                            <small>Top row: 1, 2, 3, 4 | Second row: 5, 6, 7, 8 | etc.</small>
                        </div>
                    `;
                }
            };
            
            // Image upload functionality
            const uploadBtn = document.getElementById('uploadImageBtn');
            const imageUrlInput = document.getElementById('imageUrl');
            const imageNameInput = document.getElementById('imageName');
            const uploadPreview = document.getElementById('uploadPreview');
            const uploadSuccess = document.getElementById('uploadSuccess');
            const uploadError = document.getElementById('uploadError');
            const backgroundSelector = document.getElementById('backgroundSelector');
            
            // Preview image when URL is entered
            imageUrlInput.addEventListener('blur', function() {
                const url = this.value.trim();
                if (url) {
                    uploadPreview.src = url;
                    uploadPreview.style.display = 'block';
                    uploadPreview.onerror = function() {
                        uploadPreview.style.display = 'none';
                        showUploadError('Invalid image URL or image failed to load');
                    };
                }
            });
            
            // Upload image handler
            uploadBtn.addEventListener('click', function() {
                const imageUrl = imageUrlInput.value.trim();
                const imageName = imageNameInput.value.trim();
                
                if (!imageUrl || !imageName) {
                    showUploadError('Please enter both image URL and name');
                    return;
                }
                
                // Check permission before uploading (simplified - everyone logged in can upload)
                if (!window.phpSessionData || !window.phpSessionData.player) {
                    showUploadError('You need to be logged in to upload images');
                    return;
                }
                
                uploadBtn.disabled = true;
                uploadBtn.textContent = 'Uploading...';
                
                const formData = new FormData();
                formData.append('action', 'upload_image');
                formData.append('image_url', imageUrl);
                formData.append('image_name', imageName);
                
                fetch('game.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showUploadSuccess('Image uploaded successfully!');
                        
                        // Add new option to selector
                        const optgroup = backgroundSelector.querySelector('optgroup[label="Your Uploaded Images"]');
                        if (optgroup) {
                            const option = document.createElement('option');
                            option.value = data.file_path;
                            option.textContent = data.image_name;
                            optgroup.appendChild(option);
                        } else {
                            // Create optgroup if it doesn't exist
                            const newOptgroup = document.createElement('optgroup');
                            newOptgroup.label = 'Your Uploaded Images';
                            const option = document.createElement('option');
                            option.value = data.file_path;
                            option.textContent = data.image_name;
                            newOptgroup.appendChild(option);
                            backgroundSelector.appendChild(newOptgroup);
                        }
                        
                        // Clear form
                        imageUrlInput.value = '';
                        imageNameInput.value = '';
                        uploadPreview.style.display = 'none';
                        
                    } else {
                        showUploadError(data.message || 'Upload failed');
                    }
                })
                .catch(error => {
                    showUploadError('Upload failed: ' + error.message);
                })
                .finally(() => {
                    uploadBtn.disabled = false;
                    uploadBtn.textContent = 'Upload Image';
                });
            });
            
            function showUploadSuccess(message) {
                uploadError.style.display = 'none';
                uploadSuccess.textContent = message;
                uploadSuccess.style.display = 'block';
                setTimeout(() => {
                    uploadSuccess.style.display = 'none';
                }, 5000);
            }
            
            function showUploadError(message) {
                uploadSuccess.style.display = 'none';
                uploadError.textContent = message;
                uploadError.style.display = 'block';
                setTimeout(() => {
                    uploadError.style.display = 'none';
                }, 5000);
            }
            
            // Handle form submission - manage required fields dynamically
            const gameForm = document.getElementById('gameForm');
            if (gameForm) {
                gameForm.addEventListener('submit', function(e) {
                    const submitter = e.submitter;
                    const imageUrlInput = document.getElementById('imageUrl');
                    const imageNameInput = document.getElementById('imageName');
                    
                    if (submitter && submitter.id === 'uploadImageBtn') {
                        // If uploading, make fields required
                        imageUrlInput.setAttribute('required', 'required');
                        imageNameInput.setAttribute('required', 'required');
                    } else {
                        // If starting game, remove required attributes
                        imageUrlInput.removeAttribute('required');
                        imageNameInput.removeAttribute('required');
                    }
                });
            }
            
            // Also handle the upload button click to set required attributes
            const uploadBtn = document.getElementById('uploadImageBtn');
            if (uploadBtn) {
                uploadBtn.addEventListener('click', function() {
                    const imageUrlInput = document.getElementById('imageUrl');
                    const imageNameInput = document.getElementById('imageName');
                    
                    // Validate manually since we removed required attribute
                    if (!imageUrlInput.value.trim()) {
                        imageUrlInput.focus();
                        showUploadError('Image URL is required');
                        return;
                    }
                    
                    if (!imageNameInput.value.trim()) {
                        imageNameInput.focus();
                        showUploadError('Image name is required');
                        return;
                    }
                    
                    // If validation passes, proceed with existing upload logic
                });
            }
        });
    </script>
</body>
</html>
