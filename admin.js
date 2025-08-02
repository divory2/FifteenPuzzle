/**
 * Admin Panel Functionality for Fifteen Puzzle Game
 */

document.addEventListener('DOMContentLoaded', function() {
    // Admin panel event listeners
    const userManagementBtn = document.getElementById('userManagement');
    const systemSettingsBtn = document.getElementById('systemSettings');
    const viewAllGamesBtn = document.getElementById('viewAllGames');
    
    if (userManagementBtn) {
        userManagementBtn.addEventListener('click', showUserManagement);
    }
    
    if (systemSettingsBtn) {
        systemSettingsBtn.addEventListener('click', showSystemSettings);
    }
    
    if (viewAllGamesBtn) {
        viewAllGamesBtn.addEventListener('click', showAllGameStatistics);
    }
});

/**
 * Show user management interface
 */
function showUserManagement() {
    RBAC.executeWithPermission('manage_users', function() {
        fetch('admin_api.php?action=get_users')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayUsersTable(data.users);
                } else {
                    alert('Error loading users: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error loading users');
            });
    }, 'You need admin permissions to manage users');
}

/**
 * Display users in a table
 */
function displayUsersTable(users) {
    const adminPanel = document.getElementById('adminPanel');
    
    let tableHTML = `
        <h3>User Management</h3>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Role</th>
                    <th>Last Login</th>
                    <th>Last Logout</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
    `;
    
    users.forEach(user => {
        tableHTML += `
            <tr>
                <td>${user.id}</td>
                <td>${user.player}</td>
                <td>
                    <select class="role-select" data-user-id="${user.id}">
                        <option value="player" ${user.player_role === 'player' ? 'selected' : ''}>Player</option>
                        <option value="admin" ${user.player_role === 'admin' ? 'selected' : ''}>Admin</option>
                    </select>
                </td>
                <td>${user.login_date || 'Never'}</td>
                <td>${user.logout_date || 'N/A'}</td>
                <td>
                    <button class="update-role-btn" data-user-id="${user.id}">Update Role</button>
                    <button class="delete-user-btn" data-user-id="${user.id}">Delete</button>
                </td>
            </tr>
        `;
    });
    
    tableHTML += `
            </tbody>
        </table>
        <button id="closeUserManagement">Close</button>
    `;
    
    adminPanel.innerHTML = tableHTML;
    
    // Add event listeners for buttons
    document.querySelectorAll('.update-role-btn').forEach(btn => {
        btn.addEventListener('click', updateUserRole);
    });
    
    document.querySelectorAll('.delete-user-btn').forEach(btn => {
        btn.addEventListener('click', deleteUser);
    });
    
    document.getElementById('closeUserManagement').addEventListener('click', () => {
        adminPanel.innerHTML = `
            <h3>Admin Panel</h3>
            <div class="admin-controls">
                <button id="userManagement" class="admin-only">Manage Users</button>
                <button id="systemSettings" class="admin-only">System Settings</button>
                <button id="viewAllGames" class="admin-only">View All Games</button>
            </div>
        `;
        
        // Re-attach event listeners
        document.getElementById('userManagement').addEventListener('click', showUserManagement);
        document.getElementById('systemSettings').addEventListener('click', showSystemSettings);
        document.getElementById('viewAllGames').addEventListener('click', showAllGameStatistics);
    });
}

/**
 * Update user role
 */
function updateUserRole(event) {
    const userId = event.target.dataset.userId;
    const newRole = document.querySelector(`.role-select[data-user-id="${userId}"]`).value;
    
    if (confirm(`Are you sure you want to change this user's role to ${newRole}?`)) {
        const formData = new FormData();
        formData.append('user_id', userId);
        formData.append('new_role', newRole);
        
        fetch('admin_api.php?action=update_user_role', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('User role updated successfully');
                showUserManagement(); // Refresh the table
            } else {
                alert('Error updating user role: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error updating user role');
        });
    }
}

/**
 * Delete user
 */
function deleteUser(event) {
    const userId = event.target.dataset.userId;
    
    if (confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
        const formData = new FormData();
        formData.append('user_id', userId);
        
        fetch('admin_api.php?action=delete_user', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('User deleted successfully');
                showUserManagement(); // Refresh the table
            } else {
                alert('Error deleting user: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error deleting user');
        });
    }
}

/**
 * Show system settings
 */
function showSystemSettings() {
    RBAC.executeWithPermission('manage_system', function() {
        const adminPanel = document.getElementById('adminPanel');
        adminPanel.innerHTML = `
            <h3>System Settings</h3>
            <div class="settings-form">
                <label>
                    <input type="checkbox" id="allowGuestPlay"> Allow guest users to play
                </label>
                <br><br>
                <label>
                    <input type="checkbox" id="allowImageUpload"> Allow image uploads
                </label>
                <br><br>
                <button id="saveSettings">Save Settings</button>
                <button id="closeSettings">Close</button>
            </div>
        `;
        
        document.getElementById('saveSettings').addEventListener('click', () => {
            alert('Settings saved (this is a demo - not actually implemented)');
        });
        
        document.getElementById('closeSettings').addEventListener('click', () => {
            location.reload(); // Simple way to reset the admin panel
        });
    }, 'You need admin permissions to access system settings');
}

/**
 * Show all game statistics
 */
function showAllGameStatistics() {
    RBAC.executeWithPermission('view_all_games', function() {
        fetch('admin_api.php?action=get_game_sessions')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayGameStatistics(data.sessions);
                } else {
                    alert('Error loading game statistics: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error loading game statistics');
            });
    }, 'You need admin permissions to view all game statistics');
}

/**
 * Display game statistics
 */
function displayGameStatistics(sessions) {
    const statsPanel = document.getElementById('allGameStatistics');
    statsPanel.style.display = 'block';
    
    let statsHTML = `
        <h3>All Game Sessions</h3>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Player</th>
                    <th>Start Time</th>
                    <th>End Time</th>
                    <th>Moves</th>
                    <th>Completed</th>
                    <th>Background</th>
                </tr>
            </thead>
            <tbody>
    `;
    
    sessions.forEach(session => {
        statsHTML += `
            <tr>
                <td>${session.player}</td>
                <td>${session.session_start}</td>
                <td>${session.session_end || 'In Progress'}</td>
                <td>${session.moves_count}</td>
                <td>${session.completed ? 'Yes' : 'No'}</td>
                <td>${session.background_image || 'Default'}</td>
            </tr>
        `;
    });
    
    statsHTML += `
            </tbody>
        </table>
    `;
    
    document.getElementById('allStats').innerHTML = statsHTML;
}
