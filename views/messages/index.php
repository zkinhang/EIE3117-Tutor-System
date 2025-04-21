<?php 
require_once ROOT_PATH . '/views/layout/header.php';

$message = new Message($db);
$received_messages = $message->getReceivedMessages($_SESSION['user_id']);
$sent_messages = $message->getSentMessages($_SESSION['user_id']);

// Get requests based on user type
$tutor = new Tutor($db);
if ($_SESSION['user_type'] === 'tutor') {
    $requests = $tutor->getTutorRequests($_SESSION['user_id']);
} else {
    $requests = $tutor->getStudentRequests($_SESSION['user_id']);
}
?>

<div class="container">
    <h2 class="mb-4">Messages & Requests</h2>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <?php 
            echo $_SESSION['success'];
            unset($_SESSION['success']);
            ?>
        </div>
    <?php endif; ?>

    <!-- Tabs -->
    <ul class="nav nav-tabs mb-4" id="messagesTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="received-tab" data-bs-toggle="tab" 
                    data-bs-target="#received" type="button" role="tab">
                Received Messages
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="sent-tab" data-bs-toggle="tab" 
                    data-bs-target="#sent" type="button" role="tab">
                Sent Messages
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="requests-tab" data-bs-toggle="tab" 
                    data-bs-target="#requests" type="button" role="tab">
                <?php echo $_SESSION['user_type'] === 'tutor' ? 'Student Requests' : 'My Requests'; ?>
            </button>
        </li>
    </ul>

    <!-- Tab Content -->
    <div class="tab-content" id="messagesTabContent">
        <!-- Received Messages -->
        <div class="tab-pane fade show active" id="received" role="tabpanel">
            <?php if (empty($received_messages)): ?>
                <div class="alert alert-info">
                    No messages received.
                </div>
            <?php else: ?>
                <div class="row">
                    <?php foreach ($received_messages as $msg): ?>
                        <div class="col-md-6 mb-4">
                            <div class="card <?php echo $msg['status'] === 'unread' ? 'border-primary' : ''; ?>">
                                <div class="card-body">
                                    <h5 class="card-title">
                                        From: <?php echo htmlspecialchars($msg['sender_name']); ?>
                                        <?php if ($msg['status'] === 'unread'): ?>
                                            <span class="badge bg-primary">New</span>
                                        <?php endif; ?>
                                    </h5>
                                    <?php 
                                    display_flash_message('error');
                                    display_flash_message('success');
                                    ?>
                                    <p class="card-text">
                                        <?php echo h_br(
                                            strlen($msg['message']) > 100 
                                                ? substr($msg['message'], 0, 100) . '...' 
                                                : $msg['message']
                                        ); ?>
                                    </p>
                                    <p class="text-muted">
                                        <?php echo date('Y-m-d H:i', strtotime($msg['created_at'])); ?>
                                    </p>
                                    <a href="<?php echo $basePath; ?>/messages/view/<?php echo $msg['id']; ?>" 
                                       class="btn btn-primary">
                                        Read Message
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Sent Messages -->
        <div class="tab-pane fade" id="sent" role="tabpanel">
            <?php if (empty($sent_messages)): ?>
                <div class="alert alert-info">
                    No messages sent.
                </div>
            <?php else: ?>
                <div class="row">
                    <?php foreach ($sent_messages as $msg): ?>
                        <div class="col-md-6 mb-4">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">
                                        To: <?php echo htmlspecialchars($msg['receiver_name']); ?>
                                    </h5>
                                    <?php 
                                    display_flash_message('error');
                                    display_flash_message('success');
                                    ?>
                                    <p class="card-text">
                                        <?php echo h_br(
                                            strlen($msg['message']) > 100 
                                                ? substr($msg['message'], 0, 100) . '...' 
                                                : $msg['message']
                                        ); ?>
                                    </p>
                                    <p class="text-muted">
                                        <?php echo date('Y-m-d H:i', strtotime($msg['created_at'])); ?>
                                    </p>
                                    <a href="<?php echo $basePath; ?>/messages/view/<?php echo $msg['id']; ?>" 
                                       class="btn btn-secondary">
                                        View Message
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Requests Tab -->
        <div class="tab-pane fade" id="requests" role="tabpanel">
            <?php if (empty($requests)): ?>
                <div class="alert alert-info">
                    <?php echo $_SESSION['user_type'] === 'tutor' 
                          ? 'No requests from students.' 
                          : 'You haven\'t made any tutor requests yet.'; ?>
                </div>
            <?php else: ?>
                <div class="row">
                    <?php foreach ($requests as $request): ?>
                        <div class="col-md-6 mb-4">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <?php if ($_SESSION['user_type'] === 'tutor'): ?>
                                            Request from <?php echo htmlspecialchars($request['student_nickname']); ?>
                                        <?php else: ?>
                                            Request to <?php echo htmlspecialchars($request['tutor_nickname']); ?>
                                        <?php endif; ?>
                                    </h5>
                                    <?php 
                                    display_flash_message('error');
                                    display_flash_message('success');
                                    ?>
                                    <p class="card-text">
                                        <strong>Message:</strong><br>
                                        <?php echo h_br($request['message']); ?>
                                    </p>
                                    <p class="text-muted">
                                        Requested on: <?php echo date('Y-m-d H:i', strtotime($request['created_at'])); ?>
                                    </p>
                                    
                                    <?php if ($_SESSION['user_type'] === 'tutor' && $request['status'] === 'pending'): ?>
                                        <form action="<?php echo $basePath; ?>/tutors/request/update" method="POST" class="d-inline">
                                            <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                                            <input type="hidden" name="status" value="accepted">
                                            <button type="submit" class="btn btn-success">Accept</button>
                                        </form>
                                        
                                        <form action="<?php echo $basePath; ?>/tutors/request/update" method="POST" class="d-inline">
                                            <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                                            <input type="hidden" name="status" value="rejected">
                                            <button type="submit" class="btn btn-danger">Reject</button>
                                        </form>
                                    <?php else: ?>
                                        <div class="badge bg-<?php 
                                            echo $request['status'] === 'pending' ? 'warning' : 
                                                 ($request['status'] === 'accepted' ? 'success' : 'danger'); 
                                        ?>">
                                            <?php echo ucfirst($request['status']); ?>
                                        </div>
                                        
                                        <?php if ($request['status'] === 'accepted'): ?>
                                            <?php 
                                            $other_user_id = $_SESSION['user_type'] === 'tutor' 
                                                ? $request['student_id'] 
                                                : $request['tutor_id'];
                                            ?>
                                            <a href="<?php echo $basePath; ?>/messages/new/<?php echo $other_user_id; ?>" 
                                               class="btn btn-primary ms-2">
                                                Send Message
                                            </a>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- New Message Button -->
    <?php if ($_SESSION['user_type'] === 'student'): ?>
    <div class="mt-4">
        <a href="<?php echo $basePath; ?>/tutors" class="btn btn-primary">
            Find Tutors to Message
        </a>
    </div>
    <?php endif; ?>
</div>

<?php require_once ROOT_PATH . '/views/layout/footer.php'; ?> 