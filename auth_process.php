<?php
error_reporting(0);
ini_set('display_errors', 0);
header('Content-Type: application/json');

ob_start();

function sendResponse($data)
{
    if (ob_get_length())
        ob_clean();
    echo json_encode($data);
    exit;
}

try {
    session_start();

    if (!file_exists('db.php'))
        throw new Exception("db.php not found");
    require_once 'db.php';

    if (!file_exists('config.php'))
        throw new Exception("config.php not found");
    require_once 'config.php';

    if (!file_exists('vendor/autoload.php'))
        throw new Exception("Vendor autoload not found. Run 'composer install'.");
    require 'vendor/autoload.php';

    $action = $_POST['action'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if ($action === 'register') {
        $username = $_POST['username'] ?? '';
        if (empty($username) || empty($email) || empty($password)) {
            sendResponse(['success' => false, 'error' => 'All fields are required']);
        }
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
        $stmt->execute([$email, $username]);
        if ($stmt->fetch()) {
            sendResponse(['success' => false, 'error' => 'Username or Email already exists']);
        }
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        $stmt->execute([$username, $email, $hashedPassword]);
        $userId = $pdo->lastInsertId();
        $_SESSION['user_id'] = $userId;
        $_SESSION['username'] = $username;
        sendResponse(['success' => true, 'message' => 'Registration successful!']);

    } elseif ($action === 'login') {
        if (empty($email) || empty($password)) {
            sendResponse(['success' => false, 'error' => 'Email and Password are required']);
        }
        $stmt = $pdo->prepare("SELECT id, username, password FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            sendResponse(['success' => true, 'message' => 'Login successful!']);
        } else {
            sendResponse(['success' => false, 'error' => 'Invalid email or password']);
        }

    } elseif ($action === 'forgot_password') {
        if (empty($email)) {
            sendResponse(['success' => false, 'error' => 'Email is required']);
        }
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        if ($user) {
            $code = rand(100000, 999999);
            $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
            $stmt = $pdo->prepare("UPDATE users SET reset_code = ?, reset_expires_at = ? WHERE id = ?");
            $stmt->execute([$code, $expiry, $user['id']]);

            $mail = new PHPMailer\PHPMailer\PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = SMTP_HOST;
                $mail->SMTPAuth = true;
                $mail->Username = SMTP_USER;
                $mail->Password = SMTP_PASS;
                $mail->SMTPSecure = (SMTP_PORT == 465) ? 'ssl' : 'tls';
                $mail->Port = SMTP_PORT;
                $mail->setFrom(SMTP_FROM, SMTP_FROM_NAME);
                $mail->addAddress($email);
                $mail->isHTML(true);
                $mail->Subject = "Password Reset Code - Naija Cgpa";
                $mail->Body = "Your code: <b>$code</b>. It expires in 1 hour.";
                $mail->AltBody = "Your code is: $code";
                $mail->send();
                sendResponse(['success' => true, 'message' => 'Verification code sent to your email!']);
            } catch (Exception $e) {
                sendResponse(['success' => false, 'error' => "Mailer Error: {$mail->ErrorInfo}"]);
            }
        } else {
            sendResponse(['success' => false, 'error' => 'No account found with that email']);
        }

    } elseif ($action === 'verify_code') {
        $code = $_POST['code'] ?? '';
        if (empty($email) || empty($code)) {
            sendResponse(['success' => false, 'error' => 'Email and Code are required']);
        }
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND reset_code = ? AND reset_expires_at > NOW()");
        $stmt->execute([$email, $code]);
        if ($stmt->fetch()) {
            sendResponse(['success' => true, 'message' => 'Code verified!']);
        } else {
            sendResponse(['success' => false, 'error' => 'Invalid or expired code']);
        }

    } elseif ($action === 'reset_password') {
        if (empty($email) || empty($password)) {
            sendResponse(['success' => false, 'error' => 'Email and New Password are required']);
        }
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = ?, reset_code = NULL, reset_expires_at = NULL WHERE email = ?");
        $stmt->execute([$hashedPassword, $email]);
        sendResponse(['success' => true, 'message' => 'Password reset successful!']);

    } else {
        sendResponse(['success' => false, 'error' => 'Invalid action: ' . $action]);
    }

} catch (Throwable $e) {
    sendResponse(['success' => false, 'error' => 'An unexpected server error occurred. Please try again later.']);
}
?>