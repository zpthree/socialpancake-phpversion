<?php
	$pageTitle = "messages";
	include 'inc/header.php';

	if(isset($_POST['post_submit'])) {
		$post = new Post($db, $loggedInUser);
		$post->submitPost($_POST['post_text'], 'none');
	}

	$messageObj = new Message($db, $loggedInUser);

	// checking which user is being messaged
	if (isset($_GET['convo'])) {
		$userTo = $_GET['convo'];
		$_SESSION['convo'] = $userTo;
	}

	if (!isset($userTo)) {
		$userTo = 'new';
		$_SESSION['convo'] = $userTo;
	}

	if ($userTo != "new") {
		$userToObj = new User($db, $userTo);
	}

?>

<div class="messages">

	<div class="conversations <?php if (!isset($_GET['convo'])) { echo 'conversations-wide'; } ?>" id="conversations">
		<a id="new-message" href="<?php echo $filePath; ?>i/messages/new"><h5>New Message</h5></a>
		<div class="loaded_conversations">

		</div>
	</div>

	<div class="messages-outer">

		<div class="messages-inner">

			<?php if ($userTo != "new") {
			echo "<div class='message-data-holder'>

			</div>";
			} ?>
			<div class="message_post">

				<form action="inc/ajax/ajax-send-message.php" method="POST" class="message-form">
					<?php
					if ($userTo == "new") {
						echo "<h4>Select the friend you would like to message.</h4>";
						?>
						<input type='text' onkeyup='getUsers(this.value, "<?php echo $loggedInUser; ?>")' name='q' placeholder='Name' autocomplete='off' id='search_text_input'>
						<?php
						echo "<div class='results'></div>";
					} else {
						echo "<div class='message-send-body'>
								<textarea name='message_body' id='message-textarea' placeholder='Write your message...'></textarea>
							  	<input type='submit' name='post_message' class='info' id='message-submit' value='Send'>
							  </div>
						  	";
					}
					?>
				</form>

			</div>

		</div>

	</div>

</div>

<script>
var loggedInUser = '<?php echo $loggedInUser; ?>';
$(document).ready(function() {
	var userTo = '<?php echo $userTo; ?>';
	//ajax request that submits messages
	$('#message-submit').click(function(e) {
		var form = $('.message-form');
		var formData = $(form).serialize();
		e.preventDefault();

		$.ajax({
			type: 'POST',
			url: "<?php echo $filePath; ?>inc/ajax/ajax-send-message.php",
			data: formData
		})
		.done(function(response) {
			$('#message-textarea').val("");
			$('.message-data-holder').load('<?php echo $filePath; ?>inc/ajax/ajax-message-page-data.php',function () {
				var div = document.getElementById("scroll-messages");
				div.scrollTop = div.scrollHeight;
		    });
		})
		.fail(function(data) {
			alert('Failed to submit post!');
		});

	});

	$('#scroll-messages').scroll(function() {
	  if ($('#scroll-messages').html().length) {
	    scroll_l = $('#scroll-messages').scrollLeft();
	    scroll_t = $('#scroll-messages').scrollTop();
	  }
	});

	function loadlink(){
		var scroll_l = $('#scroll-messages').scrollLeft(),
			scroll_t = $('#scroll-messages').scrollTop();
	    $('.message-data-holder').load('<?php echo $filePath; ?>inc/ajax/ajax-message-page-data.php',function () {
			 $('#scroll-messages').scrollLeft(scroll_l);
 			 $('#scroll-messages').scrollTop(scroll_t);
	    });
	}

	setInterval(function(){
	    loadlink() // this will run after every 5 seconds
	    $('.loaded_conversations').load('<?php echo $filePath; ?>inc/ajax/ajax-message-convos.php',function () {
		});
	}, 5000);

});
</script>
<script>
$('.message-data-holder').load('<?php echo $filePath; ?>inc/ajax/ajax-message-page-data.php',function () {
	var div = document.getElementById("scroll-messages");
	div.scrollTop = div.scrollHeight;
});
$('.loaded_conversations').load('<?php echo $filePath; ?>inc/ajax/ajax-message-convos.php',function () {
});
</script>
<script>
$(document).ready(function () {
var getVar = "<?php echo $_GET['convo']; ?>";
if (getVar != "") {

	if ($(window).width() < 850) {

			$('.messages-outer').show();
			$('.conversations').hide();
			$('#mobile-new-post').hide();

	} else {

			$('.messages-outer').show();

	}

}
});
</script>
<script>
$(window).resize(function() {
var getVar = "<?php echo $_GET['convo']; ?>";

 if ($(window).width() < 850) {

 			$('.messages-outer').show();
 			$('.conversations').hide();
 			$('#mobile-new-post').hide();

 	} else {

			$('.messages-outer').show();
			$('.conversations').show();
			$('#mobile-new-post').show();

 	}

 });
 </script>
<?php include 'inc/footer.php'; ?>
