/**
 * Role-Based Access Control (RBAC) for Fifteen Puzzle Game
 */

// Define roles and their permissions
const roles = {
    admin: [
        'manage_users',
        'view_all_games',
        'delete_games',
        'manage_system',
        'play_game',
        'view_statistics',
        'moderate_content'
    ],
    player: [
        'play_game',
        'view_own_statistics',
        'upload_images',
        'save_game'
    ],
    guest: [
        'view_public_content'
    ]
};

// Current user role (will be set from PHP session)
let currentUserRole = 'guest';
let currentUserName = '';

/**
 * Initialize RBAC system with user data from server
 * @param {string} role - User's role from server
 * @param {string} username - User's name from server
 */
function initializeRBAC(role, username) {
    currentUserRole = role || 'guest';
    currentUserName = username || '';
    console.log(`RBAC initialized: User ${username} with role ${role}`);
    
    // Apply role-based UI changes
    applyRoleBasedUI();
}

/**
 * Check if current user has a specific permission
 * @param {string} permission - Permission to check
 * @returns {boolean} - True if user has permission
 */
function hasPermission(permission) {
    if (!currentUserRole || !roles[currentUserRole]) {
        console.warn(`Role ${currentUserRole} not defined or user not authenticated`);
        return false;
    }
    
    const hasAccess = roles[currentUserRole].includes(permission);
    console.log(`Permission check: ${permission} for role ${currentUserRole} = ${hasAccess}`);
    return hasAccess;
}

/**
 * Show or hide element based on permission
 * @param {string} elementId - Element ID to show/hide
 * @param {string} permission - Required permission
 */
function toggleElementByPermission(elementId, permission) {
    const element = document.getElementById(elementId);
    if (!element) {
        console.warn(`Element with ID ${elementId} not found`);
        return;
    }
    
    if (hasPermission(permission)) {
        element.style.display = 'block';
        element.classList.remove('rbac-hidden');
    } else {
        element.style.display = 'none';
        element.classList.add('rbac-hidden');
    }
}

/**
 * Enable or disable element based on permission
 * @param {string} elementId - Element ID to enable/disable
 * @param {string} permission - Required permission
 */
function toggleElementAccessByPermission(elementId, permission) {
    const element = document.getElementById(elementId);
    if (!element) {
        console.warn(`Element with ID ${elementId} not found`);
        return;
    }
    
    if (hasPermission(permission)) {
        element.disabled = false;
        element.classList.remove('rbac-disabled');
    } else {
        element.disabled = true;
        element.classList.add('rbac-disabled');
        element.title = 'You do not have permission to access this feature';
    }
}

/**
 * Apply role-based UI changes
 */
function applyRoleBasedUI() {
    // Game controls - only players and admins can play
    toggleElementByPermission('gameControls', 'play_game');
    toggleElementByPermission('backgroundSelector', 'play_game');
    toggleElementByPermission('uploadContainer', 'upload_images');
    
    // Admin-only features
    toggleElementByPermission('adminPanel', 'manage_users');
    toggleElementByPermission('systemSettings', 'manage_system');
    toggleElementByPermission('userManagement', 'manage_users');
    
    // Statistics access
    toggleElementByPermission('gameStatistics', 'view_own_statistics');
    toggleElementByPermission('allGameStatistics', 'view_all_games');
    
    // Update role indicator
    updateRoleIndicator();
    
    // Apply permission-based button states
    applyButtonPermissions();
}

/**
 * Update role indicator in UI
 */
function updateRoleIndicator() {
    const roleIndicator = document.getElementById('roleIndicator');
    if (roleIndicator) {
        roleIndicator.textContent = `Role: ${currentUserRole.charAt(0).toUpperCase() + currentUserRole.slice(1)}`;
        roleIndicator.className = `role-indicator role-${currentUserRole}`;
    }
}

/**
 * Apply permissions to buttons and interactive elements
 */
function applyButtonPermissions() {
    // Upload button
    toggleElementAccessByPermission('uploadButton', 'upload_images');
    
    // Start game button
    toggleElementAccessByPermission('startGameBtn', 'play_game');
    
    // Admin buttons
    const adminButtons = document.querySelectorAll('.admin-only');
    adminButtons.forEach(button => {
        if (hasPermission('manage_users')) {
            button.style.display = 'inline-block';
            button.disabled = false;
        } else {
            button.style.display = 'none';
            button.disabled = true;
        }
    });
    
    // Player buttons
    const playerButtons = document.querySelectorAll('.player-only');
    playerButtons.forEach(button => {
        if (hasPermission('play_game')) {
            button.style.display = 'inline-block';
            button.disabled = false;
        } else {
            button.style.display = 'none';
            button.disabled = true;
        }
    });
}

/**
 * Check permission before executing action
 * @param {string} permission - Required permission
 * @param {Function} callback - Function to execute if permission granted
 * @param {string} errorMessage - Error message if permission denied
 */
function executeWithPermission(permission, callback, errorMessage = 'Access denied') {
    if (hasPermission(permission)) {
        callback();
    } else {
        alert(errorMessage + `. Required role: ${getRequiredRoleForPermission(permission)}`);
        console.warn(`Permission denied: ${permission} for role ${currentUserRole}`);
    }
}

/**
 * Get the minimum role required for a permission
 * @param {string} permission - Permission to check
 * @returns {string} - Required role
 */
function getRequiredRoleForPermission(permission) {
    for (const [role, permissions] of Object.entries(roles)) {
        if (permissions.includes(permission)) {
            return role;
        }
    }
    return 'unknown';
}

/**
 * Redirect to login if user doesn't have required permission
 * @param {string} permission - Required permission
 * @param {string} redirectUrl - URL to redirect to (default: login.php)
 */
function requirePermissionOrRedirect(permission, redirectUrl = 'login.php') {
    if (!hasPermission(permission)) {
        alert('You need to log in with appropriate permissions to access this feature.');
        window.location.href = redirectUrl;
        return false;
    }
    return true;
}

/**
 * Get current user information
 * @returns {Object} - User information
 */
function getCurrentUser() {
    return {
        role: currentUserRole,
        name: currentUserName,
        permissions: roles[currentUserRole] || []
    };
}

// Export functions for use in other scripts
window.RBAC = {
    initializeRBAC,
    hasPermission,
    toggleElementByPermission,
    toggleElementAccessByPermission,
    applyRoleBasedUI,
    executeWithPermission,
    requirePermissionOrRedirect,
    getCurrentUser,
    roles
};
