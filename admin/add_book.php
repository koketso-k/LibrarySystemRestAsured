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

$error = '';
$success = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $isbn = trim($_POST['isbn']);
    $title = trim($_POST['title']);
    $author = trim($_POST['author']);
    $category = trim($_POST['category']);
    $publication_year = trim($_POST['publication_year']);
    $total_copies = intval($_POST['total_copies']);
    
    // Validation
    if(empty($isbn) || empty($title) || empty($author) || empty($category) || empty($publication_year) || $total_copies <= 0) {
        $error = 'All fields are required and copies must be greater than 0';
    } else {
        if($library->addBook($isbn, $title, $author, $category, $publication_year, $total_copies)) {
            $success = 'Book added successfully!';
        } else {
            $error = 'Error adding book. ISBN might already exist.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Book | Library System</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container">
        <h2>Add New Book</h2>
        
        <?php if($error): ?>
        <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if($success): ?>
        <div class="success-message"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="isbn">ISBN:</label>
                <input type="text" id="isbn" name="isbn" required>
            </div>
            
            <div class="form-group">
                <label for="title">Title:</label>
                <input type="text" id="title" name="title" required>
            </div>
            
            <div class="form-group">
                <label for="author">Author:</label>
                <input type="text" id="author" name="author" required>
            </div>
            
            <div class="form-group">
                <label for="category">Category:</label>
                <input type="text" id="category" name="category" required>
            </div>
            
            <div class="form-group">
                <label for="publication_year">Publication Year:</label>
                <input type="number" id="publication_year" name="publication_year" min="1000" max="<?php echo date('Y'); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="total_copies">Total Copies:</label>
                <input type="number" id="total_copies" name="total_copies" min="1" required>
            </div>
            
            <button type="submit" class="btn btn-primary">Add Book</button>
            <a href="manage_books.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>