<?php
require '../db-connection.php';
include '../classes/user.php';
include '../classes/notification.php';

$loggedInUser = $_POST['user'];

//get unread notifications
$notifications = new Notification($db, $loggedInUser);
$notifications->markNotificationRead();
