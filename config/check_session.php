<?php
if (isset($_SESSION['mobile'])) {
    exit();
} else {
    header("Location: ../pages/login.php");
}
?>