<?php
	$pageTitle = "trending";
	include 'inc/header.php';

	if(isset($_POST['post_submit'])) {
		$post = new Post($db, $loggedInUser);
		$post->submitPost($_POST['post_text'], 'none');
	}

	if(isset($_GET['hashtag'])) {
		$hashtag = $_GET['hashtag'];
	} else {
		$hashtag = "";
	}
?>

<div class="trending-name-bar">
	<div class="trending-name-inner">
	<?php if (isset($_GET['hashtag'])) { ?>
			<h3>Hashtag >> #<?php echo $hashtag; ?></h3>
	<?php } else { ?>
		<h3>Trending Hashtags<?php echo $hashtag; ?></h3>
	<?php } ?>
	</div>
</div>

<div class="trending-page">

	<div class="left-side-panels <?php if (!isset($_GET['hashtag'])) { echo 'desktop-trending-center'; } ?>"<?php if (!isset($_GET['hashtag'])) { echo 'style=\'width: 500px;\''; } ?>>

		<div class="left-trending-side">

			<!-- <div class="trends desktop-only-trend"> -->
			<div class="side-trending-outer">
			  <h2>Trending</h2>

			<?php
			$query = $db->query(
				"SELECT * FROM trends ORDER BY hits DESC LIMIT 8"
			);

			foreach ($query as $row) {

				$word = $row['title'];
				$hits = $row['hits'];
				if ($hits == 1) {
	 				$posts = 'person is talking about this.';
	 			} else {
	 				$posts ='people are talking about this.';
	 			}
				$word_dot = strlen($word) >= 15 ? "..." : "";

				$trimmed_word = str_split($word, 15);
				$trimmed_word = $trimmed_word[0];

				echo "<div class='trending-side'>
						<a href='" . $filePath . "i/trending/hashtag/" . substr($word,1) . "'><h5>$trimmed_word $word_dot</h5></a>
				 		<p>$hits $posts</p>
				 	  </div>";


			}

			?>
			</div>

		</div>

	</div>

	<div class="main-columns <?php if (!isset($_GET['hashtag'])) { echo 'trending-main-columns-hide'; } else { echo ''; } ?>">

		<div class="trends mobile-only-trend">

			<div class="trends-inner" <?php if (!isset($_GET['hashtag'])) { echo "style='display: block;'";} ?>>

			<?php
				$query = $db->query(
					"SELECT * FROM trends ORDER BY hits DESC LIMIT 10"
				);

				foreach ($query as $row) {

					$word = $row['title'];
					$hits = $row['hits'];
					if ($hits == 1) {
		 				$posts = 'person is talking about this.';
		 			} else {
		 				$posts ='people are talking about this.';
		 			}
					$word_dot = strlen($word) >= 15 ? "..." : "";

					$trimmed_word = str_split($word, 15);
					$trimmed_word = $trimmed_word[0];

					echo "<a id='trending-link' href='" . $filePath . "i/trending/hashtag/" . substr($word,1) . "'><div class='trending-side'>
							<h4>$trimmed_word $word_dot</h4>
					 		<p>$hits $posts</p>
					 	  </div></a>";


				}

			?>
			</div>

		</div>

		<div class="trending-feed feed">
			<div class="posts-area">
			</div>
		</div>

	</div>

	<div class="right-side-panels" <?php if (!isset($_GET['hashtag'])) { echo "style='display: none;'"; }?>>
		<?php

			echo $recommendedFollows;

		?>

	</div>

</div>

<script>
var loggedInUser = '<?php echo $loggedInUser; ?>';
var hashtag = '<?php echo $hashtag; ?>';

$(document).ready(function() {

	//original ajax request for loading first posts
	$.ajax({
		url: "<?php echo $filePath; ?>inc/ajax/ajax-load-trending-posts.php",
		type: "POST",
		data: "page=1&loggedInUser=" + loggedInUser + "&hashtag=" + hashtag,

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
				url: "<?php echo $filePath; ?>inc/ajax/ajax-load-trending-posts.php",
				type: "POST",
				data: "page=" + page + "&loggedInUser=" + loggedInUser + "&hashtag=" + hashtag,
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
<script>
	<?php if (isset($_GET['hashtag'])) { ?>
		$('#mobile-trending-header').on('click', function() {
			$('.trends-inner').slideToggle();
		});
	<?php } else { ?>
		$('#mobile-trending-header').css("cursor", "default")
	<?php } ?>
</script>
<?php include 'inc/footer.php'; ?>
