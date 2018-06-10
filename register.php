<?php
require 'inc/db-connection.php';
include 'inc/handlers/register-form.php';
include 'inc/handlers/login-form.php';

?>

<html>
<head>
	<title>Welcome to Social Pancake!</title>
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0"/>
	<link rel="stylesheet" type="text/css" href="/css/normalize.css">
	<link rel="stylesheet" type="text/css" href="/css/register.css">
</head>
<body>
	<div class="wrapper">

		<div class="top-bar-outer">
			<div class="top-bar">
				<div class="logo">
					<img src="<?php echo $filePath; ?>img/icons/logo-2.png">
				</div>
				<div id="login-form">
					<form method="POST">
						<input type="username" name="log_uname" id="log_uname" placeholder="Username" value="<?php
							if (isset($_SESSION['log_uname'])) { echo $_SESSION['log_uname']; }
						?>" title="Username" required>
						<input type="password" name="log_pass" id="log_pass" placeholder="Password" title="Password" required autocomplete="off">
						<input type="submit" name="log_submit" id="log_submit" value="Submit">
					</form>
					<?php if (in_array("Username or password is incorrect.", $errorArray)) {
						echo "<div class='error-message'>Username or password is incorrect.</div>";
					} ?>
				</div>
			</div>
		</div>
		<div class="main">
			<div class="blurb-outer">
				<div id="blurb">
					<p>
						<span>Welcome to Social Pancake</span>, a social media site designed and developed by <a href="http://zachpatrick.com" target="_blank">Zach Patrick</a>.
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
			<div id="register-form">
				<div class="reg-header">
					<h3>Join Social Pancake</h3>
				</div>
				<form method="POST">

					<div id="username-holder">
						<input type="username" name="reg_uname" id="reg_uname" placeholder="Username"
						value="<?php
							if (isset($_SESSION['reg_uname'])) { echo $_SESSION['reg_uname']; }
						?>"

						title="Username"

						<?php

						if (in_array("Username is already being used.", $errorArray)) {
							echo "class='form-error'";
						} else if (in_array("Your username must be between 2 and 25 characters long.", $errorArray)) {
							echo "class='form-error'";
						}

						?>
						required>
					</div>
					<div class="error-message">
						<?php

						if (in_array("Username is already being used.", $errorArray)) {
							echo "Username is already taken.";
						} else if (in_array("Your username must be between 2 and 25 characters long.", $errorArray)) {
							echo "Your username must be between 2 and 25 characters long.";
						}

						?>
					</div>

					<div id="name-holder">
						<input type="text" name="reg_fname" id="reg_fname" placeholder="First Name"
						value="<?php
							if (isset($_SESSION['reg_fname'])) { echo $_SESSION['reg_fname']; }
						?>" title="First Name"

						<?php

						if (in_array("Your first name must be between 2 and 30 characters long.", $errorArray)) {
							echo "class='form-error'";
						}

						?>

						required>
						<input type="text" name="reg_lname" id="reg_lname" placeholder="Last Name" value="<?php
							if (isset($_SESSION['reg_lname'])) { echo $_SESSION['reg_lname']; }
						?>" title="Last Name"

						<?php

						if (in_array("Your last name must be between 2 and 30 characters long.", $errorArray)) {
							echo "class='form-error'";
						}

						?>

						required>
					</div>
					<div class="error-message">
						<?php

						if (in_array("Your first name must be between 2 and 30 characters long.", $errorArray)) {
							echo "Your first and last name must be between 2 and 30 characters long.";
						} else if (in_array("Your last name must be between 2 and 30 characters long.", $errorArray)) {
							echo "Your first and last name must be between 2 and 30 characters long.";
						}

						?>
					</div>

					<div id="email-holder">
						<input type="email" name="reg_email1" id="reg_email1" placeholder="Email" value="<?php
							if (isset($_SESSION['reg_email1'])) { echo $_SESSION['reg_email1']; }
						?>" title="Email"

						<?php

						if (in_array("Email is already in use.", $errorArray)) {
							echo "class='form-error'";
						} else if (in_array("Invalid email format.", $errorArray)) {
							echo "class='form-error'";
						} else if (in_array("Emails don't match.", $errorArray)) {
							echo "class='form-error'";
						}

						?>

						required>
						<input type="email" name="reg_email2" id="reg_email2" placeholder="Confirm Email" value="<?php
							if (isset($_SESSION['reg_email2'])) { echo $_SESSION['reg_email2']; }
						?>" title="Confirm Email"

						<?php

						if (in_array("Email is already in use.", $errorArray)) {
							echo "class='form-error'";
						} else if (in_array("Invalid email format.", $errorArray)) {
							echo "class='form-error'";
						} else if (in_array("Emails don't match.", $errorArray)) {
							echo "class='form-error'";
						}

						?>

						required>
					</div>
					<div class="error-message">
						<?php

						if (in_array("Email is already in use.", $errorArray)) {
							echo "Email is already in use.";
						} else if (in_array("Invalid email format.", $errorArray)) {
							echo "Invalid email format.";
						} else if (in_array("Emails don't match.", $errorArray)) {
							echo "Emails don't match.";
						}

						?>
					</div>

					<div id="password-holder">
						<input type="password" name="reg_pass1" id="reg_pass1" placeholder="Password" title="Password"

						<?php

						if (in_array("Passwords don't match.", $errorArray)) {
							echo "class='form-error'";
						} else if (in_array("Your password can only contain english characters.", $errorArray)) {
							echo "class='form-error'";
						} else if (in_array("Your password is too short.", $errorArray)) {
							echo "class='form-error'";
						} else if (in_array("Your password is too long. Must be less than 30 letters.", $errorArray)) {
							echo "class='form-error'";
						}

						?>

						required>
						<input type="password" name="reg_pass2" id="reg_pass2" placeholder="Confirm Password" title="Confirm Password"

						<?php

						if (in_array("Passwords don't match.", $errorArray)) {
							echo "class='form-error'";
						} else if (in_array("Your password can only contain english characters.", $errorArray)) {
							echo "class='form-error'";
						} else if (in_array("Your password is too short.", $errorArray)) {
							echo "class='form-error'";
						} else if (in_array("Your password is too long. Must be less than 30 letters.", $errorArray)) {
							echo "class='form-error'";
						}

						?>

						required>
					</div>
					<div class="error-message">
						<?php

						if (in_array("Passwords don't match.", $errorArray)) {
							echo "Passwords don't match.";
						} else if (in_array("Your password can only contain english characters.", $errorArray)) {
							echo "Your password can only contain english characters.";
						} else if (in_array("Your password is too short.", $errorArray)) {
							echo "Your password is too short.";
						} else if (in_array("Your password is too long. Must be less than 30 letters.", $errorArray)) {
							echo "Your password is too long. Must be less than 30 letters.";
						}

						?>
					</div>
					<input type="submit" name="reg_submit" id="reg_submit" value="Submit">
				</form>
			</div>
		</div>
	</div>
</body>
</html>
