<?php
// Start session
session_start();

// Page title
$pageTitle = "Unauthorized Access";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LMS - <?php echo $pageTitle; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .unauthorized-container {
            max-width: 600px;
            margin: 100px auto;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 40px;
            text-align: center;
        }
        
        .unauthorized-container h2 {
            color: #e74c3c;
            margin-bottom: 20px;
        }
        
        .unauthorized-container p {
            margin-bottom: 30px;
            color: #7f8c8d;
        }
        
        .buttons {
            display: flex;
            justify-content: center;
            gap: 20px;
        }
        
        .btn-dashboard {
            background-color: #3498db;
        }
        
        .btn-logout {
            background-color: #7f8c8d;
        }
    </style>
</head>
<body>
    <div class="unauthorized-container">
        <h2><?php echo $pageTitle; ?></h2>
        <p>Sorry, you don't have permission to access this page. If you believe this is an error, please contact the administrator.</p>
        
        <div class="buttons">
            <?php if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true): ?>
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
                    case 'hod':
                        $dashboardUrl = 'Hod/page.php';
                        break;
                    default:
                        $dashboardUrl = 'index.php';
                }
                ?>
                <a href="<?php echo $dashboardUrl; ?>" class="btn btn-dashboard">Back to Dashboard</a>
                <a href="logout.php" class="btn btn-logout">Logout</a>
            <?php else: ?>
                <a href="index.php" class="btn btn-dashboard">Back to Home</a>
                <a href="login.php" class="btn btn-primary">Login</a>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>