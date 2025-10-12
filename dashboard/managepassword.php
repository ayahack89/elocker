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

                // Generate encrypted ID for secure URL
                $row['encrypted_id'] = $encryption->encrypt($row['id']);

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

// Function to format URL with proper protocol
function formatUrl($url) {
    if (empty($url)) {
        return '';
    }
    
    // Check if URL already has a protocol or is a special protocol
    if (preg_match('/^(https?:\/\/|ftp:\/\/|mailto:|tel:|#|\/\/)/i', $url)) {
        return $url;
    }
    
    // Prepend https:// for URLs without protocol
    return 'https://' . $url;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include "../include/cdn.php"; ?>
    <link rel="stylesheet" href="../css/style.css">

    <title>Wolfallet - Manage Passwords</title>
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
                                                    <?php
                                                    $formatted_url = formatUrl($row['links']);
                                                    $displayLink = parse_url($formatted_url, PHP_URL_HOST) ?: $formatted_url;
                                                    if (strlen($displayLink) > 25) {
                                                        $displayLink = substr($displayLink, 0, 25) . '...';
                                                    }
                                                    ?>
                                                    <a href="<?php echo htmlspecialchars($formatted_url); ?>" target="_blank" rel="noopener noreferrer" class="website-link" title="<?php echo htmlspecialchars($row['links']); ?>">
                                                        <i class="ri-external-link-line me-1"></i>
                                                        <?php echo htmlspecialchars($displayLink); ?>
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
                                                    <!-- CHANGED: Use encrypted ID in URL -->
                                                    <a href="update?token=<?php echo urlencode($row['encrypted_id']); ?>" class="action-btn edit" title="Edit">
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
                Are you sure you want to delete this password? This action cannot be unchanged and the data will be permanently lost.
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