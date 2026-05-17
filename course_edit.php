<?php
session_start();
include('includes/db_connect.php');

// Ensure only admin can access
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    die("Access denied. Admins only.");
}

$id = $_GET['id'];

// Fetch course details
$result = $conn->query("SELECT * FROM courses WHERE id = $id");
$course = $result->fetch_assoc();

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $course_name = $_POST['course_name'];
    $department  = $_POST['department'];

    $sql = "UPDATE courses SET course_name='$course_name', department='$department' WHERE id=$id";

    if ($conn->query($sql) === TRUE) {
        header("Location: course_list.php"); // redirect back to list
        exit();
    } else {
        echo "Error: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Course</title>
</head>
<body>
    <h2>Edit Course</h2>
    <form method="POST" action="">
        <label>Course Name:</label><br>
        <input type="text" name="course_name" value="<?php echo $course['course_name']; ?>" required><br><br>

        <label>Department:</label><br>
        <input type="text" name="department" value="<?php echo $course['department']; ?>" required><br><br>

        <input type="submit" value="Update Course">
    </form>
</body>
</html>
