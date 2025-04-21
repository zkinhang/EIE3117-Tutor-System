<?php require_once ROOT_PATH . '/views/layout/header.php'; ?>

<div class="container">
    <div class="row">
        <div class="col-md-12 text-center">
            <h1>Welcome to Tutor Matching System</h1>
            <?php if (!isset($_SESSION['user_id'])): ?>
                <p>Please <a href="/tutor_system/public/login">login</a> or <a href="/tutor_system/public/register">register</a> to get started.</p>
            <?php endif; ?>
        </div>
    </div>
    
    <?php if (isset($_SESSION['user_id'])): ?>
        <!-- User Profile Section -->
        <div class="row mt-4">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body text-center">
                        <div class="user-info">
                            <!-- Profile Image -->
                            <div class="profile-image mb-3">
                                <?php if (isset($_SESSION['profile_image']) && $_SESSION['profile_image']): ?>
                                    <img src="<?php echo str_replace('/public', '', $basePath) . $_SESSION['profile_image']; ?>" 
                                         alt="Profile" 
                                         class="rounded-circle"
                                         style="width: 100px; height: 100px; object-fit: cover;">
                                <?php else: ?>
                                    <img src="<?php echo $basePath; ?>/images/default-avatar.png" 
                                         alt="Default Profile" 
                                         class="rounded-circle"
                                         style="width: 100px; height: 100px; object-fit: cover;">
                                <?php endif; ?>
                            </div>
                            <!-- User Name -->
                            <h4><?php echo isset($_SESSION['nickname']) ? htmlspecialchars($_SESSION['nickname']) : 'User'; ?></h4>
                            <!-- Edit Profile Button -->
                            <a href="<?php echo $basePath; ?>/profile/edit" class="btn btn-primary">Edit Profile</a>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-8">
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <h5 class="card-title">Find Tutors</h5>
                                <p class="card-text">Browse our list of qualified tutors.</p>
                                <a href="<?php echo $basePath; ?>/tutors" class="btn btn-primary">Find Tutors</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <h5 class="card-title">Messages</h5>
                                <p class="card-text">Check your messages and requests.</p>
                                <a href="<?php echo $basePath; ?>/messages" class="btn btn-primary">View Messages</a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <?php if ($_SESSION['user_type'] === 'tutor'): ?>
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Tutor Dashboard</h5>
                            <p class="card-text">Manage your tutoring requests and students.</p>
                            <a href="<?php echo $basePath; ?>/tutors/requests" class="btn btn-primary">View Requests</a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once ROOT_PATH . '/views/layout/footer.php'; ?> 