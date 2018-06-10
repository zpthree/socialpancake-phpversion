<?php
$pageTitle = "recommended";
include 'inc/header.php';

$recommendedFollows = new User($db, $loggedInUser);
$recommendedPageFollows = $recommendedFollows->whoToFollow(100, 'recommended');

?>

<div class="main-columns">

  <?php
    echo $recommendedPageFollows;
  ?>

</div>

<script>
var loggedInUser = '<?php echo $loggedInUser; ?>';
$.ajaxSetup({ cache: false });

$(document).ready(function() {

	//original ajax request for loading first posts
	$.ajax({
		url: "<?php echo $filePath; ?>inc/ajax/ajax-load-posts.php",
		type: "POST",
		data: "page=1&loggedInUser=" + loggedInUser,

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

		if ((document.body.scrollHeight == document.body.scrollTop + window.innerHeight) && noMorePosts == 'false') {

			var ajaxReq = $.ajax ({
				url: "<?php echo $filePath; ?>inc/ajax/ajax-load-posts.php",
				type: "POST",
				data: "page=" + page + "&loggedInUser=" + loggedInUser,
				cache: false,

				success: function(response) {
					$('.posts-area').find('.next-page').remove(); //remove current .next-page class
					$('.posts-area').find('.no-more-posts').remove(); //remove current .no-more-posts class

					$('.loading').hide();
					$('.posts-area').append(response);
				}
			});
		} // end if statement

		return false;

	}); //end (window).scroll(function()

});
</script>

<?php include 'inc/footer.php'; ?>
