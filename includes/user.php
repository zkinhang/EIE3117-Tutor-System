<?php
class User {
    private $conn;
    private $table_name = "users";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getUserById($id) {
        try {
            $query = "SELECT id, nickname, email, user_type 
                     FROM " . $this->table_name . " 
                     WHERE id = :id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':id' => $id]);
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function getUsersByType($type) {
        try {
            $query = "SELECT id, nickname, email, user_type 
                     FROM " . $this->table_name . " 
                     WHERE user_type = :type";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':type' => $type]);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function getById($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateProfile($id, $nickname, $email, $age, $gender, $profile_image = null) {
        $query = "UPDATE " . $this->table_name . 
                " SET nickname = :nickname, email = :email, age = :age, 
                  gender = :gender" . 
                ($profile_image ? ", profile_image = :profile_image" : "") .
                " WHERE id = :id";
        
        $params = [
            ':id' => $id,
            ':nickname' => $nickname,
            ':email' => $email,
            ':age' => $age,
            ':gender' => $gender
        ];
        
        if($profile_image) {
            $params[':profile_image'] = $profile_image;
        }
        
        $stmt = $this->conn->prepare($query);
        return $stmt->execute($params);
    }
}
?> 