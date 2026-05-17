<?php
session_start();
include('includes/db_connect.php');

// Ensure only admin can access
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    die("Access denied. Admins only.");
}

// Determine which type of users to show
$type = isset($_GET['type']) ? $_GET['type'] : 'students';
$table = ($type == 'faculty') ? 'faculty' : 'students';

// Handle delete request securely
if (isset($_GET['delete_id'])) {
    $delete_id = (int)$_GET['delete_id'];
    $stmt = $conn->prepare("DELETE FROM $table WHERE id = ?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    header("Location: manage_users.php?type=$type");
    exit();
}

// Handle search & filter
$search = isset($_GET['search']) ? $_GET['search'] : '';
$department = isset($_GET['department']) ? $_GET['department'] : '';

$sql = "SELECT id, name, email, department, photo FROM $table WHERE 1=1";

if (!empty($search)) {
    $sql .= " AND (name LIKE '%$search%' OR email LIKE '%$search%')";
}
if (!empty($department)) {
    $sql .= " AND department = '$department'";
}

$sql .= " ORDER BY id ASC";
$result = $conn->query($sql);

// Fetch distinct departments for filter dropdown
$deptResult = $conn->query("SELECT DISTINCT department FROM $table");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage <?php echo ucfirst($type); ?></title>
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: linear-gradient(135deg, #1f1c2c, #928dab);
            color: #fff;
            margin: 0;
            padding: 0;
        }
        header {
            background: rgba(255,255,255,0.1);
            padding: 15px;
            text-align: center;
        }
        header h2 {
            margin: 0;
            font-size: 28px;
            color: #ffcc00;
        }
        nav {
            margin-top: 10px;
        }
        nav a {
            margin: 0 15px;
            text-decoration: none;
            color: #00e6e6;
            font-weight: bold;
        }
        nav a:hover { color: #ffcc00; }
        .search-filter {
            margin: 20px;
            text-align: center;
        }
        .search-filter input, .search-filter select {
            padding: 8px;
            border-radius: 6px;
            border: none;
            margin: 5px;
        }
        .search-filter input[type="submit"] {
            background: linear-gradient(135deg, #00c6ff, #0072ff);
            color: #fff;
            font-weight: bold;
            cursor: pointer;
        }
        table {
            border-collapse: collapse;
            width: 90%;
            margin: 20px auto;
            background: rgba(255,255,255,0.1);
            border-radius: 8px;
            overflow: hidden;
        }
        th, td {
            border: 1px solid #333;
            padding: 10px;
            text-align: center;
        }
        th {
            background-color: #444;
            color: #ffcc00;
        }
        img.profile-thumb {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            border: 2px solid #ffcc00;
        }
        .delete-link { color: #ff4444; text-decoration: none; font-weight: bold; }
        .edit-link { color: #00e6e6; text-decoration: none; font-weight: bold; }
    </style>
</head>
<body>
    <header>
        <h2>Manage <?php echo ucfirst($type); ?></h2>
        <nav>
            <a href="admin_dashboard.php">Dashboard</a>
            <a href="manage_users.php?type=students">Manage Students</a>
            <a href="manage_users.php?type=faculty">Manage Faculty</a>
            <a href="manage_courses.php">Manage Courses</a>
            <a href="manage_enrollments.php">Manage Enrollments</a>
            <a href="logout.php">Logout</a>
        </nav>
    </header>

    <!-- ✅ Search & Filter -->
    <div class="search-filter">
        <form method="GET" action="">
            <input type="hidden" name="type" value="<?php echo $type; ?>">
            <input type="text" name="search" placeholder="Search by name/email" value="<?php echo htmlspecialchars($search); ?>">
            <select name="department">
                <option value="">All Departments</option>
                <?php while($d = $deptResult->fetch_assoc()) {
                    $selected = ($department == $d['department']) ? "selected" : "";
                    echo "<option value='".htmlspecialchars($d['department'])."' $selected>".htmlspecialchars($d['department'])."</option>";
                } ?>
            </select>
            <input type="submit" value="Filter">
        </form>
    </div>

    <!-- ✅ User Table -->
    <table>
        <tr>
            <th>ID</th><th>Photo</th><th>Name</th><th>Email</th><th>Department</th><th>Action</th>
        </tr>
        <?php
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                echo "<tr>
                        <td>".$row['id']."</td>
                        <td>";
                if (!empty($row['photo'])) {
                    echo "<img class='profile-thumb' src='".htmlspecialchars($row['photo'])."' alt='Photo'>";
                } else {
                    echo "<img class='profile-thumb' src='uploads/default.png' alt='Default'>";
                }
                echo "</td>
                        <td>".htmlspecialchars($row['name'])."</td>
                        <td>".htmlspecialchars($row['email'])."</td>
                        <td>".htmlspecialchars($row['department'])."</td>
                        <td>
                            <a class='edit-link' href='profile.php?id=".$row['id']."&type=$type'>Edit</a> | 
                            <a class='delete-link' href='manage_users.php?type=$type&delete_id=".$row['id']."' onclick=\"return confirm('Are you sure you want to delete this user?');\">Delete</a>
                        </td>
                      </tr>";
            }
        } else {
            echo "<tr><td colspan='6'>No users found</td></tr>";
        }
        ?>
    </table>
</body>
</html>
