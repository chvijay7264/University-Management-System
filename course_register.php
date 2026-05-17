<?php
// course_register.php
include('includes/db_connect.php'); // include database connection

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $course_name = $_POST['course_name'];
    $department  = $_POST['department'];

    $sql = "INSERT INTO courses (course_name, department) 
            VALUES ('$course_name', '$department')";

    if ($conn->query($sql) === TRUE) {
        echo "New course added successfully!";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Course Registration</title>
</head>
<body>
    <h2>Add New Course</h2>
    <form method="POST" action="">
        <label>Course Name:</label><br>
        <input type="text" name="course_name" required><br><br>

        <label>Department:</label><br>
        <input type="text" name="department" required><br><br>

        <input type="submit" value="Add Course">
    </form>
</body>
</html>
