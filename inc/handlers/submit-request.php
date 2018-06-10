<?php
include '../db-connection.php';
include '../classes/user.php';
include '../classes/post.php';

if (isset($_SESSION['username'])) {
	$loggedInUser = $_SESSION['username'];
	$userDetails = $db->query("
		SELECT * FROM users WHERE username = '$loggedInUser'
	");
	$userRow = $userDetails->fetch_assoc();
} else {
	header("Location: register.php");
}

if (isset($_POST['user_from'])) {
	$userFrom = $_POST['user_from'];
}


if ($_POST['request_response'] == "accept") {
	$addFriendQuery = $db->query ("
		UPDATE users SET friends = CONCAT(friends, '$userFrom,') WHERE username = '$loggedInUser'
	");
	$addFriendQuery = $db->query ("
		UPDATE users SET friends = CONCAT(friends, '$loggedInUser,') WHERE username = '$userFrom'
	");
	$deleteQuery = $db->query("
		DELETE FROM friend_requests WHERE user_to = '$loggedInUser' AND user_from = '$userFrom'
	");
}

if ($_POST['request_response'] == "ignore") {
	$deleteQuery = $db->query("
		DELETE FROM friend_requests WHERE user_to = '$loggedInUser' AND user_from = '$userFrom'
	");
	echo "Request has been ignored!";
	//header("Location: requests.php");
}


?>