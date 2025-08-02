# 🧩 Fifteen Puzzle Game - Web Development Project

A comprehensive web-based implementation of the classic Fifteen Puzzle game with user authentication, role-based access control, and administrative features.

**Team**: Vignesh A M Raja & Devon Ivory

## 🚀 Quick Start

1. **Visit the Project Portal**: Open `index.php` in your browser
2. **Setup Database**: Click "Setup Database" or visit `setup.php`
3. **Login**: Use test accounts or register as a new user
4. **Play**: Select a background image and start solving puzzles!

## 🎮 Live Demo

- **Project Portal**: `index.php` - Main landing page with all information
- **Game Access**: `login.php` - Login/Register interface  
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
- Secure user registration and login
- Password hashing with PHP's `password_hash()`
- Session management
- SQL injection protection with prepared statements
- Role-based access control (RBAC)

### 🎯 Role-Based Access Control
- **Admin Role**: User management, system settings, view all statistics
- **Player Role**: Play games, upload images, view personal stats
- **Guest Role**: Limited read-only access

### 🎮 Game Features
- Classic 15-puzzle sliding tile game
- Custom background image support
- Predefined sample images
- URL-based image uploads
- Move counting and win detection
- Responsive game board

### 👑 Admin Panel
- User management (view, edit roles, delete)
- System statistics dashboard
- Game session monitoring
- Real-time database status

### 📊 Statistics & Tracking
- Game session logging
- Move counting
- Completion tracking
- Player progress monitoring

## 🛠️ Technology Stack

### Frontend
- **HTML5** - Modern semantic markup
- **CSS3** - Responsive design with Grid and Flexbox
- **JavaScript (Vanilla)** - Game logic and RBAC
- **AJAX** - Asynchronous admin operations

### Backend
- **PHP 7+** - Server-side logic
- **MySQL** - Database management
- **Session Management** - User state handling

### Security
- **Password Hashing** - bcrypt via PHP
- **Prepared Statements** - SQL injection prevention
- **RBAC** - Role-based access control
- **Input Validation** - Data sanitization

## 📁 File Structure

```
FifteenPuzzle/
├── index.php                 # Project portal (main landing page)
├── index.html               # Static version of landing page
├── index-styles.css         # Styles for landing page
├── login.php                # Login/register interface
├── game.php                 # Main game interface
├── logout.php               # Session cleanup
├── setup.php                # Database setup interface
├── db_config.php            # Database configuration
├── seed_database.php        # Database seeding script
├── loginForm.php            # Login processing
├── registerForm.php         # Registration processing
├── admin_api.php            # Admin API endpoints
├── gameboard.js             # Game logic
├── rbac.js                  # Role-based access control
├── admin.js                 # Admin panel functionality
├── validation.js            # Form validation
├── login.css                # Main stylesheet
├── README.md                # This file
├── DATABASE_SETUP.md        # Database documentation
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
3. **Start Game**: Click "Start" to begin puzzle
4. **Move Tiles**: Click tiles adjacent to empty space to slide them
5. **Win**: Arrange tiles in numerical order (1-15) with empty space at bottom-right

## 🔒 Security Features

- **Password Security**: All passwords hashed with bcrypt
- **SQL Injection Protection**: Prepared statements throughout
- **Session Security**: Proper session management and cleanup
- **Access Control**: Role-based permissions on all features
- **Input Validation**: Server-side validation of all inputs

## 📱 Responsive Design

The application is fully responsive and works on:
- Desktop computers
- Tablets  
- Mobile phones
- Various screen sizes and orientations

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
- Clear browser cache

## 📄 Documentation

- **Project Proposal**: `Group Project Proposal - Fifteen Puzzle- Vignesh - Devon.pdf`
- **Database Setup**: `DATABASE_SETUP.md`
- **Project Specifications**: `Project2puzzle_V2.pdf`

## 🎓 Educational Objectives

This project demonstrates:
- Full-stack web development
- Database design and management
- User authentication systems
- Role-based access control
- Responsive web design
- JavaScript game development
- PHP backend development
- Security best practices

## 📞 Support

For questions or issues:
1. Check the documentation files
2. Review the project proposal PDF
3. Examine the database setup guide
4. Check browser console for errors

## 📊 Project Stats

- **Lines of Code**: ~2000+
- **Files**: 20+ source files
- **Features**: 15+ major features
- **Roles**: 2 user roles + guest access
- **Tables**: 2 database tables
- **Test Accounts**: 4 pre-configured users

---

**Course**: Web Development  
**Repository**: FifteenPuzzle  
**Version**: v0.1.1  
**Year**: 2025