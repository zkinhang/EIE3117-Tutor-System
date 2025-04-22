<div class="row">
    <?php foreach ($tutors as $tutor): ?>
        <div class="col-md-4 mb-4">
            <div class="card">
                <div class="card-body text-center">
                    <!-- Profile Image -->
                    <div class="profile-image mb-3">
                        <?php if (isset($tutor['profile_image']) && $tutor['profile_image']): ?>
                            <img src="/uploads/profile_images/<?php echo basename($tutor['profile_image']); ?>" 
                                 alt="Tutor Profile">
                        <?php else: ?>
                            <img src="<?php echo $basePath; ?>/images/default-avatar.png" 
                                 alt="Default Profile">
                        <?php endif; ?>
                    </div>
                    <h5 class="card-title"><?php echo htmlspecialchars($tutor['nickname']); ?></h5>
                    <!-- Rest of the tutor card content -->
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div> 