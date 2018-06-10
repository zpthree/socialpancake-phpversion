<?php
$pageTitle = "notification-post";
include("inc/header.php");

if(isset($_GET['id'])) {
	$id = $_GET['id'];
}
else {
	$id = 0;
}
?>

<div class="main-columns post-id-page">

	<div class="feed">

		<div class="posts-area">

		<?php
			$post = new Post($db, $loggedInUser);
			$post->getSinglePost($id);
		?>

		</div>

	</div>

</div>

<script>
var loggedInUser = '<?php echo $loggedInUser; ?>';
</script>

<?php include 'inc/footer.php'; ?>
