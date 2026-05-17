<?php
// faculty_register.php
include('includes/db_connect.php'); // include database connection

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name       = $_POST['name'];
    $department = $_POST['department'];
    $email      = $_POST['email'];

    $sql = "INSERT INTO faculty (name, department, email) 
            VALUES ('$name', '$department', '$email')";

    if ($conn->query($sql) === TRUE) {
        echo "New faculty member added successfully!";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Faculty Registration</title>
</head>
<body>
    <h2>Add New Faculty</h2>
    <form method="POST" action="">
        <label>Name:</label><br>
        <input type="text" name="name" required><br><br>

        <label>Department:</label><br>
        <input type="text" name="department" required><br><br>

        <label>Email:</label><br>
        <input type="email" name="email" required><br><br>

        <input type="submit" value="Add Faculty">
    </form>
</body>
</html>
