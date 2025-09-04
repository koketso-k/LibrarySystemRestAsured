-- NADV 744 - Optimized Library Database Schema
-- 

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- Create optimized database with proper character set
DROP DATABASE IF EXISTS library_db;
CREATE DATABASE IF NOT EXISTS library_db 
CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE library_db;

-- Optimized Students table with indexes
CREATE TABLE students (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  student_number VARCHAR(20) NOT NULL,
  full_name VARCHAR(100) NOT NULL,
  email VARCHAR(100) NOT NULL,
  password VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  last_login TIMESTAMP NULL DEFAULT NULL,
  status ENUM('active', 'inactive') DEFAULT 'active',
  PRIMARY KEY (id),
  UNIQUE KEY unique_student_number (student_number),
  UNIQUE KEY unique_email (email),
  INDEX idx_student_status (status),
  INDEX idx_last_login (last_login)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Optimized Books table with full-text search capability
CREATE TABLE books (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  isbn VARCHAR(20) NOT NULL,
  title VARCHAR(255) NOT NULL,
  author VARCHAR(100) NOT NULL,
  category VARCHAR(50) NOT NULL,
  publication_year YEAR NOT NULL,
  publisher VARCHAR(100) DEFAULT NULL,
  description TEXT DEFAULT NULL,
  total_copies INT UNSIGNED DEFAULT 1,
  available_copies INT UNSIGNED DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY unique_isbn (isbn),
  INDEX idx_category (category),
  INDEX idx_author (author),
  INDEX idx_availability (available_copies),
  INDEX idx_publication_year (publication_year),
  FULLTEXT KEY ft_search (title, author, description)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Optimized Borrows table with proper indexing
CREATE TABLE borrows (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  student_id INT UNSIGNED NOT NULL,
  book_id INT UNSIGNED NOT NULL,
  borrow_date DATE NOT NULL,
  due_date DATE NOT NULL,
  return_date DATE DEFAULT NULL,
  status ENUM('borrowed', 'returned', 'overdue') DEFAULT 'borrowed',
  fine_amount DECIMAL(10,2) DEFAULT 0.00,
  reminder_sent TINYINT(1) DEFAULT 0,
  overdue_notice_sent TINYINT(1) DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  INDEX idx_student_borrows (student_id, status),
  INDEX idx_book_borrows (book_id, status),
  INDEX idx_due_date (due_date),
  INDEX idx_return_date (return_date),
  INDEX idx_borrow_status (status),
  FOREIGN KEY (student_id) REFERENCES students (id) ON DELETE CASCADE,
  FOREIGN KEY (book_id) REFERENCES books (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Admin table with enhanced security
CREATE TABLE admins (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  username VARCHAR(50) NOT NULL,
  password VARCHAR(255) NOT NULL,
  email VARCHAR(100) NOT NULL,
  full_name VARCHAR(100) NOT NULL,
  role ENUM('superadmin', 'admin', 'librarian') DEFAULT 'librarian',
  last_login TIMESTAMP NULL DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  status ENUM('active', 'inactive') DEFAULT 'active',
  PRIMARY KEY (id),
  UNIQUE KEY unique_username (username),
  UNIQUE KEY unique_admin_email (email),
  INDEX idx_admin_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default admin with secure password (password: 'password')
INSERT INTO admins (username, password, email, full_name, role) 
VALUES (
  'admin', 
  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 
  'admin@library.com', 
  'System Administrator', 
  'superadmin'
);

-- Insert sample books for testing
INSERT INTO books (isbn, title, author, category, publication_year, publisher, description, total_copies, available_copies) VALUES
('978-0061120084', 'To Kill a Mockingbird', 'Harper Lee', 'Fiction', 1960, 'J.B. Lippincott & Co.', 'A novel about race and class in 1930s Deep South', 5, 5),
('978-0451524935', '1984', 'George Orwell', 'Fiction', 1949, 'Secker & Warburg', 'Dystopian social science fiction novel', 3, 3),
('978-0141439518', 'Pride and Prejudice', 'Jane Austen', 'Romance', 1813, 'T. Egerton', 'Romantic novel of manners', 4, 4),
('978-0544003415', 'The Lord of the Rings', 'J.R.R. Tolkien', 'Fantasy', 1954, 'Allen & Unwin', 'Epic high fantasy novel', 6, 6),
('978-0439023481', 'The Hunger Games', 'Suzanne Collins', 'Young Adult', 2008, 'Scholastic', 'Dystopian novel', 5, 5);

-- Create optimized database views
CREATE VIEW available_books_view AS
SELECT 
    id, isbn, title, author, category, 
    publication_year, available_copies, total_copies
FROM books 
WHERE available_copies > 0;

CREATE VIEW overdue_books_view AS
SELECT 
    b.id as borrow_id,
    s.student_number,
    s.full_name as student_name,
    s.email as student_email,
    bk.title as book_title,
    bk.isbn,
    b.borrow_date,
    b.due_date,
    b.return_date,
    DATEDIFF(CURRENT_DATE, b.due_date) as days_overdue,
    b.fine_amount
FROM borrows b
JOIN students s ON b.student_id = s.id
JOIN books bk ON b.book_id = bk.id
WHERE b.status = 'borrowed' 
AND b.due_date < CURRENT_DATE
AND b.return_date IS NULL;

-- Create stored procedures for common operations
DELIMITER //

CREATE PROCEDURE CalculateOverdueFines()
BEGIN
    UPDATE borrows 
    SET 
        fine_amount = DATEDIFF(CURRENT_DATE, due_date) * 0.50,
        status = 'overdue',
        updated_at = CURRENT_TIMESTAMP
    WHERE status = 'borrowed' 
    AND due_date < CURRENT_DATE 
    AND return_date IS NULL;
END//

CREATE PROCEDURE GetStudentBorrowHistory(IN student_id INT)
BEGIN
    SELECT 
        b.title,
        b.author,
        br.borrow_date,
        br.due_date,
        br.return_date,
        br.status,
        br.fine_amount,
        CASE 
            WHEN br.return_date IS NULL AND br.due_date < CURRENT_DATE THEN DATEDIFF(CURRENT_DATE, br.due_date)
            WHEN br.return_date > br.due_date THEN DATEDIFF(br.return_date, br.due_date)
            ELSE 0 
        END as days_late
    FROM borrows br
    JOIN books b ON br.book_id = b.id
    WHERE br.student_id = student_id
    ORDER BY br.borrow_date DESC;
END//

CREATE PROCEDURE GetLibraryStatistics()
BEGIN
    SELECT 
        (SELECT COUNT(*) FROM students WHERE status = 'active') as total_students,
        (SELECT COUNT(*) FROM books) as total_books,
        (SELECT SUM(available_copies) FROM books) as available_books,
        (SELECT COUNT(*) FROM borrows WHERE status = 'borrowed') as currently_borrowed,
        (SELECT COUNT(*) FROM borrows WHERE status = 'overdue') as overdue_books,
        (SELECT COALESCE(SUM(fine_amount), 0) FROM borrows WHERE fine_amount > 0) as total_fines;
END//

DELIMITER ;

COMMIT;
