<?php
include '../db-connection.php';
include '../classes/user.php';

//	getLiveSearchUsers script is in scripts.js

$query = $_POST['query'];
$loggedInUser = $_POST['loggedInUser'];

$names = explode(" ", $query);

if (count($names) == 2) {
	$usersReturnedQuery = $db->query("
		SELECT * FROM users WHERE (first_name LIKE '%$names[0]%' AND last_name LIKE '%$names[1]%') AND closed_account = 'no' LIMIT 6
	");
}

// else if query has one word only, search first names and last names
else {
	$usersReturnedQuery = $db->query("
		SELECT * FROM users WHERE (first_name LIKE '%$names[0]%' OR last_name LIKE '%$names[0]%' OR username LIKE '%$names[0]%' OR email LIKE '$names[0]') AND closed_account = 'no' LIMIT 6
	");
}

if ($query != "") {
	while ($usersReturnedRow = $usersReturnedQuery->fetch_assoc()) {
		$user = new User($db, $loggedInUser);
		// $mutualFriendsNum = $user->getMutualFollowing($usersReturnedRow['username']);
		// if ($usersReturnedRow['username'] != $loggedInUser) {
		// 	if ($mutualFriendsNum == 1) {
		// 		$mutualFriends = $mutualFriendsNum . " friend in common";
		// 	} else {
		// 		$mutualFriends = $mutualFriendsNum . " friends in common";
		// 	}
		// } else {
		// 	$mutualFriends = "";
		// }

		if ($usersReturnedRow['verified'] == 'yes') {
			$verified = "verified";
			$verifiedPic = $filePath . "img/icons/verified.svg";
		} else {
			$verified = "";
			$verifiedPic = "";
		}

		echo "<div class='result-display'>
				<a  href='" . $filePath . $usersReturnedRow['username'] . "'>
				<div class='live-search-photo'>
					<img src='" . $filePath .  $usersReturnedRow['profile_pic'] . "'>
				</div>
				<div class='live-search-text'>
					<span class='search-full-name'><p class='name'>" . $usersReturnedRow['first_name'] . " " . $usersReturnedRow['last_name'] . "</p><img class='$verified' src='$verifiedPic'><span class='search-username'>@" . $usersReturnedRow['username'] . "</span></span>
					<p id='grey'></p>
				</div></a>
			  </div>";
	}
}

?>
