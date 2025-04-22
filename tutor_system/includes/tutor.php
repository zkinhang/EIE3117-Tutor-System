<?php
class Tutor {
    private $conn;
    private $table_name = "tutor_profiles";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create($user_id, $expertise_area, $description) {
        $query = "INSERT INTO " . $this->table_name . 
                " (user_id, expertise_area, description) VALUES 
                (:user_id, :expertise_area, :description)";
        
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            ':user_id' => $user_id,
            ':expertise_area' => $expertise_area,
            ':description' => $description
        ]);
    }

    public function updateProfile($user_id, $expertise_area, $description) {
        try {
            // First check if profile exists
            $check_query = "SELECT id FROM tutor_profiles WHERE user_id = :user_id";
            $check_stmt = $this->conn->prepare($check_query);
            $check_stmt->execute([':user_id' => $user_id]);
            
            if ($check_stmt->rowCount() > 0) {
                // Update existing profile
                $query = "UPDATE tutor_profiles 
                         SET expertise_area = :expertise_area, 
                             description = :description,
                             updated_at = CURRENT_TIMESTAMP
                         WHERE user_id = :user_id";
            } else {
                // Create new profile
                $query = "INSERT INTO tutor_profiles (user_id, expertise_area, description) 
                         VALUES (:user_id, :expertise_area, :description)";
            }
            
            $stmt = $this->conn->prepare($query);
            return $stmt->execute([
                ':user_id' => $user_id,
                ':expertise_area' => $expertise_area,
                ':description' => $description
            ]);
        } catch (PDOException $e) {
            error_log("Error updating tutor profile: " . $e->getMessage());
            return false;
        }
    }

    public function getTutorById($id) {
        $query = "SELECT u.*, t.expertise_area, t.description 
                 FROM users u 
                 LEFT JOIN " . $this->table_name . " t ON u.id = t.user_id 
                 WHERE u.id = :id AND u.user_type = 'tutor'";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getAllTutors($search = '', $expertise = '', $page = 1, $per_page = 12) {
        $offset = ($page - 1) * $per_page;
        $params = [];
        $where_conditions = ["u.user_type = 'tutor'"];
        
        if (!empty($search)) {
            $where_conditions[] = "(u.nickname LIKE :search OR t.expertise_area LIKE :search OR t.description LIKE :search)";
            $params[':search'] = "%$search%";
        }
        
        if (!empty($expertise)) {
            $where_conditions[] = "t.expertise_area = :expertise";
            $params[':expertise'] = $expertise;
        }
        
        $where_clause = implode(' AND ', $where_conditions);
        
        $query = "SELECT u.id, u.nickname, u.profile_image, t.expertise_area, t.description 
                 FROM users u 
                 JOIN " . $this->table_name . " t ON u.id = t.user_id 
                 WHERE $where_clause
                 ORDER BY u.nickname ASC
                 LIMIT :offset, :per_page";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->bindValue(':per_page', $per_page, PDO::PARAM_INT);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTotalTutors($search = '', $expertise = '') {
        $params = [];
        $where_conditions = ["u.user_type = 'tutor'"];
        
        if (!empty($search)) {
            $where_conditions[] = "(u.nickname LIKE :search OR t.expertise_area LIKE :search OR t.description LIKE :search)";
            $params[':search'] = "%$search%";
        }
        
        if (!empty($expertise)) {
            $where_conditions[] = "t.expertise_area = :expertise";
            $params[':expertise'] = $expertise;
        }
        
        $where_clause = implode(' AND ', $where_conditions);
        
        $query = "SELECT COUNT(*) as total 
                 FROM users u 
                 JOIN " . $this->table_name . " t ON u.id = t.user_id 
                 WHERE $where_clause";
        
        $stmt = $this->conn->prepare($query);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }

    public function getExpertiseAreas() {
        $query = "SELECT DISTINCT expertise_area 
                 FROM " . $this->table_name . " 
                 ORDER BY expertise_area ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function createRequest($student_id, $tutor_id, $message) {
        try {
            // First check if there's already a pending request
            $check_query = "SELECT id FROM tutor_requests 
                           WHERE student_id = :student_id 
                           AND tutor_id = :tutor_id 
                           AND status = 'pending'";
            
            $check_stmt = $this->conn->prepare($check_query);
            $check_stmt->execute([
                ':student_id' => $student_id,
                ':tutor_id' => $tutor_id
            ]);
            
            if ($check_stmt->rowCount() > 0) {
                error_log("Duplicate request attempted: student_id=$student_id, tutor_id=$tutor_id");
                return false;
            }

            // Insert the request with the message directly
            $query = "INSERT INTO tutor_requests (student_id, tutor_id, message) 
                      VALUES (:student_id, :tutor_id, :message)";
            
            $stmt = $this->conn->prepare($query);
            $result = $stmt->execute([
                ':student_id' => $student_id,
                ':tutor_id' => $tutor_id,
                ':message' => $message
            ]);
            
            if (!$result) {
                error_log("Failed to create tutor request: " . implode(", ", $stmt->errorInfo()));
            }
            return $result;
        } catch (PDOException $e) {
            error_log("Error creating tutor request: " . $e->getMessage());
            return false;
        }
    }

    public function getRequestStatus($student_id, $tutor_id) {
        try {
            $query = "SELECT * FROM tutor_requests 
                      WHERE student_id = :student_id 
                      AND tutor_id = :tutor_id 
                      ORDER BY created_at DESC 
                      LIMIT 1";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                ':student_id' => $student_id,
                ':tutor_id' => $tutor_id
            ]);
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // Log error if needed
            return false;
        }
    }

    public function getTutorRequests($tutor_id) {
        try {
            $query = "SELECT r.*, u.nickname as student_nickname 
                     FROM tutor_requests r 
                     JOIN users u ON r.student_id = u.id 
                     WHERE r.tutor_id = :tutor_id 
                     ORDER BY r.created_at DESC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':tutor_id' => $tutor_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function updateRequestStatus($request_id, $status) {
        try {
            $query = "UPDATE tutor_requests 
                     SET status = :status, 
                         updated_at = NOW() 
                     WHERE id = :request_id";
            
            $stmt = $this->conn->prepare($query);
            return $stmt->execute([
                ':status' => $status,
                ':request_id' => $request_id
            ]);
        } catch (PDOException $e) {
            error_log("Error updating request status: " . $e->getMessage());
            return false;
        }
    }

    public function getStudentRequests($student_id) {
        try {
            $query = "SELECT r.*, u.nickname as tutor_nickname 
                     FROM tutor_requests r 
                     JOIN users u ON r.tutor_id = u.id 
                     WHERE r.student_id = :student_id 
                     ORDER BY r.created_at DESC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':student_id' => $student_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function getAcceptedStudents($tutor_id) {
        try {
            $query = "SELECT 
                        tr.id as request_id,
                        tr.student_id,
                        tr.created_at as request_date,
                        tr.message as request_message,
                        tr.status,
                        u.nickname as student_name,
                        u.email as student_email
                     FROM tutor_requests tr
                     JOIN users u ON tr.student_id = u.id
                     WHERE tr.tutor_id = :tutor_id 
                     AND tr.status = 'accepted'
                     ORDER BY tr.created_at DESC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':tutor_id' => $tutor_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting accepted students: " . $e->getMessage());
            return [];
        }
    }

    public function getMyTutors($student_id) {
        try {
            $query = "SELECT 
                        tr.id as request_id,
                        tr.tutor_id,
                        tr.created_at as request_date,
                        tr.message as request_message,
                        u.nickname as tutor_name,
                        u.email as tutor_email,
                        tp.expertise_area
                     FROM tutor_requests tr
                     JOIN users u ON tr.tutor_id = u.id
                     LEFT JOIN tutor_profiles tp ON u.id = tp.user_id
                     WHERE tr.student_id = :student_id 
                     AND tr.status = 'accepted'
                     ORDER BY tr.created_at DESC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':student_id' => $student_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting student's tutors: " . $e->getMessage());
            return [];
        }
    }

    public function getTutorAvailability($tutor_id) {
        try {
            $query = "SELECT day_of_week, time_slot 
                     FROM tutor_availability 
                     WHERE tutor_id = :tutor_id 
                     ORDER BY day_of_week, time_slot";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':tutor_id' => $tutor_id]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Group by day of week
            $availability = [];
            foreach ($results as $row) {
                $day = $row['day_of_week'];
                if (!isset($availability[$day])) {
                    $availability[$day] = [];
                }
                $availability[$day][] = $row['time_slot'];
            }
            
            return $availability;
        } catch (PDOException $e) {
            error_log("Error getting tutor availability: " . $e->getMessage());
            return [];
        }
    }

    public function getTutorRatings($tutor_id) {
        try {
            $query = "SELECT r.*, u.nickname as student_name 
                     FROM tutor_ratings r 
                     JOIN users u ON r.student_id = u.id 
                     WHERE r.tutor_id = :tutor_id 
                     ORDER BY r.created_at DESC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':tutor_id' => $tutor_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting tutor ratings: " . $e->getMessage());
            return [];
        }
    }
}
?> 
