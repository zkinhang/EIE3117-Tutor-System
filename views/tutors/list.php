<?php 
require_once ROOT_PATH . '/views/layout/header.php';
$tutor = new Tutor($db);

// Get search and filter parameters
$search = isset($_GET['search']) ? $_GET['search'] : '';
$expertise = isset($_GET['expertise']) ? $_GET['expertise'] : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 12;

// Get filtered tutors with pagination
$tutors = $tutor->getAllTutors($search, $expertise, $page, $per_page);
$total_tutors = $tutor->getTotalTutors($search, $expertise);
$total_pages = ceil($total_tutors / $per_page);

// Get unique expertise areas for filter
$expertise_areas = $tutor->getExpertiseAreas();
?>

<div class="container">
    <h2 class="mb-4">Available Tutors</h2>
    
    <!-- Search and Filter Form -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-6">
                    <input type="text" name="search" class="form-control" 
                           placeholder="Search tutors..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <div class="col-md-4">
                    <select name="expertise" class="form-select">
                        <option value="">All Expertise Areas</option>
                        <?php foreach ($expertise_areas as $area): ?>
                            <option value="<?php echo htmlspecialchars($area); ?>" 
                                    <?php echo $expertise === $area ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($area); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Search</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Tutors Grid -->
    <div class="row">
        <?php if (empty($tutors)): ?>
            <div class="col-12">
                <div class="alert alert-info">
                    No tutors found matching your criteria.
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($tutors as $tutor): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <!-- Updated Profile Image Section -->
                            <div class="profile-image mb-3">
                                <?php if (isset($tutor['profile_image']) && $tutor['profile_image']): ?>
                                    <img src="/uploads/profile_images/<?php echo basename($tutor['profile_image']); ?>" 
                                         alt="Profile Image">
                                <?php else: ?>
                                    <img src="<?php echo $basePath; ?>/images/default-avatar.png" 
                                         alt="Default Profile">
                                <?php endif; ?>
                            </div>
                            
                            <h5 class="card-title"><?php echo htmlspecialchars($tutor['nickname']); ?></h5>
                            <p class="card-text">
                                <strong>Expertise:</strong><br>
                                <?php echo htmlspecialchars($tutor['expertise_area'] ?? 'Not specified'); ?>
                            </p>
                            <p class="card-text">
                                <?php 
                                if (isset($tutor['description'])) {
                                    echo htmlspecialchars(substr($tutor['description'], 0, 100)) . 
                                         (strlen($tutor['description']) > 100 ? '...' : '');
                                } else {
                                    echo 'No description available';
                                }
                                ?>
                            </p>
                            <a href="<?php echo $basePath; ?>/tutors/view/<?php echo $tutor['id']; ?>" 
                               class="btn btn-primary">
                                View Profile
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    
    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
        <nav aria-label="Page navigation" class="mt-4">
            <ul class="pagination justify-content-center">
                <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?php echo $page-1; ?>&search=<?php echo urlencode($search); ?>&expertise=<?php echo urlencode($expertise); ?>">
                            Previous
                        </a>
                    </li>
                <?php endif; ?>
                
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&expertise=<?php echo urlencode($expertise); ?>">
                            <?php echo $i; ?>
                        </a>
                    </li>
                <?php endfor; ?>
                
                <?php if ($page < $total_pages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?php echo $page+1; ?>&search=<?php echo urlencode($search); ?>&expertise=<?php echo urlencode($expertise); ?>">
                            Next
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    <?php endif; ?>
</div>

<?php require_once ROOT_PATH . '/views/layout/footer.php'; ?> 