<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once '../config/config.php';

class FileUpload {
    private $allowed_types;
    private $max_size;
    private $upload_path;

    public function __construct() {
        global $allowed_file_types;
        $this->allowed_types = $allowed_file_types;
        $this->max_size = MAX_FILE_SIZE;
        $this->upload_path = UPLOAD_PATH;
        
        // Create upload directory if it doesn't exist
        if (!file_exists($this->upload_path)) {
            mkdir($this->upload_path, 0777, true);
        }
    }

    public function uploadFile($file, $type = 'profile') {
        try {
            // Check for errors
            if ($file['error'] !== UPLOAD_ERR_OK) {
                return ["success" => false, "message" => "File upload error: " . $file['error']];
            }

            // Check file size
            if ($file['size'] > $this->max_size) {
                return ["success" => false, "message" => "File size exceeds maximum limit"];
            }

            // Check file type
            $file_type = mime_content_type($file['tmp_name']);
            if (!in_array($file_type, $this->allowed_types)) {
                return ["success" => false, "message" => "File type not allowed"];
            }

            // Generate unique filename
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = uniqid() . '_' . time() . '.' . $extension;
            $filepath = $this->upload_path . $filename;

            // Move uploaded file
            if (move_uploaded_file($file['tmp_name'], $filepath)) {
                return [
                    "success" => true, 
                    "message" => "File uploaded successfully",
                    "filename" => $filename,
                    "filepath" => $filepath,
                    "file_url" => $this->getFileUrl($filename)
                ];
            } else {
                return ["success" => false, "message" => "Failed to move uploaded file"];
            }
        } catch (Exception $e) {
            return ["success" => false, "message" => "Upload error: " . $e->getMessage()];
        }
    }

    public function uploadBase64($base64_data, $type = 'profile') {
        try {
            // Extract the base64 data
            if (preg_match('/^data:image\/(\w+);base64,/', $base64_data, $type_match)) {
                $image_type = $type_match[1];
                $base64_data = substr($base64_data, strpos($base64_data, ',') + 1);
            } else {
                return ["success" => false, "message" => "Invalid base64 data"];
            }

            // Decode base64 data
            $file_data = base64_decode($base64_data);
            if ($file_data === false) {
                return ["success" => false, "message" => "Failed to decode base64 data"];
            }

            // Check file size
            if (strlen($file_data) > $this->max_size) {
                return ["success" => false, "message" => "File size exceeds maximum limit"];
            }

            // Generate unique filename
            $filename = uniqid() . '_' . time() . '.' . $image_type;
            $filepath = $this->upload_path . $filename;

            // Save file
            if (file_put_contents($filepath, $file_data)) {
                return [
                    "success" => true, 
                    "message" => "File uploaded successfully",
                    "filename" => $filename,
                    "filepath" => $filepath,
                    "file_url" => $this->getFileUrl($filename)
                ];
            } else {
                return ["success" => false, "message" => "Failed to save file"];
            }
        } catch (Exception $e) {
            return ["success" => false, "message" => "Upload error: " . $e->getMessage()];
        }
    }

    private function getFileUrl($filename) {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
        $host = $_SERVER['HTTP_HOST'];
        return $protocol . "://" . $host . "/" . $this->upload_path . $filename;
    }
}

$uploader = new FileUpload();
$method = $_SERVER['REQUEST_METHOD'];

if ($method == 'POST') {
    // Check if it's a file upload or base64 upload
    if (isset($_FILES['file'])) {
        $result = $uploader->uploadFile($_FILES['file'], $_POST['type'] ?? 'profile');
        echo json_encode($result);
    } elseif (isset($_POST['base64_data'])) {
        $result = $uploader->uploadBase64($_POST['base64_data'], $_POST['type'] ?? 'profile');
        echo json_encode($result);
    } else {
        echo json_encode(["success" => false, "message" => "No file data provided"]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Method not allowed"]);
}
?>