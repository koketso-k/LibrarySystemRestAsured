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

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$books = $library->getBooks($search);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Books | Library System</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <h2>Search Books</h2>
        
        <form method="GET" action="search.php" class="search-form">
            <input type="text" name="search" placeholder="Search by title, author, or ISBN" value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit" class="btn btn-primary">Search</button>
        </form>
        
        <div class="book-grid">
            <?php if(count($books) > 0): ?>
                <?php foreach($books as $book): ?>
                <div class="book-card">
                    <h3><?php echo htmlspecialchars($book['title']); ?></h3>
                    <p class="author">by <?php echo htmlspecialchars($book['author']); ?></p>
                    <p class="isbn">ISBN: <?php echo htmlspecialchars($book['isbn']); ?></p>
                    <p class="category">Category: <?php echo htmlspecialchars($book['category']); ?></p>
                    <p class="year">Year: <?php echo htmlspecialchars($book['publication_year']); ?></p>
                    <p class="copies">Available: <?php echo $book['available_copies']; ?>/<?php echo $book['total_copies']; ?></p>
                    
                    <?php if($book['available_copies'] > 0): ?>
                    <a href="borrow_book.php?book_id=<?php echo $book['id']; ?>" class="btn btn-primary">Borrow</a>
                    <?php else: ?>
                    <button class="btn btn-disabled" disabled>Not Available</button>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No books found.</p>
            <?php endif; ?>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>