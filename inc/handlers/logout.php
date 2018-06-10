<?php
session_start();

// unset($_COOKIE['loggedInUser']);
setcookie(session_name(), '', time() - 3600, "/");
setcookie("loggedInUser", '', time() - 3600, "/");

session_destroy();

header("Location: ../../register.php");
