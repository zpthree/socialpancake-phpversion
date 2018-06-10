<?php
require '../db-connection.php';
include '../classes/user.php';
include '../classes/notification.php';
if (isset($_SESSION['username'])) {
  $loggedInUser = $_SESSION['username'];
}

//get unread notifications
$notificationObj = new Notification($db, $loggedInUser);
$numNotifications = $notificationObj->getUnreadNumber();

$numNotifications = $numNotifications;

if ($numNotifications > 0) {
  echo "<div class='notification-badge'>" . $numNotifications . "</div>";
}
