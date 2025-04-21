<?php 
require_once ROOT_PATH . '/views/layout/header.php';

// Get receiver ID from URL
$receiver_id = isset($matches[1]) ? $matches[1] : null;

if (!$receiver_id) {
    header('Location: ' . $basePath . '/messages');
    exit;
}

// Get receiver info
$user = new User($db);
$receiver = $user->getUserById($receiver_id);

if (!$receiver) {
    header('Location: ' . $basePath . '/messages');
    exit;
}
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3>Send Message to <?php echo h($receiver['nickname']); ?></h3>
                </div>
                <div class="card-body">
                    <?php 
                    display_flash_message('error');
                    display_flash_message('success');
                    ?>

                    <form action="<?php echo $basePath; ?>/messages/send" method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                        <input type="hidden" name="receiver_id" value="<?php echo htmlspecialchars($receiver_id); ?>">
                        
                        <div class="mb-3">
                            <label for="message" class="form-label">Message</label>
                            <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Send Message</button>
                        <a href="<?php echo $basePath; ?>/messages" class="btn btn-secondary">Back to Messages</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once ROOT_PATH . '/views/layout/footer.php'; ?> 