<?php
require '../db-connection.php';

//get id of post
if (isset($_GET['post_id'])) {
	$postId = $_GET['post_id'];
}

if (isset($_POST['result'])) {
	if($_POST['result'] == 'true') {
		$query = $db->query("
			UPDATE posts
			SET deleted = 'yes'
			WHERE id = '$postId'
		");

		$deletePost = $db->query("
			DELETE FROM notifications
			WHERE post_id = '$postId'
		");

	}
}

?>
