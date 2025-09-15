<?php
define('ABSPATH', __DIR__);
session_start();
require_once 'config/db.php';

$sql = "SELECT id, role FROM roles WHERE role != 'admin'";
$result = $conn->query($sql);
$roles = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $roles[] = $row;
    }
}
$error = "";

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
        $stmt = $conn->prepare("INSERT INTO users (username, name, email,role_id, password) VALUES (?, ?,?, ?, ?)");
        $stmt->bind_param("sssss", $username, $name, $email, $role_id,  $password);
        if ($stmt->execute()) {
            $_SESSION['loggedin'] = true;
            $_SESSION['username'] = $username;

            $_SESSION['toastr'] = [
                'type' => 'success',
                'message' => 'Registered successful! Welcome, ' . $username . '!'
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
<html>

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="description" content="Ripper Teach & Solutions - Web & App Development, cloud Solutions, and Security Services.">
    <link rel="icon" type="image/png" href="assets/images/logo-2.png">
    <title>Sign Up - Ripper Tech & Solutions</title>
    <link rel="stylesheet" href="assets/css/style.css" />
    <link href="assets/css/css2.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-..." crossorigin="anonymous" referrerpolicy="no-referrer" />

</head>

<body>
    <style>
        .login-form h2{
  text-align: center;
}


        </style>
    <div class="login-container">
        <div class="login-form">
            <h2>Sign Up </h2>
            <?php if (!empty($error)) echo "<p style='color:red;'>$error</p>"; ?>
            
            <form method="POST" action="" novalidate>

                <label for="reg_name">Name</label>
                <input id="reg_name" name="name" type="text" required autocomplete="name"
                        value="<?= isset($_POST['name']) ? htmlspecialchars($_POST['name']) : '' ?>">

                <label for="reg_email">Email</label>
                <input id="reg_email" name="email" type="email" required autocomplete="email"
                        value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">

                <label for="reg_role">Role</label>
                <select id="reg_role" name="role_id" required>
                    <option value="" disabled selected>Select Role</option>
                    <?php foreach ($roles as $role): ?>
                    <option value="<?= $role['id'] ?>"><?= ucfirst($role['role']) ?></option>
                    <?php endforeach; ?>
                </select>

                <label for="reg_password">Password</label>
                <input id="reg_password" name="password" type="password" minlength="6" required autocomplete="new-password">

                <button type="submit">Register</button>
                <p id="formMessage" class="form-message" role="alert" aria-live="polite"></p>

            </form>

            <span id=test>click<a style="text-decoration:none" class="goto-login" href="login.php" >Login</a></span>
        </div>
    </div>
</body>

</html>