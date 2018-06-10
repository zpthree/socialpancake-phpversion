<?php
require '../db-connection.php';
include '../classes/user.php';
include '../classes/message.php';
$loggedInUser = $_SESSION['username'];
$userToObj = new User($db, $loggedInUser);
$messageObj = new Message($db, $loggedInUser);

// checking which user is being messaged
if (!empty($_SESSION['convo'])) {
	$userTo = $_SESSION['convo'];
} else {
	$userTo = $messageObj->getMostRecentUser();
	if ($userTo == false) {
		$userTo = 'new';
	}
}

if ($userTo != "new") {
	$userToObj = new User($db, $userTo);
} 

echo $messageObj->getConvos();