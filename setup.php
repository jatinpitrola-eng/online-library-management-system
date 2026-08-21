<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once 'config/database.php';

if (!$db_is_sqlite) {
    die('This setup is for SQLite only. For MySQL, import database/library_management.sql via phpMyAdmin.');
}

$message = '';
try {
    $stmt = $conn->query("SELECT name FROM sqlite_master WHERE type='table' AND name='users'");
    if ($stmt->fetch()) {
        $message = 'Database already set up! <a href="index.php">Go to Home</a>';
    } else {
        $conn->exec("CREATE TABLE users (id INTEGER PRIMARY KEY AUTOINCREMENT, full_name TEXT NOT NULL, email TEXT NOT NULL UNIQUE, password TEXT NOT NULL, phone TEXT DEFAULT '', role TEXT NOT NULL DEFAULT 'student', status TEXT NOT NULL DEFAULT 'active', created_at TEXT DEFAULT (datetime('now')), updated_at TEXT DEFAULT (datetime('now')))");
        $conn->exec("CREATE TABLE books (id INTEGER PRIMARY KEY AUTOINCREMENT, title TEXT NOT NULL, author TEXT NOT NULL, category TEXT NOT NULL, isbn TEXT DEFAULT '', publisher TEXT DEFAULT '', publication_year INTEGER DEFAULT 0, total_quantity INTEGER NOT NULL DEFAULT 1, available_quantity INTEGER NOT NULL DEFAULT 1, description TEXT DEFAULT '', created_at TEXT DEFAULT (datetime('now')), updated_at TEXT DEFAULT (datetime('now')))");
        $conn->exec("CREATE TABLE issued_books (id INTEGER PRIMARY KEY AUTOINCREMENT, user_id INTEGER NOT NULL, book_id INTEGER NOT NULL, issue_date TEXT NOT NULL, due_date TEXT NOT NULL, return_date TEXT DEFAULT NULL, status TEXT NOT NULL DEFAULT 'issued', created_at TEXT DEFAULT (datetime('now')), updated_at TEXT DEFAULT (datetime('now')), FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE, FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE)");

        $admin_pw = password_hash('admin123', PASSWORD_DEFAULT);
        $s = $conn->prepare("INSERT INTO users (full_name, email, password, phone, role, status) VALUES (?, ?, ?, ?, 'admin', 'active')");
        $s->execute(['Library Admin', 'admin@library.com', $admin_pw, '9876543210']);

        $student_pw = password_hash('student123', PASSWORD_DEFAULT);
        $s = $conn->prepare("INSERT INTO users (full_name, email, password, phone, role, status) VALUES (?, ?, ?, ?, 'student', 'active')");
        foreach ([['Raj Patel','raj@student.com','9123456780'],['Priya Shah','priya@student.com','9123456781'],['Amit Kumar','amit@student.com','9123456782']] as $st) $s->execute($st);

        $books = [
            ['Introduction to Algorithms','Thomas H. Cormen','Computer Science','978-0262033848','MIT Press',2009,5,4,'A comprehensive textbook covering a broad range of algorithms in depth.'],
            ['Data Structures and Algorithms in Java','Robert Lafore','Computer Science','978-0672324536','Sams Publishing',2002,3,2,'A practical guide to data structures and algorithms using Java.'],
            ['Digital Electronics','A.P. Godse','Electronics','978-9350995261','Technical Publications',2015,4,3,'Complete coverage of digital electronics fundamentals.'],
            ['Engineering Mathematics','B.S. Grewal','Mathematics','978-8174091955','Khanna Publishers',2018,6,5,'Higher Engineering Mathematics for engineering students.'],
            ['OOP with C++','E. Balagurusamy','Computer Science','978-9333220486','McGraw Hill',2013,4,3,'A comprehensive book on C++ programming.'],
            ['Database System Concepts','Abraham Silberschatz','Computer Science','978-0078022159','McGraw Hill',2019,3,2,'Complete guide to database systems.'],
            ['Computer Networks','Andrew S. Tanenbaum','Computer Science','978-0133594140','Pearson',2013,4,3,'A classic textbook on computer networks.'],
            ['Operating System Concepts','Abraham Silberschatz','Computer Science','978-1118063330','Wiley',2018,3,2,'Widely used OS textbook.'],
            ['Strength of Materials','R.K. Rajput','Mechanical','978-8131808148','S. Chand',2015,5,4,'Mechanical engineering topics.'],
            ['Basic Electrical Engineering','V.N. Mittle','Electrical','978-8122403070','Tata McGraw Hill',2010,3,2,'Fundamentals of electrical engineering.'],
            ['Software Engineering','Roger S. Pressman','Computer Science','978-0078022128','McGraw Hill',2014,4,3,'Software engineering lifecycle.'],
            ['Theory of Computation','Michael Sipser','Computer Science','978-1133187790','Cengage Learning',2012,3,2,'Theory of computation.'],
            ['Physics for Scientists','Serway & Jewett','Physics','978-1133947271','Cengage Learning',2013,4,3,'Comprehensive physics textbook.'],
            ['Chemistry for Engineers','Shashi Chawla','Chemistry','978-8179921374','Dhanpat Rai',2016,3,2,'Engineering chemistry.'],
            ['Technical Communication','Meenakshi Raman','Communication','978-9333220462','McGraw Hill',2015,3,3,'Technical communication guide.'],
        ];
        $s = $conn->prepare("INSERT INTO books (title,author,category,isbn,publisher,publication_year,total_quantity,available_quantity,description) VALUES (?,?,?,?,?,?,?,?,?)");
        foreach ($books as $b) $s->execute($b);

        $s = $conn->prepare("INSERT INTO issued_books (user_id,book_id,issue_date,due_date,return_date,status) VALUES (?,?,?,?,?,?)");
        $s->execute([2,1,'2026-07-15','2026-08-14','2026-08-10','returned']);
        $s->execute([2,3,'2026-08-01','2026-08-31',NULL,'issued']);
        $s->execute([3,5,'2026-08-05','2026-09-04',NULL,'issued']);
        $s->execute([4,2,'2026-07-20','2026-08-19','2026-08-15','returned']);
        $s->execute([4,7,'2026-08-10','2026-09-09',NULL,'issued']);

        $message = 'Database setup complete! <a href="login.php">Go to Login</a>';
    }
} catch (Exception $e) {
    $message = 'Setup Error: ' . $e->getMessage();
}
?><!DOCTYPE html><html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0"><title>Setup</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<style>body{background:linear-gradient(135deg,#2c3e50,#3498db);min-height:100vh;display:flex;align-items:center;justify-content:center}.box{background:#fff;border-radius:16px;padding:3rem;max-width:500px;text-align:center;box-shadow:0 20px 50px rgba(0,0,0,.2)}.box i{font-size:4rem;color:#3498db;margin-bottom:1rem}.box h2{font-weight:700;color:#2c3e50}</style></head><body><div class="box"><i class="fas fa-database"></i><h2>Database Setup</h2><p style="color:#666"><?php echo $message; ?></p><a href="index.php" class="btn btn-primary btn-lg mt-3"><i class="fas fa-home me-2"></i>Go Home</a></div></body></html>
