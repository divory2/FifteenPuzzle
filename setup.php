<?php
/**
 * Setup script for Fifteen Puzzle game
 * Run this file once to set up the database and initial data
 */

echo "<h2>Fifteen Puzzle Game Setup</h2>\n";
echo "<pre>\n";

// Include the seeding script
include_once 'seed_database.php';

echo "</pre>\n";
echo "<p><strong>Setup completed!</strong></p>\n";
echo "<p><a href='login.php'>Go to Login Page</a></p>\n";
?>
