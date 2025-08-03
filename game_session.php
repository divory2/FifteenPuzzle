<?php
session_start();
require_once 'db_config.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['player']) || !isset($_SESSION['playerId'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

try {
    $conn = getDBConnection();
    
    $action = $_POST['action'] ?? '';
    $playerId = $_SESSION['playerId'];
    
    switch ($action) {
        case 'start_game':
            // Start a new game session
            $backgroundImage = $_POST['background_image'] ?? '';
            
            $stmt = $conn->prepare("INSERT INTO GAME_SESSIONS (player_id, session_start, background_image) VALUES (?, NOW(), ?)");
            $stmt->bind_param("is", $playerId, $backgroundImage);
            
            if ($stmt->execute()) {
                $sessionId = $conn->insert_id;
                echo json_encode([
                    'success' => true,
                    'session_id' => $sessionId,
                    'message' => 'Game session started'
                ]);
            } else {
                throw new Exception('Failed to start game session');
            }
            break;
            
        case 'update_moves':
            // Update move count for current session
            $sessionId = $_POST['session_id'] ?? 0;
            $moveCount = $_POST['move_count'] ?? 0;
            
            $stmt = $conn->prepare("UPDATE GAME_SESSIONS SET moves_count = ? WHERE id = ? AND player_id = ?");
            $stmt->bind_param("iii", $moveCount, $sessionId, $playerId);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Moves updated']);
            } else {
                throw new Exception('Failed to update moves');
            }
            break;
            
        case 'complete_game':
            // Mark game as completed
            $sessionId = $_POST['session_id'] ?? 0;
            $moveCount = $_POST['move_count'] ?? 0;
            
            $stmt = $conn->prepare("UPDATE GAME_SESSIONS SET moves_count = ?, completed = TRUE, session_end = NOW() WHERE id = ? AND player_id = ?");
            $stmt->bind_param("iii", $moveCount, $sessionId, $playerId);
            
            if ($stmt->execute()) {
                // Get the completion time for response
                $stmt2 = $conn->prepare("SELECT TIMESTAMPDIFF(SECOND, session_start, session_end) as completion_time FROM GAME_SESSIONS WHERE id = ?");
                $stmt2->bind_param("i", $sessionId);
                $stmt2->execute();
                $result = $stmt2->get_result();
                $timeData = $result->fetch_assoc();
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Game completed successfully!',
                    'completion_time' => $timeData['completion_time'] ?? 0,
                    'moves' => $moveCount
                ]);
            } else {
                throw new Exception('Failed to complete game');
            }
            break;
            
        case 'abandon_game':
            // Mark incomplete game as abandoned (optional)
            $sessionId = $_POST['session_id'] ?? 0;
            
            $stmt = $conn->prepare("UPDATE GAME_SESSIONS SET session_end = NOW() WHERE id = ? AND player_id = ? AND completed = FALSE");
            $stmt->bind_param("ii", $sessionId, $playerId);
            
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Game session ended']);
            } else {
                throw new Exception('Failed to end game session');
            }
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            break;
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
