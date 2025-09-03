<?php
require_once 'includes/auth.php';

$auth = new Auth();

// Redirect if already logged in
if($auth->isLoggedIn()) {
    if($auth->isAdmin()) {
        header('Location: admin/dashboard.php');
    } else {
        header('Location: search.php');
    }
    exit();
}

$is_admin_login = isset($_GET['admin']) && $_GET['admin'] == 1;
$error = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    if($is_admin_login) {
        // Admin login
        $username = trim($_POST['username']);
        $password = trim($_POST['password']);
        
        if($auth->loginAdmin($username, $password)) {
            header('Location: admin/dashboard.php');
            exit();
        } else {
            $error = 'Invalid username or password';
        }
    } else {
        // Student login
        $student_number = trim($_POST['student_number']);
        $password = trim($_POST['password']);
        
        if($auth->loginStudent($student_number, $password)) {
            header('Location: search.php');
            exit();
        } else {
            $error = 'Invalid student number or password';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $is_admin_login ? 'Admin Login' : 'Student Login'; ?> | Library System</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <div class="login-form">
            <h2><?php echo $is_admin_login ? 'Admin Login' : 'Student Login'; ?></h2>
            
            <?php if($error): ?>
            <div class="error-message"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <?php if($is_admin_login): ?>
                <div class="form-group">
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" required>
                </div>
                <?php else: ?>
                <div class="form-group">
                    <label for="student_number">Student Number:</label>
                    <input type="text" id="student_number" name="student_number" required>
                </div>
                <?php endif; ?>
                
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <button type="submit" class="btn btn-primary">Login</button>
            </form>
            
            <?php if(!$is_admin_login): ?>
            <p>Don't have an account? <a href="register.php">Register here</a></p>
            <p>Are you an admin? <a href="login.php?admin=1">Login here</a></p>
            <?php else: ?>
            <p>Are you a student? <a href="login.php">Student login</a></p>
            <?php endif; ?>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>