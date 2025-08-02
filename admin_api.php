<?php
session_start();
require_once 'db_config.php';

// Check if user is admin
if (!isset($_SESSION['player']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php?error=access_denied");
    exit();
}

$action = $_GET['action'] ?? '';

try {
    $conn = getDBConnection();
    
    switch ($action) {
        case 'get_users':
            $stmt = $conn->prepare("SELECT id, player, player_role, login_date, logout_date FROM PLAYER ORDER BY id DESC");
            $stmt->execute();
            $result = $stmt->get_result();
            
            $users = [];
            while ($row = $result->fetch_assoc()) {
                $users[] = $row;
            }
            
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'users' => $users]);
            break;
            
        case 'get_game_sessions':
            $stmt = $conn->prepare("
                SELECT gs.*, p.player 
                FROM GAME_SESSIONS gs 
                JOIN PLAYER p ON gs.player_id = p.id 
                ORDER BY gs.session_start DESC 
                LIMIT 50
            ");
            $stmt->execute();
            $result = $stmt->get_result();
            
            $sessions = [];
            while ($row = $result->fetch_assoc()) {
                $sessions[] = $row;
            }
            
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'sessions' => $sessions]);
            break;
            
        case 'update_user_role':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $userId = $_POST['user_id'];
                $newRole = $_POST['new_role'];
                
                if (!in_array($newRole, ['admin', 'player'])) {
                    throw new Exception('Invalid role');
                }
                
                $stmt = $conn->prepare("UPDATE PLAYER SET player_role = ? WHERE id = ?");
                $stmt->bind_param("si", $newRole, $userId);
                $stmt->execute();
                
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'User role updated']);
            }
            break;
            
        case 'delete_user':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $userId = $_POST['user_id'];
                
                // Don't allow deleting the current admin
                if ($userId == $_SESSION['playerId']) {
                    throw new Exception('Cannot delete your own account');
                }
                
                $stmt = $conn->prepare("DELETE FROM PLAYER WHERE id = ?");
                $stmt->bind_param("i", $userId);
                $stmt->execute();
                
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'User deleted']);
            }
            break;
            
        default:
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
    
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
