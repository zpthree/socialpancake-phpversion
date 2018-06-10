<?php
include '../db-connection.php';
include '../classes/user.php';
include '../classes/post.php';
include '../classes/notification.php';

if(!empty($_POST['username'])) {

	$uploadOk = 1;
	$imageName = $_FILES['fileToUpload']['name'];
	if (!empty($_POST['postId'])) {
		$editId = $_POST['postId'];
	} else {
		$editId = "";
	}

	$errorMessage = "";

	if ($imageName != "") {
		$targetDir = "../../img/posts/";
		$imageName = $targetDir . uniqid() . basename($imageName);
		$imageFileType = pathinfo($imageName, PATHINFO_EXTENSION);

		if ($_FILES['fileToUpload']['size'] > 10000000) {
			$errorMessage = "Sorry your file is too large.";
			$uploadOk = 0;
		}

		if(strtolower($imageFileType) != "jpeg" && strtolower($imageFileType) != "jpg" && strtolower($imageFileType) != "png") {
			$errorMessage = "Sorry only jpeg, jpg, and png files are allowed.";
			$uploadOk = 0;
		}

		if($uploadOk) {
			if(move_uploaded_file($_FILES['fileToUpload']['tmp_name'], $imageName)) {
				// image uploaded ok
			} else {
				// image did not upload
				$uploadOk = 0;
			}
		}

	}

	if ($uploadOk) {
		$post = new Post($db, $_POST['username']);
		$post->submitPost($_POST['post_body'], 'none', $imageName, $editId);
	} else {
		echo "<div style='text-align: center;' class='alert alert-danger'>

				$errorMessage;

			</div>";
	}
}
