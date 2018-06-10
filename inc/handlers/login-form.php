<?php

if (isset($_POST['log_submit'])) {
	$username = $_POST['log_uname'];
	$_SESSION['log_uname'] = $username;
	$password = md5($_POST['log_pass']);

	$check_login = $db->query("
		SELECT * FROM users WHERE (username = '$username' AND password = '$password') OR (email = '$username' AND password = '$password')
	");
	$num_rows = $check_login->num_rows;

	if ($num_rows == 1) {
		$row = $check_login->fetch_assoc();
		$username = $row['username'];
		$first_name = $row['first_name'];
		$last_name = $row['last_name'];

		//check to see if the users account is closed or not -- if it is closed reopen it
		$check_account = $db->query("
			SELECT * FROM users WHERE username = '$username' AND closed_account = 'yes'
		");

		if ($check_account->num_rows == 1) {
			$reopen_account = $db->query("
				UPDATE users SET closed_account = 'no' WHERE username = '$username'
			");
		}

		setcookie("loggedInUser", $username, time()+3600*24*30*12*10, "/");

		header("Location: " . $filePath . "i/home");
		exit;
	} else {
		array_push($errorArray, "Username or password is incorrect.");
	}
}
