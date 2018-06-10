<?php
require '../db-connection.php';
include '../classes/user.php';

//get id of post
if (isset($_GET['post_id'])) {
	$postId = $_GET['post_id'];
	$sharedId = $_GET['unique_id'];
	$shareValue = $_POST['shareValue'];
}

$sharedBy = $_SESSION['username'];

if(isset($_SESSION['username'])) {
	$loggedInUser = $_SESSION['username'];
	$userDetails = $db->query("
		SELECT * FROM users WHERE username = '$loggedInUser'
	");
	$user = $userDetails->fetch_assoc();
	$fullUserName = $user['first_name'] . " " . $user['last_name'];
} else {
	header('Location: ../../register.php');
}

$getOriginal = $db->query("
	SELECT * FROM posts WHERE post_unique_id = '$sharedId'
");

$originalPost = $getOriginal->fetch_assoc();
$sharedBody = $originalPost['body'];
$sharedImage = $originalPost['image'];
$sharedYoutube = $originalPost['youtube'];
$sharedAddedBy = $originalPost['added_by'];
$sharedUserTo = $originalPost['user_to'];
$sharedDateAdded = $originalPost['date_added'];
$sharedClosedAccount = $originalPost['closed_account'];
$sharedLikes = $originalPost['likes'];
$sharedDeleted = $originalPost['deleted'];
$sharedDeleted = $originalPost['edited'];

//share button button
if ($shareValue == "Share") {

	//insert the post into the database
	$insertPost = $db->prepare("
		INSERT INTO posts (body, image, youtube, added_by, user_to, date_added, closed_account, deleted, likes, shared_by, post_unique_id, edited)
		VALUES (?, ?, ?, ?, ?, NOW(), ?, ?, ?, ?, ?, ?)
	");

	$insertPost->bind_Param('sssssssssss', $sharedBody, $sharedImage, $sharedYoutube, $sharedAddedBy, $sharedUserTo, $sharedClosedAccount, $sharedDeleted, $sharedLikes, $sharedBy, $sharedId, $sharedDeleted);
	$insertPost->execute();

	$link = "i/post/" . $postId;
	$message = $fullUserName . " shared your post";
	$type = "share";

	$insertNotification = $db->prepare("
		INSERT INTO notifications (user_to, user_from, type, message, link, datetime, opened, viewed)
		VALUES (?, ?, ?, ?, ?, NOW(), 'no', 'no')
	");
	$insertNotification->bind_param('sssss', $sharedAddedBy, $loggedInUser, $type, $message, $link);
	$insertNotification->execute();

}

//share button button
if ($shareValue == "Unshare" && $sharedBy != 'none') {

	//delete the post from the database
	$deletePost = $db->query("
		DELETE FROM posts WHERE post_unique_id = '$sharedId' AND shared_by = '$sharedBy'
	");
}
