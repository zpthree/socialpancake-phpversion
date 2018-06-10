<?php
  require 'db-connection.php';
  include 'classes/user.php';
?>
<div class="side-trending-outer" id="<?php echo $trendingDisplay; ?>">
  <h2>Trending</h2>
  <?php
  $trends = $db->query(
    "SELECT * FROM trends ORDER BY hits DESC LIMIT 6"
  );
  $trendsRow = $trends->fetch_assoc();

  foreach ($trends as $trend) {

    $trendsUsers = $trend['users'];
    $trendsUsersArray = explode(",", $trendsUsers);
    $trendUsersNum = count($trendsUsersArray) - 2;

    foreach ($trendsUsersArray as $trendUser) {
      $trendPics = $db->query("
        SELECT profile_pic FROM users WHERE username = '$trendUser'
      ");
      $trendPics = $trendPics->fetch_assoc();
      $trendUserPics = "<img src='" . $filePath . $trendPics['profile_pic'] . "'>";
    }

    $word = $trend['title'];
    $hits = $trend['hits'];
    if ($trendUsersNum == 1) {
      $posts = 'person is talking about this';
    } else {
      $posts ='people are talking about this';
    }
    $word_dot = strlen($word) >= 15 ? "..." : "";

    $trimmed_word = str_split($word, 15);
    $trimmed_word = $trimmed_word[0];

    echo "<div class='trending-side'>
        <a href='" . $filePath ."i/trending/hashtag/" . substr($word,1) . "'><h5>$trimmed_word $word_dot</h5></a>
        <p>$trendUsersNum $posts</p>
        </div>";


  }

  ?>
</div>
