<?php
include_once "pdo.php";

session_start();

$errors = array();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate input
    if (empty($_POST['email']) || empty($_POST['password'])) {
        $errors['email'] = "Email and password are required";
    } else {
        $email = htmlentities($_POST['email']);

        // Additional validation for email format
        if (!strpos($email, '@')) {
            $errors['email'] = "Email must have an at-sign (@)";
        }

        // Check login credentials
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute(array(':email' => $email));
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Verify the password using password_verify
            $stored_hash = password_hash($user['password'], PASSWORD_DEFAULT);


            // var_dump($_POST['password']);
            // var_dump($stored_hash);

            if (password_verify($_POST['password'], $stored_hash)) {
                // Password is correct, proceed with login
                $_SESSION['success'] = "Login success $email";
                error_log("Login success $email");
                header("Location: autos.php?name=" . urlencode($email));
                return;
            } else {
                // Password is incorrect
                error_log("Login fail $email");
                $errors['password'] = "Login failed. Incorrect password";
            }
        } else {
            // User not found
            $errors['email'] = "Login failed. User not found";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - Alimi Oluwatunmise</title>
</head>
<body>
    <section>
        <h4><i>Signup/Login</i></h4>
        <?php
        if (isset($_SESSION['error'])) {
            echo '<p style="color: red;">' . htmlentities($_SESSION['error']) . '</p>';
            unset($_SESSION['error']);
        }
        ?>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
            <label>Email:</label>
            <input type="text" name="email" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>">
            <label><?php echo isset($errors['email']) ? '<p style="color: red;">' . $errors['email'] : ''; ?></label>
            <br><br>

            <label>Password:</label>
            <input type="password" name="password">
            <label><?php echo isset($errors['password']) ? '<p style="color: red;">' . $errors['password'] : ''; ?></label>
            <br><br>
            <div>
                <input type="submit" name="login" value="Login">
            </div>
        </form>
    </section>
</body>
</html>