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
    
    if (isset($input['action']) && isset($input['user_id'])) {
        $user_id = $input['user_id'];
        
        switch ($input['action']) {
            case 'get_profile':
                $user = $auth->getUserById($user_id);
                if ($user) {
                    echo json_encode(["success" => true, "user" => $user]);
                } else {
                    echo json_encode(["success" => false, "message" => "User not found"]);
                }
                break;
                
            case 'update_profile':
                if (isset($input['first_name']) && isset($input['last_name'])) {
                    $data = [
                        'first_name' => $input['first_name'],
                        'last_name' => $input['last_name'],
                        'phone' => isset($input['phone']) ? $input['phone'] : null,
                        'bio' => isset($input['bio']) ? $input['bio'] : null,
                        'profile_picture' => isset($input['profile_picture']) ? $input['profile_picture'] : null
                    ];
                    
                    $result = $auth->updateProfile($user_id, $data);
                    echo json_encode($result);
                } else {
                    echo json_encode(["success" => false, "message" => "First name and last name are required"]);
                }
                break;
                
            case 'change_password':
                if (isset($input['current_password']) && isset($input['new_password'])) {
                    $result = $auth->changePassword($user_id, $input['current_password'], $input['new_password']);
                    echo json_encode($result);
                } else {
                    echo json_encode(["success" => false, "message" => "Current password and new password are required"]);
                }
                break;
                
            default:
                echo json_encode(["success" => false, "message" => "Invalid action"]);
        }
    } else {
        echo json_encode(["success" => false, "message" => "User ID and action are required"]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Method not allowed"]);
}
?>