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

// Get all borrowed books
$borrowed_books = $library->getAllBorrowedBooks();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Borrowed Books | Library System</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container">
        <h2>View All Borrowed Books</h2>
        
        <?php if(count($borrowed_books) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Book</th>
                        <th>ISBN</th>
                        <th>Borrow Date</th>
                        <th>Due Date</th>
                        <th>Return Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($borrowed_books as $book): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($book['full_name']); ?> (<?php echo htmlspecialchars($book['student_number']); ?>)</td>
                        <td><?php echo htmlspecialchars($book['title']); ?></td>
                        <td><?php echo htmlspecialchars($book['isbn']); ?></td>
                        <td><?php echo $book['borrow_date']; ?></td>
                        <td><?php echo $book['due_date']; ?></td>
                        <td><?php echo $book['return_date'] ? $book['return_date'] : 'Not returned'; ?></td>
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
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>