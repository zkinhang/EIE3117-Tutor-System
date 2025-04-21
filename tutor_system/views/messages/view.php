<?php 
require_once ROOT_PATH . '/views/layout/header.php';

// Get message ID from URL
$message_id = isset($matches[1]) ? $matches[1] : null;

if (!$message_id) {
    header('Location: ' . $basePath . '/messages');
    exit;
}

$message = new Message($db);
$msg_data = $message->getMessageById($message_id);

// Check if message exists and user has permission to view it
if (!$msg_data || ($msg_data['sender_id'] !== $_SESSION['user_id'] && $msg_data['receiver_id'] !== $_SESSION['user_id'])) {
    header('Location: ' . $basePath . '/messages');
    exit;
}

// Mark message as read if user is the receiver
if ($msg_data['receiver_id'] === $_SESSION['user_id'] && $msg_data['status'] === 'unread') {
    $message->markAsRead($message_id);
    $msg_data['status'] = 'read';
}

// Get conversation history
$conversation = $message->getConversation($msg_data['sender_id'], $msg_data['receiver_id']);
?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <?php 
                    echo $_SESSION['success'];
                    unset($_SESSION['success']);
                    ?>
                </div>
            <?php endif; ?>

            <div class="card mb-4">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="mb-0">Conversation with 
                            <?php 
                            echo htmlspecialchars($_SESSION['user_id'] === $msg_data['sender_id'] 
                                ? $msg_data['receiver_name'] 
                                : $msg_data['sender_name']); 
                            ?>
                        </h3>
                        <a href="<?php echo $basePath; ?>/messages" class="btn btn-secondary btn-sm">
                            Back to Messages
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Conversation History -->
                    <div class="conversation-history mb-4">
                        <?php foreach ($conversation as $msg): ?>
                            <div class="message-bubble mb-3 <?php echo $msg['sender_id'] === $_SESSION['user_id'] ? 'text-end' : ''; ?>">
                                <div class="d-inline-block p-3 rounded <?php 
                                    echo $msg['sender_id'] === $_SESSION['user_id'] 
                                        ? 'bg-primary text-white' 
                                        : 'bg-light'; 
                                ?>" style="max-width: 80%;">
                                    <div class="message-content">
                                        <?php echo nl2br(htmlspecialchars($msg['message'])); ?>
                                    </div>
                                    <div class="message-meta small <?php 
                                        echo $msg['sender_id'] === $_SESSION['user_id'] 
                                            ? 'text-white-50' 
                                            : 'text-muted'; 
                                    ?>">
                                        <?php echo date('Y-m-d H:i', strtotime($msg['created_at'])); ?>
                                        <?php if ($msg['id'] == $message_id): ?>
                                            <span class="badge bg-warning ms-2">Current Message</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Reply Form -->
                    <?php if ($msg_data['receiver_id'] === $_SESSION['user_id']): ?>
                        <div class="reply-form mt-4 pt-4 border-top">
                            <h4 class="mb-3">Reply</h4>
                            <form action="<?php echo $basePath; ?>/messages/send" method="POST">
                                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                                <input type="hidden" name="receiver_id" value="<?php echo $msg_data['sender_id']; ?>">
                                
                                <div class="mb-3">
                                    <label for="message" class="form-label">Your Reply</label>
                                    <textarea class="form-control" id="message" name="message" rows="4" required
                                            placeholder="Type your reply here..."></textarea>
                                </div>
                                
                                <button type="submit" class="btn btn-primary">Send Reply</button>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.message-bubble {
    margin-bottom: 1rem;
}
.message-bubble .message-content {
    margin-bottom: 0.25rem;
}
.message-bubble .message-meta {
    font-size: 0.8rem;
}
</style>

<?php require_once ROOT_PATH . '/views/layout/footer.php'; ?> 