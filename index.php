<?php
session_start();

// Initialize session data if not exists
if (!isset($_SESSION['users'])) {
    $_SESSION['users'] = [
        [
            'id' => 1,
            'email' => 'admin@univ-alger.dz',
            'password' => 'password',
            'role' => 'admin',
            'firstName' => 'Sarah',
            'lastName' => 'Wilson',
            'phone' => '+213 123 456 791',
            'faculty' => 'administration',
            'department' => 'administration',
            'level' => 'all',
            'bio' => 'System administrator for the attendance management system',
            'profilePicture' => null
        ]
    ];
}

if (!isset($_SESSION['students'])) {
    $_SESSION['students'] = [];
}

if (!isset($_SESSION['professors'])) {
    $_SESSION['professors'] = [];
}

if (!isset($_SESSION['attendance'])) {
    $_SESSION['attendance'] = [];
}

if (!isset($_SESSION['justifications'])) {
    $_SESSION['justifications'] = [];
}

// Faculty and Department Structure
$faculties = [
    'sciences' => [
        'name' => 'Faculty of Sciences',
        'departments' => [
            'computer_science' => [
                'name' => 'Computer Science Department',
                'levels' => ['L1', 'L2', 'L3', 'M1', 'M2'],
                'courses' => [
                    'L1' => [
                        ['code' => 'CS101', 'name' => 'Introduction to Programming', 'credits' => 4, 'type' => 'Fundamental'],
                        ['code' => 'CS102', 'name' => 'Computer Systems', 'credits' => 3, 'type' => 'Fundamental'],
                        ['code' => 'MATH101', 'name' => 'Calculus I', 'credits' => 4, 'type' => 'Mathematics']
                    ],
                    'L2' => [
                        ['code' => 'CS201', 'name' => 'Data Structures', 'credits' => 4, 'type' => 'Fundamental'],
                        ['code' => 'CS202', 'name' => 'Database Systems', 'credits' => 4, 'type' => 'Fundamental'],
                        ['code' => 'CS203', 'name' => 'Web Development', 'credits' => 3, 'type' => 'Technical']
                    ],
                    'L3' => [
                        ['code' => 'CS301', 'name' => 'Software Engineering', 'credits' => 4, 'type' => 'Fundamental'],
                        ['code' => 'CS302', 'name' => 'Artificial Intelligence', 'credits' => 3, 'type' => 'Specialization'],
                        ['code' => 'CS303', 'name' => 'Computer Networks', 'credits' => 4, 'type' => 'Technical']
                    ],
                    'M1' => [
                        ['code' => 'CS401', 'name' => 'Machine Learning', 'credits' => 4, 'type' => 'Specialization'],
                        ['code' => 'CS402', 'name' => 'Cloud Computing', 'credits' => 3, 'type' => 'Technical'],
                        ['code' => 'CS403', 'name' => 'Mobile Development', 'credits' => 3, 'type' => 'Technical']
                    ],
                    'M2' => [
                        ['code' => 'CS501', 'name' => 'Advanced Algorithms', 'credits' => 4, 'type' => 'Advanced'],
                        ['code' => 'CS502', 'name' => 'Big Data Analytics', 'credits' => 4, 'type' => 'Specialization'],
                        ['code' => 'CS503', 'name' => 'Cybersecurity', 'credits' => 3, 'type' => 'Specialization']
                    ]
                ]
            ],
            'mathematics' => [
                'name' => 'Mathematics Department',
                'levels' => ['L1', 'L2', 'L3', 'M1', 'M2'],
                'courses' => [
                    'L1' => [
                        ['code' => 'MATH101', 'name' => 'Calculus I', 'credits' => 4, 'type' => 'Fundamental'],
                        ['code' => 'MATH102', 'name' => 'Linear Algebra', 'credits' => 4, 'type' => 'Fundamental']
                    ],
                    'L2' => [
                        ['code' => 'MATH201', 'name' => 'Calculus II', 'credits' => 4, 'type' => 'Fundamental'],
                        ['code' => 'MATH202', 'name' => 'Differential Equations', 'credits' => 4, 'type' => 'Fundamental']
                    ]
                ]
            ],
            'natural_life_sciences' => [
                'name' => 'Natural and Life Sciences Department',
                'levels' => ['L1', 'L2', 'L3', 'M1', 'M2'],
                'courses' => [
                    'L1' => [
                        ['code' => 'BIO101', 'name' => 'General Biology I', 'credits' => 4, 'type' => 'Fundamental'],
                        ['code' => 'CHM101', 'name' => 'General Chemistry I', 'credits' => 4, 'type' => 'Fundamental']
                    ]
                ]
            ],
            'material_sciences' => [
                'name' => 'Material Sciences Department',
                'levels' => ['L1', 'L2', 'L3', 'M1', 'M2'],
                'courses' => [
                    'L1' => [
                        ['code' => 'MAT101', 'name' => 'Introduction to Materials', 'credits' => 4, 'type' => 'Fundamental'],
                        ['code' => 'PHY101', 'name' => 'General Physics I', 'credits' => 4, 'type' => 'Fundamental']
                    ]
                ]
            ],
            'architecture' => [
                'name' => 'Architecture Department',
                'levels' => ['L1', 'L2', 'L3', 'M1', 'M2'],
                'courses' => [
                    'L1' => [
                        ['code' => 'ARCH101', 'name' => 'Introduction to Architecture', 'credits' => 4, 'type' => 'Fundamental'],
                        ['code' => 'ARCH102', 'name' => 'Architectural Drawing', 'credits' => 3, 'type' => 'Technical']
                    ]
                ]
            ]
        ]
    ]
];

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'login':
                $email = $_POST['email'];
                $password = $_POST['password'];
                
                $user = null;
                foreach ($_SESSION['users'] as $u) {
                    if ($u['email'] === $email && $u['password'] === $password) {
                        $user = $u;
                        break;
                    }
                }
                
                if ($user) {
                    $_SESSION['current_user'] = $user;
                    $_SESSION['current_role'] = $user['role'];
                    echo json_encode(['success' => true, 'message' => 'Login successful', 'role' => $user['role']]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
                }
                exit;
                
            case 'register':
                $fname = $_POST['fname'];
                $lname = $_POST['lname'];
                $email = $_POST['email'];
                $role = $_POST['role'];
                $faculty = $_POST['faculty'];
                $department = $_POST['department'];
                $password = $_POST['password'];
                
                // Validate email domain
                if (strpos($email, '@univ-alger.dz') === false) {
                    echo json_encode(['success' => false, 'message' => 'Please use a valid university email (@univ-alger.dz)']);
                    exit;
                }
                
                // Check if user exists
                $exists = false;
                foreach ($_SESSION['users'] as $u) {
                    if ($u['email'] === $email) {
                        $exists = true;
                        break;
                    }
                }
                
                if ($exists) {
                    echo json_encode(['success' => false, 'message' => 'User with this email already exists']);
                    exit;
                }
                
                // Validate faculty and department
                if (!isset($faculties[$faculty]) || !isset($faculties[$faculty]['departments'][$department])) {
                    echo json_encode(['success' => false, 'message' => 'Invalid faculty or department selected']);
                    exit;
                }
                
                // Create new user
                $newUser = [
                    'id' => count($_SESSION['users']) + 1,
                    'email' => $email,
                    'password' => $password,
                    'role' => $role,
                    'firstName' => $fname,
                    'lastName' => $lname,
                    'faculty' => $faculty,
                    'department' => $department,
                    'level' => $role === 'student' ? 'L1' : 'all',
                    'phone' => '',
                    'bio' => '',
                    'profilePicture' => null
                ];
                
                $_SESSION['users'][] = $newUser;
                
                if ($role === 'student') {
                    $_SESSION['students'][] = [
                        'id' => 'STU' . (count($_SESSION['students']) + 1),
                        'name' => $fname . ' ' . $lname,
                        'email' => $email,
                        'faculty' => $faculty,
                        'department' => $department,
                        'level' => 'L1',
                        'enrollment' => date('Y-m-d'),
                        'status' => 'active'
                    ];
                } elseif ($role === 'professor') {
                    $_SESSION['professors'][] = $newUser;
                }
                
                $_SESSION['current_user'] = $newUser;
                $_SESSION['current_role'] = $role;
                
                echo json_encode(['success' => true, 'message' => 'Registration successful', 'role' => $role]);
                exit;
                
            case 'logout':
                unset($_SESSION['current_user']);
                unset($_SESSION['current_role']);
                echo json_encode(['success' => true, 'message' => 'Logout successful']);
                exit;
                
            case 'save_attendance':
                $course = $_POST['course'];
                $date = $_POST['date'];
                $type = $_POST['type'];
                $attendance_data = $_POST['attendance'];
                
                if (!isset($_SESSION['attendance'][$_SESSION['current_user']['faculty']])) {
                    $_SESSION['attendance'][$_SESSION['current_user']['faculty']] = [];
                }
                
                if (!isset($_SESSION['attendance'][$_SESSION['current_user']['faculty']][$_SESSION['current_user']['department']])) {
                    $_SESSION['attendance'][$_SESSION['current_user']['faculty']][$_SESSION['current_user']['department']] = [];
                }
                
                if (!isset($_SESSION['attendance'][$_SESSION['current_user']['faculty']][$_SESSION['current_user']['department']][$course])) {
                    $_SESSION['attendance'][$_SESSION['current_user']['faculty']][$_SESSION['current_user']['department']][$course] = [];
                }
                
                $_SESSION['attendance'][$_SESSION['current_user']['faculty']][$_SESSION['current_user']['department']][$course][] = [
                    'date' => $date,
                    'type' => $type,
                    'records' => $attendance_data
                ];
                
                echo json_encode(['success' => true, 'message' => 'Attendance saved successfully']);
                exit;
                
            case 'add_student':
                $id = $_POST['student_id'];
                $fname = $_POST['fname'];
                $lname = $_POST['lname'];
                $email = $_POST['email'];
                $faculty = $_POST['faculty'];
                $department = $_POST['department'];
                $level = $_POST['level'];
                $enrollment = $_POST['enrollment'];
                
                $_SESSION['students'][] = [
                    'id' => $id,
                    'name' => $fname . ' ' . $lname,
                    'email' => $email,
                    'faculty' => $faculty,
                    'department' => $department,
                    'level' => $level,
                    'enrollment' => $enrollment,
                    'status' => 'active'
                ];
                
                echo json_encode(['success' => true, 'message' => 'Student added successfully']);
                exit;
        }
    }
}

// Get current user and role
$current_user = isset($_SESSION['current_user']) ? $_SESSION['current_user'] : null;
$current_role = isset($_SESSION['current_role']) ? $_SESSION['current_role'] : null;

// Determine active page
$active_page = 'login-page';
if ($current_user) {
    if (isset($_GET['page'])) {
        $active_page = $_GET['page'];
    } else {
        $active_page = $current_role . '-home';
    }
} else {
    if (isset($_GET['page'])) {
        $active_page = $_GET['page'];
    }
}

// Helper functions
function getDepartmentIcon($department) {
    $icons = [
        'computer_science' => 'fas fa-laptop-code',
        'mathematics' => 'fas fa-calculator',
        'natural_life_sciences' => 'fas fa-leaf',
        'material_sciences' => 'fas fa-atom',
        'architecture' => 'fas fa-ruler-combined',
        'administration' => 'fas fa-user-shield'
    ];
    return $icons[$department] ?? 'fas fa-building';
}

function getDepartmentDescription($department) {
    $descriptions = [
        'computer_science' => 'Programming, Algorithms, Software Engineering',
        'mathematics' => 'Algebra, Calculus, Statistics, Applied Mathematics',
        'natural_life_sciences' => 'Biology, Chemistry, Environmental Sciences',
        'material_sciences' => 'Physics, Chemistry, Material Sciences',
        'architecture' => 'Architectural Design, Urban Planning, Construction',
        'administration' => 'System Administration and Management'
    ];
    return $descriptions[$department] ?? 'Academic Department';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Algiers University - Attendance Management System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary-color: #1a4d8c;
            --secondary-color: #e63946;
            --accent-color: #2a9d8f;
            --light-color: #f8f9fa;
            --dark-color: #343a40;
            --success-color: #28a745;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --border-radius: 8px;
            --box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f5f7fa;
            color: var(--dark-color);
            line-height: 1.6;
        }
        
        .container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        /* Header Styles */
        header {
            background-color: var(--primary-color);
            color: white;
            padding: 1rem 0;
            box-shadow: var(--box-shadow);
        }
        
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .logo img {
            height: 50px;
        }
        
        .logo h1 {
            font-size: 1.5rem;
            font-weight: 600;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: var(--accent-color);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            cursor: pointer;
            background-size: cover;
            background-position: center;
        }
        
        .user-menu {
            position: relative;
        }
        
        .dropdown-menu {
            position: absolute;
            top: 100%;
            right: 0;
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            width: 200px;
            z-index: 1000;
            display: none;
            margin-top: 10px;
        }
        
        .dropdown-menu.active {
            display: block;
        }
        
        .dropdown-item {
            display: block;
            padding: 12px 15px;
            text-decoration: none;
            color: var(--dark-color);
            border-bottom: 1px solid #eee;
            transition: background-color 0.3s;
        }
        
        .dropdown-item:hover {
            background-color: #f8f9fa;
        }
        
        .dropdown-item i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        
        /* Navigation Styles */
        nav {
            background-color: white;
            box-shadow: var(--box-shadow);
        }
        
        .nav-tabs {
            display: flex;
            list-style: none;
            overflow-x: auto;
        }
        
        .nav-tabs li {
            flex-shrink: 0;
        }
        
        .nav-tabs a {
            display: block;
            padding: 15px 20px;
            text-decoration: none;
            color: var(--dark-color);
            font-weight: 500;
            border-bottom: 3px solid transparent;
            transition: all 0.3s ease;
        }
        
        .nav-tabs a:hover, .nav-tabs a.active {
            color: var(--primary-color);
            border-bottom-color: var(--primary-color);
            background-color: rgba(26, 77, 140, 0.05);
        }
        
        /* Main Content Styles */
        main {
            padding: 30px 0;
        }
        
        .dashboard-cards {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .card {
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 20px;
            transition: transform 0.3s ease;
        }
        
        .card:hover {
            transform: translateY(-5px);
        }
        
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .card-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--dark-color);
        }
        
        .card-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
        }
        
        .card-content {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 10px;
        }
        
        .card-footer {
            font-size: 0.9rem;
            color: #6c757d;
        }
        
        .card-stats {
            background-color: var(--primary-color);
        }
        
        .card-attendance {
            background-color: var(--accent-color);
        }
        
        .card-absent {
            background-color: var(--danger-color);
        }
        
        .card-justification {
            background-color: var(--warning-color);
        }
        
        /* Table Styles */
        .section-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 20px;
            color: var(--primary-color);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .table-container {
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            overflow: hidden;
            margin-bottom: 30px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        thead {
            background-color: var(--primary-color);
            color: white;
        }
        
        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #dee2e6;
        }
        
        tbody tr:hover {
            background-color: rgba(0, 0, 0, 0.02);
        }
        
        .status-present {
            color: var(--success-color);
            font-weight: 600;
        }
        
        .status-absent {
            color: var(--danger-color);
            font-weight: 600;
        }
        
        .status-pending {
            color: var(--warning-color);
            font-weight: 600;
        }
        
        .btn {
            display: inline-block;
            padding: 8px 15px;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 500;
            transition: background-color 0.3s ease;
            text-decoration: none;
        }
        
        .btn:hover {
            background-color: #0d3a6b;
        }
        
        .btn-success {
            background-color: var(--success-color);
        }
        
        .btn-success:hover {
            background-color: #218838;
        }
        
        .btn-danger {
            background-color: var(--danger-color);
        }
        
        .btn-danger:hover {
            background-color: #c82333;
        }
        
        .btn-warning {
            background-color: var(--warning-color);
            color: var(--dark-color);
        }
        
        .btn-warning:hover {
            background-color: #e0a800;
        }
        
        /* Back Button */
        .btn-back {
            background-color: #6c757d;
            margin-bottom: 20px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-back:hover {
            background-color: #5a6268;
        }
        
        /* Form Styles */
        .form-container {
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 30px;
            margin-bottom: 30px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
        }
        
        input, select, textarea {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid #ced4da;
            border-radius: 4px;
            font-size: 1rem;
        }
        
        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(26, 77, 140, 0.25);
        }
        
        .form-row {
            display: flex;
            gap: 20px;
        }
        
        .form-row .form-group {
            flex: 1;
        }
        
        /* Profile Picture Upload */
        .profile-picture-container {
            text-align: center;
            margin-bottom: 20px;
        }
        
        .profile-picture {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid var(--primary-color);
            margin-bottom: 10px;
        }
        
        .profile-picture-placeholder {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background-color: #e9ecef;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 10px;
            border: 3px solid var(--primary-color);
            font-size: 2rem;
            color: var(--primary-color);
        }
        
        .profile-upload-btn {
            display: inline-block;
            padding: 8px 15px;
            background-color: var(--primary-color);
            color: white;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .profile-upload-btn:hover {
            background-color: #0d3a6b;
        }
        
        /* Faculty Selection */
        .faculty-selection {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin: 20px 0;
        }
        
        .faculty-card {
            background: white;
            border: 2px solid #e9ecef;
            border-radius: var(--border-radius);
            padding: 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .faculty-card:hover {
            border-color: var(--primary-color);
            transform: translateY(-2px);
        }
        
        .faculty-card.selected {
            border-color: var(--primary-color);
            background-color: rgba(26, 77, 140, 0.05);
        }
        
        .faculty-icon {
            font-size: 2.5rem;
            margin-bottom: 10px;
            color: var(--primary-color);
        }
        
        .faculty-name {
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .faculty-desc {
            font-size: 0.9rem;
            color: #6c757d;
        }
        
        /* Department Selection */
        .department-selection {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
            margin: 20px 0;
        }
        
        .department-card {
            background: white;
            border: 2px solid #e9ecef;
            border-radius: var(--border-radius);
            padding: 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .department-card:hover {
            border-color: var(--accent-color);
            transform: translateY(-2px);
        }
        
        .department-card.selected {
            border-color: var(--accent-color);
            background-color: rgba(42, 157, 143, 0.05);
        }
        
        .department-icon {
            font-size: 2rem;
            margin-bottom: 10px;
            color: var(--accent-color);
        }
        
        .department-name {
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .department-desc {
            font-size: 0.85rem;
            color: #6c757d;
        }
        
        /* Level Selection */
        .level-selection {
            display: flex;
            gap: 10px;
            margin: 20px 0;
            flex-wrap: wrap;
        }
        
        .level-btn {
            padding: 10px 20px;
            background: white;
            border: 2px solid #e9ecef;
            border-radius: var(--border-radius);
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 500;
        }
        
        .level-btn:hover {
            border-color: var(--primary-color);
        }
        
        .level-btn.selected {
            background-color: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }
        
        /* Course Grid */
        .course-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }
        
        .course-card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 20px;
            transition: transform 0.3s ease;
        }
        
        .course-card:hover {
            transform: translateY(-3px);
        }
        
        .course-code {
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 5px;
        }
        
        .course-name {
            font-weight: 500;
            margin-bottom: 10px;
        }
        
        .course-info {
            font-size: 0.9rem;
            color: #6c757d;
            margin-bottom: 5px;
        }
        
        /* Tabs for different views */
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        /* Footer Styles */
        footer {
            background-color: var(--dark-color);
            color: white;
            padding: 30px 0;
            margin-top: 50px;
        }
        
        .footer-content {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 30px;
        }
        
        .footer-section {
            flex: 1;
            min-width: 250px;
        }
        
        .footer-section h3 {
            margin-bottom: 20px;
            font-size: 1.2rem;
        }
        
        .footer-section ul {
            list-style: none;
        }
        
        .footer-section ul li {
            margin-bottom: 10px;
        }
        
        .footer-section a {
            color: #adb5bd;
            text-decoration: none;
            transition: color 0.3s ease;
        }
        
        .footer-section a:hover {
            color: white;
        }
        
        .copyright {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #495057;
            color: #adb5bd;
        }
        
        /* Role-based pages */
        .role-selection {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin: 30px 0;
        }
        
        .role-card {
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 30px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 250px;
        }
        
        .role-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }
        
        .role-icon {
            font-size: 3rem;
            margin-bottom: 20px;
            color: var(--primary-color);
        }
        
        .role-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 10px;
        }
        
        /* Login Form */
        .login-container {
            max-width: 400px;
            margin: 50px auto;
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 40px;
        }
        
        .login-title {
            text-align: center;
            margin-bottom: 30px;
            color: var(--primary-color);
        }
        
        .login-logo {
            text-align: center;
            margin-bottom: 20px;
            font-size: 3rem;
            color: var(--primary-color);
        }
        
        /* Account Management */
        .account-tabs {
            display: flex;
            border-bottom: 1px solid #dee2e6;
            margin-bottom: 20px;
        }
        
        .account-tab {
            padding: 12px 20px;
            cursor: pointer;
            border-bottom: 3px solid transparent;
        }
        
        .account-tab.active {
            border-bottom-color: var(--primary-color);
            color: var(--primary-color);
            font-weight: 500;
        }
        
        .account-content {
            display: none;
        }
        
        .account-content.active {
            display: block;
        }
        
        /* Charts */
        .chart-container {
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 20px;
            margin-bottom: 30px;
            height: 400px;
        }
        
        /* Responsive Styles */
        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: 15px;
            }
            
            .dashboard-cards {
                grid-template-columns: 1fr;
            }
            
            .form-row {
                flex-direction: column;
                gap: 0;
            }
            
            .role-selection {
                flex-direction: column;
                align-items: center;
            }
            
            .faculty-selection, .department-selection {
                grid-template-columns: 1fr;
            }
            
            .course-grid {
                grid-template-columns: 1fr;
            }
        }
        
        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }
        
        .modal-content {
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            width: 90%;
            max-width: 600px;
            padding: 30px;
            position: relative;
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .modal-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--primary-color);
        }
        
        .close-modal {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--dark-color);
        }
        
        /* Toast notifications */
        .toast {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background-color: var(--success-color);
            color: white;
            padding: 15px 25px;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            z-index: 1100;
            display: none;
        }
        
        .toast.error {
            background-color: var(--danger-color);
        }
        
        .toast.warning {
            background-color: var(--warning-color);
            color: var(--dark-color);
        }

        /* Email validation styles */
        .email-validation {
            font-size: 0.8rem;
            margin-top: 5px;
            display: none;
        }

        .email-validation.valid {
            color: var(--success-color);
        }

        .email-validation.invalid {
            color: var(--danger-color);
        }
    </style>
</head>
<body>
    <!-- Header Section -->
    <header>
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <i class="fas fa-university fa-2x"></i>
                    <h1>Algiers University - Attendance Management System</h1>
                </div>
                <?php if ($current_user): ?>
                <div class="user-info">
                    <div class="user-menu">
                        <div class="user-avatar" id="user-avatar">
                            <?= substr($current_user['firstName'], 0, 1) . substr($current_user['lastName'], 0, 1) ?>
                        </div>
                        <div class="dropdown-menu" id="user-dropdown">
                            <a href="?page=account-page" class="dropdown-item">
                                <i class="fas fa-user"></i> My Profile
                            </a>
                            <a href="?page=account-page" class="dropdown-item">
                                <i class="fas fa-cog"></i> Account Settings
                            </a>
                            <a href="#" class="dropdown-item" id="logout-link">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </a>
                        </div>
                    </div>
                    <div>
                        <div id="user-name"><?= $current_user['firstName'] . ' ' . $current_user['lastName'] ?></div>
                        <div id="user-role" style="font-size: 0.8rem; opacity: 0.8;">
                            <?= ucfirst($current_user['role']) ?> - 
                            <?= 
                                isset($faculties[$current_user['faculty']]['departments'][$current_user['department']]) 
                                ? $faculties[$current_user['faculty']]['departments'][$current_user['department']]['name'] 
                                : $current_user['department'] 
                            ?>
                        </div>
                    </div>
                </div>
                <?php else: ?>
                <div id="login-header" style="display: flex; gap: 10px;">
                    <button id="login-btn" class="btn">Login</button>
                    <button id="signup-btn" class="btn btn-success">Sign Up</button>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <!-- Navigation Section -->
    <?php if ($current_user): ?>
    <nav>
        <div class="container">
            <ul class="nav-tabs" id="main-nav">
                <?php
                $nav_items = [];
                if ($current_role === 'professor') {
                    $nav_items = [
                        ['id' => 'professor-home', 'text' => 'Dashboard', 'icon' => 'tachometer-alt'],
                        ['id' => 'professor-session', 'text' => 'Take Attendance', 'icon' => 'clipboard-list'],
                        ['id' => 'professor-summary', 'text' => 'Attendance Summary', 'icon' => 'chart-bar']
                    ];
                } elseif ($current_role === 'student') {
                    $nav_items = [
                        ['id' => 'student-home', 'text' => 'Dashboard', 'icon' => 'tachometer-alt'],
                        ['id' => 'student-attendance', 'text' => 'My Attendance', 'icon' => 'calendar-check']
                    ];
                } elseif ($current_role === 'admin') {
                    $nav_items = [
                        ['id' => 'admin-home', 'text' => 'Dashboard', 'icon' => 'tachometer-alt'],
                        ['id' => 'admin-statistics', 'text' => 'Statistics', 'icon' => 'chart-pie'],
                        ['id' => 'admin-students', 'text' => 'Student Management', 'icon' => 'user-graduate']
                    ];
                }
                
                foreach ($nav_items as $item) {
                    $active_class = ($active_page === $item['id']) ? 'active' : '';
                    echo "<li><a href=\"?page={$item['id']}\" class=\"$active_class\"><i class=\"fas fa-{$item['icon']}\"></i> {$item['text']}</a></li>";
                }
                ?>
            </ul>
        </div>
    </nav>
    <?php endif; ?>

    <!-- Main Content Section -->
    <main class="container">
        <?php if (!$current_user): ?>
        <!-- Login Page -->
        <div id="login-page" class="tab-content <?= $active_page === 'login-page' ? 'active' : '' ?>">
            <div class="login-container">
                <div class="login-logo">
                    <i class="fas fa-university"></i>
                </div>
                <h2 class="login-title">Welcome to Algiers University</h2>
                
                <form id="login-form">
                    <div class="form-group">
                        <label for="login-email">Email</label>
                        <input type="email" id="login-email" required>
                        <div class="email-validation" id="login-email-validation">Please use a valid university email (@univ-alger.dz)</div>
                    </div>
                    
                    <div class="form-group">
                        <label for="login-password">Password</label>
                        <input type="password" id="login-password" required>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn" style="width: 100%;">Login</button>
                    </div>
                    
                    <div style="text-align: center; margin-top: 20px;">
                        <p>Don't have an account? <a href="#" id="show-signup">Sign up here</a></p>
                    </div>
                </form>
            </div>
        </div>

        <!-- Sign Up Page -->
        <div id="signup-page" class="tab-content <?= $active_page === 'signup-page' ? 'active' : '' ?>">
            <button class="btn btn-back" id="back-to-login-from-signup">
                <i class="fas fa-arrow-left"></i> Back to Login
            </button>
            
            <div class="login-container" style="max-width: 700px;">
                <div class="login-logo">
                    <i class="fas fa-user-plus"></i>
                </div>
                <h2 class="login-title">Create Account</h2>
                
                <form id="signup-form">
                    <!-- Profile Picture Upload -->
                    <div class="profile-picture-container">
                        <div class="profile-picture-placeholder" id="profile-preview">
                            <i class="fas fa-user"></i>
                        </div>
                        <input type="file" id="profile-picture" accept="image/*" style="display: none;">
                        <label for="profile-picture" class="profile-upload-btn">
                            <i class="fas fa-camera"></i> Upload Profile Picture
                        </label>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="signup-fname">First Name</label>
                            <input type="text" id="signup-fname" required>
                        </div>
                        <div class="form-group">
                            <label for="signup-lname">Last Name</label>
                            <input type="text" id="signup-lname" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="signup-email">Email</label>
                        <input type="email" id="signup-email" required>
                        <div class="email-validation" id="signup-email-validation">Please use a valid university email (@univ-alger.dz)</div>
                    </div>
                    
                    <div class="form-group">
                        <label for="signup-role">Account Type</label>
                        <select id="signup-role" required>
                            <option value="">-- Select Role --</option>
                            <option value="professor">Professor</option>
                            <option value="student">Student</option>
                            <option value="admin">Administrator</option>
                        </select>
                    </div>
                    
                    <!-- Faculty Selection -->
                    <div class="form-group">
                        <label>Select Faculty</label>
                        <div class="faculty-selection" id="faculty-selection">
                            <?php foreach ($faculties as $key => $faculty): ?>
                            <div class="faculty-card" data-faculty="<?= $key ?>">
                                <div class="faculty-icon">
                                    <i class="fas fa-flask"></i>
                                </div>
                                <div class="faculty-name"><?= $faculty['name'] ?></div>
                                <div class="faculty-desc">Natural Sciences, Mathematics, Computer Science</div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <input type="hidden" id="selected-faculty" name="faculty" required>
                    </div>
                    
                    <!-- Department Selection -->
                    <div class="form-group" id="department-selection-container" style="display: none;">
                        <label>Select Department</label>
                        <div class="department-selection" id="department-selection">
                            <!-- Departments will be populated based on faculty selection -->
                        </div>
                        <input type="hidden" id="selected-department" name="department" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="signup-password">Password</label>
                        <input type="password" id="signup-password" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="signup-confirm-password">Confirm Password</label>
                        <input type="password" id="signup-confirm-password" required>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-success" style="width: 100%;">Create Account</button>
                    </div>
                </form>
            </div>
        </div>
        <?php else: ?>
        
        <!-- Account Management Page -->
        <?php if ($active_page === 'account-page'): ?>
        <div id="account-page" class="tab-content active">
            <a href="?page=<?= $current_role ?>-home" class="btn btn-back">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
            
            <h2 class="section-title"><i class="fas fa-user-cog"></i> Account Management</h2>
            
            <div class="account-tabs">
                <div class="account-tab active" data-tab="profile">Personal Information</div>
                <div class="account-tab" data-tab="security">Security Settings</div>
                <div class="account-tab" data-tab="preferences">Preferences</div>
            </div>
            
            <!-- Profile Tab -->
            <div id="profile-tab" class="account-content active">
                <div class="form-container">
                    <h3 style="margin-bottom: 20px;">Personal Information</h3>
                    
                    <!-- Profile Picture in Account Management -->
                    <div class="profile-picture-container">
                        <div class="profile-picture-placeholder" id="account-profile-preview">
                            <i class="fas fa-user"></i>
                        </div>
                        <input type="file" id="account-profile-picture" accept="image/*" style="display: none;">
                        <label for="account-profile-picture" class="profile-upload-btn">
                            <i class="fas fa-camera"></i> Change Profile Picture
                        </label>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="profile-fname">First Name</label>
                            <input type="text" id="profile-fname" value="<?= $current_user['firstName'] ?>">
                        </div>
                        <div class="form-group">
                            <label for="profile-lname">Last Name</label>
                            <input type="text" id="profile-lname" value="<?= $current_user['lastName'] ?>">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="profile-email">Email</label>
                        <input type="email" id="profile-email" value="<?= $current_user['email'] ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="profile-phone">Phone Number</label>
                        <input type="tel" id="profile-phone" value="<?= $current_user['phone'] ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="profile-faculty">Faculty</label>
                        <input type="text" id="profile-faculty" value="<?= 
                            isset($faculties[$current_user['faculty']]) 
                            ? $faculties[$current_user['faculty']]['name'] 
                            : $current_user['faculty'] 
                        ?>" readonly>
                    </div>
                    
                    <div class="form-group">
                        <label for="profile-department">Department</label>
                        <input type="text" id="profile-department" value="<?= 
                            isset($faculties[$current_user['faculty']]['departments'][$current_user['department']]) 
                            ? $faculties[$current_user['faculty']]['departments'][$current_user['department']]['name'] 
                            : $current_user['department'] 
                        ?>" readonly>
                    </div>
                    
                    <div class="form-group">
                        <label for="profile-bio">Bio</label>
                        <textarea id="profile-bio" rows="4"><?= $current_user['bio'] ?></textarea>
                    </div>
                    
                    <button id="save-profile-btn" class="btn btn-success">Save Changes</button>
                </div>
            </div>
            
            <!-- Security Tab -->
            <div id="security-tab" class="account-content">
                <div class="form-container">
                    <h3 style="margin-bottom: 20px;">Security Settings</h3>
                    
                    <div class="form-group">
                        <label for="current-password">Current Password</label>
                        <input type="password" id="current-password">
                    </div>
                    
                    <div class="form-group">
                        <label for="new-password">New Password</label>
                        <input type="password" id="new-password">
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm-new-password">Confirm New Password</label>
                        <input type="password" id="confirm-new-password">
                    </div>
                    
                    <button id="change-password-btn" class="btn btn-success">Change Password</button>
                </div>
            </div>
            
            <!-- Preferences Tab -->
            <div id="preferences-tab" class="account-content">
                <div class="form-container">
                    <h3 style="margin-bottom: 20px;">Preferences</h3>
                    
                    <div class="form-group">
                        <label for="pref-language">Language</label>
                        <select id="pref-language">
                            <option value="en">English</option>
                            <option value="fr" selected>French</option>
                            <option value="ar">Arabic</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="pref-timezone">Timezone</label>
                        <select id="pref-timezone">
                            <option value="+1" selected>Central European Time (CET)</option>
                            <option value="+0">Greenwich Mean Time (GMT)</option>
                            <option value="-5">Eastern Standard Time (EST)</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="pref-email-notifications">
                            <input type="checkbox" id="pref-email-notifications" checked> Email Notifications
                        </label>
                    </div>
                    
                    <div class="form-group">
                        <label for="pref-sms-notifications">
                            <input type="checkbox" id="pref-sms-notifications"> SMS Notifications
                        </label>
                    </div>
                    
                    <div class="form-group">
                        <label for="pref-auto-logout">
                            <input type="checkbox" id="pref-auto-logout" checked> Auto-logout after 30 minutes of inactivity
                        </label>
                    </div>
                    
                    <button id="save-preferences-btn" class="btn btn-success">Save Preferences</button>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Professor Pages -->
        <?php if ($current_role === 'professor'): ?>
        
        <!-- Professor Home Page -->
        <?php if ($active_page === 'professor-home'): ?>
        <div id="professor-home" class="tab-content active">
            <a href="?page=login-page" class="btn btn-back">
                <i class="fas fa-arrow-left"></i> Back to Login
            </a>
            <h2 class="section-title"><i class="fas fa-tachometer-alt"></i> Professor Dashboard</h2>
            <div class="card" style="margin-bottom: 20px;">
                <div class="card-header">
                    <div class="card-title">Department Information</div>
                </div>
                <div class="card-content" style="font-size: 1.2rem;">
                    <span id="professor-faculty-name">
                        <?= isset($faculties[$current_user['faculty']]) ? $faculties[$current_user['faculty']]['name'] : $current_user['faculty'] ?>
                    </span> - 
                    <span id="professor-department-name">
                        <?= 
                            isset($faculties[$current_user['faculty']]['departments'][$current_user['department']]) 
                            ? $faculties[$current_user['faculty']]['departments'][$current_user['department']]['name'] 
                            : $current_user['department'] 
                        ?>
                    </span>
                </div>
            </div>
            
            <div class="dashboard-cards">
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">My Courses</div>
                        <div class="card-icon card-stats">
                            <i class="fas fa-book"></i>
                        </div>
                    </div>
                    <div class="card-content" id="professor-course-count">
                        <?php
                        $course_count = 0;
                        if (isset($faculties[$current_user['faculty']]['departments'][$current_user['department']])) {
                            foreach ($faculties[$current_user['faculty']]['departments'][$current_user['department']]['courses'] as $level_courses) {
                                $course_count += count($level_courses);
                            }
                        }
                        echo $course_count;
                        ?>
                    </div>
                    <div class="card-footer">Active this semester</div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">Today's Attendance</div>
                        <div class="card-icon card-attendance">
                            <i class="fas fa-clipboard-check"></i>
                        </div>
                    </div>
                    <div class="card-content">87%</div>
                    <div class="card-footer">45/52 students present</div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">Pending Justifications</div>
                        <div class="card-icon card-justification">
                            <i class="fas fa-file-alt"></i>
                        </div>
                    </div>
                    <div class="card-content">8</div>
                    <div class="card-footer">Requiring review</div>
                </div>
            </div>
            
            <!-- Level Selection -->
            <div class="form-container">
                <h3 style="margin-bottom: 15px;">Select Academic Level</h3>
                <div class="level-selection" id="professor-level-selection">
                    <?php
                    if (isset($faculties[$current_user['faculty']]['departments'][$current_user['department']])) {
                        foreach ($faculties[$current_user['faculty']]['departments'][$current_user['department']]['levels'] as $level) {
                            echo "<button class=\"level-btn\" data-level=\"$level\">$level</button>";
                        }
                    }
                    ?>
                </div>
            </div>
            
            <!-- Courses Grid -->
            <div id="professor-courses-container">
                <h3 style="margin-bottom: 20px;">My Courses</h3>
                <div class="course-grid" id="professor-courses-grid">
                    <?php
                    if (isset($faculties[$current_user['faculty']]['departments'][$current_user['department']])) {
                        $first_level = $faculties[$current_user['faculty']]['departments'][$current_user['department']]['levels'][0];
                        if (isset($faculties[$current_user['faculty']]['departments'][$current_user['department']]['courses'][$first_level])) {
                            foreach ($faculties[$current_user['faculty']]['departments'][$current_user['department']]['courses'][$first_level] as $course) {
                                echo "
                                <div class=\"course-card\">
                                    <div class=\"course-code\">{$course['code']}</div>
                                    <div class=\"course-name\">{$course['name']}</div>
                                    <div class=\"course-info\">Credits: {$course['credits']} | Type: {$course['type']}</div>
                                    <div style=\"margin-top: 15px;\">
                                        <a href=\"?page=professor-session&course={$course['code']}\" class=\"btn\" style=\"margin-right: 10px;\">
                                            Take Attendance
                                        </a>
                                        <a href=\"?page=professor-summary&course={$course['code']}\" class=\"btn\">
                                            View Sessions
                                        </a>
                                    </div>
                                </div>
                                ";
                            }
                        }
                    }
                    ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Professor Session Page -->
        <?php if ($active_page === 'professor-session'): ?>
        <div id="professor-session" class="tab-content active">
            <a href="?page=professor-home" class="btn btn-back">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
            <h2 class="section-title"><i class="fas fa-clipboard-list"></i> Take Attendance</h2>
            <div class="card" style="margin-bottom: 20px;">
                <div class="card-content" style="font-size: 1.1rem;">
                    <span id="session-faculty-name">
                        <?= isset($faculties[$current_user['faculty']]) ? $faculties[$current_user['faculty']]['name'] : $current_user['faculty'] ?>
                    </span> - 
                    <span id="session-department-name">
                        <?= 
                            isset($faculties[$current_user['faculty']]['departments'][$current_user['department']]) 
                            ? $faculties[$current_user['faculty']]['departments'][$current_user['department']]['name'] 
                            : $current_user['department'] 
                        ?>
                    </span>
                </div>
            </div>
            
            <div class="form-container">
                <div class="form-row">
                    <div class="form-group">
                        <label for="course-select">Select Course</label>
                        <select id="course-select">
                            <option value="">-- Select a course --</option>
                            <?php
                            if (isset($faculties[$current_user['faculty']]['departments'][$current_user['department']])) {
                                foreach ($faculties[$current_user['faculty']]['departments'][$current_user['department']]['levels'] as $level) {
                                    if (isset($faculties[$current_user['faculty']]['departments'][$current_user['department']]['courses'][$level])) {
                                        foreach ($faculties[$current_user['faculty']]['departments'][$current_user['department']]['courses'][$level] as $course) {
                                            $selected = (isset($_GET['course']) && $_GET['course'] === $course['code']) ? 'selected' : '';
                                            echo "<option value=\"{$course['code']}\" $selected>{$course['code']} - {$course['name']}</option>";
                                        }
                                    }
                                }
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="session-date">Date</label>
                        <input type="date" id="session-date" value="<?= date('Y-m-d') ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="session-type">Session Type</label>
                    <select id="session-type">
                        <option value="lecture">Lecture</option>
                        <option value="lab">Lab</option>
                        <option value="tutorial">Tutorial</option>
                    </select>
                </div>
                
                <button id="load-students-btn" class="btn">Load Student List</button>
            </div>
            
            <div class="table-container">
                <h3 style="padding: 15px 20px; border-bottom: 1px solid #dee2e6;" id="session-title">Student Attendance</h3>
                <table id="attendance-table">
                    <thead>
                        <tr>
                            <th>Student ID</th>
                            <th>Student Name</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $department_students = array_filter($_SESSION['students'], function($student) use ($current_user) {
                            return $student['faculty'] === $current_user['faculty'] && $student['department'] === $current_user['department'];
                        });
                        
                        if (empty($department_students)) {
                            echo '
                            <tr>
                                <td colspan="4" style="text-align: center; padding: 20px;">
                                    No students found for this department. Please add students through the admin panel.
                                </td>
                            </tr>
                            ';
                        } else {
                            foreach ($department_students as $student) {
                                echo "
                                <tr>
                                    <td>{$student['id']}</td>
                                    <td>{$student['name']}</td>
                                    <td class=\"status-present\">Present</td>
                                    <td>
                                        <button class=\"btn btn-danger toggle-status\" data-student=\"{$student['id']}\">
                                            Mark Absent
                                        </button>
                                    </td>
                                </tr>
                                ";
                            }
                        }
                        ?>
                    </tbody>
                </table>
                
                <div style="padding: 15px 20px; text-align: right;">
                    <button id="save-attendance-btn" class="btn btn-success">Save Attendance</button>
                    <button id="export-attendance-btn" class="btn">Export to Excel</button>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Professor Summary Page -->
        <?php if ($active_page === 'professor-summary'): ?>
        <div id="professor-summary" class="tab-content active">
            <a href="?page=professor-home" class="btn btn-back">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
            <h2 class="section-title"><i class="fas fa-chart-bar"></i> Attendance Summary</h2>
            <div class="card" style="margin-bottom: 20px;">
                <div class="card-content" style="font-size: 1.1rem;">
                    <span id="summary-faculty-name">
                        <?= isset($faculties[$current_user['faculty']]) ? $faculties[$current_user['faculty']]['name'] : $current_user['faculty'] ?>
                    </span> - 
                    <span id="summary-department-name">
                        <?= 
                            isset($faculties[$current_user['faculty']]['departments'][$current_user['department']]) 
                            ? $faculties[$current_user['faculty']]['departments'][$current_user['department']]['name'] 
                            : $current_user['department'] 
                        ?>
                    </span>
                </div>
            </div>
            
            <div class="form-container">
                <div class="form-row">
                    <div class="form-group">
                        <label for="summary-course">Course</label>
                        <select id="summary-course">
                            <option value="">All Courses</option>
                            <?php
                            if (isset($faculties[$current_user['faculty']]['departments'][$current_user['department']])) {
                                foreach ($faculties[$current_user['faculty']]['departments'][$current_user['department']]['levels'] as $level) {
                                    if (isset($faculties[$current_user['faculty']]['departments'][$current_user['department']]['courses'][$level])) {
                                        foreach ($faculties[$current_user['faculty']]['departments'][$current_user['department']]['courses'][$level] as $course) {
                                            $selected = (isset($_GET['course']) && $_GET['course'] === $course['code']) ? 'selected' : '';
                                            echo "<option value=\"{$course['code']}\" $selected>{$course['code']} - {$course['name']}</option>";
                                        }
                                    }
                                }
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="summary-period">Period</label>
                        <select id="summary-period">
                            <option value="week">Last 7 days</option>
                            <option value="month" selected>Last 30 days</option>
                            <option value="semester">This Semester</option>
                        </select>
                    </div>
                </div>
                
                <button id="generate-summary-btn" class="btn">Generate Summary</button>
            </div>
            
            <div class="table-container">
                <h3 style="padding: 15px 20px; border-bottom: 1px solid #dee2e6;">Attendance Summary</h3>
                <table id="summary-table">
                    <thead>
                        <tr>
                            <th>Student ID</th>
                            <th>Student Name</th>
                            <th>Total Sessions</th>
                            <th>Present</th>
                            <th>Absent</th>
                            <th>Attendance Rate</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $department_students = array_filter($_SESSION['students'], function($student) use ($current_user) {
                            return $student['faculty'] === $current_user['faculty'] && $student['department'] === $current_user['department'];
                        });
                        
                        if (empty($department_students)) {
                            echo '
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 20px;">
                                    No students found for this department. Please add students through the admin panel.
                                </td>
                            </tr>
                            ';
                        } else {
                            $selected_course = isset($_GET['course']) ? $_GET['course'] : '';
                            
                            foreach ($department_students as $student) {
                                $total_sessions = 0;
                                $present_count = 0;
                                
                                // Calculate attendance for the student
                                if (isset($_SESSION['attendance'][$current_user['faculty']][$current_user['department']])) {
                                    foreach ($_SESSION['attendance'][$current_user['faculty']][$current_user['department']] as $course_code => $sessions) {
                                        if ($selected_course && $course_code !== $selected_course) {
                                            continue;
                                        }
                                        
                                        foreach ($sessions as $session) {
                                            $total_sessions++;
                                            foreach ($session['records'] as $record) {
                                                if ($record['studentId'] === $student['id'] && $record['status'] === 'present') {
                                                    $present_count++;
                                                    break;
                                                }
                                            }
                                        }
                                    }
                                }
                                
                                $attendance_rate = $total_sessions > 0 ? round(($present_count / $total_sessions) * 100) : 0;
                                $status = 'Good';
                                if ($attendance_rate < 70) $status = 'At Risk';
                                else if ($attendance_rate < 80) $status = 'Warning';
                                
                                $status_class = $attendance_rate >= 80 ? 'present' : 'absent';
                                
                                echo "
                                <tr>
                                    <td>{$student['id']}</td>
                                    <td>{$student['name']}</td>
                                    <td>$total_sessions</td>
                                    <td>$present_count</td>
                                    <td>" . ($total_sessions - $present_count) . "</td>
                                    <td class=\"status-$status_class\">$attendance_rate%</td>
                                    <td>$status</td>
                                </tr>
                                ";
                            }
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>
        
        <?php endif; ?>

        <!-- Student Pages -->
        <?php if ($current_role === 'student'): ?>
        
        <!-- Student Home Page -->
        <?php if ($active_page === 'student-home'): ?>
        <div id="student-home" class="tab-content active">
            <a href="?page=login-page" class="btn btn-back">
                <i class="fas fa-arrow-left"></i> Back to Login
            </a>
            <h2 class="section-title"><i class="fas fa-user-graduate"></i> Student Dashboard</h2>
            <div class="card" style="margin-bottom: 20px;">
                <div class="card-header">
                    <div class="card-title">Academic Information</div>
                </div>
                <div class="card-content" style="font-size: 1.2rem;">
                    <span id="student-faculty-name">
                        <?= isset($faculties[$current_user['faculty']]) ? $faculties[$current_user['faculty']]['name'] : $current_user['faculty'] ?>
                    </span> - 
                    <span id="student-department-name">
                        <?= 
                            isset($faculties[$current_user['faculty']]['departments'][$current_user['department']]) 
                            ? $faculties[$current_user['faculty']]['departments'][$current_user['department']]['name'] 
                            : $current_user['department'] 
                        ?>
                    </span>
                </div>
            </div>
            
            <div class="dashboard-cards">
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">My Courses</div>
                        <div class="card-icon card-stats">
                            <i class="fas fa-book"></i>
                        </div>
                    </div>
                    <div class="card-content">
                        <?php
                        $course_count = 0;
                        if (isset($faculties[$current_user['faculty']]['departments'][$current_user['department']])) {
                            foreach ($faculties[$current_user['faculty']]['departments'][$current_user['department']]['courses'] as $level_courses) {
                                $course_count += count($level_courses);
                            }
                        }
                        echo $course_count;
                        ?>
                    </div>
                    <div class="card-footer">Enrolled this semester</div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">Overall Attendance</div>
                        <div class="card-icon card-attendance">
                            <i class="fas fa-clipboard-check"></i>
                        </div>
                    </div>
                    <div class="card-content">89%</div>
                    <div class="card-footer">Across all courses</div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">Absences</div>
                        <div class="card-icon card-absent">
                            <i class="fas fa-user-times"></i>
                        </div>
                    </div>
                    <div class="card-content">7</div>
                    <div class="card-footer">This semester</div>
                </div>
            </div>
            
            <!-- Level Selection for Students -->
            <div class="form-container">
                <h3 style="margin-bottom: 15px;">Select Academic Level</h3>
                <div class="level-selection" id="student-level-selection">
                    <?php
                    if (isset($faculties[$current_user['faculty']]['departments'][$current_user['department']])) {
                        foreach ($faculties[$current_user['faculty']]['departments'][$current_user['department']]['levels'] as $level) {
                            echo "<button class=\"level-btn student-level-btn\" data-level=\"$level\">$level</button>";
                        }
                    }
                    ?>
                </div>
            </div>
            
            <!-- Student Courses Grid -->
            <div id="student-courses-container">
                <h3 style="margin-bottom: 20px;">My Courses</h3>
                <div class="course-grid" id="student-courses-grid">
                    <?php
                    if (isset($faculties[$current_user['faculty']]['departments'][$current_user['department']])) {
                        $first_level = $faculties[$current_user['faculty']]['departments'][$current_user['department']]['levels'][0];
                        if (isset($faculties[$current_user['faculty']]['departments'][$current_user['department']]['courses'][$first_level])) {
                            foreach ($faculties[$current_user['faculty']]['departments'][$current_user['department']]['courses'][$first_level] as $course) {
                                echo "
                                <div class=\"course-card\">
                                    <div class=\"course-code\">{$course['code']}</div>
                                    <div class=\"course-name\">{$course['name']}</div>
                                    <div class=\"course-info\">Credits: {$course['credits']} | Type: {$course['type']}</div>
                                    <div style=\"margin-top: 15px;\">
                                        <a href=\"?page=student-attendance&course={$course['code']}\" class=\"btn\">
                                            View Attendance
                                        </a>
                                    </div>
                                </div>
                                ";
                            }
                        }
                    }
                    ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Student Attendance Page -->
        <?php if ($active_page === 'student-attendance'): ?>
        <div id="student-attendance" class="tab-content active">
            <a href="?page=student-home" class="btn btn-back">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
            <h2 class="section-title"><i class="fas fa-calendar-check"></i> My Attendance</h2>
            <div class="card" style="margin-bottom: 20px;">
                <div class="card-content" style="font-size: 1.1rem;">
                    <span id="student-attendance-faculty-name">
                        <?= isset($faculties[$current_user['faculty']]) ? $faculties[$current_user['faculty']]['name'] : $current_user['faculty'] ?>
                    </span> - 
                    <span id="student-attendance-department-name">
                        <?= 
                            isset($faculties[$current_user['faculty']]['departments'][$current_user['department']]) 
                            ? $faculties[$current_user['faculty']]['departments'][$current_user['department']]['name'] 
                            : $current_user['department'] 
                        ?>
                    </span>
                </div>
            </div>
            
            <div class="form-container">
                <div class="form-group">
                    <label for="student-course-select">Select Course</label>
                    <select id="student-course-select">
                        <option value="">-- Select a course --</option>
                        <?php
                        if (isset($faculties[$current_user['faculty']]['departments'][$current_user['department']])) {
                            foreach ($faculties[$current_user['faculty']]['departments'][$current_user['department']]['levels'] as $level) {
                                if (isset($faculties[$current_user['faculty']]['departments'][$current_user['department']]['courses'][$level])) {
                                    foreach ($faculties[$current_user['faculty']]['departments'][$current_user['department']]['courses'][$level] as $course) {
                                        $selected = (isset($_GET['course']) && $_GET['course'] === $course['code']) ? 'selected' : '';
                                        echo "<option value=\"{$course['code']}\" $selected>{$course['code']} - {$course['name']}</option>";
                                    }
                                }
                            }
                        }
                        ?>
                    </select>
                </div>
                
                <button id="load-student-attendance-btn" class="btn">Load Attendance</button>
            </div>
            
            <div class="table-container">
                <h3 style="padding: 15px 20px; border-bottom: 1px solid #dee2e6;" id="student-attendance-title">Attendance Records</h3>
                <table id="student-attendance-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Session Type</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $selected_course = isset($_GET['course']) ? $_GET['course'] : '';
                        
                        if ($selected_course && isset($_SESSION['attendance'][$current_user['faculty']][$current_user['department']][$selected_course])) {
                            foreach ($_SESSION['attendance'][$current_user['faculty']][$current_user['department']][$selected_course] as $session) {
                                $status = 'absent';
                                foreach ($session['records'] as $record) {
                                    // In a real app, we would check the actual student ID
                                    if ($record['studentId'] === $current_user['id']) {
                                        $status = $record['status'];
                                        break;
                                    }
                                }
                                
                                $status_class = "status-$status";
                                $status_display = ucfirst($status);
                                
                                $justify_button = '';
                                if ($status === 'absent') {
                                    $justify_button = "<button class=\"btn btn-warning justify-absence\" data-date=\"{$session['date']}\">Justify Absence</button>";
                                }
                                
                                echo "
                                <tr>
                                    <td>{$session['date']}</td>
                                    <td>" . ucfirst($session['type']) . "</td>
                                    <td class=\"$status_class\">$status_display</td>
                                    <td>$justify_button</td>
                                </tr>
                                ";
                            }
                        } else {
                            echo '
                            <tr>
                                <td colspan="4" style="text-align: center; padding: 20px;">
                                    No attendance records found for this course.
                                </td>
                            </tr>
                            ';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Justification Form -->
            <div class="form-container" id="justification-form">
                <h3 style="margin-bottom: 20px;">Submit Absence Justification</h3>
                
                <div class="form-group">
                    <label for="absence-date">Date of Absence</label>
                    <input type="date" id="absence-date" value="<?= date('Y-m-d') ?>">
                </div>
                
                <div class="form-group">
                    <label for="justification-type">Justification Type</label>
                    <select id="justification-type">
                        <option value="medical">Medical</option>
                        <option value="personal">Personal</option>
                        <option value="family">Family Emergency</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="justification-details">Details</label>
                    <textarea id="justification-details" rows="4" placeholder="Please provide details for your absence..."></textarea>
                </div>
                
                <div class="form-group">
                    <label for="supporting-docs">Supporting Documents (Optional)</label>
                    <input type="file" id="supporting-docs">
                </div>
                
                <button id="submit-justification-btn" class="btn btn-success">Submit Justification</button>
            </div>
        </div>
        <?php endif; ?>
        
        <?php endif; ?>

        <!-- Administrator Pages -->
        <?php if ($current_role === 'admin'): ?>
        
        <!-- Admin Home Page -->
        <?php if ($active_page === 'admin-home'): ?>
        <div id="admin-home" class="tab-content active">
            <a href="?page=login-page" class="btn btn-back">
                <i class="fas fa-arrow-left"></i> Back to Login
            </a>
            <h2 class="section-title"><i class="fas fa-user-shield"></i> Administrator Dashboard</h2>
            
            <div class="dashboard-cards">
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">Total Students</div>
                        <div class="card-icon card-stats">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                    <div class="card-content"><?= count($_SESSION['students']) ?></div>
                    <div class="card-footer">Registered in system</div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">Total Professors</div>
                        <div class="card-icon card-attendance">
                            <i class="fas fa-chalkboard-teacher"></i>
                        </div>
                    </div>
                    <div class="card-content"><?= count($_SESSION['professors']) ?></div>
                    <div class="card-footer">Active this semester</div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">System Usage</div>
                        <div class="card-icon card-justification">
                            <i class="fas fa-chart-line"></i>
                        </div>
                    </div>
                    <div class="card-content">
                        <?php
                        $total_courses = 0;
                        $active_courses = 0;
                        
                        foreach ($faculties as $faculty) {
                            foreach ($faculty['departments'] as $department) {
                                foreach ($department['courses'] as $level_courses) {
                                    $total_courses += count($level_courses);
                                }
                            }
                        }
                        
                        // In a real app, we would check which courses have attendance records
                        $usage_rate = $total_courses > 0 ? round((count($_SESSION['attendance']) / $total_courses) * 100) : 0;
                        echo $usage_rate . '%';
                        ?>
                    </div>
                    <div class="card-footer">Active courses using system</div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">Pending Actions</div>
                        <div class="card-icon card-absent">
                            <i class="fas fa-tasks"></i>
                        </div>
                    </div>
                    <div class="card-content"><?= count($_SESSION['justifications']) ?></div>
                    <div class="card-footer">Requiring attention</div>
                </div>
            </div>
            
            <div class="table-container">
                <h3 style="padding: 15px 20px; border-bottom: 1px solid #dee2e6;">Recent System Activity</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Date & Time</th>
                            <th>User</th>
                            <th>Faculty</th>
                            <th>Department</th>
                            <th>Action</th>
                            <th>Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><?= date('Y-m-d H:i:s') ?></td>
                            <td><?= $current_user['firstName'] . ' ' . $current_user['lastName'] ?></td>
                            <td>Administration</td>
                            <td>Administration</td>
                            <td>Login</td>
                            <td>User logged into the system</td>
                        </tr>
                        <!-- More activity rows would be added dynamically -->
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Admin Statistics Page -->
        <?php if ($active_page === 'admin-statistics'): ?>
        <div id="admin-statistics" class="tab-content active">
            <a href="?page=admin-home" class="btn btn-back">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
            <h2 class="section-title"><i class="fas fa-chart-pie"></i> System Statistics</h2>
            
            <div class="form-container">
                <div class="form-row">
                    <div class="form-group">
                        <label for="stats-period">Time Period</label>
                        <select id="stats-period">
                            <option value="week">Last 7 days</option>
                            <option value="month" selected>Last 30 days</option>
                            <option value="semester">This Semester</option>
                            <option value="year">This Academic Year</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="stats-faculty">Faculty</label>
                        <select id="stats-faculty">
                            <option value="">All Faculties</option>
                            <?php foreach ($faculties as $key => $faculty): ?>
                            <option value="<?= $key ?>"><?= $faculty['name'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <button id="generate-stats-btn" class="btn">Generate Statistics</button>
            </div>
            
            <div class="dashboard-cards">
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">Overall Attendance Rate</div>
                        <div class="card-icon card-stats">
                            <i class="fas fa-percentage"></i>
                        </div>
                    </div>
                    <div class="card-content">
                        <?php
                        $total_sessions = 0;
                        $present_count = 0;
                        
                        foreach ($_SESSION['attendance'] as $faculty_attendance) {
                            foreach ($faculty_attendance as $department_attendance) {
                                foreach ($department_attendance as $course_sessions) {
                                    foreach ($course_sessions as $session) {
                                        $total_sessions += count($session['records']);
                                        foreach ($session['records'] as $record) {
                                            if ($record['status'] === 'present') {
                                                $present_count++;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                        
                        $overall_rate = $total_sessions > 0 ? round(($present_count / $total_sessions) * 100) : 0;
                        echo $overall_rate . '%';
                        ?>
                    </div>
                    <div class="card-footer">
                        <?= $present_count ?> present out of <?= $total_sessions ?> sessions
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">Courses with Lowest Attendance</div>
                        <div class="card-icon card-absent">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                    </div>
                    <div class="card-content">
                        <?= count($_SESSION['attendance']) > 0 ? count($_SESSION['attendance']) : 'No' ?> courses
                    </div>
                    <div class="card-footer">
                        <?= count($_SESSION['attendance']) > 0 ? 'With attendance records' : 'No courses available' ?>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">Justification Approval Rate</div>
                        <div class="card-icon card-justification">
                            <i class="fas fa-file-check"></i>
                        </div>
                    </div>
                    <div class="card-content">
                        <?php
                        $total_justifications = count($_SESSION['justifications']);
                        $approved_justifications = 0;
                        
                        foreach ($_SESSION['justifications'] as $justification) {
                            if ($justification['status'] === 'approved') {
                                $approved_justifications++;
                            }
                        }
                        
                        $approval_rate = $total_justifications > 0 ? round(($approved_justifications / $total_justifications) * 100) : 0;
                        echo $approval_rate . '%';
                        ?>
                    </div>
                    <div class="card-footer">
                        <?= $approved_justifications ?> approved out of <?= $total_justifications ?> justifications
                    </div>
                </div>
            </div>
            
            <div class="chart-container">
                <canvas id="attendance-chart"></canvas>
            </div>
            
            <div class="chart-container">
                <canvas id="faculty-chart"></canvas>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Admin Student Management Page -->
        <?php if ($active_page === 'admin-students'): ?>
        <div id="admin-students" class="tab-content active">
            <a href="?page=admin-home" class="btn btn-back">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
            <h2 class="section-title"><i class="fas fa-user-graduate"></i> Student Management</h2>
            
            <div class="form-container">
                <div class="form-row">
                    <div class="form-group">
                        <label for="import-file">Import Student List</label>
                        <input type="file" id="import-file" accept=".xlsx, .xls, .csv">
                    </div>
                    <div class="form-group">
                        <label for="export-format">Export Format</label>
                        <select id="export-format">
                            <option value="excel">Excel (.xlsx)</option>
                            <option value="csv">CSV</option>
                            <option value="pdf">PDF</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <button id="import-students-btn" class="btn"><i class="fas fa-file-import"></i> Import from Excel</button>
                    <button id="export-students-btn" class="btn btn-success"><i class="fas fa-file-export"></i> Export Student List</button>
                    <button id="add-student-btn" class="btn"><i class="fas fa-plus"></i> Add New Student</button>
                </div>
            </div>
            
            <div class="table-container">
                <h3 style="padding: 15px 20px; border-bottom: 1px solid #dee2e6;">Student List</h3>
                <table id="admin-students-table">
                    <thead>
                        <tr>
                            <th>Student ID</th>
                            <th>Full Name</th>
                            <th>Email</th>
                            <th>Faculty</th>
                            <th>Department</th>
                            <th>Enrollment Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if (empty($_SESSION['students'])) {
                            echo '
                            <tr>
                                <td colspan="8" style="text-align: center; padding: 20px;">
                                    No students found. Use the "Add New Student" button to add students to the system.
                                </td>
                            </tr>
                            ';
                        } else {
                            foreach ($_SESSION['students'] as $student) {
                                $faculty_name = isset($faculties[$student['faculty']]) ? $faculties[$student['faculty']]['name'] : $student['faculty'];
                                $department_name = isset($faculties[$student['faculty']]['departments'][$student['department']]) 
                                    ? $faculties[$student['faculty']]['departments'][$student['department']]['name'] 
                                    : $student['department'];
                                
                                echo "
                                <tr>
                                    <td>{$student['id']}</td>
                                    <td>{$student['name']}</td>
                                    <td>{$student['email']}</td>
                                    <td>$faculty_name</td>
                                    <td>$department_name</td>
                                    <td>{$student['enrollment']}</td>
                                    <td class=\"status-present\">" . ucfirst($student['status']) . "</td>
                                    <td>
                                        <button class=\"btn edit-student\" data-id=\"{$student['id']}\">Edit</button>
                                        <button class=\"btn btn-danger remove-student\" data-id=\"{$student['id']}\">Remove</button>
                                    </td>
                                </tr>
                                ";
                            }
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>
        
        <?php endif; ?>
        
        <?php endif; ?>
    </main>

    <!-- Footer Section -->
    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>Algiers University</h3>
                    <p>Founded in 1909, Algiers University is one of the oldest and most prestigious universities in Algeria, committed to excellence in education and research.</p>
                </div>
                
                <div class="footer-section">
                    <h3>Quick Links</h3>
                    <ul>
                        <li><a href="#">University Website</a></li>
                        <li><a href="#">Student Portal</a></li>
                        <li><a href="#">Faculty Directory</a></li>
                        <li><a href="#">Academic Calendar</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h3>Contact Us</h3>
                    <ul>
                        <li><i class="fas fa-map-marker-alt"></i> 2 Rue Didouche Mourad, Algiers</li>
                        <li><i class="fas fa-phone"></i> +213 21 63 53 76</li>
                        <li><i class="fas fa-envelope"></i> info@univ-alger.dz</li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h3>Technical Support</h3>
                    <ul>
                        <li><a href="#">System Documentation</a></li>
                        <li><a href="#">User Guide</a></li>
                        <li><a href="#">Report an Issue</a></li>
                        <li><a href="#">FAQ</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="copyright">
                &copy; 2023 Algiers University - Attendance Management System. All rights reserved.
            </div>
        </div>
    </footer>

    <!-- Modals -->
    <!-- Add Student Modal -->
    <div id="add-student-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Add New Student</h3>
                <button class="close-modal">&times;</button>
            </div>
            <form id="add-student-form">
                <div class="form-row">
                    <div class="form-group">
                        <label for="new-student-id">Student ID</label>
                        <input type="text" id="new-student-id" required>
                    </div>
                    <div class="form-group">
                        <label for="new-student-fname">First Name</label>
                        <input type="text" id="new-student-fname" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="new-student-lname">Last Name</label>
                        <input type="text" id="new-student-lname" required>
                    </div>
                    <div class="form-group">
                        <label for="new-student-email">Email</label>
                        <input type="email" id="new-student-email" required>
                        <div class="email-validation" id="new-student-email-validation">Please use a valid university email (@univ-alger.dz)</div>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="new-student-faculty">Faculty</label>
                        <select id="new-student-faculty" required>
                            <option value="">-- Select Faculty --</option>
                            <?php foreach ($faculties as $key => $faculty): ?>
                            <option value="<?= $key ?>"><?= $faculty['name'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="new-student-department">Department</label>
                        <select id="new-student-department" required>
                            <option value="">-- Select Department --</option>
                            <!-- Departments will be populated based on faculty -->
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="new-student-level">Academic Level</label>
                        <select id="new-student-level" required>
                            <option value="">-- Select Level --</option>
                            <option value="L1">L1</option>
                            <option value="L2">L2</option>
                            <option value="L3">L3</option>
                            <option value="M1">M1</option>
                            <option value="M2">M2</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="new-student-enrollment">Enrollment Date</label>
                        <input type="date" id="new-student-enrollment" value="<?= date('Y-m-d') ?>" required>
                    </div>
                </div>
                
                <div style="text-align: right; margin-top: 20px;">
                    <button type="button" class="btn btn-danger close-modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Add Student</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Toast Notification -->
    <div id="toast" class="toast"></div>

    <script>
        // Application State (for client-side functionality)
        const faculties = <?= json_encode($faculties) ?>;
        
        // Initialize the application
        $(document).ready(function() {
            // Set current date as default for date inputs
            const today = new Date().toISOString().split('T')[0];
            $('#session-date').val(today);
            $('#absence-date').val(today);
            $('#new-student-enrollment').val(today);
            
            // Initialize departments for Faculty of Sciences
            populateDepartments('sciences');
            
            // Email validation for login
            $('#login-email').on('input', function() {
                validateEmail($(this).val(), '#login-email-validation');
            });
            
            // Email validation for signup
            $('#signup-email').on('input', function() {
                validateEmail($(this).val(), '#signup-email-validation');
            });
            
            // Email validation for new student
            $('#new-student-email').on('input', function() {
                validateEmail($(this).val(), '#new-student-email-validation');
            });
            
            // Faculty selection
            $('.faculty-card').on('click', function() {
                const faculty = $(this).data('faculty');
                $('.faculty-card').removeClass('selected');
                $(this).addClass('selected');
                $('#selected-faculty').val(faculty);
                
                // Show department selection
                $('#department-selection-container').show();
                populateDepartments(faculty);
            });
            
            // Department selection
            $(document).on('click', '.department-card', function() {
                $('.department-card').removeClass('selected');
                $(this).addClass('selected');
                $('#selected-department').val($(this).data('department'));
            });
            
            // Faculty change in admin student modal
            $('#new-student-faculty').on('change', function() {
                const faculty = $(this).val();
                populateDepartmentDropdown(faculty, '#new-student-department');
            });
            
            // Profile picture upload for signup
            $('#profile-picture').on('change', function(e) {
                handleProfilePictureUpload(e, '#profile-preview');
            });
            
            // Profile picture upload for account management
            $('#account-profile-picture').on('change', function(e) {
                handleProfilePictureUpload(e, '#account-profile-preview');
            });
            
            // Level selection for professors
            $(document).on('click', '.level-btn', function() {
                $('.level-btn').removeClass('selected');
                $(this).addClass('selected');
                const level = $(this).data('level');
                loadCoursesForLevel(level);
            });
            
            // Level selection for students
            $(document).on('click', '.student-level-btn', function() {
                $('.student-level-btn').removeClass('selected');
                $(this).addClass('selected');
                const level = $(this).data('level');
                loadStudentCoursesForLevel(level);
            });
            
            // Login/Signup buttons
            $('#login-btn').on('click', function() {
                window.location.href = '?page=login-page';
            });
            
            $('#signup-btn').on('click', function() {
                window.location.href = '?page=signup-page';
            });
            
            $('#show-signup').on('click', function(e) {
                e.preventDefault();
                window.location.href = '?page=signup-page';
            });
            
            // Back to login from signup
            $('#back-to-login-from-signup').on('click', function() {
                window.location.href = '?page=login-page';
            });
            
            // Login form
            $('#login-form').on('submit', function(e) {
                e.preventDefault();
                const email = $('#login-email').val();
                const password = $('#login-password').val();
                
                if (!validateEmail(email, '#login-email-validation')) {
                    showToast('Please use a valid university email (@univ-alger.dz)', 'error');
                    return;
                }
                
                $.post('', {
                    action: 'login',
                    email: email,
                    password: password
                }, function(response) {
                    const data = JSON.parse(response);
                    if (data.success) {
                        showToast(data.message, 'success');
                        setTimeout(() => {
                            window.location.href = '?page=' + data.role + '-home';
                        }, 1000);
                    } else {
                        showToast(data.message, 'error');
                    }
                });
            });
            
            // Signup form
            $('#signup-form').on('submit', function(e) {
                e.preventDefault();
                const fname = $('#signup-fname').val();
                const lname = $('#signup-lname').val();
                const email = $('#signup-email').val();
                const role = $('#signup-role').val();
                const faculty = $('#selected-faculty').val();
                const department = $('#selected-department').val();
                const password = $('#signup-password').val();
                const confirmPassword = $('#signup-confirm-password').val();
                
                if (!validateEmail(email, '#signup-email-validation')) {
                    showToast('Please use a valid university email (@univ-alger.dz)', 'error');
                    return;
                }
                
                if (!faculty) {
                    showToast('Please select a faculty', 'error');
                    return;
                }
                
                if (!department) {
                    showToast('Please select a department', 'error');
                    return;
                }
                
                if (password !== confirmPassword) {
                    showToast('Passwords do not match', 'error');
                    return;
                }
                
                $.post('', {
                    action: 'register',
                    fname: fname,
                    lname: lname,
                    email: email,
                    role: role,
                    faculty: faculty,
                    department: department,
                    password: password
                }, function(response) {
                    const data = JSON.parse(response);
                    if (data.success) {
                        showToast(data.message, 'success');
                        setTimeout(() => {
                            window.location.href = '?page=' + data.role + '-home';
                        }, 1000);
                    } else {
                        showToast(data.message, 'error');
                    }
                });
            });
            
            // User dropdown menu
            $('#user-avatar').on('click', function() {
                $('#user-dropdown').toggleClass('active');
            });
            
            // Close dropdown when clicking outside
            $(document).on('click', function(e) {
                if (!$(e.target).closest('.user-menu').length) {
                    $('#user-dropdown').removeClass('active');
                }
            });
            
            // Logout
            $('#logout-link').on('click', function(e) {
                e.preventDefault();
                $.post('', {
                    action: 'logout'
                }, function(response) {
                    const data = JSON.parse(response);
                    if (data.success) {
                        showToast(data.message, 'success');
                        setTimeout(() => {
                            window.location.href = '?page=login-page';
                        }, 1000);
                    }
                });
            });
            
            // Account tabs
            $('.account-tab').on('click', function() {
                const tabId = $(this).data('tab');
                $('.account-tab').removeClass('active');
                $(this).addClass('active');
                $('.account-content').removeClass('active');
                $(`#${tabId}-tab`).addClass('active');
            });
            
            // Save profile
            $('#save-profile-btn').on('click', function() {
                // In a real app, we would send this to the server
                showToast('Profile updated successfully', 'success');
            });
            
            // Change password
            $('#change-password-btn').on('click', function() {
                // In a real app, we would send this to the server
                showToast('Password changed successfully', 'success');
            });
            
            // Save preferences
            $('#save-preferences-btn').on('click', function() {
                // In a real app, we would send this to the server
                showToast('Preferences saved successfully', 'success');
            });
            
            // Professor functionality
            $('#load-students-btn').on('click', function() {
                const course = $('#course-select').val();
                if (course) {
                    // In this PHP version, the student list is already loaded from server
                    showToast('Student list loaded', 'success');
                } else {
                    showToast('Please select a course first', 'error');
                }
            });
            
            $('#save-attendance-btn').on('click', function() {
                const course = $('#course-select').val();
                const date = $('#session-date').val();
                const type = $('#session-type').val();
                
                if (!course) {
                    showToast('Please select a course', 'error');
                    return;
                }
                
                // Collect attendance data
                const attendanceData = [];
                $('#attendance-table tbody tr').each(function() {
                    const studentId = $(this).find('td:first').text();
                    const status = $(this).find('td:nth-child(3)').hasClass('status-present') ? 'present' : 'absent';
                    
                    attendanceData.push({
                        studentId: studentId,
                        status: status
                    });
                });
                
                $.post('', {
                    action: 'save_attendance',
                    course: course,
                    date: date,
                    type: type,
                    attendance: attendanceData
                }, function(response) {
                    const data = JSON.parse(response);
                    if (data.success) {
                        showToast(data.message, 'success');
                        setTimeout(() => {
                            window.location.href = '?page=professor-home';
                        }, 1000);
                    } else {
                        showToast(data.message, 'error');
                    }
                });
            });
            
            $('#export-attendance-btn').on('click', function() {
                // In a real app, this would generate and download an Excel file
                showToast('Attendance data exported successfully', 'success');
            });
            
            $('#generate-summary-btn').on('click', function() {
                const course = $('#summary-course').val();
                // In this PHP version, the summary is already generated from server
                showToast('Summary generated', 'success');
            });
            
            // Student functionality
            $('#load-student-attendance-btn').on('click', function() {
                const course = $('#student-course-select').val();
                if (course) {
                    // In this PHP version, the attendance is already loaded from server
                    showToast('Attendance records loaded', 'success');
                } else {
                    showToast('Please select a course first', 'error');
                }
            });
            
            $('#submit-justification-btn').on('click', function() {
                const date = $('#absence-date').val();
                const type = $('#justification-type').val();
                const details = $('#justification-details').val();
                
                if (!details) {
                    showToast('Please provide details for your absence', 'error');
                    return;
                }
                
                // In a real app, we would send this to the server
                showToast('Absence justification submitted successfully', 'success');
                $('#justification-details').val('');
            });
            
            // Admin functionality
            $('#import-students-btn').on('click', function() {
                // In a real app, this would process an uploaded Excel file
                showToast('Student list imported successfully', 'success');
            });
            
            $('#export-students-btn').on('click', function() {
                // In a real app, this would generate and download an Excel file
                showToast('Student list exported successfully', 'success');
            });
            
            $('#add-student-btn').on('click', function() {
                $('#add-student-modal').show();
            });
            
            $('#add-student-form').on('submit', function(e) {
                e.preventDefault();
                const id = $('#new-student-id').val();
                const fname = $('#new-student-fname').val();
                const lname = $('#new-student-lname').val();
                const email = $('#new-student-email').val();
                const faculty = $('#new-student-faculty').val();
                const department = $('#new-student-department').val();
                const level = $('#new-student-level').val();
                const enrollment = $('#new-student-enrollment').val();
                
                if (!validateEmail(email, '#new-student-email-validation')) {
                    showToast('Please use a valid university email (@univ-alger.dz)', 'error');
                    return;
                }
                
                $.post('', {
                    action: 'add_student',
                    student_id: id,
                    fname: fname,
                    lname: lname,
                    email: email,
                    faculty: faculty,
                    department: department,
                    level: level,
                    enrollment: enrollment
                }, function(response) {
                    const data = JSON.parse(response);
                    if (data.success) {
                        $('#add-student-modal').hide();
                        $('#add-student-form')[0].reset();
                        showToast(data.message, 'success');
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
                    } else {
                        showToast(data.message, 'error');
                    }
                });
            });
            
            $('#generate-stats-btn').on('click', function() {
                // In a real app, we would fetch data from the server
                showToast('Statistics generated', 'success');
            });
            
            // Modal close functionality
            $('.close-modal').on('click', function() {
                $(this).closest('.modal').hide();
            });
            
            // Toggle attendance status
            $(document).on('click', '.toggle-status', function() {
                const $row = $(this).closest('tr');
                const $statusCell = $row.find('td:nth-child(3)');
                
                if ($statusCell.hasClass('status-present')) {
                    $statusCell.removeClass('status-present').addClass('status-absent').text('Absent');
                    $(this).removeClass('btn-danger').addClass('btn-success').text('Mark Present');
                } else {
                    $statusCell.removeClass('status-absent').addClass('status-present').text('Present');
                    $(this).removeClass('btn-success').addClass('btn-danger').text('Mark Absent');
                }
            });
            
            // Justification functionality
            $(document).on('click', '.justify-absence', function() {
                const date = $(this).data('date');
                $('#absence-date').val(date);
                $('html, body').animate({
                    scrollTop: $('#justification-form').offset().top - 100
                }, 500);
            });
            
            // Edit/Remove student buttons
            $(document).on('click', '.edit-student', function() {
                const studentId = $(this).data('id');
                showToast(`Edit student ${studentId} - Feature coming soon`, 'warning');
            });
            
            $(document).on('click', '.remove-student', function() {
                const studentId = $(this).data('id');
                if (confirm(`Are you sure you want to remove student ${studentId}?`)) {
                    // In a real app, we would send a request to the server
                    showToast(`Student ${studentId} removed successfully`, 'success');
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                }
            });
            
            // Initialize charts
            initCharts();
        });
        
        // Email validation function
        function validateEmail(email, validationElementId) {
            const validationElement = $(validationElementId);
            const isValid = email.endsWith('@univ-alger.dz');
            
            if (email === '') {
                validationElement.hide();
                return false;
            }
            
            if (isValid) {
                validationElement.removeClass('invalid').addClass('valid').text('Valid university email').show();
                return true;
            } else {
                validationElement.removeClass('valid').addClass('invalid').text('Please use a valid university email (@univ-alger.dz)').show();
                return false;
            }
        }
        
        // Populate departments based on faculty selection
        function populateDepartments(faculty) {
            const departmentSelection = $('#department-selection');
            departmentSelection.empty();
            
            if (faculties[faculty]) {
                const departments = faculties[faculty].departments;
                
                for (const [key, dept] of Object.entries(departments)) {
                    const icon = getDepartmentIcon(key);
                    departmentSelection.append(`
                        <div class="department-card" data-department="${key}">
                            <div class="department-icon">
                                <i class="${icon}"></i>
                            </div>
                            <div class="department-name">${dept.name}</div>
                            <div class="department-desc">${getDepartmentDescription(key)}</div>
                        </div>
                    `);
                }
            }
        }
        
        // Populate department dropdown
        function populateDepartmentDropdown(faculty, selector) {
            const dropdown = $(selector);
            dropdown.empty();
            dropdown.append('<option value="">-- Select Department --</option>');
            
            if (faculties[faculty]) {
                const departments = faculties[faculty].departments;
                
                for (const [key, dept] of Object.entries(departments)) {
                    dropdown.append(`<option value="${key}">${dept.name}</option>`);
                }
            }
        }
        
        // Get department icon
        function getDepartmentIcon(department) {
            const icons = {
                'computer_science': 'fas fa-laptop-code',
                'mathematics': 'fas fa-calculator',
                'natural_life_sciences': 'fas fa-leaf',
                'material_sciences': 'fas fa-atom',
                'architecture': 'fas fa-ruler-combined',
                'administration': 'fas fa-user-shield'
            };
            return icons[department] || 'fas fa-building';
        }
        
        // Get department description
        function getDepartmentDescription(department) {
            const descriptions = {
                'computer_science': 'Programming, Algorithms, Software Engineering',
                'mathematics': 'Algebra, Calculus, Statistics, Applied Mathematics',
                'natural_life_sciences': 'Biology, Chemistry, Environmental Sciences',
                'material_sciences': 'Physics, Chemistry, Material Sciences',
                'architecture': 'Architectural Design, Urban Planning, Construction',
                'administration': 'System Administration and Management'
            };
            return descriptions[department] || 'Academic Department';
        }
        
        // Load courses for selected level
        function loadCoursesForLevel(level) {
            // In this PHP version, we'll reload the page with the selected level
            window.location.href = '?page=professor-home&level=' + level;
        }
        
        // Load student courses for selected level
        function loadStudentCoursesForLevel(level) {
            // In this PHP version, we'll reload the page with the selected level
            window.location.href = '?page=student-home&level=' + level;
        }
        
        // Profile picture handling
        function handleProfilePictureUpload(event, previewSelector) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    // Create image element for preview
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.className = 'profile-picture';
                    
                    // Replace placeholder with actual image
                    const preview = $(previewSelector);
                    preview.empty();
                    preview.append(img);
                };
                reader.readAsDataURL(file);
            }
        }
        
        function initCharts() {
            // Initialize charts with empty data
            if ($('#attendance-chart').length) {
                window.attendanceChart = new Chart($('#attendance-chart'), {
                    type: 'line',
                    data: {
                        labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4'],
                        datasets: [{
                            label: 'Attendance Rate',
                            data: [85, 82, 88, 90],
                            borderColor: '#1a4d8c',
                            backgroundColor: 'rgba(26, 77, 140, 0.1)',
                            tension: 0.3,
                            fill: true
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: false,
                                min: 0,
                                max: 100
                            }
                        }
                    }
                });
            }
            
            if ($('#faculty-chart').length) {
                window.facultyChart = new Chart($('#faculty-chart'), {
                    type: 'bar',
                    data: {
                        labels: ['Computer Science', 'Mathematics', 'Natural & Life Sciences', 'Material Sciences', 'Architecture'],
                        datasets: [{
                            label: 'Average Attendance Rate',
                            data: [87, 82, 85, 79, 88],
                            backgroundColor: [
                                'rgba(26, 77, 140, 0.7)',
                                'rgba(42, 157, 143, 0.7)',
                                'rgba(230, 57, 70, 0.7)',
                                'rgba(255, 193, 7, 0.7)',
                                'rgba(108, 117, 125, 0.7)'
                            ]
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                max: 100
                            }
                        }
                    }
                });
            }
        }
        
        // Utility functions
        function showToast(message, type = 'success') {
            const toast = $('#toast');
            toast.removeClass('error warning').addClass(type);
            toast.text(message).fadeIn();
            
            setTimeout(() => {
                toast.fadeOut();
            }, 3000);
        }
    </script>
</body>
</html>