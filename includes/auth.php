<?php
/**
 * Authentication Helper Module
 * Provides centralized authentication checks and user session management
 * Used across all protected pages to ensure user is logged in
 */

// Function to check if user is logged in and redirect to login if not
function require_login() {
    // Check if session contains 'id' key which is set during successful login
    if (!isset($_SESSION['id'])) {
        // User is not authenticated, redirect to login page
        header('location:login.php');
        // Exit to prevent further code execution after redirect
        exit();
    }
}

// Function to check if user is already logged in (used on login/register pages)
function require_logout() {
    // Check if session contains 'id' key which indicates an active login
    if (isset($_SESSION['id'])) {
        // User is already logged in, redirect to dashboard to avoid re-login
        header('location:dashboard.php');
        // Exit to prevent further code execution after redirect
        exit();
    }
}

// Function to get current logged-in user's ID from session
function get_current_user_id() {
    // Return the user ID from session if it exists, otherwise return null
    return isset($_SESSION['id']) ? (int)$_SESSION['id'] : null;
}

// Function to get current logged-in user's email from session
function get_current_user_email() {
    // Return the user email from session if it exists, otherwise return null
    return isset($_SESSION['email']) ? $_SESSION['email'] : null;
}

// Function to safely log out the user
function logout_user() {
    // Unset all session variables to clear the session array
    $_SESSION = array();
    
    // Get session cookie parameters to properly clear the cookie
    $params = session_get_cookie_params();
    
    // Delete the session cookie from the browser by setting expiration to past time
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
    
    // Destroy the session on the server side
    session_destroy();
}

?>
