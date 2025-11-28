<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once '../auth/Auth.php';

$auth = new Auth();
$method = $_SERVER['REQUEST_METHOD'];

if ($method == 'POST') {
    $input = json_decode(file_get_contents("php://input"), true);
    
    if (isset($input['action'])) {
        switch ($input['action']) {
            case 'login':
                if (isset($input['email']) && isset($input['password'])) {
                    $result = $auth->login($input['email'], $input['password']);
                    echo json_encode($result);
                } else {
                    echo json_encode(["success" => false, "message" => "Email and password are required"]);
                }
                break;
                
            case 'register':
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
                    
                    $result = $auth->register($data);
                    echo json_encode($result);
                } else {
                    echo json_encode(["success" => false, "message" => "Required fields are missing"]);
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