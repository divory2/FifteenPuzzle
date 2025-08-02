/**
 * Simplified Role-Based Access Control (RBAC) for Fifteen Puzzle Game
 * - Everyone can play the game and upload images
 * - Only admins can access admin panel
 */

// Current user info (will be set from PHP session)
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
    console.log(`✅ RBAC initialized: User "${username}" with role "${role}"`);
    
    // Apply role-based UI changes
    applyRoleBasedUI();
}

/**
 * Check if current user can access admin panel
 * @returns {boolean} - True if user is admin
 */
function canAccessAdminPanel() {
    return currentUserRole === 'admin';
}

/**
 * Get current user information
 * @returns {object} - User info object
 */
function getCurrentUser() {
    return {
        role: currentUserRole,
        username: currentUserName
    };
}

/**
 * Apply role-based UI changes
 */
function applyRoleBasedUI() {
    console.log(`🎨 Applying UI changes for role: ${currentUserRole}`);
    
    // Update role indicator if it exists
    const roleIndicator = document.getElementById('roleIndicator');
    if (roleIndicator) {
        roleIndicator.textContent = `Role: ${currentUserRole.charAt(0).toUpperCase() + currentUserRole.slice(1)}`;
        roleIndicator.className = `role-indicator role-${currentUserRole}`;
    }
    
    // Show/hide admin panel links
    const adminLinks = document.querySelectorAll('a[href="admin.php"]');
    adminLinks.forEach(link => {
        if (canAccessAdminPanel()) {
            link.style.display = 'inline';
        } else {
            link.style.display = 'none';
        }
    });
    
    console.log(`✅ UI updated for ${currentUserRole}`);
}

// Export functions for use in other scripts
window.RBAC = {
    init: initializeRBAC,
    initializeRBAC,
    canAccessAdminPanel,
    applyRoleBasedUI,
    getCurrentUser
};
