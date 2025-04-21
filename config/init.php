<?php
// Start session
session_start();

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define root path
define('ROOT_PATH', dirname(__DIR__));

// Include database configuration
require_once ROOT_PATH . '/config/database.php';

// Include core classes and security functions
require_once ROOT_PATH . '/includes/auth.php';
require_once ROOT_PATH . '/includes/user.php';
require_once ROOT_PATH . '/includes/tutor.php';
require_once ROOT_PATH . '/includes/message.php';
require_once ROOT_PATH . '/includes/helpers.php';
require_once ROOT_PATH . '/includes/security.php';

// Set security headers
set_security_headers();

// Initialize database connection
$database = new Database();
$db = $database->getConnection();

// Add or update this line at the top of your init.php
$basePath = '/tutor_system/public';

// Check for remember me token
if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_token']) && isset($_COOKIE['user_id'])) {
    $auth = new Auth($db);
    if ($auth->validateRememberToken($_COOKIE['user_id'], $_COOKIE['remember_token'])) {
        $user = $auth->getUserById($_COOKIE['user_id']);
        if ($user) {
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['login_id'] = $user['login_id'];
            $_SESSION['nickname'] = $user['nickname'] ?? 'User';
            $_SESSION['user_type'] = $user['user_type'];
            $_SESSION['profile_image'] = $user['profile_image'];

            // Refresh the remember me token
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+30 days'));
            
            // Store new token
            $auth->storeRememberToken($user['id'], $token, $expires);
            
            // Update cookies
            setcookie('remember_token', $token, [
                'expires' => time() + (30 * 24 * 60 * 60),
                'path' => '/',
                'secure' => true,
                'httponly' => true,
                'samesite' => 'Strict'
            ]);
            
            setcookie('user_id', $user['id'], [
                'expires' => time() + (30 * 24 * 60 * 60),
                'path' => '/',
                'secure' => true,
                'httponly' => true,
                'samesite' => 'Strict'
            ]);
        }
    }
}
?>
