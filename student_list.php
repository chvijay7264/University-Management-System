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
    $stmt = $conn->prepare("DELETE FROM students WHERE id = ?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    header("Location: student_list.php");
    exit();
}

// Search & filter
$search = $_GET['search'] ?? '';
$department = $_GET['department'] ?? '';

$sql = "SELECT id, name, email, department, photo FROM students WHERE 1=1";
if (!empty($search)) {
    $sql .= " AND (name LIKE '%$search%' OR email LIKE '%$search%')";
}
if (!empty($department)) {
    $sql .= " AND department = '$department'";
}
$sql .= " ORDER BY id ASC";
$result = $conn->query($sql);

$deptResult = $conn->query("SELECT DISTINCT department FROM students");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Students</title>
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: linear-gradient(135deg, #755ce7, #211f1f);
            color: #fff; margin: 0; padding: 0;
        }
        header {
            background:linear-gradient(135deg, rgba(255,255,255,0.1),#2575fc);
            padding: 15px;
            text-align: center;
            border-radius:5px;
        }
        header h2 {
            margin: 10px;
            font-size: 28px;
            color: #d570f1;
            padding: 5px;
            display: flex;              /* flexbox for logo + text */
            align-items: center;        /* vertical alignment */
            justify-content: center;    /* keep centered */
            gap: 15px;  
            margin-right: 100PX;                /* space between logo and text */
        }
        .logo {
            height: 100px;
            width: 100px;
            border-radius: 50%; /* circular logo */
            object-fit: cover;
            margin-left: 20PX;
        }
        header h3 {
            margin: 5px;
            font-size: 22px;
            color: #fff;
        }
        nav a {
            margin: 0 15px;
            text-decoration: none;
            color: #00e6e6ae;
            font-weight: bold;
            padding-bottom: 5px;
        }
        nav a:hover { color: #ffffff; }
        table {
            border-collapse:collapse;
            width:90%;
            margin:20px auto;
            background:rgba(255,255,255,0.1);
            border-radius:8px;
        }
        th,td {
            border:1px solid #333;
            padding:10px;
            text-align:center;
        }
        th {
            background:#444;
            color:#ffcc00;
        }
        img.profile-thumb {
            width:50px;
            height:50px;
            border-radius:50%;
            border: 8px solid #40008a;
        }
        .delete-link { color:#ff4444; font-weight:bold; text-decoration:none; }
        .edit-link { color:#00e6e6; font-weight:bold; text-decoration:none; }
        .search-filter {
            text-align:center;
            margin:20px;
        }
        .search-filter input,.search-filter select {
            padding:8px;
            border-radius:6px;
            border:none;
            margin:5px;
        }
        .search-filter input[type=submit] {
            background:linear-gradient(135deg,#00c6ff,#0072ff);
            color: #fff;
            font-weight:bold;
            cursor:pointer;
        }
    </style>
</head>
<body>
    <header>
        <!-- ✅ Logo inside University header -->
        <h2>
            <img src="University.jpeg" alt="Borcelle University Logo" class="logo">
            University of Borcelle
        </h2>
        <h3>Students of Borcelle</h3>
        <nav>
            <a href="admin_dashboard.php">Dashboard</a>
            <a href="student_list.php">Manage Students</a>
            <a href="logout.php">Logout</a>
        </nav>
    </header>

    <div class="search-filter">
        <form method="GET" action="">
            <input type="text" name="search" placeholder="Search by name/email" value="<?php echo htmlspecialchars($search); ?>">
            <select name="department">
                <option value="">All Departments</option>
                <?php while($d=$deptResult->fetch_assoc()){ 
                    $sel=($department==$d['department'])?"selected":""; 
                    echo "<option value='".htmlspecialchars($d['department'])."' $sel>".htmlspecialchars($d['department'])."</option>"; 
                } ?>
            </select>
            <input type="submit" value="Filter">
        </form>
    </div>

    <table>
        <tr><th>ID</th><th>Photo</th><th>Name</th><th>Email</th><th>Department</th><th>Action</th></tr>
        <?php if($result->num_rows>0){ while($row=$result->fetch_assoc()){ ?>
            <tr>
                <td><?php echo $row['id']; ?></td>
                <td>
                    <?php 
                    if (!empty($row['photo'])) {
                        echo "<img class='profile-thumb' src='".htmlspecialchars($row['photo'])."' alt='Photo'>";
                    } else {
                        echo "<img class='profile-thumb' src='images/avatar_placeholder.png' alt='No Photo'>";
                    }
                    ?>
                </td>
                <td><?php echo htmlspecialchars($row['name']); ?></td>
                <td><?php echo htmlspecialchars($row['email']); ?></td>
                <td><?php echo htmlspecialchars($row['department']); ?></td>
                <td>
                    <a class="edit-link" href="edit_student.php?id=<?php echo $row['id']; ?>">Edit</a> | 
                    <a class="delete-link" href="student_list.php?delete_id=<?php echo $row['id']; ?>" onclick="return confirm('Delete this student?');">Delete</a>
                </td>
            </tr>
        <?php }} else { echo "<tr><td colspan='6'>No students found</td></tr>"; } ?>
    </table>
</body>
</html>
