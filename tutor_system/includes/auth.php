<?php
class Auth {
    private $conn;
    private $table_name = "users";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function register($data) {
        try {
            // Check if login_id or email already exists
            $check_query = "SELECT id FROM " . $this->table_name . 
                          " WHERE login_id = :login_id OR email = :email";
            $check_stmt = $this->conn->prepare($check_query);
            $check_stmt->execute([
                ':login_id' => $data['login_id'],
                ':email' => $data['email']
            ]);

            if ($check_stmt->rowCount() > 0) {
                return ['success' => false, 'message' => 'Login ID or email already exists'];
            }

            // Handle profile image upload
            $profile_image = null;
            if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = ROOT_PATH . '/uploads/profile_images/';
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }

                $file_extension = strtolower(pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION));
                $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];

                if (!in_array($file_extension, $allowed_extensions)) {
                    return ['success' => false, 'message' => 'Invalid file type. Only JPG, JPEG, PNG & GIF files are allowed.'];
                }

                if ($_FILES['profile_image']['size'] > 2 * 1024 * 1024) {
                    return ['success' => false, 'message' => 'File size too large. Maximum size is 2MB.'];
                }

                $file_name = uniqid() . '.' . $file_extension;
                $target_path = $upload_dir . $file_name;

                if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $target_path)) {
                    $profile_image = '/uploads/profile_images/' . $file_name;
                } else {
                    return ['success' => false, 'message' => 'Failed to upload profile image.'];
                }
            }

            // Insert user data
            $query = "INSERT INTO " . $this->table_name . 
                    " (login_id, password, nickname, email, age, gender, user_type, profile_image) VALUES 
                    (:login_id, :password, :nickname, :email, :age, :gender, :user_type, :profile_image)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                ':login_id' => $data['login_id'],
                ':password' => password_hash($data['password'], PASSWORD_DEFAULT),
                ':nickname' => $data['nickname'],
                ':email' => $data['email'],
                ':age' => $data['age'],
                ':gender' => $data['gender'],
                ':user_type' => $data['user_type'],
                ':profile_image' => $profile_image
            ]);

            return ['success' => true, 'message' => 'Registration successful'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Registration failed: ' . $e->getMessage()];
        }
    }

    public function storeRememberToken($user_id, $token, $expires) {
        try {
            $query = "INSERT INTO remember_tokens (user_id, token, expires_at) VALUES (:user_id, :token, :expires_at)";
            $stmt = $this->conn->prepare($query);
            return $stmt->execute([
                ':user_id' => $user_id,
                ':token' => $token,
                ':expires_at' => $expires
            ]);
        } catch (Exception $e) {
            return false;
        }
    }

    public function validateRememberToken($user_id, $token) {
        try {
            $query = "SELECT * FROM remember_tokens 
                      WHERE user_id = :user_id 
                      AND token = :token 
                      AND expires_at > NOW()";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                ':user_id' => $user_id,
                ':token' => $token
            ]);
            return $stmt->rowCount() > 0;
        } catch (Exception $e) {
            return false;
        }
    }

    public function getUserById($id) {
        try {
            $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':id' => $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return false;
        }
    }
    

    public function login($login_id, $password, $remember = false) {
        try {
            $query = "SELECT * FROM " . $this->table_name . " WHERE login_id = :login_id";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':login_id' => $login_id]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['login_id'] = $user['login_id'];
                $_SESSION['nickname'] = $user['nickname'] ?? 'User';
                $_SESSION['user_type'] = $user['user_type'];
                $_SESSION['profile_image'] = $user['profile_image'];

                if ($remember) {
                    $token = bin2hex(random_bytes(32));
                    $expires = date('Y-m-d H:i:s', strtotime('+30 days'));
                    
                    // Store token in database
                    $this->storeRememberToken($user['id'], $token, $expires);
                    
                    // Set cookies
                    setcookie('remember_token', $token, [
                        'expires' => time() + (30 * 24 * 60 * 60),
                        'path' => '/',
                        'secure' => false,
                        'httponly' => true,
                        'samesite' => 'Lax'
                    ]);
                    
                    setcookie('user_id', $user['id'], [
                        'expires' => time() + (30 * 24 * 60 * 60),
                        'path' => '/',
                        'secure' => false,
                        'httponly' => true,
                        'samesite' => 'Lax'
                    ]);
                }

                return ['success' => true, 'message' => 'Login successful'];
            }

            return ['success' => false, 'message' => 'Invalid login ID or password'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Login failed: ' . $e->getMessage()];
        }
    }

    public function logout() {
        // Clear session
        $_SESSION = array();
        
        // Delete session cookie
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', [
                'expires' => time() - 3600,
                'path' => '/',
                'domain' => '',
                'secure' => true,
                'httponly' => true,
                'samesite' => 'Strict'
            ]);
        }
        
        // Delete remember me cookies if they exist
        if (isset($_COOKIE['remember_token'])) {
            // Remove from database
            $token = $_COOKIE['remember_token'];
            $query = "DELETE FROM remember_tokens WHERE token = :token";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':token' => $token]);
            
            // Delete both cookies
            setcookie('remember_token', '', time() - 3600, '/');
            setcookie('user_id', '', time() - 3600, '/');
        }
        
        // Destroy session
        session_destroy();

        return ['success' => true, 'message' => 'Logout successful'];
    }

    public function isLoggedIn() {
        // Check session first
        if (isset($_SESSION['user_id'])) {
            return true;
        }

        // Check remember token
        if (isset($_COOKIE['remember_token'])) {
            $token = $_COOKIE['remember_token'];
            
            // Verify token in database
            $query = "SELECT u.* FROM users u 
                     JOIN remember_tokens rt ON u.id = rt.user_id 
                     WHERE rt.token = :token AND rt.expires_at > NOW()";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':token' => $token]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['login_id'] = $user['login_id'];
                $_SESSION['nickname'] = $user['nickname'];
                $_SESSION['user_type'] = $user['user_type'];
                $_SESSION['profile_image'] = $user['profile_image'];
                return true;
            }
        }
        
        return false;
    }
}
?> 