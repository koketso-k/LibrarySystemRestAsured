<?php
require_once 'includes/auth.php';
require_once 'includes/functions.php';

$auth = new Auth();
$library = new LibraryFunctions();

// Redirect if already logged in
if($auth->isLoggedIn()) {
    if($auth->isAdmin()) {
        header('Location: admin/dashboard.php');
    } else {
        header('Location: search.php');
    }
    exit();
}

// Get featured books (random selection of 6 books)
$all_books = $library->getBooks();
$featured_books = [];

if(count($all_books) > 0) {
    // If we have books, select 6 random ones for featured section
    shuffle($all_books);
    $featured_books = array_slice($all_books, 0, 6);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library Management System</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .hero {
            background: linear-gradient(135deg, #3498db, #2c3e50);
            color: white;
            padding: 60px 0;
            text-align: center;
            margin-bottom: 40px;
            border-radius: 10px;
        }
        
        .hero h1 {
            font-size: 2.8rem;
            margin-bottom: 20px;
            font-weight: 700;
        }
        
        .hero p {
            font-size: 1.3rem;
            margin-bottom: 30px;
            opacity: 0.9;
        }
        
        .cta-buttons {
            display: flex;
            justify-content: center;
            gap: 15px;
            flex-wrap: wrap;
        }
        
        .featured-books {
            padding: 40px 0;
        }
        
        .featured-books h2 {
            text-align: center;
            margin-bottom: 30px;
            color: #2c3e50;
            font-size: 2.2rem;
        }
        
        .book-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
            margin-top: 20px;
        }
        
        .book-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border-left: 4px solid #3498db;
        }
        
        .book-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }
        
        .book-card h3 {
            color: #2c3e50;
            margin-bottom: 10px;
            font-size: 1.3rem;
            line-height: 1.4;
        }
        
        .book-card .author {
            color: #7f8c8d;
            font-style: italic;
            margin-bottom: 8px;
            font-size: 0.95rem;
        }
        
        .book-card .category {
            background: #ecf0f1;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.8rem;
            display: inline-block;
            margin-bottom: 10px;
            color: #34495e;
        }
        
        .book-card .isbn {
            font-size: 0.85rem;
            color: #95a5a6;
            margin-bottom: 5px;
        }
        
        .book-card .year {
            font-size: 0.9rem;
            color: #7f8c8d;
            margin-bottom: 10px;
        }
        
        .book-card .copies {
            font-weight: bold;
            color: #27ae60;
            margin: 15px 0;
            font-size: 1.1rem;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin: 40px 0;
        }
        
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
        }
        
        .stat-card h3 {
            color: #7f8c8d;
            margin-bottom: 15px;
            font-size: 1.1rem;
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            color: #3498db;
            margin: 0;
        }
        
        @media (max-width: 768px) {
            .hero h1 {
                font-size: 2.2rem;
            }
            
            .hero p {
                font-size: 1.1rem;
            }
            
            .cta-buttons {
                flex-direction: column;
                align-items: center;
            }
            
            .book-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <section class="hero">
            <h1>Welcome to the Digital Library</h1>
            <p>Discover, Borrow, and Explore Thousands of Books</p>
            <div class="cta-buttons">
                <a href="login.php" class="btn btn-primary">Student Login</a>
                <a href="register.php" class="btn btn-secondary">Student Register</a>
                <a href="login.php?admin=1" class="btn btn-admin">Admin Login</a>
            </div>
        </section>
        
        <!-- Library Statistics -->
       
<div class="stats-grid">
    <div class="stat-card">
        <h3>Total Books</h3>
        <p class="stat-number"><?php 
            $total_books = 0;
            foreach($all_books as $book) {
                $total_books += $book['total_copies'];
            }
            echo $total_books; 
        ?></p>
    </div>
    <div class="stat-card">
        <h3>Available Books</h3>
        <p class="stat-number"><?php 
            $available_books = 0;
            foreach($all_books as $book) {
                $available_books += $book['available_copies'];
            }
            echo $available_books; 
        ?></p>
    </div>
    <div class="stat-card">
        <h3>Borrowed Books</h3>
        <p class="stat-number"><?php 
            $borrowed_books = $total_books - $available_books;
            echo $borrowed_books; 
        ?></p>
    </div>
</div>
        <section class="featured-books">
            <h2>Featured Books</h2>
            <div class="book-grid">
                <?php if(count($featured_books) > 0): ?>
                    <?php foreach($featured_books as $book): ?>
                    <div class="book-card">
                        <h3><?php echo htmlspecialchars($book['title']); ?></h3>
                        <p class="author">by <?php echo htmlspecialchars($book['author']); ?></p>
                        <span class="category"><?php echo htmlspecialchars($book['category']); ?></span>
                        <p class="isbn">ISBN: <?php echo htmlspecialchars($book['isbn']); ?></p>
                        <p class="year">Published: <?php echo htmlspecialchars($book['publication_year']); ?></p>
                        <p class="copies">Available: <?php echo $book['available_copies']; ?>/<?php echo $book['total_copies']; ?></p>
                        <?php if($auth->isLoggedIn() && $auth->isStudent()): ?>
                            <?php if($book['available_copies'] > 0): ?>
                            <a href="borrow_book.php?book_id=<?php echo $book['id']; ?>" class="btn btn-primary">Borrow Now</a>
                            <?php else: ?>
                            <button class="btn btn-disabled" disabled>Currently Unavailable</button>
                            <?php endif; ?>
                        <?php else: ?>
                            <a href="login.php" class="btn btn-primary">Login to Borrow</a>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-books">
                        <p>No books available in the library yet.</p>
                        <?php if($auth->isLoggedIn() && $auth->isAdmin()): ?>
                            <a href="admin/add_book.php" class="btn btn-primary">Add Books</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>
        
        <!-- Library Features Section -->
        <section class="library-features">
            <h2>Why Choose Our Library?</h2>
            <div class="features-grid">
                <div class="feature">
                    <h3>📚 Wide Selection</h3>
                    <p>Access thousands of books across various genres and categories</p>
                </div>
                <div class="feature">
                    <h3>⏰ 24/7 Access</h3>
                    <p>Browse and manage your account anytime, anywhere</p>
                </div>
                <div class="feature">
                    <h3>🔍 Easy Search</h3>
                    <p>Find exactly what you're looking for with our advanced search</p>
                </div>
                <div class="feature">
                    <h3>📱 Mobile Friendly</h3>
                    <p>Access your library on any device with our responsive design</p>
                </div>
            </div>
        </section>
    </div>
    
    <?php include 'includes/footer.php'; ?>
    
    <style>
        .library-features {
            padding: 60px 0;
            background: #f8f9fa;
            margin: 40px -20px;
            padding: 40px 20px;
        }
        
        .library-features h2 {
            text-align: center;
            margin-bottom: 40px;
            color: #2c3e50;
            font-size: 2.2rem;
        }
        
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .feature {
            background: white;
            padding: 30px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            transition: transform 0.3s ease;
        }
        
        .feature:hover {
            transform: translateY(-5px);
        }
        
        .feature h3 {
            color: #2c3e50;
            margin-bottom: 15px;
            font-size: 1.4rem;
        }
        
        .feature p {
            color: #7f8c8d;
            line-height: 1.6;
        }
        
        @media (max-width: 768px) {
            .features-grid {
                grid-template-columns: 1fr;
            }
            
            .library-features {
                margin: 40px -10px;
                padding: 30px 15px;
            }
        }
    </style>
</body>
</html>