<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Authentication Test - Fabricon.shop</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
            min-height: 100vh;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #F28B50, #e07a3f);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .content { padding: 30px; }
        .test-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .test-section h3 {
            color: #333;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #F28B50;
        }
        .status {
            padding: 15px;
            border-radius: 5px;
            margin: 10px 0;
        }
        .success {
            background: #d4edda;
            border: 1px solid #28a745;
            color: #155724;
        }
        .error {
            background: #f8d7da;
            border: 1px solid #dc3545;
            color: #721c24;
        }
        .info {
            background: #d1ecf1;
            border: 1px solid #17a2b8;
            color: #0c5460;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #F28B50;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 5px;
            border: none;
            cursor: pointer;
            font-size: 14px;
        }
        .btn:hover {
            background: #e07a3f;
        }
        pre {
            background: #2d2d2d;
            color: #f8f8f2;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
            font-size: 13px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }
        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background: #F28B50;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔐 Authentication System Test</h1>
            <p>Fabricon.shop - Testing Login & Registration</p>
        </div>
        
        <div class="content">
            <?php
            // Test 1: Check if auth.php exists
            echo '<div class="test-section">';
            echo '<h3>Test 1: Authentication File Check</h3>';
            if (file_exists('php/auth.php')) {
                echo '<div class="status success">✅ auth.php file exists</div>';
            } else {
                echo '<div class="status error">❌ auth.php file not found</div>';
            }
            echo '</div>';

            // Test 2: Check database connection
            echo '<div class="test-section">';
            echo '<h3>Test 2: Database Connection</h3>';
            try {
                define('FABRICON_APP', true);
                require_once 'php/config.php';
                $db = Database::getInstance()->getConnection();
                echo '<div class="status success">✅ Database connection successful</div>';
                
                // Check if users table exists
                $stmt = $db->query("SHOW TABLES LIKE 'users'");
                if ($stmt->rowCount() > 0) {
                    echo '<div class="status success">✅ Users table exists</div>';
                    
                    // Count users
                    $stmt = $db->query("SELECT COUNT(*) as count FROM users");
                    $count = $stmt->fetch()['count'];
                    echo '<div class="status info">ℹ️ Total users in database: ' . $count . '</div>';
                } else {
                    echo '<div class="status error">❌ Users table not found</div>';
                }
            } catch (Exception $e) {
                echo '<div class="status error">❌ Database error: ' . htmlspecialchars($e->getMessage()) . '</div>';
            }
            echo '</div>';

            // Test 3: Session check
            echo '<div class="test-section">';
            echo '<h3>Test 3: Session Status</h3>';
            session_start();
            if (isset($_SESSION['logged_in']) && $_SESSION['logged_in']) {
                echo '<div class="status success">✅ User is logged in</div>';
                echo '<div class="status info">';
                echo '<strong>User Details:</strong><br>';
                echo 'User ID: ' . $_SESSION['user_id'] . '<br>';
                echo 'Email: ' . $_SESSION['email'] . '<br>';
                echo 'Name: ' . $_SESSION['first_name'] . ' ' . $_SESSION['last_name'] . '<br>';
                echo 'Login Time: ' . date('Y-m-d H:i:s', $_SESSION['login_time']);
                echo '</div>';
                echo '<a href="php/auth.php?action=logout" class="btn">Logout</a>';
            } else {
                echo '<div class="status info">ℹ️ No active session</div>';
                echo '<a href="login.html" class="btn">Go to Login Page</a>';
            }
            echo '</div>';

            // Test 4: Recent users
            if (isset($db)) {
                echo '<div class="test-section">';
                echo '<h3>Test 4: Recent Users</h3>';
                try {
                    $stmt = $db->query("
                        SELECT user_id, email, first_name, last_name, created_at, last_login 
                        FROM users 
                        ORDER BY created_at DESC 
                        LIMIT 5
                    ");
                    $users = $stmt->fetchAll();
                    
                    if (count($users) > 0) {
                        echo '<table>';
                        echo '<thead><tr><th>ID</th><th>Email</th><th>Name</th><th>Created</th><th>Last Login</th></tr></thead>';
                        echo '<tbody>';
                        foreach ($users as $user) {
                            echo '<tr>';
                            echo '<td>' . $user['user_id'] . '</td>';
                            echo '<td>' . htmlspecialchars($user['email']) . '</td>';
                            echo '<td>' . htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) . '</td>';
                            echo '<td>' . $user['created_at'] . '</td>';
                            echo '<td>' . ($user['last_login'] ?: 'Never') . '</td>';
                            echo '</tr>';
                        }
                        echo '</tbody></table>';
                    } else {
                        echo '<div class="status info">ℹ️ No users found. Create an account to test!</div>';
                    }
                } catch (Exception $e) {
                    echo '<div class="status error">❌ Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
                }
                echo '</div>';

                // Test 5: Recent activity
                echo '<div class="test-section">';
                echo '<h3>Test 5: Recent Activity</h3>';
                try {
                    $stmt = $db->query("
                        SELECT a.*, u.email 
                        FROM activity_logs a
                        LEFT JOIN users u ON a.user_id = u.user_id
                        ORDER BY a.created_at DESC 
                        LIMIT 10
                    ");
                    $activities = $stmt->fetchAll();
                    
                    if (count($activities) > 0) {
                        echo '<table>';
                        echo '<thead><tr><th>Action</th><th>User</th><th>Time</th><th>IP</th></tr></thead>';
                        echo '<tbody>';
                        foreach ($activities as $activity) {
                            echo '<tr>';
                            echo '<td><strong>' . htmlspecialchars($activity['action']) . '</strong></td>';
                            echo '<td>' . htmlspecialchars($activity['email'] ?: 'N/A') . '</td>';
                            echo '<td>' . $activity['created_at'] . '</td>';
                            echo '<td>' . htmlspecialchars($activity['ip_address']) . '</td>';
                            echo '</tr>';
                        }
                        echo '</tbody></table>';
                    } else {
                        echo '<div class="status info">ℹ️ No activity logged yet</div>';
                    }
                } catch (Exception $e) {
                    echo '<div class="status error">❌ Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
                }
                echo '</div>';
            }

            // Test 6: API Endpoints
            echo '<div class="test-section">';
            echo '<h3>Test 6: API Endpoints</h3>';
            echo '<div class="status info">';
            echo '<strong>Available Endpoints:</strong><br><br>';
            echo '<code>POST php/auth.php?action=register</code> - User registration<br>';
            echo '<code>POST php/auth.php?action=login</code> - User login<br>';
            echo '<code>POST php/auth.php?action=google-login</code> - Google OAuth<br>';
            echo '<code>GET php/auth.php?action=logout</code> - User logout<br>';
            echo '<code>GET php/auth.php?action=check-session</code> - Check session<br>';
            echo '</div>';
            echo '</div>';

            // Test 7: Quick Actions
            echo '<div class="test-section">';
            echo '<h3>Quick Actions</h3>';
            echo '<a href="login.html" class="btn">Login Page</a>';
            echo '<a href="index.html" class="btn">Homepage</a>';
            echo '<a href="test_database.php" class="btn">Database Test</a>';
            echo '<a href="http://localhost/phpmyadmin" class="btn" target="_blank">phpMyAdmin</a>';
            echo '</div>';
            ?>
        </div>
    </div>
</body>
</html>
