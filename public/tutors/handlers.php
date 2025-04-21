<?php
require_once dirname(dirname(__DIR__)) . '/config/init.php';

// Only allow tutors to access this handler
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'tutor') {
    header('Location: ' . $basePath . '/tutors/profile');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $tutor = new Tutor($db);
    
    if ($_POST['action'] === 'update_profile') {
        $expertise_area = $_POST['expertise_area'] ?? '';
        $description = $_POST['description'] ?? '';
        
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
                $_SESSION['error'] = 'Invalid file type. Only JPG, JPEG, PNG & GIF files are allowed.';
                header('Location: ' . $basePath . '/tutors/profile');
                exit;
            }

            if ($_FILES['profile_image']['size'] > 2 * 1024 * 1024) {
                $_SESSION['error'] = 'File size too large. Maximum size is 2MB.';
                header('Location: ' . $basePath . '/tutors/profile');
                exit;
            }

            $file_name = uniqid() . '.' . $file_extension;
            $target_path = $upload_dir . $file_name;

            if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $target_path)) {
                $profile_image = '/uploads/profile_images/' . $file_name;
                
                // Update user's profile image in users table
                $query = "UPDATE users SET profile_image = :profile_image WHERE id = :user_id";
                $stmt = $db->prepare($query);
                $stmt->execute([
                    ':profile_image' => $profile_image,
                    ':user_id' => $_SESSION['user_id']
                ]);
                
                // Update session
                $_SESSION['profile_image'] = $profile_image;
            }
        }
        
        // Check if profile exists
        $existing_profile = $tutor->getTutorById($_SESSION['user_id']);
        
        if ($existing_profile && isset($existing_profile['expertise_area'])) {
            // Update existing profile
            if ($tutor->updateProfile($_SESSION['user_id'], $expertise_area, $description)) {
                $_SESSION['success'] = 'Profile updated successfully!';
            } else {
                $_SESSION['error'] = 'Failed to update profile.';
            }
        } else {
            // Create new profile
            if ($tutor->create($_SESSION['user_id'], $expertise_area, $description)) {
                $_SESSION['success'] = 'Profile created successfully!';
            } else {
                $_SESSION['error'] = 'Failed to create profile.';
            }
        }
    }
}

// Handle tutor request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'request_tutor') {
    if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'student') {
        $_SESSION['error'] = 'You must be logged in as a student to request a tutor.';
        header('Location: ' . $basePath . '/login');
        exit;
    }

    $tutor_id = $_POST['tutor_id'] ?? '';
    $message = $_POST['message'] ?? '';
    
    $tutor = new Tutor($db);
    
    if ($tutor->createRequest($_SESSION['user_id'], $tutor_id, $message)) {
        $_SESSION['success'] = 'Your request has been sent to the tutor.';
    } else {
        $_SESSION['error'] = 'Failed to send request. Please try again.';
    }
    
    header('Location: ' . $basePath . '/tutors/view/' . $tutor_id);
    exit;
}

// Redirect back to profile page
header('Location: ' . $basePath . '/tutors/profile');
exit; 