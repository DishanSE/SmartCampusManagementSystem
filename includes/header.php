<?php
// Common header for public pages
if(!isset($pageTitle)) {
    $pageTitle = "Learning Management System";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LMS - <?php echo $pageTitle; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .header {
            background-color: #2c3e50;
            color: white;
            padding: 20px 0;
        }
        
        .container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .navbar h1 {
            font-size: 24px;
        }
        
        .nav-links {
            display: flex;
        }
        
        .nav-links a {
            color: white;
            text-decoration: none;
            margin-left: 20px;
            padding: 8px 12px;
            border-radius: 4px;
            transition: background-color 0.3s;
        }
        
        .nav-links a:hover {
            background-color: #3498db;
        }
        
        .nav-links a.login-btn {
            background-color: #3498db;
        }
        
        @media (max-width: 768px) {
            .navbar {
                flex-direction: column;
            }
            
            .navbar h1 {
                margin-bottom: 15px;
            }
            
            .nav-links {
                flex-wrap: wrap;
                justify-content: center;
            }
            
            .nav-links a {
                margin: 5px;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <nav class="navbar">
                <h1>LMS</h1>
                <div class="nav-links">
                    <a href="index.php">Home</a>
                    <a href="about.php">About Us</a>
                    <a href="contact.php">Contact</a>
                    <?php if(isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true): ?>
                        <?php 
                        $dashboardUrl = '';
                        switch ($_SESSION['role']) {
                            case 'admin':
                                $dashboardUrl = 'admin/dashboard.php';
                                break;
                            case 'lecturer':
                                $dashboardUrl = 'lecturer/dashboard.php';
                                break;
                            case 'student':
                                $dashboardUrl = 'student/dashboard.php';
                                break;
                            default:
                                $dashboardUrl = 'index.php';
                        }
                        ?>
                        <a href="<?php echo $dashboardUrl; ?>">Dashboard</a>
                        <a href="logout.php">Logout</a>
                    <?php else: ?>
                        <a href="login.php" class="login-btn">Login</a>
                    <?php endif; ?>
                </div>
            </nav>
        </div>
    </header>