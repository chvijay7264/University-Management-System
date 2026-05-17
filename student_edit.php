<?php
session_start();
include('includes/db_connect.php');

// Ensure only admin can access
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    die("Access denied. Admins only.");
}

$id = $_GET['id'];

// Fetch student details
$result = $conn->query("SELECT * FROM students WHERE id = $id");
$student = $result->fetch_assoc();

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name  = $_POST['name'];
    $email = $_POST['email'];

    $sql = "UPDATE students SET name='$name', email='$email' WHERE id=$id";

    if ($conn->query($sql) === TRUE) {
        header("Location: student_list.php"); // redirect back to list
        exit();
    } else {
        echo "Error: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Student</title>
</head>
<body>
    <h2>Edit Student</h2>
    <form method="POST" action="">
        <label>Name:</label><br>
        <input type="text" name="name" value="<?php echo $student['name']; ?>" required><br><br>

        <label>Email:</label><br>
        <input type="email" name="email" value="<?php echo $student['email']; ?>" required><br><br>

        <input type="submit" value="Update Student">
    </form>
</body>
</html>
