<?php
session_start();

if (!isset($_SESSION['student_id'])) {
    header("Location:index.php");
}

?>