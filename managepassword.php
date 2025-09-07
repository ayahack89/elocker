<?php
session_start();
include "server/db_config.php";

// Define encryption constants (must be the same as in storenewpassword.php)
define('ENCRYPTION_KEY', 'Your-Super-Secret-32-Character-Key-Here');
define('CIPHER_ALGO', 'aes-256-cbc');

// Security Check: Redirect if not logged in
if (!isset($_SESSION['username'])) {
    header('Location: index.php');
    exit();
}

$username = $_SESSION['username'];
$search_term = isset($_POST['search']) ? trim($_POST['search']) : '';
$passwords = [];
$total_pages = 0;
$current_page = 1;

// --- Database Logic ---
if (!empty($search_term)) {
    // --- Search Query ---
    $sql = "SELECT * FROM `storage` WHERE username = ? AND (email LIKE ? OR links LIKE ?)";
    $stmt = mysqli_prepare($conn, $sql);
    $search_like = "%" . $search_term . "%";
    mysqli_stmt_bind_param($stmt, "sss", $username, $search_like, $search_like);
} else {
    // --- Pagination Logic ---
    $limit = 10;
    $current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $offset = ($current_page - 1) * $limit;

    // Get total records for pagination
    $count_sql = "SELECT COUNT(*) as total FROM `storage` WHERE username = ?";
    $count_stmt = mysqli_prepare($conn, $count_sql);
    mysqli_stmt_bind_param($count_stmt, "s", $username);
    mysqli_stmt_execute($count_stmt);
    $count_result = mysqli_stmt_get_result($count_stmt);
    $total_records = mysqli_fetch_assoc($count_result)['total'];
    $total_pages = ceil($total_records / $limit);

    // --- Paginated Fetch Query ---
    $sql = "SELECT * FROM `storage` WHERE username = ? LIMIT ? OFFSET ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "sii", $username, $limit, $offset);
}

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        // --- Decrypt Password ---
        $encrypted_data = base64_decode($row['password']);
        $iv_length = openssl_cipher_iv_length(CIPHER_ALGO);
        $iv = substr($encrypted_data, 0, $iv_length);
        $encrypted_password = substr($encrypted_data, $iv_length);
        $decrypted_password = openssl_decrypt($encrypted_password, CIPHER_ALGO, ENCRYPTION_KEY, 0, $iv);
        
        $row['decrypted_password'] = $decrypted_password ?: 'Decryption Failed!';
        $passwords[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include "include/cdn.php"; ?>
    <link rel="stylesheet" href="css/style.css">
    <title>Elocker - Manage Passwords</title>
</head>
<body class="background text-light">

    <?php include "include/navbar.php"; ?>

    <main class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="login-header mb-4">
                    <h2>Manage Passwords</h2>
                    <p>View, search, and manage your saved credentials.</p>
                </div>

                <div class="control-bar">
                    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="search-form" method="post">
                        <i class="ri-search-line search-icon"></i>
                        <input class="form-control" type="search" name="search" placeholder="Search by email or site..." value="<?php echo htmlspecialchars($search_term); ?>">
                    </form>
                    <a href="storenewpassword.php" class="btn btn-primary add-new-btn">
                        <i class="ri-add-line"></i> Add New
                    </a>
                </div>

                <div class="custom-table">
                    <div class="custom-table-header">
                        <div class="header-cell">Account / Email</div>
                        <div class="header-cell">Website</div>
                        <div class="header-cell">Password</div>
                        <div class="header-cell text-end">Actions</div>
                    </div>

                    <div class="custom-table-body">
                        <?php if (empty($passwords)): ?>
                            <div class="no-data-cell">
                                <i class="ri-information-line"></i> 
                                <?php echo !empty($search_term) ? 'No results found for your search.' : 'You haven\'t stored any passwords yet.'; ?>
                            </div>
                        <?php else: ?>
                            <?php foreach ($passwords as $row): ?>
                            <div class="custom-table-row">
                                <div class="table-cell truncate-cell" data-label="Account">
                                    <?php echo htmlspecialchars($row['email']); ?>
                                </div>
                                <div class="table-cell truncate-cell" data-label="Website">
                                    <a href="<?php echo htmlspecialchars($row['links']); ?>" target="_blank" rel="noopener noreferrer">
                                        <?php echo htmlspecialchars($row['links']); ?>
                                    </a>
                                </div>
                                <div class="table-cell" data-label="Password">
                                    <div class="password-cell">
                                        <input type="password" class="password-field" value="••••••••" readonly>
                                        <div class="password-actions">
                                            <i class="ri-eye-line toggle-password" title="Show/Hide Password"></i>
                                            <i class="ri-clipboard-line copy-password" title="Copy Password" data-password="<?php echo htmlspecialchars($row['decrypted_password']); ?>"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="table-cell actions-cell" data-label="Actions">
                                    <a href="update.php?id=<?php echo $row['id']; ?>" class="action-icon" title="Edit"><i class="ri-edit-box-line"></i></a>
                                    <a href="delete.php?delete=<?php echo $row['id']; ?>" class="action-icon action-icon-delete" title="Delete" onclick="return confirm('Are you sure you want to delete this password?');"><i class="ri-delete-bin-line"></i></a>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if (empty($search_term) && $total_pages > 1): ?>
                <nav class="pagination-container">
                    <ul class="pagination">
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?php echo ($i == $current_page) ? 'active' : ''; ?>">
                            <a class="page-link" href="managepassword.php?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                        </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.toggle-password').forEach(icon => {
            icon.addEventListener('click', function () {
                const passwordField = this.closest('.password-cell').querySelector('.password-field');
                const copyIcon = this.closest('.password-cell').querySelector('.copy-password');
                const isPassword = passwordField.type === 'password';
                
                passwordField.type = isPassword ? 'text' : 'password';
                passwordField.value = isPassword ? copyIcon.dataset.password : '••••••••';
                this.classList.toggle('ri-eye-line');
                this.classList.toggle('ri-eye-off-line');
            });
        });

        document.querySelectorAll('.copy-password').forEach(icon => {
            icon.addEventListener('click', function () {
                const passwordToCopy = this.dataset.password;
                navigator.clipboard.writeText(passwordToCopy).then(() => {
                    const originalIcon = this.className;
                    this.className = 'ri-check-line';
                    this.style.color = 'var(--success-color)';
                    
                    setTimeout(() => {
                        this.className = originalIcon;
                        this.style.color = ''; 
                    }, 1500);
                }).catch(err => {
                    console.error('Failed to copy password: ', err);
                });
            });
        });
    });
    </script>
</body>
</html>