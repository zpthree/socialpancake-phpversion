<?php
ob_start();
session_start();
$timezone = date_default_timezone_set("America/New_York");

$home = "http://socialpancake.net";
$filePath = "http://socialpancake.net/";

// $db = new mysqli("localhost", "root", "root", "socialmedia");
$db = new mysqli("localhost", "postyyqm_zpthree", "Nawlins32", "postyyqm_social");

if ($db->connect_errno !== 0) {
	exit("Failed to connect: " . $db->connect_errno);
}
