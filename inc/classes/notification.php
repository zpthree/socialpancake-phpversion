<?php

class Notification
{
	private $userObj;
	private $db;

	public function __construct($db, $user) {
		$this->db = $db;
		$this->userObj = new User($this->db, $user);
		$this->filePath = "/";
	}

	public function getUnreadNumber() {
		$loggedInUser = $this->userObj->getUsername();
		$sql = $this->db->query("
			SELECT * FROM notifications WHERE viewed = 'no' AND user_to = '$loggedInUser'
		");
		return $sql->num_rows;
	}

	public function markNotificationRead() {
		$loggedInUser = $this->userObj->getUsername();
		$sql = $this->db->query("
			UPDATE notifications
			SET opened = 'yes'
			WHERE user_to = '$loggedInUser'
		");
	}

	public function getUnreadFollowerNumber() {
		$loggedInUser = $this->userObj->getUsername();
		$sql = $this->db->query("
			SELECT * FROM notifications WHERE viewed = 'no' AND user_to = '$loggedInUser' AND type = 'follow'
		");
		return $sql->num_rows;
	}

	public function getNotifications($data, $limit) {

		$page = $data['page'];
		$loggedInUser = $this->userObj->getUsername();
		$returnString = [];

		if ($page == 1) {
			$start = 0;
		} else {
			$start = ($page - 1) * $limit;
		}

		$setViewed = $this->db->query("
			UPDATE notifications SET viewed = 'yes' WHERE user_to = '$loggedInUser'
		");

		$query = $this->db->query("
			SELECT * FROM notifications WHERE user_to = '$loggedInUser' ORDER BY id DESC
		");

		if ($query->num_rows == 0) {
			echo "<div class='no-notifications'><img src='" . $this->filePath . "img/icons/empty.svg' alt='Empty Tray' draggable='false'><h4>Nothing to show!</h4></div>";
		}

		$numIterations = 0; //number of messages checked
		$count = 1; //number of messages posted

		while($row = $query->fetch_assoc()) {

			$dateTime = $row['datetime'];

			if ($row['type'] == 'comment') {
				$typeImg = "

					<svg class='notification-icon alert-comment' mlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink' viewBox='0 0 505.7 512'>
						<path d='M373.1,511c-12.6,0-25.3-2.3-37.8-6.8c-25.4-9.2-45.7-23.4-60.1-42.3l-0.3-0.4h-0.6
							c-7.8,0.6-14.8,0.9-21.5,0.9c-67.3,0-130.5-24-178.1-67.6C27.2,351.2,1,293.3,1,231.7S27.2,112.2,74.7,68.6
							C122.3,25,185.6,1,252.8,1c67.3,0,130.5,24,178.1,67.6c47.5,43.6,73.7,101.5,73.7,163.1c0,85.4-51.2,163.4-133.6,203.6l-0.9,0.4
							l0.3,0.9c3.4,9.2,7.5,18.5,12.3,27.6c10.5,20.2,33.2,30.8,40,33.6C417.3,501.2,399,511,373.1,511L373.1,511z'/>
						<path d='M252.8,2.1c33.9,0,66.7,6.1,97.7,18.1c29.9,11.6,56.7,28.1,79.7,49.2s41.1,45.6,53.7,73
							c13.1,28.3,19.7,58.3,19.7,89.3c0,42.4-12.7,83.8-36.8,119.8c-23.4,35-56.7,63.7-96.2,83l-1.7,0.8l0.7,1.8
							c3.4,9.2,7.6,18.5,12.3,27.7c9.9,19.1,30,29.6,38.6,33.3c-7,3.9-24.1,11.9-47.3,11.9c-12.5,0-25-2.3-37.4-6.8
							c-25.3-9.2-45.3-23.3-59.7-41.9l-0.7-0.9l-1.1,0.1c-7.8,0.6-14.8,0.9-21.4,0.9c-33.9,0-66.7-6.1-97.7-18.1
							c-29.9-11.6-56.7-28.1-79.7-49.3c-23-21.1-41.1-45.6-53.7-73C8.7,292.7,2.1,262.7,2.1,231.7s6.6-61,19.7-89.3
							c12.6-27.3,30.7-51.9,53.7-73s49.8-37.7,79.7-49.2C186.1,8.2,219,2.1,252.8,2.1 M252.8,0C113.2,0,0,103.7,0,231.7
							c0,127.9,113.2,231.7,252.8,231.7c7.3,0,14.4-0.3,21.5-0.9c12.4,16.1,31.3,32,60.6,42.6c13.9,5,26.8,6.9,38.2,6.9
							c31.7,0,52-14.3,52-14.3s-29-10.3-41.3-33.9c-5.1-9.9-9.1-19.1-12.2-27.5c79.8-39,134.1-116,134.1-204.6
							C505.7,103.7,392.5,0,252.8,0L252.8,0z'/>
					</svg>

				";
			} else if ($row['type'] == 'like') {
				$typeImg = "

					<svg class='notification-icon alert-like' version='1.1' xmlns='http://www.w3.org/2000/svg'viewBox='0 0 1024 1024'>
					<path d='M513.4,141c0,0,277.1-171.5,441.1,60.5c0,0,113.2,181-19.3,387.3c0,0-104.6,197.2-421.7,363.5
						c0,0-287.5-143.6-411.2-347.4c0,0-140.3-188.7-36.2-392.1C66.1,212.8,187-34.5,513.4,141z'/>
					</svg>

				";
			} else if ($row['type'] == 'share') {
				$typeImg = "
					<svg class='notification-icon alert-share' xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink'  viewBox='0 0 20 20'>
						<path d='M19.7,8.2l-6.6-6C12.6,1.7,12,2.2,12,3v3C7.3,6,3.3,8.9,1.4,12.8c-0.7,1.3-1.1,2.7-1.4,4.1
						c-0.2,1,1.3,1.5,1.9,0.6C4.1,14,7.8,11.7,12,11.7V15c0,0.8,0.6,1.3,1.1,0.8l6.6-6C20.1,9.4,20.1,8.6,19.7,8.2z'/>
					</svg>
				";
			} else if ($row['type'] == 'mention') {
				$typeImg = "
				 <svg class='notification-icon alert-mention' version='1.1' xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink' x='0px' y='0px'
					 viewBox='0 0 331.5 339.7' xml:space='preserve'>
					<path d='M241.9,84.8c-12.9-3.4-26.1,4.3-29.5,17.2l-0.1,0.4C199.8,89,182.7,81.7,164,81.7c-41.8,0-81,36.4-89.2,83
						c-4.5,25.6,1.3,50.3,16,67.8c12.8,15.2,30.8,23.6,51,23.6c14.8,0,29.3-4.6,42.2-12.6c1.3,2.3,2.9,4.5,4.9,6.5
						c20.2,20.6,63.9,8.1,76.9,3.8c39.3-13.1,65.8-54.2,65.8-102.2c0-51.6-17.7-90.1-60.9-121.2C241.5,9.4,205.7,0,169.9,0
						C76.2,0,0,76.2,0,169.8c0,45.2,15.5,90.3,48,122.6c32.3,32,77.1,47.3,121.8,47.3H189c45.2,0,81.5-17,95.9-27
						c10.9-7.6,13.6-22.7,5.9-33.6c-7.6-10.9-22.7-13.6-33.6-5.9c-6.8,4.7-33.8,18.3-68.2,18.3h-19.2c-33,0-65.8-11.5-88-33.7
						c-22.2-22.2-33.7-55-33.7-88c0-67.1,54.6-121.6,121.6-121.6c25.7,0,51.8,6.3,72.5,21.2c30.5,22,40.8,46.1,40.8,82.1
						c0,27.2-13.2,49.9-32.8,56.4c-6.4,2.1-12.2,3.5-17.2,4.3l25.8-98C262.5,101.4,254.8,88.2,241.9,84.8z M141.7,207.8
						c-5.8,0-10.5-2.1-14-6.3c-5.4-6.4-7.4-17.1-5.4-28.4c4-23,23.5-43.1,41.7-43.1c5.8,0,10.5,2.1,14,6.3c5.4,6.4,7.4,17.1,5.4,28.4
						C179.4,187.6,159.9,207.8,141.7,207.8z'/>
				</svg>

				";
			} else if ($row['type'] == 'follow' || $row['type'] == 'follow-request') {
				$typeImg = "

					<svg class='notification-icon alert-friends' version='1.1' viewBox='0 0 40 40' xml:space='preserve' xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink'><path d='M25.99,21.537l4.202-2.262c0.535,0.317,1.146,0.496,1.796,0.496  v0.002h0h0.001v-0.002c0.649,0,1.261-0.179,1.796-0.496l3.739,2.014c0.292,0.158,0.463,0.442,0.463,0.774l0,1.064  c0,0.484-0.395,0.88-0.879,0.88h-6.631l-4.486-2.415V21.537L25.99,21.537z M7.995,12.012L7.995,12.012L7.995,12.012H7.993l0,0  c-0.851,0-1.625,0.389-2.186,1.019c-0.572,0.641-0.926,1.527-0.926,2.51h0.001v0.001l0,0H4.881c0,0.982,0.354,1.869,0.925,2.509  c0.542,0.608,1.283,0.993,2.1,1.019l0.01,0.001H7.92h0.002h0.003h0.002h0h0.008h0.002h0.003h0.002h0.002H7.95l0.003,0.001h0.001  h0.001h0.002h0.003h0.003h0.003h0.002h0.003h0.003h0.002h0.003h0.002h0.003h0.002h0.002h0.002v-0.002h0h0.001v0.002h0.003h0.002  h0.003h0.002H8.01h0.003h0.002h0.003h0.003h0.002h0.003h0.002h0.003h0.002h0.001h0.001l0.003-0.001h0.003h0.002h0.003h0.002h0.003  h0.008h0h0.003h0.002H8.07h0.002l0.01-0.001c0.817-0.025,1.557-0.41,2.099-1.019c0.572-0.64,0.926-1.527,0.926-2.509h-0.002l0,0  v-0.002h0.002c0-0.98-0.354-1.867-0.927-2.509h0.001C9.62,12.4,8.846,12.012,7.995,12.012L7.995,12.012z M31.989,12.012  L31.989,12.012L31.989,12.012h-0.001l0,0c-0.851,0-1.625,0.389-2.186,1.019c-0.572,0.641-0.926,1.527-0.926,2.51h0.001v0.001l0,0  h-0.001c0,0.982,0.354,1.869,0.925,2.509c0.543,0.608,1.283,0.993,2.1,1.019l0.009,0.001h0.003h0.002h0.002h0.003h0h0.008h0.003  h0.002h0.003h0.002h0.003l0.003,0.001h0.001h0.001h0.002h0.003h0.002h0.003h0.002h0.003h0.003h0.002h0.003h0.003h0.002h0.002h0.002  h0.003v-0.002h0h0.001v0.002h0.003h0.002h0.002H32h0.002h0.003h0.002h0.003h0.003h0.002h0.003h0.003h0.002h0.003h0.001h0.001  l0.003-0.001h0.003h0.002h0.002h0.003h0.002h0.008h0h0.003h0.002h0.002h0.003l0.01-0.001c0.817-0.025,1.557-0.41,2.1-1.019  c0.572-0.64,0.925-1.527,0.925-2.509H35.1l0,0v-0.002h0.001c0-0.98-0.354-1.867-0.926-2.509h0.001  C33.614,12.4,32.84,12.012,31.989,12.012L31.989,12.012z M19.993,7.004v0.003h-0.001h-0.002V7.004c-1.844,0-3.52,0.845-4.737,2.209  c-1.239,1.387-2.005,3.309-2.005,5.436h0.003v0.002v0.003h-0.003c0,2.126,0.766,4.048,2.004,5.435  c1.218,1.364,2.895,2.209,4.739,2.209v-0.003h0.001h0.002v0.003c1.844,0,3.52-0.844,4.737-2.209  c1.239-1.387,2.005-3.309,2.005-5.436h-0.003V14.65v-0.003h0.003c0-2.124-0.768-4.046-2.007-5.435l0.001-0.001  C23.513,7.848,21.836,7.004,19.993,7.004L19.993,7.004z M8.151,27.023c-0.731,0.393-1.157,1.104-1.157,1.935v1.839  c0,1.211,0.988,2.198,2.199,2.198h21.595c1.211,0,2.2-0.987,2.2-2.198v-1.839c0-0.829-0.426-1.542-1.157-1.935l-7.948-4.28  c-2.422,1.436-5.363,1.437-7.786,0.001L8.151,27.023L8.151,27.023z M2.458,21.289l3.739-2.014c0.535,0.317,1.146,0.496,1.796,0.496  v0.002h0h0.001v-0.002c0.649,0,1.26-0.179,1.795-0.496l4.202,2.262v0.056l-4.486,2.415H2.875c-0.484,0-0.88-0.396-0.88-0.88v-1.064  C1.996,21.73,2.166,21.447,2.458,21.289z'/>
					</svg>

				";
			}

			if ($numIterations++ < $start) {
				continue;
			}

			if ($count++ > $limit) {
				break;
			}

			$userFrom = $row['user_from'];
			$sql = $this->db->query("
				SELECT * FROM users WHERE username = '$userFrom'
			");
			$userData = $sql->fetch_assoc();

			//timezone
			$dateTimeNow = date("Y-m-d H:i:s");
			$startDate = new DateTime($row['datetime']); //time of post
			$endDate = new DateTime($dateTimeNow); //current time
			$interval = $startDate->diff($endDate); //difference between dates
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

			$opened = $row['opened'];
			$style = ($row['opened'] == 'no') ? "unopened-notification" : "";

			$returnString[] = "<div class='notification-card $style'>
								<a href='" . $this->filePath . $row['link'] . "'>
									<div class='notifications-profile-pic'>
										<img src='" . $this->filePath . $userData['profile_pic'] . "'>
									</div>
									<div>
										<p>" . $row['message'] ."</p>
										<p class='timestamp-smaller' id='grey'>" . $typeImg . $timeMessage . "</p>
									</div>
								</a>
							</div>
			";
		}

		//if posts were loaded
		if ($count > $limit) {
			$returnString[] = "<input type='hidden' class='next-page-notifications' value='" . ($page + 1) . "'>
							  <input type='hidden' class='no-more-notifications' value='false'>";
		}

		foreach ($returnString as $notification) {
			echo $notification;
		}
	}

	public function insertNotification($postId, $userTo, $type) {
		$loggedInUser = $this->userObj->getUsername();
		$loggedInUserName = $this->userObj->getFirstAndLastName();
		$notificationType = "";

		switch($type) {

			case 'comment':
				$message = $loggedInUserName . " commented on your post.";
				$notificationType = "comment";
				$link = "i/post/" . $postId;
				break;

			case 'like':
				$message = $loggedInUserName . " liked on your post.";
				$notificationType = "like";
				$link = "i/post/" . $postId;
				break;

			case 'profile_post':
				$message = $loggedInUserName . " posted on your profile.";
				$notificationType = "mention";
				$link = "i/post/" . $postId;
				break;

			case 'comment_non_owner':
				$message = $loggedInUserName . " commented on a post you commented on.";
				$notificationType = "comment";
				$link = "i/post/" . $postId;
				break;

			case 'profile_comment':
				$message = $loggedInUserName . " commented on your profile post.";
				$notificationType = "comment";
				$link = "i/post/" . $postId;
				break;
			case 'following':
				$message = $loggedInUserName . " is now following you.";
				$notificationType = "follow";
				$link = $loggedInUser;
				break;
		}

		$insertNotification = $this->db->prepare("
			INSERT INTO notifications (user_to, user_from, notification_type, message, link, datetime, opened, viewed)
			VALUES (?, ?, ?, ?, ?, NOW(), 'no', 'no')
		");
		$insertNotification->bind_param('sssss', $userTo, $loggedInUser, $notificationType, $message, $link);
		$insertNotification->execute();
	}

}
