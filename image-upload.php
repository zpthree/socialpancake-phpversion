<?php
$pageTitle = "image-upload";
include("inc/header.php");

$profileId = $loggedInUserRow['username'];
$imgSrc = "";
$resultPath = "";
$msg = "";

/***********************************************************
	0 - Remove The Temp image if it exists
***********************************************************/
	if (!isset($_POST['x']) && !isset($_FILES['image']['name']) ){
		//Delete users temp image
			$temppath = 'img/profile-pics/'.$profileId.'_temp.jpeg';
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
		$uploaddir = $_SERVER['DOCUMENT_ROOT'].'/twitter-redesign/img/profile-pics';
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
			$imgSrc= "img/profile-pics/".$file_name; // the image to display in crop area
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
			$main_width = 500; // set the width of the image
			$main_height = $original_height / ($original_width / $main_width);	// this sets the height in ratio
		//create new image using correct php func
			if($_FILES["image"]["type"] == "image/gif"){
				$src2 = imagecreatefromgif($fullpath);
			}elseif($_FILES["image"]["type"] == "image/jpeg" || $_FILES["image"]["type"] == "image/pjpeg"){
				$src2 = imagecreatefromjpeg($fullpath);
			}elseif($_FILES["image"]["type"] == "image/png"){
				$src2 = imagecreatefrompng($fullpath);
			}else{
				$msg .= "There was an error uploading the file. Please upload a .jpg, .gif or .png file. <br />";
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
		$src = 'img/profile-pics/'.$_POST['src'];
		$finalname = $profileId.md5(time());

	if($type == 'jpg' || $type == 'jpeg' || $type == 'JPG' || $type == 'JPEG'){

		//the target dimensions 150x150
			$targ_w = $targ_h = 150;
		//quality of the output
			$jpeg_quality = 90;
		//create a cropped copy of the image
			$img_r = imagecreatefromjpeg($src);
			$dst_r = imagecreatetruecolor( $targ_w, $targ_h );
			imagecopyresampled($dst_r,$img_r,0,0,$_POST['x'],$_POST['y'],
			$targ_w,$targ_h,$_POST['w'],$_POST['h']);
		//save the new cropped version
			imagejpeg($dst_r, "img/profile-pics/".$finalname."n.jpeg", 90);

	}else if($type == 'png' || $type == 'PNG'){

		//the target dimensions 150x150
			$targ_w = $targ_h = 150;
		//quality of the output
			$jpeg_quality = 90;
		//create a cropped copy of the image
			$img_r = imagecreatefrompng($src);
			$dst_r = imagecreatetruecolor( $targ_w, $targ_h );
			imagecopyresampled($dst_r,$img_r,0,0,$_POST['x'],$_POST['y'],
			$targ_w,$targ_h,$_POST['w'],$_POST['h']);
		//save the new cropped version
			imagejpeg($dst_r, "img/profile-pics/".$finalname."n.jpeg", 90);

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
			imagejpeg($dst_r, "img/profile-pics/".$finalname."n.jpeg", 90);

	}
		//free up memory
			imagedestroy($img_r); // free up memory
			imagedestroy($dst_r); //free up memory
			@ unlink($src); // delete the original upload

		//return cropped image to page
		$resultPath ="img/profile-pics/".$finalname."n.jpeg";

		//Insert image into database
		$insert_pic_query = mysqli_query($db, "UPDATE users SET profile_pic= '$resultPath' WHERE username = '$loggedInUser'");
		header("Location: ".$loggedInUser);

}// post x
?>
<div id="Overlay"></div>
<div class="main-column column">


	<div id="formExample">

	    <p><b> <?=$msg?> </b></p>

	    <form action="settings.php" method="post"  enctype="multipart/form-data">
	        Upload something<br /><br />
	        <input type="file" id="image" name="image" style="width:200px; height:30px; " /><br /><br />
	        <input type="submit" value="Submit" style="width:85px; height:25px;" />
	    </form><br /><br />

	</div> <!-- Form-->


    <?php
    if($imgSrc){ //if an image has been uploaded display cropping area?>
	    <script>
	    	$('#Overlay').show();
			$('#formExample').hide();
	    </script>
	    <div id="CroppingContainer">

	        <div id="CroppingArea">
	            <img src="<?=$imgSrc?>" border="0" id="jcrop_target"/>
	        </div>

	        <div id="InfoArea">
	           <p>
	                <b>Crop Profile Image</b><br /><br />
	                <span style="font-size:14px;">
	                    Crop / resize your uploaded profile image. <br />
	                    Once you are happy with your profile image then please click save.

	                </span>
	           </p>
	        </div>

	        <br />

	        <div id="CropImageForm">
	            <form action="settings.php" method="post" onsubmit="return checkCoords();">
	                <input type="hidden" id="x" name="x" />
	                <input type="hidden" id="y" name="y" />
	                <input type="hidden" id="w" name="w" />
	                <input type="hidden" id="h" name="h" />
	                <input type="hidden" value="jpeg" name="type" /> <?php // $type ?>
	                <input type="hidden" value="<?=$src?>" name="src" />
	                <input type="submit" value="Save" id="image-form-save" />
	            </form>
	        </div>

	        <div id="CropImageForm2">
	            <form action="settings.php" method="post" onsubmit="return cancelCrop();">
	                <input type="submit" value="Cancel Crop" id="image-form-cancel" />
	            </form>
	        </div>

	    </div><!-- CroppingContainer -->
	<?php
	} ?>
</div>





 <?php if($resultPath) {
	 ?>

     <img src="<?=$resultPath?>" style="position:relative; margin:10px auto; width:150px; height:150px;" />

 <?php } ?>


    <br /><br />
