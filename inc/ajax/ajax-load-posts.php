<?php
include '../db-connection.php';
include '../classes/user.php';
include '../classes/post.php';

$limit = 10; //sets number of posts to be loaded per call

$posts = new Post($db, $_REQUEST['loggedInUser']);
$posts->loadPostsFollowing($_REQUEST, $limit);
