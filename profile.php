<?php
session_start();
include('includes/db_connect.php');

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

if ($role == 'student') {
    $table = 'students';
} elseif ($role == 'faculty') {
    $table = 'faculty';
} else {
    $table = 'users'; // admins stored in users
}

// ✅ Fetch user details
$sql = "SELECT * FROM $table WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    die("User not found.");
}

// ✅ Handle profile update
if (isset($_POST['update_profile'])) {
    $name = $_POST['name'] ?? ($user['name'] ?? $user['username']);
    $email = $_POST['email'] ?? ($user['email'] ?? null);
    $department = $_POST['department'] ?? ($user['department'] ?? null);

    // Handle photo upload
    $photo = $user['photo'] ?? null;
    if (!empty($_FILES['photo']['name'])) {
        $targetDir = "uploads/";
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        $photo = $targetDir . time() . "_" . basename($_FILES['photo']['name']);
        move_uploaded_file($_FILES['photo']['tmp_name'], $photo);
    }

    if ($role == 'admin') {
        $update_sql = "UPDATE users SET username=?, email=?, department=?, photo=? WHERE id=?";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("ssssi", $name, $email, $department, $photo, $user_id);
    } else {
        $update_sql = "UPDATE $table SET name=?, email=?, department=?, photo=? WHERE id=?";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("ssssi", $name, $email, $department, $photo, $user_id);
    }
    $stmt->execute();
    header("Location: profile.php");
    exit();
}

// ✅ Handle photo removal
if (isset($_POST['remove_photo'])) {
    if ($role == 'admin') {
        $update_sql = "UPDATE users SET photo=NULL WHERE id=?";
    } else {
        $update_sql = "UPDATE $table SET photo=NULL WHERE id=?";
    }
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    header("Location: profile.php");
    exit();
}

// ✅ Handle password change
if (isset($_POST['change_password'])) {
    $current = $_POST['current_password'] ?? null;
    $new     = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];

    $stmt = $conn->prepare("SELECT password FROM $table WHERE id=?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();

    if ($role == 'admin' || password_verify($current, $row['password'])) {
        if ($new === $confirm) {
            $hashed = password_hash($new, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE $table SET password=? WHERE id=?");
            $stmt->bind_param("si", $hashed, $user_id);
            $stmt->execute();
            $msg = "✅ Password updated successfully!";
        } else {
            $msg = "❌ New passwords do not match.";
        }
    } else {
        $msg = "❌ Current password is incorrect.";
    }
}

// ✅ Fetch enrolled courses if student
$enrolledCourses = [];
if ($table == 'students') {
    $course_sql = "SELECT courses.course_name 
                   FROM enrollments 
                   JOIN courses ON enrollments.course_id = courses.id 
                   WHERE enrollments.student_id = ?";
    $stmt = $conn->prepare($course_sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $course_result = $stmt->get_result();
    while ($c = $course_result->fetch_assoc()) {
        $enrolledCourses[] = $c['course_name'];
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>My Profile</title>
    <style>
        body {font-family: 'Segoe UI', Arial, sans-serif; background: linear-gradient(135deg, #755ce7, #211f1f); color: #fff; margin: 0; padding: 0;text-align: center; }
        h2 { margin-left:750px;height: 60px;width: 200px; font-size: 32px;color: cadetblue;border:30px;background-color:floralwhite;box-shadow:5px 0px 5px white;border-radius:30px; }
        nav { margin: 20px; background: rgba(255,255,255,0.1); padding: 10px; border-radius: 8px; }
        nav a { margin: 0 15px; text-decoration: none; color: #00b3ff; font-weight: bold; }
        nav a:hover { color: #00e6e6; }
        .profile-box { margin: 40px auto; width: 500px; background: rgba(255,255,255,0.1); padding: 25px; border-radius: 12px; text-align: left; }
        .profile-box label { display: block; margin: 10px 0 5px; color: #ffcc00; }
        .profile-box input { width: 100%; padding: 10px; border-radius: 6px; border: none; margin-bottom: 15px; }
        .profile-box input[type="submit"] { background: linear-gradient(135deg, #00c6ff, #0072ff); color: #fff; font-weight: bold; cursor: pointer; }
        .profile-photo { text-align: center; margin-bottom: 20px; }
        .profile-photo img { width: 120px; height: 120px; border-radius: 50%; border: 3px solid #ffffff; }
        .msg { margin: 15px; font-weight: bold; color: #ffcc00; }
    </style>
</head>
<body>
    <h2>My Profile</h2>
    <nav>
        <a href="admin_dashboard.php">Dashboard</a> |
        <a href="logout.php">Logout</a>
    </nav>

    <?php if (isset($msg)) echo "<div class='msg'>$msg</div>"; ?>

    <!-- ✅ Profile Card with Photo -->
    <div class="profile-box">
        <div class="profile-photo">
            <?php if (!empty($user['photo'])) { ?>
                <img src="<?php echo htmlspecialchars($user['photo']); ?>" alt="Profile Photo">
            <?php } else { ?>
                <img src="images/avatar_placeholder.png" alt="No Photo">
            <?php } ?>
        </div>

        <form method="POST" action="profile.php" enctype="multipart/form-data">
            <label>Name/Username:</label>
            <input type="text" name="name" value="<?php echo htmlspecialchars($user['name'] ?? $user['username']); ?>" required>

            <label>Email:</label>
            <input type="email" name="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>">

            <label>Department:</label>
            <input type="text" name="department" value="<?php echo htmlspecialchars($user['department'] ?? ''); ?>">

            <label>Profile Photo:</label>
            <input type="file" name="photo">

            <input type="submit" name="update_profile" value="Update Profile">
        </form>

        <!-- ✅ Remove Photo Button -->
        <?php if (!empty($user['photo'])) { ?>
            <form method="POST" action="profile.php" style="margin-top:10px;">
                <input type="submit" name="remove_photo" value="Remove Photo">
            </form>
        <?php } ?>
    </div>

    <!-- ✅ Password Change Form -->
    <div class="profile-box">
        <form method="POST" action="profile.php">
            <?php if ($role != 'admin') { ?>
                <label>Current Password:</label>
                <input type="password" name="current_password" required>
            <?php } ?>

            <label>New Password:</label>
            <input type="password" name="new_password" required>

            <label>Confirm New Password:</label>
            <input type="password" name="confirm_password" required>

            <input type="submit" name="change_password" value="Change Password">
        </form>
    </div>

    <!-- ✅ Show Enrolled Courses for Students -->
    <?php if ($role == 'student') { ?>
        <div class="profile-box">
            <h3>Enrolled Courses</h3>
            <ul>
                <?php if (!empty($enrolledCourses)) {
                    foreach ($enrolledCourses as $course) {
                        echo "<li>" . htmlspecialchars($course) . "</li>";
                    }
                } else {
                    echo "<li>No courses enrolled yet.</li>";
                } ?>
                </ul>
                </div>
    <?php } ?>
    </body>
    </html>