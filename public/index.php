<?php
require_once dirname(__DIR__) . '/config/init.php';

// Handle static files
$request_uri = $_SERVER['REQUEST_URI'];
$basePath = '/tutor_system/public'; // Define base path earlier

if (preg_match('/\.(css|js|png|jpg|jpeg|gif)$/', $request_uri)) {
    $path_component = parse_url($request_uri, PHP_URL_PATH);
    // Remove the base path to get the path relative to the public directory
    $relative_path = str_replace($basePath, '', $path_component);
    $file_path = __DIR__ . $relative_path; // Construct the correct local path

    if (file_exists($file_path)) {
        $extension = pathinfo($file_path, PATHINFO_EXTENSION);
        $mime_types = [
            'css' => 'text/css',
            'js' => 'text/javascript',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif' => 'image/gif'
        ];
        
        if (isset($mime_types[$extension])) {
            header('Content-Type: ' . $mime_types[$extension]);
            readfile($file_path);
            exit;
        }
    }
    // If file not found or mime type not recognized, potentially fall through or show 404
    // Adding a simple 404 here to avoid sending HTML for missing static assets
    http_response_code(404);
    echo "404 Not Found";
    exit;
}

// Get the request URI and remove the base path
$request = $_SERVER['REQUEST_URI'];

// Remove query string if present
$request = strtok($request, '?');

// Remove base path and trailing slash
$path = rtrim(str_replace($basePath, '', $request), '/');

// Default to '/' if path is empty
$path = empty($path) ? '/' : '/' . ltrim($path, '/');

// Remove the debug lines
// echo "Request URI: " . $request . "<br>";
// echo "Path after processing: " . $path . "<br>";

switch ($path) {
    case '/':
        require ROOT_PATH . '/views/home.php';
        break;
        
    case '/tutors':
        require ROOT_PATH . '/views/tutors/list.php';
        break;
        
    case '/login':
        require ROOT_PATH . '/views/auth/login.php';
        break;
        
    case '/register':
        require ROOT_PATH . '/views/auth/register.php';
        break;
        
    case '/messages':
        require ROOT_PATH . '/views/messages/index.php';
        break;
        
    case '/requests':
        if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'tutor') {
            header('Location: ' . $basePath . '/');
            exit;
        }
        require ROOT_PATH . '/views/tutors/requests.php';
        break;
        
    case '/logout':
        $auth = new Auth($db);
        $auth->logout();
        header('Location: ' . $basePath . '/login');
        exit;
        break;
        
    // For viewing individual tutor profiles
    case (preg_match('/^\/tutors\/view\/(\d+)$/', $path, $matches) ? true : false):
        require ROOT_PATH . '/views/tutors/view.php';
        break;
        
    // For handling tutor requests
    case '/tutors/request':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!isset($_SESSION['user_id'])) {
                $_SESSION['error'] = 'Please login first';
                header('Location: ' . $basePath . '/login');
                exit;
            }

            if ($_POST['action'] === 'request_tutor') {
                $tutor = new Tutor($db);
                // Add validation for required fields
                if (empty($_POST['tutor_id']) || empty($_POST['message'])) {
                    $_SESSION['error'] = 'Missing required fields';
                    header('Location: ' . $basePath . '/tutors');
                    exit;
                }

                // Add error logging
                try {
                    if ($tutor->createRequest($_SESSION['user_id'], $_POST['tutor_id'], $_POST['message'])) {
                        $_SESSION['success'] = 'Request sent successfully!';
                    } else {
                        $_SESSION['error'] = 'Failed to send request';
                    }
                } catch (Exception $e) {
                    error_log($e->getMessage());
                    $_SESSION['error'] = 'An error occurred while processing your request';
                }
                header('Location: ' . $basePath . '/tutors/view/' . $_POST['tutor_id']);
                exit;
            }
        }
        break;
        
    // For auth handlers
    case '/auth/handlers.php':
        require ROOT_PATH . '/public/auth/handlers.php';
        break;
        
    case '/messages/send':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!isset($_SESSION['user_id'])) {
                header('Location: ' . $basePath . '/login');
                exit;
            }

            $message = new Message($db);
            if ($message->send($_SESSION['user_id'], $_POST['receiver_id'], $_POST['message'])) {
                $_SESSION['success'] = 'Message sent successfully!';
            } else {
                $_SESSION['error'] = 'Failed to send message';
            }
            
            // Return to the previous page instead of redirecting to messages
            if (isset($_SERVER['HTTP_REFERER'])) {
                header('Location: ' . $_SERVER['HTTP_REFERER']);
            } else {
                header('Location: ' . $basePath . '/messages/new?receiver_id=' . $_POST['receiver_id']);
            }
            exit;
        }
        break;
        
    case '/my-requests':
        if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'student') {
            header('Location: ' . $basePath . '/');
            exit;
        }
        require ROOT_PATH . '/views/students/requests.php';
        break;
        
    // Add this case to your switch statement
    case (preg_match('/^\/messages\/view\/(\d+)$/', $path, $matches) ? true : false):
        require ROOT_PATH . '/views/messages/view.php';
        break;
        
    case '/tutors/update-profile':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'tutor') {
                $_SESSION['error'] = 'Unauthorized access';
                header('Location: ' . $basePath . '/');
                exit;
            }
        

            $tutor = new Tutor($db);
            $expertise_area = $_POST['expertise_area'] ?? '';
            $description = $_POST['description'] ?? '';
            
            if ($tutor->updateProfile($_SESSION['user_id'], $expertise_area, $description)) {
                $_SESSION['success'] = 'Profile updated successfully!';
            } else {
                $_SESSION['error'] = 'Failed to update profile';
            }
            
            header('Location: ' . $basePath . '/profile');
            exit;
        }
        break;
        
    case '/my-students':
        if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'tutor') {
            header('Location: ' . $basePath . '/');
            exit;
        }
        require ROOT_PATH . '/views/tutors/my_students.php';
        break;
        
    case '/my-tutors':
        if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'student') {
            header('Location: ' . $basePath . '/');
            exit;
        }
        require ROOT_PATH . '/views/students/my_tutors.php';
        break;
        
    case '/tutors/request/update':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'tutor') {
                $_SESSION['error'] = 'Unauthorized access';
                header('Location: ' . $basePath . '/');
                exit;
            }

            $tutor = new Tutor($db);
            if ($tutor->updateRequestStatus($_POST['request_id'], $_POST['status'])) {
                $_SESSION['success'] = 'Request ' . $_POST['status'] . ' successfully!';
                // Redirect to my-students page only if request was accepted
                if ($_POST['status'] === 'accepted') {
                    header('Location: ' . $basePath . '/my-students');
                } else {
                    header('Location: ' . $basePath . '/requests');
                }
            } else {
                $_SESSION['error'] = 'Failed to update request status';
                header('Location: ' . $basePath . '/requests');
            }
            exit;
        }
        break;
        
    case (preg_match('/^\/messages\/new(?:\/(\d+))?$/', $path, $matches) ? true : false):
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . $basePath . '/login');
            exit;
        }
        
        // Get receiver_id from either URL parameter or query string
        $receiver_id = $matches[1] ?? $_GET['receiver_id'] ?? null;
        
        if (!$receiver_id) {
            $_SESSION['error'] = 'Invalid recipient';
            header('Location: ' . $basePath . '/messages');
            exit;
        }
        
        require ROOT_PATH . '/views/messages/new.php';
        break;
        
    case '/profile/edit':
        require_once ROOT_PATH . '/controllers/ProfileController.php';
        $profileController = new ProfileController($db);
        $profileController->edit();
        break;
        
    case '/profile/update':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_once ROOT_PATH . '/controllers/ProfileController.php';
            $profileController = new ProfileController($db);
            $profileController->update();
        }
        break;
        
    case (preg_match('/^\/uploads\/profile_images\/(.+)$/', $path, $matches) ? true : false):
        $filename = $matches[1];
        $filepath = dirname(__DIR__) . '/uploads/profile_images/' . $filename;
        if (file_exists($filepath)) {
            $extension = pathinfo($filepath, PATHINFO_EXTENSION);
            $contentTypes = [
                'jpg' => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'png' => 'image/png',
                'gif' => 'image/gif'
            ];
            $contentType = $contentTypes[$extension] ?? 'application/octet-stream';
            header('Content-Type: ' . $contentType);
            readfile($filepath);
        } else {
            header('HTTP/1.0 404 Not Found');
            echo '404 Not Found';
        }
        exit;
        
    default:
        http_response_code(404);
        require ROOT_PATH . '/views/404.php';
        break;
}

try {
    // Test database connection
    $database = new Database();
    $db = $database->getConnection();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?> 