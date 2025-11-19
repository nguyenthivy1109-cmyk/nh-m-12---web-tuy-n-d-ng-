<?php

require_once '../config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

requireRole(ROLE_ADMIN);

header("Location: " . adminRoute('dashboard'));
exit();
