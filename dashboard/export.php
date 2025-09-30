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

try {
    // Initialize encryption
    $encryption = new Encryption();

    // Fetch all user data
    $sql = "SELECT * FROM `storage` WHERE user_id = ? ORDER BY id DESC";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $export_data = [];

    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            try {
                // Decrypt all fields
                $decrypted_username = $encryption->decrypt($row['username']);
                $decrypted_email = $encryption->decrypt($row['email']);
                $decrypted_links = $encryption->decrypt($row['links']);
                $decrypted_password = $encryption->decrypt($row['password']);
                $decrypted_passkeys = $encryption->decrypt($row['passkeys']);
                $decrypted_notes = $encryption->decrypt($row['notes']);
                
                // Prepare data for export (excluding id and user_id)
                $export_data[] = [
                    'Email' => $decrypted_email ?: 'Decryption Failed',
                    'Username' => $decrypted_username ?: 'Decryption Failed',
                    'Website' => $decrypted_links ?: '',
                    'Password' => $decrypted_password ?: 'Decryption Failed',
                    'Passkeys' => $decrypted_passkeys ?: '',
                    'Notes' => $decrypted_notes ?: '',
                    'Last Update' => $row['lastupdate']
                ];
            } catch (Exception $e) {
                // Skip rows with decryption errors
                error_log("Export decryption error for record ID {$row['id']}: " . $e->getMessage());
                continue;
            }
        }
    }

    // Set headers for CSV file download
    $filename = "password_export_" . date('Y-m-d_H-i-s') . ".csv";
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    // Create output stream
    $output = fopen('php://output', 'w');
    
    // Add BOM for UTF-8 to help Excel with special characters
    fputs($output, "\xEF\xBB\xBF");
    
    // Add headers
    if (!empty($export_data)) {
        fputcsv($output, array_keys($export_data[0]));
        
        // Add data rows
        foreach ($export_data as $row) {
            fputcsv($output, $row);
        }
    } else {
        fputcsv($output, ['No data available']);
    }
    
    fclose($output);
    exit();

} catch (Exception $e) {
    // If there's an error, redirect back with error message
    $_SESSION['export_error'] = "Export failed: " . $e->getMessage();
    header('Location: managepassword');
    exit();
}
?>