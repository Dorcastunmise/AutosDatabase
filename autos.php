<?php
session_start();

// Check if user is logged in
if (!isset($_GET['name'])) {
    die("Name parameter missing");
}

if (isset($_POST['logout'])) {
    header('Location: login.php');
    return;
}

include_once "pdo.php";

$errors = array();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add'])) {
    // Validate input
    $make = trim($_POST['make']);
    $year = trim($_POST['year']);
    $mileage = trim($_POST['mileage']);

    if (!is_numeric($year)) {
        $errors['year'] = "Year must be numeric";
    }

    if (!is_numeric($mileage)) {
        $errors['mileage'] = "Mileage must be numeric";
    }

    if (strlen($make) < 1) {
        $errors['make'] = "Make is required";
    }

    if (empty($errors)) {
        // Insert data into the database
        $stmt = $pdo->prepare('INSERT INTO autos (make, year, mileage) VALUES (:mk, :yr, :mi)');
        $stmt->execute(array(
            ':mk' => $make,
            ':yr' => $year,
            ':mi' => $mileage
        ));

        $_SESSION['success'] = "Record inserted";
        header('Location: autos.php?name=' . urlencode($_GET['name']));
        return;
    }
}

// Fetch automobile records from the database
$stmt = $pdo->query("SELECT * FROM autos");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Alimi Oluwatunmise</title>
</head>
<body>
    <section>
        <h4><i>Welcome <?php echo htmlentities($_GET['name']); ?></i></h4>
        <?php
        if (isset($_SESSION['error'])) {
            echo '<p style="color: red;">' . htmlentities($_SESSION['error']) . '</p>';
            unset($_SESSION['error']);
        }

        if (isset($_SESSION['success'])) {
            echo '<p style="color: green;">' . htmlentities($_SESSION['success']) . '</p>';
            unset($_SESSION['success']);
        }
        ?>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . '?name=' . urlencode($_GET['name']); ?>" method="POST">
            <label>Make:</label>
                <input type="text" name="make">
                    <p><?php echo isset($errors['make']) ? '<span style="color: red;">' . $errors['make'] . '</span>' : ''; ?></p>
                        <br>
            <label>Year:</label>
                <input type="text" name="year">
                    <p><?php echo isset($errors['year']) ? '<span style="color: red;">' . $errors['year'] . '</span>' : ''; ?></p>
                        <br>
            <label>Mileage:</label>
                <input type="text" name="mileage">
                    <p><?php echo isset($errors['mileage']) ? '<span style="color: red;">' . $errors['mileage'] . '</span>' : ''; ?></p>
                        <br>
            <div>
                <input type="submit" name="add" value="Add">
                    <input type="submit" name="logout" value="Logout">
            </div>
        </form>

        <?php
        if ($rows) {
            echo '<h3>Automobiles</h3>';
            echo '<ul>';
            foreach ($rows as $row) {
                $make = htmlspecialchars($row['make']); // Using htmlspecialchars to prevent rendering HTML tags
                echo '<li>' . $make . ' ' . $row['year'] . ' ' . $row['mileage'] . ' miles</li>';
            }
            echo '</ul>';
        }
        ?>
    </section>
</body>
</html>
