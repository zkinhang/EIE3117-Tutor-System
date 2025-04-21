<?php 
require_once ROOT_PATH . '/views/layout/header.php';

// Only allow tutors to access this page
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'tutor') {
    header('Location: ' . $basePath . '/');
    exit;
}

$tutor = new Tutor($db);
$requests = $tutor->getTutorRequests($_SESSION['user_id']);
?>

<div class="container">
    <h2 class="mb-4">Student Requests</h2>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <?php 
            echo $_SESSION['success'];
            unset($_SESSION['success']);
            ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger">
            <?php 
            echo $_SESSION['error'];
            unset($_SESSION['error']);
            ?>
        </div>
    <?php endif; ?>

    <?php if (empty($requests)): ?>
        <div class="alert alert-info">
            No requests at the moment.
        </div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($requests as $request): ?>
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">
                                Request from <?php echo htmlspecialchars($request['student_nickname']); ?>
                            </h5>
                            <p class="card-text">
                                <strong>Message:</strong><br>
                                <?php echo nl2br(htmlspecialchars($request['message'])); ?>
                            </p>
                            <p class="text-muted">
                                Requested on: <?php echo date('Y-m-d H:i', strtotime($request['created_at'])); ?>
                            </p>
                            
                            <?php if ($request['status'] === 'pending'): ?>
                                <div class="mt-3">
                                    <form action="<?php echo $basePath; ?>/tutors/request/update" method="POST" class="d-inline">
                                        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                                        <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                                        <input type="hidden" name="status" value="accepted">
                                        <button type="submit" class="btn btn-success">Accept</button>
                                    </form>
                                    
                                    <form action="<?php echo $basePath; ?>/tutors/request/update" method="POST" class="d-inline">
                                        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                                        <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                                        <input type="hidden" name="status" value="rejected">
                                        <button type="submit" class="btn btn-danger">Reject</button>
                                    </form>
                                </div>
                            <?php else: ?>
                                <div class="mt-2">
                                    <span class="badge bg-<?php 
                                        echo $request['status'] === 'accepted' ? 'success' : 'danger'; 
                                    ?>">
                                        <?php echo ucfirst($request['status']); ?>
                                    </span>
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