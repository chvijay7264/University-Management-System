<?php
session_start();
include('includes/db_connect.php');

// Ensure only admin can access
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    die("Access denied. Admins only.");
}

$id = $_GET['id'];

// Fetch enrollment details
$result = $conn->query("SELECT * FROM enrollments WHERE id = $id");
$enrollment = $result->fetch_assoc();

// Fetch students
$students = $conn->query("SELECT id, name FROM students");

// Fetch courses
$courses = $conn->query("SELECT id, course_name FROM courses");

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $student_id = $_POST['student_id'];
    $course_id  = $_POST['course_id'];

    $sql = "UPDATE enrollments SET student_id='$student_id', course_id='$course_id' WHERE id=$id";

    if ($conn->query($sql) === TRUE) {
        header("Location: enrollment_list.php"); // redirect back to list
        exit();
    } else {
        echo "Error: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Enrollment</title>
</head>
<body>
    <h2>Edit Enrollment</h2>
    <form method="POST" action="">
        <label>Select Student:</label><br>
        <select name="student_id" required>
            <?php while($s = $students->fetch_assoc()) { ?>
                <option value="<?php echo $s['id']; ?>" <?php if($s['id'] == $enrollment['student_id']) echo "selected"; ?>>
                    <?php echo $s['name']; ?>
                </option>
            <?php } ?>
        </select><br><br>

        <label>Select Course:</label><br>
        <select name="course_id" required>
            <?php while($c = $courses->fetch_assoc()) { ?>
                <option value="<?php echo $c['id']; ?>" <?php if($c['id'] == $enrollment['course_id']) echo "selected"; ?>>
                    <?php echo $c['course_name']; ?>
                </option>
            <?php } ?>
        </select><br><br>

        <input type="submit" value="Update Enrollment">
    </form>
</body>
</html>
