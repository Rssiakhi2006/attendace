<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once '../config/database.php';

class JustificationManager {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // Submit absence justification
    public function submitJustification($student_id, $session_id, $type, $details, $supporting_docs = null) {
        try {
            // Check if justification already exists for this session
            $check_query = "SELECT id FROM absence_justifications 
                           WHERE student_id = :student_id AND session_id = :session_id";
            $check_stmt = $this->conn->prepare($check_query);
            $check_stmt->bindParam(":student_id", $student_id);
            $check_stmt->bindParam(":session_id", $session_id);
            $check_stmt->execute();
            
            if ($check_stmt->rowCount() > 0) {
                return ["success" => false, "message" => "Justification already submitted for this session"];
            }
            
            $query = "INSERT INTO absence_justifications 
                     (student_id, session_id, justification_type, details, supporting_docs) 
                     VALUES (:student_id, :session_id, :type, :details, :supporting_docs)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":student_id", $student_id);
            $stmt->bindParam(":session_id", $session_id);
            $stmt->bindParam(":type", $type);
            $stmt->bindParam(":details", $details);
            $stmt->bindParam(":supporting_docs", $supporting_docs);
            
            if ($stmt->execute()) {
                return ["success" => true, "message" => "Justification submitted successfully"];
            } else {
                return ["success" => false, "message" => "Unable to submit justification"];
            }
        } catch (PDOException $e) {
            return ["success" => false, "message" => "Database error: " . $e->getMessage()];
        }
    }

    // Get student's justifications
    public function getStudentJustifications($student_id) {
        try {
            $query = "SELECT aj.*, ases.session_date, ases.session_type, c.code as course_code, c.name as course_name
                     FROM absence_justifications aj
                     INNER JOIN attendance_sessions ases ON aj.session_id = ases.id
                     INNER JOIN courses c ON ases.course_id = c.id
                     WHERE aj.student_id = :student_id
                     ORDER BY aj.submitted_at DESC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":student_id", $student_id);
            $stmt->execute();
            
            $justifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return ["success" => true, "justifications" => $justifications];
        } catch (PDOException $e) {
            return ["success" => false, "message" => "Database error: " . $e->getMessage()];
        }
    }

    // Get pending justifications for professor
    public function getPendingJustifications($professor_id) {
        try {
            $query = "SELECT aj.*, u.first_name, u.last_name, ases.session_date, ases.session_type, 
                     c.code as course_code, c.name as course_name
                     FROM absence_justifications aj
                     INNER JOIN users u ON aj.student_id = u.id
                     INNER JOIN attendance_sessions ases ON aj.session_id = ases.id
                     INNER JOIN courses c ON ases.course_id = c.id
                     INNER JOIN professor_courses pc ON c.id = pc.course_id
                     WHERE aj.status = 'pending' AND pc.professor_id = :professor_id
                     ORDER BY aj.submitted_at DESC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":professor_id", $professor_id);
            $stmt->execute();
            
            $justifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return ["success" => true, "justifications" => $justifications];
        } catch (PDOException $e) {
            return ["success" => false, "message" => "Database error: " . $e->getMessage()];
        }
    }

    // Update justification status
    public function updateJustificationStatus($justification_id, $status, $reviewed_by) {
        try {
            $query = "UPDATE absence_justifications 
                     SET status = :status, reviewed_by = :reviewed_by, reviewed_at = NOW()
                     WHERE id = :justification_id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":status", $status);
            $stmt->bindParam(":reviewed_by", $reviewed_by);
            $stmt->bindParam(":justification_id", $justification_id);
            
            if ($stmt->execute()) {
                // If approved, update attendance record
                if ($status == 'approved') {
                    $this->updateAttendanceForJustification($justification_id);
                }
                
                return ["success" => true, "message" => "Justification status updated"];
            } else {
                return ["success" => false, "message" => "Unable to update justification status"];
            }
        } catch (PDOException $e) {
            return ["success" => false, "message" => "Database error: " . $e->getMessage()];
        }
    }

    // Update attendance record when justification is approved
    private function updateAttendanceForJustification($justification_id) {
        try {
            $query = "UPDATE attendance_records ar
                     INNER JOIN absence_justifications aj ON ar.session_id = aj.session_id AND ar.student_id = aj.student_id
                     SET ar.status = 'present'
                     WHERE aj.id = :justification_id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":justification_id", $justification_id);
            $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error updating attendance for justification: " . $e->getMessage());
        }
    }
}

$justificationManager = new JustificationManager();
$method = $_SERVER['REQUEST_METHOD'];

if ($method == 'POST') {
    $input = json_decode(file_get_contents("php://input"), true);
    
    if (isset($input['action'])) {
        switch ($input['action']) {
            case 'submit_justification':
                if (isset($input['student_id']) && isset($input['session_id']) && 
                    isset($input['type']) && isset($input['details'])) {
                    $supporting_docs = isset($input['supporting_docs']) ? $input['supporting_docs'] : null;
                    $result = $justificationManager->submitJustification(
                        $input['student_id'], 
                        $input['session_id'], 
                        $input['type'], 
                        $input['details'],
                        $supporting_docs
                    );
                    echo json_encode($result);
                } else {
                    echo json_encode(["success" => false, "message" => "All fields are required"]);
                }
                break;
                
            case 'get_student_justifications':
                if (isset($input['student_id'])) {
                    $result = $justificationManager->getStudentJustifications($input['student_id']);
                    echo json_encode($result);
                } else {
                    echo json_encode(["success" => false, "message" => "Student ID is required"]);
                }
                break;
                
            case 'get_pending_justifications':
                if (isset($input['professor_id'])) {
                    $result = $justificationManager->getPendingJustifications($input['professor_id']);
                    echo json_encode($result);
                } else {
                    echo json_encode(["success" => false, "message" => "Professor ID is required"]);
                }
                break;
                
            case 'update_justification_status':
                if (isset($input['justification_id']) && isset($input['status']) && isset($input['reviewed_by'])) {
                    $result = $justificationManager->updateJustificationStatus(
                        $input['justification_id'], 
                        $input['status'], 
                        $input['reviewed_by']
                    );
                    echo json_encode($result);
                } else {
                    echo json_encode(["success" => false, "message" => "All fields are required"]);
                }
                break;
                
            default:
                echo json_encode(["success" => false, "message" => "Invalid action"]);
        }
    } else {
        echo json_encode(["success" => false, "message" => "Action parameter is required"]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Method not allowed"]);
}
?>