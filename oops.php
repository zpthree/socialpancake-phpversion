<?php
$pageTitle = "oops";
include 'inc/header.php';

if(isset($_POST['post_submit'])) {
	$post = new Post($db, $loggedInUser);
	$post->submitPost($_POST['post_text'], 'none');
}
?>

<div class="user_details column">

		<h1>
			Looks like this page doesn't exist!
		</h1>


	</div>

<?php include 'inc/footer.php'; ?>