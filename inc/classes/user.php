<?php

class User
{

	private $user;
	private $db;

	public function __construct($db, $user) {
		$this->db = $db;
		$userDetails = $this->db->query("
			SELECT * FROM users WHERE username = '$user'
		");
		$this->user = $userDetails->fetch_assoc();
		$this->filePath = "/";
	}

	public function getUsername() {
		return $this->user['username'];
	}

	public function getNumberOfFriendRequests() {
		$username = $this->user['username'];
		$sql = $this->db->query("
			SELECT * FROM friend_requests WHERE user_to = '$username'
		");
		return $sql->num_rows;
	}

	public function getFirstAndLastName() {
		$username = $this->user['username'];
		$getFullName = $this->db->query("
			SELECT first_name, last_name FROM users WHERE username='$username'
		");
		$userRow = $getFullName->fetch_assoc();
		return $userRow['first_name'] . " " . $userRow['last_name'];
	}

	public function checkIfPrivate($userToCheck) {
		$checkIfPrivate = $this->db->query("
			SELECT private_account FROM users WHERE username='$userToCheck'
		");
		$userRow = $checkIfPrivate->fetch_assoc();
		return $userRow['private_account'];
	}

	public function getProfilePic() {
		$username = $this->user['username'];
		$getProfilePic = $this->db->query("
			SELECT profile_pic FROM users WHERE username='$username'
		");
		$userRow = $getProfilePic->fetch_assoc();
		return $userRow['profile_pic'];
	}

	public function getNumPosts() {
		$username = $this->user['username'];
		$getNumPosts = $this->db->query("
			SELECT num_posts FROM users WHERE username = '$username'
		");
		$numPostsRow = $getNumPosts->fetch_assoc();
		return $numPostsRow['num_posts'];
	}

	public function getNumLikes() {
		$username = $this->user['username'];
		$getNumLikes = $this->db->query("
			SELECT num_likes FROM users WHERE username = '$username'
		");
		$numLikesRow = $getNumLikes->fetch_assoc();
		return $numLikesRow['num_likes'];
	}

	public function getFollowingArray() {
		$username = $this->user['username'];
		$getFollowing = $this->db->query("
			SELECT following FROM users WHERE username = '$username'
		");
		$followingRow = $getFollowing->num_rows;
		return $followingRow;
	}


	public function isClosed() {
		$username = $this->user['username'];
		$checkForClosed = $this->db->query("
			SELECT closed_account FROM users WHERE username = '$username'
		");
		$checkForClosedRow = $checkForClosed->fetch_assoc();

		if ($checkForClosedRow['closed_account'] == 'yes') {
			return true;
		} else {
			return false;
		}
	}

	public function isFollowing($checkUsername) {
		$usernameComma = "," . $checkUsername . ",";

		if (strstr($this->user['following'], $usernameComma) || $checkUsername == $this->user['username']) {
			return true;
		} else {
			return false;
		}

	}

	public function followFriend($userFollowing, $loggedInUser) {

		//username user query

		$followingDetails = $this->db->query("
			SELECT * FROM users WHERE username = '$userFollowing'
		");
		$followingRow = $followingDetails->fetch_assoc();

		//loggedInUser user query

		$userDetails = $this->db->query("
			SELECT * FROM users WHERE username = '$loggedInUser'
		");
		$userRow = $userDetails->fetch_assoc();

		$followingUserCheck = "," . $userFollowing . ",";

		if (!strpos($userRow['following'], $followingUserCheck)) {

			if ($followingRow['private_account'] != 'yes') {

				//follow user if account isn't set to private

				$addFollowingQuery = $this->db->query ("
					UPDATE users SET following = CONCAT(following, '$userFollowing,') WHERE username = '$loggedInUser'
				");
				$addFollowersQuery = $this->db->query ("
					UPDATE users SET followers = CONCAT(followers, '$loggedInUser,') WHERE username = '$userFollowing'
				");

				$link = $loggedInUser;
				$message = $userRow['first_name'] . " " . $userRow['last_name'] . " followed you";
				$type = "follow";

				$insertNotification = $this->db->prepare("
					INSERT INTO notifications (post_id, user_to, user_from, type, message, link, datetime, opened, viewed)
					VALUES ('none', ?, ?, ?, ?, ?, NOW(), 'no', 'no')
				");
				$insertNotification->bind_param('sssss', $userFollowing, $loggedInUser, $type, $message, $loggedInUser);
				$insertNotification->execute();

			} else {

				//send follow request

				$insertNotification = $this->db->prepare("
					INSERT INTO friend_requests (user_to, user_from)
					VALUES (?, ?)
				");
				$insertNotification->bind_param('ss', $userFollowing, $loggedInUser);
				$insertNotification->execute();

				//insert notification

				$link = $userFollowing . '/requests';
				$message = $userRow['first_name'] . " " . $userRow['last_name'] . " wants to follow you";
				$type = "follow-request";

				$insertNotification = $this->db->prepare("
					INSERT INTO notifications (post_id, user_to, user_from, type, message, link, datetime, opened, viewed)
					VALUES (',', ?, ?, ?, ?, ?, NOW(), 'no', 'no')
				");
				$insertNotification->bind_param('sssss', $userFollowing, $loggedInUser, $type, $message, $link);
				$insertNotification->execute();
			}

		}

	}

	public function didReceiveRequest($userFrom) {
		$userTo = $this->user['username'];
		$checkRequestQuery = $this->db->query("
			SELECT * FROM friend_requests WHERE user_to = '$userTo' AND user_from = '$userFrom'
		");
		if ($checkRequestQuery->num_rows > 0 ) {
			return true;
		} else {
			return false;
		}
	}

	public function didSendRequest($userTo) {
		$userFrom = $this->user['username'];
		$checkRequestQuery = $this->db->query("
			SELECT * FROM friend_requests WHERE user_to = '$userTo' AND user_from = '$userFrom'
		");
		if ($checkRequestQuery->num_rows > 0 ) {
			return true;
		} else {
			return false;
		}
	}

	public function unfollowFriend($userToRemove) {
		$loggedInUser = $this->user['username'];
		$query = $this->db->query("
			SELECT following FROM users WHERE username = '$userToRemove'
		");
		$userRow = $query->fetch_assoc();
		$friendArrayUsername = $userRow['following'];

		$newFriendArray = str_replace($userToRemove . ",", "", $this->user['following']);
		$unfollowFriend = $this->db->query("
			UPDATE users SET following = '$newFriendArray' WHERE username = '$loggedInUser'
		");

		$newFriendArray = str_replace($loggedInUser . ",", "", $friendArrayUsername);
		$unfollowFriend = $this->db->query("
			UPDATE users SET followers = '$newFriendArray' WHERE username = '$userToRemove'
		");
	}

	public function acceptFollow($userToAccept) {
		$loggedInUser = $this->user['username'];

		$userDetails = $this->db->query("
			SELECT * FROM users WHERE username = '$loggedInUser'
		");
		$userRow = $userDetails->fetch_assoc();

		$addFollowingQuery = $this->db->query ("
			UPDATE users SET followers = CONCAT(followers, '$userToAccept,') WHERE username = '$loggedInUser'
		");
		$addFollowersQuery = $this->db->query ("
			UPDATE users SET following = CONCAT(following, '$loggedInUser,') WHERE username = '$userToAccept'
		");

		$removeRequest = $this->db->query("
			DELETE FROM friend_requests
			WHERE user_to = '$loggedInUser' AND user_from = '$userToAccept'
		");

		$link = $loggedInUser;
		$message = $userRow['first_name'] . " " . $userRow['last_name'] . " accepted your follow request";
		$type = "follow";

		$insertNotification = $this->db->prepare("
			INSERT INTO notifications (post_id, user_to, user_from, type, message, link, datetime, opened, viewed)
			VALUES (',', ?, ?, ?, ?, ?, NOW(), 'no', 'no')
		");
		$insertNotification->bind_param('sssss', $userToAccept, $loggedInUser, $type, $message, $loggedInUser);
		$insertNotification->execute();
	}

	public function sendRequest($userTo) {
		$userFrom = $this->user['username'];
		$query = $this->db->prepare("
			INSERT INTO friend_requests (user_to, user_from)
			VALUES (?,?)
		");
		$query->bind_Param('ss', $userTo, $userFrom);
		$query->execute();
	}

	public function getMutualFollowing($userToCheck) {
		$mutualFollowing = 0;
		//get array of users loggedInUser is following
		$userFollowing = $this->user['following'];
		$userFollowingArray = explode(",", $userFollowing);

		//get array of users userToCheck is following
		$query = $this->db->query("
			SELECT following FROM users WHERE username = '$userToCheck'
		");
		$userRow = $query->fetch_assoc();
		$userToCheckFollowing = $userRow['following'];
		$userToCheckFollowingArray = explode(",", $userToCheckFollowing);

		$mutualFollowing = array_intersect($userFollowingArray, $userToCheckFollowingArray);

		return $mutualFollowing;
	}

	public function getFollowersYouFollow($userToCheck) {
		$followersYouFollow = 0;
		//get array of users loggedInUser is following
		$userFollowing = $this->user['following'];
		$userFollowingArray = explode(",", $userFollowing);

		//get array of users userToCheck is following
		$query = $this->db->query("
			SELECT followers FROM users WHERE username = '$userToCheck'
		");
		$userRow = $query->fetch_assoc();
		$userToCheckFollowers = $userRow['followers'];
		$userToCheckFollowersArray = explode(",", $userToCheckFollowers);

		$followersYouFollow = array_intersect($userFollowingArray, $userToCheckFollowersArray);

		foreach ($followersYouFollow as $followers) {
			if ($followers !== ',' && $followers !== '') {
				$mutualFollowing[] = $followers;
			}
		}

		return $mutualFollowing;
	}

	public function whoToFollow($recommendedNum, $pageTitle) {
		$username = $this->user['username'];
		$recommendedArray= [];
		$result = [];

		$getFollowing = $this->db->query("
			SELECT following FROM users WHERE LOWER(username) = LOWER('$username')
		");

		$getFollowing = $getFollowing->fetch_assoc();

		$following = strtolower($getFollowing['following']);
		$followingArray = explode(',', $following);

		//get recommendations
		if (count($followingArray) > 2) {

			foreach ($followingArray as $following) {
				$getFollowing = $this->db->query("
					SELECT following FROM users WHERE LOWER(username) = LOWER('$following')
				");
				$getFollowing = $getFollowing->fetch_assoc();
				$following = $getFollowing['following'];
				$followingFollowingArray = explode(',', strtolower($following));

				//push followers to follow to recommendedArray
				foreach ($followingFollowingArray as $followingFollowing) {

					if ($followingFollowing != "," && $followingFollowing != "") {
						array_push($recommendedArray, $followingFollowing);
					}

				}

			}

			//count number of followers in common with $username
			$recommendedArray = array_diff($recommendedArray, $followingArray);

			// shuffle array while keeping keys
			$shuffleKeys = array_keys($recommendedArray);
			shuffle($shuffleKeys);
			$newArray = array();
			foreach($shuffleKeys as $key) {
			    $newRecommended[$key] = $recommendedArray[$key];
			}

			if (!empty($newRecommended)) {

				$recommendedArray = array_count_values($newRecommended);

			}


			//loop through and display 3 followers
			$whoToFollowDiv = "<div class='recommended-outer'>
					<h2>Recommended</h2>
					<div class='recommended-inner'>
			";
			$i = 1;
			foreach ($recommendedArray as $key => $commonFollowers) {
				if (strtolower($key) != strtolower($username)) {
					$recommendedUser = $this->db->query("
						SELECT * FROM users WHERE LOWER(username) = LOWER('$key')
					");
					$recommendedRow = $recommendedUser->fetch_assoc();

					$whoToFollowDiv .= "
						<div class='recommended-follow'>
							<img class='recommended-img' src='" . $this->filePath . $recommendedRow['profile_pic'] . "'>
							<div class='recommended-info'>
								<a href='" . $this->filePath . $recommendedRow['username'] . "'><h3>" . $recommendedRow['first_name'] . " " . $recommendedRow['last_name'] . "</h3></a>
								<h5 class='recommended-username'>@" . $recommendedRow['username'] . "</h5>
								<h5>$commonFollowers followers you know.</h5>
							</div>
						</div>
					";
					if ($i == $recommendedNum) break;
					$i++;
				}
			}

			$whoToFollowDiv .= "</div>
												</div>";

			if (array_key_exists($username, $recommendedArray)) {
				$recommendedNum = count($recommendedArray) - 1;
			} else {
				$recommendedNum = count($recommendedArray);
			}

			if (count($recommendedArray) > 1) {

				return $whoToFollowDiv;

			}

		} else {
			$whoToFollowDiv .= "
						<div class='recommended-follow'>
							<h1>Looks like you're following everyone!</h1>
						</div>
					";
		}

	}

}

?>
