<?php
session_start();
define('ABSPATH', __DIR__);
require_once 'config/toastr.php';
require_once 'config/db.php';

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = 'Invalid request';
    } else {
        $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_SPECIAL_CHARS);
        $password = $_POST['password'];

        if (!$username || !$password) {
            $error = 'Username and password are required';
        } else {
            try {
                $stmt = $conn->prepare("
                    SELECT u.id, u.password, r.role as role_name, r.id as role_id
                    FROM users u
                    JOIN roles r ON u.role_id = r.id
                    WHERE u.username = ?
                    LIMIT 1
                ");

                if (!$stmt || !$stmt->bind_param("s", $username)) {
                    throw new RuntimeException('Database preparation failed');
                }

                $stmt->execute();
                $result = $stmt->get_result();
                if (!$result) {
                    throw new RuntimeException('Database query failed');
                }

                $user = $result->fetch_assoc();
                $stmt->close();

                if (!$user || !password_verify($password, $user['password'])) {
                    $error = 'Invalid username or password';
                } else {
                    session_regenerate_id(true);
                    $_SESSION['loggedin']  = true;
                    $_SESSION['username']  = $username;
                    $_SESSION['user_id']   = $user['id'];
                    $_SESSION['role_id']   = $user['role_id'];
                    $_SESSION['role_name'] = $user['role_name'];

                    $_SESSION['toastr'] = [
                        'type' => 'success',
                        'message' => 'Welcome back, ' . $username . '!'
                    ];

                    switch ($user['role_name']) {
                        case 'admin':
                            header("Location: pages/admin/dashboard.php");
                            break;
                        case 'moderator':
                            header("Location: pages/moderator/dashboard.php");
                            break;
                        case 'user':
                        default:
                            header("Location: index.php");
                    }
                    exit;
                }
            } catch (Exception $e) {
                error_log("Login error: " . $e->getMessage());
                $error = 'Internal server error';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- SEO/meta kept simple to avoid affecting logic -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Ripper Tech &amp; Solutions</title>
    <meta name="description" content="Login to Ripper Tech &amp; Solutions to access your account.">
    <meta name="robots" content="noindex, nofollow">
    <link rel="icon" type="image/png" href="assets/images/logo-2.png">

    <!-- (Optional) your global CSS can remain -->
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="assets/css/css2.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/toastr.min.css">
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
          crossorigin="anonymous">

    <!-- 🔒 Purely visual CSS below; no JS, no name/id changes -->
    <style>
      :root { --indigo:#667eea; --purple:#764ba2; --text:#2c3e50; --muted:#6c757d; }
      html, body { height: 100%; }
      body {
        margin: 0;
        font-family: -apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Arial,sans-serif;
        background: linear-gradient(135deg, var(--indigo), var(--purple));
        display: flex; align-items: center; justify-content: center;
        padding: 24px;
      }
      .login-container { width: 100%; max-width: 420px; }
      .login-form {
        background: #fff; border-radius: 14px; padding: 2rem;
        box-shadow: 0 10px 25px rgba(0,0,0,.15);
      }
      .login-form h2 {
        margin: 0 0 1.25rem; text-align: center; font-weight: 800; color: var(--text);
      }
      .login-form label {
        display: block; margin: .85rem 0 .35rem; font-weight: 600; color: var(--text);
      }
      .login-form input {
        width: 100%; padding: .8rem .9rem; border-radius: 10px;
        border: 1px solid #cfd6e4; font-size: 1rem;
        transition: border-color .2s, box-shadow .2s;
      }
      .login-form input:focus {
        border-color: var(--indigo);
        box-shadow: 0 0 0 3px rgba(102,126,234,.25); outline: none;
      }
      .btn.btn-primary, .btn-primary {
        margin-top: .5rem; width: 100%; border: 0; cursor: pointer;
        background: linear-gradient(135deg, var(--indigo), var(--purple));
        color: #fff; font-weight: 700; padding: .85rem 1rem;
        border-radius: 10px; transition: transform .15s, box-shadow .15s;
      }
      .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 18px rgba(102,126,234,.40);
      }
      .form-text {
        font-size: .9rem; color: var(--muted); line-height: 1.45; margin: .75rem 0 1rem;
      }
      .goto-register {
        color: var(--indigo); text-decoration: none; font-weight: 600; margin-left: .25rem;
      }
      .goto-register:hover { text-decoration: underline; }
      .error-msg {
        color: #c62828; background: #fff5f5; border: 1px solid #f6d9d9;
        border-radius: 10px; padding: .65rem .85rem; margin: 0 0 1rem;
        display: flex; gap: .5rem; align-items: center; font-size: .95rem;
      }
    </style>
</head>
<body>
  <div class="login-container">
    <div class="login-form">
      <h2><i class="fas fa-right-to-bracket" aria-hidden="true"></i> Login</h2>

      <?php if (!empty($error)): ?>
        <p class="error-msg"><i class="fas fa-circle-exclamation" aria-hidden="true"></i><?= htmlspecialchars($error) ?></p>
      <?php endif; ?>

      <form method="POST" action="" novalidate>
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

        <label for="username">Username</label>
        <input id="username" type="text" name="username" placeholder="admin" required
               value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
               autocomplete="username">

        <label for="password">Password</label>
        <input id="password" type="password" name="password" placeholder="••••••••" required
               autocomplete="current-password">

        <div class="form-text">
          By logging in, you confirm that you have read and agree to our
          <a href="privacy.php" target="_blank">Privacy Policy</a>
          (GDPR enforced on 25 May 2018).
        </div>

        <button type="submit" class="btn btn-primary">Login</button>
      </form>

      <div style="text-align:center; margin-top:1rem;">
        <span>Don’t have an account?</span>
        <a class="goto-register" href="register.php">Register</a>
      </div>
    </div>
  </div>

  <!-- keep your scripts if you use toastr -->
  <script src="assets/js/jquery.min.js"></script>
  <script src="assets/js/toastr.min.js"></script>
  <script src="assets/js/script.js" defer></script>
  <?php include 'config/toastr.php'; ?>
</body>
</html>
