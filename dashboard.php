<?php
// =============================================
// Student Dashboard
// =============================================
$base_path = '';
$page_title = 'Dashboard - Online Library Management System';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'config/database.php';
require_once 'includes/auth.php';
include 'includes/header.php';

try {
    // Currently issued books for this student
    $stmt = $conn->prepare("SELECT ib.*, b.title as book_title, b.author as book_author, b.category, b.isbn 
                          FROM issued_books ib 
                          JOIN books b ON ib.book_id = b.id 
                          WHERE ib.user_id = ? AND ib.status = 'issued' 
                          ORDER BY ib.due_date ASC");
    $stmt->execute([$_SESSION['user_id']]);
    $my_issued = $stmt->fetchAll();

    // Return history
    $stmt = $conn->prepare("SELECT ib.*, b.title as book_title 
                          FROM issued_books ib 
                          JOIN books b ON ib.book_id = b.id 
                          WHERE ib.user_id = ? AND ib.status = 'returned' 
                          ORDER BY ib.return_date DESC LIMIT 5");
    $stmt->execute([$_SESSION['user_id']]);
    $my_returned = $stmt->fetchAll();

    // Total books issued (all time)
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM issued_books WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $total_issued_all = $stmt->fetch()['total'];

    // Total available books in library
    $stmt = $conn->query("SELECT COUNT(*) as total FROM books WHERE available_quantity > 0");
    $available_books_count = $stmt->fetch()['total'];

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>

<!-- Page Header -->
<div class="page-header">
    <div class="container">
        <h4><i class="fas fa-tachometer-alt me-2"></i>My Dashboard</h4>
        <p>Welcome, <?php echo e($_SESSION['full_name']); ?>! Here is your library activity overview.</p>
    </div>
</div>

<main class="container py-4">
    <!-- Stats -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="stat-card issued-books">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="stat-number"><?php echo count($my_issued); ?></div>
                        <div class="stat-label">Currently Issued</div>
                    </div>
                    <div class="stat-icon"><i class="fas fa-hand-holding"></i></div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card available-books">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="stat-number"><?php echo number_format($available_books_count); ?></div>
                        <div class="stat-label">Available Books</div>
                    </div>
                    <div class="stat-icon"><i class="fas fa-book"></i></div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card total-students">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="stat-number"><?php echo $total_issued_all; ?></div>
                        <div class="stat-label">Total Issued (All Time)</div>
                    </div>
                    <div class="stat-icon"><i class="fas fa-history"></i></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Currently Issued Books -->
        <div class="col-lg-7">
            <div class="content-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-book-reader me-2"></i>My Issued Books</span>
                    <a href="my_books.php" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body p-0">
                    <?php if (count($my_issued) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-custom mb-0">
                            <thead>
                                <tr>
                                    <th>Book</th>
                                    <th>Due Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($my_issued as $item): ?>
                                <tr>
                                    <td>
                                        <a href="book_details.php?id=<?php echo $item['book_id']; ?>" class="text-decoration-none fw-semibold"><?php echo e($item['book_title']); ?></a>
                                        <br><small class="text-muted"><?php echo e($item['book_author']); ?></small>
                                    </td>
                                    <td>
                                        <?php echo date('d M Y', strtotime($item['due_date'])); ?>
                                        <?php if (strtotime($item['due_date']) < time()): ?>
                                            <br><span class="text-danger" style="font-size: 0.78rem;"><i class="fas fa-exclamation-triangle"></i> Overdue</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (strtotime($item['due_date']) < time()): ?>
                                            <span class="badge bg-danger badge-status">Overdue</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning badge-status">Issued</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div class="empty-state py-4">
                        <i class="fas fa-book-open"></i>
                        <p class="mb-0">You have no books issued currently.</p>
                        <a href="books.php" class="btn btn-sm btn-primary mt-2">Browse Books</a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Return History -->
        <div class="col-lg-5">
            <div class="content-card">
                <div class="card-header"><i class="fas fa-undo me-2"></i>Recent Returns</div>
                <div class="card-body p-0">
                    <?php if (count($my_returned) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-custom mb-0">
                            <thead>
                                <tr>
                                    <th>Book</th>
                                    <th>Returned</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($my_returned as $item): ?>
                                <tr>
                                    <td class="fw-semibold"><?php echo e($item['book_title']); ?></td>
                                    <td><?php echo date('d M Y', strtotime($item['return_date'])); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div class="empty-state py-4">
                        <i class="fas fa-history"></i>
                        <p class="mb-0">No return history yet.</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
