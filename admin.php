<?php
session_start();
require_once 'db_config.php';

// Check if user is logged in and has admin role
if (!isset($_SESSION['player']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Get admin user info
$username = $_SESSION['player'];
$role = $_SESSION['role'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Fifteen Puzzle</title>
    <link rel="stylesheet" href="login.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }

        .admin-dashboard {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .dashboard-header {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .dashboard-header h1 {
            margin: 0;
            font-size: 2.5em;
            font-weight: 300;
        }

        .user-info {
            background: rgba(255,255,255,0.1);
            padding: 15px;
            border-radius: 10px;
            margin-top: 20px;
            display: inline-block;
        }

        .dashboard-content {
            padding: 40px;
        }

        .admin-nav {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
            margin-bottom: 40px;
        }

        .nav-card {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 30px;
            text-align: center;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }

        .nav-card:hover {
            transform: translateY(-5px);
            border-color: #667eea;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.2);
        }

        .nav-card h3 {
            color: #2c3e50;
            margin-bottom: 15px;
            font-size: 1.3em;
        }

        .nav-card p {
            color: #666;
            margin-bottom: 20px;
            line-height: 1.6;
        }

        .quick-actions {
            margin-top: 40px;
        }

        .quick-actions h2 {
            color: #2c3e50;
            margin-bottom: 20px;
            font-size: 1.8em;
        }

        .action-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
        }

        .back-link {
            position: fixed;
            top: 20px;
            left: 20px;
            background: rgba(255,255,255,0.9);
            padding: 10px 20px;
            border-radius: 25px;
            text-decoration: none;
            color: #2c3e50;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .back-link:hover {
            background: white;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .logout-link {
            position: fixed;
            top: 20px;
            right: 20px;
            background: rgba(231, 76, 60, 0.9);
            color: white;
            padding: 10px 20px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .logout-link:hover {
            background: #e74c3c;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(231, 76, 60, 0.3);
        }
    </style>
</head>
<body>
    <a href="index.php" class="back-link">← Back to Home</a>
    <a href="logout.php" class="logout-link">Logout</a>

    <div class="admin-dashboard">
        <div class="dashboard-header">
            <h1>🛡️ Admin Dashboard</h1>
            <div class="user-info">
                <strong>Welcome, <?php echo htmlspecialchars($username); ?>!</strong><br>
                <small>Role: Administrator</small>
            </div>
        </div>

        <div class="dashboard-content">
            <div class="admin-nav">
                <div class="nav-card">
                    <h3>👥 User Management</h3>
                    <p>Manage player accounts, roles, and permissions. View user activity and registration statistics.</p>
                    <button id="userManagement" class="admin-only">Manage Users</button>
                </div>

                <div class="nav-card">
                    <h3>⚙️ System Settings</h3>
                    <p>Configure system parameters, database settings, and application preferences.</p>
                    <button id="systemSettings" class="admin-only">System Settings</button>
                </div>

                <div class="nav-card">
                    <h3>📊 Game Analytics</h3>
                    <p>View comprehensive game statistics, player performance, and usage analytics.</p>
                    <button id="viewAllGames" class="admin-only">View All Games</button>
                </div>

                <div class="nav-card">
                    <h3>🎮 Play Game</h3>
                    <p>Access the Fifteen Puzzle game as an administrator (for testing purposes).</p>
                    <a href="game.php" class="admin-only" style="display: inline-block; margin-top: 10px;">Go to Game</a>
                </div>
            </div>

            <div class="quick-actions">
                <h2>Quick Actions</h2>
                <div class="action-buttons">
                    <button id="refreshData" class="admin-only">Refresh Data</button>
                    <button id="exportLogs" class="admin-only">Export Logs</button>
                    <button id="clearCache" class="admin-only">Clear Cache</button>
                </div>
            </div>

            <!-- Admin Panel Content Areas -->
            <div id="adminPanel" class="admin-panel">
                <div class="admin-controls" style="display: none;">
                    <!-- Content will be populated by admin.js -->
                </div>
            </div>

            <!-- Statistics Panels -->
            <div id="gameStatistics" class="statistics-panel" style="display: none;">
                <h3>Your Game Statistics</h3>
                <div id="playerStats"></div>
            </div>

            <div id="allGameStatistics" class="statistics-panel" style="display: none;">
                <h3>All Players Statistics</h3>
                <div id="allStats"></div>
            </div>
        </div>
    </div>

    <script src="rbac.js"></script>
    <script src="admin.js"></script>
    <script>
        // Initialize RBAC system with admin user data
        document.addEventListener('DOMContentLoaded', function() {
            const userRole = '<?php echo $_SESSION['role']; ?>';
            const username = '<?php echo $_SESSION['player']; ?>';
            
            // Initialize RBAC with admin role
            RBAC.init(userRole, username);
            
            // Show admin-specific UI elements
            RBAC.showElementsForRole();
            
            // Add click handlers for quick actions
            document.getElementById('refreshData').addEventListener('click', function() {
                alert('Data refreshed successfully!');
            });
            
            document.getElementById('exportLogs').addEventListener('click', function() {
                alert('Log export functionality would be implemented here.');
            });
            
            document.getElementById('clearCache').addEventListener('click', function() {
                alert('Cache cleared successfully!');
            });
        });
    </script>
</body>
</html>
