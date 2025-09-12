<?php
session_start();
include 'config/db.php';

$tableCheck = $conn->query("SHOW TABLES LIKE 'users'");
if ($tableCheck->num_rows == 0) {
    $createTableSql = "CREATE TABLE users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        is_admin TINYINT(1) DEFAULT 0
    )";
    $conn->query($createTableSql);
}
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $check = $conn->prepare("SELECT id FROM users WHERE username=?");
    $check->bind_param("s", $username);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $error = "Username already taken!";
    } else {
        $stmt = $conn->prepare("INSERT INTO users (username, name, email, password) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $username, $name, $email, $password);
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
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="description" content="Ripper Teach & Solutions - Web & App Development, cloud Solutions, and Security Services.">
    <title>Login</title>
    <link rel="stylesheet" href="assets/css/style.css" />
    <link href="assets/css/css2.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-..." crossorigin="anonymous" referrerpolicy="no-referrer" />

</head>

<body>
    <div class="login-container">
        <div class="login-form">
            <h2>Register</h2>
            <?php if (!empty($error)) echo "<p style='color:red;'>$error</p>"; ?>
            <form method="POST" action="">
                <label>Username:</label>
                <input type="text" placeholder="admin" name="username" required value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"> 
                <label>Name:</label>
                <input type="text" placeholder="Admin Lal" name="name" required value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
                <label>Email:</label>
                <input type="email" placeholder="admin@gmail.com" name="email" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                <label>Password:</label>
                <input type="password" placeholder="12345" name="password" required>
                <button type="submit">Register</button>
            </form>
            <span>Goto </span><a style="text-decoration:none" class="goto-login" href="login.php">Login</a>
        </div>
    </div>
</body>

</html>