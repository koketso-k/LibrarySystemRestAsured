# Library Book Borrowing System

A comprehensive web-based library management system built with PHP, MySQL, HTML, CSS, and JavaScript.

## Features

### Student Features
- User registration and login
- Book search and browsing
- Book borrowing and returning
- View borrowing history
- Personal profile management

### Admin Features
- Add, edit, and delete books
- View all borrowed books
- Generate overdue reports
- Export reports to CSV
- Manage book inventory

### System Features
- Prevents double-borrowing of the same book
- Tracks due dates and calculates overdue status
- Responsive design for mobile and desktop
- Secure authentication system
- Error handling and validation

## Installation

1. **Setup XAMPP**
   - Install XAMPP on your system
   - Start Apache and MySQL services

2. **Database Setup**
   - Create a database named `library_db` in phpMyAdmin
   - Import the `database/library_db.sql` file

3. **Project Setup**
   - Clone or download this project
   - Place the project folder in `xampp/htdocs/`
   - Update database configuration in `includes/config.php` if needed

4. **Access the Application**
   - Open your browser and go to `http://localhost/library-system/`

## Default Login Credentials

### Admin
- Username: `admin`
- Password: `password`

### Student
- Register a new account from the registration page

## File Structure
