<?php
// =============================================
// Students Management Page (Admin Only)
// =============================================
$base_path = '';
$page_title = 'Students - Online Library Management System';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$required_role = 'admin';
require_once 'config/database.php';
require_once 'includes/auth.php';
include 'includes/header.php';

// Handle status toggle
if (isset($_GET['toggle_status'])) {
    $user_id = intval($_GET['toggle_status']);
    try {
        $stmt = $conn->prepare("UPDATE users SET status = CASE WHEN status = 'active' THEN 'inactive' ELSE 'active' END WHERE id = ? AND role = 'student'");
        $stmt->execute([$user_id]);
        $_SESSION['success'] = 'Student status updated.';
        header('Location: students.php');
        exit();
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Failed to update status.';
        header('Location: students.php');
        exit();
    }
}

// Search
$search = trim($_GET['search'] ?? '');

// Fetch students with their issued book count
try {
    if (!empty($search)) {
        $stmt = $conn->prepare("SELECT u.*, (SELECT COUNT(*) FROM issued_books ib WHERE ib.user_id = u.id AND ib.status = 'issued') as currently_issued 
                              FROM users u 
                              WHERE u.role = 'student' AND (u.full_name LIKE ? OR u.email LIKE ?) 
                              ORDER BY u.created_at DESC");
        $search_param = "%$search%";
        $stmt->execute([$search_param, $search_param]);
    } else {
        $stmt = $conn->query("SELECT u.*, (SELECT COUNT(*) FROM issued_books ib WHERE ib.user_id = u.id AND ib.status = 'issued') as currently_issued 
                              FROM users u 
                              WHERE u.role = 'student' 
                              ORDER BY u.created_at DESC");
    }
    $students = $stmt->fetchAll();
} catch (PDOException $e) {
    $students = [];
}
?>

<!-- Page Header -->
<div class="page-header">
    <div class="container">
        <h4><i class="fas fa-users me-2"></i>Manage Students</h4>
        <p>View and manage all registered students</p>
    </div>
</div>

<main class="container py-4">
    <!-- Search -->
    <div class="row mb-4">
        <div class="col-md-6">
            <form method="GET" action="">
                <div class="input-group">
                    <input type="text" name="search" class="form-control search-input" placeholder="Search students by name or email..." value="<?php echo e($search); ?>">
                    <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i></button>
                    <?php if (!empty($search)): ?>
                        <a href="students.php" class="btn btn-outline-secondary"><i class="fas fa-times"></i></a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- Students Table -->
    <div class="content-card">
        <div class="card-body p-0">
            <?php if (count($students) > 0): ?>
            <div class="table-responsive">
                <table class="table table-custom mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Books Issued</th>
                            <th>Status</th>
                            <th>Registered</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i = 1; foreach ($students as $s): ?>
                        <tr>
                            <td><?php echo $i++; ?></td>
                            <td class="fw-semibold"><?php echo e($s['full_name']); ?></td>
                            <td><?php echo e($s['email']); ?></td>
                            <td><?php echo $s['phone'] ? e($s['phone']) : '-'; ?></td>
                            <td>
                                <?php if ($s['currently_issued'] > 0): ?>
                                    <span class="badge bg-warning badge-status"><?php echo $s['currently_issued']; ?> issued</span>
                                <?php else: ?>
                                    <span class="text-muted">0</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($s['status'] === 'active'): ?>
                                    <span class="badge bg-success badge-status">Active</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary badge-status">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo date('d M Y', strtotime($s['created_at'])); ?></td>
                            <td>
                                <a href="students.php?toggle_status=<?php echo $s['id']; ?>" 
                                   class="btn btn-sm <?php echo $s['status'] === 'active' ? 'btn-warning' : 'btn-success'; ?> btn-action"
                                   onclick="return confirm('Toggle this student\'s status?')">
                                    <i class="fas fa-<?php echo $s['status'] === 'active' ? 'ban' : 'check'; ?> me-1"></i>
                                    <?php echo $s['status'] === 'active' ? 'Deactivate' : 'Activate'; ?>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-users"></i>
                <h5>No Students Found</h5>
                <p class="text-muted"><?php echo !empty($search) ? 'Try adjusting your search.' : 'No students have registered yet.'; ?></p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>