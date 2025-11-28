<?php
// Application configuration
define('APP_NAME', 'Algiers University - Attendance Management System');
define('APP_VERSION', '1.0.0');
define('UPLOAD_PATH', 'uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB

// JWT Secret for token generation
define('JWT_SECRET', 'your-secret-key-here');

// Email configuration
define('SMTP_HOST', 'smtp.univ-alger.dz');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'noreply@univ-alger.dz');
define('SMTP_PASSWORD', 'your-smtp-password');
define('FROM_EMAIL', 'noreply@univ-alger.dz');
define('FROM_NAME', 'Algiers University');

// Allowed file types for uploads
$allowed_file_types = [
    'image/jpeg',
    'image/png',
    'image/gif',
    'application/pdf',
    'application/msword',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
];
?>