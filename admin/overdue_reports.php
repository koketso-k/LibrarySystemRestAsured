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

// Get overdue books
$overdue_books = $library->getOverdueBooks();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Overdue Reports | Library System</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container">
        <h2>Overdue Books Report</h2>
        
        <?php if(count($overdue_books) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Book</th>
                        <th>ISBN</th>
                        <th>Borrow Date</th>
                        <th>Due Date</th>
                        <th>Days Overdue</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $today = new DateTime();
                    foreach($overdue_books as $book): 
                        $due_date = new DateTime($book['due_date']);
                        $interval = $today->diff($due_date);
                        $days_overdue = $interval->days;
                        if($today > $due_date) {
                            $days_overdue = $interval->days;
                        } else {
                            $days_overdue = 0;
                        }
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($book['full_name']); ?> (<?php echo htmlspecialchars($book['student_number']); ?>)</td>
                        <td><?php echo htmlspecialchars($book['title']); ?></td>
                        <td><?php echo htmlspecialchars($book['isbn']); ?></td>
                        <td><?php echo $book['borrow_date']; ?></td>
                        <td><?php echo $book['due_date']; ?></td>
                        <td><?php echo $days_overdue; ?> days</td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <div class="export-button">
                <a href="export_overdue.php" class="btn btn-primary">Export to CSV</a>
            </div>
        <?php else: ?>
            <p>No overdue books at this time.</p>
        <?php endif; ?>
    </div>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>