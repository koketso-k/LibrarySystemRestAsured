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

// Get all books
$books = $library->getBooks();

// Handle book deletion
if(isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $result = $library->deleteBook($delete_id);
    
    if($result == 'success') {
        $_SESSION['success'] = 'Book deleted successfully!';
    } elseif($result == 'currently_borrowed') {
        $_SESSION['error'] = 'Cannot delete book. It is currently borrowed by students.';
    } else {
        $_SESSION['error'] = 'Error deleting book.';
    }
    
    header('Location: manage_books.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Books | Library System</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container">
        <h2>Manage Books</h2>
        
        <?php if(isset($_SESSION['success'])): ?>
            <div class="success-message"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        
        <?php if(isset($_SESSION['error'])): ?>
            <div class="error-message"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>
        
        <div class="action-buttons">
            <a href="add_book.php" class="btn btn-primary">Add New Book</a>
        </div>
        
        <?php if(count($books) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>ISBN</th>
                        <th>Title</th>
                        <th>Author</th>
                        <th>Category</th>
                        <th>Year</th>
                        <th>Copies</th>
                        <th>Available</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($books as $book): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($book['isbn']); ?></td>
                        <td><?php echo htmlspecialchars($book['title']); ?></td>
                        <td><?php echo htmlspecialchars($book['author']); ?></td>
                        <td><?php echo htmlspecialchars($book['category']); ?></td>
                        <td><?php echo htmlspecialchars($book['publication_year']); ?></td>
                        <td><?php echo $book['total_copies']; ?></td>
                        <td><?php echo $book['available_copies']; ?></td>
                        <td>
                            <a href="edit_book.php?id=<?php echo $book['id']; ?>" class="btn btn-secondary">Edit</a>
                            <a href="manage_books.php?delete_id=<?php echo $book['id']; ?>" class="btn btn-admin" onclick="return confirm('Are you sure you want to delete this book?')">Delete</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No books found in the library.</p>
        <?php endif; ?>
    </div>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>