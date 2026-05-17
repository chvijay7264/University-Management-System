<?php
session_start();
include('includes/db_connect.php');

// Ensure only admin can access
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    die("Access denied. Admins only.");
}

if (!isset($_GET['id'])) {
    die("No course ID provided.");
}

$course_id = (int)$_GET['id'];

// Fetch course details
$stmt = $conn->prepare("SELECT id, course_name, department FROM courses WHERE id=?");
$stmt->bind_param("i", $course_id);
$stmt->execute();
$course = $stmt->get_result()->fetch_assoc();

if (!$course) {
    die("Course not found.");
}

// Handle update
if (isset($_POST['update_course'])) {
    $course_name = $_POST['course_name'];
    $department = $_POST['department'];

    $update_sql = "UPDATE courses SET course_name=?, department=? WHERE id=?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("ssi", $course_name, $department, $course_id);
    if ($stmt->execute()) {
        // ✅ Redirect to the correct file name
        header("Location: course_list.php");
        exit();
    } else {
        echo "Error updating course.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Course</title>
    <style>
        body { font-family:'Segoe UI',Arial,sans-serif; background:linear-gradient(135deg,#1f1c2c,#928dab); color:#fff; margin:0; padding:0; }
        header { background:rgba(255,255,255,0.1); padding:15px; text-align:center; }
        header h2 { margin:0; font-size:28px; color:#ffcc00; }
        nav a { margin:0 15px; text-decoration:none; color:#00e6e6; font-weight:bold; }
        nav a:hover { color:#ffcc00; }
        .container { width:50%; margin:30px auto; background:rgba(255,255,255,0.1); padding:25px; border-radius:12px; box-shadow:0 6px 12px rgba(0,0,0,0.3); }
        label { display:block; margin-top:10px; font-weight:bold; }
        input[type="text"] { width:100%; padding:10px; margin-top:5px; border-radius:6px; border:none; }
        input[type="submit"] { margin-top:20px; padding:12px; width:100%; background:#0072ff; color:#fff; font-weight:bold; border:none; border-radius:6px; cursor:pointer; }
        input[type="submit"]:hover { background:#00c6ff; }
    </style>
</head>
<body>
    <header>
        <h2>Edit Course</h2>
        <nav>
            <a href="admin_dashboard.php">Dashboard</a>
            <a href="course_list.php">Manage Courses</a> <!-- ✅ Correct file name -->
            <a href="logout.php">Logout</a>
        </nav>
    </header>

    <div class="container">
        <form method="POST">
            <label>Course Name:</label>
            <input type="text" name="course_name" value="<?php echo htmlspecialchars($course['course_name']); ?>" required>

            <label>Department:</label>
            <input type="text" name="department" value="<?php echo htmlspecialchars($course['department']); ?>">

            <input type="submit" name="update_course" value="Update Course">
        </form>
    </div>
</body>
</html>
