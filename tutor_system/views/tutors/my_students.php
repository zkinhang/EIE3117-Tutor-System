<?php 
require_once ROOT_PATH . '/views/layout/header.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'tutor') {
    header('Location: ' . $basePath . '/');
    exit;
}

$tutor = new Tutor($db);
$accepted_students = $tutor->getAcceptedStudents($_SESSION['user_id']);
?>

<div class="container">
    <h2 class="mb-4">My Students</h2>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <?php 
            echo $_SESSION['success'];
            unset($_SESSION['success']);
            ?>
        </div>
    <?php endif; ?>

    <?php if (empty($accepted_students)): ?>
        <div class="alert alert-info">
            You don't have any accepted students yet.
        </div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($accepted_students as $student): ?>
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($student['student_name']); ?></h5>
                            <p class="card-text">
                                <strong>Initial Request:</strong><br>
                                <?php echo nl2br(htmlspecialchars($student['request_message'])); ?>
                            </p>
                            <p class="text-muted">
                                Student since: <?php echo date('Y-m-d', strtotime($student['request_date'])); ?>
                            </p>
                            <div class="mt-3">
                                <a href="<?php echo $basePath; ?>/messages/new?receiver_id=<?php echo $student['student_id']; ?>" 
                                   class="btn btn-primary">
                                    Message Student
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