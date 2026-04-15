<?php
session_start();
include('includes/config.php');

// check admin login
if (!isset($_SESSION['admin'])) {
    header('location:../index.php');
    exit();
}

/* ===== SESION DELETE ===== */
if (isset($_GET['session_id'])) {
    $id = $_GET['session_id'];

    $query = mysqli_query($con, "DELETE FROM `academic_session` WHERE id='$id'");
    if ($query) {
        echo "<script>alert('Academic Session deleted successfully'); window.location.href='academic.php#session';</script>";
    } else {
         echo "<script>alert('Something went wrong');window.location.href='academic.php#session'</script>";
    }
}

/* ===== RACk DELETE ===== */
if (isset($_GET['rack_id'])) {
    $id = $_GET['rack_id'];

    $query = mysqli_query($con, "DELETE FROM `rack_section` WHERE id='$id'");
    if ($query) {
        echo "<script>alert('Rack deleted successfully'); window.location.href='academic.php#rack';</script>";
    } else {
         echo "<script>alert('Something went wrong');window.location.href='academic.php#rack'</script>";
    }
}

/* ===== DEPARTMENT DELETE ===== */
if (isset($_GET['depart_id'])) {
    $id = $_GET['depart_id'];

    $query = mysqli_query($con, "DELETE FROM `department` WHERE id='$id'");
    if ($query) {
        $catquery = mysqli_query($con, "DELETE FROM `category` WHERE department_id='$id'");
        echo "<script>alert('Department deleted successfully'); window.location.href='academic.php#dept';</script>";
    } else {
         echo "<script>alert('Something went wrong');window.location.href='academic.php#dept'</script>";
    }
}

/* ===== Category DELETE ===== */
if (isset($_GET['category_id'])) {
    $id = $_GET['category_id'];

    $query = mysqli_query($con, "DELETE FROM `category` WHERE id='$id'");
    if ($query) {
        echo "<script>alert('Category deleted successfully'); window.location.href='academic.php#category';</script>";
    } else {
         echo "<script>alert('Something went wrong');window.location.href='academic.php#category'</script>";
    }
}

/* ===== USERs DELETE ===== */
if (isset($_GET['user_id'])) {
    $id = $_GET['user_id'];

    $query = mysqli_query($con, "DELETE FROM `users` WHERE id='$id'");
    if ($query) {
        echo "<script>alert('User deleted successfully'); window.location.href='manage_students.php#view';</script>";
    } else {
         echo "<script>alert('Something went wrong');window.location.href='manage_students.php#view'</script>";
    }
}

/* ===== Books DELETE ===== */
if (isset($_GET['book_id'])) {
    $id = $_GET['book_id'];

    $query = mysqli_query($con, "DELETE FROM `books` WHERE id='$id'");

    if ($query) {
        $book_copie = mysqli_query($con, "DELETE FROM `book_copies` WHERE `book_id`='$id'");
        echo "<script>alert('Book deleted successfully'); window.location.href='manage_books.php#manage';</script>";
    } else {
         echo "<script>alert('Something went wrong');window.location.href='manage_books.php#manage'</script>";
    }
}

