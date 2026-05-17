<?php
session_start();
include('includes/db_connect.php');

// Ensure only faculty can access
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'faculty') {
    die("Access denied. Faculty only.");
}

$username = $_SESSION['username'];

// ✅ Fetch faculty details
$sql = "SELECT f.id, f.name, f.email, f.department, f.photo
        FROM users u
        JOIN faculty f ON u.faculty_id = f.id
        WHERE u.username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$faculty = $stmt->get_result()->fetch_assoc();

if ($faculty) {
    $faculty_id = $faculty['id'];
    $msg = "";

    // ✅ Handle photo upload
    if (isset($_POST['upload_photo'])) {
        if (!empty($_FILES['photo']['name'])) {
            $targetDir = "uploads/";
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0777, true);
            }
            $photoPath = $targetDir . time() . "_" . basename($_FILES['photo']['name']);
            if (move_uploaded_file($_FILES['photo']['tmp_name'], $photoPath)) {
                $update_sql = "UPDATE faculty SET photo=? WHERE id=?";
                $stmt = $conn->prepare($update_sql);
                $stmt->bind_param("si", $photoPath, $faculty_id);
                if ($stmt->execute()) {
                    $msg = "✅ Photo uploaded successfully!";
                }
            }
        }
    }

    // ✅ Handle photo removal
    if (isset($_POST['remove_photo'])) {
        $update_sql = "UPDATE faculty SET photo=NULL WHERE id=?";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("i", $faculty_id);
        if ($stmt->execute()) {
            $msg = "✅ Photo removed successfully!";
        }
    }

    // ✅ Handle new course submission
    if (isset($_POST['add_course'])) {
        $course_name = $_POST['course_name'];
        $department = $_POST['department'];

        $insert_sql = "INSERT INTO courses (course_name, department, faculty_id) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($insert_sql);
        $stmt->bind_param("ssi", $course_name, $department, $faculty_id);
        if ($stmt->execute()) {
            $msg = "✅ Course added successfully!";
        } else {
            $msg = "❌ Error adding course.";
        }
    }

    // ✅ Handle student enrollment
    if (isset($_POST['enroll_student'])) {
        $student_id = $_POST['student_id'];
        $course_id = $_POST['course_id'];

        $check_sql = "SELECT * FROM enrollments WHERE student_id=? AND course_id=?";
        $stmt = $conn->prepare($check_sql);
        $stmt->bind_param("ii", $student_id, $course_id);
        $stmt->execute();
        $check_result = $stmt->get_result();

        if ($check_result->num_rows > 0) {
            $msg = "⚠️ Student already enrolled in this course.";
        } else {
            $insert_sql = "INSERT INTO enrollments (student_id, course_id) VALUES (?, ?)";
            $stmt = $conn->prepare($insert_sql);
            $stmt->bind_param("ii", $student_id, $course_id);
            if ($stmt->execute()) {
                $msg = "✅ Student enrolled successfully!";
            } else {
                $msg = "❌ Error enrolling student.";
            }
        }
    }

    // ✅ Get courses assigned to this faculty
    $course_sql = "SELECT id, course_name, department FROM courses WHERE faculty_id = ?";
    $stmt = $conn->prepare($course_sql);
    $stmt->bind_param("i", $faculty_id);
    $stmt->execute();
    $course_result = $stmt->get_result();

    // ✅ Get students enrolled in those courses
    $student_sql = "SELECT s.name AS student_name, c.course_name 
                    FROM enrollments e
                    JOIN students s ON e.student_id = s.id 
                    JOIN courses c ON e.course_id = c.id 
                    WHERE c.faculty_id = ?";
    $stmt = $conn->prepare($student_sql);
    $stmt->bind_param("i", $faculty_id);
    $stmt->execute();
    $student_result = $stmt->get_result();

} else {
    die("Faculty record not found.");
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Faculty Dashboard</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background: linear-gradient(135deg, #755ce7, #211f1f); color: #fff; margin: 0; padding: 0; }
        header { background: rgba(255,255,255,0.1); padding: 15px; text-align: center; }
        header h2{ margin: 10px;
        text-align:center;margin-right: 100px; font-size: 28px; color: #ce91dd;}
        .logo {
            height: 120px;
            width: 120px;
            border-radius: 50%; /* circular logo */
            object-fit: cover;
            vertical-align: middle;
            margin-left: 10px;
        }
        header h3 { margin: 20px; font-size: 20px; color: #d570f1; }
       
        nav { margin-top: 15px; }
        nav a { margin: 0 15px; text-decoration: none; color: #00e6e6ae; font-weight: bold; }
        nav a:hover { color: #ffffff; }
        .container { display: flex; justify-content: space-around; margin: 30px; flex-wrap: wrap; }
        .card { background: rgba(255,255,255,0.1); border-radius: 12px; padding: 25px; width: 45%; margin-bottom: 20px; box-shadow: 0 6px 12px rgba(0,0,0,0.3); }
        .profile-photo { text-align: center; margin-bottom: 15px; }
        .profile-photo img { width: 120px; height: 120px; border-radius: 50%; border: 5px solid #40008a; }
        table { border-collapse: collapse; width: 100%; margin-top: 15px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: center; }
        th { background: #444; color: #d9ec4c; }
        .logout { text-align: center; margin: 30px; }
        .logout a { color: #8b878b; font-weight: bold; text-decoration: none; }
        .logout a:hover { color: #ffffff; }
        .msg { margin: 15px; font-weight: bold; text-align: center; color: #c7dfda; }
        input, select { padding: 10px; margin: 10px 0; width: 100%; border-radius: 6px; border: none; }
        input[type="submit"] { background: #0072ff; color: #fff; font-weight: bold; cursor: pointer; }
    </style>
</head>
<body>
    <header>
        <!-- ✅ Logo added inside University header -->
        <h2>
            <img src="University.jpeg" alt="Borcelle University Logo" class="logo">
            University of Borcelle
        </h2>
        <h3>Faculty of Borcelle </h3>
        
        <nav>
            <a href="faculty_dashboard.php">Dashboard</a>
            <a href="logout.php">Logout</a>
        </nav>
    </header>

    <?php if (!empty($msg)) echo "<div class='msg'>$msg</div>"; ?>

    <div class="container">
        <!-- ✅ Profile Card -->
        <div class="card">
            <div class="profile-photo">
                <?php if (!empty($faculty['photo'])) { ?>
                    <img src="<?php echo htmlspecialchars($faculty['photo']); ?>" alt="Profile Photo">
                <?php } else { ?>
                    <img src="images/avatar_placeholder.png" alt="No Photo">
                <?php } ?>
            </div>
            <p><strong>Username:</strong> <?php echo htmlspecialchars($username); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($faculty['email'] ?? 'Not available'); ?></p>
            <p><strong>Department:</strong> <?php echo htmlspecialchars($faculty['department'] ?? 'Not available'); ?></p>

            <!-- ✅ Upload/Remove Photo -->
            <form method="POST" action="faculty_dashboard.php" enctype="multipart/form-data">
                <input type="file" name="photo" required>
                <input type="submit" name="upload_photo" value="Upload Photo">
            </form>
            <?php if (!empty($faculty['photo'])) { ?>
                <form method="POST" action="faculty_dashboard.php">
                    <input type="submit" name="remove_photo" value="Remove Photo">
                </form>
            <?php } ?>
        </div>

        <!-- ✅ Courses Card -->
        <div class="card">
            <h3 style="text-align:center; color: #d9ec4c;">Your Courses</h3>
            <table>
                <tr><th>Course Name</th><th>Department</th></tr>
                <?php
                if ($course_result->num_rows > 0) {
                    while($row = $course_result->fetch_assoc()) {
                        echo "<tr><td>".htmlspecialchars($row['course_name'])."</td><td>".htmlspecialchars($row['department'])."</td></tr>";
                    }
                } else {
                    echo "<tr><td colspan='2'>No courses assigned yet</td></tr>";
                }
                ?>
                </table>
            <!-- ✅ Add New Course Form -->
            <h3 style="text-align:center; color: #d9ec4c; margin-top:20px;">Add a New Course</h3>
            <form method="POST" action="faculty_dashboard.php">
                <input type="text" name="course_name" placeholder="Course Name" required>
                <input type="text" name="department" placeholder="Department" required>
                <input type="submit" name="add_course" value="Add Course">
                </form>
                </div>
                </div>
    <div class="container">
        <!-- ✅ Enrolled Students Card -->
        <div class="card" style="width: 100%;">
            <h3 style="text-align:center; color: #d9ec4c;">Enrolled Students</h3>
            <table>
                <tr><th>Student Name</th><th>Course Name</th></tr>
                <?php
                if ($student_result->num_rows > 0) {
                    while($row = $student_result->fetch_assoc()) {
                        echo "<tr><td>".htmlspecialchars($row['student_name'])."</td><td>".htmlspecialchars($row['course_name'])."</td></tr>";
                    }
                } else {
                    echo "<tr><td colspan='2'>No students enrolled yet</td></tr>";
                }
                ?>
            </table>
        </div>
        </div>
        </body>
        </html>
