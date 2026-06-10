<?php
/**
 * admin/logout.php
 * Admin Logout
 */

require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../includes/Helper.php';
require_once __DIR__ . '/../includes/Auth.php';

$auth = new Auth();
$auth->logout();

redirect(url('/admin/login.php'));
