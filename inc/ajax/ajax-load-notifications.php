<?php
include '../db-connection.php';
include '../classes/user.php';
include '../classes/notification.php';

$limit = 15; //number of messages to load

$notification = new Notification($db, $_REQUEST['loggedInUser']);
$notification->getNotifications($_REQUEST, $limit);