<?php
require_once ROOT_PATH . '/config/init.php';
require_once ROOT_PATH . '/includes/database.php';
require_once ROOT_PATH . '/includes/auth.php';
require_once ROOT_PATH . '/controllers/ProfileController.php';

// Define base path
$basePath = '/tutor_system/public';

class Router {
    private $routes = [];
    private $basePath;

    public function __construct($basePath) {
        $this->basePath = $basePath;
    }

    public function addRoute($method, $path, $handler) {
        $this->routes[] = [
            'method' => $method,
            'path' => $this->basePath . $path,
            'handler' => $handler
        ];
    }

    public function handleRequest($method, $path) {
        // Debug information
        error_log("Request Method: " . $method);
        error_log("Request Path: " . $path);
        error_log("Base Path: " . $this->basePath);
        error_log("Available Routes:");
        foreach ($this->routes as $route) {
            error_log("  " . $route['method'] . " " . $route['path']);
        }

        foreach ($this->routes as $route) {
            if ($route['method'] === $method && $route['path'] === $path) {
                $route['handler']();
                return;
            }
        }

        // No route found
        header("HTTP/1.0 404 Not Found");
        echo "404 Page Not Found";
        echo "<br>The page you are looking for does not exist.";
        echo "<br>Requested path: " . $path;
        echo "<br>Available routes:";
        echo "<pre>";
        foreach ($this->routes as $route) {
            echo $route['method'] . " " . $route['path'] . "\n";
        }
        echo "</pre>";
    }
}

// Initialize router
$router = new Router($basePath);

// Auth routes
$router->addRoute('GET', '/login', function() use ($db) {
    require_once ROOT_PATH . '/views/auth/login.php';
});

$router->addRoute('GET', '/register', function() use ($db) {
    require_once ROOT_PATH . '/views/auth/register.php';
});

$router->addRoute('POST', '/auth/handlers.php', function() use ($db) {
    require_once ROOT_PATH . '/public/auth/handlers.php';
});

// Home route
$router->addRoute('GET', '/', function() use ($db) {
    require_once ROOT_PATH . '/views/home.php';
});

// Profile routes
$router->addRoute('GET', '/profile/edit', function() use ($db) {
    $profileController = new ProfileController($db);
    $profileController->edit();
});

$router->addRoute('POST', '/profile/update', function() use ($db) {
    $profileController = new ProfileController($db);
    $profileController->update();
});

// Tutor routes
$router->addRoute('GET', '/tutors', function() use ($db) {
    require_once ROOT_PATH . '/controllers/TutorController.php';
    $controller = new TutorController($db);
    $controller->index();
});

$router->addRoute('GET', '/tutors/view', function() use ($db) {
    require_once ROOT_PATH . '/controllers/TutorController.php';
    $controller = new TutorController($db);
    $controller->view();
});

// Handle the request
$router->handleRequest($_SERVER['REQUEST_METHOD'], parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
?> 