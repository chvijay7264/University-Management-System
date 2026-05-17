<?php
session_start();
include('includes/db_connect.php');

// Ensure only admin can access
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    die("Access denied. Admins only.");
}

if (!isset($_GET['id'])) {
    die("No enrollment ID provided.");
}

$enrollment_id = (int)$_GET['id'];

// Fetch enrollment details
$stmt = $conn->prepare("SELECT e.id, e.student_id, e.course_id, s.name AS student_name, c.course_name 
                        FROM enrollments e
                        JOIN students s ON e.student_id = s.id
                        JOIN courses c ON e.course_id = c.id
                        WHERE e.id=?");
$stmt->bind_param("i", $enrollment_id);
$stmt->execute();
$enrollment = $stmt->get_result()->fetch_assoc();

if (!$enrollment) {
    die("Enrollment not found.");
}

// Fetch all students and courses for dropdowns
$students = $conn->query("SELECT id, name FROM students ORDER BY name ASC");
$courses = $conn->query("SELECT id, course_name FROM courses ORDER BY course_name ASC");

// Handle update
if (isset($_POST['update_enrollment'])) {
    $student_id = $_POST['student_id'];
    $course_id = $_POST['course_id'];

    $update_sql = "UPDATE enrollments SET student_id=?, course_id=? WHERE id=?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("iii", $student_id, $course_id, $enrollment_id);
    if ($stmt->execute()) {
        // ✅ Redirect to the correct file name
        header("Location: enrollment_list.php");
        exit();
    } else {
        echo "Error updating enrollment.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Enrollment</title>
    <style>
        body { font-family:'Segoe UI',Arial,sans-serif; background:linear-gradient(135deg,#1f1c2c,#928dab); color:#fff; margin:0; padding:0; }
        header { background:rgba(255,255,255,0.1); padding:15px; text-align:center; }
        header h2 { margin:0; font-size:28px; color:#ffcc00; }
        nav a { margin:0 15px; text-decoration:none; color:#00e6e6; font-weight:bold; }
        nav a:hover { color:#ffcc00; }
        .container { width:50%; margin:30px auto; background:rgba(255,255,255,0.1); padding:25px; border-radius:12px; box-shadow:0 6px 12px rgba(0,0,0,0.3); }
        label { display:block; margin-top:10px; font-weight:bold; }
        select { width:100%; padding:10px; margin-top:5px; border-radius:6px; border:none; }
        input[type="submit"] { margin-top:20px; padding:12px; width:100%; background:#0072ff; color:#fff; font-weight:bold; border:none; border-radius:6px; cursor:pointer; }
        input[type="submit"]:hover { background:#00c6ff; }
    </style>
</head>
<body>
    <header>
        <h2>Edit Enrollment</h2>
        <nav>
            <a href="admin_dashboard.php">Dashboard</a>
            <a href="enrollment_list.php">Manage Enrollments</a> <!-- ✅ Correct file name -->
            <a href="logout.php">Logout</a>
        </nav>
    </header>

    <div class="container">
        <form method="POST">
            <label>Student:</label>
            <select name="student_id" required>
                <?php while($s = $students->fetch_assoc()){ 
                    $sel = ($s['id'] == $enrollment['student_id']) ? "selected" : "";
                    echo "<option value='".$s['id']."' $sel>".htmlspecialchars($s['name'])."</option>";
                } ?>
            </select>

            <label>Course:</label>
            <select name="course_id" required>
                <?php while($c = $courses->fetch_assoc()){ 
                    $sel = ($c['id'] == $enrollment['course_id']) ? "selected" : "";
                    echo "<option value='".$c['id']."' $sel>".htmlspecialchars($c['course_name'])."</option>";
                } ?>
            </select>

            <input type="submit" name="update_enrollment" value="Update Enrollment">
        </form>
    </div>
</body>
</html>
