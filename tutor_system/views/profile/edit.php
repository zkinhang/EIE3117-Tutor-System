<?php require_once ROOT_PATH . '/views/layout/header.php'; ?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <?php 
            display_flash_message('error');
            display_flash_message('success');
            ?>

            <div class="card">
                <div class="card-header">
                    <h2>My Profile</h2>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?php echo $basePath; ?>/profile/update" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                        <!-- Profile Image Section -->
                        <div class="text-center mb-3">
                            <?php if (isset($user['profile_image']) && $user['profile_image']): ?>
                                <img src="<?php echo str_replace('/public', '', $basePath) . $user['profile_image']; ?>" 
                                     alt="Current Profile" 
                                     class="rounded-circle"
                                     style="width: 150px; height: 150px; object-fit: cover;">
                            <?php else: ?>
                                <img src="<?php echo $basePath; ?>/images/default-avatar.png" 
                                     alt="Default Profile" 
                                     class="rounded-circle"
                                     style="width: 150px; height: 150px; object-fit: cover;">
                            <?php endif; ?>
                            <div class="mt-2">
                                <input type="file" name="profile_image" id="profile_image" class="form-control" accept="image/*">
                                <small class="text-muted">Maximum file size: 2MB. Supported formats: JPG, PNG, GIF</small>
                            </div>
                        </div>

                        <!-- Basic Info -->
                        <div class="mb-3">
                            <label class="form-label">Nickname</label>
                            <input type="text" name="nickname" class="form-control" 
                                   value="<?php echo htmlspecialchars($user['nickname'] ?? ''); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" 
                                   value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Age</label>
                            <input type="number" name="age" class="form-control" 
                                   value="<?php echo htmlspecialchars($user['age'] ?? ''); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Gender</label>
                            <select name="gender" class="form-control" required>
                                <option value="male" <?php echo ($user['gender'] ?? '') === 'male' ? 'selected' : ''; ?>>Male</option>
                                <option value="female" <?php echo ($user['gender'] ?? '') === 'female' ? 'selected' : ''; ?>>Female</option>
                                <option value="other" <?php echo ($user['gender'] ?? '') === 'other' ? 'selected' : ''; ?>>Other</option>
                            </select>
                        </div>

                        <!-- Tutor-specific fields -->
                        <?php if (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'tutor'): ?>
                            <div class="mb-3">
                                <label for="expertise_area" class="form-label">List your areas of expertise, separated by commas</label>
                                <input type="text" class="form-control" id="expertise_area" name="expertise_area" 
                                       value="<?php echo htmlspecialchars($user['expertise_area'] ?? ''); ?>">
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">About Me / Description</label>
                                <textarea class="form-control" id="description" name="description" 
                                          rows="5" placeholder="Describe your teaching experience and approach"
                                ><?php echo htmlspecialchars($user['description'] ?? ''); ?></textarea>
                            </div>
                        <?php endif; ?>

                        <div class="text-center">
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once ROOT_PATH . '/views/layout/footer.php'; ?> 