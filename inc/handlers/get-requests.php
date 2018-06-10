<?php
include '../db-connection.php';
include '../classes/user.php';
include '../classes/post.php';

if (isset($_SESSION['username'])) {
	$loggedInUser = $_SESSION['username'];
	$userDetails = $db->query("
		SELECT * FROM users WHERE username = '$loggedInUser'
	");
	$userRow = $userDetails->fetch_assoc();
} else {
	header("Location: register.php");
}

	$getRequests = $db->query("
		SELECT * FROM friend_requests WHERE user_to = '$loggedInUser'
	");

	if ($getRequests->num_rows > 0) {
		echo "<h3>Friend Requests</h3>
			  <div class='request-response'></div>
		";
		while ($requestsRow = $getRequests->fetch_assoc()) {
			$userFrom = $requestsRow['user_from'];
			$userFromObj = new User($db, $userFrom);

			$userFromFriendArray = $userFromObj->getFriendsArray();

			$getUserFromInfo = $db->query("
				SELECT first_name, last_name, profile_pic, friends FROM users WHERE username = '$userFrom' LIMIT 1
			");
			$friendInfo = $getUserFromInfo->fetch_assoc();
			$friendFriends = (substr_count($friendInfo['friends'], ",")) - 1;
			$friendFriends = max($friendFriends, 0);
			$friendFirstName = $friendInfo['first_name'];
			$friendLastName = $friendInfo['last_name'];
			$friendPicture = $friendInfo['profile_pic'];

			echo "
				<div id='requestFormDiv$userFrom'>
					<div id='friend-request requestForm$userFrom' class='friends-card-outer'>
						<div class='friends-card'>
							<div class='friends-card-img'><img src='$friendPicture'></div>
							<div class='friends-card-info-holder'>
								<div class='friends-card-info'>
									<div class='friends-card-name'><a href='$userFrom'>$friendFirstName $friendLastName</a></div>
									<div class='friends-card-username'>@$userFrom</div>
									<div class='friends-card-username'>$friendFriends Friends</div>
								</div>
							</div>
						</div>
						<form action='inc/handlers/submit-request.php' class='friend-request requestForm$userFrom' method='POST'>
							<button type='submit' class='accept-button' id='accept_button$userFrom'>
								Accept
								<input type='hidden' name='request_response' value='Accept'>
							</button>
							<button type='submit' class='ignore-button' id='ignore_button$userFrom'>
								Ignore
								<input type='hidden' name='request_response' value='Ignore'>
							</button>
							<input type='hidden' name='user_from' value='$userFrom'>
						</form>
					</div>
				</div>
			";

			?>

			<div>

			<script> // submits sidebar friend request action
			var loggedInUser = '<?php echo $loggedInUser; ?>';
			$('#accept_button<?php echo $userFrom; ?>').on('click', function(e) {
				var userFrom = "<?php echo $userFrom; ?>";
				var formData = 'user_from=' + userFrom + '&request_response=accept';
				var form = $('.requestForm' + userFrom);
				var requestResponse = "You are now friends with <?php echo $friendFirstName . ' ' . $friendLastName; ?>!";

				e.preventDefault();
				// Serialize the form data.

				// Submit the form using AJAX.
				$.ajax({
					type: 'POST',
					url: $(form).attr('action'),
					data: formData
				})
				.done(function(response) {
					$('#requestFormDiv' + userFrom).hide('slide',{direction:'right'},500);
					$('.request-response').delay(450).show("fast").text(requestResponse);


				})
				.fail(function(data) {

				});
			});

			$('#ignore_button<?php echo $userFrom; ?>').on('click', function(e) {
				var userFrom = "<?php echo $userFrom; ?>";
				var formData = 'user_from=' + userFrom + '&request_response=ignore';
				var form = $('.requestForm' + userFrom);
				var requestResponse = "You ignored <?php echo $friendFirstName . ' ' . $friendLastName; ?>'s friend requst.";

				e.preventDefault();
				// Serialize the form data.

				// Submit the form using AJAX.
				$.ajax({
					type: 'POST',
					url: $(form).attr('action'),
					data: formData
				})
				.done(function(response) {
					$('#requestFormDiv' + userFrom).hide('slide',{direction:'right'},500);
					$('.request-response').delay(450).show("fast").text(requestResponse);

				})
				.fail(function(data) {

				});
			});
			</script>
		</div>

			<?php

		}

	}
