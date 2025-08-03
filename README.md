# 🧩 Fifteen Puzzle Game - Web Development Project

A comprehensive web-based implementation of the classic Fifteen Puzzle game with user authentication, role-based access control, competitive leaderboards, AI puzzle solver, and modern responsive design.

**Team**: Vignesh A M Raja & Devon Ivory

## 🚀 Quick Start

1. **Visit the Project Portal**: Open `index.php` in your browser
2. **Setup Database**: Click "Setup Database" or visit `setup.php`
3. **Login**: Use test accounts or register as a new user
4. **Play**: Select a background image and start solving puzzles!
5. **Compete**: Check the leaderboard to see how you rank against other players!
6. **AI Help**: Use browser console commands to get AI solving assistance

## 🎮 Live Demo

- **Project Portal**: `index.php` - Main landing page with all information
- **Game Access**: `login.php` - Modern login/register interface with enhanced styling
- **Game Play**: `game.php` - Enhanced puzzle game with session tracking
- **Leaderboard**: `leaderboard.php` - Comprehensive competitive rankings and statistics
- **Admin Panel**: Available after logging in as admin
- **Database Setup**: `setup.php` - One-time initialization

## 👥 Team

- **Vignesh A M Raja** - Developer (Backend & Database)
- **Devon Ivory** - Developer (UI/UX & Game Logic)

## 🔑 Test Accounts

| Role | Username | Password | Description |
|------|----------|----------|-------------|
| Admin | admin | admin123 | Full system access |
| Player | demo | demo123 | Standard player |
| Player | testuser1 | password123 | Test account |
| Player | player1 | mypassword | Test account |

## ✨ Features

### 🔐 Authentication & Security
- Secure user registration and login with modern UI
- Password hashing with PHP's `password_hash()`
- Session management with proper cleanup
- SQL injection protection with prepared statements
- Role-based access control (RBAC)
- Enhanced registration form with validation

### 🎯 Role-Based Access Control
- **Admin Role**: User management, system settings, view all statistics
- **Player Role**: Play games, upload images, view personal stats, compete on leaderboards
- **Guest Role**: Limited read-only access to leaderboards

### 🎮 Game Features
- Classic 15-puzzle sliding tile game with enhanced mechanics
- Custom background image support with drag-and-drop upload
- Predefined sample images with preview
- URL-based image uploads with validation
- Real-time move counting and session tracking
- Win detection with completion celebration
- Responsive game board with smooth animations
- **AI Puzzle Solver**: Browser console commands for automated solving
  - `solvePuzzle()` - Complete puzzle solution
  - `getNextMove()` - Get next optimal move
  - `getHint()` - Get solving hint
  - Uses A* pathfinding algorithm for optimal solutions

### 🏆 Comprehensive Leaderboard System
- **Fastest Times**: Top 10 players by completion speed
- **Fewest Moves**: Top 10 players by move efficiency  
- **Recent Completions**: Latest 20 puzzle completions
- **Personal Best**: Individual player statistics and achievements
- **Global Statistics**: Total games, completion rates, averages
- Real-time game session tracking and ranking
- User highlighting for personal achievements
- Responsive design with modern styling

### 👑 Admin Panel
- User management (view, edit roles, delete users)
- System statistics dashboard with real-time data
- Game session monitoring and analytics
- Database status and health monitoring
- Admin-only access controls

### 📊 Advanced Statistics & Tracking
- Complete game session logging with timestamps
- Move counting and efficiency tracking
- Completion time measurement (seconds precision)
- Player progress and achievement monitoring
- Background image usage analytics
- Leaderboard ranking calculations

## 🛠️ Technology Stack

### Frontend
- **HTML5** - Modern semantic markup with enhanced forms
- **CSS3** - Advanced responsive design with Grid, Flexbox, and animations
  - Glassmorphism effects with backdrop-filter
  - Gradient buttons with hover animations
  - Background image integration
  - Modern card-based layouts
- **JavaScript (Vanilla)** - Advanced game logic, RBAC, and AI solver
  - A* pathfinding algorithm for puzzle solving
  - Real-time session tracking
  - Enhanced event handling and animations
- **AJAX** - Asynchronous operations for admin panel and session management

### Backend
- **PHP 7+** - Advanced server-side logic with comprehensive session management
- **MySQL** - Optimized database with complex queries for leaderboards
- **Session Management** - Enhanced user state handling with game tracking
- **Game Session API** - Real-time game state persistence and statistics

### Security
- **Password Hashing** - bcrypt via PHP
- **Prepared Statements** - SQL injection prevention
- **RBAC** - Role-based access control
- **Input Validation** - Data sanitization

## 📁 File Structure

```
FifteenPuzzle/
├── index.php                 # Project portal (main landing page)
├── index-styles.css         # Styles for landing page
├── login.php                # Enhanced login interface with modern styling
├── register.php             # Registration page with proper form layout
├── game.php                 # Main game interface with session tracking
├── leaderboard.php          # Comprehensive leaderboard and statistics
├── game_session.php         # Game session API for tracking and persistence
├── logout.php               # Session cleanup
├── setup.php                # Database setup interface
├── db_config.php            # Database configuration
├── seed_database.php        # Database seeding script
├── loginForm.php            # Login processing
├── registerForm.php         # Registration processing with validation
├── admin_api.php            # Admin API endpoints
├── gameboard.js             # Enhanced game logic with AI solver
├── rbac.js                  # Role-based access control
├── admin.js                 # Admin panel functionality
├── validation.js            # Form validation
├── login.css                # Enhanced stylesheet with modern design
├── README.md                # This file
├── DATABASE_SETUP.md        # Database documentation
├── RBAC_SUMMARY.md          # Role-based access control documentation
├── images/                  # Background images directory
│   ├── background.jpg       # Main background image
│   ├── tlk1.jpg            # Sample puzzle background
│   └── tlk2.jpg            # Sample puzzle background
└── Group Project Proposal - Fifteen Puzzle- Vignesh - Devon.pdf
```

## 🗄️ Database Schema

### PLAYER Table
```sql
CREATE TABLE PLAYER (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    player VARCHAR(30) NOT NULL,
    player_password VARCHAR(255) NOT NULL,
    player_role VARCHAR(10) NOT NULL,
    login_date DATE DEFAULT NULL,
    logout_date DATE DEFAULT NULL
);
```

### GAME_SESSIONS Table  
```sql
CREATE TABLE GAME_SESSIONS (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    player_id INT(6) UNSIGNED,
    session_start TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    session_end TIMESTAMP NULL,
    moves_count INT DEFAULT 0,
    completed BOOLEAN DEFAULT FALSE,
    background_image VARCHAR(255),
    FOREIGN KEY (player_id) REFERENCES PLAYER(id) ON DELETE CASCADE
);
```

## 🔧 Installation & Setup

### Prerequisites
- Web server (Apache/Nginx)
- PHP 7.0 or higher
- MySQL 5.7 or higher
- Modern web browser

### Setup Steps

1. **Clone/Download** the project files to your web server directory

2. **Database Configuration**: Edit `db_config.php` with your database credentials:
   ```php
   define('DB_SERVER', 'localhost');
   define('DB_USERNAME', 'your_username');
   define('DB_PASSWORD', 'your_password');  
   define('DB_NAME', 'your_database');
   ```

3. **Initialize Database**: Visit `setup.php` or run:
   ```bash
   php setup.php
   ```

4. **Access Application**: Visit `index.php` in your browser

5. **Test Login**: Use admin credentials `admin/admin123`

## 🎯 How to Play

1. **Login** with your account or register as new user
2. **Select Background**: Choose from predefined images or enter custom URL
3. **Start Game**: Click "Start" to begin puzzle (automatic session tracking begins)
4. **Move Tiles**: Click tiles adjacent to empty space to slide them
5. **Get Help**: Use browser console AI commands for hints or solutions:
   - `solvePuzzle()` - Automatically solve the entire puzzle
   - `getNextMove()` - Get the next optimal move
   - `getHint()` - Get a strategic hint
6. **Win**: Arrange tiles in numerical order (1-15) with empty space at bottom-right
7. **Compete**: Check your ranking on the leaderboard system
8. **Track Progress**: View your personal best times and move counts

## 🔒 Security Features

- **Password Security**: All passwords hashed with bcrypt (cost factor 10)
- **SQL Injection Protection**: Prepared statements throughout codebase
- **Session Security**: Proper session management with regeneration and cleanup
- **Access Control**: Role-based permissions on all features and endpoints
- **Input Validation**: Comprehensive server-side validation of all inputs
- **XSS Protection**: HTML escaping and sanitization
- **CSRF Protection**: Form token validation where applicable

## 📱 Responsive Design

The application is fully responsive with modern CSS features:
- **Desktop computers** - Full feature set with hover effects
- **Tablets** - Adapted grid layouts and touch-friendly controls
- **Mobile phones** - Optimized single-column layouts
- **Various screen sizes** - Fluid grid systems and flexible components
- **Modern browsers** - CSS Grid, Flexbox, and advanced animations
- **Accessibility** - WCAG compliant color contrasts and navigation

## 🐛 Troubleshooting

### Database Connection Issues
- Check `db_config.php` credentials
- Ensure MySQL server is running
- Verify database exists

### Permission Errors
- Check file permissions (644 for files, 755 for directories)
- Ensure web server can read/write files

### Game Not Loading
- Check browser console for JavaScript errors
- Ensure all CSS/JS files are accessible
- Clear browser cache and reload
- Verify image uploads are within file size limits

### Leaderboard Issues
- Ensure game sessions are completing properly
- Check database GAME_SESSIONS table for data
- Verify user is logged in for personal statistics

### AI Solver Not Working
- Open browser console (F12)
- Ensure game is started before using solver commands
- Check for JavaScript errors in console

## 📄 Documentation

- **Project Proposal**: `Group Project Proposal - Fifteen Puzzle- Vignesh - Devon.pdf`
- **Database Setup**: `DATABASE_SETUP.md`
- **Project Specifications**: `Project2puzzle_V2.pdf`

## 🎓 Educational Objectives

This project demonstrates:
- **Full-stack web development** with modern PHP and JavaScript
- **Database design and management** with complex queries and relationships
- **User authentication systems** with secure password handling
- **Role-based access control** with proper authorization
- **Responsive web design** with modern CSS techniques
- **Game development** with JavaScript and canvas manipulation
- **Algorithm implementation** (A* pathfinding for puzzle solving)
- **Session management** and real-time data tracking
- **Competitive features** with leaderboard systems
- **Security best practices** throughout the application
- **Modern UI/UX design** with animations and visual effects

## 📞 Support

For questions or issues:
1. Check the documentation files
2. Review the project proposal PDF
3. Examine the database setup guide
4. Check browser console for errors

## 📊 Project Stats

- **Lines of Code**: ~3500+ (significantly expanded)
- **Files**: 25+ source files (including enhanced components)
- **Features**: 25+ major features (including AI solver and leaderboards)
- **Roles**: 2 user roles + guest access with comprehensive permissions
- **Tables**: 2 optimized database tables with complex relationships
- **Test Accounts**: 4 pre-configured users with various roles
- **AI Algorithms**: A* pathfinding implementation for puzzle solving
- **CSS Features**: Glassmorphism, gradients, animations, responsive grids
- **Security Measures**: 7+ implemented security practices
- **Browser Compatibility**: Modern browsers with fallback support

---

**Course**: Web Development  
**Repository**: FifteenPuzzle  
**Version**: v0.1.1  
**Year**: 2025