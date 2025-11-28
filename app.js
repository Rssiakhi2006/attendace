// app.js

const state = {
    currentUser: null,
    currentRole: null
};

$(document).ready(function() {
    // Set current date as default for date inputs
    const today = new Date().toISOString().split('T')[0];
    $('#session-date').val(today);
    $('#absence-date').val(today);
    $('#new-student-enrollment').val(today);

    // ... rest of the initialization ...

    // Login form
    $('#login-form').on('submit', function(e) {
        e.preventDefault();
        const email = $('#login-email').val();
        const password = $('#login-password').val();

        if (!validateEmail(email, '#login-email-validation')) {
            showToast('Please use a valid university email (@univ-alger.dz)', 'error');
            return;
        }

        loginUser(email, password);
    });

    // ... other event handlers ...
});

// Email validation function
function validateEmail(email, validationElementId) {
    // ... same as before ...
}

// Login user via API
function loginUser(email, password) {
    $.ajax({
        url: 'api/auth.php',
        method: 'POST',
        data: JSON.stringify({
            action: 'login',
            email: email,
            password: password
        }),
        contentType: 'application/json',
        success: function(response) {
            if (response.success) {
                state.currentUser = response.user;
                loginAsRole(response.user.role);
                showToast(`Welcome back, ${response.user.first_name}!`, 'success');
            } else {
                showToast(response.message, 'error');
            }
        },
        error: function() {
            showToast('Network error. Please try again.', 'error');
        }
    });
}

// Register user via API
function registerUser(fname, lname, email, role, faculty, department, password) {
    $.ajax({
        url: 'api/auth.php',
        method: 'POST',
        data: JSON.stringify({
            action: 'register',
            email: email,
            password: password,
            role: role,
            first_name: fname,
            last_name: lname,
            faculty: faculty,
            department: department,
            level: role === 'student' ? 'L1' : null
        }),
        contentType: 'application/json',
        success: function(response) {
            if (response.success) {
                // Now log the user in
                loginUser(email, password);
            } else {
                showToast(response.message, 'error');
            }
        },
        error: function() {
            showToast('Network error. Please try again.', 'error');
        }
    });
}

// ... other functions (loadCoursesForLevel, loadStudentList, etc.) as provided above ...

// Utility function to show toast messages
function showToast(message, type = 'success') {
    // ... same as before ...
}