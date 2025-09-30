<?php
// INDEX::Prevent Direct Access to Private Files
if (!defined('APP_RUN')) {
    header("HTTP/1.0 403 Forbidden");
    header("Location: useraccount.php");
    exit();
}
?>
