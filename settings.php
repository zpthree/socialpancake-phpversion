<?php

$pageTitle = "settings";
include("inc/header.php");

$profileId = $loggedInUserRow['username'];
if ($loggedInUserRow['bio'] === 'none') {
	$loggedInUserRowBio = "";
} else {
	$loggedInUserRowBio = $loggedInUserRow['bio'];
}
$imgSrc = "";
$resultPath = "";
$msg = "";
$_SESSION['pic_upload_error'] = "";

if(isset($_POST['post_submit'])) {
	$post = new Post($db, $loggedInUser);
	$post->submitPost($_POST['post_text'], 'none');
}

if (isset($_GET['deactivate']) && $_GET['deactivate'] == 'true') {
	$closeAccount = $db->query("
		UPDATE users
		SET closed_account = 'yes'
		WHERE username = '$profileId'
	");
	header("Location: /inc/handlers/logout.php");
}

if (isset($_POST['info_submit'])) {

	$fname = ""; //first name
	$lname = ""; //last name
	$uname = ""; //username
	$em = ""; //email address
	$birthday = ""; //user birthday
	$bio = ""; //user bio
	$errorArray = []; //holds any errors from form input

	//first name
	$fname = strip_tags($_POST['fname']); // removes any HTML tags
	$fname = str_replace(' ', '', $fname); //remove any spaces
	$fname = ucfirst(strtolower($fname)); //uppercase first letter ONLY

	//last name
	$lname = strip_tags($_POST['lname']); // removes any HTML tags
	$lname = str_replace(' ', '', $lname); //remove any spaces
	$lname = ucfirst(strtolower($lname)); //uppercase first letter ONLY

	//username
	$uname = strip_tags($_POST['uname']); // removes any HTML tags
	$uname = str_replace(' ', '', $uname); //remove any spaces

	//email
	$em = strip_tags($_POST['email']); // removes any HTML tags
	$em = str_replace(' ', '', $em); //remove any spaces

	//bio
	$bio = strip_tags($_POST['user_bio']); // removes any HTML tags

	//email
	if (isset($_POST['private'])) {
		$private_account = "yes";
	} else {
		$private_account ="no";
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


	$updateInfo = $db->query("
		UPDATE users
		SET first_name = '$fname', last_name  = '$lname', username = '$uname', email = '$em', bio = '$bio', private_account = '$private_account'
		WHERE username = '$loggedInUser'
	");

	$_SESSION['username'] = $uname;
	header("Location: " . $filePath . "i/settings");
}

if (isset($_POST['pass_submit'])) {
	$pass1 = ""; //check current password
	$pass2 = ""; //password
	$pass3 = ""; //password confirmation
	$errorArray = []; //holds any errors from form input

	//password
	$pass1 = strip_tags($_POST['pass_1']); //remove any HTML tags
	$pass2 = strip_tags($_POST['pass_2']); //remove any HTML tags
	$pass3 = strip_tags($_POST['pass_3']); //remove any HTML tags

	$sql = $db->query("
		SELECT password FROM users WHERE username = '$loggedInUser'
	");
	$getSql = $sql->fetch_assoc();
	$currentPassword = $getSql['password'];

	if ($currentPassword === md5($pass1)) {
		//validate passwords and encrypt
		if ($pass2 != $pass3) {
			array_push($errorArray, "Passwords don't match.");
		} else if (preg_match('/[^A-Za-z0-9]/', $pass2)) {
			array_push($errorArray, "Your password can only contain english characters.");
		}
		if(strlen($pass) < 5) {
			array_push($errorArray, "Your password is too short.");
		}
		if(strlen($pass) > 30) {
			array_push($errorArray, "Your password is too long. Must be less than 30 letters.");
		}

		if (!empty($errorArray)) {
			$pass2 = md5($pass2);

			$updateInfo = $db->query("
				UPDATE users
				SET password = '$pass2'
				WHERE username = '$loggedInUser'
			");

			header("Location: " . $filePath . "i/settings");
		}
	}

}

$userPostsNum = $loggedInUserRow['num_posts'];
//get following number
$getFollowingList = $db->query("
	SELECT following FROM users WHERE username = '$loggedInUser'
");
$followingList = $getFollowingList->fetch_assoc();
$followingList = explode(",", trim(implode(",", $followingList),","));
$followingNum = count($followingList);

//get number of followers
$getFollowersList = $db->query("
	SELECT followers FROM users WHERE username = '$loggedInUser'
");
$followersList = $getFollowersList->fetch_assoc();
$followersList = explode(",", trim(implode(",", $followersList),","));
if (count($followersList) > 1) {
	$followersNum = count($followersList);
} else {
	$followersNum = 0;
}

/***********************************************************
	0 - Remove The Temp image if it exists
***********************************************************/
	if (!isset($_POST['x']) && !isset($_FILES['image']['name']) ){
		//Delete users temp image
			$temppath = $filePath . 'img/profile-pics/'.$profileId.'_temp.jpeg';
			if (file_exists ($temppath)){ @unlink($temppath); }
	}


if(isset($_FILES['image']['name'])){
/***********************************************************
	1 - Upload Original Image To Server
***********************************************************/
	//Get Name | Size | Temp Location
		$ImageName = $_FILES['image']['name'];
		$ImageSize = $_FILES['image']['size'];
		$ImageTempName = $_FILES['image']['tmp_name'];
	//Get File Ext
		$ImageType = @explode('/', $_FILES['image']['type']);
		$type = $ImageType[1]; //file type
	//Set Upload directory
		$uploaddir = $_SERVER['DOCUMENT_ROOT'] . '/img/profile-pics';
	//Set File name
		$file_temp_name = $profileId.'_original.'.md5(time()).'n'.$type; //the temp file name
		$fullpath = $uploaddir."/".$file_temp_name; // the temp file path
		$file_name = $profileId.'_temp.jpeg'; //$profileId.'_temp.'.$type; // for the final resized image
		$fullpath_2 = $uploaddir."/".$file_name; //for the final resized image
	//Move the file to correct location
		$move = move_uploaded_file($ImageTempName ,$fullpath) ;
		chmod($fullpath, 0777);
		//Check for valid uplaod
		if (!$move) {
			die ('File didnt upload');
		} else {
			$imgSrc= $filePath . "img/profile-pics/".$file_name; // the image to display in crop area
			$msg= "Upload Complete!";  	//message to page
			$src = $file_name;	 		//the file name to post from cropping form to the resize
		}

/***********************************************************
	2  - Resize The Image To Fit In Cropping Area
***********************************************************/
		//get the uploaded image size
			clearstatcache();
			$original_size = getimagesize($fullpath);
			$original_width = $original_size[0];
			$original_height = $original_size[1];
		// Specify The new size
			$main_width = 400; // set the width of the image
			$main_height = $original_height / ($original_width / $main_width);	// this sets the height in ratio
		//create new image using correct php func
			if($_FILES["image"]["type"] == "image/gif"){
				$src2 = imagecreatefromgif($fullpath);
				$_SESSION['pic_upload_error'] = "";
			}elseif($_FILES["image"]["type"] == "image/jpeg" || $_FILES["image"]["type"] == "image/pjpeg"){
				$src2 = imagecreatefromjpeg($fullpath);
				$_SESSION['pic_upload_error'] = "";
			}elseif($_FILES["image"]["type"] == "image/png"){
				$src2 = imagecreatefrompng($fullpath);
				$_SESSION['pic_upload_error'] = "";
			}else{

				header("Location: " . $filePath ."i/settings");
				$msg .= "There was an error uploading the file. Please upload a .jpg, .gif or .png file. <br />";
				$_SESSION['pic_upload_error'] = "There was an error uploading the file. Please upload a .jpg, .gif or .png file. <br />";
			}
		//create the new resized image
			$main = imagecreatetruecolor($main_width,$main_height);
			imagecopyresampled($main,$src2,0, 0, 0, 0,$main_width,$main_height,$original_width,$original_height);
		//upload new version
			$main_temp = $fullpath_2;
			imagejpeg($main, $main_temp, 90);
			chmod($main_temp,0777);
		//free up memory
			imagedestroy($src2);
			imagedestroy($main);
			//imagedestroy($fullpath);
			@ unlink($fullpath); // delete the original upload

}//ADD Image

/***********************************************************
	3- Cropping & Converting The Image To Jpg
***********************************************************/
if (isset($_POST['x'])){

	//the file type posted
		$type = $_POST['type'];
	//the image src
		$src = $filePath . 'img/profile-pics/'.$_POST['src'];
		$finalname = $profileId.md5(time());

	if($type == 'jpg' || $type == 'jpeg' || $type == 'JPG' || $type == 'JPEG'){

		//the target dimensions 150x150
			$targ_w = $targ_h = 150;
		//quality of the output
			$jpeg_quality = 90;

		//create a cropped copy of the image
			$img_r = imagecreatefromjpeg($src);
			$dst_r = imagecreatetruecolor($targ_w, $targ_h);
			imagecopyresampled($dst_r,$img_r,0,0,$_POST['x'],$_POST['y'],
			$targ_w,$targ_h,$_POST['w'],$_POST['h']);
		//save the new cropped version
			imagejpeg($dst_r, "img/profile-pics/".$finalname."n.jpeg", $jpeg_quality);

	}else if($type == 'png' || $type == 'PNG'){

		//the target dimensions 150x150
			$targ_w = $targ_h = 150;
		//quality of the output
			$jpeg_quality = 100;
		//create a cropped copy of the image
			$img_r = imagecreatefrompng($src);
			$dst_r = imagecreatetruecolor( $targ_w, $targ_h );
			imagecopyresampled($dst_r,$img_r,0,0,$_POST['x'],$_POST['y'],
			$targ_w,$targ_h,$_POST['w'],$_POST['h']);
		//save the new cropped version
			imagejpeg($dst_r, "img/profile-pics/".$finalname."n.jpeg", $jpeg_quality);

	}else if($type == 'gif' || $type == 'GIF'){

		//the target dimensions 150x150
			$targ_w = $targ_h = 150;
		//quality of the output
			$jpeg_quality = 90;
		//create a cropped copy of the image
			$img_r = imagecreatefromgif($src);
			$dst_r = imagecreatetruecolor( $targ_w, $targ_h );
			imagecopyresampled($dst_r,$img_r,0,0,$_POST['x'],$_POST['y'],
			$targ_w,$targ_h,$_POST['w'],$_POST['h']);
		//save the new cropped version
			imagejpeg($dst_r, "img/profile-pics/".$finalname."n.jpeg", $jpeg_quality);

	}
		//free up memory
			imagedestroy($img_r); // free up memory
			imagedestroy($dst_r); //free up memory
			@ unlink($src); // delete the original upload

		//return cropped image to page
		$resultPath = "img/profile-pics/".$finalname."n.jpeg";

		//Insert image into database
		$insert_pic_query = $db->query("
			UPDATE users
			SET profile_pic = '$resultPath'
			WHERE username = '$loggedInUser'
		");

		header("Location: " . $filePath . "i/settings");

}// post x
?>
<div id="Overlay"></div>

<div class="main-columns">

	<div id="formExample" class="settings-main">

		<?php if (!isset($_GET['deactivate_account']) || $_GET['deactivate_account'] != "deactivate-account") { ?>



		<div id="account-info">
			<h2>Update Account Information</h2>
			<form action='<?php echo $filePath; ?>i/settings' method='POST'>
				<div>
					<h4>First Name</h4>
					<input type="text" value="<?php echo $loggedInUserRow['first_name']; ?>" name='fname' required>
				</div>
				<div>
					<h4>Last Name</h4>
					<input type="text" value="<?php echo $loggedInUserRow['last_name']; ?>" name='lname' required>
				</div>
				<div>
					<h4>Username</h4>
					<input type="text" value="<?php echo $loggedInUserRow['username']; ?>" name='uname' required>
				</div>
				<div>
					<h4>Email</h4>
					<input type="text" value="<?php echo $loggedInUserRow['email']; ?>" name='email' required>
				</div>
				<div>
					<h4>Bio</h4>
					<textarea name="user_bio"><?php echo $loggedInUserRowBio; ?></textarea>
				</div>
				<div id='private-check'>
					<h4>Private Account</h4>
					<input type='checkbox' name="private" id="private" <?php if ($loggedInUserRow['private_account'] == 'yes') { echo "checked"; } ?>>
				</div>
				<input type="submit" name='info_submit' value="Update Information">
			</form>
		</div>

		<div id="account-picture">
			<h2>Update Profile Picture</h2>
			<div class="pic_upload_error">
				<?php
					echo $_SESSION['pic_upload_error'];
				?>
			</div>
			<div id="profile_img_outer">
				<img id="profile_img" src="<?php echo $filePath . $loggedInUserRow['profile_pic']; ?>">
			</div>
			<form action="<?php echo $filePath; ?>i/settings" method="post" enctype="multipart/form-data">
		        <input type="file" id="image" name="image" style="display: none;"/>
		        <input type="submit" id="image_upload_submit" value="Update Picture" style="display: none;">
		    </form>
		</div>

		<div id="account-password">
			<h2>Update Password</h2>
			<form action='<?php echo $filePath; ?>i/settings' method='POST'>
				<div>
					<h4>Confirm Password</h4>
					<input type="password" name='pass_1'>
				</div>
				<div>
					<h4>New Password</h4>
					<input type="password" name='pass_2'>
				</div>
				<div>
					<h4>Confirm New Password</h4>
					<input type="password" name='pass_3'>
				</div>
				<input type="submit" name='pass_submit' value="Update Password">
			</form>
		</div>

		<div id="deactivate-account">
				<h2>Would you like to deactivate your account?</h2>
				<a href="<?php echo $filePath; ?>i/settings/deactivate-account">
					<button type="button">
						Deactivate Account
					</button>
				</a>
		</div>

	<?php } else if ($_GET['deactivate_account'] == "deactivate-account")  { ?>

		<div id="deactivate-verify">
				<h2>Are you sure you want to deactivate your account? Your account will become active again if you log back in.</h2>
				<div class="deactivate-verify-options">
					<a href="<?php echo $filePath; ?>i/settings">
						<button class='cancel-deactivate' type="button">
							Cancel
						</button>
					</a>
					<a href='/i/settings?deactivate=true'>
						<button class='verify-deactive' type="button">
							Deactivate Account
						</button>
					</a>
				</form>
		</div>

	<?php } ?>

	    <p><b> <?=$msg?> </b></p>


	</div> <!-- Form-->


    <?php if($imgSrc){ //if an image has been uploaded display cropping area ?>
	    <script>
	    	$('#Overlay').show();
			$('#formExample').hide();
	    </script>
	    <div id="cropping-container">

					<h2>New Profile Image</h2>

					<div id="cropping-container-inner">
						<div id="cropping-area">
		            <img src="<?=$imgSrc?>" border="0" id="jcrop_target"/>
		        </div>

						<div class="info-area">
							<div id="info-area">
			           <div>
			                <h4>Crop Profile Image</h4>
	                    <p>Crop / resize your uploaded profile image.</p>
	                    <p>Once you are happy with your profile image then please click save.</p>
			           </div>
			        </div>

							<div id="crop-image-form-options">

								<div id="crop-image-form">
				            <form action="<?php echo $filePath; ?>i/settings" method="post" onsubmit="return checkCoords();">
				                <input type="hidden" id="x" name="x" />
				                <input type="hidden" id="y" name="y" />
				                <input type="hidden" id="w" name="w" />
				                <input type="hidden" id="h" name="h" />
				                <input type="hidden" value="jpeg" name="type" /> <?php // $type ?>
				                <input type="hidden" value="<?=$src?>" name="src" />
				                <input type="submit" value="Save" id="image-form-save" />
				            </form>
				        </div>

				        <div id="crop-image-form2">
				            <form action="<?php echo $filePath; ?>i/settings.php" method="post" onsubmit="return cancelCrop();">
				                <input type="submit" value="Cancel Crop" id="image-form-cancel"/>
				            </form>
				        </div>

							</div>

						</div>

					</div>

	    </div><!-- CroppingContainer -->
	<?php
	} ?>
</div>





 <?php if($resultPath) {
	 ?>

     <img src="<?=$resultPath?>" />

 <?php } ?>


    <br /><br />

<script>
var loggedInUser = '<?php echo $loggedInUser; ?>';
</script>
<script>
$("#profile_img").click(function() {
		$('#image').trigger('click');
});
</script>
<script>
$("#image").change(function(){
		$('#image_upload_submit').trigger('click');
});
</script>

<?php include 'inc/footer.php'; ?>
