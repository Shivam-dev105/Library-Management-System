<?php
include('includes/config.php');

if (!isset($_GET['user_id']) || empty($_GET['user_id'])) {
    header("Location: manage_students.php");
    exit();
}

$user_id = mysqli_real_escape_string($con, $_GET['user_id']);

$query = mysqli_query($con, "SELECT qr_code FROM users WHERE id='$user_id'");
$user = mysqli_fetch_assoc($query);

if (empty($user['qr_code'])) {
    header("Location: manage_students.php");
    exit();
}

$filePath = "uploads/qrcodes/" . $user['qr_code'];

if (file_exists($filePath)) {

    header("Content-Type: image/png");
    header("Content-Disposition: attachment; filename=".$user['qr_code']);
    readfile($filePath);
    exit();

} else {
    header("Location: manage_students.php");
    exit();
}
?>