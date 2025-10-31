<?php
session_start();
$conn = new mysqli("localhost","root","","company_db");
if($conn->connect_error) die("DB Error: ".$conn->connect_error);

// Registration
if(isset($_POST['register'])){
    $fullname = $_POST['fullname'];
    $email = $_POST['email'];
    $password = md5($_POST['password']);
    $role = $_POST['role'];

    // ADMIN auto-approved, others pending
    $status = ($role == 'ADMIN') ? 'APPROVED' : 'PENDING';

    // Check if email already exists
    $check = $conn->query("SELECT * FROM users WHERE email='$email'");
    if($check->num_rows > 0){
        $msg = "❌ Email already registered!";
    } else {
        $conn->query("INSERT INTO users(fullname,email,password,role,status) VALUES('$fullname','$email','$password','$role','$status')");
        $msg = ($role == 'ADMIN') ? "✅ Admin registered successfully! You can login immediately." : "✅ Registration successful! Wait for admin approval.";
    }
}

// Login
if(isset($_POST['login'])){
    $email = $_POST['login_email'];
    $password = md5($_POST['login_password']);

    $res = $conn->query("SELECT * FROM users WHERE email='$email' AND password='$password'");
    if($res->num_rows > 0){
        $row = $res->fetch_assoc();
        if($row['role'] == 'ADMIN' || $row['status'] == 'APPROVED'){
            // ADMIN can login immediately, others only if approved
            $_SESSION['role'] = $row['role'];
            $_SESSION['fullname'] = $row['fullname'];
            $_SESSION['user_id'] = $row['id'];
            header("Location:dashboard.php"); exit;
        } else {
            $msg = "⏳ Your account is pending admin approval";
        }
    } else {
        $msg = "❌ Invalid credentials";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Company Login/Register</title>
<style>
body{font-family:Arial;background:#3498db;color:#fff;}
.container{background:rgba(0,0,0,0.7);width:400px;margin:50px auto;padding:30px;border-radius:15px;}
input,select,button{width:100%;padding:10px;margin:10px 0;border-radius:5px;border:none;}
button:hover{background:#0ff;color:#000;cursor:pointer;}
.link{color:#0ff;cursor:pointer;text-decoration:underline;text-align:center;display:block;}
p{margin:5px 0;}
</style>
</head>
<body>
<div class="container">
<h2>Company Portal</h2>
<?php if(isset($msg)) echo "<p>$msg</p>"; ?>

<!-- Login Form -->
<form method="POST">
<input type="email" name="login_email" placeholder="Email" required>
<input type="password" name="login_password" placeholder="Password" required>
<button name="login">Login</button>
</form>
<p class="link" onclick="toggleForms()">Register Here</p>

<!-- Registration Form -->
<form method="POST" id="registerForm" style="display:none;">
<input type="text" name="fullname" placeholder="Full Name" required>
<input type="email" name="email" placeholder="Email" required>
<input type="password" name="password" placeholder="Password" required>
<select name="role" required>
<option value="">Select Role</option>
<option value="ADMIN">ADMIN</option>
<option value="HR">HR</option>
<option value="MANAGER">MANAGER</option>
<option value="EMPLOYEE">EMPLOYEE</option>
</select>
<button name="register">Register</button>
</form>
<p class="link" onclick="toggleForms()">Back to Login</p>

<script>
function toggleForms(){
let loginForm=document.forms[0];
let registerForm=document.getElementById('registerForm');
loginForm.style.display=(loginForm.style.display==='none')?'block':'none';
registerForm.style.display=(registerForm.style.display==='none')?'block':'none';
}
</script>
</div>
</body>
</html>
