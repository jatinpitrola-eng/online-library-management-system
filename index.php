<?php
// =============================================
// Home Page - Landing Page
// =============================================
$base_path = '';
$page_title = 'Home - Online Library Management System';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'config/database.php';
include 'includes/header.php';

// Get total books count
$total_books = 0;
$available_books = 0;
try {
    $stmt = $conn->prepare("SELECT COUNT(*) as total, SUM(available_quantity) as available FROM books");
    $stmt->execute();
    $book_stats = $stmt->fetch();
    $total_books = $book_stats['total'];
    $available_books = $book_stats['available'] ?? 0;
} catch (PDOException $e) {
    // Silent fail for landing page stats
}

// Get some recent books for display
$recent_books = [];
try {
    $stmt = $conn->prepare("SELECT * FROM books ORDER BY created_at DESC LIMIT 6");
    $stmt->execute();
    $recent_books = $stmt->fetchAll();
} catch (PDOException $e) {
    // Silent fail
}
?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="container position-relative">
        <h1><i class="fas fa-book-open me-3"></i>Online Library Management System</h1>
        <p>A complete digital solution for managing library books, student accounts, and issue/return operations. Built for modern college libraries.</p>
        <div class="d-flex gap-3 justify-content-center flex-wrap">
            <?php if (isset($_SESSION['user_id'])): ?>
                <?php if ($_SESSION['role'] === 'admin'): ?>
                    <a href="<?php echo $base_path; ?>admin/dashboard.php" class="btn btn-light btn-lg px-4 fw-semibold">
                        <i class="fas fa-tachometer-alt me-2"></i>Go to Dashboard
                    </a>
                <?php else: ?>
                    <a href="<?php echo $base_path; ?>books.php" class="btn btn-light btn-lg px-4 fw-semibold">
                        <i class="fas fa-book me-2"></i>Browse Books
                    </a>
                <?php endif; ?>
            <?php else: ?>
                <a href="<?php echo $base_path; ?>login.php" class="btn btn-light btn-lg px-4 fw-semibold">
                    <i class="fas fa-sign-in-alt me-2"></i>Login
                </a>
                <a href="<?php echo $base_path; ?>register.php" class="btn btn-outline-light btn-lg px-4 fw-semibold">
                    <i class="fas fa-user-plus me-2"></i>Register
                </a>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="py-5">
    <div class="container">
        <h2 class="section-title text-center">System Features</h2>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="content-card text-center p-4">
                    <div class="mb-3"><i class="fas fa-book fa-3x" style="color: var(--accent);"></i></div>
                    <h5 class="fw-bold">Book Management</h5>
                    <p class="text-muted">Admins can add, edit, delete, and search books. Complete book catalog with all details including ISBN, publisher, and availability status.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="content-card text-center p-4">
                    <div class="mb-3"><i class="fas fa-exchange-alt fa-3x" style="color: var(--success);"></i></div>
                    <h5 class="fw-bold">Issue & Return</h5>
                    <p class="text-muted">Efficient book issue and return system with due date tracking, automatic availability updates, and complete transaction history.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="content-card text-center p-4">
                    <div class="mb-3"><i class="fas fa-users fa-3x" style="color: #9b59b6;"></i></div>
                    <h5 class="fw-bold">User Management</h5>
                    <p class="text-muted">Secure registration and login for students. Admin can manage all users and monitor their library activity and issued books.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="content-card text-center p-4">
                    <div class="mb-3"><i class="fas fa-search fa-3x" style="color: var(--warning);"></i></div>
                    <h5 class="fw-bold">Smart Search</h5>
                    <p class="text-muted">Search books by title, author, or category. Quick and efficient search to find the exact book you need from the library catalog.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="content-card text-center p-4">
                    <div class="mb-3"><i class="fas fa-chart-bar fa-3x" style="color: var(--danger);"></i></div>
                    <h5 class="fw-bold">Dashboard Analytics</h5>
                    <p class="text-muted">Admin dashboard with real-time statistics showing total books, issued books, available books, and recent activity overview.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="content-card text-center p-4">
                    <div class="mb-3"><i class="fas fa-shield-alt fa-3x" style="color: var(--primary);"></i></div>
                    <h5 class="fw-bold">Secure System</h5>
                    <p class="text-muted">Password hashing, prepared SQL statements, session-based authentication, and role-based access control for complete security.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Stats Section -->
<section class="py-4 bg-white">
    <div class="container">
        <div class="row g-4 text-center">
            <div class="col-md-4">
                <h2 class="fw-bold" style="color: var(--accent);"><?php echo number_format($total_books); ?></h2>
                <p class="text-muted fw-semibold">Total Books</p>
            </div>
            <div class="col-md-4">
                <h2 class="fw-bold" style="color: var(--success);"><?php echo number_format($available_books); ?></h2>
                <p class="text-muted fw-semibold">Available Copies</p>
            </div>
            <div class="col-md-4">
                <h2 class="fw-bold" style="color: #9b59b6;">15+</h2>
                <p class="text-muted fw-semibold">Categories</p>
            </div>
        </div>
    </div>
</section>

<!-- Recent Books Section -->
<?php if (!empty($recent_books)): ?>
<section class="py-5">
    <div class="container">
        <h2 class="section-title text-center">Recently Added Books</h2>
        <div class="row g-4">
            <?php foreach ($recent_books as $book): ?>
            <div class="col-md-4 col-sm-6">
                <div class="book-card">
                    <div class="book-cover">
                        <i class="fas fa-book"></i>
                    </div>
                    <div class="book-info">
                        <span class="book-category"><?php echo e($book['category']); ?></span>
                        <h5 class="book-title"><?php echo e($book['title']); ?></h5>
                        <p class="book-author"><i class="fas fa-pen-fancy me-1"></i><?php echo e($book['author']); ?></p>
                        <div class="book-availability">
                            <?php if ($book['available_quantity'] > 0): ?>
                                <span class="available"><i class="fas fa-check-circle me-1"></i>Available (<?php echo $book['available_quantity']; ?>)</span>
                            <?php else: ?>
                                <span class="unavailable"><i class="fas fa-times-circle me-1"></i>Not Available</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-4">
            <a href="<?php echo $base_path; ?>books.php" class="btn btn-primary btn-lg px-4">
                <i class="fas fa-arrow-right me-2"></i>View All Books
            </a>
        </div>
    </div>
</section>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
