<?php
session_start();
include('includes/db_connect.php');

// Ensure only admin can access
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    die("Access denied. Admins only.");
}

$id = $_GET['id'];

// Fetch faculty details
$result = $conn->query("SELECT * FROM faculty WHERE id = $id");
$faculty = $result->fetch_assoc();

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name       = $_POST['name'];
    $email      = $_POST['email'];
    $department = $_POST['department'];

    $sql = "UPDATE faculty SET name='$name', email='$email', department='$department' WHERE id=$id";

    if ($conn->query($sql) === TRUE) {
        header("Location: faculty_list.php"); // redirect back to list
        exit();
    } else {
        echo "Error: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Faculty</title>
</head>
<body>
    <h2>Edit Faculty</h2>
    <form method="POST" action="">
        <label>Name:</label><br>
        <input type="text" name="name" value="<?php echo $faculty['name']; ?>" required><br><br>

        <label>Email:</label><br>
        <input type="email" name="email" value="<?php echo $faculty['email']; ?>" required><br><br>

        <label>Department:</label><br>
        <input type="text" name="department" value="<?php echo $faculty['department']; ?>" required><br><br>

        <input type="submit" value="Update Faculty">
    </form>
</body>
</html>
