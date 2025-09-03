<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';

$auth = new Auth();
$library = new LibraryFunctions();

// Redirect if not admin
if(!$auth->isLoggedIn() || !$auth->isAdmin()) {
    header('Location: ../login.php');
    exit();
}

// Send due date reminders
$reminders_sent = $library->sendDueDateReminders();

// Send overdue notifications
$overdue_notices_sent = $library->sendOverdueNotifications();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Send Reminders | Library System</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container">
        <h2>Email Reminders Sent</h2>
        
        <div class="reminder-results">
            <p>Due date reminders sent: <strong><?php echo $reminders_sent; ?></strong></p>
            <p>Overdue notices sent: <strong><?php echo $overdue_notices_sent; ?></strong></p>
        </div>
        
        <p>Note: In a production environment, these would be actual emails sent to students.
           For this demo, the notifications are logged to the server error log.</p>
        
        <a href="dashboard.php" class="btn btn-primary">Back to Dashboard</a>
    </div>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>