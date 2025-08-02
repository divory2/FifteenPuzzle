<?php
// Check if user is already logged in and redirect to game
session_start();
if (isset($_SESSION['player'])) {
    $redirectMessage = "Welcome back, " . htmlspecialchars($_SESSION['player']) . "!";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fifteen Puzzle Game - Project Portal</title>
    <link rel="stylesheet" href="login.css">
    <link rel="stylesheet" href="index-styles.css">
</head>
<body>
    <?php if (isset($redirectMessage)): ?>
    <div class="welcome-banner">
        <p><?php echo $redirectMessage; ?> <a href="game.php">Continue to Game</a> | <a href="logout.php">Logout</a></p>
    </div>
    <?php endif; ?>

    <div class="landing-container">
        <!-- Header Section -->
        <div class="header-section">
            <h1>🧩 Fifteen Puzzle Game</h1>
            <p class="subtitle">Web Development Project - Interactive Sliding Puzzle with User Management</p>
            <p><strong>Team:</strong> Vignesh & Devon | <strong>Course:</strong> Web Development</p>
        </div>

        <!-- Quick Navigation Cards -->
        <div class="nav-cards">
            <div class="nav-card">
                <h3>🎮 Play Game</h3>
                <p>Start playing the Fifteen Puzzle game. Login with your account or register as a new player.</p>
                <?php if (isset($_SESSION['player'])): ?>
                    <a href="game.php" class="btn btn-primary">Continue Game</a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-primary">Start Playing</a>
                <?php endif; ?>
            </div>
            
            <div class="nav-card">
                <h3>⚙️ Setup Database</h3>
                <p>First-time setup? Initialize the database with sample users and game tables.</p>
                <a href="setup.php" class="btn btn-secondary">Run Setup</a>
            </div>
            
            <div class="nav-card">
                <h3>👑 Admin Access</h3>
                <p>Admin panel for user management and system settings. Requires admin credentials.</p>
                <?php if (isset($_SESSION['player']) && $_SESSION['role'] === 'admin'): ?>
                    <a href="admin.php" class="btn btn-admin">Go to Admin Panel</a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-admin">Admin Login</a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Project Information -->
        <div class="project-info">
            <h2>📋 Project Overview</h2>
            <p>This is a comprehensive web-based implementation of the classic Fifteen Puzzle game, developed as a web development course project. The application features user authentication, role-based access control, and an interactive gaming experience.</p>
            
            <div class="features-grid">
                <div class="feature-item">
                    <h4>🔐 User Authentication</h4>
                    <p>Secure login/register system with password hashing and session management.</p>
                </div>
                
                <div class="feature-item">
                    <h4>🎯 Role-Based Access</h4>
                    <p>Different permission levels for admins and players with appropriate UI controls.</p>
                </div>
                
                <div class="feature-item">
                    <h4>🎨 Custom Images</h4>
                    <p>Players can select from predefined images or upload custom puzzle backgrounds.</p>
                </div>
                
                <div class="feature-item">
                    <h4>📊 Game Statistics</h4>
                    <p>Track game sessions, moves, completion times, and player progress.</p>
                </div>
                
                <div class="feature-item">
                    <h4>👥 Admin Panel</h4>
                    <p>Comprehensive user management, system settings, and game monitoring tools.</p>
                </div>
                
                <div class="feature-item">
                    <h4>📱 Responsive Design</h4>
                    <p>Mobile-friendly interface that works across different screen sizes.</p>
                </div>
            </div>
        </div>

        <!-- Document Viewer -->
        <div class="document-viewer">
            <h3>📄 Project Documentation</h3>
            <p>View the complete project proposal and specifications document:</p>
            
            <div class="pdf-container">
                <a href="Group Project Proposal - Fifteen Puzzle- Vignesh - Devon.pdf" class="pdf-link" target="_blank">
                    📄 View Project Proposal (PDF)
                </a>
                <br>
                <small>Opens in new tab - Contains project requirements, specifications, and implementation details</small>
            </div>
            
            <div style="margin-top: 20px;">
                <h4>📚 Additional Documentation:</h4>
                <div style="display: flex; gap: 15px; flex-wrap: wrap; justify-content: center; margin-top: 15px;">
                    <a href="README.md" class="btn" style="background: #17a2b8;">README Guide</a>
                    <a href="DATABASE_SETUP.md" class="btn" style="background: #ffc107; color: #000;">Database Setup</a>
                    <a href="Project2puzzle_V2.pdf" class="btn" style="background: #6f42c1;">Project Specs</a>
                </div>
            </div>
        </div>

        <!-- Team Information -->
        <div class="team-info">
            <h3>👥 Development Team</h3>
            <div class="team-members">
                <div class="member">
                    <h4>Vignesh</h4>
                    <p>Developer</p>
                    <p>Backend & Database</p>
                </div>
                <div class="member">
                    <h4>Devon</h4>
                    <p>Developer</p>
                    <p>UI/UX & Game Logic</p>
                </div>
            </div>
        </div>

        <!-- System Status -->
        <?php
        // Check database connection and display system status
        try {
            require_once 'db_config.php';
            $conn = getDBConnection();
            
            // Count users
            $userCount = $conn->query("SELECT COUNT(*) as count FROM PLAYER")->fetch_assoc()['count'] ?? 0;
            $adminCount = $conn->query("SELECT COUNT(*) as count FROM PLAYER WHERE player_role = 'admin'")->fetch_assoc()['count'] ?? 0;
            $gameCount = $conn->query("SELECT COUNT(*) as count FROM GAME_SESSIONS")->fetch_assoc()['count'] ?? 0;
            
            $dbStatus = "Connected";
            $dbStatusClass = "status-good";
        } catch (Exception $e) {
            $userCount = "N/A";
            $adminCount = "N/A";
            $gameCount = "N/A";
            $dbStatus = "Not Connected";
            $dbStatusClass = "status-error";
        }
        ?>
        
        <div class="project-info">
            <h2>📊 System Status</h2>
            <div class="quick-stats">
                <div class="stat-item">
                    <div class="stat-number"><?php echo $userCount; ?></div>
                    <div class="stat-label">Registered Users</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number"><?php echo $adminCount; ?></div>
                    <div class="stat-label">Admin Users</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number"><?php echo $gameCount; ?></div>
                    <div class="stat-label">Game Sessions</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number <?php echo $dbStatusClass; ?>"><?php echo $dbStatus; ?></div>
                    <div class="stat-label">Database</div>
                </div>
            </div>
        </div>

        <!-- Quick Start Guide -->
        <div class="project-info">
            <h2>🚀 Quick Start Guide</h2>
            <ol style="line-height: 1.8; color: #333;">
                <li><strong>First Time:</strong> Click "Setup Database" to initialize the system</li>
                <li><strong>Admin Access:</strong> Login with <code>admin / admin123</code></li>
                <li><strong>Player Access:</strong> Login with <code>demo / demo123</code> or register new account</li>
                <li><strong>Play Game:</strong> Select background image and start solving puzzles</li>
                <li><strong>Admin Features:</strong> Manage users, view statistics, system settings</li>
            </ol>
            
            <h3>🔑 Test Accounts</h3>
            <div style="background: white; padding: 15px; border-radius: 5px; margin: 15px 0;">
                <strong>Admin:</strong> admin / admin123<br>
                <strong>Players:</strong> demo / demo123, testuser1 / password123, player1 / mypassword
            </div>
        </div>

        <!-- Technology Stack -->
        <div class="project-info">
            <h2>💻 Technology Stack</h2>
            <div class="features-grid">
                <div class="feature-item">
                    <h4>Frontend</h4>
                    <p>HTML5, CSS3, JavaScript (Vanilla), Responsive Design</p>
                </div>
                
                <div class="feature-item">
                    <h4>Backend</h4>
                    <p>PHP 7+, MySQL Database, Session Management</p>
                </div>
                
                <div class="feature-item">
                    <h4>Security</h4>
                    <p>Password Hashing, Prepared Statements, RBAC</p>
                </div>
                
                <div class="feature-item">
                    <h4>Features</h4>
                    <p>AJAX, File Upload, Admin Panel, Game Statistics</p>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>&copy; 2025 Fifteen Puzzle Game Project | Developed by Vignesh & Devon</p>
            <p>Web Development Course | Repository: FifteenPuzzle (<?php echo isset($_GET['branch']) ? htmlspecialchars($_GET['branch']) : 'v0.1.1'; ?>)</p>
        </div>
    </div>

    <style>
        .welcome-banner {
            background: #d4edda;
            color: #155724;
            padding: 10px;
            text-align: center;
            border-bottom: 1px solid #c3e6cb;
        }
        
        .welcome-banner a {
            color: #0c5460;
            text-decoration: underline;
        }
        
        .status-good {
            color: #28a745 !important;
        }
        
        .status-error {
            color: #dc3545 !important;
        }
    </style>
</body>
</html>
