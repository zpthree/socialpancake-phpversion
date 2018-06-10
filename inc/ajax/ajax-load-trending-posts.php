<?php
include '../db-connection.php';
include '../classes/user.php';
include '../classes/post.php';

$limit = 6; //sets number of posts to be loaded per call
$hashtag = $_POST['hashtag'];

$posts = new Post($db, $_REQUEST['loggedInUser']);
$posts->loadPostsTrending($_REQUEST, $limit, $hashtag);
