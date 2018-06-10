		</div>
	</div>

	<div id="post_form" class="modal fade" tabindex="0" role="dialog">

	  <div class="modal-dialog" role="document">

	    <div class="modal-content">

	      <div class="modal-header">

	        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
	        <h4 class="modal-title">Say Something <?php if ($pageTitle == 'profile' && $username != $loggedInUser) {echo ' to ' . $profileUserRow['first_name']; } ?></h4>

	      </div>

	      <div class="modal-body">

	       <form class="site-post" action="/inc/ajax/ajax-submit-posts.php" method="POST" enctype="multipart/form-data">
	      		<div class="form-group">
	      			<textarea class='autoExpand form-control' rows='4' data-min-rows='4' placeholder="What's happening, <?php echo $loggedInUserRow['first_name']; ?>?" name="post_body"><?php
								if ($pageTitle == 'profile' && $username != $loggedInUser) {
									echo '@' . $profileUserRow['username'] . ' ';
								} else if ($pageTitle == 'trending' && isset($_GET['hashtag'])) {
									echo ' #' . $hashtag;
								}
							?></textarea>
							<input type="hidden" name="youtubeVideo" id="youtubeVideo" value="">
							<input type="hidden" name="postId" id='postEdit' value="">
	      			<input type="hidden" name="username" id="formUsername" value="<?php echo $loggedInUser; ?>">
							<input type="file" name="fileToUpload" id='fileToUpload' style='display: none;'>
	      		</div>
	      	</form>

					<div id='file-img'>
						<img src="" alt="">
						<button type="button" id="file-img-close">&times;</button>
					</div>
					<div id="youtube-video-edit">

					</div>

	      </div>

	      <div class="modal-footer">

					<button type="button" name="button" class="image-upload">
						<svg version="1.1" id="upload-icon" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 44 34" xml:space="preserve">
						<path d="M38,0H6C2.7,0,0,2.7,0,6v22c0,3.3,2.7,6,6,6h32c3.3,0,6-2.7,6-6V6C44,2.7,41.3,0,38,0z M40,28c0,1.1-0.9,2-2,2
							H6c-1.1,0-2-0.9-2-2V6c0-1.1,0.9-2,2-2h32c1.1,0,2,0.9,2,2V28z"/>
						<polygon points="3,21 11,14 20,23 27,18 41,28 40,31 5,31 "/>
						<circle cx="32" cy="10" r="3"/>
						</svg>
						<div class="file-name"></div>
					</button>

					<button type="button" class="btn btn-primary" name="post_button" id="submit_post">Post</button>

	      </div>

	    </div><!-- /.modal-content -->

	  </div><!-- /.modal-dialog -->

	</div><!-- /.modal -->

<script>
//ajax request that submits loggedInUser's posts
$('#submit_post').click(function(e) {
	var form = $('.site-post');
	var formData = $(form).serialize();
	var title = "<?php echo $pageTitle; ?>";
	// form.append('media', file);
	e.preventDefault();
	$.ajax({
		type: 'POST',
		url: "/inc/ajax/ajax-submit-posts.php",
		data: new FormData($('.site-post')[0]),
	  cache: false,
	  contentType: false,
		processData: false
	})
	.done(function(response) {
		$("#post_form").modal('hide');
		if (title == "home") {
			location.reload();
		} else {
			$('#post_form textarea').val('');
			$('#post-successful').css("top", "50px");
			setTimeout(function() { $('#post-successful').css("top", "0"); }, 2500);
		}
	})
	.fail(function(data) {
		alert('Failed to submit post!');
	});

});
</script>

<script>
<?php if (isset($_GET['s'])) { ?>
		$('.messages-outer').show();
		$('.wrapper').hide();
<?php } ?>
</script>

<script>
<?php if(isset($_GET['hashtag'])) { ?>
	var getHashtag = "<?php echo $hashtag; ?>";
<?php } ?>
$(function() {
	$('#post_form').on('shown.bs.modal', function () {
	 if (getHashtag != "") {
		 	$('.form-control').caretToStart();
		 }
	})
});
</script>
<script>
function readURL(input) {

	if (input.files && input.files[0]) {
			var reader = new FileReader();

			reader.onload = function (e) {
					$('#file-img img').attr('src', e.target.result);
			}

			reader.readAsDataURL(input.files[0]);
	}
}

$("#fileToUpload").change(function(){
		readURL(this);
		$('#file-img').css("display", "flex");
});
</script>
<script>
var pageTitle = "<?php echo $pageTitle; ?>";
if (pageTitle != "trending") {
	$('.left-trending-side').load('/inc/trending.php',function () {
	});
}
</script>
<script> //this will run every 5 seconds and update the notification badges
$('#notification-badge').load('/inc/ajax/load-notifications.php',function () {});
$('#message-badge').load('/inc/ajax/load-message-notifications.php',function () {});

function loadlink(){
		$('#notification-badge').load('/inc/ajax/load-notifications.php',function () {});
		$('#message-badge').load('/inc/ajax/load-message-notifications.php',function () {});
}

setInterval(function(){
		loadlink() // this will run after every 5 seconds
		$('#notification-badge').load('/inc/ajax/load-notifications.php',function () {});
		$('#message-badge').load('/inc/ajax/load-message-notifications.php',function () {});
}, 5000);
</script>
</body>
</html>
