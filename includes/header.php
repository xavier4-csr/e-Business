<?php
/**
 * HTML Header Component
 * Contains DOCTYPE, meta tags, common styles, and CSRF token generation
 * This file is included at the start of each page before nav bar
 */

// Ensure CSRF token exists in session (used in all forms)
if (empty($_SESSION['csrf'])) {
    // Generate a cryptographically secure random CSRF token
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

?>
<!DOCTYPE html>
<!-- HTML5 Document Type Declaration -->
<html lang="en">
<head>
    <!-- Character encoding specification for proper text rendering -->
    <meta charset="UTF-8">
    
    <!-- Viewport setting for responsive design on mobile devices -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- Page Title: Will be overridden by individual pages -->
    <title><?php echo isset($page_title) ? htmlspecialchars($page_title) : 'RegE - Business Registration'; ?></title>
    
    <!-- Global Stylesheet with Common Styles -->
    <style>
        /* Reset default browser styles and set baseline */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        /* Body styling: Set font, background, and layout */
        body {
            font-family: 'Arial', 'Segoe UI', sans-serif;
            background-color: #f4f4f4;
            color: #333;
            line-height: 1.6;
        }
        
        /* Main container: Center content and add padding */
        .container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        
        /* Page heading styling */
        h1 {
            color: #007bff;
            margin-bottom: 20px;
            font-size: 28px;
            border-bottom: 2px solid #007bff;
            padding-bottom: 10px;
        }
        
        /* Secondary heading styling */
        h2 {
            color: #333;
            margin-bottom: 15px;
            font-size: 22px;
        }
        
        /* Form input styling */
        input[type="text"],
        input[type="email"],
        input[type="password"],
        input[type="number"],
        input[type="url"],
        textarea,
        select {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-family: inherit;
            font-size: 14px;
        }
        
        /* Input focus state: Highlight when user is typing */
        input[type="text"]:focus,
        input[type="email"]:focus,
        input[type="password"]:focus,
        input[type="number"]:focus,
        input[type="url"]:focus,
        textarea:focus,
        select:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 5px rgba(0, 123, 255, 0.3);
        }
        
        /* Label styling for form fields */
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
        }
        
        /* Button styling: Primary action buttons */
        button,
        .btn {
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.3s;
        }
        
        /* Button hover state: Visual feedback on mouse over */
        button:hover,
        .btn:hover {
            background-color: #0056b3;
        }
        
        /* Danger button styling: For delete operations -->
        .btn-danger {
            background-color: #dc3545;
        }
        
        /* Danger button hover state */
        .btn-danger:hover {
            background-color: #c82333;
        }
        
        /* Success button styling: For positive actions */
        .btn-success {
            background-color: #28a745;
        }
        
        /* Success button hover state */
        .btn-success:hover {
            background-color: #218838;
        }
        
        /* Table styling: For displaying data in grid format -->
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        /* Table header cell styling */
        th {
            background-color: #f8f9fa;
            padding: 12px;
            text-align: left;
            font-weight: bold;
            border-bottom: 2px solid #ddd;
        }
        
        /* Table data cell styling */
        td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
        }
        
        /* Table row hover: Highlight row on mouse over -->
        tr:hover {
            background-color: #f9f9f9;
        }
        
        /* Error message styling: Display validation/error messages -->
        .error {
            background-color: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 15px;
            border: 1px solid #f5c6cb;
        }
        
        /* Success message styling: Display confirmation messages -->
        .success {
            background-color: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 15px;
            border: 1px solid #c3e6cb;
        }
        
        /* Info message styling: Display informational messages -->
        .info {
            background-color: #d1ecf1;
            color: #0c5460;
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 15px;
            border: 1px solid #bee5eb;
        }
        
        /* Card component: Grouped content box -->
        .card {
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        
        /* Stats component: Display key metrics -->
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        
        /* Stat card styling */
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
        }
        
        /* Stat card number styling */
        .stat-number {
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        /* Stat card label styling */
        .stat-label {
            font-size: 14px;
            opacity: 0.9;
        }
        
        /* Form group: Container for a form field and label -->
        .form-group {
            margin-bottom: 20px;
        }
        
        /* Row layout: Display items in a horizontal row -->
        .row {
            display: flex;
            gap: 15px;
            margin-bottom: 15px;
        }
        
        /* Column: Flexible width item in row -->
        .col {
            flex: 1;
        }
        
        /* Action buttons group: Align buttons horizontally -->
        .action-buttons {
            display: flex;
            gap: 10px;
        }
    </style>
    
    <!-- Google Fonts: Optional, can be used for better typography -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
</head>

<!-- Body tag: HTML content starts here -->
<body>
