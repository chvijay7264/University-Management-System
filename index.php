<?php
// Include the database connection file
include('includes/db_connect.php');

// Now you can use $conn directly
$sql = "SELECT * FROM students";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        echo "ID: " . $row["id"]. " - Name: " . $row["name"]. "<br>";
    }
} else {
    echo "No records found.";
}
?>
