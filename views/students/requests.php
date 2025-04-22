<?php 
require_once ROOT_PATH . '/views/layout/header.php';

// Only allow students to access this page
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'student') {
    header('Location: ' . $basePath . '/');
    exit;
}

$tutor = new Tutor($db);
$requests = $tutor->getStudentRequests($_SESSION['user_id']);
?>

<div class="container">
    <h2 class="mb-4">My Tutor Requests</h2>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <?php 
            echo $_SESSION['success'];
            unset($_SESSION['success']);
            ?>
        </div>
    <?php endif; ?>

    <?php if (empty($requests)): ?>
        <div class="alert alert-info">
            You haven't made any tutor requests yet.
        </div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($requests as $request): ?>
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">
                                Request to <?php echo htmlspecialchars($request['tutor_nickname']); ?>
                            </h5>
                            <p class="card-text">
                                <strong>Your Message:</strong><br>
                                <?php echo nl2br(htmlspecialchars($request['message'])); ?>
                            </p>
                            <p class="text-muted">
                                Requested on: <?php echo date('Y-m-d H:i', strtotime($request['created_at'])); ?>
                            </p>
                            
                            <div class="mt-2">
                                <span class="badge bg-<?php 
                                    echo $request['status'] === 'pending' ? 'warning' : 
                                         ($request['status'] === 'accepted' ? 'success' : 'danger'); 
                                ?>">
                                    <?php echo ucfirst($request['status']); ?>
                                </span>
                            </div>
                            
                            <?php if ($request['status'] === 'accepted'): ?>
                                <div class="mt-3">
                                    <a href="<?php echo $basePath; ?>/messages/new/<?php echo $request['tutor_id']; ?>" 
                                       class="btn btn-primary">
                                        Message Tutor
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once ROOT_PATH . '/views/layout/footer.php'; ?> 