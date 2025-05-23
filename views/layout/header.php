<?php 
$basePath = '/tutor_system/public';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Update Content Security Policy -->
    <meta http-equiv="Content-Security-Policy" content="
        default-src 'self';
        style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net;
        script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net;
        img-src 'self' data: https:;
    ">
    
    <!-- Make sure CSS is loaded correctly -->
    <link rel="stylesheet" type="text/css" href="<?php echo $basePath; ?>/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">

</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container">
            <a class="navbar-brand" href="<?php echo $basePath; ?>/">Tutor Matching</a>
            <div class="navbar-nav">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a class="nav-link" href="<?php echo $basePath; ?>/tutors">Find Tutors</a>
                    <?php if ($_SESSION['user_type'] === 'tutor'): ?>
                        <a class="nav-link" href="<?php echo $basePath; ?>/profile/edit">My Profile</a>
                        <a class="nav-link" href="<?php echo $basePath; ?>/my-students">My Students</a>
                        <a class="nav-link" href="<?php echo $basePath; ?>/requests">View Requests</a>
                    <?php else: ?>
                        <a class="nav-link" href="<?php echo $basePath; ?>/profile/edit">My Profile</a>
                        <a class="nav-link" href="<?php echo $basePath; ?>/my-tutors">My Tutors</a>
                        <a class="nav-link" href="<?php echo $basePath; ?>/my-requests">My Requests</a>
                    <?php endif; ?>
                    <a class="nav-link" href="<?php echo $basePath; ?>/messages">Messages</a>
                    <a class="nav-link" href="<?php echo $basePath; ?>/logout">Logout</a>
                <?php else: ?>
                    <a class="nav-link" href="<?php echo $basePath; ?>/login">Login</a>
                    <a class="nav-link" href="<?php echo $basePath; ?>/register">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
    <div class="container mt-4"> 
