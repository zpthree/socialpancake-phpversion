<?php
	require 'db-connection.php';
	include 'classes/user.php';
	include 'classes/post.php';
	include 'classes/message.php';
	include 'classes/notification.php';

	if (!empty($_COOKIE['loggedInUser']) && empty($_SESSION['username'])) {
		$_SESSION['username'] = $_COOKIE['loggedInUser'];
	}

	$siteName = "Social Pancake";
	$newPost = "";


	if (isset($_SESSION['username'])) {
		$loggedInUser = $_SESSION['username'];
		$userDetails = $db->query("
			SELECT * FROM users WHERE username = '$loggedInUser'
		");
		$loggedInUserRow = $userDetails->fetch_assoc();
	} else {
		header("Location: /register.php");
	}

	$_SESSION['first_name'] = $loggedInUserRow['first_name'];
	$_SESSION['last_name'] = $loggedInUserRow['last_name'];
	$userFullName = $_SESSION['first_name'] . " " . $_SESSION['last_name'];

	if ($pageTitle != 'profile') {
		$username = $loggedInUser;
	} else {
		$username = $_GET['profileUsername'];
	}
	// check if loggedInUser is verified
	$verifiedQuery = $db->query("
		SELECT verified FROM users WHERE username = '$username'
	");
	$verifiedRow = $verifiedQuery->fetch_assoc();
	if ($verifiedRow['verified'] == 'yes') {
		$userVerifiedPic = "/img/icons/verified.svg";
		$userVerified = 'verified';
	} else {
		$userVerifiedPic = "";
		$userVerified = "";
	}

	//get recommended follows
	$recommendedFollows = new User($db, $loggedInUser);
	$recommendedFollows = $recommendedFollows->whoToFollow(4, "");

?>
<!DOCTYPE html>
<html >
<head>
	<title><?php echo $newPost . $siteName; ?></title>
	<link rel="icon" href="/img/icons/logo.PNG">
	<meta charset="utf-8">
	<meta name="google" value="notranslate">
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0"/>
	<!-- JavaScript -->
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
	<script src="/js/jquery-ui.min.js"></script>
	<script src="/js/jcrop_bits.js"></script>
	<script src="/js/jquery.Jcrop.js"></script>
	<script src="/js/bootstrap.js"></script>
	<script src="/js/bootbox.min.js"></script>
	<script src="/js/scripts.js"></script>
	<script src="/js/jquery.caret.js"></script>
	<!-- CSS -->
	<link rel="stylesheet" type="text/css" href="/css/normalize.css">
	<link rel="stylesheet" type="text/css" href="/css/jquery.Jcrop.css">
	<link rel="stylesheet" type="text/css" href="/css/bootstrap.css">
	<link rel="stylesheet" type="text/css" href="/css/styles.min.css">
	<?php if ($_SESSION['username'] !== 'zpthree') { ?>
	<!-- Global Site Tag (gtag.js) - Google Analytics -->
	<script async src="https://www.googletagmanager.com/gtag/js?id=UA-106499794-1"></script>
	<script>
	  window.dataLayer = window.dataLayer || [];
	  function gtag(){dataLayer.push(arguments)};
	  gtag('js', new Date());

	  gtag('config', 'UA-106499794-1');
	</script>
	<?php } ?>
</head>
<body class="doc-body">

	<div class="main-menu">

		<div class="top-bar">
			<div class="nav">
				<nav class="Navigation">
					<ul class="primary-icons"> <!-- menu that displays fixed at the top of the page -->

						<div class="nav-left">

							<a  class='desktop-only' id="logo" href="<?php echo $home; ?>"><img src="/img/icons/logo.PNG" alt=""></a>

							<div class="search">
								<input type="hidden" id='scrollPos'value="">
								<form action="/i/search" method="GET" name="seach_form" class="search-box">
									<input type="text" onfocus="getLiveSearchUsers(this.value, '<?php echo $loggedInUser; ?>')" onkeyup="getLiveSearchUsers(this.value, '<?php echo $loggedInUser; ?>')" name='q' placeholder='Search for a user...' autocomplete='off' id='search-text-input' class="search-bar desktop-only">
									<button class="search-button">
										<svg class="search-icon" height="32px" version="1.1" viewBox="0 0 32 32" width="32px" xmlns="http://www.w3.org/2000/svg" xmlns:sketch="http://www.bohemiancoding.com/sketch/ns" xmlns:xlink="http://www.w3.org/1999/xlink">
											<path d="M19.4271164,21.4271164 C18.0372495,22.4174803 16.3366522,23 14.5,23 C9.80557939,23 6,19.1944206 6,14.5 C6,9.80557939 9.80557939,6 14.5,6 C19.1944206,6 23,9.80557939 23,14.5 C23,16.3366522 22.4174803,18.0372495 21.4271164,19.4271164 L27.0119176,25.0119176 C27.5621186,25.5621186 27.5575313,26.4424687 27.0117185,26.9882815 L26.9882815,27.0117185 C26.4438648,27.5561352 25.5576204,27.5576204 25.0119176,27.0119176 L19.4271164,21.4271164 L19.4271164,21.4271164 Z M14.5,21 C18.0898511,21 21,18.0898511 21,14.5 C21,10.9101489 18.0898511,8 14.5,8 C10.9101489,8 8,10.9101489 8,14.5 C8,18.0898511 10.9101489,21 14.5,21 L14.5,21 Z" id="search"/>
										</svg>
									</button>
								</form>
								<!-- search results will be generated here with ajax -->
								<div class="search-results">
								</div>

							</div>


						</div>

						<div class="nav-right">

							<div class="user-menu-outer">

								<div class="user-menu">

									<a id='user-menu-profile-link' href="<?php echo "/" . $loggedInUser; ?>">
										<img src="<?php echo "/" . $loggedInUserRow['profile_pic']; ?>" alt="<?php echo $loggedInUserRow['username']; ?>'s Profile Picture">
										<div class="user-profile-name">

											<div class="profile-user-fullname">
												<h5><?php echo $loggedInUserRow['first_name'] . ' '  . $loggedInUserRow['last_name']; ?></h5>


											</div>
											<p>@<?php echo $loggedInUserRow['username']; ?></p>

										</div>
									</a>

									<ul>

										<a class='mobile-only' href="/i/search"><li>Find a Friend</li></a>
										<a href="/i/settings"><li>Settings</li></a>
										<a><li id='site-info' data-toggle="modal" data-target="#info-modal">Information</li></a>
										<a href="/inc/handlers/logout.php"><li>Logout</li></a>

									</ul>

								</div>

							</div>

							<li class='home-icons'><a href="/" class="<?php if ($pageTitle == 'home') { echo 'active-icon'; } ?>"> <!-- home icon -->
								<svg class="nav-icons" id="home-icon" version="1.1" viewBox="0 0 32 32" xml:space="preserve" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
									<path d="M7,2H6C4.9,2,4,2.9,4,4v4.288l4.893-4.903C8.63,2.585,7.884,2,7,2z M30.875,14L18,1.094C17.313,0.438,16.891,0,16,0  s-1.313,0.438-2,1.094L1.125,14C0.406,14.719,0,15.188,0,16c0,1.172,0.969,2,2,2h2v12c0,1.1,0.9,2,2,2h6V22h8v10h6c1.1,0,2-0.9,2-2  V18h2c1.031,0,2-0.828,2-2C32,15.188,31.594,14.719,30.875,14z"/>
								</svg>
								<h3 class='desktop-only'>Home</h3>
							</a></li>

							<li class='mobile-only'><a href="/i/trending"class="<?php if ($pageTitle == 'trending') { echo 'active-icon'; } ?>">
								<svg class="nav-icons" height='48' viewBox='0 0 48 48' width='48' xmlns='http://www.w3.org/2000/svg'>
									<path d='M32 12l4.59 4.59-9.76 9.75-8-8-14.83 14.83 2.83 2.83 12-12 8 8 12.58-12.59 4.59 4.59v-12z'/>
									<path d='M0 0h48v48h-48z' fill='none'/>
								</svg>
							</a></li>

							<li><a href="/i/notifications" class="<?php if ($pageTitle == 'notifications') { echo 'active-icon'; } ?>"> <!-- notifications icon -->
								<svg class="nav-icons" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px"
									 y="0px" viewBox="0 0 60 60" style="enable-background:new 0 0 60 60;" xml:space="preserve">
									<path d="M22.4,47.7c0,4.2,3.4,7.6,7.6,7.6s7.6-3.4,7.6-7.6v-0.1L22.4,47.7C22.4,47.6,22.4,47.6,22.4,47.7z"/>
									<path d="M47.7,31.2C47.6,20.7,42.3,11.8,35,9c-0.4-2.4-2.5-4.2-5-4.2S25.4,6.6,25,9c-7.3,2.8-12.6,11.7-12.7,22.2
									c-3.1,3.9-5,8.8-5,14.1h7.1h1.4h28.5h3.4h5C52.7,40,50.8,35.1,47.7,31.2z"/>
								</svg>
								<h3 class='desktop-only'>Notifications</h3>
								<div id="notification-badge"></div>
							</a></li>

							<li><a href="/i/messages" class=" <?php if ($pageTitle == 'messages') { echo 'active-icon'; } ?>"> <!-- messages icon -->
								<svg class="nav-icons" version="1.1" viewBox="0 0 32 32" xml:space="preserve" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
									<path d="M29,5H3C1.9,5,1,5.9,1,7v18c0,1.1,0.9,2,2,2h26c1.1,0,2-0.9,2-2V7C31,5.9,30.1,5,29,5z M27.293,24.707L19.5,16.914  l-1.01,0.9C17.783,18.451,17.174,19,16,19s-1.783-0.549-2.49-1.186l-1.01-0.9l-7.793,7.793l-1.414-1.414l7.711-7.711L3.335,8.747  l1.33-1.493l10.184,9.075C15.491,16.907,15.622,17,16,17s0.509-0.093,1.151-0.672l10.184-9.075l1.33,1.493l-7.669,6.835l7.711,7.711  L27.293,24.707z"/>
								</svg>
								<h3 class='desktop-only'>Messages</h3>
								<div id="message-badge"></div>
							</a></li>

							<div class="">

								<li class="nav-profile-pic user-menu-toggle"><img class="" src="<?php echo "/" . $loggedInUserRow['profile_pic']; ?>"></li> <!-- profile icon -->

							</div>

						</div>

					</ul>
				</nav>
			</div>
		</div>
	</div> <!-- end mobile only -->

	<!-- Information Modal -->
	<div class="modal fade" id="info-modal" tabindex="-1" role="dialog">
	  <div class="modal-dialog" role="document">
	    <div class="modal-content">
	      <div class="modal-header">
	        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
	        <h4 class="modal-title">About Social Pancake</h4>
	      </div>
	      <div id="blurb" class="modal-body">
	       <div>
						<p>
							Social Pancake is a social media site designed and developed by <a href="/zpthree" target="_blank">Zach Patrick</a>.
						</p>
						<p>
							I built this site as a way to practice and improve as a developer. I also have always wanted to be verified, and now I am!
						</p>
						<p>
							Feel free to take a look around, and I hope you have a great day!
						</p>
				<!-- The site was built using HTML, CSS, JavaScript, PHP, and SQL. -->
				</div>
	      </div>
	    </div><!-- /.modal-content -->
	  </div><!-- /.modal-dialog -->
	</div><!-- /.modal -->

	<div class="wrapper">

		<div class="mobile-menu">


				<div class="search-results"></div>

				<?php

						echo $recommendedFollows;

				?>
				<div class="menu-navigation">
					<ul>
						<a href="/i/settings"><li>Settings</li></a>
						<li id='site-info' data-toggle="modal" data-target="#info-modal">Information</li>
						<a href="/inc/handlers/logout.php"><li>Logout</li></a>
					</ul>
				</div>
		</div>

		<div class="main-content">
		<div id="new-post-outer">
				<div id="mobile-new-post" data-toggle="modal" data-target="#post_form"> <!-- new post icon -->
						<svg class="post-icons" class="nav-icons" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
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

		<div id='post-successful'>
			<h4>Your post was sent successfully!</h4>
		</div>
