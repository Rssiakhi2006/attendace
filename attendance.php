<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once '../config/database.php';

class AttendanceManager {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // Create attendance session
    public function createSession($course_id, $session_date, $session_type, $created_by) {
        try {
            $query = "INSERT INTO attendance_sessions (course_id, session_date, session_type, created_by) 
                     VALUES (:course_id, :session_date, :session_type, :created_by)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":course_id", $course_id);
            $stmt->bindParam(":session_date", $session_date);
            $stmt->bindParam(":session_type", $session_type);
            $stmt->bindParam(":created_by", $created_by);
            
            if ($stmt->execute()) {
                $session_id = $this->conn->lastInsertId();
                
                // Initialize attendance records for all enrolled students as absent
                $this->initializeAttendanceRecords($session_id, $course_id);
                
                return ["success" => true, "session_id" => $session_id, "message" => "Attendance session created"];
            } else {
                return ["success" => false, "message" => "Unable to create attendance session"];
            }
        } catch (PDOException $e) {
            return ["success" => false, "message" => "Database error: " . $e->getMessage()];
        }
    }

    // Initialize attendance records for all enrolled students
    private function initializeAttendanceRecords($session_id, $course_id) {
        try {
            $query = "INSERT INTO attendance_records (session_id, student_id, status)
                     SELECT :session_id, se.student_id, 'absent'
                     FROM student_enrollments se
                     WHERE se.course_id = :course_id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":session_id", $session_id);
            $stmt->bindParam(":course_id", $course_id);
            $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error initializing attendance records: " . $e->getMessage());
        }
    }

    // Get students for attendance session
    public function getSessionStudents($session_id) {
        try {
            $query = "SELECT u.id, u.first_name, u.last_name, ar.status
                     FROM attendance_records ar
                     INNER JOIN users u ON ar.student_id = u.id
                     WHERE ar.session_id = :session_id
                     ORDER BY u.last_name, u.first_name";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":session_id", $session_id);
            $stmt->execute();
            
            $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return ["success" => true, "students" => $students];
        } catch (PDOException $e) {
            return ["success" => false, "message" => "Database error: " . $e->getMessage()];
        }
    }

    // Update attendance status
    public function updateAttendance($session_id, $student_id, $status) {
        try {
            $query = "UPDATE attendance_records SET status = :status 
                     WHERE session_id = :session_id AND student_id = :student_id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":status", $status);
            $stmt->bindParam(":session_id", $session_id);
            $stmt->bindParam(":student_id", $student_id);
            
            if ($stmt->execute()) {
                return ["success" => true, "message" => "Attendance updated"];
            } else {
                return ["success" => false, "message" => "Unable to update attendance"];
            }
        } catch (PDOException $e) {
            return ["success" => false, "message" => "Database error: " . $e->getMessage()];
        }
    }

    // Get student attendance for a course
    public function getStudentAttendance($student_id, $course_id) {
        try {
            $query = "SELECT ases.session_date, ases.session_type, ar.status
                     FROM attendance_sessions ases
                     INNER JOIN attendance_records ar ON ases.id = ar.session_id
                     WHERE ar.student_id = :student_id AND ases.course_id = :course_id
                     ORDER BY ases.session_date DESC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":student_id", $student_id);
            $stmt->bindParam(":course_id", $course_id);
            $stmt->execute();
            
            $attendance = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Calculate statistics
            $total = count($attendance);
            $present = count(array_filter($attendance, function($record) {
                return $record['status'] == 'present';
            }));
            $attendance_rate = $total > 0 ? round(($present / $total) * 100, 2) : 0;
            
            return [
                "success" => true, 
                "attendance" => $attendance,
                "statistics" => [
                    "total_sessions" => $total,
                    "present" => $present,
                    "absent" => $total - $present,
                    "attendance_rate" => $attendance_rate
                ]
            ];
        } catch (PDOException $e) {
            return ["success" => false, "message" => "Database error: " . $e->getMessage()];
        }
    }

    // Get attendance summary for a course
    public function getCourseAttendanceSummary($course_id, $period = 'month') {
        try {
            // Calculate date range based on period
            $date_condition = "";
            $params = [":course_id" => $course_id];
            
            switch ($period) {
                case 'week':
                    $date_condition = "AND ases.session_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
                    break;
                case 'semester':
                    // Assuming semester starts 4 months ago
                    $date_condition = "AND ases.session_date >= DATE_SUB(CURDATE(), INTERVAL 4 MONTH)";
                    break;
                case 'month':
                default:
                    $date_condition = "AND ases.session_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
                    break;
            }
            
            $query = "SELECT u.id, u.first_name, u.last_name,
                     COUNT(ar.id) as total_sessions,
                     SUM(CASE WHEN ar.status = 'present' THEN 1 ELSE 0 END) as present_count,
                     SUM(CASE WHEN ar.status = 'absent' THEN 1 ELSE 0 END) as absent_count,
                     CASE 
                         WHEN COUNT(ar.id) > 0 THEN 
                             ROUND((SUM(CASE WHEN ar.status = 'present' THEN 1 ELSE 0 END) / COUNT(ar.id)) * 100, 2)
                         ELSE 0
                     END as attendance_rate
                     FROM users u
                     INNER JOIN student_enrollments se ON u.id = se.student_id
                     LEFT JOIN attendance_sessions ases ON se.course_id = ases.course_id $date_condition
                     LEFT JOIN attendance_records ar ON ases.id = ar.session_id AND ar.student_id = u.id
                     WHERE se.course_id = :course_id
                     GROUP BY u.id, u.first_name, u.last_name
                     ORDER BY u.last_name, u.first_name";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            
            $summary = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return ["success" => true, "summary" => $summary];
        } catch (PDOException $e) {
            return ["success" => false, "message" => "Database error: " . $e->getMessage()];
        }
    }
}

$attendanceManager = new AttendanceManager();
$method = $_SERVER['REQUEST_METHOD'];

if ($method == 'POST') {
    $input = json_decode(file_get_contents("php://input"), true);
    
    if (isset($input['action'])) {
        switch ($input['action']) {
            case 'create_session':
                if (isset($input['course_id']) && isset($input['session_date']) && 
                    isset($input['session_type']) && isset($input['created_by'])) {
                    $result = $attendanceManager->createSession(
                        $input['course_id'], 
                        $input['session_date'], 
                        $input['session_type'], 
                        $input['created_by']
                    );
                    echo json_encode($result);
                } else {
                    echo json_encode(["success" => false, "message" => "All fields are required"]);
                }
                break;
                
            case 'get_session_students':
                if (isset($input['session_id'])) {
                    $result = $attendanceManager->getSessionStudents($input['session_id']);
                    echo json_encode($result);
                } else {
                    echo json_encode(["success" => false, "message" => "Session ID is required"]);
                }
                break;
                
            case 'update_attendance':
                if (isset($input['session_id']) && isset($input['student_id']) && isset($input['status'])) {
                    $result = $attendanceManager->updateAttendance(
                        $input['session_id'], 
                        $input['student_id'], 
                        $input['status']
                    );
                    echo json_encode($result);
                } else {
                    echo json_encode(["success" => false, "message" => "All fields are required"]);
                }
                break;
                
            case 'get_student_attendance':
                if (isset($input['student_id']) && isset($input['course_id'])) {
                    $result = $attendanceManager->getStudentAttendance($input['student_id'], $input['course_id']);
                    echo json_encode($result);
                } else {
                    echo json_encode(["success" => false, "message" => "Student ID and Course ID are required"]);
                }
                break;
                
            case 'get_course_summary':
                if (isset($input['course_id'])) {
                    $period = isset($input['period']) ? $input['period'] : 'month';
                    $result = $attendanceManager->getCourseAttendanceSummary($input['course_id'], $period);
                    echo json_encode($result);
                } else {
                    echo json_encode(["success" => false, "message" => "Course ID is required"]);
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