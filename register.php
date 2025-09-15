<?php
define('ABSPATH', __DIR__);
session_start();
require_once 'config/db.php';

// Fetch roles (excluding admin)
$sql = "SELECT id, role FROM roles WHERE role != 'admin'";
$result = $conn->query($sql);
$roles = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $roles[] = $row;
    }
}
$error = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $username = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $_POST['name']));
    $email = trim($_POST['email']);
    $role_id = intval($_POST['role_id']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $check = $conn->prepare("SELECT id FROM users WHERE username=?");
    $check->bind_param("s", $username);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $error = "Username already taken!";
    } else {
        $stmt = $conn->prepare("INSERT INTO users (username, name, email, role_id, password) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssis", $username, $name, $email, $role_id, $password);
        if ($stmt->execute()) {
            $_SESSION['loggedin'] = true;
            $_SESSION['username'] = $username;
            $_SESSION['toastr'] = [
                'type' => 'success',
                'message' => 'Registration successful! Welcome, ' . $username . '!'
            ];
            header("Location: index.php");
            exit;
        } else {
            $error = "Something went wrong. Try again!";
        }
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="Sign up for Ripper Tech & Solutions to access web, mobile, cloud, and security services." />
  <meta name="keywords" content="Sign up, Register, Create Account, Ripper Tech & Solutions, Web Development, Cloud Solutions" />
  <title>Sign Up - Ripper Tech & Solutions</title>
  <link rel="icon" type="image/png" href="assets/images/logo-2.png" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" crossorigin="anonymous" />
  <style>
    body {
      font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
      background: linear-gradient(135deg, #667eea, #764ba2);
      display: flex;
      align-items: center;
      justify-content: center;
      min-height: 100vh;
      margin: 0;
    }
    .signup-container {
      background: #fff;
      border-radius: 16px;
      box-shadow: 0 10px 25px rgba(0,0,0,0.15);
      padding: 2rem;
      max-width: 420px;
      width: 100%;
      animation: fadeIn 0.8s ease-in-out;
    }
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }
    .signup-container h2 {
      text-align: center;
      margin-bottom: 1.5rem;
      color: #2c3e50;
    }
    .signup-container label {
      display: block;
      margin-top: 1rem;
      font-weight: 600;
      color: #2c3e50;
    }
    .signup-container input,
    .signup-container select {
      width: 100%;
      padding: 0.75rem;
      border-radius: 8px;
      border: 1px solid #ccc;
      margin-top: 0.5rem;
      transition: border-color 0.2s;
    }
    .signup-container input:focus,
    .signup-container select:focus {
      border-color: #667eea;
      outline: none;
      box-shadow: 0 0 0 3px rgba(102,126,234,0.3);
    }
    .form-check {
      margin-top: 1rem;
      font-size: 0.9rem;
      color: #555;
    }
    .form-check a { color: #667eea; text-decoration: none; }
    .form-check a:hover { text-decoration: underline; }
    .signup-container button {
      margin-top: 1.5rem;
      width: 100%;
      background: linear-gradient(135deg, #667eea, #764ba2);
      color: #fff;
      font-weight: 600;
      border: none;
      padding: 0.75rem;
      border-radius: 8px;
      cursor: pointer;
      transition: transform 0.2s, box-shadow 0.2s;
    }
    .signup-container button:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 15px rgba(102,126,234,0.4);
    }
    .error-message {
      color: #c62828;
      text-align: center;
      margin-bottom: 1rem;
    }
    .login-link {
      display: block;
      text-align: center;
      margin-top: 1rem;
      font-size: 0.9rem;
    }
    .login-link a { color: #667eea; text-decoration: none; }
    .login-link a:hover { text-decoration: underline; }
  </style>
</head>
<body>
  <div class="signup-container">
    <h2><i class="fas fa-user-plus"></i> Sign Up</h2>

    <?php if (!empty($error)) echo "<p class='error-message'>$error</p>"; ?>

    <form method="POST" action="" novalidate>
      <label for="reg_name"><i class="fas fa-user"></i> Name</label>
      <input id="reg_name" name="name" type="text" required autocomplete="name"
             value="<?= isset($_POST['name']) ? htmlspecialchars($_POST['name']) : '' ?>">

      <label for="reg_email"><i class="fas fa-envelope"></i> Email</label>
      <input id="reg_email" name="email" type="email" required autocomplete="email"
             value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">

      <label for="reg_role"><i class="fas fa-user-tag"></i> Role</label>
      <select id="reg_role" name="role_id" required>
        <option value="" disabled selected>Select Role</option>
        <?php foreach ($roles as $role): ?>
          <option value="<?= $role['id'] ?>"><?= ucfirst($role['role']) ?></option>
        <?php endforeach; ?>
      </select>

      <label for="reg_password"><i class="fas fa-lock"></i> Password</label>
      <input id="reg_password" name="password" type="password" minlength="6" required autocomplete="new-password">

      <div class="form-checkZ">
        <input class="form-check-input" type="checkbox" name="agree_privacy" id="agree_privacy" required>
        <label for="agree_privacy">
          I agree to the <a href="privacy.php" target="_blank">Privacy Policy</a> and understand how my data will be processed in compliance with GDPR (enforced on 25 May 2018).
        </label>
      </div>

      <button type="submit"><i class="fas fa-user-plus"></i> Register</button>
    </form>

    <div class="login-link">
      Already have an account? <a href="login.php">Login</a>
    </div>
  </div>
</body>
</html>
