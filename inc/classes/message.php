<?php

class Message
{
	private $userObj;
	private $db;

	public function __construct($db, $user) {
		$this->db = $db;
		$this->userObj = new User($this->db, $user);
		$this->filePath = "/";
	}

	public function getUnreadMessages() {
		$loggedInUser = $this->userObj->getUsername();
		$sql = $this->db->query("
			SELECT * FROM messages WHERE opened = 'no' AND user_to = '$loggedInUser'
		");
		return $sql->num_rows;
	}

	public function getMostRecentUser() {
		$loggedInUser = $this->userObj->getUsername();

		$sql = $this->db->query("
			SELECT user_to, user_from FROM messages WHERE user_to = '$loggedInUser' OR user_from = '$loggedInUser' ORDER BY id DESC LIMIT 1
		");

		if ($sql->num_rows == 0) {
			return false;
		}

		$row = $sql->fetch_assoc();
		$userTo = $row['user_to'];
		$userFrom = $row['user_from'];

		if ($userTo != $loggedInUser) {
			return $userTo;
		} else {
			return $userFrom;
		}
	}

	public function sendMessage($userTo, $body) {
		if($body != "") {
			$loggedInUser = $this->userObj->getUsername();
			$query = $this->db->prepare("
				INSERT INTO messages (user_to, user_from, body, date, opened, viewed, deleted)
				VALUES (?,?,?, NOW(), 'no', 'no', 'no')
			");
			$query->bind_param('sss', $userTo, $loggedInUser, $body);
			$query->execute();
		}
	}

	public function getMessages($otherUser) {
		$loggedInUser = $this->userObj->getUsername();
		$data = "";

		$query = $this->db->query("
			UPDATE messages SET opened = 'yes' WHERE user_to = '$loggedInUser' AND user_from = '$otherUser'
		");

		$get_messages = $this->db->query("
			SELECT * FROM messages WHERE (user_to = '$loggedInUser' AND user_from = '$otherUser') OR (user_from = '$loggedInUser' and user_to = '$otherUser')
		");

		while ($row = $get_messages->fetch_assoc()) {
			$userTo = $row['user_to'];
			$userFrom = $row['user_from'];
			$body = $row['body'];
			$body = "<p>" . preg_replace('#(\\\r|\\\r\\\n|\\\n)#', '</p><p>', $body) . "</p>";

			$body = stripcslashes($body);


			$divTop = ($userTo == $loggedInUser) ? "<div class='message' id='green'>" : "<div class='message' id='blue'>";
			$data = $data . $divTop . $body . "</div>";
		}

		return $data;
	}

	public function getLatestMessage($loggedInUser, $user2) {
		$detailsArray = [];
		$query = $this->db->query("
			SELECT body, user_to, date, opened FROM messages WHERE (user_to = '$loggedInUser' AND user_from = '$user2') OR (user_to = '$user2' AND user_from = '$loggedInUser') ORDER BY id DESC LIMIT 1
		");

		$row = $query->fetch_assoc();
		$sentBy = ($row['user_to'] == $loggedInUser) ? "They said: " : "You said: ";
		if ($row['user_to'] == $loggedInUser && $row['opened'] == 'no') {
			$openedMessage = 'no';
		} else {
			$openedMessage = "";
		}

		$userUnread = $this->db->query("
			SELECT * FROM messages WHERE opened = 'no' AND user_from = '$user2' AND user_to = '$loggedInUser'
		");
		$userUnreadNum = $userUnread->num_rows;

		if ($userUnreadNum > 0) {
			$userUnreadNum = $userUnreadNum;
		} else {
			$userUnreadNum = "";
		}


		//timezone
		$dateTimeNow = date("Y-m-d H:i:s");
		$dateTime = $row['date'];
		$postStartDate = new DateTime($dateTime); //time of post
		$endDate = new DateTime($dateTimeNow); //current time
		$interval = $postStartDate->diff($endDate); //difference between dates
		$newtime = strtotime($dateTime);
		if ($interval->y >= 1) {
			$timeMessage = date("F j, Y", $newtime);
		} else if ($interval->m >= 1) {
			$timeMessage = date("F j, Y", $newtime);
		} else if ($interval->d >=1) {
			if ($interval->d == 1) {
				$timeMessage = "Yesterday " . date("g:i a", $newtime);
			} else {
				$timeMessage = date("F j, Y", $newtime);
			}
		} else if ($interval->h >=1) {
			if ($interval->h == 1) {
				$timeMessage = $interval->h . " hr";
			} else {
				$timeMessage = $interval->h . " hr";
			}
		} else if ($interval->i >=1) {
			if ($interval->i == 1) {
				$timeMessage = $interval->i . " min";
			} else {
				$timeMessage = $interval->i . " min";
			}
		} else {
			if ($interval->s < 30) {
				$timeMessage = "Just now";
			} else {
				$timeMessage = $interval->s . " sec";
			}
		}

		array_push($detailsArray, $sentBy);
		array_push($detailsArray, $row['body']);
		array_push($detailsArray, $openedMessage);
		array_push($detailsArray, $timeMessage);
		array_push($detailsArray, $userUnreadNum);
		return $detailsArray;

	}

	public function getConvos() {
		$loggedInUser = $this->userObj->getUsername();
		$return_string = "";
		$convos = array();

		$query = $this->db->query("
			SELECT user_to, user_from FROM messages WHERE user_to = '$loggedInUser' OR user_from = '$loggedInUser' ORDER BY id DESC
		");

		while ($row = $query->fetch_assoc()) {
			$user_to_push = ($row['user_to'] != $loggedInUser) ? $row['user_to'] : $row['user_from'];
			if (!in_array($user_to_push, $convos)) {
				array_push($convos, $user_to_push);
			}
		}
		foreach($convos as $username) {
			$userFoundObj = new User($this->db, $username);
			$latestMessageDetails = $this->getLatestMessage($loggedInUser, $username);

			$dots = (strlen($latestMessageDetails[1]) >= 12) ? "..." : "";
			$split = str_split($latestMessageDetails[1], 12);
			$split = $split[0] . $dots;
			$messageURL = $this->filePath . "i/messages/$username";

			if ($latestMessageDetails[4] > 0) {
				$userUnread = "user-unread";
			} else {
				$userUnread = "";
			}

			$return_string .= "	<a class='convos-a' href='$messageURL'>
									<div class='convos-outer'>
										<img class='side-message-pic' src='" . $this->filePath . $userFoundObj->getProfilePic() . "'>
										<div class='user-found-messages'>
											" . $userFoundObj->getFirstAndLastName() .
											"<span class='timestamp_smaller' id='grey'>" . $latestMessageDetails[3] . "</span>
											<p id='grey' style='margin: 0;'>" . $latestMessageDetails[0] . $split . "</p>
										</div>
										<div class='$userUnread'>
											" . $latestMessageDetails[4] . "
										</div>
									</div>
								</a>
			";
		}

		return $return_string;

	}

}
