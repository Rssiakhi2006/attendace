<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once '../config/database.php';

class AdminManager {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // Get system statistics
    public function getSystemStats() {
        try {
            // Total students
            $student_query = "SELECT COUNT(*) as total FROM users WHERE role = 'student' AND is_active = 1";
            $student_stmt = $this->conn->prepare($student_query);
            $student_stmt->execute();
            $student_count = $student_stmt->fetch(PDO::FETCH_ASSOC)['total'];

            // Total professors
            $professor_query = "SELECT COUNT(*) as total FROM users WHERE role = 'professor' AND is_active = 1";
            $professor_stmt = $this->conn->prepare($professor_query);
            $professor_stmt->execute();
            $professor_count = $professor_stmt->fetch(PDO::FETCH_ASSOC)['total'];

            // Total courses
            $course_query = "SELECT COUNT(*) as total FROM courses";
            $course_stmt = $this->conn->prepare($course_query);
            $course_stmt->execute();
            $course_count = $course_stmt->fetch(PDO::FETCH_ASSOC)['total'];

            // Pending justifications
            $justification_query = "SELECT COUNT(*) as total FROM absence_justifications WHERE status = 'pending'";
            $justification_stmt = $this->conn->prepare($justification_query);
            $justification_stmt->execute();
            $justification_count = $justification_stmt->fetch(PDO::FETCH_ASSOC)['total'];

            // System usage (courses with attendance sessions in last 30 days)
            $usage_query = "SELECT COUNT(DISTINCT course_id) as active_courses 
                           FROM attendance_sessions 
                           WHERE session_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
            $usage_stmt = $this->conn->prepare($usage_query);
            $usage_stmt->execute();
            $active_courses = $usage_stmt->fetch(PDO::FETCH_ASSOC)['active_courses'];
            $usage_rate = $course_count > 0 ? round(($active_courses / $course_count) * 100, 2) : 0;

            return [
                "success" => true,
                "stats" => [
                    "total_students" => $student_count,
                    "total_professors" => $professor_count,
                    "total_courses" => $course_count,
                    "pending_justifications" => $justification_count,
                    "system_usage_rate" => $usage_rate,
                    "active_courses" => $active_courses
                ]
            ];
        } catch (PDOException $e) {
            return ["success" => false, "message" => "Database error: " . $e->getMessage()];
        }
    }

    // Get all users with filtering
    public function getUsers($role = null, $faculty = null, $department = null) {
        try {
            $query = "SELECT id, email, role, first_name, last_name, faculty, department, level, 
                     phone, is_active, created_at
                     FROM users WHERE 1=1";
            $params = [];

            if ($role) {
                $query .= " AND role = :role";
                $params[':role'] = $role;
            }

            if ($faculty) {
                $query .= " AND faculty = :faculty";
                $params[':faculty'] = $faculty;
            }

            if ($department) {
                $query .= " AND department = :department";
                $params[':department'] = $department;
            }

            $query .= " ORDER BY created_at DESC";

            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);

            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return ["success" => true, "users" => $users];
        } catch (PDOException $e) {
            return ["success" => false, "message" => "Database error: " . $e->getMessage()];
        }
    }

    // Add new user (for admin)
    public function addUser($data) {
        try {
            require_once '../auth/Auth.php';
            $auth = new Auth();
            return $auth->register($data);
        } catch (Exception $e) {
            return ["success" => false, "message" => "Error adding user: " . $e->getMessage()];
        }
    }

    // Update user status
    public function updateUserStatus($user_id, $is_active) {
        try {
            $query = "UPDATE users SET is_active = :is_active, updated_at = NOW() WHERE id = :user_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":is_active", $is_active);
            $stmt->bindParam(":user_id", $user_id);

            if ($stmt->execute()) {
                $status = $is_active ? "activated" : "deactivated";
                return ["success" => true, "message" => "User $status successfully"];
            } else {
                return ["success" => false, "message" => "Unable to update user status"];
            }
        } catch (PDOException $e) {
            return ["success" => false, "message" => "Database error: " . $e->getMessage()];
        }
    }

    // Get system logs
    public function getSystemLogs($limit = 100) {
        try {
            $query = "SELECT sl.*, u.first_name, u.last_name, u.role
                     FROM system_logs sl
                     LEFT JOIN users u ON sl.user_id = u.id
                     ORDER BY sl.created_at DESC
                     LIMIT :limit";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
            $stmt->execute();

            $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return ["success" => true, "logs" => $logs];
        } catch (PDOException $e) {
            return ["success" => false, "message" => "Database error: " . $e->getMessage()];
        }
    }

    // Get attendance statistics by faculty/department
    public function getAttendanceStatistics($period = 'month', $faculty = null, $department = null) {
        try {
            // Calculate date range
            $date_condition = "";
            switch ($period) {
                case 'week':
                    $date_condition = "AND ases.session_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
                    break;
                case 'semester':
                    $date_condition = "AND ases.session_date >= DATE_SUB(CURDATE(), INTERVAL 4 MONTH)";
                    break;
                case 'year':
                    $date_condition = "AND ases.session_date >= DATE_SUB(CURDATE(), INTERVAL 1 YEAR)";
                    break;
                case 'month':
                default:
                    $date_condition = "AND ases.session_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
                    break;
            }

            $query = "SELECT 
                     u.faculty, u.department,
                     COUNT(ar.id) as total_records,
                     SUM(CASE WHEN ar.status = 'present' THEN 1 ELSE 0 END) as present_count,
                     CASE 
                         WHEN COUNT(ar.id) > 0 THEN 
                             ROUND((SUM(CASE WHEN ar.status = 'present' THEN 1 ELSE 0 END) / COUNT(ar.id)) * 100, 2)
                         ELSE 0
                     END as attendance_rate
                     FROM attendance_records ar
                     INNER JOIN attendance_sessions ases ON ar.session_id = ases.id
                     INNER JOIN users u ON ar.student_id = u.id
                     WHERE 1=1 $date_condition";

            $params = [];

            if ($faculty) {
                $query .= " AND u.faculty = :faculty";
                $params[':faculty'] = $faculty;
            }

            if ($department) {
                $query .= " AND u.department = :department";
                $params[':department'] = $department;
            }

            $query .= " GROUP BY u.faculty, u.department
                       ORDER BY attendance_rate DESC";

            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);

            $stats = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return ["success" => true, "statistics" => $stats];
        } catch (PDOException $e) {
            return ["success" => false, "message" => "Database error: " . $e->getMessage()];
        }
    }
}

$adminManager = new AdminManager();
$method = $_SERVER['REQUEST_METHOD'];

if ($method == 'POST') {
    $input = json_decode(file_get_contents("php://input"), true);
    
    if (isset($input['action'])) {
        switch ($input['action']) {
            case 'get_system_stats':
                $result = $adminManager->getSystemStats();
                echo json_encode($result);
                break;
                
            case 'get_users':
                $role = isset($input['role']) ? $input['role'] : null;
                $faculty = isset($input['faculty']) ? $input['faculty'] : null;
                $department = isset($input['department']) ? $input['department'] : null;
                $result = $adminManager->getUsers($role, $faculty, $department);
                echo json_encode($result);
                break;
                
            case 'add_user':
                if (isset($input['email']) && isset($input['password']) && isset($input['role']) && 
                    isset($input['first_name']) && isset($input['last_name']) && isset($input['faculty']) && 
                    isset($input['department'])) {
                    
                    $data = [
                        'email' => $input['email'],
                        'password' => $input['password'],
                        'role' => $input['role'],
                        'first_name' => $input['first_name'],
                        'last_name' => $input['last_name'],
                        'faculty' => $input['faculty'],
                        'department' => $input['department'],
                        'level' => isset($input['level']) ? $input['level'] : null,
                        'phone' => isset($input['phone']) ? $input['phone'] : null,
                        'bio' => isset($input['bio']) ? $input['bio'] : null,
                        'profile_picture' => isset($input['profile_picture']) ? $input['profile_picture'] : null
                    ];
                    
                    $result = $adminManager->addUser($data);
                    echo json_encode($result);
                } else {
                    echo json_encode(["success" => false, "message" => "Required fields are missing"]);
                }
                break;
                
            case 'update_user_status':
                if (isset($input['user_id']) && isset($input['is_active'])) {
                    $result = $adminManager->updateUserStatus($input['user_id'], $input['is_active']);
                    echo json_encode($result);
                } else {
                    echo json_encode(["success" => false, "message" => "User ID and status are required"]);
                }
                break;
                
            case 'get_system_logs':
                $limit = isset($input['limit']) ? $input['limit'] : 100;
                $result = $adminManager->getSystemLogs($limit);
                echo json_encode($result);
                break;
                
            case 'get_attendance_statistics':
                $period = isset($input['period']) ? $input['period'] : 'month';
                $faculty = isset($input['faculty']) ? $input['faculty'] : null;
                $department = isset($input['department']) ? $input['department'] : null;
                $result = $adminManager->getAttendanceStatistics($period, $faculty, $department);
                echo json_encode($result);
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