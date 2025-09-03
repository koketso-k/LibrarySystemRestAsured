<?php
require_once 'includes/auth.php';
require_once 'includes/functions.php';

$auth = new Auth();
$library = new LibraryFunctions();

// Redirect if not logged in
if(!$auth->isLoggedIn() || !$auth->isStudent()) {
    header('Location: login.php');
    exit();
}

$student_id = $_SESSION['student_id'];
$book_id = isset($_GET['book_id']) ? intval($_GET['book_id']) : 0;

if($book_id <= 0) {
    header('Location: search.php');
    exit();
}

// Get book details
$book = $library->getBookById($book_id);

if(!$book) {
    header('Location: search.php');
    exit();
}

$message = '';
$due_date = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $result = $library->borrowBook($student_id, $book_id);
    
    switch($result) {
        case 'success':
            $message = 'Book borrowed successfully!';
            // Calculate due date (14 days from today)
            $due_date = date('Y-m-d', strtotime('+14 days'));
            break;
        case 'already_borrowed':
            $message = 'You have already borrowed this book and not returned it.';
            break;
        case 'not_available':
            $message = 'This book is not available for borrowing.';
            break;
        default:
            $message = 'An error occurred. Please try again.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Borrow Book | Library System</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .due-date-info {
            background-color: #e8f5e9;
            border-left: 4px solid #4caf50;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        
        .due-date {
            font-weight: bold;
            color: #2e7d32;
            font-size: 1.2em;
        }
        
        .reminder {
            margin-top: 10px;
            font-size: 0.9em;
            color: #555;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <h2>Borrow Book</h2>
        
        <?php if($message): ?>
        <div class="message"><?php echo $message; ?></div>
        
        <?php if($message == 'Book borrowed successfully!'): ?>
        <div class="due-date-info">
            <h3>📚 Borrowing Successful!</h3>
            <p>Your book has been borrowed successfully. Please note the following important information:</p>
            <p class="due-date">Due Date: <?php echo date('F j, Y', strtotime($due_date)); ?></p>
            <div class="reminder">
                <p>⏰ <strong>Reminders:</strong></p>
                <ul>
                    <li>Books must be returned on or before the due date</li>
                    <li>Late returns incur fines of $0.50 per day per book</li>
                    <li>You can view all your borrowed books in your Profile page</li>
                    <li>You will receive email reminders 3 days before the due date</li>
                </ul>
            </div>
        </div>
        <?php endif; ?>
        
        <?php endif; ?>
        
        <div class="book-details">
            <h3><?php echo htmlspecialchars($book['title']); ?></h3>
            <p><strong>Author:</strong> <?php echo htmlspecialchars($book['author']); ?></p>
            <p><strong>ISBN:</strong> <?php echo htmlspecialchars($book['isbn']); ?></p>
            <p><strong>Category:</strong> <?php echo htmlspecialchars($book['category']); ?></p>
            <p><strong>Publication Year:</strong> <?php echo htmlspecialchars($book['publication_year']); ?></p>
            <p><strong>Available Copies:</strong> <?php echo $book['available_copies']; ?>/<?php echo $book['total_copies']; ?></p>
        </div>
        
        <?php if($book['available_copies'] > 0 && empty($message)): ?>
        <div class="due-date-info">
            <h3>📅 Borrowing Information</h3>
            <p class="due-date">If you borrow this book, it will be due on: <?php echo date('F j, Y', strtotime('+14 days')); ?></p>
            <p class="reminder">Loan period: 14 days | Renewals: 1 renewal possible (if no holds)</p>
        </div>
        
        <form method="POST" action="">
            <p>Are you sure you want to borrow this book?</p>
            <button type="submit" class="btn btn-primary">Confirm Borrow</button>
            <a href="search.php" class="btn btn-secondary">Cancel</a>
        </form>
        <?php else: ?>
        <a href="search.php" class="btn btn-primary">Back to Search</a>
        <?php endif; ?>
    </div>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>