<?php
include '../db-connection.php';
include '../classes/user.php';
include '../classes/post.php';
include '../classes/notification.php';

if(!empty($_POST['post_body'])) {
	$post = new Post($db, $_POST['userFrom']);
	$post->submitPost($_POST['post_body'], $_POST['userTo']);
}