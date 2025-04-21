<?php
require_once ROOT_PATH . '/includes/auth.php';

class ProfileController {
    private $auth;
    private $conn;
    private $basePath;

    public function __construct($db) {
        $this->conn = $db;
        $this->auth = new Auth($db);
        $this->basePath = '/tutor_system/public';
    }

    public function edit() {
        if (!$this->auth->isLoggedIn()) {
            header('Location: ' . $this->basePath . '/login');
            exit;
        }

        // Get user data
        if ($_SESSION['user_type'] === 'tutor') {
            $query = "SELECT u.*, t.expertise_area, t.description 
                     FROM users u 
                     LEFT JOIN tutor_profiles t ON u.id = t.user_id 
                     WHERE u.id = :user_id";
        } else {
            $query = "SELECT * FROM users WHERE id = :user_id";
        }

        $stmt = $this->conn->prepare($query);
        $stmt->execute([':user_id' => $_SESSION['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            $_SESSION['error'] = 'User not found';
            header('Location: ' . $this->basePath . '/home');
            exit;
        }

        // Load the view
        require_once ROOT_PATH . '/views/profile/edit.php';
    }

    public function update() {
        if (!$this->auth->isLoggedIn()) {
            header('Location: ' . $this->basePath . '/login');
            exit;
        }

        try {
            // Start transaction
            $this->conn->beginTransaction();

            // Initialize profile_image variable
            $profile_image = null;

            // Handle profile image upload
            if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = ROOT_PATH . '/uploads/profile_images/';
                
                // Create directory if it doesn't exist
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }

                $file_extension = strtolower(pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION));
                $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];

                if (!in_array($file_extension, $allowed_extensions)) {
                    throw new Exception('Invalid file type. Only JPG, JPEG, PNG & GIF files are allowed.');
                }

                if ($_FILES['profile_image']['size'] > 2 * 1024 * 1024) {
                    throw new Exception('File size too large. Maximum size is 2MB.');
                }

                $file_name = uniqid() . '.' . $file_extension;
                $target_path = $upload_dir . $file_name;

                if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $target_path)) {
                    $profile_image = '/uploads/profile_images/' . $file_name;
                } else {
                    throw new Exception('Failed to upload profile image.');
                }
            }

            // Prepare update data
            $update_data = [
                'nickname' => $_POST['nickname'],
                'email' => $_POST['email'],
                'age' => $_POST['age'] ?? null,
                'gender' => $_POST['gender'] ?? null,
                'id' => $_SESSION['user_id']
            ];

            // Add profile image to update data if a new one was uploaded
            if ($profile_image) {
                $update_data['profile_image'] = $profile_image;
            }

            // Build the update query
            $query = "UPDATE users SET 
                     nickname = :nickname,
                     email = :email,
                     age = :age,
                     gender = :gender";
            
            if ($profile_image) {
                $query .= ", profile_image = :profile_image";
            }
            
            $query .= " WHERE id = :id";

            $stmt = $this->conn->prepare($query);
            $stmt->execute($update_data);

            // Update tutor profile if user is a tutor
            if ($_SESSION['user_type'] === 'tutor' && isset($_POST['expertise_area'])) {
                $tutor = new Tutor($this->conn);
                $tutor->updateProfile(
                    $_SESSION['user_id'],
                    $_POST['expertise_area'],
                    $_POST['description'] ?? ''
                );
            }

            // Update session data
            $_SESSION['nickname'] = $_POST['nickname'];
            if ($profile_image) {
                $_SESSION['profile_image'] = $profile_image;
            }

            // Commit transaction
            $this->conn->commit();
            $_SESSION['success'] = 'Profile updated successfully!';

            // Redirect back to edit page instead of profile
            header('Location: ' . $this->basePath . '/profile/edit');
            exit;

        } catch (Exception $e) {
            // Rollback transaction if it's active
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            $_SESSION['error'] = $e->getMessage();
            header('Location: ' . $this->basePath . '/profile/edit');
            exit;
        }
    }
}
?> 