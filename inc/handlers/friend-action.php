<?php
require '../db-connection.php';
include '../classes/user.php';
$loggedInUser = $_POST['loggedInUser'];
$userFullName = $_POST['userFullName'];
$username = $_POST['username'];
$action = $_POST['action'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
	if ($action === 'delete') {
		$user = new User($db, $loggedInUser);
		$user->unfollowFriend($username);
	} else if ($action === 'add') {
		$user = new User($db, $loggedInUser);
		$user->followFriend($username, $loggedInUser);
	} else if ($action === 'accept-request') {
		$user = new User($db, $loggedInUser);
		$user->acceptFollow($username);
	}  else if ($action == 'respond') {
		header("Location: ". $filePath . $loggedInUser . "/requests");
	}

	if (isset($_POST['post_message'])) {
		if (isset($_POST['message_body'])) {
			$body = $db->escape_string($_POST['message_body']);
			$date = date("Y-m-d H:i:s");
			$message_obj->sendMessage($username, $body, $date);
		}

		$link = '#profileTabs a[href="#messages_div"]';
		echo "<script>
				$(function() {
					$('" . $link . "').tab('show');
				});
			</script>";
	}
}

echo $loggedInUser . " " . $username . " " . $action;
?>
