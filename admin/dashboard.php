<?php
// =============================================
// Admin Dashboard
// =============================================
$base_path = '../';
$page_title = 'Admin Dashboard - Online Library Management System';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$required_role = 'admin';
require_once '../config/database.php';
require_once '../includes/auth.php';
include '../includes/header.php';

// Dashboard Statistics
try {
    // Total books
    $stmt = $conn->query("SELECT COUNT(*) as total FROM books");
    $total_books = $stmt->fetch()['total'];

    // Available books (sum of available quantities)
    $stmt = $conn->query("SELECT SUM(available_quantity) as total FROM books");
    $available_books = $stmt->fetch()['total'] ?? 0;

    // Currently issued books
    $stmt = $conn->query("SELECT COUNT(*) as total FROM issued_books WHERE status = 'issued'");
    $issued_books = $stmt->fetch()['total'];

    // Total students
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM users WHERE role = 'student'");
    $stmt->execute();
    $total_students = $stmt->fetch()['total'];

    // Overdue books
    $stmt = $conn->query("SELECT COUNT(*) as total FROM issued_books WHERE status = 'issued' AND due_date < CURDATE()");
    $overdue_books = $stmt->fetch()['total'];

    // Recent issued books
    $stmt = $conn->query("SELECT ib.*, b.title as book_title, u.full_name as student_name 
                          FROM issued_books ib 
                          JOIN books b ON ib.book_id = b.id 
                          JOIN users u ON ib.user_id = u.id 
                          ORDER BY ib.created_at DESC LIMIT 5");
    $recent_issued = $stmt->fetchAll();

    // Recent returned books
    $stmt = $conn->query("SELECT ib.*, b.title as book_title, u.full_name as student_name 
                          FROM issued_books ib 
                          JOIN books b ON ib.book_id = b.id 
                          JOIN users u ON ib.user_id = u.id 
                          WHERE ib.status = 'returned' 
                          ORDER BY ib.return_date DESC LIMIT 5");
    $recent_returned = $stmt->fetchAll();

    // Books by category
    $stmt = $conn->query("SELECT category, COUNT(*) as count FROM books GROUP BY category ORDER BY count DESC LIMIT 5");
    $categories = $stmt->fetchAll();

} catch (PDOException $e) {
    die("Error loading dashboard: " . $e->getMessage());
}
?>

<!-- Page Header -->
<div class="page-header">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h4><i class="fas fa-tachometer-alt me-2"></i>Admin Dashboard</h4>
                <p>Welcome back, <?php echo e($_SESSION['full_name']); ?>! Here is your library overview.</p>
            </div>
            <div>
                <a href="<?php echo $base_path; ?>add_book.php" class="btn btn-primary"><i class="fas fa-plus me-2"></i>Add New Book</a>
            </div>
        </div>
    </div>
</div>

<main class="container py-4">
    <!-- Statistics Cards -->
    <div class="row g-4 mb-4">
        <div class="col-lg-3 col-md-6">
            <div class="stat-card total-books">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="stat-number"><?php echo number_format($total_books); ?></div>
                        <div class="stat-label">Total Books</div>
                    </div>
                    <div class="stat-icon"><i class="fas fa-book"></i></div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="stat-card available-books">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="stat-number"><?php echo number_format($available_books); ?></div>
                        <div class="stat-label">Available Copies</div>
                    </div>
                    <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="stat-card issued-books">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="stat-number"><?php echo number_format($issued_books); ?></div>
                        <div class="stat-label">Issued Books</div>
                    </div>
                    <div class="stat-icon"><i class="fas fa-hand-holding"></i></div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="stat-card total-students">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="stat-number"><?php echo number_format($total_students); ?></div>
                        <div class="stat-label">Total Students</div>
                    </div>
                    <div class="stat-icon"><i class="fas fa-users"></i></div>
                </div>
            </div>
        </div>
    </div>

    <?php if ($overdue_books > 0): ?>
    <div class="alert alert-warning d-flex align-items-center mb-4" role="alert">
        <i class="fas fa-exclamation-triangle me-3 fa-lg"></i>
        <div>
            <strong><?php echo $overdue_books; ?> book(s) are overdue!</strong>
            <a href="<?php echo $base_path; ?>admin/dashboard.php" class="alert-link ms-2">View details in issued books table.</a>
        </div>
    </div>
    <?php endif; ?>

    <div class="row g-4">
        <!-- Recent Issued Books -->
        <div class="col-lg-6">
            <div class="content-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-hand-holding me-2"></i>Recent Issued Books</span>
                    <a href="<?php echo $base_path; ?>issue_book.php" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body p-0">
                    <?php if (count($recent_issued) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-custom mb-0">
                            <thead>
                                <tr>
                                    <th>Student</th>
                                    <th>Book</th>
                                    <th>Issue Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_issued as $item): ?>
                                <tr>
                                    <td><?php echo e($item['student_name']); ?></td>
                                    <td><?php echo e($item['book_title']); ?></td>
                                    <td><?php echo date('d M Y', strtotime($item['issue_date'])); ?></td>
                                    <td>
                                        <?php if ($item['status'] === 'issued'): ?>
                                            <?php if (strtotime($item['due_date']) < time()): ?>
                                                <span class="badge bg-danger badge-status">Overdue</span>
                                            <?php else: ?>
                                                <span class="badge bg-warning badge-status">Issued</span>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="badge bg-success badge-status">Returned</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div class="empty-state py-4"><i class="fas fa-inbox"></i><p>No issued books yet.</p></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Recent Returned Books -->
        <div class="col-lg-6">
            <div class="content-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-undo me-2"></i>Recent Returned Books</span>
                </div>
                <div class="card-body p-0">
                    <?php if (count($recent_returned) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-custom mb-0">
                            <thead>
                                <tr>
                                    <th>Student</th>
                                    <th>Book</th>
                                    <th>Return Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_returned as $item): ?>
                                <tr>
                                    <td><?php echo e($item['student_name']); ?></td>
                                    <td><?php echo e($item['book_title']); ?></td>
                                    <td><?php echo date('d M Y', strtotime($item['return_date'])); ?></td>
                                    <td><span class="badge bg-success badge-status">Returned</span></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div class="empty-state py-4"><i class="fas fa-inbox"></i><p>No returned books yet.</p></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Books by Category -->
        <div class="col-lg-6">
            <div class="content-card">
                <div class="card-header"><i class="fas fa-chart-bar me-2"></i>Books by Category</div>
                <div class="card-body">
                    <?php foreach ($categories as $cat): ?>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span><?php echo e($cat['category']); ?></span>
                        <span class="fw-bold" style="color: var(--accent);"><?php echo $cat['count']; ?> books</span>
                    </div>
                    <div class="progress mb-3" style="height: 6px;">
                        <div class="progress-bar" style="width: <?php echo ($cat['count'] / $total_books) * 100; ?>%; background: var(--accent);"></div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="col-lg-6">
            <div class="content-card">
                <div class="card-header"><i class="fas fa-bolt me-2"></i>Quick Actions</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-6">
                            <a href="<?php echo $base_path; ?>add_book.php" class="btn btn-outline-primary w-100 py-3">
                                <i class="fas fa-plus-circle d-block fa-2x mb-2"></i>Add Book
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="<?php echo $base_path; ?>books.php" class="btn btn-outline-success w-100 py-3">
                                <i class="fas fa-book d-block fa-2x mb-2"></i>View Books
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="<?php echo $base_path; ?>issue_book.php" class="btn btn-outline-warning w-100 py-3">
                                <i class="fas fa-hand-holding d-block fa-2x mb-2"></i>Issue Book
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="<?php echo $base_path; ?>students.php" class="btn btn-outline-info w-100 py-3">
                                <i class="fas fa-users d-block fa-2x mb-2"></i>Students
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include '../includes/footer.php'; ?>