<?php
session_start();
include "../private/db_config.php";
include "../private/config.php";
include "../private/encryption.php";

// Security Check: Redirect if not logged in
if (!isset($_SESSION['username'])) {
    header('Location: ../index');
    exit();
}

$username = $_SESSION['username'];
$user_id = $_SESSION['id'];
$search_term = isset($_POST['search']) ? trim($_POST['search']) : '';
$category_filter = isset($_POST['category_filter']) ? trim($_POST['category_filter']) : '';
$passwords = [];
$total_pages = 0;
$current_page = 1;

// Define categories (same as in storenewpassword.php)
$categories = [
    'Emails' => 'Email Accounts',
    'Social Media' => 'Social Media',
    'Banking' => 'Banking & Finance',
    'E-commerce' => 'E-commerce & Shopping',
    'Work' => 'Work & Business',
    'Entertainment' => 'Entertainment',
    'Utilities' => 'Utilities & Services',
    'Education' => 'Education',
    'Gaming' => 'Gaming',
    'Development' => 'Development',
    'Others' => 'Others'
];

try {
    // Initialize encryption
    $encryption = new Encryption();

    // --- Database Logic ---
    if (!empty($search_term) || !empty($category_filter)) {
        // --- Search/Filter Query ---
        $sql = "SELECT * FROM `storage` WHERE user_id = ?";
        $params = ["i", $user_id];
        
        if (!empty($category_filter)) {
            $sql .= " AND category = ?";
            $params[0] .= "s";
            $params[] = $category_filter;
        }
        
        $stmt = mysqli_prepare($conn, $sql);
        if (!empty($category_filter)) {
            mysqli_stmt_bind_param($stmt, ...$params);
        } else {
            mysqli_stmt_bind_param($stmt, "i", $user_id);
        }
    } else {
        // --- Pagination Logic ---
        $limit = 10;
        $current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $offset = ($current_page - 1) * $limit;

        // Get total records for pagination
        $count_sql = "SELECT COUNT(*) as total FROM `storage` WHERE user_id = ?";
        $count_stmt = mysqli_prepare($conn, $count_sql);
        mysqli_stmt_bind_param($count_stmt, "i", $user_id);
        mysqli_stmt_execute($count_stmt);
        $count_result = mysqli_stmt_get_result($count_stmt);
        $total_records = mysqli_fetch_assoc($count_result)['total'];
        $total_pages = ceil($total_records / $limit);

        // --- Paginated Fetch Query ---
        $sql = "SELECT * FROM `storage` WHERE user_id = ? ORDER BY id DESC LIMIT ? OFFSET ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "iii", $user_id, $limit, $offset);
    }

    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            try {
                // --- Decrypt All Fields (except category) ---
                $decrypted_username = $encryption->decrypt($row['username']);
                $decrypted_email = $encryption->decrypt($row['email']);
                $decrypted_links = $encryption->decrypt($row['links']);
                $decrypted_password = $encryption->decrypt($row['password']);
                $decrypted_passkeys = $encryption->decrypt($row['passkeys']);
                $decrypted_notes = $encryption->decrypt($row['notes']);

                // Replace encrypted data with decrypted data
                $row['username'] = $decrypted_username ?: 'No username';
                $row['email'] = $decrypted_email ?: 'Empty field';
                $row['links'] = $decrypted_links ?: '';
                $row['decrypted_password'] = $decrypted_password ?: 'Decryption Failed!';
                $row['passkeys'] = $decrypted_passkeys ?: '';
                $row['notes'] = $decrypted_notes ?: '';

                // Format lastupdate timestamp
                if (!empty($row['lastupdate'])) {
                    $timestamp = strtotime($row['lastupdate']);
                    $row['formatted_lastupdate'] = date('M j, Y g:i A', $timestamp);
                    $row['time_ago'] = getTimeAgo($row['lastupdate']);
                } else {
                    $row['formatted_lastupdate'] = 'Never';
                    $row['time_ago'] = 'Never updated';
                }

                // If searching, filter after decryption
                if (!empty($search_term)) {
                    $search_lower = strtolower($search_term);
                    if (
                        strpos(strtolower($row['email']), $search_lower) !== false ||
                        strpos(strtolower($row['links']), $search_lower) !== false ||
                        strpos(strtolower($row['passkeys']), $search_lower) !== false ||
                        strpos(strtolower($row['notes']), $search_lower) !== false
                    ) {
                        $passwords[] = $row;
                    }
                } else {
                    $passwords[] = $row;
                }
            } catch (Exception $e) {
                // Log decryption error but continue processing other rows
                error_log("Decryption error for record ID {$row['id']}: " . $e->getMessage());
                continue;
            }
        }
    }
} catch (Exception $e) {
    $message = "System error: " . $e->getMessage();
    $message_type = 'alert-danger';
}

// Function to calculate time ago
function getTimeAgo($timestamp)
{
    $current_time = time();
    $timestamp = strtotime($timestamp);
    $time_difference = $current_time - $timestamp;

    if ($time_difference < 1) {
        return 'Just now';
    }

    $condition = array(
        12 * 30 * 24 * 60 * 60 => 'year',
        30 * 24 * 60 * 60 => 'month',
        24 * 60 * 60 => 'day',
        60 * 60 => 'hour',
        60 => 'minute',
        1 => 'second'
    );

    foreach ($condition as $secs => $str) {
        $d = $time_difference / $secs;
        if ($d >= 1) {
            $t = round($d);
            return $t . ' ' . $str . ($t > 1 ? 's' : '') . ' ago';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include "../include/cdn.php"; ?>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .page-header {
            margin-bottom: 1.5rem;
        }

        .text-muted {
            color: var(--text-muted) !important;
        }

        /* --- Updated Control Bar --- */
        .control-bar {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .search-form {
            flex-grow: 1;
            position: relative;
        }

        .search-icon {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            font-size: 1.1rem;
            pointer-events: none;
        }

        .search-input {
            width: 100%;
            background-color: #27272a;
            border: 1px solid #3f3f46;
            border-radius: 9999px;
            padding: 12px 50px 12px 48px;
            color: var(--text-light);
            font-size: 0.95rem;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .search-input::placeholder {
            color: var(--text-muted);
        }

        .search-input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.2);
        }

        .clear-search-wrapper {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
        }

        .clear-search {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: transparent;
            border: none;
            color: var(--text-muted);
            padding: 4px;
            line-height: 1;
            border-radius: 50%;
            font-size: 1.2rem;
        }

        .clear-search:hover {
            color: var(--text-light);
            background-color: #3f3f46;
        }

        .filter-controls {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .category-filter {
            background-color: #27272a;
            border: 1px solid #3f3f46;
            border-radius: 8px;
            padding: 12px 16px;
            color: var(--text-light);
            font-size: 0.95rem;
            min-width: 180px;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .category-filter:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.2);
        }

        .add-new-btn, .export-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            white-space: nowrap;
            border-radius: 9999px;
            padding: 12px 22px;
            font-weight: 500;
            font-size: 0.95rem;
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            color: #fff;
            text-decoration: none;
        }

        .add-new-btn:hover, .export-btn:hover {
            background-color: #0b5ed7;
            border-color: #0a58ca;
            color: #fff;
        }

        /* --- Table Styling --- */
        .table-container {
            background: var(--card-bg);
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.08);
        }

        .fixed-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 1500px; /* Increased for new column */
        }

        .fixed-table thead {
            background: rgba(255, 255, 255, 0.06);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .fixed-table th {
            padding: 16px 14px;
            text-align: left;
            font-weight: 600;
            font-size: 0.82rem;
            color: var(--text-light);
            white-space: nowrap;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .fixed-table td {
            padding: 14px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.04);
            vertical-align: middle;
            font-size: 0.88rem;
        }

        .fixed-table tbody tr:hover {
            background: rgba(255, 255, 255, 0.02);
        }

        .fixed-table tbody tr:last-child td {
            border-bottom: none;
        }

        /* Updated column widths */
        .col-category {
            width: 12%;
            min-width: 140px;
        }

        .col-account {
            width: 16%;
            min-width: 180px;
        }

        .col-website {
            width: 14%;
            min-width: 160px;
        }

        .col-password {
            width: 12%;
            min-width: 150px;
        }

        .col-passkeys {
            width: 10%;
            min-width: 130px;
        }

        .col-notes {
            width: 12%;
            min-width: 150px;
        }

        .col-strength {
            width: 8%;
            min-width: 110px;
        }

        .col-actions {
            width: 7%;
            min-width: 90px;
        }

        .col-lastupdate {
            width: 9%;
            min-width: 130px;
        }

        /* Category badge styling */
        .category-badge {
            display: inline-block;
            padding: 6px 12px;
            background: rgba(13, 110, 253, 0.15);
            color: var(--primary-color);
            border-radius: 6px;
            font-size: 0.8rem;
            font-weight: 500;
            border: 1px solid rgba(13, 110, 253, 0.3);
            white-space: nowrap;
            text-align: center;
            width: 100%;
        }

        .account-email {
            font-weight: 500;
            color: var(--text-light);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            padding: 8px 12px;
            background: rgba(255, 255, 255, 0.03);
            border-radius: 6px;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .website-link {
            color: var(--primary-color);
            text-decoration: none;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            display: block;
            padding: 8px 12px;
            background: rgba(255, 255, 255, 0.03);
            border-radius: 6px;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .website-link:hover {
            background: rgba(255, 255, 255, 0.06);
            color: var(--primary-color);
            text-decoration: none;
        }

        .password-cell {
            display: flex;
            align-items: center;
            gap: 8px;
            background: rgba(255, 255, 255, 0.03);
            padding: 8px 12px;
            border-radius: 6px;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .password-field {
            flex: 1;
            background: transparent;
            border: none;
            color: var(--text-light);
            font-family: 'Courier New', monospace;
            font-size: 0.85rem;
            padding: 0;
        }

        .password-field:focus {
            outline: none;
        }

        .password-actions {
            display: flex;
            gap: 6px;
        }

        .password-actions i {
            cursor: pointer;
            padding: 5px;
            border-radius: 4px;
            font-size: 0.85rem;
            background: rgba(255, 255, 255, 0.05);
        }

        .password-actions i:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        .passkeys-content {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .passkeys-text {
            flex: 1;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            font-size: 0.85rem;
            background: rgba(255, 255, 255, 0.03);
            padding: 8px 12px;
            border-radius: 6px;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .copy-passkeys-btn {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: var(--text-light);
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 0.75rem;
            white-space: nowrap;
        }

        .copy-passkeys-btn:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        .notes-text {
            font-size: 0.85rem;
            line-height: 1.4;
            background: rgba(255, 255, 255, 0.03);
            padding: 10px 12px;
            border-radius: 6px;
            border: 1px solid rgba(255, 255, 255, 0.05);
            max-height: 60px;
            overflow-y: auto;
            word-wrap: break-word;
        }

        .notes-text::-webkit-scrollbar {
            width: 4px;
        }

        .notes-text::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 2px;
        }

        .notes-text::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.15);
            border-radius: 2px;
        }

        .notes-text::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.25);
        }

        /* Password Strength Styles */
        .strength-container {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .strength-meter {
            height: 6px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 3px;
            overflow: hidden;
        }

        .strength-fill {
            height: 100%;
            border-radius: 3px;
            transition: all 0.3s ease;
        }

        .strength-danger {
            background: linear-gradient(90deg, #dc3545, #e74c3c);
        }

        .strength-warning {
            background: linear-gradient(90deg, #ffc107, #f39c12);
        }

        .strength-info {
            background: linear-gradient(90deg, #0dcaf0, #3498db);
        }

        .strength-success {
            background: linear-gradient(90deg, #198754, #27ae60);
        }

        .strength-perfect {
            background: linear-gradient(90deg, #198754, #2ecc71);
        }

        .text-danger {
            color: #dc3545 !important;
        }

        .text-warning {
            color: #ffc107 !important;
        }

        .text-info {
            color: #0dcaf0 !important;
        }

        .text-success {
            color: #198754 !important;
        }

        .text-perfect {
            color: #2ecc71 !important;
        }

        .action-buttons {
            display: flex;
            justify-content: flex-end;
            gap: 6px;
        }

        .action-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 34px;
            height: 34px;
            border-radius: 6px;
            background: rgba(255, 255, 255, 0.05);
            color: var(--text-light);
            text-decoration: none;
            border: 1px solid rgba(255, 255, 255, 0.08);
        }

        .action-btn:hover {
            background: rgba(255, 255, 255, 0.1);
            color: var(--text-light);
            text-decoration: none;
        }

        .action-btn.edit:hover {
            background: rgba(13, 110, 253, 0.15);
            border-color: rgba(13, 110, 253, 0.3);
        }

        .action-btn.delete:hover {
            background: rgba(220, 53, 69, 0.15);
            border-color: rgba(220, 53, 69, 0.3);
        }

        /* Last Update Styles */
        .lastupdate-cell {
            text-align: center;
        }

        .lastupdate-time {
            font-size: 0.8rem;
            font-weight: 500;
            color: var(--text-light);
            white-space: nowrap;
        }

        .lastupdate-ago {
            font-size: 0.75rem;
            color: var(--text-muted);
            white-space: nowrap;
        }

        /* Delete Confirmation Modal */
        .modal-backdrop {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 1050;
        }

        .modal-content {
            background: rgba(20, 20, 20, 1);
            border-radius: 12px;
            padding: 2rem;
            max-width: 450px;
            width: 90%;
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 1);
        }

        .modal-header {
            text-align: center;
            margin-bottom: 1.5rem;
        }

        .modal-icon {
            font-size: 3rem;
            color: #dc3545;
            margin-bottom: 1rem;
        }

        .modal-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--text-light);
            margin-bottom: 0.5rem;
        }

        .modal-message {
            color: var(--text-muted);
            text-align: center;
            line-height: 1.5;
            margin-bottom: 2rem;
        }

        .modal-actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
        }

        .modal-btn {
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 500;
            border: none;
            cursor: pointer;
            transition: all 0.2s ease;
            min-width: 120px;
        }

        .modal-btn-cancel {
            background: rgba(255, 255, 255, 0.05);
            color: var(--text-light);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .modal-btn-cancel:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        .modal-btn-delete {
            background: #dc3545;
            color: white;
            border: 1px solid #dc3545;
        }

        .modal-btn-delete:hover {
            background: #c82333;
            border-color: #c82333;
        }

        .table-responsive::-webkit-scrollbar {
            height: 10px;
        }

        .table-responsive::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.03);
            border-radius: 0 0 8px 8px;
        }

        .table-responsive::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.15);
            border-radius: 5px;
            border: 2px solid rgba(255, 255, 255, 0.03);
        }

        .table-responsive::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.25);
        }

        .table-responsive {
            overflow-x: auto;
            border-radius: 8px;
        }

        .empty-state {
            padding: 60px 20px;
            text-align: center;
            color: var(--text-muted);
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.4;
        }

        .empty-state p {
            font-size: 1rem;
            margin-bottom: 1.5rem;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .fixed-table {
                min-width: 1300px;
            }

            .fixed-table th,
            .fixed-table td {
                padding: 12px 10px;
            }

            .modal-actions {
                flex-direction: column;
            }

            .modal-btn {
                min-width: auto;
                width: 100%;
            }

            .control-bar {
                flex-direction: column;
                align-items: stretch;
            }

            .filter-controls {
                flex-direction: column;
                width: 100%;
            }

            .category-filter {
                min-width: auto;
                width: 100%;
            }
        }

        @media (max-width: 576px) {
            .control-bar {
                flex-direction: column;
                align-items: stretch;
            }
        }
    </style>
    <title>Elocker - Manage Passwords</title>
</head>

<body class="background text-light">

    <?php include "../include/navbar.php"; ?>

    <main class="container py-4">
        <div class="row justify-content-center">
            <div class="col-12">
                <div class="page-header">
                    <h2 class="fw-bold mb-2">Manage Passwords</h2>
                    <p class="text-muted mb-0">View, search, and manage your saved credentials.</p>
                </div>

                <?php if (!empty($message)): ?>
                    <div class="alert <?php echo $message_type; ?>" role="alert">
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>

                <form method="post" action="">
                    <div class="control-bar">
                        <div class="search-form">
                            <i class="ri-search-line search-icon"></i>
                            <input class="search-input" type="search" name="search"
                                placeholder="Search by email or site..."
                                value="<?php echo htmlspecialchars($search_term); ?>">
                            <?php if (!empty($search_term)): ?>
                                <div class="clear-search-wrapper">
                                    <a href="managepassword" class="clear-search" title="Clear search">
                                        <i class="ri-close-line"></i>
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="filter-controls">
                            <select name="category_filter" class="category-filter">
                                <option value="">All labels</option>
                                <?php foreach ($categories as $key => $value): ?>
                                    <option value="<?php echo htmlspecialchars($key); ?>" 
                                        <?php echo $category_filter === $key ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($value); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>

                            <button type="submit" class="add-new-btn">
                                <i class="ri-filter-line"></i> Apply Filter
                            </button>
                        </div>

                        <a href="export" class="add-new-btn">
                            <i class="ri-download-line"></i> Export Data
                        </a>
                        <a href="storenewpassword" class="add-new-btn">
                            <i class="ri-add-line"></i> Add New
                        </a>
                    </div>
                </form>

                <div class="table-container">
                    <div class="table-responsive">
                        <table class="fixed-table">
                            <thead>
                                <tr>
                                    <th class="col-category">Label</th>
                                    <th class="col-account">Account / Email</th>
                                    <th class="col-website">Website</th>
                                    <th class="col-password">Password</th>
                                    <th class="col-passkeys">Passkey</th>
                                    <th class="col-notes">Note</th>
                                    <th class="col-strength">Strength</th>
                                    <th class="col-actions text-center">Actions</th>
                                    <th class="col-lastupdate text-center">Last Update</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($passwords)): ?>
                                    <tr>
                                        <td colspan="9" class="empty-state">
                                            <i class="ri-database-2-line"></i>
                                            <p class="mb-3">
                                                <?php 
                                                if (!empty($search_term) && !empty($category_filter)) {
                                                    echo 'No results found for your search and category filter.';
                                                } elseif (!empty($search_term)) {
                                                    echo 'No results found for your search.';
                                                } elseif (!empty($category_filter)) {
                                                    echo 'No passwords found in this category.';
                                                } else {
                                                    echo 'You haven\'t stored any passwords yet.';
                                                }
                                                ?>
                                            </p>
                                            <?php if (empty($search_term) && empty($category_filter)): ?>
                                                <a href="storenewpassword" class="btn btn-primary">
                                                    <i class="ri-add-line"></i> Add your first password
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($passwords as $row): ?>
                                        <tr>
                                            <td class="col-category">
                                                <div class="category-badge">
                                                    <i class="ri-price-tag-3-line"></i>
                                                    <?php 
                                                    $category_display = isset($categories[$row['category']]) ? $categories[$row['category']] : $row['category'];
                                                    echo htmlspecialchars($category_display); 
                                                    ?>
                                                </div>
                                            </td>
                                            <td class="col-account">
                                                <div class="account-email" title="<?php echo htmlspecialchars($row['email']); ?>">
                                                    <?php echo htmlspecialchars($row['email']); ?>
                                                </div>
                                            </td>
                                            <td class="col-website">
                                                <?php if (!empty($row['links'])): ?>
                                                    <a href="<?php echo htmlspecialchars($row['links']); ?>" target="_blank" rel="noopener noreferrer" class="website-link" title="<?php echo htmlspecialchars($row['links']); ?>">
                                                        <i class="ri-external-link-line me-1"></i>
                                                        <?php
                                                        $displayLink = parse_url($row['links'], PHP_URL_HOST) ?: $row['links'];
                                                        if (strlen($displayLink) > 25) {
                                                            $displayLink = substr($displayLink, 0, 25) . '...';
                                                        }
                                                        echo htmlspecialchars($displayLink);
                                                        ?>
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="col-password">
                                                <div class="password-cell">
                                                    <input type="password" class="password-field" value="••••••••" readonly>
                                                    <div class="password-actions">
                                                        <i class="ri-eye-line toggle-password" title="Show/Hide Password"></i>
                                                        <i class="ri-clipboard-line copy-password" title="Copy Password" data-password="<?php echo htmlspecialchars($row['decrypted_password']); ?>"></i>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="col-passkeys">
                                                <?php if (!empty($row['passkeys'])): ?>
                                                    <div class="passkeys-content">
                                                        <span class="passkeys-text" title="<?php echo htmlspecialchars($row['passkeys']); ?>">
                                                            <?php
                                                            $displayPasskeys = $row['passkeys'];
                                                            if (strlen($displayPasskeys) > 18) {
                                                                $displayPasskeys = substr($displayPasskeys, 0, 18) . '...';
                                                            }
                                                            echo htmlspecialchars($displayPasskeys);
                                                            ?>
                                                        </span>
                                                        <button class="btn btn-sm copy-passkeys-btn" data-passkeys="<?php echo htmlspecialchars($row['passkeys']); ?>">
                                                            Copy
                                                        </button>
                                                    </div>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="col-notes">
                                                <?php if (!empty($row['notes'])): ?>
                                                    <div class="notes-text">
                                                        <?php echo htmlspecialchars($row['notes']); ?>
                                                    </div>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="col-strength">
                                                <div class="strength-container" data-password="<?php echo htmlspecialchars($row['decrypted_password']); ?>">
                                                    <div class="strength-meter">
                                                        <div class="strength-fill strength-danger" style="width: 0%"></div>
                                                    </div>
                                                    <div class="strength-text text-muted">0%</div>
                                                </div>
                                            </td>
                                            <td class="col-actions">
                                                <div class="action-buttons">
                                                    <a href="update?id=<?php echo $row['id']; ?>" class="action-btn edit" title="Edit">
                                                        <i class="ri-edit-2-line"></i>
                                                    </a>
                                                    <button class="action-btn delete delete-btn" title="Delete" data-id="<?php echo $row['id']; ?>">
                                                        <i class="ri-delete-bin-6-line"></i>
                                                    </button>
                                                </div>
                                            </td>
                                            <td class="col-lastupdate lastupdate-cell">
                                                <div class="lastupdate-time" title="<?php echo htmlspecialchars($row['formatted_lastupdate']); ?>">
                                                    <?php echo htmlspecialchars($row['formatted_lastupdate']); ?>
                                                </div>
                                                <div class="lastupdate-ago">
                                                    <?php echo htmlspecialchars($row['time_ago']); ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <?php if (empty($search_term) && empty($category_filter) && $total_pages > 1): ?>
                    <nav class="pagination-container mt-3">
                        <ul class="pagination justify-content-center">
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?php echo ($i == $current_page) ? 'active' : ''; ?>">
                                    <a class="page-link" href="managepassword?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <!-- Delete Confirmation Modal -->
    <div class="modal-backdrop" id="deleteModal">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-icon">
                    <i class="ri-delete-bin-7-fill"></i>
                </div>
                <h3 class="modal-title">Delete Password</h3>
            </div>
            <div class="modal-message">
                Are you sure you want to delete this password? This action cannot be undone and the data will be permanently lost.
            </div>
            <div class="modal-actions">
                <button class="modal-btn modal-btn-cancel" id="cancelDelete">Cancel</button>
                <button class="modal-btn modal-btn-delete" id="confirmDelete">Delete Permanently</button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Delete Modal Functionality
            const deleteModal = document.getElementById('deleteModal');
            const cancelDeleteBtn = document.getElementById('cancelDelete');
            const confirmDeleteBtn = document.getElementById('confirmDelete');
            let currentDeleteId = null;

            // Open delete modal when delete button is clicked
            document.querySelectorAll('.delete-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    currentDeleteId = this.getAttribute('data-id');
                    deleteModal.style.display = 'flex';
                });
            });

            // Close modal when cancel is clicked
            cancelDeleteBtn.addEventListener('click', function() {
                deleteModal.style.display = 'none';
                currentDeleteId = null;
            });

            // Confirm delete action
            confirmDeleteBtn.addEventListener('click', function() {
                if (currentDeleteId) {
                    window.location.href = 'delete?delete=' + currentDeleteId;
                }
            });

            // Close modal when clicking outside
            deleteModal.addEventListener('click', function(e) {
                if (e.target === deleteModal) {
                    deleteModal.style.display = 'none';
                    currentDeleteId = null;
                }
            });

            // Password strength calculation function
            function calculatePasswordStrength(password) {
                let score = 0;
                let feedback = [];

                if (!password || password.length === 0) {
                    return {
                        score: 0,
                        feedback: ['Empty password']
                    };
                }

                // Length criteria
                if (password.length >= 8) score += 25;
                if (password.length >= 12) score += 15;
                if (password.length >= 16) score += 10;

                // Character variety
                if (/[a-z]/.test(password)) score += 10; // lowercase
                if (/[A-Z]/.test(password)) score += 10; // uppercase
                if (/[0-9]/.test(password)) score += 10; // numbers
                if (/[^A-Za-z0-9]/.test(password)) score += 15; // special characters

                // Bonus points for complexity
                if (/(?=.*[a-z])(?=.*[A-Z])/.test(password)) score += 5; // mixed case
                if (/(?=.*\d)(?=.*[^A-Za-z0-9])/.test(password)) score += 5; // numbers + special chars
                if (password.length > 20) score += 5; // very long passwords

                // Deductions for weak patterns
                if (/^[a-zA-Z]+$/.test(password)) score -= 10; // letters only
                if (/^\d+$/.test(password)) score -= 15; // numbers only
                if (/(.)\1{2,}/.test(password)) score -= 10; // repeated characters
                if (/123|abc|password|admin|qwerty/i.test(password)) score -= 20; // common patterns

                // Ensure score is between 0 and 100
                score = Math.max(0, Math.min(100, score));

                return score;
            }

            function updateStrengthDisplay(element, password) {
                const strength = calculatePasswordStrength(password);
                const fillElement = element.querySelector('.strength-fill');
                const textElement = element.querySelector('.strength-text');

                // Remove all existing classes
                fillElement.className = 'strength-fill';
                textElement.className = 'strength-text';

                // Set width and color based on strength
                fillElement.style.width = strength + '%';

                if (strength <= 30) {
                    fillElement.classList.add('strength-danger');
                    textElement.classList.add('text-danger');
                    textElement.textContent = strength + '% (Weak)';
                } else if (strength <= 80) {
                    fillElement.classList.add('strength-warning');
                    textElement.classList.add('text-warning');
                    textElement.textContent = strength + '% (Medium)';
                } else if (strength <= 90) {
                    fillElement.classList.add('strength-info');
                    textElement.classList.add('text-info');
                    textElement.textContent = strength + '% (Strong)';
                } else if (strength < 100) {
                    fillElement.classList.add('strength-success');
                    textElement.classList.add('text-success');
                    textElement.textContent = strength + '% (Very Strong)';
                } else {
                    fillElement.classList.add('strength-perfect');
                    textElement.classList.add('text-perfect');
                    textElement.textContent = strength + '% (Perfect)';
                }
            }

            // Initialize strength indicators for all passwords
            document.querySelectorAll('.strength-container').forEach(container => {
                const password = container.dataset.password;
                updateStrengthDisplay(container, password);
            });

            // Update strength display when password visibility is toggled
            document.querySelectorAll('.toggle-password').forEach(icon => {
                icon.addEventListener('click', function() {
                    const passwordField = this.closest('.password-cell').querySelector('.password-field');
                    const copyIcon = this.closest('.password-cell').querySelector('.copy-password');
                    const isPassword = passwordField.type === 'password';
                    const strengthContainer = this.closest('tr').querySelector('.strength-container');
                    const actualPassword = copyIcon.dataset.password;

                    passwordField.type = isPassword ? 'text' : 'password';
                    passwordField.value = isPassword ? actualPassword : '••••••••';
                    this.classList.toggle('ri-eye-line');
                    this.classList.toggle('ri-eye-off-line');

                    // Update strength display with actual password
                    if (isPassword) {
                        updateStrengthDisplay(strengthContainer, actualPassword);
                    }
                });
            });

            // Password copy functionality
            document.querySelectorAll('.copy-password').forEach(icon => {
                icon.addEventListener('click', function() {
                    const passwordToCopy = this.dataset.password;
                    navigator.clipboard.writeText(passwordToCopy).then(() => {
                        const originalIcon = this.className;
                        this.className = 'ri-check-line text-success';

                        setTimeout(() => {
                            this.className = originalIcon;
                        }, 1500);
                    }).catch(err => {
                        console.error('Failed to copy password: ', err);
                    });
                });
            });

            // Passkeys copy functionality
            document.querySelectorAll('.copy-passkeys-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const passkeysToCopy = this.dataset.passkeys;
                    navigator.clipboard.writeText(passkeysToCopy).then(() => {
                        const originalText = this.textContent;
                        this.textContent = 'Copied!';
                        this.classList.remove('btn-secondary');
                        this.classList.add('btn-success');

                        setTimeout(() => {
                            this.textContent = originalText;
                            this.classList.remove('btn-success');
                            this.classList.add('btn-secondary');
                        }, 1500);
                    }).catch(err => {
                        console.error('Failed to copy passkeys: ', err);
                    });
                });
            });
        });
    </script>
</body>

</html>