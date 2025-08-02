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
        
        // Validate user permission
        if (!isset($_SESSION['player'])) {
            throw new Exception('User not logged in');
        }
        
        $imageName = trim($_POST['image_name'] ?? '');
        $imageUrl = trim($_POST['image_url'] ?? '');
        $playerId = $_SESSION['playerId'] ?? null;
        
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
            background: #f8f9fa;
            padding: 15px 25px;
            border-radius: 8px;
            text-align: center;
        }
        
        .stat-item h4 {
            margin: 0 0 5px 0;
            color: #2c3e50;
        }
        
        .stat-value {
            font-size: 1.5em;
            font-weight: bold;
            color: #3498db;
        }
        
        #gameBoard {
            display: grid;
            grid-template-columns: repeat(4, 100px);
            grid-template-rows: repeat(4, 100px);
            gap: 2px;
            justify-content: center;
            margin: 20px auto;
            background: #2c3e50;
            padding: 10px;
            border-radius: 10px;
        }
        
        .tile {
            background-size: 400% 400%;
            border: 2px solid #34495e;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .tile:hover {
            border-color: #3498db;
            transform: scale(1.05);
        }
        
        .tile.empty {
            background: rgba(255,255,255,0.1);
            border-color: transparent;
        }
        
        .game-actions {
            margin-top: 20px;
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
                grid-template-columns: repeat(4, 80px);
                grid-template-rows: repeat(4, 80px);
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
                            <input type="submit" name="action" value="start" class="btn btn-primary" id="startGameBtn">
                        </div>
                    </div>

                    <!-- Image Upload Section -->
                    <div class="image-upload">
                        <h3 class="section-title">📤 Upload New Image</h3>
                        
                        <div id="uploadForm" class="upload-form">
                            <div class="input-group">
                                <label for="imageUrl">Image URL:</label>
                                <input type="url" id="imageUrl" name="image_url" placeholder="https://example.com/image.jpg" required>
                            </div>
                            
                            <div class="input-group">
                                <label for="imageName">Image Name:</label>
                                <input type="text" id="imageName" name="image_name" placeholder="Enter a name for this image" required maxlength="50">
                            </div>
                            
                            <img id="uploadPreview" class="image-preview" src="" alt="Upload preview" style="display: none;">
                            
                            <button type="button" id="uploadImageBtn" class="btn btn-secondary">Upload Image</button>
                            
                            <div id="uploadSuccess" class="upload-success"></div>
                            <div id="uploadError" class="upload-error"></div>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="game-board-container">
            <h3>🎯 Game Board</h3>
            
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
        // Initialize RBAC system with user data from PHP session
        document.addEventListener('DOMContentLoaded', function() {
            const userRole = '<?php echo $_SESSION['role'] ?? 'player'; ?>';
            const userName = '<?php echo htmlspecialchars($_SESSION['player']); ?>';
            
            // Initialize RBAC
            if (typeof RBAC !== 'undefined') {
                RBAC.init(userRole, userName);
            }
            
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
        });
    </script>
</body>
</html>
