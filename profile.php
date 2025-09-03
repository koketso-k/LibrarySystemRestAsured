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
$borrowed_books = $library->getBorrowedBooks($student_id);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile | Library System</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .status-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.8em;
            font-weight: bold;
        }
        
        .status-borrowed {
            background-color: #e3f2fd;
            color: #1976d2;
        }
        
        .status-overdue {
            background-color: #ffebee;
            color: #d32f2f;
        }
        
        .status-returned {
            background-color: #e8f5e9;
            color: #388e3c;
        }
        
        .due-date {
            font-weight: bold;
        }
        
        .overdue {
            color: #d32f2f;
        }
        
        .due-soon {
            color: #f57c00;
        }
        
        .fine-amount {
            color: #d32f2f;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <h2>My Profile</h2>
        
        <div class="profile-info">
            <p><strong>Student Number:</strong> <?php echo $_SESSION['student_number']; ?></p>
            <p><strong>Full Name:</strong> <?php echo $_SESSION['full_name']; ?></p>
            <p><strong>Email:</strong> <?php echo $_SESSION['email']; ?></p>
        </div>
        
        <h3>My Borrowed Books</h3>
        
        <?php if(count($borrowed_books) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Author</th>
                        <th>Borrow Date</th>
                        <th>Due Date</th>
                        <th>Days Left</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($borrowed_books as $book): 
                        $today = new DateTime();
                        $due_date = new DateTime($book['due_date']);
                        $interval = $today->diff($due_date);
                        $days_left = $interval->format('%r%a');
                        
                        if ($book['status'] == 'returned') {
                            $status_class = 'status-returned';
                            $status_text = 'Returned';
                            $days_text = '-';
                        } else if ($due_date < $today) {
                            $status_class = 'status-overdue';
                            $status_text = 'Overdue';
                            $days_left = abs($days_left); // Make positive
                            $days_text = "<span class='overdue'>-$days_left days</span>";
                        } else {
                            $status_class = 'status-borrowed';
                            $status_text = 'Borrowed';
                            $days_text = "$days_left days";
                            
                            // Highlight if due in 3 days or less
                            if ($days_left <= 3) {
                                $days_text = "<span class='due-soon'>$days_left days</span>";
                            }
                        }
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($book['title']); ?></td>
                        <td><?php echo htmlspecialchars($book['author']); ?></td>
                        <td><?php echo date('M j, Y', strtotime($book['borrow_date'])); ?></td>
                        <td class="due-date <?php echo ($due_date < $today && $book['status'] != 'returned') ? 'overdue' : ''; ?>">
                            <?php echo date('M j, Y', strtotime($book['due_date'])); ?>
                        </td>
                        <td><?php echo $days_text; ?></td>
                        <td><span class="status-badge <?php echo $status_class; ?>"><?php echo $status_text; ?></span></td>
                        <td>
                            <?php if($book['status'] != 'returned'): ?>
                            <a href="return_book.php?borrow_id=<?php echo $book['borrow_id']; ?>" class="btn btn-primary">Return</a>
                            <?php else: ?>
                            <span>Already Returned</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    
                    <?php if($due_date < $today && $book['status'] != 'returned'): ?>
                    <tr class="overdue-details">
                        <td colspan="7" style="background-color: #ffebee; padding: 10px;">
                            ⚠️ <strong>This book is overdue!</strong> 
                            Fine accrued: <span class="fine-amount">$<?php echo number_format(abs($days_left) * 0.5, 2); ?></span>
                            (<?php echo abs($days_left); ?> days × $0.50/day)
                        </td>
                    </tr>
                    <?php endif; ?>
                    
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <div class="borrowing-summary">
                <h4>Borrowing Summary</h4>
                <?php
                $total_borrowed = 0;
                $total_overdue = 0;
                $total_fines = 0;
                
                foreach($borrowed_books as $book) {
                    if ($book['status'] != 'returned') {
                        $total_borrowed++;
                        
                        $due_date = new DateTime($book['due_date']);
                        $today = new DateTime();
                        
                        if ($due_date < $today) {
                            $total_overdue++;
                            $interval = $today->diff($due_date);
                            $days_overdue = $interval->days;
                            $total_fines += $days_overdue * 0.5;
                        }
                    }
                }
                ?>
                <p>Books currently borrowed: <strong><?php echo $total_borrowed; ?></strong></p>
                <p>Overdue books: <strong><?php echo $total_overdue; ?></strong></p>
                <?php if ($total_fines > 0): ?>
                <p class="overdue">Total fines due: <strong>$<?php echo number_format($total_fines, 2); ?></strong></p>
                <?php endif; ?>
            </div>
            
        <?php else: ?>
            <p>You haven't borrowed any books yet.</p>
            <a href="search.php" class="btn btn-primary">Browse Books</a>
        <?php endif; ?>
    </div>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>