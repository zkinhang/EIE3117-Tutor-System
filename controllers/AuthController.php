<?php
require_once ROOT_PATH . '/includes/auth.php';

class AuthController {
    private $auth;
    private $conn;
    private $basePath;

    public function __construct($db) {
        $this->conn = $db;
        $this->auth = new Auth($db);
        $this->basePath = '/tutor_system/public';
    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $login_id = $_POST['login_id'] ?? '';
            $password = $_POST['password'] ?? '';
            $remember = isset($_POST['remember']);

            $result = $this->auth->login($login_id, $password, $remember);

            if ($result['success']) {
                header('Location: ' . $this->basePath . '/home');
                exit;
            } else {
                $_SESSION['error'] = $result['message'];
                header('Location: ' . $this->basePath . '/login');
                exit;
            }
        }
    }

    public function logout() {
        $this->auth->logout();
        header('Location: ' . $this->basePath . '/login');
        exit;
    }
}
?> 