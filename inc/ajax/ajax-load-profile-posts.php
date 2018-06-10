<?php
include '../db-connection.php';
include '../classes/user.php';
include '../classes/post.php';

$images = "";
$deleted = "";

if (!empty($_POST['images'])) {
  $images = $_POST['images'];
}

if (!empty($_POST['deleted'])) {
  $deleted = $_POST['deleted'];
}

$limit = 10; //number of posts to be loaded per call

$posts = new Post($db, $_REQUEST['loggedInUser']);
$posts->loadProfilePosts($_REQUEST, $limit, $images, $deleted);
