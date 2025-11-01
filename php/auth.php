<?php
/**
 * Fabricon.shop Authentication Handler
 * Handles user login, registration, and session management
 */

session_start();
define('FABRICON_APP', true);
require_once 'config.php';

header('Content-Type: application/json');

// Get request method and action
$method = $_SERVER['REQUEST_METHOD'];
$action = isset($_GET['action']) ? $_GET['action'] : '';

// Handle different actions
switch ($action) {
    case 'login':
        handleLogin();
        break;
    case 'register':
        handleRegister();
        break;
    case 'logout':
        handleLogout();
        break;
    case 'check-session':
        checkSession();
        break;
    case 'google-login':
        handleGoogleLogin();
        break;
    default:
        jsonResponse(['error' => 'Invalid action'], 400);
}

/**
 * Handle user login
 */
function handleLogin() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        jsonResponse(['error' => 'Method not allowed'], 405);
    }

    $input = json_decode(file_get_contents('php://input'), true);
    $email = isset($input['email']) ? sanitizeInput($input['email']) : '';
    $password = isset($input['password']) ? $input['password'] : '';
    $remember = isset($input['remember']) ? $input['remember'] : false;

    // Validate input
    if (empty($email) || empty($password)) {
        jsonResponse(['error' => 'Email and password are required'], 400);
    }

    if (!validateEmail($email)) {
        jsonResponse(['error' => 'Invalid email format'], 400);
    }

    try {
        $db = Database::getInstance()->getConnection();
        
        // Get user by email
        $stmt = $db->prepare("SELECT * FROM users WHERE email = ? AND status = 'active'");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user) {
            // Log failed attempt
            logActivity(null, 'login_failed', ['email' => $email, 'reason' => 'user_not_found']);
            jsonResponse(['error' => 'Invalid email or password'], 401);
        }

        // Verify password
        if (!verifyPassword($password, $user['password_hash'])) {
            // Log failed attempt
            logActivity($user['user_id'], 'login_failed', ['reason' => 'invalid_password']);
            jsonResponse(['error' => 'Invalid email or password'], 401);
        }

        // Check if email is verified
        if (!$user['email_verified']) {
            jsonResponse(['error' => 'Please verify your email address first'], 403);
        }

        // Create session
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['first_name'] = $user['first_name'];
        $_SESSION['last_name'] = $user['last_name'];
        $_SESSION['logged_in'] = true;
        $_SESSION['login_time'] = time();

        // Update last login
        $stmt = $db->prepare("UPDATE users SET last_login = NOW() WHERE user_id = ?");
        $stmt->execute([$user['user_id']]);

        // Log successful login
        logActivity($user['user_id'], 'login_success', ['method' => 'email']);

        // Set remember me cookie if requested
        if ($remember) {
            $token = generateToken();
            setcookie('remember_token', $token, time() + (86400 * 30), '/'); // 30 days
            
            // Store token in database (you'd need a remember_tokens table)
            // For now, we'll skip this
        }

        jsonResponse([
            'success' => true,
            'message' => 'Login successful',
            'user' => [
                'id' => $user['user_id'],
                'email' => $user['email'],
                'first_name' => $user['first_name'],
                'last_name' => $user['last_name']
            ]
        ]);

    } catch (Exception $e) {
        error_log("Login error: " . $e->getMessage());
        jsonResponse(['error' => 'An error occurred during login'], 500);
    }
}

/**
 * Handle user registration
 */
function handleRegister() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        jsonResponse(['error' => 'Method not allowed'], 405);
    }

    $input = json_decode(file_get_contents('php://input'), true);
    $firstName = isset($input['first_name']) ? sanitizeInput($input['first_name']) : '';
    $lastName = isset($input['last_name']) ? sanitizeInput($input['last_name']) : '';
    $email = isset($input['email']) ? sanitizeInput($input['email']) : '';
    $password = isset($input['password']) ? $input['password'] : '';
    $confirmPassword = isset($input['confirm_password']) ? $input['confirm_password'] : '';

    // Validate input
    if (empty($firstName) || empty($lastName) || empty($email) || empty($password)) {
        jsonResponse(['error' => 'All fields are required'], 400);
    }

    if (!validateEmail($email)) {
        jsonResponse(['error' => 'Invalid email format'], 400);
    }

    if (strlen($password) < PASSWORD_MIN_LENGTH) {
        jsonResponse(['error' => 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters'], 400);
    }

    if ($password !== $confirmPassword) {
        jsonResponse(['error' => 'Passwords do not match'], 400);
    }

    try {
        $db = Database::getInstance()->getConnection();
        
        // Check if email already exists
        $stmt = $db->prepare("SELECT user_id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            jsonResponse(['error' => 'Email already registered'], 409);
        }

        // Hash password
        $passwordHash = hashPassword($password);
        
        // Generate verification token
        $verificationToken = generateToken();

        // Insert user
        $stmt = $db->prepare("
            INSERT INTO users 
            (email, password_hash, first_name, last_name, verification_token, email_verified, status, created_at)
            VALUES (?, ?, ?, ?, ?, ?, 'active', NOW())
        ");
        
        // For demo purposes, we'll set email_verified to true
        // In production, send verification email and set to false
        $stmt->execute([
            $email,
            $passwordHash,
            $firstName,
            $lastName,
            $verificationToken,
            1 // Set to true for demo, should be 0 in production
        ]);

        $userId = $db->lastInsertId();

        // Log registration
        logActivity($userId, 'user_registered', ['method' => 'email']);

        // Auto-login after registration
        $_SESSION['user_id'] = $userId;
        $_SESSION['email'] = $email;
        $_SESSION['first_name'] = $firstName;
        $_SESSION['last_name'] = $lastName;
        $_SESSION['logged_in'] = true;
        $_SESSION['login_time'] = time();

        // In production, send verification email here
        // sendVerificationEmail($email, $verificationToken);

        jsonResponse([
            'success' => true,
            'message' => 'Registration successful',
            'user' => [
                'id' => $userId,
                'email' => $email,
                'first_name' => $firstName,
                'last_name' => $lastName
            ]
        ]);

    } catch (Exception $e) {
        error_log("Registration error: " . $e->getMessage());
        jsonResponse(['error' => 'An error occurred during registration'], 500);
    }
}

/**
 * Handle Google OAuth login
 */
function handleGoogleLogin() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        jsonResponse(['error' => 'Method not allowed'], 405);
    }

    $input = json_decode(file_get_contents('php://input'), true);
    $googleToken = isset($input['token']) ? $input['token'] : '';
    $googleId = isset($input['google_id']) ? $input['google_id'] : '';
    $email = isset($input['email']) ? sanitizeInput($input['email']) : '';
    $firstName = isset($input['first_name']) ? sanitizeInput($input['first_name']) : '';
    $lastName = isset($input['last_name']) ? sanitizeInput($input['last_name']) : '';
    $picture = isset($input['picture']) ? $input['picture'] : '';

    if (empty($googleId) || empty($email)) {
        jsonResponse(['error' => 'Invalid Google authentication data'], 400);
    }

    try {
        $db = Database::getInstance()->getConnection();
        
        // Check if user exists with this email
        $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            // User exists, log them in
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['first_name'] = $user['first_name'];
            $_SESSION['last_name'] = $user['last_name'];
            $_SESSION['logged_in'] = true;
            $_SESSION['login_time'] = time();
            $_SESSION['google_auth'] = true;

            // Update last login
            $stmt = $db->prepare("UPDATE users SET last_login = NOW() WHERE user_id = ?");
            $stmt->execute([$user['user_id']]);

            logActivity($user['user_id'], 'login_success', ['method' => 'google']);

            jsonResponse([
                'success' => true,
                'message' => 'Login successful',
                'user' => [
                    'id' => $user['user_id'],
                    'email' => $user['email'],
                    'first_name' => $user['first_name'],
                    'last_name' => $user['last_name']
                ]
            ]);
        } else {
            // Create new user
            $stmt = $db->prepare("
                INSERT INTO users 
                (email, password_hash, first_name, last_name, email_verified, status, created_at)
                VALUES (?, ?, ?, ?, 1, 'active', NOW())
            ");
            
            // Use a random password hash for Google users (they won't use it)
            $randomPassword = hashPassword(generateToken());
            
            $stmt->execute([
                $email,
                $randomPassword,
                $firstName,
                $lastName
            ]);

            $userId = $db->lastInsertId();

            // Auto-login
            $_SESSION['user_id'] = $userId;
            $_SESSION['email'] = $email;
            $_SESSION['first_name'] = $firstName;
            $_SESSION['last_name'] = $lastName;
            $_SESSION['logged_in'] = true;
            $_SESSION['login_time'] = time();
            $_SESSION['google_auth'] = true;

            logActivity($userId, 'user_registered', ['method' => 'google']);

            jsonResponse([
                'success' => true,
                'message' => 'Account created and logged in',
                'user' => [
                    'id' => $userId,
                    'email' => $email,
                    'first_name' => $firstName,
                    'last_name' => $lastName
                ]
            ]);
        }

    } catch (Exception $e) {
        error_log("Google login error: " . $e->getMessage());
        jsonResponse(['error' => 'An error occurred during Google login'], 500);
    }
}

/**
 * Handle logout
 */
function handleLogout() {
    if (isset($_SESSION['user_id'])) {
        logActivity($_SESSION['user_id'], 'logout');
    }

    // Clear session
    session_unset();
    session_destroy();

    // Clear remember me cookie
    if (isset($_COOKIE['remember_token'])) {
        setcookie('remember_token', '', time() - 3600, '/');
    }

    jsonResponse([
        'success' => true,
        'message' => 'Logged out successfully'
    ]);
}

/**
 * Check if user is logged in
 */
function checkSession() {
    if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
        jsonResponse([
            'logged_in' => true,
            'user' => [
                'id' => $_SESSION['user_id'],
                'email' => $_SESSION['email'],
                'first_name' => $_SESSION['first_name'],
                'last_name' => $_SESSION['last_name']
            ]
        ]);
    } else {
        jsonResponse(['logged_in' => false]);
    }
}
?>
