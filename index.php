<?php
// Include config file
require_once 'config.php';

// Start session
session_start();

// Check if user is already logged in, redirect based on role
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    switch ($_SESSION['role']) {
        case 'admin':
            header("location: admin/dashboard.php");
            exit;
        case 'lecturer':
            header("location: lecturer/dashboard.php");
            exit;
        case 'student':
            header("location: student/dashboard.php");
            exit;
        case 'hod':
            header("location: Hod/page.php");
            exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Campus Management System</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f5f5f5;
        }
        
        .container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .header {
            background-color: #2c3e50;
            color: white;
            padding: 20px 0;
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
        
        .hero {
            padding: 270px 0;
            text-align: center;
            background-color: #3498db;
            color: white;
        }
        
        .hero h2 {
            font-size: 36px;
            margin-bottom: 20px;
        }
        
        .hero p {
            font-size: 18px;
            max-width: 600px;
            margin: 0 auto 30px;
        }
        
        .cta-button {
            display: inline-block;
            background-color: white;
            color: #3498db;
            padding: 12px 24px;
            border-radius: 4px;
            text-decoration: none;
            font-weight: bold;
            transition: all 0.3s;
        }
        
        .cta-button:hover {
            background-color: #f5f5f5;
            transform: translateY(-2px);
        }
        
        .features {
            padding: 80px 0;
        }
        
        .section-title {
            text-align: center;
            margin-bottom: 50px;
        }
        
        .section-title h2 {
            font-size: 30px;
            color: #2c3e50;
            margin-bottom: 10px;
        }
        
        .section-title p {
            color: #7f8c8d;
        }
        
        .feature-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
        }
        
        .feature-card {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 30px;
            text-align: center;
        }
        
        .feature-card img {
            width: 80px;
            height: 80px;
            margin-bottom: 20px;
        }
        
        .feature-card h3 {
            font-size: 20px;
            color: #2c3e50;
            margin-bottom: 15px;
        }
        
        .feature-card p {
            color: #7f8c8d;
        }
        
        .footer {
            background-color: #2c3e50;
            color: white;
            padding: 40px 0;
            text-align: center;
        }
        
        .footer p {
            margin-bottom: 20px;
        }
        
        .social-links a {
            color: white;
            margin: 0 10px;
            font-size: 20px;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <nav class="navbar">
                <h1>SCMS</h1>
                <div class="nav-links">
                    <a href="index.php">Home</a>
                    <a href="about.php">About Us</a>
                    <a href="contact.php">Contact</a>
                    <a href="login.php" class="login-btn">Login</a>
                </div>
            </nav>
        </div>
    </header>
    
    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <h2>Welcome to Our Smart Campous Management System</h2>
            <a href="login.php" class="cta-button">Get Started</a>
        </div>
    </section>
    
    
    
    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p>&copy; 2025 Learning Management System. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>