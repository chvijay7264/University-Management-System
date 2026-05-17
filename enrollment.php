<?php
session_start();
include('includes/db_connect.php');

// Ensure only admin can access
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    die("Access denied. Admins only.");
}

// Handle delete request securely
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']); // sanitize input
    $stmt = $conn->prepare("DELETE FROM enrollments WHERE id = ?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    header("Location: enrollment_list.php?msg=deleted"); // refresh page after deletion
    exit();
}

// Fetch all enrollments with student + course info
$sql = "SELECT enrollments.id, students.name AS student_name, courses.course_name AS course_name
        FROM enrollments
        JOIN students ON enrollments.student_id = students.id
        JOIN courses ON enrollments.course_id = courses.id
        ORDER BY enrollments.id ASC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Enrollment List</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; background: #f4f4f4; }
        h2 { margin-top: 20px; }
        table {
            border-collapse: collapse;
            width: 80%;
            margin: 20px auto;
            background: #fff;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        th, td {
            border: 1px solid #333;
            padding: 10px;
            text-align: center;
        }
        th { background-color: #0072ff; color: #fff; }
        nav {
            margin: 20px;
        }
        nav a {
            margin: 0 10px;
            text-decoration: none;
            color: #0072ff;
            font-weight: bold;
        }
        nav a:hover { text-decoration: underline; }
        .delete-link {
            color: red;
            text-decoration: none;
            font-weight: bold;
        }
        .msg {
            margin: 15px;
            font-weight: bold;
            color: green;
        }
    </style>
</head>
<body>
    <h2>Enrollment List</h2>

    <!-- ✅ Navigation -->
    <nav>
        <a href="admin_dashboard.php">Dashboard</a> |
        <a href="student_list.php">Manage Students</a> |
        <a href="faculty_list.php">Manage Faculty</a> |
        <a href="course_list.php">Manage Courses</a> |
        <a href="enrollment.php">Enroll Student</a> |
        <a href="logout.php">Logout</a>
    </nav>

    <?php if (isset($_GET['msg']) && $_GET['msg'] == 'deleted') { ?>
        <div class="msg">Enrollment deleted successfully!</div>
    <?php } ?>

    <table>
        <tr>
            <th>ID</th>
            <th>Student</th>
            <th>Course</th>
            <th>Action</th>
        </tr>
        <?php
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                echo "<tr>
                        <td>".$row['id']."</td>
                        <td>".htmlspecialchars($row['student_name'])."</td>
                        <td>".htmlspecialchars($row['course_name'])."</td>
                        <td><a class='delete-link' href='enrollment_list.php?delete_id=".$row['id']."' onclick=\"return confirm('Are you sure you want to delete this enrollment?');\">Delete</a></td>
                      </tr>";
            }
        } else {
            echo "<tr><td colspan='4'>No enrollments found</td></tr>";
        }
        ?>
    </table>
</body>
</html>
