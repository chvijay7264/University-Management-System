<?php
session_start();
include('includes/db_connect.php');

// Ensure only students can access
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'student') {
    die("Access denied. Students only.");
}

$username = $_SESSION['username'];

// ✅ Fetch student details with JOIN
$sql = "SELECT u.student_id, s.name, s.email, s.department, s.photo
        FROM users u
        JOIN students s ON u.student_id = s.id
        WHERE u.username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if ($user && $user['student_id']) {
    $student_id = $user['student_id'];
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
                $update_sql = "UPDATE students SET photo=? WHERE id=?";
                $stmt = $conn->prepare($update_sql);
                $stmt->bind_param("si", $photoPath, $student_id);
                if ($stmt->execute()) {
                    $msg = "✅ Photo uploaded successfully!";
                }
            }
        }
    }

    // ✅ Handle photo removal
    if (isset($_POST['remove_photo'])) {
        $update_sql = "UPDATE students SET photo=NULL WHERE id=?";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("i", $student_id);
        if ($stmt->execute()) {
            $msg = "✅ Photo removed successfully!";
        }
    }

    // ✅ Handle enrollment form submission
    if (isset($_POST['enroll'])) {
        $course_id = $_POST['course_id'];

        $check_sql = "SELECT * FROM enrollments WHERE student_id=? AND course_id=?";
        $stmt = $conn->prepare($check_sql);
        $stmt->bind_param("ii", $student_id, $course_id);
        $stmt->execute();
        $check_result = $stmt->get_result();

        if ($check_result->num_rows > 0) {
            $msg = "⚠️ Already enrolled in this course.";
        } else {
            $insert_sql = "INSERT INTO enrollments (student_id, course_id) VALUES (?, ?)";
            $stmt = $conn->prepare($insert_sql);
            $stmt->bind_param("ii", $student_id, $course_id);
            if ($stmt->execute()) {
                $msg = "✅ Enrollment successful!";
            } else {
                $msg = "❌ Error enrolling. Please try again.";
            }
        }
    }

    // ✅ Get enrolled courses
    $sql = "SELECT courses.course_name, courses.department 
            FROM enrollments 
            JOIN courses ON enrollments.course_id = courses.id 
            WHERE enrollments.student_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();

    // ✅ Get all available courses for dropdown
    $course_sql = "SELECT id, course_name FROM courses";
    $course_result = $conn->query($course_sql);

} else {
    die("Student record not linked. Please update user registration.");
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Student Dashboard</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background: linear-gradient(135deg, #755ce7, #211f1f); color: #fff; margin: 0; padding: 0; }
        header { background:linear-gradient(135deg, rgba(255,255,255,0.1),#2575fc); padding: 15px; text-align: center; border-radius:5px; }
        
        header h2 {
            margin: 20px;
            font-size: 28px;
            color: #ce91dd;
            display: flex;              /* flexbox for logo + text */
            align-items: center;        /* vertical alignment */
            justify-content: center;    /* keep centered */
            gap: 15px;  
            margin-right: 100px; 
                           /* space between logo and text */
        }
        .logo {
            height: 120px;
            width: 120px;
            border-radius: 50%; /* circular logo */
            object-fit: cover;
            margin-left: 30px;
        }
        header h3 { margin: 10px; font-size: 20px; color: #d570f1; }
       
        nav { margin-top: 10px; }
        nav a { margin: 0 15px; text-decoration: none; color: #00e6e6ae; font-weight: bold; }
        nav a:hover { color: #ffffff; }
        .container { display: flex; justify-content: space-around; margin: 30px; flex-wrap: wrap; }
        .card { background: rgba(255,255,255,0.1); border-radius: 12px; padding: 25px; width: 45%; margin-bottom: 20px; box-shadow: 0 6px 12px rgba(0,0,0,0.3); }
        .profile-photo { text-align: center; margin-bottom: 15px; }
        .profile-photo img { width: 120px; height: 120px; border-radius: 100%; border: 8px solid #40008a; }
        table { border-collapse: collapse; width: 100%; margin-top: 15px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: center; }
        th { background: #444; color: #d9ec4c; }
        .logout { text-align: center; margin: 30px; }
        .logout a { background: #8b878b; border-style: groove; margin-top: 20px; color: #000000; font-weight: bold; text-decoration: none; }
        .logout a:hover { color: #ffffff; }
        .msg { margin: 15px; font-weight: bold; color: #c7dfda; text-align: center; }
        select, input[type="submit"], input[type="file"] { padding: 10px; margin: 10px 0; width: 100%; border-radius: 6px; border: none; }
        input[type="submit"] { background: #0072ff; color: #fff; font-weight: bold; cursor: pointer; }
    </style>
</head>
<body>
    <header>
        <!-- ✅ Logo inside University header -->
        <h2>
            <img src="University.jpeg" alt="Borcelle University Logo" class="logo">
            University of Borcelle
        </h2>
        <h3>Student of Borcelle</h3>
        <nav>
            <a href="student_dashboard.php">Dashboard</a>
            <a href="logout.php">Logout</a>
        </nav>
    </header>

    <?php if (!empty($msg)) echo "<div class='msg'>$msg</div>"; ?>

    <div class="container">
        <!-- ✅ Profile Card -->
        <div class="card">
            <div class="profile-photo">
                <?php if (!empty($user['photo'])) { ?>
                    <img src="<?php echo htmlspecialchars($user['photo']); ?>" alt="Profile Photo">
                <?php } else { ?>
                    <img src="images/avatar_placeholder.png" alt="No Photo">
                <?php } ?>
            </div>
            <p><strong>Username:</strong> <?php echo htmlspecialchars($username); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email'] ?? 'Not available'); ?></p>
            <p><strong>Department:</strong> <?php echo htmlspecialchars($user['department'] ?? 'Not available'); ?></p>

            <!-- ✅ Upload/Remove Photo -->
            <form method="POST" action="student_dashboard.php" enctype="multipart/form-data">
                <input type="file" name="photo" required>
                <input type="submit" name="upload_photo" value="Upload Photo">
            </form>
            <?php if (!empty($user['photo'])) { ?>
                <form method="POST" action="student_dashboard.php">
                    <input type="submit" name="remove_photo" value="Remove Photo">
                </form>
            <?php } ?>
        </div>

        <!-- ✅ Courses Card -->
        <div class="card">
            <h3 style="text-align:center; color: #d9ec4c;">Your Enrolled Courses</h3>
            <table>
                <tr>
                    <th>Course Name</th>
                    <th>Department</th>
                </tr>
                <?php
                if (isset($result) && $result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo "<tr>
                                <td>".htmlspecialchars($row['course_name'])."</td>
                                <td>".htmlspecialchars($row['department'])."</td>
                                </tr>";
                    }
                } else {
                    echo "<tr><td colspan='2'>No courses enrolled yet.</td></tr>";
                }
                ?>
                </table>
            <h3 style="text-align:center; color: #d9ec4c;">Enroll in a New Course</h3>
            <form method="POST" action="student_dashboard.php">
                <select name="course_id" required>
                    <option value="">Select a course</option>
                    <?php
                    if ($course_result->num_rows > 0) {
                        while($course = $course_result->fetch_assoc()) {
                            echo "<option value='".htmlspecialchars($course['id'])."'>".htmlspecialchars($course['course_name'])."</option>";
                        }
                    }
                    ?>
                </select>
                <input type="submit" name="enroll" value="Enroll">
                </form>
                </div>
                </div>
    <div class="logout">
        <a href="logout.php">Logout</a>
        </div>
        </body>
        </html>
