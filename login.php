<?php
session_start();
include('includes/db_connect.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();

        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['role'] = $row['role'];

            if ($row['role'] == 'admin') {
                header("Location: admin_dashboard.php");
            } elseif ($row['role'] == 'faculty') {
                header("Location: faculty_dashboard.php");
            } elseif ($row['role'] == 'student') {
                header("Location: student_dashboard.php");
            }
            exit();
        } else {
            $error = "Invalid username or password!";
        }
    } else {
        $error = "Invalid username or password!";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <style>
        body { font-family:'Segoe UI',Arial,sans-serif; background: #0f0f1a; color: #fff; margin:0; }
        .container { display:flex; height:100vh; }
        .left-panel { flex:1; display:flex; flex-direction:column; align-items:center; justify-content:center; background: #1f1c2c; }
        .left-panel h1 { font-size:36px; font-weight:bold; margin-top:20px; }
        .right-panel { flex:1; display:flex; align-items:center; justify-content:center; background:#121212; }
        .login-box { width:350px; background:rgba(255,255,255,0.05); padding:30px; border-radius:12px; box-shadow:0 6px 12px rgba(0,0,0,0.5); text-align:center; }
        .login-box h2 { margin-bottom:20px; color:#ffcc00; }
        input { width:90%; padding:12px; margin:10px 0; border-radius:6px; border:none; }
        input[type="submit"] { background:linear-gradient(135deg,#6a11cb,#2575fc); color:#fff; font-weight:bold; cursor:pointer; transition:transform 0.2s; }
        input[type="submit"]:hover { transform:scale(1.05); }
        .error { color:#ff4444; font-weight:bold; }
        .links { margin-top:15px; font-size:14px; }
        .links a { color:#00c6ff; text-decoration:none; margin:0 5px; }
       h1{
        margin-top: 50px;
       } h2{
            margin-top:30px;
            font-size: 40px;    
            text-align: center;
        }
         h3{
            margin-top:30px;
            font-size: 40px;  
            margin-left: 70px;  
            text-align: center;
        }
        .logo{align-items: center; height: 300px;   /* larger logo */
           width: 300px;
           border-radius: 50%; /* circular logo */
           object-fit: cover;
           margin-left: 20px;
           margin-bottom: auto;}
                          /* space between logo and text */
    
    </style>
</head>
<body>
    <div class="container">
        <div class="left-panel">
             <h1> <img src="University.jpeg" alt="Borcelle University Logo" class="logo"></h1>
            <h3>Welcome Back ...!</h3>

           
        </div>
        <div class="right-panel">
            <div class="login-box">
                <h2>Login</h2>
                <?php if (!empty($error)) echo "<p class='error'>$error</p>"; ?>
                <form method="POST" action="">
                    <input type="text" name="username" placeholder="Username" required><br>
                    <input type="password" name="password" placeholder="Password" required><br>
                    <label style="float:left; font-size:14px;">
                        <input type="checkbox" name="remember"> Remember me
                    </label><br><br>
                    <input type="submit" value="Login">
                </form>
                <div class="links">
                    <a href="#">Forgot password?</a><br>
                    Don’t have an account? <a href="user_register.php">Signup</a><br>
                    <a href="#">Terms & Conditions</a> | <a href="#">Support</a> | <a href="#">Customer Care</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
