<?php 
$servername = "localhost";
$username = "root"; // update if needed
$password = "";     // update if needed
$dbname = "mypetakom";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
 ?>
<!DOCTYPE html>
<html>
<head>
    <title>Add New User</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f9f9f9;
            padding: 30px;
        }
        .form-container {
            background: #fff;
            padding: 25px;
            border-radius: 12px;
            width: 450px;
            margin: auto;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        input, select {
            width: 100%;
            padding: 10px;
            margin: 8px 0 16px;
            border: 1px solid #ccc;
            border-radius: 8px;
        }
        button {
            background-color: #8B0000;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            width: 100%;
        }
        a {
            display: inline-block;
            margin-top: 16px;
            text-align: center;
            color: #555;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Add New User</h2>
        <form method="post" action="insert_user.php">
            <label>User Type:</label>
            <select name="user_type" required>
                <option value="student">Student</option>
                <option value="staff">Staff</option>
            </select>

            <label>ID:</label>
            <input type="text" name="id" required>

            <label>Name:</label>
            <input type="text" name="name" required>

            <label>Email:</label>
            <input type="email" name="email" required>

            <label>Password:</label>
            <input type="password" name="password" required>

            <label>Card (STUDENT) or Role (ADMIN/STAFF):</label>
            <input type="text" name="card_or_role" required>

            <label>Phone Number (Only for Student):</label>
            <input type="text" name="phone">

            <button type="submit">Add User</button>
        </form>

        <a href="admin_manage_profile.php">← Back to User List</a>
    </div>
</body>
</html>
