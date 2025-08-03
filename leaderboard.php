<?php
session_start();
require_once 'db_config.php';

// Check if user is logged in (optional - we can show leaderboard to guests too)
$isLoggedIn = isset($_SESSION['player']);
$currentUser = $_SESSION['player'] ?? null;

try {
    $conn = getDBConnection();
    
    // Get top 10 fastest completions (by time)
    $fastestQuery = "
        SELECT 
            p.player,
            gs.moves_count,
            TIMESTAMPDIFF(SECOND, gs.session_start, gs.session_end) as completion_time_seconds,
            gs.session_end as completed_at,
            gs.background_image
        FROM GAME_SESSIONS gs
        JOIN PLAYER p ON gs.player_id = p.id
        WHERE gs.completed = TRUE 
        AND gs.session_end IS NOT NULL
        ORDER BY completion_time_seconds ASC, moves_count ASC
        LIMIT 10
    ";
    
    $fastestResult = $conn->query($fastestQuery);
    $fastestTimes = $fastestResult ? $fastestResult->fetch_all(MYSQLI_ASSOC) : [];
    
    // Get top 10 by fewest moves
    $fewestMovesQuery = "
        SELECT 
            p.player,
            gs.moves_count,
            TIMESTAMPDIFF(SECOND, gs.session_start, gs.session_end) as completion_time_seconds,
            gs.session_end as completed_at,
            gs.background_image
        FROM GAME_SESSIONS gs
        JOIN PLAYER p ON gs.player_id = p.id
        WHERE gs.completed = TRUE 
        AND gs.session_end IS NOT NULL
        ORDER BY moves_count ASC, completion_time_seconds ASC
        LIMIT 10
    ";
    
    $fewestMovesResult = $conn->query($fewestMovesQuery);
    $fewestMoves = $fewestMovesResult ? $fewestMovesResult->fetch_all(MYSQLI_ASSOC) : [];
    
    // Get recent completions (last 20)
    $recentQuery = "
        SELECT 
            p.player,
            gs.moves_count,
            TIMESTAMPDIFF(SECOND, gs.session_start, gs.session_end) as completion_time_seconds,
            gs.session_end as completed_at,
            gs.background_image
        FROM GAME_SESSIONS gs
        JOIN PLAYER p ON gs.player_id = p.id
        WHERE gs.completed = TRUE 
        AND gs.session_end IS NOT NULL
        ORDER BY gs.session_end DESC
        LIMIT 20
    ";
    
    $recentResult = $conn->query($recentQuery);
    $recentCompletions = $recentResult ? $recentResult->fetch_all(MYSQLI_ASSOC) : [];
    
    // Get user's personal best (if logged in)
    $personalBest = null;
    if ($isLoggedIn && isset($_SESSION['playerId'])) {
        $personalQuery = "
            SELECT 
                gs.moves_count,
                TIMESTAMPDIFF(SECOND, gs.session_start, gs.session_end) as completion_time_seconds,
                gs.session_end as completed_at,
                gs.background_image
            FROM GAME_SESSIONS gs
            WHERE gs.player_id = ? 
            AND gs.completed = TRUE 
            AND gs.session_end IS NOT NULL
            ORDER BY moves_count ASC, completion_time_seconds ASC
            LIMIT 1
        ";
        
        $stmt = $conn->prepare($personalQuery);
        $stmt->bind_param("i", $_SESSION['playerId']);
        $stmt->execute();
        $personalResult = $stmt->get_result();
        $personalBest = $personalResult->fetch_assoc();
    }
    
    // Get general statistics
    $statsQuery = "
        SELECT 
            COUNT(*) as total_games,
            COUNT(CASE WHEN completed = TRUE THEN 1 END) as completed_games,
            AVG(CASE WHEN completed = TRUE THEN moves_count END) as avg_moves,
            AVG(CASE WHEN completed = TRUE THEN TIMESTAMPDIFF(SECOND, session_start, session_end) END) as avg_time,
            COUNT(DISTINCT player_id) as total_players
        FROM GAME_SESSIONS
    ";
    
    $statsResult = $conn->query($statsQuery);
    $stats = $statsResult ? $statsResult->fetch_assoc() : [];
    
} catch (Exception $e) {
    error_log("Leaderboard error: " . $e->getMessage());
    $fastestTimes = [];
    $fewestMoves = [];
    $recentCompletions = [];
    $personalBest = null;
    $stats = [];
}

function formatTime($seconds) {
    if ($seconds < 60) {
        return $seconds . 's';
    } elseif ($seconds < 3600) {
        $minutes = floor($seconds / 60);
        $remainingSeconds = $seconds % 60;
        return $minutes . 'm ' . $remainingSeconds . 's';
    } else {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $remainingSeconds = $seconds % 60;
        return $hours . 'h ' . $minutes . 'm ' . $remainingSeconds . 's';
    }
}

function formatDate($dateString) {
    return date('M j, Y g:i A', strtotime($dateString));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leaderboard - Fifteen Puzzle</title>
    <link rel="stylesheet" href="login.css">
    <style>
        .leaderboard-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .leaderboard-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
            border-radius: 15px;
            margin-bottom: 30px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            text-align: center;
            border-left: 4px solid #3498db;
        }
        
        .stat-number {
            font-size: 2.5em;
            font-weight: bold;
            color: #3498db;
            margin-bottom: 10px;
        }
        
        .stat-label {
            color: #2c3e50;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-size: 0.9em;
        }
        
        .leaderboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 30px;
            margin-bottom: 30px;
        }
        
        .leaderboard-section {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .section-header {
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
            padding: 20px;
            text-align: center;
            font-size: 1.3em;
            font-weight: bold;
        }
        
        .leaderboard-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .leaderboard-table th {
            background: #f8f9fa;
            padding: 15px 10px;
            text-align: left;
            font-weight: 600;
            color: #2c3e50;
            border-bottom: 2px solid #dee2e6;
        }
        
        .leaderboard-table td {
            padding: 12px 10px;
            border-bottom: 1px solid #dee2e6;
        }
        
        .leaderboard-table tbody tr:hover {
            background: #f8f9fa;
            transition: background 0.2s ease;
        }
        
        .rank {
            font-weight: bold;
            color: #3498db;
            text-align: center;
            width: 50px;
        }
        
        .rank.gold { color: #f1c40f; }
        .rank.silver { color: #95a5a6; }
        .rank.bronze { color: #e67e22; }
        
        .player-name {
            font-weight: 600;
            color: #2c3e50;
        }
        
        .current-user {
            background: #e8f4f8 !important;
            border-left: 4px solid #3498db;
        }
        
        .personal-best {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            padding: 25px;
            margin-bottom: 30px;
            border-left: 4px solid #27ae60;
        }
        
        .personal-best h3 {
            color: #27ae60;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .personal-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 20px;
        }
        
        .personal-stat {
            text-align: center;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 10px;
        }
        
        .personal-stat-value {
            font-size: 1.8em;
            font-weight: bold;
            color: #27ae60;
            margin-bottom: 5px;
        }
        
        .personal-stat-label {
            color: #2c3e50;
            font-size: 0.9em;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .navigation {
            text-align: center;
            margin-top: 30px;
        }
        
        .navigation a {
            margin: 0 15px;
            padding: 12px 25px;
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-block;
        }
        
        .navigation a:hover {
            background: linear-gradient(135deg, #2980b9, #1f639a);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(52, 152, 219, 0.3);
        }
        
        .empty-state {
            text-align: center;
            padding: 40px;
            color: #7f8c8d;
        }
        
        .empty-state h4 {
            margin-bottom: 10px;
            color: #95a5a6;
        }
        
        @media (max-width: 768px) {
            .leaderboard-grid {
                grid-template-columns: 1fr;
            }
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .personal-stats {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .leaderboard-table {
                font-size: 0.9em;
            }
            
            .leaderboard-table th,
            .leaderboard-table td {
                padding: 8px 5px;
            }
        }
    </style>
</head>
<body>
    <div class="leaderboard-container">
        <div class="leaderboard-header">
            <h1>🏆 Fifteen Puzzle Leaderboard</h1>
            <p>Compete with players around the world!</p>
            <?php if ($isLoggedIn): ?>
                <p>Welcome back, <strong><?php echo htmlspecialchars($currentUser); ?></strong>!</p>
            <?php else: ?>
                <p><a href="login.php" style="color: #f1c40f; text-decoration: underline;">Login</a> to track your scores</p>
            <?php endif; ?>
        </div>
        
        <!-- Global Statistics -->
        <?php if (!empty($stats)): ?>
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo number_format($stats['total_games'] ?? 0); ?></div>
                <div class="stat-label">Total Games</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo number_format($stats['completed_games'] ?? 0); ?></div>
                <div class="stat-label">Completed Games</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo number_format($stats['total_players'] ?? 0); ?></div>
                <div class="stat-label">Total Players</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo round($stats['avg_moves'] ?? 0); ?></div>
                <div class="stat-label">Avg Moves</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo formatTime(round($stats['avg_time'] ?? 0)); ?></div>
                <div class="stat-label">Avg Time</div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Personal Best (for logged in users) -->
        <?php if ($isLoggedIn && $personalBest): ?>
        <div class="personal-best">
            <h3>🎯 Your Personal Best</h3>
            <div class="personal-stats">
                <div class="personal-stat">
                    <div class="personal-stat-value"><?php echo $personalBest['moves_count']; ?></div>
                    <div class="personal-stat-label">Fewest Moves</div>
                </div>
                <div class="personal-stat">
                    <div class="personal-stat-value"><?php echo formatTime($personalBest['completion_time_seconds']); ?></div>
                    <div class="personal-stat-label">Best Time</div>
                </div>
                <div class="personal-stat">
                    <div class="personal-stat-value"><?php echo formatDate($personalBest['completed_at']); ?></div>
                    <div class="personal-stat-label">Achieved On</div>
                </div>
            </div>
        </div>
        <?php elseif ($isLoggedIn): ?>
        <div class="personal-best">
            <h3>🎯 Your Personal Best</h3>
            <div class="empty-state">
                <h4>No completed games yet!</h4>
                <p>Start playing to see your best scores here.</p>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Leaderboards -->
        <div class="leaderboard-grid">
            <!-- Fastest Times -->
            <div class="leaderboard-section">
                <div class="section-header">⚡ Fastest Times</div>
                <?php if (!empty($fastestTimes)): ?>
                <table class="leaderboard-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Player</th>
                            <th>Time</th>
                            <th>Moves</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($fastestTimes as $index => $record): ?>
                        <tr <?php echo ($isLoggedIn && $record['player'] === $currentUser) ? 'class="current-user"' : ''; ?>>
                            <td class="rank <?php 
                                if ($index === 0) echo 'gold';
                                elseif ($index === 1) echo 'silver';
                                elseif ($index === 2) echo 'bronze';
                            ?>"><?php echo $index + 1; ?></td>
                            <td class="player-name"><?php echo htmlspecialchars($record['player']); ?></td>
                            <td><?php echo formatTime($record['completion_time_seconds']); ?></td>
                            <td><?php echo $record['moves_count']; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <div class="empty-state">
                    <h4>No completed games yet!</h4>
                    <p>Be the first to complete a puzzle!</p>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Fewest Moves -->
            <div class="leaderboard-section">
                <div class="section-header">🎯 Fewest Moves</div>
                <?php if (!empty($fewestMoves)): ?>
                <table class="leaderboard-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Player</th>
                            <th>Moves</th>
                            <th>Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($fewestMoves as $index => $record): ?>
                        <tr <?php echo ($isLoggedIn && $record['player'] === $currentUser) ? 'class="current-user"' : ''; ?>>
                            <td class="rank <?php 
                                if ($index === 0) echo 'gold';
                                elseif ($index === 1) echo 'silver';
                                elseif ($index === 2) echo 'bronze';
                            ?>"><?php echo $index + 1; ?></td>
                            <td class="player-name"><?php echo htmlspecialchars($record['player']); ?></td>
                            <td><?php echo $record['moves_count']; ?></td>
                            <td><?php echo formatTime($record['completion_time_seconds']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <div class="empty-state">
                    <h4>No completed games yet!</h4>
                    <p>Be the first to complete a puzzle!</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Recent Completions -->
        <?php if (!empty($recentCompletions)): ?>
        <div class="leaderboard-section">
            <div class="section-header">📅 Recent Completions</div>
            <table class="leaderboard-table">
                <thead>
                    <tr>
                        <th>Player</th>
                        <th>Moves</th>
                        <th>Time</th>
                        <th>Completed</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentCompletions as $record): ?>
                    <tr <?php echo ($isLoggedIn && $record['player'] === $currentUser) ? 'class="current-user"' : ''; ?>>
                        <td class="player-name"><?php echo htmlspecialchars($record['player']); ?></td>
                        <td><?php echo $record['moves_count']; ?></td>
                        <td><?php echo formatTime($record['completion_time_seconds']); ?></td>
                        <td><?php echo formatDate($record['completed_at']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
        
        <div class="navigation">
            <?php if ($isLoggedIn): ?>
                <a href="game.php">🎮 Play Game</a>
                <a href="index.php">🏠 Home</a>
                <?php if ($_SESSION['role'] === 'admin'): ?>
                    <a href="admin.php">👑 Admin Panel</a>
                <?php endif; ?>
                <a href="logout.php">🚪 Logout</a>
            <?php else: ?>
                <a href="login.php">🔑 Login</a>
                <a href="registerForm.php">📝 Register</a>
                <a href="index.php">🏠 Home</a>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
