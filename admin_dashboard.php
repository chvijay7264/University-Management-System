<?php
session_start();
include('includes/db_connect.php');

// ✅ Ensure only admin can access
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    die("Access denied. Admins only.");
}

// ✅ Fetch counts
$studentCount = $conn->query("SELECT COUNT(*) AS total FROM students")->fetch_assoc()['total'];
$facultyCount = $conn->query("SELECT COUNT(*) AS total FROM faculty")->fetch_assoc()['total'];
$courseCount  = $conn->query("SELECT COUNT(*) AS total FROM courses")->fetch_assoc()['total'];
$enrollCount  = $conn->query("SELECT COUNT(*) AS total FROM enrollments")->fetch_assoc()['total'];

// ✅ Fetch recent activity
$recentStudents = $conn->query("SELECT name FROM students ORDER BY id DESC LIMIT 5");
$recentFaculty  = $conn->query("SELECT name FROM faculty ORDER BY id DESC LIMIT 5");
$recentCourses  = $conn->query("SELECT course_name FROM courses ORDER BY id DESC LIMIT 5");
$recentEnrolls  = $conn->query("SELECT students.name AS student, courses.course_name AS course
                                FROM enrollments
                                JOIN students ON enrollments.student_id = students.id
                                JOIN courses ON enrollments.course_id = courses.id
                                ORDER BY enrollments.id DESC LIMIT 5");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: linear-gradient(135deg, #755ce7, #928dab);
            color: #fff;
            margin: 0;
            padding: 0;
            text-align: center;
        }
        h2 {
            margin-top: 10px;
            font-size: 32px;
            color: grey;
            height: 100px;
            margin: 40px;
            background: linear-gradient(135deg,lightskyblue,lightgrey);
            padding: 10px;
            border-radius: 20px;
            box-shadow: 4px 0px 10px whitesmoke;
            display: flex;              /* flexbox for logo + text */
            align-items: center;        /* vertical alignment */
            justify-content: center;    /* keep centered */
            gap: 15px;                  /* space between logo and text */
        }
        .logo {
            height: 95px;       /* fits inside header height */
            width: 95px;
            border-radius: 50%; /* circular logo */
            object-fit: cover;
        }
        header {
            margin-left: 100px;
            background: grey;
            height:100px;
            width:1500px;
            padding: 5px;
            border-radius: 55px;
        }
        h3 {
            font-size: 25px;
        }
        nav {
           font-size: 15px;
        }
        nav a {
            margin: 0 15px;
            text-decoration: none;
            color: #f1f1f1;
            font-weight: bold;
            transition: color 0.3s;
        }
        nav a:hover {
            color: #7bf7f7;
        }
        .stats {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            margin: 30px;
        }
        .card {
            border-radius: 12px;
            padding: 25px;
            margin: 15px;
            width: 200px;
            background: linear-gradient(135deg, blue, violet );
            box-shadow: 0 6px 12px rgba(0,0,0,0.3);
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .card:hover {
            transform: translateY(-8px);
            box-shadow: 0 10px 18px rgba(0,0,0,0.5);
        }
        .card h3 {
            margin: 0;
            font-size: 28px;
            color: #fff;
        }
        .card p {
            font-size: 18px;
            margin-top: 10px;
            color: #f1f1f1;
        }
        .activity {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            margin: 30px;
        }
        .activity-box {
            border-radius: 12px;
            padding: 20px;
            margin: 15px;
            width: 250px;
            background: linear-gradient(135deg,gold,yellow);
            color: #2D3436;
            box-shadow: 15px 10px 10px rgba(0,0,0,0.5);
            transition: transform 0.3s;
        }
        .activity-box:hover {
            transform: translateY(-6px);
        }
        .activity-box h4 {
            margin-bottom: 12px;
            font-size: 20px;
            color: #fff;
        }
        .activity-box ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .activity-box li {
            margin: 6px 0;
            font-size: 16px;
        }
    </style>
</head>
<body>
    
    <!-- ✅ University header with logo at left corner -->
    <h2>
        <img src="University.jpeg" alt="Borcelle University Logo" class="logo">
        University of Borcelle
    </h2>

    <header>
        <h3>Admin dashboard</h3>
        <!-- ✅ Navigation -->
        <nav>
            <a href="student_list.php">Students</a> |
            <a href="faculty_list.php">Faculty</a> |
            <a href="course_list.php">Courses</a> |
            <a href="enrollment_list.php">Enrollments</a> |
            <a href="profile.php">My Profile</a> |
            <a href="logout.php">Logout</a>
        </nav>
    </header>

    <!-- ✅ Statistics Cards -->
    <div class="stats">
        <div class="card"><h3><?php echo $studentCount; ?></h3><p>Total Students</p></div>
        <div class="card"><h3><?php echo $facultyCount; ?></h3><p>Total Faculty</p></div>
        <div class="card"><h3><?php echo $courseCount; ?></h3><p>Total Courses</p></div>
        <div class="card"><h3><?php echo $enrollCount; ?></h3><p>Total Enrollments</p></div>
    </div>

    <!-- ✅ Recent Activity -->
    <div class="activity">
        <div class="activity-box">
            <h4>Recent Students</h4>
            <ul>
                <?php while($s = $recentStudents->fetch_assoc()) { echo "<li>".$s['name']."</li>"; } ?>
            </ul>
        </div>
        <div class="activity-box">
            <h4>Recent Faculty</h4>
            <ul>
                <?php while($f = $recentFaculty->fetch_assoc()) { echo "<li>".$f['name']."</li>"; } ?>
            </ul>
        </div>
        <div class="activity-box">
            <h4>Recent Courses</h4>
            <ul>
                <?php while($c = $recentCourses->fetch_assoc()) { echo "<li>".$c['course_name']."</li>"; } ?>
            </ul>
        </div>
        <div class="activity-box">
            <h4>Recent Enrollments</h4>
            <ul>
                <?php while($e = $recentEnrolls->fetch_assoc()) { echo "<li>".$e['student']." → ".$e['course']."</li>"; } ?>
            </ul>
        </div>
    </div>
</body>
</html>
