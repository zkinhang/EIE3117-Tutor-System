<?php 
require_once ROOT_PATH . '/views/layout/header.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'student') {
    header('Location: ' . $basePath . '/');
    exit;
}

$tutor = new Tutor($db);
$my_tutors = $tutor->getMyTutors($_SESSION['user_id']);
?>

<div class="container">
    <h2 class="mb-4">My Tutors</h2>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <?php 
            echo $_SESSION['success'];
            unset($_SESSION['success']);
            ?>
        </div>
    <?php endif; ?>

    <?php if (empty($my_tutors)): ?>
        <div class="alert alert-info">
            You don't have any accepted tutors yet. 
            <a href="<?php echo $basePath; ?>/tutors">Find tutors here!</a>
        </div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($my_tutors as $tutor): ?>
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($tutor['tutor_name']); ?></h5>
                            <p class="card-text">
                                <strong>Expertise:</strong><br>
                                <?php echo htmlspecialchars($tutor['expertise_area'] ?? 'Not specified'); ?>
                            </p>
                            <p class="card-text">
                                <small class="text-muted">Connected since: <?php echo date('Y-m-d', strtotime($tutor['request_date'])); ?></small>
                            </p>
                            <div class="mt-3">
                                <a href="<?php echo $basePath; ?>/messages/new?receiver_id=<?php echo $tutor['tutor_id']; ?>" 
                                   class="btn btn-primary">
                                    Message Tutor
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once ROOT_PATH . '/views/layout/footer.php'; ?> 