<?php
// student_register.php
include('includes/db_connect.php'); // include database connection

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name   = $_POST['name'];
    $email  = $_POST['email'];
    $course = $_POST['course'];
    $year   = $_POST['year'];

    // Insert into database
    $sql = "INSERT INTO students (name, email, course, year) 
            VALUES ('$name', '$email', '$course', '$year')";

    if ($conn->query($sql) === TRUE) {
        echo "New student registered successfully!";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Student Registration</title>
</head>
<body>
    <h2>Register Student</h2>
    <form method="POST" action="">
        <label>Name:</label><br>
        <input type="text" name="name" required><br><br>

        <label>Email:</label><br>
        <input type="email" name="email" required><br><br>

        <label>Course:</label><br>
        <input type="text" name="course" required><br><br>

        <label>Year:</label><br>
        <input type="number" name="year" required><br><br>

        <input type="submit" value="Register">
    </form>
</body>
</html>
