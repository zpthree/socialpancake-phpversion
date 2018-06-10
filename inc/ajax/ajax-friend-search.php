<?php
	require '../db-connection.php';
	include '../classes/user.php';

	$query = $_POST['query'];
	$loggedInUser = $_POST['loggedInUser'];

	$names = explode(" ", $query);

	if (strpos($query, "_") !== false) {
		$usersReturned = $db->query("
			SELECT * FROM users WHERE username LIKE '$query%' AND closed_account = 'no' LIMIT 8
		");
	} else if (count($names) == 2) {
		$usersReturned = $db->query("
			SELECT * FROM users WHERE (first_name LIKE '%$names[0]%' AND last_name LIKE '%$names[1]%') AND closed_account = 'no' LIMIT 8
		");
	} else {
		$usersReturned = $db->query("
			SELECT * FROM users WHERE (first_name LIKE '%$names[0]%' OR last_name LIKE '%$names[0]%' OR username LIKE '%$names[0]%') AND closed_account = 'no' LIMIT 8
		");
	}

	if ($query != "") {
		while ($usersReturnedRow = $usersReturned->fetch_assoc()) {
			$user = new User($db, $loggedInUser);
			// if ($usersReturnedRow['username'] != $loggedInUser) {
			// 	$mutual_friends = $user->getMutualFollowing($usersReturnedRow['username']) . " friends in common";
			// } else {
			// 	$mutual_friends = "";
			// }

			if ($usersReturnedRow['verified'] == 'yes') {
				$verified = "verified";
				$verifiedPic = "/img/icons/verified.svg";
			} else {
				$verified = "";
				$verifiedPic = "";
			}

			if($user->isFollowing($usersReturnedRow['username'])) {
				echo "<div class='resultDisplay'>
						<a href='" . $filePath . "i/messages/" . $usersReturnedRow['username'] . "' style='color: #000;'>
							<div class='liveSearchProfilePic'>
								<img src='" . $user->filePath . $usersReturnedRow['profile_pic'] . "'>
							</div>
							<div class='liveSearchText'>
								<span class='search-full-name'><p class='name'>" . $usersReturnedRow['first_name'] . " " . $usersReturnedRow['last_name'] . "</p><img class='$verified' src='$verifiedPic'><span class='search-username'>@" . $usersReturnedRow['username'] . "</span></span>
							</div>
						</a>
						</div>";
			}
		}
	}

?>
