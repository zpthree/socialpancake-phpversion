<?php
$pageTitle = "search";
include 'inc/header.php';

if (isset($_GET['q'])) {
	$query = $_GET['q'];
} else {
	$query = "";
}

?>

<div class="search-results-page">
	<div class="mobile-search">
		<input type="hidden" id='scrollPos'value="">
		<form action="<?php echo $filePath; ?>i/search" method="GET" name="seach_form" class="search-box">
			<input type="text" onfocus="getLiveSearchUsers(this.value, '<?php echo $loggedInUser; ?>')" onkeyup="getLiveSearchUsers(this.value, '<?php echo $loggedInUser; ?>')" name='q' placeholder='Search for a user...' autocomplete='off' id='search-text-input' class="mobile-search-bar">
			<button class="search-button">
				<svg class="search-icon" height="32px" version="1.1" viewBox="0 0 32 32" width="32px" xmlns="http://www.w3.org/2000/svg" xmlns:sketch="http://www.bohemiancoding.com/sketch/ns" xmlns:xlink="http://www.w3.org/1999/xlink">
					<path d="M19.4271164,21.4271164 C18.0372495,22.4174803 16.3366522,23 14.5,23 C9.80557939,23 6,19.1944206 6,14.5 C6,9.80557939 9.80557939,6 14.5,6 C19.1944206,6 23,9.80557939 23,14.5 C23,16.3366522 22.4174803,18.0372495 21.4271164,19.4271164 L27.0119176,25.0119176 C27.5621186,25.5621186 27.5575313,26.4424687 27.0117185,26.9882815 L26.9882815,27.0117185 C26.4438648,27.5561352 25.5576204,27.5576204 25.0119176,27.0119176 L19.4271164,21.4271164 L19.4271164,21.4271164 Z M14.5,21 C18.0898511,21 21,18.0898511 21,14.5 C21,10.9101489 18.0898511,8 14.5,8 C10.9101489,8 8,10.9101489 8,14.5 C8,18.0898511 10.9101489,21 14.5,21 L14.5,21 Z" id="search"/>
				</svg>
			</button>
		</form>
		<!-- search results will be generated here with ajax -->
		<div class="search-results">
		</div>

	</div>

	<?php
		if (isset($_GET['q']) && $query == "") {
			echo "<h1>You forgot to search for something!</h1>";
		} else if (isset($_GET['q'])) {

			$names = explode(" ", $query);

			if (count($names) == 2) {
				$usersReturnedQuery = $db->query("
					SELECT * FROM users WHERE (first_name LIKE '%$names[0]%' AND last_name LIKE '%$names[1]%') AND closed_account = 'no' LIMIT 6
				");
			}

			// else if query has one word only, search first names and last names
			else {
				$usersReturnedQuery = $db->query("
					SELECT * FROM users WHERE (first_name LIKE '%$names[0]%' OR last_name LIKE '%$names[0]%' OR username LIKE '%$names[0]%' OR email LIKE '$names[0]') AND closed_account = 'no' LIMIT 6
				");
			}


			//check if results were found
			if ($usersReturnedQuery->num_rows == 0) {
				echo "<h4 id='results-found'>" . $usersReturnedQuery->num_rows . " results found</h4>";
			} else if ($usersReturnedQuery->num_rows == 1) {
				echo "<h4 id='results-found'>" . $usersReturnedQuery->num_rows . " result found</h4>";
			} else {
				echo "<h4 id='results-found'>" . $usersReturnedQuery->num_rows . " results found</h4>";
			}

			echo "<div class='search-results-area'>";
			while ($user = $usersReturnedQuery->fetch_assoc()) {
				$userObj = new user($db, $loggedInUserRow['username']);

				$button = "";
				$mutualFriends = "";

				if ($user['username'] != $loggedInUserRow['username']) {

					//generate button based on friendship status
					if ($userObj->isFollowing($user['username'])) {
						$button = "<input type='submit' name='" . $user['username'] . "' class='danger' value='Remove Friend'>";
					} else if ($userObj->didReceiveRequest($loggedInUserRow['username'])) {
						$button = "<input type='submit' name='" . $user['username'] . "' class='warning' value='Respond'>";
					} else if ($userObj->didSendRequest($loggedInUserRow['username'])) {
						$button = "<input class='warning' value='Request Sent'>";
					}  else {
						$button = "<input type='submit' name='" . $user['username'] . "' class='success' value='Add Friend'>";
					}

					// $mutualFriends = $userObj->getMutualFollowing($user['username']) . " friends in common";

					//button forms

				}

				echo "<div class='search-result-card'>
						<div class='results-user-holder'>
							<div class='result_profile'>
								<a href='" . $filePath . $user['username'] . "'><img src='" . $filePath . $user['profile_pic'] ."'></a>
							</div>
							<div class='result-user-info'>
								<a href='" . $filePath . $user['username'] . "'>" .  $user['first_name'] . " " . $user['last_name'] . "
									<p id='grey'>@" . $user['username'] . "</p>
								</a>
							</div>
						</div>
					</div>
					";
			} // end while
			echo "</div>";
		}
	?>
</div>
<script>
var loggedInUser = "<?php echo $loggedInUser; ?>";
</script>
<?php include 'inc/footer.php'; ?>
