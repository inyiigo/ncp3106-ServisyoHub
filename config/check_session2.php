<?php
if (isset($_SESSION['mobile'])) {
    header("Location: ../pages/home-gawain.php");
    exit();
}else{
    // Do nothing, allow access to the page
}
?>