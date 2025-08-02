# Fifteen Puzzle Game - Database Setup

## Files Added

### Database Configuration
- `db_config.php` - Centralized database connection configuration
- `seed_database.php` - Database seeding script with sample data
- `setup.php` - One-time setup script for initial database creation

## Setup Instructions

### 1. First Time Setup
Run the setup script once to initialize the database:

```bash
php setup.php
```

Or visit in browser: `http://localhost/your-project/setup.php`

### 2. What Gets Created
- **PLAYER table** - Stores user authentication data
- **GAME_SESSIONS table** - Tracks game sessions and statistics
- **Sample users** for testing:
  - Username: `testuser1`, Password: `password123`
  - Username: `player1`, Password: `mypassword`
  - Username: `admin`, Password: `adminpass`

### 3. Database Schema

#### PLAYER Table
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

#### GAME_SESSIONS Table
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

## Updated Files

### Security Improvements
- All PHP files now use prepared statements
- Centralized database configuration
- Proper error handling with try-catch blocks
- Session management improvements

### Files Modified
- `loginForm.php` - Uses new db_config.php
- `registerForm.php` - Uses new db_config.php  
- `logout.php` - Uses new db_config.php
- `game.php` - Already has user info display

## Usage

1. Run `setup.php` once
2. Access `login.php` to start using the application
3. Register new users or use sample accounts for testing

## Configuration

Edit `db_config.php` to change database connection settings:

```php
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'your_username');
define('DB_PASSWORD', 'your_password');
define('DB_NAME', 'your_database');
```
