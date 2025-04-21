<div class="row">
    <?php foreach ($tutors as $tutor): ?>
        <div class="col-md-4 mb-4">
            <div class="card">
                <div class="card-body text-center">
                    <!-- Profile Image -->
                    <?php if (isset($tutor['profile_image']) && $tutor['profile_image']): ?>
                        <img src="<?php echo str_replace('/public', '', $basePath) . $tutor['profile_image']; ?>" 
                             alt="Tutor Profile" 
                             class="rounded-circle mb-3"
                             style="width: 150px; height: 150px; object-fit: cover;">
                    <?php else: ?>
                        <img src="<?php echo $basePath; ?>/images/default-avatar.png" 
                             alt="Default Profile" 
                             class="rounded-circle mb-3"
                             style="width: 150px; height: 150px; object-fit: cover;">
                    <?php endif; ?>
                    <h5 class="card-title"><?php echo htmlspecialchars($tutor['nickname']); ?></h5>
                    <!-- Rest of the tutor card content -->
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div> 