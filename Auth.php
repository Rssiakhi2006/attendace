<?php
require_once '../config/database.php';

class Auth {
    private $conn;
    private $table_name = "users";

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    // Register new user
    public function register($data) {
        try {
            // Check if email already exists
            $query = "SELECT id FROM " . $this->table_name . " WHERE email = :email";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":email", $data['email']);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                return ["success" => false, "message" => "Email already exists"];
            }

            // Validate university email
            if (!$this->isValidUniversityEmail($data['email'])) {
                return ["success" => false, "message" => "Please use a valid university email (@univ-alger.dz)"];
            }

            // Hash password
            $hashed_password = password_hash($data['password'], PASSWORD_DEFAULT);

            // Insert user
            $query = "INSERT INTO " . $this->table_name . " 
                     (email, password, role, first_name, last_name, faculty, department, level, phone, bio, profile_picture)
                     VALUES (:email, :password, :role, :first_name, :last_name, :faculty, :department, :level, :phone, :bio, :profile_picture)";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":email", $data['email']);
            $stmt->bindParam(":password", $hashed_password);
            $stmt->bindParam(":role", $data['role']);
            $stmt->bindParam(":first_name", $data['first_name']);
            $stmt->bindParam(":last_name", $data['last_name']);
            $stmt->bindParam(":faculty", $data['faculty']);
            $stmt->bindParam(":department", $data['department']);
            $stmt->bindParam(":level", $data['level']);
            $stmt->bindParam(":phone", $data['phone']);
            $stmt->bindParam(":bio", $data['bio']);
            $stmt->bindParam(":profile_picture", $data['profile_picture']);

            if ($stmt->execute()) {
                $user_id = $this->conn->lastInsertId();
                
                // If student, enroll in courses for their level
                if ($data['role'] == 'student') {
                    $this->enrollStudentInCourses($user_id, $data['faculty'], $data['department'], $data['level']);
                }
                
                // Log the registration
                $this->logSystemAction($user_id, "User registration", "New " . $data['role'] . " registered: " . $data['email']);
                
                return ["success" => true, "message" => "User registered successfully", "user_id" => $user_id];
            } else {
                return ["success" => false, "message" => "Unable to register user"];
            }
        } catch (PDOException $e) {
            return ["success" => false, "message" => "Database error: " . $e->getMessage()];
        }
    }

    // Login user
    public function login($email, $password) {
        try {
            $query = "SELECT id, email, password, role, first_name, last_name, faculty, department, level, phone, bio, profile_picture, is_active
                     FROM " . $this->table_name . " WHERE email = :email AND is_active = 1";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":email", $email);
            $stmt->execute();

            if ($stmt->rowCount() == 1) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (password_verify($password, $row['password'])) {
                    // Update last login
                    $this->updateLastLogin($row['id']);
                    
                    // Log the login
                    $this->logSystemAction($row['id'], "User login", "User logged in successfully");
                    
                    // Remove password from response
                    unset($row['password']);
                    
                    return ["success" => true, "user" => $row];
                }
            }
            
            return ["success" => false, "message" => "Invalid email or password"];
        } catch (PDOException $e) {
            return ["success" => false, "message" => "Database error: " . $e->getMessage()];
        }
    }

    // Validate university email
    private function isValidUniversityEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) && 
               preg_match('/@univ-alger\.dz$/', $email);
    }

    // Enroll student in courses for their level
    private function enrollStudentInCourses($student_id, $faculty, $department, $level) {
        try {
            $query = "SELECT id FROM courses WHERE faculty = :faculty AND department = :department AND level = :level";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":faculty", $faculty);
            $stmt->bindParam(":department", $department);
            $stmt->bindParam(":level", $level);
            $stmt->execute();
            
            $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $enrollment_date = date('Y-m-d');
            
            foreach ($courses as $course) {
                $enroll_query = "INSERT INTO student_enrollments (student_id, course_id, enrollment_date) 
                                VALUES (:student_id, :course_id, :enrollment_date)";
                $enroll_stmt = $this->conn->prepare($enroll_query);
                $enroll_stmt->bindParam(":student_id", $student_id);
                $enroll_stmt->bindParam(":course_id", $course['id']);
                $enroll_stmt->bindParam(":enrollment_date", $enrollment_date);
                $enroll_stmt->execute();
            }
        } catch (PDOException $e) {
            // Log error but don't fail registration
            error_log("Error enrolling student in courses: " . $e->getMessage());
        }
    }

    // Update last login
    private function updateLastLogin($user_id) {
        $query = "UPDATE " . $this->table_name . " SET updated_at = NOW() WHERE id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();
    }

    // Log system action
    private function logSystemAction($user_id, $action, $details) {
        $query = "INSERT INTO system_logs (user_id, action, details, ip_address, user_agent) 
                 VALUES (:user_id, :action, :details, :ip_address, :user_agent)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->bindParam(":action", $action);
        $stmt->bindParam(":details", $details);
        $stmt->bindParam(":ip_address", $_SERVER['REMOTE_ADDR']);
        $stmt->bindParam(":user_agent", $_SERVER['HTTP_USER_AGENT']);
        $stmt->execute();
    }

    // Get user by ID
    public function getUserById($user_id) {
        try {
            $query = "SELECT id, email, role, first_name, last_name, faculty, department, level, phone, bio, profile_picture
                     FROM " . $this->table_name . " WHERE id = :user_id AND is_active = 1";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":user_id", $user_id);
            $stmt->execute();

            if ($stmt->rowCount() == 1) {
                return $stmt->fetch(PDO::FETCH_ASSOC);
            }
            return null;
        } catch (PDOException $e) {
            return null;
        }
    }

    // Update user profile
    public function updateProfile($user_id, $data) {
        try {
            $query = "UPDATE " . $this->table_name . " SET 
                     first_name = :first_name, last_name = :last_name, phone = :phone, bio = :bio,
                     profile_picture = :profile_picture, updated_at = NOW()
                     WHERE id = :user_id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":first_name", $data['first_name']);
            $stmt->bindParam(":last_name", $data['last_name']);
            $stmt->bindParam(":phone", $data['phone']);
            $stmt->bindParam(":bio", $data['bio']);
            $stmt->bindParam(":profile_picture", $data['profile_picture']);
            $stmt->bindParam(":user_id", $user_id);

            if ($stmt->execute()) {
                // Log the update
                $this->logSystemAction($user_id, "Profile update", "User updated their profile");
                return ["success" => true, "message" => "Profile updated successfully"];
            } else {
                return ["success" => false, "message" => "Unable to update profile"];
            }
        } catch (PDOException $e) {
            return ["success" => false, "message" => "Database error: " . $e->getMessage()];
        }
    }

    // Change password
    public function changePassword($user_id, $current_password, $new_password) {
        try {
            // Get current password
            $query = "SELECT password FROM " . $this->table_name . " WHERE id = :user_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":user_id", $user_id);
            $stmt->execute();

            if ($stmt->rowCount() == 1) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (password_verify($current_password, $row['password'])) {
                    // Update password
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $update_query = "UPDATE " . $this->table_name . " SET password = :password, updated_at = NOW() WHERE id = :user_id";
                    $update_stmt = $this->conn->prepare($update_query);
                    $update_stmt->bindParam(":password", $hashed_password);
                    $update_stmt->bindParam(":user_id", $user_id);

                    if ($update_stmt->execute()) {
                        // Log the password change
                        $this->logSystemAction($user_id, "Password change", "User changed their password");
                        return ["success" => true, "message" => "Password changed successfully"];
                    }
                }
            }
            
            return ["success" => false, "message" => "Current password is incorrect"];
        } catch (PDOException $e) {
            return ["success" => false, "message" => "Database error: " . $e->getMessage()];
        }
    }
}
?>