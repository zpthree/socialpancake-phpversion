<?php
include '../db-connection.php';
include '../classes/user.php';
include '../classes/post.php';
include '../classes/notification.php';
include '../classes/message.php';

$loggedInUser = $_SESSION['username'];
if (!empty($_SESSION['convo'])) {
	$userTo = $_SESSION['convo'];
} else {
	$userTo = $messageObj->getMostRecentUser();
	if ($userTo == false) {
		$userTo = 'new';
	}
}


if (!empty($_POST['message_body'])) {
	$body = $db->escape_string($_POST['message_body']);
	$date = date("Y-m-s H:i:s");
	$messageObj = new Message($db, $loggedInUser);
	$messageObj->sendMessage($userTo, $body);
}
