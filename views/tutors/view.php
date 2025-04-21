<?php 
require_once ROOT_PATH . '/views/layout/header.php';

// Get tutor ID from URL
$tutor_id = isset($matches[1]) ? $matches[1] : null;

if (!$tutor_id) {
    header('Location: ' . $basePath . '/tutors');
    exit;
}

$tutor = new Tutor($db);
$tutor_data = $tutor->getTutorById($tutor_id);

if (!$tutor_data) {
    header('Location: ' . $basePath . '/tutors');
    exit;
}

// Get tutor's availability
$availability = $tutor->getTutorAvailability($tutor_id);

// Get tutor's ratings and reviews
$ratings = $tutor->getTutorRatings($tutor_id);
$average_rating = !empty($ratings) ? array_sum(array_column($ratings, 'rating')) / count($ratings) : 0;

// Check if there's an existing request
$existing_request = null;
if (isset($_SESSION['user_id']) && $_SESSION['user_type'] === 'student') {
    $existing_request = $tutor->getRequestStatus($_SESSION['user_id'], $tutor_id);
}
?>

<div class="container">
    <?php 
    display_flash_message('error');
    display_flash_message('success');
    ?>
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-body">
                    <!-- Tutor Profile Header -->
                    <div class="text-center mb-4">
                        <!-- Profile Image -->
                        <?php if (isset($tutor_data['profile_image']) && $tutor_data['profile_image']): ?>
                            <img src="<?php echo str_replace('/public', '', $basePath) . $tutor_data['profile_image']; ?>" 
                                 alt="Tutor Profile" 
                                 class="rounded-circle mb-3"
                                 style="width: 200px; height: 200px; object-fit: cover;">
                        <?php else: ?>
                            <img src="<?php echo $basePath; ?>/images/default-avatar.png" 
                                 alt="Default Profile" 
                                 class="rounded-circle mb-3"
                                 style="width: 200px; height: 200px; object-fit: cover;">
                        <?php endif; ?>
                        <h2><?php echo htmlspecialchars($tutor_data['nickname']); ?></h2>
                    </div>
                    <!-- Rest of the tutor profile content -->
                    <div class="row">
                        <div class="col-md-4 text-center">
                            <div class="rating mb-3">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="fas fa-star <?php echo $i <= $average_rating ? 'text-warning' : 'text-muted'; ?>"></i>
                                <?php endfor; ?>
                                <span class="ms-2">(<?php echo count($ratings); ?> reviews)</span>
                            </div>
                        </div>
                        
                        <div class="col-md-8">
                            <div class="mb-3">
                                <h5>Expertise Areas</h5>
                                <p class="mb-0"><?php echo nl2br(htmlspecialchars($tutor_data['expertise_area'] ?? 'Not specified')); ?></p>
                            </div>
                            
                            <div class="mb-3">
                                <h5>About Me</h5>
                                <p class="mb-0"><?php echo nl2br(htmlspecialchars($tutor_data['description'] ?? 'No description available')); ?></p>
                            </div>
                            
                            <?php if (!empty($availability)): ?>
                                <div class="mb-3">
                                    <h5>Availability</h5>
                                    <ul class="list-unstyled mb-0">
                                        <?php foreach ($availability as $day => $times): ?>
                                            <li>
                                                <strong><?php echo $day; ?>:</strong> 
                                                <?php echo implode(', ', $times); ?>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Reviews Section -->
            <?php if (!empty($ratings)): ?>
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Reviews</h5>
                    </div>
                    <div class="card-body">
                        <?php foreach ($ratings as $rating): ?>
                            <div class="border-bottom pb-3 mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong><?php echo htmlspecialchars($rating['student_name']); ?></strong>
                                        <div class="rating">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <i class="fas fa-star <?php echo $i <= $rating['rating'] ? 'text-warning' : 'text-muted'; ?>"></i>
                                            <?php endfor; ?>
                                        </div>
                                    </div>
                                    <small class="text-muted">
                                        <?php echo date('M d, Y', strtotime($rating['created_at'])); ?>
                                    </small>
                                </div>
                                <p class="mb-0 mt-2"><?php echo h_br($rating['review']); ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Action Buttons -->
            <div class="card">
                <div class="card-body">
                    <?php if (isset($_SESSION['user_id']) && $_SESSION['user_type'] === 'student'): ?>
                        <?php if ($existing_request): ?>
                            <div class="alert alert-info">
                                <h5 class="alert-heading">Request Status</h5>
                                <p class="mb-0">
                                    Your request is currently <strong><?php echo ucfirst($existing_request['status']); ?></strong>.
                                    <?php if ($existing_request['status'] === 'pending'): ?>
                                        The tutor will review your request and get back to you soon.
                                    <?php endif; ?>
                                </p>
                            </div>
                        <?php else: ?>
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#requestModal">
                                Request Tutor
                            </button>
                        <?php endif; ?>

                        <a href="<?php echo $basePath; ?>/messages/new/<?php echo htmlspecialchars($tutor_data['id']); ?>" 
                           class="btn btn-secondary">
                            Send Message
                        </a>
                    <?php endif; ?>
                    
                    <a href="<?php echo $basePath; ?>/tutors" 
                       class="btn btn-outline-secondary">
                        Back to Tutors List
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Request Modal -->
<?php if (isset($_SESSION['user_id']) && $_SESSION['user_type'] === 'student' && !$existing_request): ?>
<div class="modal fade" id="requestModal" tabindex="-1" aria-labelledby="requestModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?php echo $basePath; ?>/tutors/request" method="POST">
                <input type="hidden" name="action" value="request_tutor">
                <input type="hidden" name="tutor_id" value="<?php echo htmlspecialchars($tutor_id); ?>">
                
                <div class="modal-header">
                    <h5 class="modal-title" id="requestModalLabel">Request Tutor</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="subjects" class="form-label">Subjects You Need Help With</label>
                        <textarea class="form-control" id="subjects" name="subjects" rows="2" required
                                placeholder="List the subjects or topics you need help with"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="schedule" class="form-label">Preferred Schedule</label>
                        <textarea class="form-control" id="schedule" name="schedule" rows="2" required
                                placeholder="When would you like to have tutoring sessions?"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="goals" class="form-label">Your Goals</label>
                        <textarea class="form-control" id="goals" name="goals" rows="2" required
                                placeholder="What do you want to achieve through tutoring?"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="message" class="form-label">Additional Message</label>
                        <textarea class="form-control" id="message" name="message" rows="3"
                                placeholder="Any other information you'd like to share with the tutor"></textarea>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Send Request</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<?php require_once ROOT_PATH . '/views/layout/footer.php'; ?> 