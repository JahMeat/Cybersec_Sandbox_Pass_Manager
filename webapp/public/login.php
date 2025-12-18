<?php

// Harden session cookie with Secure, HttpOnly, and SameSite flags
$cookieParams = session_get_cookie_params();
session_set_cookie_params([
    'lifetime' => $cookieParams['lifetime'],
    'path'     => $cookieParams['path'],
    'domain'   => $cookieParams['domain'],
    'secure'   => true,
    'httponly' => true,
    'samesite' => 'Lax',
]);

session_start();

include './components/loggly-logger.php';

$hostname = 'backend-mysql-database';
$username = 'user';
$password = 'supersecretpw';
$database = 'password_manager';

$conn = new mysqli($hostname, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

unset($error_message);

if ($conn->connect_error) {
    $errorMessage = "Connection failed: " . $conn->connect_error;    
    die($errorMessage);
}

// Brute force guard functions
include './components/bruteforce-guard.php';


// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Check if account is locked
    $lockRemaining = bf_is_locked($username);
    if ($lockRemaining > 0) {
        $error_message = "Too many failed attempts. Account locked for $lockRemaining seconds.";
        $logger->warning("Login attempt while locked for username: $username");
    } else {
        // Proceed with login attempt
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? AND password = ? AND approved = 1");
        $stmt->bind_param("ss", $username, $password);
        $stmt->execute();
        $result = $stmt->get_result();
        // $sql = "SELECT * FROM users WHERE username = '$username' AND password = '$password' AND approved = 1";
        // $result = $conn->query($sql);

        if($result->num_rows > 0) {
            
            $logger->info("Login successful for username: $username");
            $userFromDB = $result->fetch_assoc();

            // Clear brute force tracking on success
            bf_record_success($username);

            //$_SESSION['authenticated'] = $username;
            $_SESSION['authenticated'] = $username;  

            if ($userFromDB['default_role_id'] == 1)
            {        
                $_SESSION['isSiteAdministrator'] = 1;                
            }else{
                unset($_SESSION['isSiteAdministrator']); 
            }
            header("Location: index.php");
            exit();
        } else {
            // Record failed attempt
            bf_record_fail($username);
            $error_message = 'Invalid username or password.';
            $logger->warning("Login failed for username: $username");  
        }
    }

    $conn->close();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <title>Login Page</title>
</head>
<body>
    <div class="container mt-5">
        <div class="col-md-6 offset-md-3">
            <h2 class="text-center">Login</h2>
            <?php if (isset($error_message)) : ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>
            <form action="login.php" method="post">
                <div class="form-group">
                    <label for="username">Username:</label>
                    <input type="text" class="form-control" id="username" name="username" required>
                </div>
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Login</button>
            </form>
            <div class="mt-3 text-center">
                <a href="./users/request_account.php" class="btn btn-secondary btn-block">Request an Account</a>
            </div>
        </div>
    </div>
</body>
</html>
