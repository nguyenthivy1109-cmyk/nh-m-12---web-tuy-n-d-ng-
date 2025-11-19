<?php
/**
 * File index.php ở root - Redirect đến router
 * Giữ file này để tương thích với các link cũ và SEO
 */

require_once 'config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Redirect đến router với role=public và page=home
header("Location: " . publicRoute('home'));
exit();

