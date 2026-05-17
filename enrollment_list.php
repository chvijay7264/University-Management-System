<?php
session_start();
include('includes/db_connect.php');

// Ensure only admin can access
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    die("Access denied. Admins only.");
}

// Handle delete
if (isset($_GET['delete_id'])) {
    $delete_id = (int)$_GET['delete_id'];
    $stmt = $conn->prepare("DELETE FROM enrollments WHERE id = ?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    header("Location: enrollment_list.php"); // ✅ Correct file name
    exit();
}

// Search & filter
$search = $_GET['search'] ?? '';

$sql = "SELECT e.id, s.name AS student_name, c.course_name, c.department 
        FROM enrollments e
        JOIN students s ON e.student_id = s.id
        JOIN courses c ON e.course_id = c.id
        WHERE 1=1";
if (!empty($search)) {
    $sql .= " AND (s.name LIKE '%$search%' OR c.course_name LIKE '%$search%')";
}
$sql .= " ORDER BY e.id ASC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Enrollments</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background: linear-gradient(135deg, #755ce7, #211f1f); color: #fff; margin: 0; padding: 0; }
        header { background:rgba(255,255,255,0.1); padding:15px; text-align:center; }
        header h2 {
            margin:20px;
            font-size:28px;
            color: #ff82fd;
            display: flex;              /* flexbox for logo + text */
            align-items: center;        /* vertical alignment */
            justify-content: center;    /* keep centered */
            gap: 15px;       
            margin-right: 120px;           /* space between logo and text */
        }
        .logo {
            height: 100px;
            width: 100px;
            border-radius: 50%; /* circular logo */
            object-fit: cover;
            margin-left: 30px;
        }
        header h3 { margin:10px; font-size: 20px; color: #2c94f6; }
        nav a { margin:0 15px; text-decoration:none; color: #00e6e6; font-weight:bold; }
        nav a:hover { color: #fff; }
        table { border-collapse:collapse; width:90%; margin:20px auto; background:rgba(255,255,255,0.1); border-radius:8px; }
        th,td { border:1px solid #333; padding:10px; text-align:center; }
        th { background:#444; color:#ffcc00; }
        .delete-link { color:#ff4444; font-weight:bold; text-decoration:none; }
        .edit-link { color:#00e6e6; font-weight:bold; text-decoration:none; }
        .search-filter { text-align:center; margin:20px; }
        .search-filter input { padding:8px; border-radius:6px; border:none; margin:5px; }
        .search-filter input[type=submit] { background:linear-gradient(135deg,#00c6ff,#0072ff); color:#fff; font-weight:bold; cursor:pointer; }
    </style>
</head>
<body>
    <header>
        <!-- ✅ Logo inside University header -->
        <h2>
            <img src="University.jpeg" alt="Borcelle University Logo" class="logo">
            University of Borcelle
        </h2>
        <h3>Enrollments</h3>
        <nav>
            <a href="admin_dashboard.php">Dashboard</a>
            <a href="enrollment_list.php">Manage Enrollments</a> <!-- ✅ Correct file name -->
            <a href="logout.php">Logout</a>
        </nav>
    </header>

    <div class="search-filter">
        <form method="GET" action="">
            <input type="text" name="search" placeholder="Search by student/course" value="<?php echo htmlspecialchars($search); ?>">
            <input type="submit" value="Filter">
        </form>
    </div>

    <table>
        <tr><th>ID</th><th>Student</th><th>Course</th><th>Department</th><th>Action</th></tr>
        <?php if($result->num_rows>0){ while($row=$result->fetch_assoc()){ ?>
            <tr>
                <td><?php echo $row['id']; ?></td>
                <td><?php echo htmlspecialchars($row['student_name']); ?></td>
                <td><?php echo htmlspecialchars($row['course_name']); ?></td>
                <td><?php echo htmlspecialchars($row['department']); ?></td>
                <td>
                    <a class="edit-link" href="edit_enrollment.php?id=<?php echo $row['id']; ?>">Edit</a> | 
                    <a class="delete-link" href="enrollment_list.php?delete_id=<?php echo $row['id']; ?>" onclick="return confirm('Delete this enrollment?');">Delete</a>
                </td>
            </tr>
        <?php }} else { echo "<tr><td colspan='5'>No enrollments found</td></tr>"; } ?>
    </table>
</body>
</html>
