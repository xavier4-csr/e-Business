<?php
/**
 * Navigation Bar Component
 * Shared header/navigation used across all authenticated pages
 * Displays user info, navigation links, and logout button
 */

// Get current page name to highlight active link
$current_page = basename($_SERVER['PHP_SELF']);

// Retrieve current logged-in user information from session
$user_id = isset($_SESSION['id']) ? (int)$_SESSION['id'] : null;
$user_email = isset($_SESSION['email']) ? $_SESSION['email'] : 'User';

?>

<!-- Main Navigation Bar Container -->
<nav style="background-color: #333; color: #fff; padding: 15px 20px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
    
    <!-- Left Side: Logo and Brand Name -->
    <div style="display: flex; align-items: center; gap: 15px;">
        <!-- Brand/Logo Text -->
        <h2 style="margin: 0; color: #fff; font-size: 24px;">RegE</h2>
    </div>
    
    <!-- Center: Main Navigation Links -->
    <ul style="list-style-type: none; margin: 0; padding: 0; display: flex; gap: 20px; flex: 1; justify-content: center;">
        
        <!-- Dashboard Link: Shows overview of user's companies -->
        <li>
            <a href="dashboard.php" 
               style="text-decoration: none; color: #fff; padding: 8px 12px; border-radius: 3px; <?php echo ($current_page === 'dashboard.php') ? 'background-color: #007bff; font-weight: bold;' : 'transition: background-color 0.3s;'; ?>"
               <?php echo ($current_page === 'dashboard.php') ? '' : 'onmouseover="this.style.backgroundColor=\'#555\'" onmouseout="this.style.backgroundColor=\'transparent\'"'; ?>>
                📊 Dashboard
            </a>
        </li>
        
        <!-- Home Link: Returns to public home page -->
        <li>
            <a href="Home.php" 
               style="text-decoration: none; color: #fff; padding: 8px 12px; border-radius: 3px; <?php echo ($current_page === 'Home.php') ? 'background-color: #007bff; font-weight: bold;' : 'transition: background-color 0.3s;'; ?>"
               <?php echo ($current_page === 'Home.php') ? '' : 'onmouseover="this.style.backgroundColor=\'#555\'" onmouseout="this.style.backgroundColor=\'transparent\'"'; ?>>
                🏠 Home
            </a>
        </li>
        
        <!-- Register Business Link: Add new company -->
        <li>
            <a href="business-registration.php" 
               style="text-decoration: none; color: #fff; padding: 8px 12px; border-radius: 3px; <?php echo ($current_page === 'business-registration.php') ? 'background-color: #007bff; font-weight: bold;' : 'transition: background-color 0.3s;'; ?>"
               <?php echo ($current_page === 'business-registration.php') ? '' : 'onmouseover="this.style.backgroundColor=\'#555\'" onmouseout="this.style.backgroundColor=\'transparent\'"'; ?>>
                ➕ Register Business
            </a>
        </li>
        
        <!-- Contact Link: Contact page -->
        <li>
            <a href="contact.php" 
               style="text-decoration: none; color: #fff; padding: 8px 12px; border-radius: 3px; <?php echo ($current_page === 'contact.php') ? 'background-color: #007bff; font-weight: bold;' : 'transition: background-color 0.3s;'; ?>"
               <?php echo ($current_page === 'contact.php') ? '' : 'onmouseover="this.style.backgroundColor=\'#555\'" onmouseout="this.style.backgroundColor=\'transparent\'"'; ?>>
                📧 Contact
            </a>
        </li>
        
        <!-- Profile Link: User account settings and information -->
        <li>
            <a href="profile.php" 
               style="text-decoration: none; color: #fff; padding: 8px 12px; border-radius: 3px; <?php echo ($current_page === 'profile.php') ? 'background-color: #007bff; font-weight: bold;' : 'transition: background-color 0.3s;'; ?>"
               <?php echo ($current_page === 'profile.php') ? '' : 'onmouseover="this.style.backgroundColor=\'#555\'" onmouseout="this.style.backgroundColor=\'transparent\'"'; ?>>
                👤 Profile
            </a>
        </li>
    </ul>
    
    <!-- Right Side: User Info and Logout -->
    <div style="display: flex; align-items: center; gap: 15px;">
        
        <!-- Display Current User Email -->
        <span style="color: #fff; font-size: 14px;">
            Logged in as: <strong><?php echo htmlspecialchars($user_email); ?></strong>
        </span>
        
        <!-- Logout Button: Terminates user session -->
        <a href="logout.php" 
           style="background-color: #dc3545; color: #fff; padding: 8px 15px; text-decoration: none; border-radius: 3px; transition: background-color 0.3s; cursor: pointer;"
           onmouseover="this.style.backgroundColor='#c82333'"
           onmouseout="this.style.backgroundColor='#dc3545'">
            🚪 Logout
        </a>
    </div>
</nav>

<!-- Optional: Breadcrumb Navigation Bar (shows current location in app) -->
<div style="background-color: #f8f9fa; padding: 10px 20px; border-bottom: 1px solid #ddd; font-size: 13px;">
    <!-- Home Arrow Indicator -->
    🏠 
    <!-- Dynamic Breadcrumb: Show current page path -->
    <?php 
        // Map page filenames to readable breadcrumb text
        $breadcrumbs = array(
            'dashboard.php' => 'Dashboard',
            'profile.php' => 'Profile Settings',
            'business-registration.php' => 'Register Business',
            'update.php' => 'Edit Company',
            'contact.php' => 'Contact',
            'Home.php' => 'Home'
        );
        
        // Display breadcrumb text for current page, or fallback to page name
        echo isset($breadcrumbs[$current_page]) ? $breadcrumbs[$current_page] : ucfirst(str_replace('.php', '', $current_page));
    ?>
</div>

