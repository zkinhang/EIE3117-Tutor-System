<?php
require_once dirname(dirname(__DIR__)) . '/config/init.php';

// Define base path
$basePath = '/tutor_system/public';

// Verify CSRF token for POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        $_SESSION['error'] = 'Invalid security token';
        header('Location: ' . $basePath . '/login');
        exit;
    }
}

// Handle Logout
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'logout') {
    $auth = new Auth($db);
    $result = $auth->logout();
    header('Location: ' . $basePath . '/login');
    exit;
}

// Handle Login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $auth = new Auth($db);
    
    if ($_POST['action'] === 'login') {
        $login_id = $_POST['login_id'] ?? '';
        $password = $_POST['password'] ?? '';
        $remember = isset($_POST['remember_me']) ? true : false;
        $result = $auth->login($_POST['login_id'], $_POST['password'], $remember);

        if ($result['success']) {
            // Successful login
            header('Location: ' . $basePath . '/');
            exit;
        } else {
            // Failed login
            $_SESSION['error'] = $result['message'];
            header('Location: ' . $basePath . '/login');
            exit;
        }
    }
    
    if ($_POST['action'] === 'register') {
        $userData = [
            'login_id' => $_POST['login_id'] ?? '',
            'password' => $_POST['password'] ?? '',
            'nickname' => $_POST['nickname'] ?? '',
            'email' => $_POST['email'] ?? '',
            'age' => $_POST['age'] ?? '',
            'gender' => $_POST['gender'] ?? '',
            'user_type' => $_POST['user_type'] ?? ''
        ];

        $result = $auth->register($userData);

        if ($result['success']) {
            $_SESSION['success'] = $result['message'];
            header('Location: ' . $basePath . '/login');
            exit;
        } else {
            $_SESSION['error'] = $result['message'];
            header('Location: ' . $basePath . '/register');
            exit;
        }
    }

    if (isset($_POST['action']) && $_POST['action'] === 'logout') {
        $auth = new Auth($db);
        $auth->logout($_SESSION['user_id']);
        
        header('Location: ' . $basePath . '/login');
        exit;
    }
}

// If we get here, something went wrong
header('Location: ' . $basePath . '/login');
exit;

// Add this to your existing handlers.php file
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'logout') {
    $auth = new Auth($db);

    $auth->logout($_SESSION['user_id']);
    header('Location: ' . $basePath . '/login');
    exit;
} 