<?php
//declaring variables to prevent errors
$fname = ""; //first name
$lname = ""; //last name
$uname = ""; //username
$em = ""; //email address
$em2 = ""; //email address confirmation
$pass = ""; //password
$pass2 = ""; //password confirmation
$errorArray = []; //holds any errors from form input

if (isset($_POST['reg_submit'])) {

	// check registration for value

	//first name
	$fname = strip_tags($_POST['reg_fname']); // removes any HTML tags
	$fname = str_replace(' ', '', $fname); //remove any spaces
	$fname = ucfirst(strtolower($fname)); //uppercase first letter ONLY
	$_SESSION['reg_fname'] = $fname; //stores first name in a session variable

	//last name
	$lname = strip_tags($_POST['reg_lname']); // removes any HTML tags
	$lname = str_replace(' ', '', $lname); //remove any spaces
	$lname = ucfirst(strtolower($lname)); //uppercase first letter ONLY
	$_SESSION['reg_lname'] = $lname; //stores last name in a session variable

	//username
	$uname = strip_tags($_POST['reg_uname']); // removes any HTML tags
	$uname = str_replace(' ', '', $uname); //remove any spaces
	$_SESSION['reg_uname'] = $uname; //stores username in a session variable

	//email
	$em = strip_tags($_POST['reg_email1']); // removes any HTML tags
	$em = str_replace(' ', '', $em); //remove any spaces
	$_SESSION['reg_email1'] = $em; //stores email in a session variable

	//email confirmation
	$em2 = strip_tags($_POST['reg_email2']); // removes any HTML tags
	$em2 = str_replace(' ', '', $em2); //remove any spaces
	$_SESSION['reg_email2'] = $em2; //stores email confirmation in a session variable

	//password
	$pass = strip_tags($_POST['reg_pass1']); //remove any HTML tags
	$pass2 = strip_tags($_POST['reg_pass2']); //remove any HTML tags

	//validate email addresses
	if ($em == $em2) {

		//check if email is in valid format
		if (filter_var($em, FILTER_VALIDATE_EMAIL)) {
			$em = filter_var($em, FILTER_VALIDATE_EMAIL);

			//check if emails already exists
			$sql = $db->query("
				SELECT email FROM users WHERE email = '$em'
			");
			$num_rows = $sql->num_rows;

			if ($num_rows > 0) {
				array_push($errorArray, "Email is already in use.");
			}

		} else {
			array_push($errorArray, "Invalid email format.");
		}
	} else {
		array_push($errorArray, "Emails don't match.");
	}

	//validate name fields
	//first name
	if(strlen($fname) < 2 || strlen($fname) > 30) {
		array_push($errorArray, "Your first name must be between 2 and 30 characters long.");
	}
	//last name
	if(strlen($lname) < 2 || strlen($lname) > 30) {
		array_push($errorArray, "Your last name must be between 2 and 30 characters long.");
	}
	//username
	//see if username is available
	$sql = $db->query("
		SELECT username FROM users WHERE username = '$uname'
	");
	$num_rows = $sql->num_rows;
	if ($num_rows > 0) {
		array_push($errorArray, "Username is already being used.");
	}
	if(strlen($uname) < 2 || strlen($uname) > 25) {
		array_push($errorArray, "Your username must be between 2 and 25 characters long.");
	}

	//validate passwords and encrypt
	if ($pass != $pass2) {
		array_push($errorArray, "Passwords don't match.");
	} else if (preg_match('/[^A-Za-z0-9]/', $pass)) {
		array_push($errorArray, "Your password can only contain english characters.");
	}
	if(strlen($pass) < 5) {
		array_push($errorArray, "Your password is too short.");
	}
	if(strlen($pass) > 30) {
		array_push($errorArray, "Your password is too long. Must be less than 30 letters.");
	}

	if(empty($errorArray)) {
		$pass = md5($pass); //encrypts password before sending it to the database

		//profile picture assignment
		$rand = rand(1, 16); //random number between 1 and 2

		switch ($rand) {

			case 1:
				$profilePic = "img/default profile pics/profile-blue.svg";
			break;

			case 2:
				$profilePic = "img/default profile pics/profile-green.svg";
			break;

			case 3:
				$profilePic = "img/default profile pics/profile-purple.svg";
			break;

			case 4:
				$profilePic = "img/default profile pics/profile-red.svg";
			break;

			case 5:
				$profilePic = "img/default profile pics/profile-yellow.svg";
			break;

		}

		$sql = $db->prepare("
			INSERT INTO users (first_name, last_name, username, email, password, signup_date, profile_pic, num_posts, num_likes, closed_account, following, followers, favorites, birthday, bio, location, verified, private_account)
			VALUES (?, ?, ?, ?, ?, NOW(), ?, '0', '0', 'no', ',zpthree,', ',zpthree,', ',', NOW(), 'none', '0', 'no', 'no')
		");
		$sql->bind_param('ssssss', $fname, $lname, $uname, $em, $pass, $profilePic);
		$sql->execute();

		$getZPTHREE = $db->query("
			SELECT followers, following FROM users WHERE username = 'zpthree'
		");
		$zpthreeUsername = $getZPTHREE->fetch_assoc();
		$followers = $zpthreeUsername['followers'] . $uname . ",";
		$following = $zpthreeUsername['following'] . $uname . ",";

		$updateInfo = $db->query("
			UPDATE users
			SET followers = '$followers', following = '$following'
			WHERE username = 'zpthree'
		");

		setcookie("loggedInUser", $uname, time()+3600*24*30*12*10, "/");

		header("Location: " . $filePath . "i/home");
	}

}
