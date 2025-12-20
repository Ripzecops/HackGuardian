<?php
session_start();
require_once 'config/config.php';
require_once 'config/database.php';

if (isset($_POST['login'])) {

    $email = $_POST['email'];ls
    $password = $_POST['password'];
    $role = $_POST['role'];

    $q = mysqli_query($conn,
        "SELECT * FROM users 
         WHERE email='$email' AND password='$password' AND role='$role'"
    );

    if ($user = mysqli_fetch_assoc($q)) {

        // Redirect directly (NO ROLE SESSION LOGIC)
        switch ($role) {
            case 'User':
                header("Location: app/views/dashboard/user.php");
                break;
            case 'Pentester':
                header("Location: app/views/dashboard/pentester.php");
                break;
            case 'Hacker':
                header("Location: app/views/dashboard/hacker.php");
                break;
            case 'QA':
                header("Location: app/views/dashboard/qa.php");
                break;
            case 'Engineer':
                header("Location: app/views/dashboard/engineer.php");
                break;
            case 'Admin':
                header("Location: app/views/dashboard/admin.php");
                break;
        }
        exit;
    } else {
        $error = "Invalid credentials or role!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>HackGuardian Login</title>
    <link rel="stylesheet" href="public/css/style.css">
</head>
<body>

<h2>HackGuardian Login</h2>

<?php if (!empty($error)) echo "<p style='color:red'>$error</p>"; ?>

<form method="post">
    <input type="email" name="email" placeholder="Email" required>
    <input type="password" name="password" placeholder="Password" required>

    <select name="role" required>
        <option value="">Select Role</option>
        <option>User</option>
        <option>Pentester</option>
        <option>Hacker</option>
        <option>QA</option>
        <option>Engineer</option>
        <option>Admin</option>
    </select>

    <button name="login">Login</button>
</form>

<br>
<a href="app/views/auth/register.php">
    <button>Customer Register</button>
</a>

</body>
</html>
