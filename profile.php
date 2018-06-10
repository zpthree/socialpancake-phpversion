<?php
$pageTitle = "profile";
include 'inc/header.php';

if (isset($_GET['profileUsername'])) {
	$username = $_GET ['profileUsername'];
}

if (isset($_GET['action'])) {
	$action = $_GET ['action'];
} else {
	$action = "";
}

if(isset($_POST['post_submit'])) {
	$post = new Post($db, $loggedInUser);
	$post->submitPost($_POST['post_text'], 'none');
}

$getUserDetails = $db->query("
	SELECT * FROM users WHERE username = '$username'
");

$getRequests = $db->query("
	SELECT * FROM friend_requests WHERE user_to = '$username'
");

if ($getUserDetails->num_rows > 0) {
	//profile user query
	$profileUserRow = $getUserDetails->fetch_assoc();

	//check if profile user is verified

	if ($profileUserRow['verified'] === 'no') {
		$profileUserRowVerified = '';
	} else {
		$profileUserRowVerified = "<img src='/img/icons/verified.svg' title='Verified User'>";
	}

	//logged in user query
	$getUserDetails = $db->query("
		SELECT * FROM users WHERE username = '$loggedInUser'
	");
	$loggedInUserRow = $getUserDetails->fetch_assoc();

	$numFolowing = (substr_count($loggedInUserRow['following'], ",")) - 1;
	$userObj = new User ($db, $username);
	$fullName = $userObj->getFirstAndLastName();
	$userPosts = $userObj->getNumPosts();
	$userLikes = $userObj->getNumLikes();

	$userFollowing = (substr_count($loggedInUserRow['following'], ",")) - 1;
	if ($userFollowing < 1) {
		$userFollowing = 0;
	}

	$getNumPosts = $db->query("
		SELECT id FROM posts WHERE added_by = '$username' AND deleted = 'no' AND shared_by = 'none'
	");
	$userPostsNum = $getNumPosts->num_rows;

	//get following number
	$getFollowingList = $db->query("
		SELECT following FROM users WHERE username = '$username'
	");
	$followingList = $getFollowingList->fetch_assoc();
	$followingList = explode(",", trim(implode(",", $followingList),","));
	$followingNum = count($followingList);

	//get number of followers
	$getFollowersList = $db->query("
		SELECT followers FROM users WHERE username = '$username'
	");
	$followersList = $getFollowersList->fetch_assoc();
	$followersList = explode(",", trim(implode(",", $followersList),","));
	$followersNum = count($followersList);

	$openedNotification = $db->query("
		UPDATE notifications SET opened = 'yes' WHERE user_to = '$loggedInUser' AND link LIKE '$username'
	");
?>

<div class="profile-page-outer">

	<div class="profile-top-bar-scroll">

	</div>

	<div class="profile-header-outer">

		<div class="profile-header">

			<?php if (!isset($_GET['action']) || $action === 'images' || $action === 'liked-posts' || $action === 'deleted-posts') { ?>

			<div class="profile-header-img">

				<div class="mobile-user-profile-image">
					<a id='mobile-profile-link' href="<?php echo "/" . $username; ?>">
						<img src="<?php echo "/" . $profileUserRow['profile_pic']; ?>" alt="<?php echo $profileUserRow['username']; ?>'s Profile Picture">
						<div class="user-profile-name">

							<div class="profile-user-fullname">
								<h5><?php echo $profileUserRow['first_name'] . ' '  . $profileUserRow['last_name']; ?></h5>
								<?php

								 echo $profileUserRowVerified;
								 if ($username !== $loggedInUser) {

								?>

								<form action="/inc/handlers/friend-action.php" method="POST" class="profile-action profile-friend-action-<?php echo $username; ?>">
									<?php

									$profileUserObj = new User($db, $username);
									if ($profileUserObj->isClosed()) {
										//action if user is closed
									}

									$loggedInUserObj = new User($db, $loggedInUser);

									if ($loggedInUserObj->isFollowing($username)) {
										echo '<input type="submit" name="remove_friend" class="delete friend-options-action" value="Unfollow">';
									} else if ($loggedInUserObj->didReceiveRequest($username)) {
										echo '<input type="submit" name="respond_request" class="respond friend-options-action" value="Respond">';
									} else if ($loggedInUserObj->didSendRequest($username)) {
										echo '<input type="submit" name="" class="following friend-options-action" value="Request Sent">';
									} else {
										echo '<input type="submit" name="add_friend" class="add friend-options-action" value="Follow">';
									}
								?>
								</form>

							<?php } ?>

							</div>
							<p>@<?php echo $profileUserRow['username']; ?></p>

						</div>
					</a>
				</div>

			</div>

			<?php } ?>

			<div class="user-card-info">

				<ul>
					<div class="user-profile-image">
						<a id='user-profile-link' href="<?php echo "/" . $username; ?>">
							<img src="<?php echo "/" . $profileUserRow['profile_pic']; ?>" alt="<?php echo $profileUserRow['username']; ?>'s Profile Picture">
							<div class="user-profile-name">

								<div class="profile-user-fullname">
									<h5><?php echo $profileUserRow['first_name'] . ' '  . $profileUserRow['last_name']; ?></h5>
									<?php

									 echo $profileUserRowVerified;
									 if ($username !== $loggedInUser) {

									?>

									<form action="/inc/handlers/friend-action.php" method="POST" class="profile-action profile-friend-action-<?php echo $username; ?>">
										<?php

										$profileUserObj = new User($db, $username);
										if ($profileUserObj->isClosed()) {
											//action if user is closed
										}

										$loggedInUserObj = new User($db, $loggedInUser);

										if ($loggedInUserObj->isFollowing($username)) {
											echo '<input type="submit" name="remove_friend" class="delete friend-options-action" value="Unfollow">';
										} else if ($loggedInUserObj->didReceiveRequest($username)) {
											echo '<input type="submit" name="respond_request" class="respond friend-options-action" value="Respond">';
										} else if ($loggedInUserObj->didSendRequest($username)) {
											echo '<input type="submit" name="" class="following friend-options-action" value="Request Sent">';
										} else {
											echo '<input type="submit" name="add_friend" class="add friend-options-action" value="Follow">';
										}
									?>
									</form>

								<?php } ?>

								</div>
								<p>@<?php echo $profileUserRow['username']; ?></p>

							</div>
						</a>
					</div>
					<a class='desktop-only' href="<?php echo "/" . $username; ?>" <?php if (!isset($_GET['action']) || $_GET['action'] === 'images' || $_GET['action'] === 'liked-posts' || $_GET['action'] === 'deleted-posts') { echo "class='active-profile'";} ?>><li class="user-card-posts"><h4>Posts</h4><p><?php echo $userPostsNum; ?></p></li></a>
					<a class='desktop-only' href="<?php echo "/" . $username . "/". "following" ;?>" <?php if (isset($_GET['action']) && $_GET['action'] == "following") { echo "class='active-profile'";} ?>><li class="user-card-following"><h4>Following</h4><p><?php echo $followingNum; ?></p></li></a>
					<a class='desktop-only' href="<?php echo "/" . $username . "/". "followers" ;?>" <?php if (isset($_GET['action']) && $_GET['action'] == "followers") { echo "class='active-profile'";} ?>><li class="user-card-followers"><h4>Followers</h4><p><?php echo $followersNum; ?></p></li></a>
				</ul>

			</div>

		</div>

	</div>

	<div class="profile-page-inner">

	<?php if (!isset($_GET['action']) || $action === 'images' || $action === 'liked-posts' || $action === 'deleted-posts') { ?>

			<div class="left-side-panels <?php if (isset($_GET['action']) && ($action !== 'images' && $action !== 'liked-posts' && $action !== 'deleted-posts')) { echo 'profile-left-hide'; } ?>">

				<?php

				if ($loggedInUser === $username && $getRequests->num_rows > 0) {
					echo "<div class='user-follow-requests-outer'>
									<div class='user-follow-requests'>

										<a href='/" . $loggedInUser . "/requests'>You have " . $getRequests->num_rows . " follower requests</a>

									</div>
								</div>";
				}

				?>

				<?php if ((!empty($profileUserRow['bio']) && $profileUserRow['bio'] !== 'none') || $username === $loggedInUser) { ?>

				<div class="user-bio">
					<img class="quote-icons" src="/img/icons/quotes.svg" alt="">
					<p class=''>
						<?php
							if (!empty($profileUserRow['bio']) && $profileUserRow['bio'] !== 'none') {
								echo "&quot;" . $profileUserRow['bio'] . "&quot";
							} else {
								echo "You haven't made a bio yet. You can update your bio in the <a href='/i/settings'>Settings Page</a>.";
							}
						?>
					</p>
				</div>

				<?php } ?>

				<?php if ($username === 'zpthree') { ?>

				<div class="web-links">

					<h4>Connect with me</h4>

					<div class="web-icons">

						<a href="http://www.twitter.com/zpthree" target='_blank'>
							<img src="/img/icons/zp/twitter.svg" alt="Twitter" title="Twitter">
						</a>
						<a href="http://www.facebook.com/zpthree" target='_blank'>
							<img src="/img/icons/zp/facebook.svg" alt="Facebook" title="Facebook">
						</a>
						<a href="http://www.instagram.com/zpthree" target='_blank'>
							<img src="/img/icons/zp/instagram.svg" alt="Instagram" title="Instagram">
						</a>
						<a href="https://www.linkedin.com/in/zach-patrick-5252157b" target='_blank'>
							<img src="/img/icons/zp/linkedin.svg" alt="LinkedIn" title="LinkedIn">
						</a>
						<a href="http://www.zachpatrick.com" target='_blank'>
							<img src="/img/icons/zp/website.svg" alt="Personal Website" title="Personal Website">
						</a>

					</div>

				</div>

				<?php } ?>

				<?php if ($username !== $loggedInUser) { ?>

				<div class="profile-nav">

					<div class="friend-action">

							<?php

								if ($loggedInUser != $username) {

									$followersYouFollow = 0;
									//get array of users loggedInUser is following
									$userFollowing = $loggedInUserRow['following'];
									$userFollowingArray = explode(",", $userFollowing);
									//get array of users userToCheck is following
									$query = $db->query("
										SELECT followers FROM users WHERE username = '$username'
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

									if (count($followersYouFollow) > 2) {

										shuffle($mutualFollowing);


										$i = 0;
										$recommendedNum = 8;

									?>
										<div class="mutual-friends">

											<!-- LINK MUTUAL FRIENDS LINK href="<?php echo "/" . $username; ?>/followers-you-follow" -->

											<p><?php echo $profileUserRow['first_name']; ?> has <?php echo count($mutualFollowing); ?> followers you follow.</p>

											<div class="mutual-friends-display">
											<?php

											foreach ($mutualFollowing as $mutual) {

												if ($mutual !== ',' && $mutual !== '') {

													$getMutualUserDetails = $db->query("
														SELECT * FROM users WHERE username = '$mutual'
													");
													$mutualFollowingDetails = $getMutualUserDetails->fetch_assoc();

													echo "<a href='" . "/" . $mutualFollowingDetails['username'] . "'><div class='mutual-friend'>
															<div class='mutual-friend-img'><img src='/" . $mutualFollowingDetails['profile_pic'] . "'></div>
															</div></a>";

												}

												$i++;
												if ($i == $recommendedNum) break;

											}



										?>
										</div>

									</div>

								<?php

										}

									}

								?>
					</div>

				</div>

				<?php } ?>

				<div class="left-side-panels left-right-side">
					<?php

						echo $recommendedFollows;

					?>
				</div>

				<div class="left-trending-side"></div>

			</div>

			<div class="main-columns">

				<div class='mobile-only'>

					<?php if ((!empty($profileUserRow['bio']) && $profileUserRow['bio'] !== 'none') || $username === $loggedInUser) { ?>

					<div class="user-bio"> <!-- MOBILE BIO -->
						<p class=''>
							<?php
								if (!empty($profileUserRow['bio']) && $profileUserRow['bio'] !== 'none') {
									echo "&quot;" . $profileUserRow['bio'] . "&quot";
								} else {
									echo "You haven't made a bio yet. You can update your bio in the <a href='/i/settings'>Settings Page</a>.";
								}
							?>
						</p>
					</div>
					<?php } ?>

					<?php if ($username === 'zpthree') { ?>

					<div class="web-links">

						<div class="web-icons">

							<a href="http://www.twitter.com/zpthree" target='_blank'>
								<img src="/img/icons/zp/twitter.svg" alt="Twitter" title="Twitter">
							</a>
							<a href="http://www.facebook.com/zpthree" target='_blank'>
								<img src="/img/icons/zp/facebook.svg" alt="Facebook" title="Facebook">
							</a>
							<a href="http://www.instagram.com/zpthree" target='_blank'>
								<img src="/img/icons/zp/instagram.svg" alt="Instagram" title="Instagram">
							</a>
							<a href="https://www.linkedin.com/in/zach-patrick-5252157b" target='_blank'>
								<img src="/img/icons/zp/linkedin.svg" alt="LinkedIn" title="LinkedIn">
							</a>
							<a href="http://www.zachpatrick.com" target='_blank'>
								<img src="/img/icons/zp/website.svg" alt="Personal Website" title="Personal Website">
							</a>

						</div>

					</div>

					<?php } ?>

					<div class="user-card-info mobile-user-card">
						<ul>
							<a class='mobile-only' href="<?php echo "/" . $username . "/". "following" ;?>" <?php if (isset($_GET['action']) && $_GET['action'] == "following") { echo "class='active-profile'";} ?>><li class="user-card-following"><h4>Following&nbsp;<?php echo $followingNum; ?></h4></li></a>
							<a class='mobile-only' href="<?php echo "/" . $username . "/". "followers" ;?>" <?php if (isset($_GET['action']) && $_GET['action'] == "followers") { echo "class='active-profile'";} ?>><li class="user-card-followers"><h4>Followers&nbsp;<?php echo $followersNum; ?></h4></li></a>
						</ul>
					</div>

				</div>

				<div class="newsfeed-header">

					<div class="newsfeed-header-post" data-toggle="modal" data-target="#post_form">

						<p class='desktop-only'>New Post</p>
						<div class="header-new-post"> <!-- new post icon -->

								<svg class="post-icons nav-icons" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
									 viewBox="0 0 600 600" xml:space="preserve">
									<rect idth="600" height="600"/>
									<path d="M74,44h230c8.8,0,16,7.2,16,16v28c0,8.8-7.2,16-16,16H104v392h392V296.3c0-8.8,7.2-16,16-16h28
										c8.8,0,16,7.2,16,16V526c0,16.6-13.4,30-30,30H74c-16.6,0-30-13.4-30-30V74C44,57.4,57.4,44,74,44L74,44z"/>
										<path d="M501.6,153.3L257.3,397.5l-56.6-56.6L445,96.7L501.6,153.3L501.6,153.3z M242,412.9l-0.2,0.2
											l0,0l0,0l-38.6,10.4l-23.8,6.4c-3.2,0.8-6.3,0-8.6-2.3c-2.3-2.3-3.1-5.4-2.3-8.6l6.4-23.8l10.4-38.6l0,0l0,0l0.2-0.2L242,412.9
											L242,412.9z"/>
										<path d="M513.4,46.1l38.8,38.8c4.6,4.6,4.6,12.1,0,16.6l-35.8,35.8L461,81.9l35.8-35.8
											C501.4,41.5,508.9,41.5,513.4,46.1z"/>
								</svg>

						</div>

					</div>

					<div class="feed-options">

						<p class='desktop-only'>Feed Options</p>
						<a href="<?php echo "/" . $username; ?>">
							<img class="<?php if (empty($action)) echo 'active'; ?>" src="/img/icons/following.svg" alt="Following" title="All User Posts">
						</a>

						<a href="<?php echo "/" . $username; ?>/images">
							<img class="<?php if ($action == 'images') echo 'active'; ?>" src="/img/icons/if_Img_257666.svg" alt="Following" title="Pictures">
						</a>

						<a href="<?php echo "/" . $username; ?>/liked-posts">
							<img class="<?php if ($action == 'liked-posts') echo 'active'; ?>" src="/img/icons/circle-like.svg" alt="Verified" title="Liked Posts">
						</a>

						<?php if ($loggedInUser === $username) { ?>

						<a href="<?php echo "/" . $username; ?>/deleted-posts">
							<img class="<?php if ($action == 'deleted-posts') echo 'active'; ?>" src="/img/icons/trash.svg" alt="Favorite" title="Deleted Posts">
						</a>

						<?php } ?>

					</div>

				</div>

				<div class="feed ">
					<div class="profile-posts-area">
					</div>
				</div>

			</div>

			<div class="right-side-panels">
				<?php

					echo $recommendedFollows;

				?>
			</div>

	<?php } else if ($_GET['action'] === 'following') { ?>

		<div class="friends">
			<?php
			$getfollowingList = $db->query("
				SELECT following FROM users WHERE username = '$username'
			");
			$followingList = $getfollowingList->fetch_assoc();
			$followingList = explode(",", trim(implode(",", $followingList),","));
			natcasesort($followingList);

			if (count($followingList) > 0) {

				echo "<div class='page-friends-list'>
				";

				$followingList = explode(",", trim(implode(",", $followingList),","));
				foreach ($followingList as $following) {
					$getfollowingInfo = $db->query("
						SELECT first_name, last_name, profile_pic, following, verified FROM users WHERE username = '$following'
					");
					$followingInfo = $getfollowingInfo->fetch_assoc();
					$followingFriends = (substr_count($followingInfo['following'], ",")) - 1;
					$followingFirstName = $followingInfo['first_name'];
					$followingLastName = $followingInfo['last_name'];
					$userFullName = $followingInfo['first_name'] . " " . $followingInfo['last_name'];
					$followingPicture = $followingInfo['profile_pic'];
					if ($followingInfo['verified'] == 'yes') {
						$verifiedPic = "/img/icons/verified.svg";
						$verified = 'verified';
					} else {
						$verifiedPic = "";
						$verified = "";
					}

					$loggedInUserObj = new User($db, $loggedInUser);
					$friendAction = '<form action="/inc/handlers/friend-action.php" method="POST" class="friend-action" id="friend-action-' . $following . '">';
					if ($loggedInUserObj->isFollowing($following)) {
						$friendAction .= '<input type="submit" name="remove_friend" class="delete friend-options-action" value="Unfollow">';
					} else if ($loggedInUserObj->didReceiveRequest($following)) {
						$friendAction .= '<input type="submit" name="respond_request" class="respond friend-options-action" value="Respond">';
					} else if ($loggedInUserObj->didSendRequest($following)) {
						$friendAction .= '<input type="submit" name="" class="following friend-options-action" value="Following">';
					} else {
						$friendAction .= '<input type="submit" name="add_friend" class="add friend-options-action" value="Follow">';
					}
					$friendAction .= '</form>';

					echo "<div class='friends-card $following-card'>
									<div class='friends-card-info'>
										<img class='friends-card-img' src='/$followingPicture' alt='Picture of $following'>
										<div class='friends-card-name'>
											<a href='/$following' class='post-name'>$followingFirstName $followingLastName<img class='$verified' src='$verifiedPic'></a><span>@$following</span>
										</div>
										<div class='friend-button btn-group'>
											$friendAction
										</div>
									</div>
								</div>

					";

					?>

					<script>
						$('#<?php echo $following ?>-button').on('click', function () {
							if ($('#<?php echo $following; ?>-options').css('display') != 'none') {
								$('#<?php echo $following; ?>-options').hide();
								$('#<?php echo $following; ?>-options').css('display', 'none');
								$('.<?php echo $following; ?>-card').removeClass("primary-color-bg");
							} else {
								$('#<?php echo $following; ?>-options').show();
								$('#<?php echo $following; ?>-options').css('display', 'flex');
								$('.<?php echo $following; ?>-card').addClass("primary-color-bg");
							}
						});
					</script>
					<script>
						$('#<?php echo $following ?>-post').on('click', function () {
							$('#post-textarea').text("@<?php echo $following; ?> ");
						});
					</script>
					<script> //friend action button (add, remove, etc.)
						$('#friend-action-<?php echo $following; ?>').submit(function(e) {
							var loggedInUser = '<?php echo $loggedInUser; ?>';
							var filePath = '/';
							var userFullName = '<?php echo $userFullName; ?>';
							var username = '<?php echo $following; ?>';
							var action = "";

							if ($("#friend-action-<?php echo $following; ?> input").hasClass('delete')) {
								action = 'delete';
							} else if ($("#friend-action-<?php echo $following; ?> input").hasClass('respond')) {
								window.location.replace(filePath + loggedInUser + "/requests");
								action = 'respond';
							} else if ($("#friend-action-<?php echo $following; ?> input").hasClass('sent')) {
								action = 'sent';
							} else if ($("#friend-action-<?php echo $following; ?> input").hasClass('add')) {
								action = 'add';
							}

							e.preventDefault();
							// Submit the form using AJAX.
							$.ajax({
								type: 'POST',
								url: $('#friend-action-<?php echo $following; ?>').attr('action'),
								data: "loggedInUser=" + loggedInUser + "&username=" + username + "&action=" + action + "&userFullName=" + userFullName
							})
							.done(function(response) {
								if (action == 'add') {
									$('#friend-action-<?php echo $following; ?> input').val("Unfollow");
									$('#friend-action-<?php echo $following; ?> input').removeClass("add");
									$('#friend-action-<?php echo $following; ?> input').addClass("delete");
									<?php $action = "sent"; ?>;
								} else if (action == 'delete') {
									$('#friend-action-<?php echo $following; ?> input').val("Follow");
									$('#friend-action-<?php echo $following; ?> input').removeClass("delete");
									$('#friend-action-<?php echo $following; ?> input').addClass("add");
								}
							})
							.fail(function(data) {

							});

						});
					</script>

					<?php

				}
			} else {
				echo "<div class='page-friends-list'>
						<div class='no-friends'>
									<h1>You don't have any friends yet!</h1>
								</div>
						";
			}

			echo "</div>";

			?>
		</div>

		</div>

		<?php } else if ($_GET['action'] === 'followers') { ?>

			<div class="friends">
				<?php
				$getFollowersList = $db->query("
					SELECT followers FROM users WHERE username = '$username'
				");
				$followersList = $getFollowersList->fetch_assoc();
				$followersList = explode(",", trim(implode(",", $followersList),","));
				natcasesort($followersList);

				if (count($followersList) > 0) {

					echo "<div class='page-friends-list'>
					";

					$followersList = explode(",", trim(implode(",", $followersList),","));
					foreach ($followersList as $followers) {
						$getFollowersInfo = $db->query("
							SELECT first_name, last_name, profile_pic, followers, verified FROM users WHERE username = '$followers'
						");
						$followersInfo = $getFollowersInfo->fetch_assoc();
						$followersFriends = (substr_count($followersInfo['followers'], ",")) - 1;
						$followersFirstName = $followersInfo['first_name'];
						$followersLastName = $followersInfo['last_name'];
						$userFullName = $followersInfo['first_name'] . " " . $followersInfo['last_name'];
						$followersPicture = $followersInfo['profile_pic'];
						if ($followersInfo['verified'] == 'yes') {
							$verifiedPic = $filePath . "img/icons/verified.svg";
							$verified = 'verified';
						} else {
							$verifiedPic = "";
							$verified = "";
						}

						$loggedInUserObj = new User($db, $loggedInUser);
						$friendAction = '<form action="' . $filePath . 'inc/handlers/friend-action.php" method="POST" class="friend-action" id="friend-action-' . $followers . '">';
						if ($loggedInUserObj->isFollowing($followers)) {
							$friendAction .= '<input type="submit" name="remove_friend" class="delete friend-options-action" value="Unfollow">';
						} else if ($loggedInUserObj->didReceiveRequest($followers)) {
							$friendAction .= '<input type="submit" name="respond_request" class="respond friend-options-action" value="Respond">';
						} else if ($loggedInUserObj->didSendRequest($followers)) {
							$friendAction .= '<input type="submit" name="" class="following friend-options-action" value="Following">';
						} else {
							$friendAction .= '<input type="submit" name="add_friend" class="add friend-options-action" value="Follow">';
						}
						$friendAction .= '</form>';

						echo "<div class='friends-card $followers-card'>
										<div class='friends-card-info'>
											<img class='friends-card-img' src='$filePath$followersPicture' alt='Picture of $followers'>
											<div class='friends-card-name'>
												<a href='$filePath$followers' class='post-name'>$followersFirstName $followersLastName<img class='$verified' src='$verifiedPic'></a><span>@$followers</span>
											</div>
											<div class='friend-button btn-group'>
												$friendAction
											</div>
										</div>
									</div>

						";

						?>

						<script>
							$('#<?php echo $followers ?>-button').on('click', function () {
								if ($('#<?php echo $followers; ?>-options').css('display') != 'none') {
									$('#<?php echo $followers; ?>-options').hide();
									$('#<?php echo $followers; ?>-options').css('display', 'none');
									$('.<?php echo $followers; ?>-card').removeClass("primary-color-bg");
								} else {
									$('#<?php echo $followers; ?>-options').show();
									$('#<?php echo $followers; ?>-options').css('display', 'flex');
									$('.<?php echo $followers; ?>-card').addClass("primary-color-bg");
								}
							});
						</script>
						<script>
							$('#<?php echo $followers ?>-post').on('click', function () {
								$('#post-textarea').text("@<?php echo $followers; ?> ");
							});
						</script>
						<script> //friend action button (add, remove, etc.)
							$('#friend-action-<?php echo $followers; ?>').submit(function(e) {
								var loggedInUser = '<?php echo $loggedInUser; ?>';
								var filePath = '/';
								var userFullName = '<?php echo $userFullName; ?>';
								var username = '<?php echo $followers; ?>';
								var action = "";

								if ($("#friend-action-<?php echo $followers; ?> input").hasClass('delete')) {
									action = 'delete';
								} else if ($("#friend-action-<?php echo $followers; ?> input").hasClass('respond')) {
									window.location.replace(filePath + loggedInUser + "/requests");
									action = 'respond';
								} else if ($("#friend-action-<?php echo $followers; ?> input").hasClass('sent')) {
									action = 'sent';
								} else if ($("#friend-action-<?php echo $followers; ?> input").hasClass('add')) {
									action = 'add';
								}

								e.preventDefault();
								// Submit the form using AJAX.
								$.ajax({
									type: 'POST',
									url: $('#friend-action-<?php echo $followers; ?>').attr('action'),
									data: "loggedInUser=" + loggedInUser + "&username=" + username + "&action=" + action + "&userFullName=" + userFullName
								})
								.done(function(response) {
									if (action == 'add') {
										$('#friend-action-<?php echo $followers; ?> input').val("Unfollow");
										$('#friend-action-<?php echo $followers; ?> input').removeClass("add");
										$('#friend-action-<?php echo $followers; ?> input').addClass("delete");
										<?php $action = "sent"; ?>;
									} else if (action == 'delete') {
										$('#friend-action-<?php echo $followers; ?> input').val("Follow");
										$('#friend-action-<?php echo $followers; ?> input').removeClass("delete");
										$('#friend-action-<?php echo $followers; ?> input').addClass("add");
									}
								})
								.fail(function(data) {

								});

							});
						</script>

						<?php

					}
				} else {
					echo "<div class='page-friends-list'>
							<div class='no-friends'>
					        	<h1>You don't have any friends yet!</h1>
					        </div>
			        ";
				}

				echo "</div>";

				?>
			</div>

		</div>

		<?php } else if ($_GET['action'] === 'requests') { ?>

			<div class="friends">
				<?php

					if ($getRequests->num_rows > 0) {

						echo "<div class='page-friends-list'>
						";

						while($requests = $getRequests->fetch_assoc()) {
							$requestFrom = $requests['user_from'];
							$requestTo = $requests['user_to'];

							$getRequestInfo = $db->query("
								SELECT first_name, last_name, profile_pic, followers, verified FROM users WHERE username = '$requestFrom'
							");
							$requestInfo = $getRequestInfo->fetch_assoc();

							$requestFirstName = $requestInfo['first_name'];
							$requestLastName = $requestInfo['last_name'];
							$userFullName = $requestInfo['first_name'] . " " . $requestInfo['last_name'];
							$requestPicture = $requestInfo['profile_pic'];
							if ($requestInfo['verified'] == 'yes') {
								$verifiedPic = $filePath . "img/icons/verified.svg";
								$verified = 'verified';
							} else {
								$verifiedPic = "";
								$verified = "";
							}

							$loggedInUserObj = new User($db, $loggedInUser);
							$friendAction = '<form action="' . $filePath . 'inc/handlers/friend-action.php" method="POST" id="friend-action-' . $requestFrom . '" class="friend-action">';
							if ($loggedInUserObj->didReceiveRequest($requestFrom)) {
								$friendAction .= '<input type="submit" name="accept_request" id="friend_action_accept-' . $requestFrom . '" class="add friend-options-action" value="Accept">';
								$friendAction .= '<input type="submit" name="ignore_request" id="friend_action_delete-' . $requestFrom . '" class="delete friend-options-action" value="Decline">';
							}
							$friendAction .= '</form>';

							echo "<div class='friends-card $requestFrom-card'>
											<div class='friends-card-info'>
												<img class='friends-card-img' src='$filePath$requestPicture' alt='Picture of $requestFrom'>
												<div class='friends-card-name'>
													<a href='$filePath$requestFrom' class='post-name'>$requestFirstName $requestLastName<img class='$verified' src='$verifiedPic'></a><span>@$requestFrom</span>
												</div>
												<div class='friend-button btn-group'>
													$friendAction
												</div>
											</div>
										</div>

							";

				?>

				<script>
					$('#<?php echo $requestFrom; ?>-button').on('click', function () {
						if ($('#<?php echo $requestFrom; ?>-options').css('display') != 'none') {
							$('#<?php echo $requestFrom; ?>-options').hide();
							$('#<?php echo $requestFrom; ?>-options').css('display', 'none');
							$('.<?php echo $requestFrom; ?>-card').removeClass("primary-color-bg");
						} else {
							$('#<?php echo $requestFrom; ?>-options').show();
							$('#<?php echo $requestFrom; ?>-options').css('display', 'flex');
							$('.<?php echo $requestFrom; ?>-card').addClass("primary-color-bg");
						}
					});
				</script>
				<script>
					$('#<?php echo $requestFrom; ?>-post').on('click', function () {
						$('#post-textarea').text("@<?php echo $requestFrom; ?> ");
					});
				</script>
				<script> //friend action button (add, remove, etc.)
					$('#friend_action_delete-<?php echo $requestFrom; ?>').on('click', function(e) {
						var loggedInUser = '<?php echo $loggedInUser; ?>';
						var filePath = '/';
						var userFullName = '<?php echo $userFullName; ?>';
						var username = '<?php echo $requestFrom; ?>';
						var action = "ignore-request";

						e.preventDefault();

						$.ajax({
							type: 'POST',
							url: filePath + 'inc/handlers/friend-action.php',
							data: "loggedInUser=" + loggedInUser + "&username=" + username + "&action=" + action + "&userFullName=" + userFullName,
							success: function(response) {
								$('.<?php echo $requestFrom; ?>-card').hide();
							}
						});

					});
					</script>

					<script> //friend action button (add, remove, etc.)
						$('#friend_action_accept-<?php echo $requestFrom; ?>').on('click', function(e) {
							var loggedInUser = '<?php echo $loggedInUser; ?>';
							var filePath = '/';
							var userFullName = '<?php echo $userFullName; ?>';
							var username = '<?php echo $requestFrom; ?>';
							var action = "accept-request";

							e.preventDefault();

							$.ajax({
								type: 'POST',
								url: filePath + 'inc/handlers/friend-action.php',
								data: "loggedInUser=" + loggedInUser + "&username=" + username + "&action=" + action + "&userFullName=" + userFullName,
								success: function(response) {
									$('.<?php echo $requestFrom; ?>-card').hide();
								}
							});

						});
						</script>

				<?php

				}

				} else {
					echo "<div class='page-friends-list'>
							<div class='no-friends'>
					        	<h1>There aren't any current requests to follow you.</h1>
					        </div>
			        ";
				}

				echo "</div>";

				?>
			</div>

		</div>

		<?php } else if ($_GET['action'] === 'followers-you-follow') { ?>

			<div class="friends">
				<?php

					if ($getRequests->num_rows > 0) {

						echo "<div class='page-friends-list'>
						";

						while($requests = $getRequests->fetch_assoc()) {
							$requestFrom = $requests['user_from'];
							$requestTo = $requests['user_to'];

							$getRequestInfo = $db->query("
								SELECT first_name, last_name, profile_pic, followers, verified FROM users WHERE username = '$requestFrom'
							");
							$requestInfo = $getRequestInfo->fetch_assoc();

							$requestFirstName = $requestInfo['first_name'];
							$requestLastName = $requestInfo['last_name'];
							$userFullName = $requestInfo['first_name'] . " " . $requestInfo['last_name'];
							$requestPicture = $requestInfo['profile_pic'];
							if ($requestInfo['verified'] == 'yes') {
								$verifiedPic = $filePath . "img/icons/verified.svg";
								$verified = 'verified';
							} else {
								$verifiedPic = "";
								$verified = "";
							}

							$loggedInUserObj = new User($db, $loggedInUser);
							$friendAction = '<form action="' . $filePath . 'inc/handlers/friend-action.php" method="POST" id="friend-action-' . $requestFrom . '">';
							if ($loggedInUserObj->didReceiveRequest($requestFrom)) {
								$friendAction .= '<input type="submit" name="accept_request" id="friend_action_accept-' . $requestFrom . '" class="add friend-options-action" value="Accept">';
								$friendAction .= '<input type="submit" name="ignore_request" id="friend_action_delete-' . $requestFrom . '" class="delete friend-options-action" value="Decline">';
							}
							$friendAction .= '</form>';

							echo "<div class='friends-card $requestFrom-card'>
											<div class='friends-card-info'>
												<img class='friends-card-img' src='$filePath$requestPicture' alt='Picture of $requestFrom'>
												<div class='friends-card-name'>
													<a href='$filePath$requestFrom' class='post-name'>$requestFirstName $requestLastName<img class='$verified' src='$verifiedPic'></a><span>@$requestFrom</span>
												</div>
												<div class='friend-button btn-group'>
													$friendAction
												</div>
											</div>
										</div>

							";

				?>

				<script>
					$('#<?php echo $requestFrom; ?>-button').on('click', function () {
						if ($('#<?php echo $requestFrom; ?>-options').css('display') != 'none') {
							$('#<?php echo $requestFrom; ?>-options').hide();
							$('#<?php echo $requestFrom; ?>-options').css('display', 'none');
							$('.<?php echo $requestFrom; ?>-card').removeClass("primary-color-bg");
						} else {
							$('#<?php echo $requestFrom; ?>-options').show();
							$('#<?php echo $requestFrom; ?>-options').css('display', 'flex');
							$('.<?php echo $requestFrom; ?>-card').addClass("primary-color-bg");
						}
					});
				</script>
				<script>
					$('#<?php echo $requestFrom; ?>-post').on('click', function () {
						$('#post-textarea').text("@<?php echo $requestFrom; ?> ");
					});
				</script>
				<script> //friend action button (add, remove, etc.)
					$('#friend_action_delete-<?php echo $requestFrom; ?>').on('click', function(e) {
						var loggedInUser = '<?php echo $loggedInUser; ?>';
						var filePath = '/';
						var userFullName = '<?php echo $userFullName; ?>';
						var username = '<?php echo $requestFrom; ?>';
						var action = "ignore-request";

						e.preventDefault();

						$.ajax({
							type: 'POST',
							url: filePath + 'inc/handlers/friend-action.php',
							data: "loggedInUser=" + loggedInUser + "&username=" + username + "&action=" + action + "&userFullName=" + userFullName,
							success: function(response) {
								$('.<?php echo $requestFrom; ?>-card').hide();
							}
						});

					});
					</script>

					<script> //friend action button (add, remove, etc.)
						$('#friend_action_accept-<?php echo $requestFrom; ?>').on('click', function(e) {
							var loggedInUser = '<?php echo $loggedInUser; ?>';
							var filePath = '/';
							var userFullName = '<?php echo $userFullName; ?>';
							var username = '<?php echo $requestFrom; ?>';
							var action = "accept-request";

							e.preventDefault();

							$.ajax({
								type: 'POST',
								url: filePath + 'inc/handlers/friend-action.php',
								data: "loggedInUser=" + loggedInUser + "&username=" + username + "&action=" + action + "&userFullName=" + userFullName,
								success: function(response) {
									$('.<?php echo $requestFrom; ?>-card').hide();
								}
							});

						});
						</script>

				<?php

				}

				} else {
					echo "<div class='page-friends-list'>
							<div class='no-friends'>
					        	<h1>$username doesn't have any followers you know.</h1>
					        </div>
			        ";
				}

				echo "</div>";

				?>
			</div>

		</div>

		<?php } ?>

<?php if ($action !== 'liked-posts') { ?>

<script> //load posts
	var loggedInUser = '<?php echo $loggedInUser; ?>';
	var profileUsername = '<?php echo $username; ?>';
	var filePath = '/';
	var action = '<?php echo $action; ?>';
	var images = "";
	var deleted = "";

	if (action === 'images') {

		images = "posted-images";

	} else if (action === 'deleted-posts') {

		deleted = "deleted-posts";

	}

	$(document).ready(function() {

		//original ajax request for loading first posts
		$.ajax({
			url: filePath + "inc/ajax/ajax-load-profile-posts.php",
			type: "POST",
			data: "page=1&loggedInUser=" + loggedInUser + "&profileUsername=" + profileUsername + "&images=" + images + "&deleted=" + deleted,

			success: function(data) {
				$('.loading').hide();
				$('.profile-posts-area').html(data);
			}
		});

		$(window).scroll(function() {
			var height = $('.profile-posts-area').height(); //height of div that contains posts
			var scrollTop = $(this).scrollTop();
			var page = $('.profile-posts-area').find('.next-page').val();
			var noMorePosts = $('.profile-posts-area').find('.no-more-posts').val();

			if ((document.body.scrollHeight == document.body.scrollTop + window.innerHeight) && noMorePosts == 'false') {

				var ajaxReq = $.ajax ({
					url: filePath + "inc/ajax/ajax-load-profile-posts.php",
					type: "POST",
					data: "page=" + page + "&loggedInUser=" + loggedInUser + "&profileUsername=" + profileUsername + "&images=" + images + "&deleted=" + deleted,
					cache: false,

					success: function(response) {
						$('.profile-posts-area').find('.next-page').remove(); //remove current .next-page class
						$('.profile-posts-area').find('.no-more-posts').remove(); //remove current .no-more-posts class
						$('.loading').remove();
						$('.profile-posts-area').append(response);
					}
				});
			} // end if statement

			return false;

		}); //end (window).scroll(function()

	});
</script>

<?php } else if ($action === 'liked-posts') { ?>

<script> //load posts
	var loggedInUser = '<?php echo $loggedInUser; ?>';
	var profileUsername = '<?php echo $username; ?>';
	var filePath = '/';
	var action = '<?php echo $action; ?>';
	var likes = "";

	if (action === 'liked-posts') {

		likes = "liked-posts";

	}

	$(document).ready(function() {

		//original ajax request for loading first posts
		$.ajax({
			url: filePath + "inc/ajax/ajax-load-liked-posts.php",
			type: "POST",
			data: "page=1&loggedInUser=" + loggedInUser + "&profileUsername=" + profileUsername + "&likes=" + likes,

			success: function(data) {
				$('.loading').hide();
				$('.profile-posts-area').html(data);
			}
		});

		$(window).scroll(function() {
			var height = $('.profile-posts-area').height(); //height of div that contains posts
			var scrollTop = $(this).scrollTop();
			var page = $('.profile-posts-area').find('.next-page').val();
			var noMorePosts = $('.profile-posts-area').find('.no-more-posts').val();

			// if (($(window).scrollTop() + $(window).height() > $(document).height() - 100) && noMorePosts == 'false') {
			if ((document.body.scrollHeight == document.body.scrollTop + window.innerHeight) && noMorePosts == 'false') {

				var ajaxReq = $.ajax ({
					url: filePath + "inc/ajax/ajax-load-liked-posts.php",
					type: "POST",
					data: "page=" + page + "&loggedInUser=" + loggedInUser + "&profileUsername=" + profileUsername,
					cache: false,

					success: function(response) {
						$('.profile-posts-area').find('.next-page').remove(); //remove current .next-page class
						$('.profile-posts-area').find('.no-more-posts').remove(); //remove current .no-more-posts class
						$('.loading').remove();
						$('.profile-posts-area').append(response);
					}
				});
			} // end if statement

			return false;

		}); //end (window).scroll(function()

	});
</script>

<?php } ?>

<script> //friend action button (add, remove, etc.)
	$('.profile-friend-action-<?php echo $username; ?>').submit(function(e) {
		var loggedInUser = '<?php echo $loggedInUser; ?>';
		var filePath = '/';
		var userFullName = '<?php echo $userFullName; ?>';
		var username = '<?php echo $username; ?>';
		var action = "";

		if ($(".profile-friend-action-<?php echo $username; ?> input").hasClass('delete')) {
			action = 'delete';
		} else if ($(".profile-friend-action-<?php echo $username; ?> input").hasClass('respond')) {
			window.location.replace(filePath + loggedInUser + "/requests");
			action = 'respond';
		} else if ($(".profile-friend-action-<?php echo $username; ?> input").hasClass('sent')) {
			action = 'sent';
		} else if ($(".profile-friend-action-<?php echo $username; ?> input").hasClass('add')) {
			action = 'add';
		}

		e.preventDefault();
		// Submit the form using AJAX.
		$.ajax({
			type: 'POST',
			url: $('.profile-friend-action-<?php echo $username; ?>').attr('action'),
			data: "loggedInUser=" + loggedInUser + "&username=" + username + "&action=" + action + "&userFullName=" + userFullName
		})
		.done(function(response) {
			if (action == 'add') {
				$('.profile-friend-action-<?php echo $username; ?> input').val("Unfollow");
				$('.profile-friend-action-<?php echo $username; ?> input').removeClass("add");
				$('.profile-friend-action-<?php echo $username; ?> input').addClass("delete");
				<?php $action = "sent"; ?>;
			} else if (action == 'delete') {
				$('.profile-friend-action-<?php echo $username; ?> input').val("Follow");
				$('.profile-friend-action-<?php echo $username; ?> input').removeClass("delete");
				$('.profile-friend-action-<?php echo $username; ?> input').addClass("add");
			}
		})
		.fail(function(data) {

		});

	});
</script>

</div>

</div>

<?php
} else {
	header("Location: " . $filePath . "profile_error/" . $username);
}
include 'inc/footer.php';
?>
