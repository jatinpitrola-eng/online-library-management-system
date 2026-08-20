-- =============================================
-- Online Library Management System
-- Database: library_management
-- =============================================

CREATE DATABASE IF NOT EXISTS library_management
DEFAULT CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE library_management;

-- =============================================
-- Table: users
-- =============================================
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20) DEFAULT NULL,
    role ENUM('admin', 'student') NOT NULL DEFAULT 'student',
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- Table: books
-- =============================================
CREATE TABLE books (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    author VARCHAR(150) NOT NULL,
    category VARCHAR(100) NOT NULL,
    isbn VARCHAR(20) DEFAULT NULL,
    publisher VARCHAR(150) DEFAULT NULL,
    publication_year INT DEFAULT NULL,
    total_quantity INT NOT NULL DEFAULT 1,
    available_quantity INT NOT NULL DEFAULT 1,
    description TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- Table: issued_books
-- =============================================
CREATE TABLE issued_books (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    book_id INT NOT NULL,
    issue_date DATE NOT NULL,
    due_date DATE NOT NULL,
    return_date DATE DEFAULT NULL,
    status ENUM('issued', 'returned', 'overdue') NOT NULL DEFAULT 'issued',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- Sample Admin Account
-- Email: admin@library.com
-- Password: admin123
-- =============================================
INSERT INTO users (full_name, email, password, phone, role, status) VALUES
('Library Admin', 'admin@library.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '9876543210', 'admin', 'active');

-- =============================================
-- Sample Student Accounts
-- Password for all: student123
-- =============================================
INSERT INTO users (full_name, email, password, phone, role, status) VALUES
('Raj Patel', 'raj@student.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '9123456780', 'student', 'active'),
('Priya Shah', 'priya@student.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '9123456781', 'student', 'active'),
('Amit Kumar', 'amit@student.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '9123456782', 'student', 'active');

-- =============================================
-- Sample Books
-- =============================================
INSERT INTO books (title, author, category, isbn, publisher, publication_year, total_quantity, available_quantity, description) VALUES
('Introduction to Algorithms', 'Thomas H. Cormen', 'Computer Science', '978-0262033848', 'MIT Press', 2009, 5, 4, 'A comprehensive textbook covering a broad range of algorithms in depth, yet makes their design and analysis accessible to all levels of readers.'),
('Data Structures and Algorithms in Java', 'Robert Lafore', 'Computer Science', '978-0672324536', 'Sams Publishing', 2002, 3, 2, 'A practical guide to data structures and algorithms using Java programming language with clear explanations and examples.'),
('Digital Electronics', 'A.P. Godse', 'Electronics', '978-9350995261', 'Technical Publications', 2015, 4, 3, 'Complete coverage of digital electronics fundamentals including logic gates, combinational and sequential circuits.'),
('Engineering Mathematics', 'B.S. Grewal', 'Mathematics', '978-8174091955', 'Khanna Publishers', 2018, 6, 5, 'Higher Engineering Mathematics for engineering students covering all major topics in a systematic manner.'),
('Object Oriented Programming with C++', 'E. Balagurusamy', 'Computer Science', '978-9333220486', 'McGraw Hill', 2013, 4, 3, 'A comprehensive book on C++ programming covering OOP concepts, classes, inheritance, polymorphism and more.'),
('Database System Concepts', 'Abraham Silberschatz', 'Computer Science', '978-0078022159', 'McGraw Hill', 2019, 3, 2, 'The complete guide to database systems including relational model, SQL, transaction management and data mining.'),
('Computer Networks', 'Andrew S. Tanenbaum', 'Computer Science', '978-0133594140', 'Pearson', 2013, 4, 3, 'A classic textbook covering all aspects of computer networks from physical layer to application layer.'),
('Operating System Concepts', 'Abraham Silberschatz', 'Computer Science', '978-1118063330', 'Wiley', 2018, 3, 2, 'The widely used textbook covering process management, memory management, file systems and security.'),
('Strength of Materials', 'R.K. Rajput', 'Mechanical', '978-8131808148', 'S. Chand', 2015, 5, 4, 'Comprehensive coverage of stress, strain, bending, shear force, torsion and other mechanical engineering topics.'),
('Basic Electrical Engineering', 'V.N. Mittle', 'Electrical', '978-8122403070', 'Tata McGraw Hill', 2010, 3, 2, 'Fundamentals of electrical engineering including DC circuits, AC circuits, transformers and electrical machines.'),
('Software Engineering', 'Roger S. Pressman', 'Computer Science', '978-0078022128', 'McGraw Hill', 2014, 4, 3, 'A practitioner approach to software engineering covering all phases of software development life cycle.'),
('Theory of Computation', 'Michael Sipser', 'Computer Science', '978-1133187790', 'Cengage Learning', 2012, 3, 2, 'Introduction to the theory of computation covering automata, computability and complexity.'),
('Physics for Scientists and Engineers', 'Serway & Jewett', 'Physics', '978-1133947271', 'Cengage Learning', 2013, 4, 3, 'A comprehensive physics textbook covering mechanics, electromagnetism, optics and modern physics.'),
('Chemistry for Engineers', 'Shashi Chawla', 'Chemistry', '978-8179921374', 'Dhanpat Rai', 2016, 3, 2, 'Engineering chemistry covering atomic structure, chemical bonding, thermodynamics and organic chemistry.'),
('Technical Communication', 'Meenakshi Raman', 'Communication', '978-9333220462', 'McGraw Hill', 2015, 3, 3, 'Practical guide to technical communication for engineers covering reports, presentations and documentation.');

-- =============================================
-- Sample Issue Records
-- =============================================
INSERT INTO issued_books (user_id, book_id, issue_date, due_date, return_date, status) VALUES
(2, 1, '2026-07-15', '2026-08-14', '2026-08-10', 'returned'),
(2, 3, '2026-08-01', '2026-08-31', NULL, 'issued'),
(3, 5, '2026-08-05', '2026-09-04', NULL, 'issued'),
(4, 2, '2026-07-20', '2026-08-19', '2026-08-15', 'returned'),
(4, 7, '2026-08-10', '2026-09-09', NULL, 'issued');

-- =============================================
-- Update available quantities based on issued books
-- =============================================
UPDATE books SET available_quantity = total_quantity - (
    SELECT COUNT(*) FROM issued_books 
    WHERE issued_books.book_id = books.id AND status = 'issued'
);