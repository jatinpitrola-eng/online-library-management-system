<?php
// =============================================
// User Profile Page
// =============================================
$base_path = '';
$page_title = 'My Profile - Online Library Management System';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'config/database.php';
require_once 'includes/auth.php';
include 'includes/header.php';

$error = '';
$success = '';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_profile') {
    $full_name = trim($_POST['full_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');

    if (empty($full_name)) {
        $error = 'Full name is required.';
    } else {
        try {
            $stmt = $conn->prepare("UPDATE users SET full_name = ?, phone = ? WHERE id = ?");
            $stmt->execute([$full_name, $phone, $_SESSION['user_id']]);
            $_SESSION['full_name'] = $full_name;
            $_SESSION['success'] = 'Profile updated successfully!';
            header('Location: profile.php');
            exit();
        } catch (PDOException $e) {
            $error = 'Failed to update profile.';
        }
    }
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'change_password') {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error = 'All password fields are required.';
    } elseif (strlen($new_password) < 6) {
        $error = 'New password must be at least 6 characters.';
    } elseif ($new_password !== $confirm_password) {
        $error = 'New password and confirm password do not match.';
    } else {
        try {
            $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch();

            if (password_verify($current_password, $user['password'])) {
                $hashed = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->execute([$hashed, $_SESSION['user_id']]);
                $_SESSION['success'] = 'Password changed successfully!';
                header('Location: profile.php');
                exit();
            } else {
                $error = 'Current password is incorrect.';
            }
        } catch (PDOException $e) {
            $error = 'Failed to change password.';
        }
    }
}

// Fetch user details
try {
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
} catch (PDOException $e) {
    die('Error loading profile.');
}

// Get issue stats
try {
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM issued_books WHERE user_id = ? AND status = 'issued'");
    $stmt->execute([$_SESSION['user_id']]);
    $currently_issued = $stmt->fetch()['total'];

    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM issued_books WHERE user_id = ? AND status = 'returned'");
    $stmt->execute([$_SESSION['user_id']]);
    $total_returned = $stmt->fetch()['total'];
} catch (PDOException $e) {
    $currently_issued = 0;
    $total_returned = 0;
}
?>

<!-- Page Header -->
<div class="page-header">
    <div class="container">
        <h4><i class="fas fa-user me-2"></i>My Profile</h4>
        <p>View and update your profile information</p>
    </div>
</div>

<main class="container py-4">
    <!-- Profile Header -->
    <div class="profile-header mb-4">
        <div class="d-flex align-items-center gap-3">
            <div class="profile-avatar">
                <i class="fas fa-user"></i>
            </div>
            <div>
                <h3 class="mb-1"><?php echo e($user['full_name']); ?></h3>
                <p class="mb-1 opacity-75"><i class="fas fa-envelope me-2"></i><?php echo e($user['email']); ?></p>
                <p class="mb-0 opacity-75"><i class="fas fa-tag me-2"></i><?php echo ucfirst($user['role']); ?> Account</p>
            </div>
        </div>
    </div>

    <!-- Stats -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="content-card text-center p-3">
                <h4 class="fw-bold mb-1" style="color: var(--warning);"><?php echo $currently_issued; ?></h4>
                <small class="text-muted">Currently Issued</small>
            </div>
        </div>
        <div class="col-md-4">
            <div class="content-card text-center p-3">
                <h4 class="fw-bold mb-1" style="color: var(--success);"><?php echo $total_returned; ?></h4>
                <small class="text-muted">Books Returned</small>
            </div>
        </div>
        <div class="col-md-4">
            <div class="content-card text-center p-3">
                <h4 class="fw-bold mb-1" style="color: var(--accent);"><?php echo date('d M Y', strtotime($user['created_at'])); ?></h4>
                <small class="text-muted">Member Since</small>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Update Profile -->
        <div class="col-lg-6">
            <div class="form-section">
                <h5 class="fw-bold mb-3"><i class="fas fa-edit me-2"></i>Update Profile</h5>
                <?php if ($error && (isset($_POST['action']) && $_POST['action'] === 'update_profile' || !isset($_POST['action']))): ?>
                    <div class="alert alert-danger"><i class="fas fa-exclamation-circle me-2"></i><?php echo e($error); ?></div>
                <?php endif; ?>
                <form method="POST" action="">
                    <input type="hidden" name="action" value="update_profile">
                    <div class="mb-3">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="full_name" class="form-control" required value="<?php echo e($user['full_name']); ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email (cannot change)</label>
                        <input type="email" class="form-control bg-light" value="<?php echo e($user['email']); ?>" disabled>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Phone Number</label>
                        <input type="text" name="phone" class="form-control" value="<?php echo e($user['phone'] ?? ''); ?>">
                    </div>
                    <button type="submit" class="btn btn-primary px-4"><i class="fas fa-save me-2"></i>Update Profile</button>
                </form>
            </div>
        </div>

        <!-- Change Password -->
        <div class="col-lg-6">
            <div class="form-section">
                <h5 class="fw-bold mb-3"><i class="fas fa-key me-2"></i>Change Password</h5>
                <?php if ($error && isset($_POST['action']) && $_POST['action'] === 'change_password'): ?>
                    <div class="alert alert-danger"><i class="fas fa-exclamation-circle me-2"></i><?php echo e($error); ?></div>
                <?php endif; ?>
                <form method="POST" action="">
                    <input type="hidden" name="action" value="change_password">
                    <div class="mb-3">
                        <label class="form-label">Current Password</label>
                        <input type="password" name="current_password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">New Password</label>
                        <input type="password" name="new_password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Confirm New Password</label>
                        <input type="password" name="confirm_password" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-warning px-4"><i class="fas fa-key me-2"></i>Change Password</button>
                </form>
            </div>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>