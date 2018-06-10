<?php
include '../db-connection.php';
include '../classes/user.php';
include '../classes/post.php';

$likes = "";

if (!empty($_POST['$likes'])) {
  $likes = $_POST['$likes'];
}

$limit = 10; //number of posts to be loaded per call

$posts = new Post($db, $_REQUEST['loggedInUser']);
$posts->loadLikedPosts($_REQUEST, $limit, $likes);
