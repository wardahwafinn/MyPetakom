<?php 
    session_start();
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Login</title>
        <link rel="stylesheet" type="text/css" href="style/project.css">
    </head>
    <body>
        <div class="fk_images"> 
            <div class="login">
                    <img src="images/logo.png" alt="Logo" class="logo">

            <div class="form">
                <form action="loginAction.php" method="POST">
                    <input type="text" id="username" name="username" placeholder="USERNAME">

                    <input type="password" id="password" name="password" placeholder="PASSWORD" style="width: 100%;
                        padding: 12px 20px;
                        margin: 8px 0;
                        display: inline-block;
                        border-radius: 11px;
                        border: 3px solid #42110C;
                        background: rgba(228, 228, 228, 0.38);
                        box-sizing: border-box;
                        text-align: left;">

                    <select id="userType" name="userType">
                    <option value="student">Student</option>
                    <option value="admin">Administrator</option>
                    <option value="advisor">Event Advisor</option>
                    </select>

                    <label>
                    <input type="checkbox" checked="checked" name="remember"> Remember me
                    </label>
        
                    <input type="submit" value="Log in">
                        

                </form>
            </div>
            </div>
        </div>    
    </body>
</html>