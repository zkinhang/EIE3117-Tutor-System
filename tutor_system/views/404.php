<?php 
$basePath = '/tutor_system/public';
require_once ROOT_PATH . '/views/layout/header.php'; 
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6 text-center">
            <h1 class="display-1">404</h1>
            <h2>Page Not Found</h2>
            <p>The page you are looking for does not exist.</p>
            <a href="<?php echo $basePath; ?>/" class="btn btn-primary">Go Home</a>
        </div>
    </div>
</div>

<?php require_once ROOT_PATH . '/views/layout/footer.php'; ?> 