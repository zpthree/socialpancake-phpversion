<?php
$pageTitle = "notifications";
include 'inc/header.php';

if(isset($_POST['post_submit'])) {
	$post = new Post($db, $loggedInUser);
	$post->submitPost($_POST['post_text'], 'none');
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

<div class="main-columns post-page-main">
	<div id='mobile-notifications-header'>
		<h2 class="">Notifications</h2>
		<form id="mark-read-form" action="<?php echo $filePath; ?>inc/ajax/ajax-mark-read.php" method="post">
			<input type="hidden" name="user" value="<?php echo $loggedInUser; ?>">
			<button type="submit" id="mark-read">
				<h5>Mark all as read</h5>
			</button>
		</form>
	</div>
	<div class="feed notifications-feed">
	<div class="main-notifications">
	</div>
	</div>

</div>

<div class="right-side-panels">
	<?php

		echo $recommendedFollows;

	?>
</div>

<script>
var loggedInUser = '<?php echo $loggedInUser; ?>';

$(document).ready(function() {

	//original ajax request for loading first posts
	$.ajax({
		url: "<?php echo $filePath; ?>inc/ajax/ajax-load-notifications.php",
		type: "POST",
		data: "page=1&loggedInUser=" + loggedInUser,
		cache: false,

		success: function(data) {
			$(".main-notifications").html(data);
		}
	});

	$(window).scroll(function() {
		var inner_height = $('.main-notifications').innerHeight();
		var scroll_top = $('.main-notifications').scrollTop();
		var page = $('.main-notifications').find('.next-page-notifications').val();
		var no_more_data = $('.main-notifications').find('.no-more-notifications').val();

		if ((document.body.scrollHeight == document.body.scrollTop + window.innerHeight) && no_more_data == 'false') {

			var ajaxReq = $.ajax ({
				url: "<?php echo $filePath; ?>inc/ajax/ajax-load-notifications.php",
				type: "POST",
				data: "page=" + page + "&loggedInUser=" + loggedInUser,
				cache: false,

				success: function(response) {
					$('.main-notifications').find('.next-page-notifications').remove(); //remove current .next-page class
					$('.main-notifications').find('.no-more-posts-notifications').remove(); //remove current .no-more-posts class

					$('.main-notifications').append(response);
				}
			});
		} // end if statement

		return false;

	}); //end (window).scroll(function()

});
</script>

<script>
$('#mark-read-form').submit(function(e) {

	var loggedInUser = "<?php echo $loggedInUser; ?>";
	var form = $('#mark-read-form');
	var formData = $(this).serialize();

	e.preventDefault();

	$.ajax({
		type: 'POST',
		url: $(form).attr('action'),
		data: formData,

		success: function(data) {
			$('.notification-card').css("background", "#fff");
		}

	});

});
</script>

<?php include 'inc/footer.php'; ?>
