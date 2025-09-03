<?php
require_once 'auth.php';
$auth = new Auth();

// Use the BASE_URL constant from config
$base_url = defined('BASE_URL') ? BASE_URL : 'http://localhost:3000/';
?>
<header>
    <div class="container">
        <nav>
            <div class="logo">
                <a href="<?php echo $base_url; ?>index.php">Library System</a>
            </div>
            <ul class="nav-links">
                <?php if($auth->isLoggedIn()): ?>
                    <?php if($auth->isAdmin()): ?>
                        <li><a href="<?php echo $base_url; ?>admin/dashboard.php">Dashboard</a></li>
                        <li><a href="<?php echo $base_url; ?>admin/manage_books.php">Manage Books</a></li>
                        <li><a href="<?php echo $base_url; ?>admin/view_borrowed.php">View Borrowed</a></li>
                        <li><a href="<?php echo $base_url; ?>admin/overdue_reports.php">Overdue Reports</a></li>
                    <?php else: ?>
                        <li><a href="<?php echo $base_url; ?>search.php">Search Books</a></li>
                        <li><a href="<?php echo $base_url; ?>profile.php">My Profile</a></li>
                    <?php endif; ?>
                    <!-- FIXED: Logout link should point to root directory, not admin -->
                    <li><a href="<?php echo $base_url; ?>logout.php">Logout</a></li>
                <?php else: ?>
                    <li><a href="<?php echo $base_url; ?>index.php">Home</a></li>
                    <li><a href="<?php echo $base_url; ?>login.php">Login</a></li>
                    <li><a href="<?php echo $base_url; ?>register.php">Register</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
</header>