<?php
session_start();
include('includes/db_connect.php');

// Ensure only admin can access
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    die("Access denied. Admins only.");
}

if (!isset($_GET['id'])) {
    die("No student ID provided.");
}

$student_id = (int)$_GET['id'];

// Fetch student details
$stmt = $conn->prepare("SELECT id, name, email, department, photo FROM students WHERE id=?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();

if (!$student) {
    die("Student not found.");
}

// Handle update
if (isset($_POST['update_student'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $department = $_POST['department'];

    // Handle photo upload
    $photo = $student['photo'];
    if (!empty($_FILES['photo']['name'])) {
        $targetDir = "uploads/";
        if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
        $photo = $targetDir . time() . "_" . basename($_FILES['photo']['name']);
        move_uploaded_file($_FILES['photo']['tmp_name'], $photo);
    }

    $update_sql = "UPDATE students SET name=?, email=?, department=?, photo=? WHERE id=?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("ssssi", $name, $email, $department, $photo, $student_id);
    if ($stmt->execute()) {
        header("Location: student_list.php");
        exit();
    } else {
        echo "Error updating student.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Student</title>
    <style>
        body { font-family:'Segoe UI',Arial,sans-serif; background:linear-gradient(135deg,#1f1c2c,#928dab); color:#fff; margin:0; padding:0; }
        header { background:rgba(255,255,255,0.1); padding:15px; text-align:center; }
        header h2 { margin:0; font-size:28px; color:#ffcc00; }
        nav a { margin:0 15px; text-decoration:none; color:#00e6e6; font-weight:bold; }
        nav a:hover { color:#ffcc00; }
        .container { width:50%; margin:30px auto; background:rgba(255,255,255,0.1); padding:25px; border-radius:12px; box-shadow:0 6px 12px rgba(0,0,0,0.3); }
        label { display:block; margin-top:10px; font-weight:bold; }
        input[type="text"], input[type="email"], input[type="file"] { width:100%; padding:10px; margin-top:5px; border-radius:6px; border:none; }
        input[type="submit"] { margin-top:20px; padding:12px; width:100%; background:#0072ff; color:#fff; font-weight:bold; border:none; border-radius:6px; cursor:pointer; }
        input[type="submit"]:hover { background:#00c6ff; }
        .profile-photo { text-align:center; margin-bottom:15px; }
        .profile-photo img { width:120px; height:120px; border-radius:50%; border:3px solid #ffcc00; }
    </style>
</head>
<body>
    <header>
        <h2>Edit Student</h2>
        <nav>
            <a href="admin_dashboard.php">Dashboard</a>
            <a href="student_list.php">Manage Students</a>
            <a href="logout.php">Logout</a>
        </nav>
    </header>

    <div class="container">
        <div class="profile-photo">
            <?php if (!empty($student['photo'])) { ?>
                <img src="<?php echo htmlspecialchars($student['photo']); ?>" alt="Profile Photo">
            <?php } else { ?>
                <img src="images/avatar_placeholder.png" alt="No Photo">
            <?php } ?>
        </div>

        <form method="POST" enctype="multipart/form-data">
            <label>Name:</label>
            <input type="text" name="name" value="<?php echo htmlspecialchars($student['name']); ?>" required>

            <label>Email:</label>
            <input type="email" name="email" value="<?php echo htmlspecialchars($student['email']); ?>" required>

            <label>Department:</label>
            <input type="text" name="department" value="<?php echo htmlspecialchars($student['department']); ?>">

            <label>Photo:</label>
            <input type="file" name="photo">

            <input type="submit" name="update_student" value="Update Student">
        </form>
    </div>
</body>
</html>
