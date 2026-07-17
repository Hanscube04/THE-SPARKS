<?php
/**
 * config.php - application bootstrap
 * Starts the session, defines base paths, and autoload-style requires.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('BASE_PATH', dirname(__DIR__));
define('BASE_URL', '/thesparks/'); // change to '/TheSparks/' if hosted in a subfolder

require_once BASE_PATH . '/config/Database.php';
require_once BASE_PATH . '/classes/Person.php';
require_once BASE_PATH . '/classes/User.php';
require_once BASE_PATH . '/classes/Admin.php';
require_once BASE_PATH . '/classes/SuperAdmin.php';
require_once BASE_PATH . '/classes/Product.php';
require_once BASE_PATH . '/classes/OrderModel.php';
require_once BASE_PATH . '/classes/RepairRequest.php';
require_once BASE_PATH . '/classes/Auth.php';

date_default_timezone_set('Africa/Dar_es_Salaam');
