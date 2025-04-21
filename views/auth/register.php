<?php require_once ROOT_PATH . '/views/layout/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <?php 
        display_flash_message('error');
        display_flash_message('success');
        ?>

        <div class="card">
            <div class="card-header">Register</div>
            <div class="card-body">
                <form method="POST" action="<?php echo $basePath; ?>/auth/handlers.php" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="register">
                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                    
                    <!-- Profile Image Upload -->
                    <div class="mb-3">
                        <label>Profile Image</label>
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <img id="preview" src="<?php echo $basePath; ?>/images/default-avatar.png" 
                                     class="rounded-circle" alt="Profile Preview" 
                                     style="width: 100px; height: 100px; object-fit: cover;">
                            </div>
                            <div class="flex-grow-1">
                                <input type="file" name="profile_image" id="profile_image" class="form-control" 
                                       accept="image/*" onchange="previewImage(this)">
                                <small class="text-muted">Recommended size: 200x200 pixels. Max size: 2MB</small>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label>Login ID</label>
                        <input type="text" name="login_id" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Nickname</label>
                        <input type="text" name="nickname" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Age</label>
                        <input type="number" name="age" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Gender</label>
                        <select name="gender" class="form-control" required>
                            <option value="M">Male</option>
                            <option value="F">Female</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>User Type</label>
                        <select name="user_type" class="form-control" required>
                            <option value="student">Student</option>
                            <option value="tutor">Tutor</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Register</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function previewImage(input) {
    const preview = document.getElementById('preview');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
        }
        reader.readAsDataURL(input.files[0]);
    }
}
</script>

<?php require_once ROOT_PATH . '/views/layout/footer.php'; ?> 