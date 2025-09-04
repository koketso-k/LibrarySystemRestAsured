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

// Get stats
$books = $library->getBooks();
$borrowed_books = $library->getAllBorrowedBooks();
$overdue_books = $library->getOverdueBooks();

$total_books = count($books);
$total_borrowed = 0;
$total_overdue = count($overdue_books);

foreach($borrowed_books as $book) {
    if($book['status'] != 'returned') {
        $total_borrowed++;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Library System</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container">
        <h2>Admin Dashboard</h2>
        
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Books</h3>
                <p class="stat-number"><?php echo $total_books; ?></p>
            </div>
            
            <div class="stat-card">
                <h3>Books Borrowed</h3>
                <p class="stat-number"><?php echo $total_borrowed; ?></p>
            </div>
            
            <div class="stat-card">
                <h3>Overdue Books</h3>
                <p class="stat-number"><?php echo $total_overdue; ?></p>
            </div>
        </div>
        
        <div class="recent-activity">
            <h3>Recent Borrowings</h3>
            
            <?php if(count($borrowed_books) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Book</th>
                            <th>Borrow Date</th>
                            <th>Due Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $recent_borrowings = array_slice($borrowed_books, 0, 5);
                        foreach($recent_borrowings as $book): 
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($book['full_name']); ?> (<?php echo htmlspecialchars($book['student_number']); ?>)</td>
                            <td><?php echo htmlspecialchars($book['title']); ?> (ISBN: <?php echo htmlspecialchars($book['isbn']); ?>)</td>
                            <td><?php echo $book['borrow_date']; ?></td>
                            <td><?php echo $book['due_date']; ?></td>
                            <td>
                                <?php 
                                if($book['status'] == 'returned') {
                                    echo 'Returned';
                                } else {
                                    $due_date = new DateTime($book['due_date']);
                                    $today = new DateTime();
                                    
                                    if($due_date < $today) {
                                        echo 'Overdue';
                                    } else {
                                        echo 'Borrowed';
                                    }
                                }
                                ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No books have been borrowed yet.</p>
            <?php endif; ?>
        </div>

        
        
        <div class="admin-actions">
            <h3>Quick Actions</h3>
            <div class="action-buttons">
                <a href="add_book.php" class="btn btn-primary">Add New Book</a>
                <a href="manage_books.php" class="btn btn-secondary">Manage Books</a>
                <a href="view_borrowed.php" class="btn btn-secondary">View All Borrowed</a>
                <a href="overdue_reports.php" class="btn btn-admin">Overdue Reports</a>
                <a href="send_reminders.php" class="btn btn-primary">Send Reminders</a>
            </div>
        </div>
    </div>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>