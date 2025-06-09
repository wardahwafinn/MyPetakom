<?php
session_start();

// If already logged in, redirect to appropriate dashboard
if (isset($_SESSION['userID']) && isset($_SESSION['userType'])) {
    if ($_SESSION['userType'] == 'student') {
        header("Location: ../student_dash.php");
        exit();
    } else if ($_SESSION['userType'] == 'admin' || $_SESSION['userType'] == 'advisor') {
        header("Location: ../admin.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta name="description" content="login form for MyPetakom">
    <meta name="author" content="UMI MAISARAH BINTI MOHD AFENDI">
    <title>Login</title>
    <link rel="stylesheet" type="text/css" href="style/login.css">
</head>
<body>
    <div class="fk_images">
        <div class="login">
            <img src="../images/petakom.png" alt="Logo" class="logo">
            
            <div class="form">
                <form action="loginAction.php" method="POST">
                    <input type="text" id="userID" name="userID" placeholder="ID" required>
                    
                    <input type="password" id="password" name="password" placeholder="PASSWORD" required style="width: 100%;
                        padding: 12px 20px;
                        margin: 8px 0;
                        display: inline-block;
                        border-radius: 11px;
                        border: 3px solid #42110C;
                        background: rgba(228, 228, 228, 0.38);
                        box-sizing: border-box;
                        text-align: left;">
                    
                    <select id="userType" name="userType" required>
                        <option value="">Select User Type</option>
                        <option value="student">Student</option>
                        <option value="admin">Administrator</option>
                        <option value="advisor">Event Advisor</option>
                    </select>
                    
                    <label>
                        <input type="checkbox" name="remember"> Remember me
                    </label>
                    
                    <input type="submit" value="Log in">
                </form>
            </div>
        </div>
    </div>
</body>
</html>