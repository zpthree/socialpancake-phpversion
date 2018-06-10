<?php
require '../db-connection.php';
include '../classes/user.php';
include '../classes/message.php';
$loggedInUser = $_SESSION['username'];
$userToObj = new User($db, $loggedInUser);
$messageObj = new Message($db, $loggedInUser);
$userTo = "";

// checking which user is being messaged
if (!empty($_SESSION['convo'])) {
	$userTo = $_SESSION['convo'];
} else if ($userTo == false) {
		$userTo = 'new';
}

if ($userTo != "new") {
	$userToObj = new User($db, $userTo);
}
if ($userTo != "new") {
	echo "<div id='messages-header'><a href='" . $filePath . "i/messages'><svg id='left-message' version='1.1' viewBox='0 0 40 40' xml:space='preserve' xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink'>
	<polygon points='17.3,20 26.5,11.3 24.6,9.5 16.4,17.2 16.4,17.2 13.5,20 24.6,30.5 26.5,28.7 '/>
	</svg></a><h3>" . $userToObj->getFirstAndLastName() . "</h3></div>";
	echo "<div class='loaded-messages' id='scroll-messages'>";
	echo $messageObj->getMessages($userTo);
	echo "</div>";
} else {
	echo "<h4>New Messages</h4>";
}

?>
