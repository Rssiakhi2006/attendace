<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once '../config/database.php';

class CourseManager {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // Get courses by faculty, department, and level
    public function getCourses($faculty, $department, $level = null) {
        try {
            $query = "SELECT * FROM courses WHERE faculty = :faculty AND department = :department";
            $params = [":faculty" => $faculty, ":department" => $department];
            
            if ($level) {
                $query .= " AND level = :level";
                $params[":level"] = $level;
            }
            
            $query .= " ORDER BY code";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            
            $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return ["success" => true, "courses" => $courses];
        } catch (PDOException $e) {
            return ["success" => false, "message" => "Database error: " . $e->getMessage()];
        }
    }

    // Get professor's courses
    public function getProfessorCourses($professor_id) {
        try {
            $query = "SELECT c.* FROM courses c
                     INNER JOIN professor_courses pc ON c.id = pc.course_id
                     WHERE pc.professor_id = :professor_id
                     ORDER BY c.code";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":professor_id", $professor_id);
            $stmt->execute();
            
            $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return ["success" => true, "courses" => $courses];
        } catch (PDOException $e) {
            return ["success" => false, "message" => "Database error: " . $e->getMessage()];
        }
    }

    // Get student's courses
    public function getStudentCourses($student_id) {
        try {
            $query = "SELECT c.* FROM courses c
                     INNER JOIN student_enrollments se ON c.id = se.course_id
                     WHERE se.student_id = :student_id
                     ORDER BY c.code";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":student_id", $student_id);
            $stmt->execute();
            
            $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return ["success" => true, "courses" => $courses];
        } catch (PDOException $e) {
            return ["success" => false, "message" => "Database error: " . $e->getMessage()];
        }
    }

    // Get all faculties and departments
    public function getFacultiesAndDepartments() {
        try {
            // Get faculties
            $faculty_query = "SELECT * FROM faculties ORDER BY name";
            $faculty_stmt = $this->conn->prepare($faculty_query);
            $faculty_stmt->execute();
            $faculties = $faculty_stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get departments for each faculty
            $result = [];
            foreach ($faculties as $faculty) {
                $dept_query = "SELECT * FROM departments WHERE faculty_code = :faculty_code ORDER BY name";
                $dept_stmt = $this->conn->prepare($dept_query);
                $dept_stmt->bindParam(":faculty_code", $faculty['code']);
                $dept_stmt->execute();
                $departments = $dept_stmt->fetchAll(PDO::FETCH_ASSOC);
                
                $result[] = [
                    'faculty' => $faculty,
                    'departments' => $departments
                ];
            }
            
            return ["success" => true, "data" => $result];
        } catch (PDOException $e) {
            return ["success" => false, "message" => "Database error: " . $e->getMessage()];
        }
    }
}

$courseManager = new CourseManager();
$method = $_SERVER['REQUEST_METHOD'];

if ($method == 'POST') {
    $input = json_decode(file_get_contents("php://input"), true);
    
    if (isset($input['action'])) {
        switch ($input['action']) {
            case 'get_courses':
                if (isset($input['faculty']) && isset($input['department'])) {
                    $level = isset($input['level']) ? $input['level'] : null;
                    $result = $courseManager->getCourses($input['faculty'], $input['department'], $level);
                    echo json_encode($result);
                } else {
                    echo json_encode(["success" => false, "message" => "Faculty and department are required"]);
                }
                break;
                
            case 'get_professor_courses':
                if (isset($input['professor_id'])) {
                    $result = $courseManager->getProfessorCourses($input['professor_id']);
                    echo json_encode($result);
                } else {
                    echo json_encode(["success" => false, "message" => "Professor ID is required"]);
                }
                break;
                
            case 'get_student_courses':
                if (isset($input['student_id'])) {
                    $result = $courseManager->getStudentCourses($input['student_id']);
                    echo json_encode($result);
                } else {
                    echo json_encode(["success" => false, "message" => "Student ID is required"]);
                }
                break;
                
            case 'get_faculties_departments':
                $result = $courseManager->getFacultiesAndDepartments();
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