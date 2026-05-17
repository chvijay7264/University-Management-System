<?php
// Hide warnings but still show fatal errors
error_reporting(E_ALL & ~E_WARNING);

include('includes/db_connect.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $role     = $_POST['role'] ?? '';

    if (!empty($username) && !empty($password) && !empty($role)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        if ($role === 'student') {
            $name  = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');

            if (!empty($name) && !empty($email)) {
                // Check duplicate email
                $stmt = $conn->prepare("SELECT id FROM students WHERE email = ?");
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows > 0) {
                    echo "<p style='color:red;'>Email already exists!</p>";
                } else {
                    $stmt = $conn->prepare("INSERT INTO students (name, email) VALUES (?, ?)");
                    $stmt->bind_param("ss", $name, $email);
                    $stmt->execute();
                    $student_id = $conn->insert_id;

                    $stmt = $conn->prepare("INSERT INTO users (username, password, role, student_id) VALUES (?, ?, ?, ?)");
                    $stmt->bind_param("sssi", $username, $hashedPassword, $role, $student_id);
                    $stmt->execute();

                    echo "<p style='color:green;'>Student registered successfully!</p>";
                }
            } else {
                echo "<p style='color:red;'>Name and Email are required for student registration!</p>";
            }

        } elseif ($role === 'faculty') {
            $name  = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');

            if (!empty($name) && !empty($email)) {
                $stmt = $conn->prepare("SELECT id FROM faculty WHERE email = ?");
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows > 0) {
                    echo "<p style='color:red;'>Email already exists!</p>";
                } else {
                    $stmt = $conn->prepare("INSERT INTO faculty (name, email) VALUES (?, ?)");
                    $stmt->bind_param("ss", $name, $email);
                    $stmt->execute();
                    $faculty_id = $conn->insert_id;  $stmt = $conn->prepare("INSERT INTO users (username, password, role, student_id) VALUES (?, ?, ?, ?)");
                    $stmt->bind_param("sssi", $username, $hashedPassword, $role, $student_id);
                    $stmt->execute();

                    echo "<p style='color:green;'>Student registered successfully!</p>";
                }
            } else {
                echo "<p style='color:red;'>Name and Email are required for student registration!</p>";
            }

        } elseif ($role === 'faculty') {
            $name  = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? ''); if (!empty($name) && !empty($email)) {
                $stmt = $conn->prepare("SELECT id FROM faculty WHERE email = ?");
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows > 0) {
                    echo "<p style='color:red;'>Email already exists!</p>";
                } else {
                    $stmt = $conn->prepare("INSERT INTO faculty (name, email) VALUES (?, ?)");
                    $stmt->bind_param("ss", $name, $email);
                    $stmt->execute();
                    $faculty_id = $conn->insert_id;

                    $stmt = $conn->prepare("INSERT INTO users (username, password, role, faculty_id) VALUES (?, ?, ?, ?)");
                    $stmt->bind_param("sssi", $username, $hashedPassword, $role, $faculty_id); $stmt->execute();

                    echo "<p style='color:green;'>Faculty registered successfully!</p>";
                }
            } else {
                echo "<p style='color:red;'>Name and Email are required for faculty registration!</p>";
            }

        } else {
            $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $username, $hashedPassword, $role);
            $stmt->execute();

            echo "<p style='color:green;'>Admin registered successfully!</p>";
        }} else {
        echo "<p style='color:red;'>All fields must be filled!</p>";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>User Registration</title>
    <style>
       body {
           font-family: 'Segoe UI', Arial, sans-serif;
           background: linear-gradient(135deg, #755ce7, #333232);
           color: #fff; margin: 0; padding: 0;
       }
       header {
           background: rgba(255,255,255,0.1);
           padding: 5px;
       }
       p{
        color:aquamarine;
       }
        a{
        color:lightslategrey;
       }
       a:hover{
        color:aqua;
       }
       .logo-title {
           display: flex;
           align-items: center;       /* aligns logo with text vertically */
           justify-content: flex-start; /* pushes logo+text to the left corner */
           gap: 10px;                 /* space between logo and text */
       }
       .logo {
           align-items: center;
           height: 170px;   /* larger logo */
           width: 170px;
           border-radius: 50%; /* circular logo */
           object-fit: cover;
           margin-left: 450px;
           margin-bottom: auto;
       }
       .title {  
           font-size: 45px; /* bigger college name */
           color: #ce91dd;
           margin-top: auto;
          margin-left: 15px;
           
           
       }
       .subtitle {
           font-size: 26px;
           color: #d570f1;
           margin-top: 12px;
           text-align: center; /* keeps subtitle centered */
       }
       form {
           margin:50px auto; text-align: center;
           background: linear-gradient(135deg, #755ce7, #333232);
           padding: 20px; width: 500px; border-radius: 12px;
       }
      
       input, select {
           margin: 8px 0; padding: 8px;
           border-radius: 10px; border: none;
           text-align: center;
       }
       input[type="submit"] {
           width: 150px; height: 35px;
           background:#6a11cb; color:#fff; font-weight:bold;
           cursor:pointer;
       }
       .error { background:#ff4444; padding:10px; border-radius:8px; margin-bottom:15px; font-weight:bold; }
       .success { background:#44ff44; padding:10px; border-radius:8px; margin-bottom:15px; font-weight:bold; }
    </style>
</head>
<body>
    <header>
        <div class="logo-title">
            <img src="University.jpeg" alt="Borcelle University Logo" class="logo">
            <h2 class="title">University of Borcelle</h2>
        </div>
        <h3 class="subtitle">Register User</h3>
    </header>

    <form method="POST" action="">
        <!-- Error/Success banners -->
        <?php if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($error)) echo "<div class='error'>$error</div>"; ?>
        <?php if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($success)) echo "<div class='success'>$success</div>"; ?>

        <label style="color:yellow;">Username:</label><br>
        <input type="text" name="username" placeholder="Eg: Vijay" required style="width:300px;"><br><br>

        <label style="color:yellow;">Password:</label><br>
        <input type="password" name="password" placeholder="eg: A1234" required style="width:300px;"><br><br>

        <label style="color:yellow;">Role:</label><br>
        <select name="role" id="role" style="width:200px;" required onchange="toggleFields()">
            <option value="faculty">Faculty</option>
            <option value="student">Student</option>
        </select><br><br>

        <div id="extraFields">
            <label style="color:yellow;">Name:</label><br>
            <input type="text" name="name" placeholder="Name" style="width:300px;"><br><br>

            <label style="color:yellow;">Email:</label><br>
            <input type="email" name="email" placeholder="Email" style="width:300px;"><br><br>
        </div>

        <input type="submit" value="Register">
        <p>Already registered?<a href="login.php"> login</a></p>
    </form>

    <script>
        function toggleFields() {
            const role = document.getElementById('role').value;
            const extraFields = document.getElementById('extraFields');
            if (role === 'admin') {
                extraFields.style.display = 'none';
            } else {
                extraFields.style.display = 'block';
            }
        }
        toggleFields();
    </script>
</body>
</html>
