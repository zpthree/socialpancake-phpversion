<?php
require '../db-connection.php';

if(isset($_SESSION['username'])) {
	$loggedInUser = $_SESSION['username'];
	$userDetails = $db->query("
		SELECT * FROM users WHERE username = '$loggedInUser'
	");
	$user = $userDetails->fetch_assoc();
} else {
	header('Location: ../../register.php');
}

$name = $db->query("
	SELECT first_name, last_name FROM users WHERE username = '$loggedInUser'
");
$nameRow = $name->fetch_assoc();
$fullUserName = $nameRow['first_name'] . " " . $nameRow['last_name'];

//send likes
//get id of post
if (isset($_GET['post_id'])) {
	$postId = $_GET['post_id'];
	$uniqueId = $_GET['unique_id'];
}

$likesValue = $_POST['likesValue'];

$getLikes = $db->query("
	SELECT id, likes, added_by FROM posts WHERE post_unique_id = '$uniqueId' ORDER BY id LIMIT 1
");
$userRow = $getLikes->fetch_assoc();
$postId = $userRow['id'];
$numLikes = $userRow['likes'];
$userLiked = $userRow['added_by'];

$userDetails = $db->query("
	SELECT * FROM users WHERE username = '$userLiked'
");
$userRow = $userDetails->fetch_assoc();
$numUserLikes = $userRow['num_likes'];

//like button
if ($likesValue == "Like") {
	$numLikes++;
	$updatePostLikes = $db->query("
		UPDATE posts SET likes ='$numLikes' WHERE id = '$postId'
	");
	$numUserLikes++;
	$updateUserLikes = $db->query("
		UPDATE users SET num_likes = '$numUserLikes' WHERE username = '$userLiked'
	");
	$insertUser = $db->prepare("
		INSERT INTO likes (username, post_id, post_unique_id)
		VALUES (?,?, ?)
	");
	$insertUser->bind_Param('sss', $loggedInUser, $postId, $uniqueId);
	$insertUser->execute();

	//insert notification
	$link = "i/post/" . $postId;
	$message = $fullUserName . " liked your post";
	$type = "like";
	if($userLiked != $loggedInUser) {
		$insertNotification = $db->prepare("
			INSERT INTO notifications (post_id, user_to, user_from, type, message, link, datetime, opened, viewed)
			VALUES (?, ?, ?, ?, ?, ?, NOW(), 'no', 'no')
		");
		$insertNotification->bind_param('ssssss', $postId, $userLiked, $loggedInUser, $type, $message, $link);
		$insertNotification->execute();
	}
}

//unlike button
if ($likesValue == "Unlike") {
	$numLikes--;
	$updatePostLikes = $db->query("
		UPDATE posts SET likes ='$numLikes' WHERE post_id = '$postId'
	");
	$numUserLikes--;
	$updateUserLikes = $db->query("
		UPDATE users SET num_likes = '$numUserLikes' WHERE username = '$userLiked'
	");
	$deleteUser = $db->prepare("
		DELETE FROM likes WHERE username = '$loggedInUser' AND post_unique_id = '$uniqueId'
	");

	$deleteUser->execute();

	if($userLiked != $loggedInUser) {
		$deleteNotification = $db->prepare("
			DELETE FROM notifications
			WHERE user_from = '$loggedInUser' AND post_id = '$postId'
		");
		$deleteNotification->execute();
	}
}

?>
