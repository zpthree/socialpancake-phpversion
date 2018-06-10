<?php
require '../db-connection.php';
include '../classes/user.php';

if(isset($_SESSION['username'])) {
	$loggedInUser = $_SESSION['username'];
	$userDetails = $db->query("
		SELECT * FROM users WHERE username = '$loggedInUser'
	");
	$user = $userDetails->fetch_assoc();
} else {
	header('Location: ../../register.php');
}

//send comments
//get id of post
if (isset($_GET['post_id'])) {
	$postId = $_GET['post_id'];
	$uniqueId = $_GET['unique_id'];
}

$name = $db->query("
	SELECT first_name, last_name FROM users WHERE username = '$loggedInUser'
");
$nameRow = $name->fetch_assoc();
$fullUserName = $nameRow['first_name'] . " " . $nameRow['last_name'];

$userQuery = $db->query("
	SELECT id, added_by, user_to FROM posts WHERE post_unique_id = '$uniqueId' ORDER BY id LIMIT 1
");
$userRow = $userQuery->fetch_assoc();
$postId = $userRow['id'];
$postedTo = $userRow['added_by'];
$userTo = $userRow['user_to'];
$type = "comment";

if (!empty($_POST['post_body'])) {
	$postBody = $_POST['post_body'];
	$postBody = $db->escape_string($postBody);
	$dateTimeNow = date('Y-m-d H:i:s');

	$insertPost = $db->prepare("
		INSERT INTO comments (post_body, posted_by, posted_to, date_added, removed, post_id, post_unique_id)
		VALUES (?, ?, ?, ?, 'no', ?, ?)
	");

	$insertPost->bind_Param('ssssss', $postBody, $loggedInUser, $postedTo, $dateTimeNow, $postId, $uniqueId);
	$insertPost->execute();

	//insert notification
	$link = "i/post/" . $postId;
	if($postedTo != $loggedInUser) {
		$message = $fullUserName . " commented on your post";
		$insertNotification = $db->prepare("
			INSERT INTO notifications (post_id, user_to, user_from, type, message, link, datetime, opened, viewed)
			VALUES (?, ?, ?, ?, ?, ?, NOW(), 'no', 'no')
		");
		$insertNotification->bind_param('ssssss', $postId, $postedTo, $loggedInUser, $type, $message, $link);
		$insertNotification->execute();
	}

	if($userTo != 'none' && $userTo != $loggedInUser) {
		$message = $fullUserName . " commented on your profile post";
		$insertNotification = $db->prepare("
			INSERT INTO notifications (post_id, user_to, user_from, type, message, link, datetime, opened, viewed)
			VALUES (?, ?, ?, ?, ?, NOW(), 'no', 'no')
		");
		$insertNotification->bind_param('ssssss', $postId, $userTo, $loggedInUser, $type, $message, $link);
		$insertNotification->execute();
	}


		$getCommenters = $db->query("
			SELECT * FROM comments WHERE post_id = '$postId'
		");
		$notifiedUsers = [];

		while($userRow = $getCommenters->fetch_assoc()) {
			$message = $loggedInUser . " commented on a post you commented on.";
			if($userRow['posted_by'] != $postedTo && $userRow['posted_by'] != $userTo
				&& $userRow['posted_by'] != $loggedInUser && !in_array($userRow['posted_by'], $notifiedUsers)) {

				$insertNotification = $db->prepare("
					INSERT INTO notifications (post_id, user_to, user_from, type, message, link, datetime, opened, viewed)
					VALUES (?, ?, ?, ?, ?, ?, NOW(), 'no', 'no')
				");
				$insertNotification->bind_param('ssssss', $postId, $userRow['posted_by'], $loggedInUser, $type, $message, $link);
				$insertNotification->execute();

				array_push($notifiedUsers, $userRow['posted_by']);
			}

		}

	$getComments = $db->query("
		SELECT * FROM comments WHERE post_id = '$postId' ORDER BY id DESC
	");
	$commentCount = $getComments->num_rows;
	$commentCount;

}

?>
