<?php
require '../db-connection.php';
include '../classes/user.php';
include '../classes/message.php';
if (isset($_SESSION['username'])) {
  $loggedInUser = $_SESSION['username'];
}

//get unread messages
$messageObj = new Message($db, $loggedInUser);
$numMessages = $messageObj->getUnreadMessages();

if ($numMessages > 0) {
  echo "<div class='notification-badge'>" . $numMessages . "</div>";
}
