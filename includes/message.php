<?php
class Message {
    private $conn;
    private $table_name = "messages";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function send($sender_id, $receiver_id, $message) {
        try {
            $query = "INSERT INTO messages (sender_id, receiver_id, message) 
                      VALUES (:sender_id, :receiver_id, :message)";
            
            $stmt = $this->conn->prepare($query);
            return $stmt->execute([
                ':sender_id' => $sender_id,
                ':receiver_id' => $receiver_id,
                ':message' => $message
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function getReceivedMessages($user_id) {
        try {
            $query = "SELECT m.*, u.nickname as sender_name 
                     FROM messages m 
                     JOIN users u ON m.sender_id = u.id 
                     WHERE m.receiver_id = :user_id 
                     ORDER BY m.created_at DESC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':user_id' => $user_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function getSentMessages($user_id) {
        try {
            $query = "SELECT m.*, u.nickname as receiver_name 
                     FROM messages m 
                     JOIN users u ON m.receiver_id = u.id 
                     WHERE m.sender_id = :user_id 
                     ORDER BY m.created_at DESC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':user_id' => $user_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function markAsRead($message_id) {
        try {
            $query = "UPDATE messages 
                     SET status = 'read' 
                     WHERE id = :message_id";
            
            $stmt = $this->conn->prepare($query);
            return $stmt->execute([':message_id' => $message_id]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function getUnreadCount($user_id) {
        $query = "SELECT COUNT(*) as count 
                 FROM " . $this->table_name . 
                " WHERE receiver_id = :user_id AND status = 'unread'";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':user_id' => $user_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'];
    }

    public function getConversation($user1_id, $user2_id) {
        $query = "SELECT m.*, 
                         s.nickname as sender_name, 
                         r.nickname as receiver_name 
                  FROM messages m 
                  JOIN users s ON m.sender_id = s.id 
                  JOIN users r ON m.receiver_id = r.id 
                  WHERE (m.sender_id = :user1 AND m.receiver_id = :user2) 
                     OR (m.sender_id = :user2 AND m.receiver_id = :user1) 
                  ORDER BY m.created_at ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute([
            ':user1' => $user1_id,
            ':user2' => $user2_id
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getMessageById($message_id) {
        try {
            $query = "SELECT m.*, 
                             s.nickname as sender_name, 
                             r.nickname as receiver_name 
                      FROM messages m 
                      JOIN users s ON m.sender_id = s.id 
                      JOIN users r ON m.receiver_id = r.id 
                      WHERE m.id = :message_id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':message_id' => $message_id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return false;
        }
    }
}
?> 