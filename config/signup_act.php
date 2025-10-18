<?php
include 'db_connect.php';
$mobile = trim($_POST['mobile']);
$password = trim($_POST['password']);
if(empty($mobile) || empty($password) ) {
    ?>
    <script>alert('Please fill in all required fields.');
    window.location.href = '../pages/signup.php';
    </script>
    <?php
}else{
    $checkQuery = "SELECT * FROM users WHERE mobile = '$mobile'";
    $checkResult = mysqli_query($conn, $checkQuery);
    if(
        strlen($password) < 8 || strlen($password) > 16 ||
        !preg_match('/[A-Z]/', $password) ||
        !preg_match('/[a-z]/', $password) ||
        !preg_match('/\d/', $password) ||
        !preg_match('/[^A-Za-z0-9]/', $password) ||
        preg_match('/\s/', $password)
    ){
        ?>
        <script>alert('Password must be 8-16 characters long and contain no spaces.');
        window.location.href = '../pages/signup.php';
        </script>
        <?php
        exit();
    }
    if(mysqli_num_rows($checkResult) > 0) {
        ?>
        <script>alert('Mobile number already registered. Please use a different number.');
        window.location.href = '../pages/signup.php';
        </script>
        <?php
    } else {
        $insertQuery = "INSERT INTO users (mobile, password) VALUES ('$mobile', '$password')";
        if(mysqli_query($conn, $insertQuery)) {
            ?>
            <script>alert('Registration successful! You can now log in.');
            window.location.href = '../pages/login.php';
            </script>
            <?php
        } else {
            ?>
            <script>alert('Error during registration. Please try again.');
            window.location.href = '../pages/signup.php';
            </script>
            <?php
        }
    }
}
?>