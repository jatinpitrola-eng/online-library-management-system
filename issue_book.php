<?php
// =============================================
// Issue Book Page (Admin Only)
// =============================================
$base_path = '';
$page_title = 'Issue Book - Online Library Management System';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$required_role = 'admin';
require_once 'config/database.php';
require_once 'includes/auth.php';
include 'includes/header.php';

$error = '';

// Handle issue form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = intval($_POST['user_id'] ?? 0);
    $book_id = intval($_POST['book_id'] ?? 0);
    $issue_date = $_POST['issue_date'] ?? date('Y-m-d');
    $due_date = $_POST['due_date'] ?? '';

    if ($user_id <= 0 || $book_id <= 0 || empty($due_date)) {
        $error = 'Please select a student, book, and set a due date.';
    } else {
        try {
            $conn->beginTransaction();

            // Check book availability with row lock
            $stmt = $conn->prepare("SELECT available_quantity, title FROM books WHERE id = ? FOR UPDATE");
            $stmt->execute([$book_id]);
            $book_check = $stmt->fetch();

            if (!$book_check || $book_check['available_quantity'] <= 0) {
                throw new Exception('This book is not available for issue.');
            }

            // Check if student already has this book issued
            $stmt = $conn->prepare("SELECT id FROM issued_books WHERE user_id = ? AND book_id = ? AND status = 'issued'");
            $stmt->execute([$user_id, $book_id]);
            if ($stmt->fetch()) {
                throw new Exception('This student already has this book issued.');
            }

            // Issue the book
            $stmt = $conn->prepare("INSERT INTO issued_books (user_id, book_id, issue_date, due_date, status) VALUES (?, ?, ?, ?, 'issued')");
            $stmt->execute([$user_id, $book_id, $issue_date, $due_date]);

            // Decrease available quantity
            $stmt = $conn->prepare("UPDATE books SET available_quantity = available_quantity - 1 WHERE id = ?");
            $stmt->execute([$book_id]);

            $conn->commit();
            $_SESSION['success'] = 'Book issued successfully!';
            header('Location: issue_book.php');
            exit();

        } catch (Exception $e) {
            $conn->rollBack();
            $error = $e->getMessage();
        }
    }
}

// Fetch all students
try {
    $stmt = $conn->prepare("SELECT id, full_name, email FROM users WHERE role = 'student' AND status = 'active' ORDER BY full_name ASC");
    $stmt->execute();
    $students = $stmt->fetchAll();
} catch (PDOException $e) {
    $students = [];
}

// Fetch all books with available quantity
try {
    $stmt = $conn->query("SELECT id, title, author, available_quantity FROM books WHERE available_quantity > 0 ORDER BY title ASC");
    $available_books = $stmt->fetchAll();
} catch (PDOException $e) {
    $available_books = [];
}

// Fetch all issued books (for table below)
try {
    $stmt = $conn->query("SELECT ib.*, b.title as book_title, b.isbn, u.full_name as student_name, u.email as student_email 
                          FROM issued_books ib 
                          JOIN books b ON ib.book_id = b.id 
                          JOIN users u ON ib.user_id = u.id 
                          ORDER BY ib.issue_date DESC");
    $all_issued = $stmt->fetchAll();
} catch (PDOException $e) {
    $all_issued = [];
}
?>

<!-- Page Header -->
<div class="page-header">
    <div class="container">
        <h4><i class="fas fa-hand-holding me-2"></i>Issue Book</h4>
        <p>Issue a book to a student from the library</p>
    </div>
</div>

<main class="container py-4">
    <!-- Issue Form -->
    <div class="row mb-4">
        <div class="col-lg-8">
            <div class="form-section">
                <h5 class="fw-bold mb-3"><i class="fas fa-clipboard-check me-2"></i>New Issue</h5>
                <?php if ($error): ?>
                    <div class="alert alert-danger"><i class="fas fa-exclamation-circle me-2"></i><?php echo e($error); ?></div>
                <?php endif; ?>
                <form method="POST" action="">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Select Student *</label>
                            <select name="user_id" class="form-select" required>
                                <option value="">-- Choose Student --</option>
                                <?php foreach ($students as $s): ?>
                                    <option value="<?php echo $s['id']; ?>" <?php echo (isset($user_id) && $user_id == $s['id']) ? 'selected' : ''; ?>>
                                        <?php echo e($s['full_name']); ?> (<?php echo e($s['email']); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Select Book *</label>
                            <select name="book_id" class="form-select" required>
                                <option value="">-- Choose Book --</option>
                                <?php foreach ($available_books as $b): ?>
                                    <option value="<?php echo $b['id']; ?>" <?php echo (isset($book_id) && $book_id == $b['id']) ? 'selected' : ''; ?>>
                                        <?php echo e($b['title']); ?> by <?php echo e($b['author']); ?> (<?php echo $b['available_quantity']; ?> avail)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Issue Date *</label>
                            <input type="date" name="issue_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Due Date *</label>
                            <input type="date" name="due_date" class="form-control" value="<?php echo date('Y-m-d', strtotime('+15 days')); ?>" required>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary px-4"><i class="fas fa-hand-holding me-2"></i>Issue Book</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- All Issued Books Table -->
    <div class="content-card">
        <div class="card-header"><i class="fas fa-list me-2"></i>All Issue Records</div>
        <div class="card-body p-0">
            <?php if (count($all_issued) > 0): ?>
            <div class="table-responsive">
                <table class="table table-custom mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Student</th>
                            <th>Book</th>
                            <th>Issue Date</th>
                            <th>Due Date</th>
                            <th>Return Date</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i = 1; foreach ($all_issued as $item): ?>
                        <tr>
                            <td><?php echo $i++; ?></td>
                            <td><?php echo e($item['student_name']); ?></td>
                            <td><?php echo e($item['book_title']); ?></td>
                            <td><?php echo date('d M Y', strtotime($item['issue_date'])); ?></td>
                            <td><?php echo date('d M Y', strtotime($item['due_date'])); ?></td>
                            <td><?php echo $item['return_date'] ? date('d M Y', strtotime($item['return_date'])) : '-'; ?></td>
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
                            <td>
                                <?php if ($item['status'] === 'issued'): ?>
                                    <a href="return_book.php?id=<?php echo $item['id']; ?>" class="btn btn-sm btn-success btn-action" onclick="return confirmReturn('<?php echo addslashes(e($item['book_title'])); ?>')">
                                        <i class="fas fa-undo me-1"></i>Return
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="empty-state"><i class="fas fa-inbox"></i><p>No issue records yet.</p></div>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>