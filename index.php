<?php
$pageTitle = "home";
include 'inc/header.php';

if (isset($_POST['post_submit'])) {
	$post = new Post($db, $loggedInUser);
	$post->submitPost($_POST['post_text'], 'none');
}

if (!empty($_GET['feedView'])) {
	$feedView = $_GET ['feedView'];
} else {
	$feedView = "";
}

$userPostsNum = $loggedInUserRow['num_posts'];

//get following info
$getFollowingList = $db->query("
	SELECT following FROM users WHERE username = '$loggedInUser'
");
$followingList = $getFollowingList->fetch_assoc();
$followingList = explode(",", trim(implode(",", $followingList),","));
$followingNum = count($followingList);

//get followers info
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

$friendCount = explode(",", $loggedInUserRow['following']);
$friendCount = count($friendCount) - 2;
if ($friendCount < 1) {
	$friendCount = 0;
}
?>

<div class="left-side-panels">
	<div class="user-card">
		<img class="user-card-img" src="<?php echo $filePath . $loggedInUserRow['profile_pic']; ?>">
				<div class='person-details'>
					<a href='<?php echo $filePath . $loggedInUserRow['username']; ?>' class='post-name'><span><?php echo $loggedInUserRow['first_name'] . " " . $loggedInUserRow['last_name']; ?></span><img class='<?php echo $userVerified; ?>' src='<?php echo $userVerifiedPic; ?>'></a><span class='post-username'>@<?php echo $loggedInUserRow['username']; ?></span>
				</div>
	</div>

	<div class="left-trending-side"></div>

	<div class="left-side-panels left-right-side">
		<?php

			echo $recommendedFollows;

		?>
	</div>

</div>

<div class="main-columns">

	<div class="feed">

		<div class="posts-area"></div>

	</div>

</div>

<div class="right-side-panels">
	<?php

		echo $recommendedFollows;

	?>
</div>

<?php if (empty($feedView) || $feedView == 'verified') { ?>

<script>
var loggedInUser = '<?php echo $loggedInUser; ?>';
var feedView = '<?php echo $feedView; ?>';
$.ajaxSetup({ cache: false });

$(document).ready(function() {

	//original ajax request for loading first posts
	$.ajax({
		url: "<?php echo $filePath; ?>inc/ajax/ajax-load-posts.php",
		type: "POST",
		data: "page=1&loggedInUser=" + loggedInUser + "&feedView=" + feedView,

		success: function(data) {
			$('.loading').hide();
			$('.posts-area').html(data);
		}
	});

	$(window).scroll(function() {
		var height = $('.posts-area').height(); //height of div that contains posts
		var scrollTop = $(this).scrollTop();
		var page = $('.posts-area').find('.next-page').val();
		var noMorePosts = $('.posts-area').find('.no-more-posts').val();

		// if (($(window).scrollTop() + $(window).height() > $(document).height() - 100) && noMorePosts == 'false') {
		if ((document.body.scrollHeight == document.body.scrollTop + window.innerHeight) && noMorePosts == 'false') {

			var ajaxReq = $.ajax ({
				url: "<?php echo $filePath; ?>inc/ajax/ajax-load-posts.php",
				type: "POST",
				data: "page=" + page + "&loggedInUser=" + loggedInUser + "&feedView=" + feedView,
				cache: false,

				success: function(response) {
					$('.posts-area').find('.next-page').remove(); //remove current .next-page class
					$('.posts-area').find('.no-more-posts').remove(); //remove current .no-more-posts class
					$('.loading').remove();
					$('.posts-area').append(response);
				}
			});
		} // end if statement

		return false;

	}); //end (window).scroll(function()

});
</script>

<?php } ?>

<?php include 'inc/footer.php'; ?>
