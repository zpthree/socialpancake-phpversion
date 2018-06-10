<?php
class Post
{

	private $userObj;
	private $db;

	public function __construct($db, $user){
		$this->db = $db;
		$this->userObj = new User ($this->db, $user);
		$this->filePath = "/";
	}

	public function submitPost($postBody, $userTo, $imageName, $editId) {
		$postBody = strip_tags($postBody); //remove any HTML tags
		$postBody = preg_replace('/(\r\n|\n|\r)/', ' new_line_80636', $postBody); //deletes all spaces
		$postBody = preg_replace('/,/', 'comma_break_80636', $postBody); //deletes all spaces
		// $checkEmpty = preg_replace('/\s+/', '', $postBody); //deletes all spaces

		if (($postBody != "" || $imageName != "") && $editId == "") {
			$imageName = substr($imageName, 6);

			//embed youtube links
			$bodyArray = preg_split("/[\s,]+/", $postBody);

			foreach ($bodyArray as $key => $value) {

				if (strpos($value, "www.youtube.com/watch?v=") !== false) {

					$link = preg_split("!&!", $value);

					$value = preg_replace("!watch\?v=!", "embed/", $link[0]);
					$embedded_value = "<div class='youtube-video'><iframe width='100%' height='270' frameborder='0' allowfullscreen='allowfullscreen' src='" . $value . "'></iframe></div>";
					$embedded_value = preg_replace('/new_line_80636/', '', $embedded_value);
					$bodyArray[$key] = $value;

				}

			}

			// split youtube video from text
				//keep line breaks in text

			$youtubeVideo = "";
			$newBody = "";

			foreach ($bodyArray as $body) {
				if (!strpos($body, "youtube")) {
					$newBody .= $body . " ";
				}
			}

			$postBody = preg_replace('/$link[0]/', "", $newBody);

			if (!empty($value)) {
				$youtubeVideo = $embedded_value;
			} else {
				$youtubeVideo = "";
			}

			$postBody = preg_replace('/comma_break_80636/', ',', $postBody);
			$postBody = $this->db->escape_string($postBody);

			//get username of person sending post
			$addedBy = $this->userObj->getUsername();
			$postBody = preg_replace('/new_line_80636/', "<br>", $postBody); //deletes all spaces
			$postBody = preg_replace('/<br> <br> <br> <br> <br> <br> <br> <br> <br> <br> <br> <br> <br>/', "<br><br>", $postBody);
			$postBody = preg_replace('/<br> <br> <br> <br> <br> <br> <br> <br> <br> <br> <br> <br>/', "<br><br>", $postBody);
			$postBody = preg_replace('/<br> <br> <br> <br> <br> <br> <br> <br> <br> <br> <br>/', "<br><br>", $postBody);
			$postBody = preg_replace('/<br> <br> <br> <br> <br> <br> <br> <br> <br> <br>/', "<br><br>", $postBody);
			$postBody = preg_replace('/<br> <br> <br> <br> <br> <br> <br> <br> <br>/', "<br><br>", $postBody);
			$postBody = preg_replace('/<br> <br> <br> <br> <br> <br> <br> <br>/', "<br><br>", $postBody);
			$postBody = preg_replace('/<br> <br> <br> <br> <br> <br> <br>/', "<br><br>", $postBody);
			$postBody = preg_replace('/<br> <br> <br> <br> <br> <br>/', "<br><br>", $postBody);
			$postBody = preg_replace('/<br> <br> <br> <br> <br>/', "<br><br>", $postBody);
			$postBody = preg_replace('/<br> <br> <br> <br>/', "<br><br>", $postBody);

			$uniqueId = uniqid() . $addedBy;
			//insert the post into the database
			$insertPost = $this->db->prepare("
				INSERT INTO posts (body, image, youtube, added_by, user_to, date_added, closed_account, deleted, likes, shared_by, post_unique_id, edited)
				VALUES (?, ?, ?, ?, ?, NOW(), 'no', 'no', '0', 'none', ?, 'no')
			");

			$insertPost->bind_Param('ssssss', $postBody, $imageName, $youtubeVideo, $addedBy, $userTo, $uniqueId);
			$insertPost->execute();

			$returnedId = $this->db->insert_id;
			$userTo = [];

			foreach ($bodyArray as $key => $value) {
				if (strpos($value, "@") !== false) {

					$link = preg_split("!@!", $value);

					$value = preg_replace("!@!", "", $link[1]);
					$value = $value;
					$bodyArray[$key] = $value;
					$userTo[] = $link[1];
				}
			}
			//insert notification into database
			$userFullname = $this->userObj->getFirstAndLastName();
			$loggedInUser = $this->userObj->getUsername();
			foreach ($userTo as $user) {
				$link = "i/post/" . $returnedId;
				$message = $userFullname . " mentioned you in a post.";
				$type = "mention";

				$insertNotification = $this->db->prepare("
					INSERT INTO notifications (post_id, user_to, user_from, type, message, link, datetime, opened, viewed)
					VALUES (?, ?, ?, ?, ?, ?, NOW(), 'no', 'no')
				");
				$insertNotification->bind_param('ssssss', $returnedId, $user, $loggedInUser, $type, $message, $link);
				$insertNotification->execute();

			}

			//update post count for user
			$numPosts = $this->userObj->getNumPosts();
			$numPosts++;
			$updatePostCount = $this->db->query("
				UPDATE users SET num_posts = '$numPosts' WHERE username = '$addedBy'
			");

			$stopWords =
			"
				#a
				#able
				#about
				#above
				#abst
				#accordance
				#according
				#accordingly
				#across
				#act
				#actually
				#added
				#adj
				#affected
				#affecting
				#affects
				#after
				#afterwards
				#again
				#against
				#ah
				#all
				#almost
				#alone
				#along
				#already
				#also
				#although
				#always
				#am
				#among
				#amongst
				#an
				#and
				#announce
				#another
				#any
				#anybody
				#anyhow
				#anymore
				#anyone
				#anything
				#anyway
				#anyways
				#anywhere
				#apparently
				#approximately
				#are
				#aren
				#arent
				#arise
				#around
				#as
				#aside
				#ask
				#asking
				#asshole
				#at
				#auth
				#available
				#away
				#awfully
				#b
				#back
				#be
				#became
				#because
				#become
				#becomes
				#becoming
				#been
				#before
				#beforehand
				#begin
				#beginning
				#beginnings
				#begins
				#behind
				#being
				#believe
				#below
				#beside
				#besides
				#between
				#beyond
				#bitch
				#bitchass
				#biol
				#both
				#brief
				#briefly
				#but
				#by
				#c
				#ca
				#came
				#can
				#cannot
				#can't
				#cause
				#causes
				#certain
				#certainly
				#co
				#com
				#come
				#comes
				#contain
				#containing
				#contains
				#could
				#couldnt
				#d
				#date
				#did
				#didn't
				#different
				#do
				#does
				#doesn't
				#doing
				#done
				#don't
				#down
				#downwards
				#due
				#during
				#e
				#each
				#ed
				#edu
				#effect
				#eg
				#eight
				#eighty
				#either
				#else
				#elsewhere
				#end
				#ending
				#enough
				#especially
				#et
				#et-al
				#etc
				#even
				#ever
				#every
				#everybody
				#everyone
				#everything
				#everywhere
				#ex
				#except
				#f
				#far
				#few
				#ff
				#fifth
				#first
				#five
				#fix
				#followed
				#following
				#follows
				#for
				#former
				#formerly
				#forth
				#found
				#four
				#from
				#fuck
				#fucking
				#fucker
				#further
				#furthermore
				#g
				#gave
			  #get
				#gets
				#getting
				#give
				#given
				#gives
				#giving
				#go
				#goes
				#gone
				#got
				#gotten
				#h
				#had
				#happens
				#hardly
				#has
				#hasn't
				#have
				#haven't
				#having
				#he
				#hed
				#hence
				#her
				#here
				#hereafter
				#hereby
				#herein
				#heres
				#hereupon
				#hers
				#herself
				#hes
				#hi
				#hid
				#him
				#himself
				#his
				#hither
				#home
				#how
				#howbeit
				#however
				#hundred
				#i
				#id
				#ie
				#if
				#i'll
				#im
				#immediate
				#immediately
				#importance
				#important
				#in
				#inc
				#indeed
				#index
				#information
				#instead
				#into
				#invention
				#inward
				#is
				#isn't
				#it
				#itd
				#it'll
				#its
				#itself
				#i've
				#j
				#just
				#k
				#keep
				#keeps
				#kept
				#kg
				#km
				#know
				#known
				#knows
				#l
				#largely
				#last
				#lately
				#later
				#latter
				#latterly
				#least
				#less
				#lest
				#let
				#lets
				#like
				#liked
				#likely
				#line
				#little
				#'ll
				#look
				#looking
				#looks
				#ltd
				#m
				#made
				#mainly
				#make
				#makes
				#many
				#may
				#maybe
				#me
				#mean
				#means
				#meantime
				#meanwhile
				#merely
				#mg
				#might
				#million
				#miss
				#ml
				#more
				#moreover
				#most
				#mostly
				#mr
				#mrs
				#much
				#mug
				#must
				#my
				#myself
				#n
				#na
				#name
				#namely
				#nay
				#nd
				#near
				#nearly
				#necessarily
				#necessary
				#need
				#needs
				#neither
				#never
				#nevertheless
				#new
				#next
				#nine
				#ninety
				#no
				#nobody
				#non
				#none
				#nonetheless
				#noone
				#nor
				#normally
				#nos
				#not
				#noted
				#nothing
				#now
				#nowhere
				#o
				#obtain
				#obtained
				#obviously
				#of
				#off
				#often
				#oh
				#ok
				#okay
				#old
				#omitted
				#on
				#once
				#one
				#ones
				#only
				#onto
				#or
				#ord
				#other
				#others
				#otherwise
				#ought
				#our
				#ours
				#ourselves
				#out
				#outside
				#over
				#overall
				#owing
				#own
				#p
				#page
				#pages
				#part
				#particular
				#particularly
				#past
				#per
				#perhaps
				#placed
				#please
				#plus
				#poorly
				#possible
				#possibly
				#potentially
				#pp
				#predominantly
				#present
				#previously
				#primarily
				#probably
				#promptly
				#proud
				#provides
				#put
				#q
				#que
				#quickly
				#quite
				#qv
				#r
				#ran
				#rather
				#rd
				#re
				#readily
				#really
				#recent
				#recently
				#ref
				#refs
				#regarding
				#regardless
				#regards
				#related
				#relatively
				#research
				#respectively
				#resulted
				#resulting
				#results
				#right
				#run
				#s
				#said
				#same
				#saw
				#say
				#saying
				#says
				#sec
				#section
				#see
				#seeing
				#seem
				#seemed
				#seeming
				#seems
				#seen
				#self
				#selves
				#sent
				#seven
				#several
				#sex
				#shall
				#she
				#shed
				#she'll
				#shes
				#shit
				#shitter
				#should
				#shouldn't
				#show
				#showed
				#shown
				#showns
				#shows
				#significant
				#significantly
				#similar
				#similarly
				#since
				#six
				#slightly
				#so
				#some
				#somebody
				#somehow
				#someone
				#somethan
				#something
				#sometime
				#sometimes
				#somewhat
				#somewhere
				#soon
				#sorry
				#specifically
				#specified
				#specify
				#specifying
				#still
				#stop
				#strongly
				#sub
				#substantially
				#successfully
				#such
				#sufficiently
				#suggest
				#sup
				#sure
				#t
				#take
				#taken
				#taking
				#tell
				#tends
				#th
				#than
				#thank
				#thanks
				#thanx
				#that
				#that'll
				#thats
				#that've
				#the
				#their
				#theirs
				#them
				#themselves
				#then
				#thence
				#there
				#thereafter
				#thereby
				#thered
				#therefore
				#therein
				#there'll
				#thereof
				#therere
				#theres
				#thereto
				#thereupon
				#there've
				#these
				#they
				#theyd
				#they'll
				#theyre
				#they've
				#think
				#this
				#those
				#thou
				#though
				#thoughh
				#thousand
				#throug
				#through
				#throughout
				#thru
				#thus
				#til
				#tip
				#to
				#together
				#too
				#took
				#toward
				#towards
				#tried
				#tries
				#truly
				#try
				#trying
				#ts
				#twice
				#two
				#u
				#un
				#under
				#unfortunately
				#unless
				#unlike
				#unlikely
				#until
				#unto
				#up
				#upon
				#ups
				#us
				#use
				#used
				#useful
				#usefully
				#usefulness
				#uses
				#using
				#usually
				#v
				#value
				#various
				#'ve
				#very
				#via
				#viz
				#vol
				#vols
				#vs
				#w
				#want
				#wants
				#was
				#wasnt
				#way
				#we
				#wed
				#welcome
				#we'll
				#went
				#were
				#werent
				#we've
				#what
				#whatever
				#what'll
				#whats
				#when
				#whence
				#whenever
				#where
				#whereafter
				#whereas
				#whereby
				#wherein
				#wheres
				#whereupon
				#wherever
				#whether
				#which
				#while
				#whim
				#whither
				#who
				#whod
				#whoever
				#whole
				#who'll
				#whom
				#whomever
				#whos
				#why
				#whose
				#willing
				#widely
				#wish
				#with
				#within
				#without
				#wont
				#words
				#world
				#would
				#wouldnt
				#www
				#x
				#y
				#yes
				#yet
				#you
				#youd
				#you'll
				#your
				#youre
				#yours
				#yourself
				#yourselves
				#you've
				#z
				#zero
			";

			$stopWords = preg_split("/[\s,]+/", $stopWords);

			$noPunctuation = preg_replace("/[^a-zA-Z 0-9 \#]+/", "", $postBody);
			preg_match_all("/(#\w+)/", $noPunctuation, $matches);


			// if (strpos($noPunctuation, "height") === false  && strpos($noPunctuation, "width") === false && strpos($noPunctuation, "http") === false) {
				$noPunctuation = preg_split("/[\s,]+/", $noPunctuation);
				if ($matches) {
				    $hashtagsArray = array_count_values($matches[0]);
				    $hashtags = array_keys($hashtagsArray);
				}
				$noPunctuation = $hashtags;

				foreach($stopWords as $value) {
					foreach($noPunctuation as $key => $value2) {
						if (strtolower($value) == strtolower($value2)) {
							$noPunctuation[$key] = "";
						}
					}
				}

				foreach($noPunctuation as $value) {
					$this->calculateTrend(ucfirst($value));
				}

			// }
		} else {
			$bodyArray = preg_split("/(\r\n|\n|\r)/", $postBody);

			$newBody = "";

			foreach ($bodyArray as $body) {
				if (!strpos($body, "youtube")) {
					$newBody .= $body . "\r\n";
				}
			}

			$pos = strrpos($newBody, "\r\n");

	    if ($pos !== false) {
	        $newBody = substr_replace($newBody, "", $pos, strlen("\r\n"));
	    }

			$postBody = preg_replace('/$link[0]/', "", $newBody);

			if (!empty($value)) {
				$youtubeVideo = $embedded_value;
			} else {
				$youtubeVideo = "";
			}

			$postBody = preg_replace('/comma_break_80636/', ',', $postBody);
			$postBody = $this->db->escape_string($postBody);

			$editPost = $this->db->prepare("
				UPDATE posts
				SET body = ?, edited = 'yes'
				WHERE id = '$editId'
			");

			$editPost->bind_Param('s', $postBody);
			$editPost->execute();
		}
	}

	public function calculateTrend($term) {

		$userPost = $this->userObj->getUsername();

		if (!empty($term)) {

			$query = $this->db->query("
				SELECT * FROM trends WHERE title = '$term'
			");
			$trendsRow = $query->fetch_assoc();
			$getTrendUsers = $trendsRow['users'];

				if ($query->num_rows == 0) {

					$userPost = ',' . $userPost . ',';

					//insert post into database
					$insertQuery = $this->db->query("
						INSERT INTO trends (title, users, hits)
						VALUES ('$term', '$userPost', 1)
					");

				} else {

					$usernameToCheck = "," . $userPost . ",";

					if (!strpos($getTrendUsers,$usernameToCheck)) {

						$getTrendUsers .= $userPost . ",";

						$insertQuery = $this->db->query("
							UPDATE trends SET users = '$getTrendUsers' , hits = hits + 1 WHERE title = '$term'
						");

					} else {

						$insertQuery = $this->db->query("
							UPDATE trends SET hits = hits + 1 WHERE title = '$term'
						");

					}

				}

		}

	}

	public function loadPostsFollowing($data, $limit) {

		$page = $data['page'];
		$loggedInUser = $this->userObj->getUsername();
		$userFullname = $this->userObj->getFirstAndLastName();
		$userProfilePic = $this->userObj->getProfilePic();

		if ($page == 1) {
			$start = 0;
		} else {
			$start = ($page - 1) * $limit;
		}

		$postBlock = []; //arrays to return
		$getPosts = $this->db->query("
			SELECT * FROM posts WHERE deleted = 'no' ORDER BY date_added DESC
		");

		// check if loggedInUser is verified
		$verifiedQuery = $this->db->query("
			SELECT verified FROM users WHERE username = '$loggedInUser'
		");
		$verifiedRow = $verifiedQuery->fetch_assoc();
		if ($verifiedRow['verified'] == 'yes') {
			$loggedInUserVerifiedPic = $this->filePath . "img/icons/verified.svg";
			$loggedInUserVerified = 'verified';
		} else {
			$loggedInUserVerifiedPic = "";
			$loggedInUserVerified = "";
		}


		if ($getPosts->num_rows > 0) {

			$numIterations =  0; //number of results checked (not necessarily posted)
			$count = 1;

			while ($postRow = $getPosts->fetch_assoc()) {
				//prepare userTo string so it can be included even if not posted to user
				if ($postRow['user_to'] == 'none') {
					$userTo = "";
				} else {
					$userToObj = new User($this->db, $postRow['user_to']);
					$userToName = $userToObj->getFirstAndLastName();
					$userTo = $postRow['user_to'];
					$userTo = "<a class='user-to' href='" . $userTo . "'>@" . $userTo . "</a>";
				}

				// user info
				$addedBy = $postRow['added_by'];
				$dateTime = $postRow['date_added'];
				$imagePath = $this->filePath . $postRow['image'];
				if ($imagePath === $this->filePath) $imagePath = "";
				$youtubeVideo = $postRow['youtube'];
				$postId = $postRow['id'];
				$uniqueId = $postRow['post_unique_id'];
				$postAdded = $postRow['date_added'];
				$sharedBy = $postRow['shared_by'];
				$body = $postRow['body'];

				$editedPost = $postRow['edited'];

				if ($editedPost == 'yes') {
					$editedPostClass = 'edited-post';
					$editedText = "| Edited";
				} else {
					$editedPostClass = '';
					$editedText = "";
				}

				$editBodyArray = [];
				$editBodyArray[] = preg_split("/<div/", $body);
				$editBody = $editBodyArray[0];
				$newEditBody = [];
				foreach ($editBody as $edit) {
					if (strpos($edit, "youtube")) $edit = "";

					$newEditBody[] = $edit;
				}

				$editPostBody = implode(" ", $newEditBody);
				$editPostBody = preg_replace('/\s+/', ' ', $editPostBody); //deletes all spaces

				//format body text
				$body = "<p>" . preg_replace('#(\\\r|\\\r\\\n|\\\n)#', '</p><p>', $body) . "</p>";
				$body = stripcslashes($body);
				//Convert hashtags to <a> links
				$body = preg_replace("/#([A-Za-z0-9\/\.]*)/", "<a class=\"post-hashtag\" href='" . $this->filePath . "i/trending/hashtag/$1'>#$1</a>", $body);
				//Convert @ tags to <a> links
				$body = preg_replace("/@([A-Za-z0-9\/\.]*)/", "<a class=\"post-hashtag\" href='" . $this->filePath . "$1'>@$1</a>", $body);
				//posting links
				if (strpos($body, "youtube.com") == false) {
					// The Regular Expression filter
					$reg_exUrl = "/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/";
					// Check if there is a url in the body
					if (preg_match($reg_exUrl, $body, $url)) {
							$url[0] = strip_tags($url[0]);
					       // make the urls hyper links
						   $out = strlen($url[0]) > 20 ? substr($url[0],0,30)."..." : $url[0];
					       $body = preg_replace($reg_exUrl, "<a class='post-hashtag' href='$url[0]' target='_blank'>$out</a> ", $body);
					} else {
					       // if no urls in the body just return the body
					       $body = $body;
					}
				}

				//check if user who posted, has their account closed
				$addedByObj = new User($this->db, $addedBy);
				if ($addedByObj->isClosed()) {
					continue;
				}

				$loggedInUserObj = new User($this->db, $loggedInUser);
				if ($loggedInUserObj->isFollowing($addedBy) || $loggedInUserObj->isFollowing($sharedBy)) {

					if ($numIterations++ < $start) {
						continue;
					}

					// once 10 posts have been loaded, break
					if ($count > $limit) {
						break;
					} else {
						$count++;
					}

					if ($loggedInUser == $addedBy) {
						$otherUserMenuOptions = "";
						$loggedInUserMenuOptions = "<li style='cursor: pointer;'><a data-toggle='modal' data-target='#post_form' onClick='javascript:edit$postId()'>Edit Post</a></li>

																				<li style='cursor: pointer;'><a id='post$postId'>Delete Post</a></li>";

					} else {
						$otherUserMenuOptions = "<li data-toggle='modal' data-target='#post_form' 			id='dropdown-post$postId'><a>Say Something to @$addedBy</a></li>

						<li><a href='". $this->filePath ."$addedBy'>Go to @$addedBy's profile</a></li>";
						$loggedInUserMenuOptions = "";
					}

					//get post user info

					$userDetails = $this->db->query ("
						SELECT first_name, last_name, profile_pic, verified, username, private_account FROM users WHERE username = '$addedBy'
					");

					$userRow = $userDetails->fetch_assoc();
					$firstName = $userRow['first_name'];
					$lastName = $userRow['last_name'];
					$profilePic = $userRow['profile_pic'];
					$username = $userRow['username'];

					if ($userRow['private_account'] == 'yes' || $userRow['username'] == $loggedInUser) {
						$privateAccount = "private-account";
					} else {
						$privateAccount = "";
					}

					//check to see if user is verified
					if ($userRow['verified'] == 'yes') {
						$postVerifiedCheck = "<img class='verified' src='" . $this->filePath . "img/icons/verified.svg' title='Verified user'>";
					} else {
						$postVerifiedCheck = "";
					}

					//======================================================================
					//
					//	 check to see if post was shared
					//
					//======================================================================

						$totalShares = "";

						$getUserShared = $this->db->query("
							SELECT * FROM posts WHERE post_unique_id =  '$uniqueId' AND shared_by = '$loggedInUser'
						");
						$userShares = $getUserShared->num_rows;
						$shareMenuOption = "";
						if ($userShares > 0) {
							$sharedIconClass = 'user-shared';
							$shareValue = "Unshare";
							if ($loggedInUser != $addedBy) {
								$shareMenuOption = "<li class='share-menu-option share$postId'><a>Unshare this post</a></li>";
							}
						} else {
							$sharedIconClass= '';
							$shareValue = "Share";
							if ($loggedInUser != $addedBy) {
								$shareMenuOption = "<li class='share-menu-option share$postId'><a>Share this post</a></li>";
							}
						}

						if(!isset($totalShares)) {
							$totalShares = '';
						}

						if (!empty($sharedBy) && $sharedBy != 'none') {

							$getSharedPost = $this->db->query("
								SELECT * FROM posts WHERE post_unique_id = '$uniqueId' AND shared_by = 'none'
							");
							$sharedPost = $getSharedPost->fetch_assoc();

							$getSharedUserName = $this->db->query("
								SELECT first_name, last_name FROM users WHERE username = '$sharedBy'
							");
							$sharedUser = $getSharedUserName->fetch_assoc();


							$sharedDiv = "<div class='shared-div'>
											<a href='$sharedBy' class='post-name'>Shared by&nbsp;<span>" . $sharedUser['first_name'] . " " . $sharedUser['last_name'] . "</span></a>
										 </div>";

							$addedBy = $sharedPost['added_by'];
							$dateTime = $sharedPost['date_added'];
							$uniqueId = $sharedPost['post_unique_id'];
							$sharedUserDetails = $this->db->query ("
								SELECT first_name, last_name, profile_pic, verified, username FROM users WHERE username = '$addedBy'
							");

							$sharedRow = $sharedUserDetails->fetch_assoc();
							$firstName = $sharedRow['first_name'];
							$lastName = $sharedRow['last_name'];
							$profilePic = $sharedRow['profile_pic'];
							$username = $sharedRow['username'];
							$sharedClass = "shared";

							$checkShares = $this->db->query("
								SELECT * FROM posts WHERE post_unique_id = '$uniqueId' AND shared_by <> 'none'
							");
							$totalShares = $checkShares->num_rows;

							if ($totalShares > 0) {
								$totalShares = $totalShares;
							}

						} else {
							$sharedDiv = "";
							$sharedClass = "";
						} // end of shared check

					//timezone
					//set times for posts
					$dateTimeNow = date("Y-m-d H:i:s");
					$postStartDate = new DateTime($dateTime); //time of post
					$endDate = new DateTime($dateTimeNow); //current time
					$postInterval = $postStartDate->diff($endDate); //difference between dates
					$hourDiff = $postInterval->h + ($postInterval->d*24);
					$newtime = strtotime($dateTime);
					if ($hourDiff > 24) {
						$postTimeMessage = date("F j, Y", $newtime);
					} else if ($postInterval->h >=1) {
						if ($postInterval->h == 1) {
							$postTimeMessage = $postInterval->h . " hr";
						} else {
							$postTimeMessage = $postInterval->h . " hr";
						}
					} else if ($postInterval->i >=1) {
						if ($postInterval->i == 1) {
							$postTimeMessage = $postInterval->i . " min";
						} else {
							$postTimeMessage = $postInterval->i . " min";
						}
					} else {
						if ($postInterval->s < 30) {
							$postTimeMessage = "Just now";
						} else {
							$postTimeMessage = $postInterval->s . " sec";
						}
					}

					//======================================================================
					//
					//	check for comments
					//
					//======================================================================

						$commentsCheck = $this->db->query("
							SELECT * FROM comments WHERE post_unique_id = '$uniqueId'
						");
						$commentsCheckNum = $commentsCheck->num_rows;

						$commentsBlock = "";
						$getComments = $this->db->query("
							SELECT * FROM comments WHERE post_unique_id = '$uniqueId' ORDER BY id ASC
						");
						$commentCount = $getComments->num_rows;

						if ($commentCount > 0) {
							$commentCount = $commentCount;
						} else {
							$commentCount = "";
						}

						if ($commentCount != 0) {

							while ($comment = $getComments->fetch_assoc()) {
								$commentBody = "<p>" . preg_replace('#(\\\r|\\\r\\\n|\\\n)#', '</p><p>', $comment['post_body']) . "</p>";
								$commentBody = stripcslashes($commentBody);
								$postedTo = $comment['posted_to'];
								$postedBy = $comment['posted_by'];
								$dateAdded = $comment['date_added'];
								$removed = $comment['removed'];

								$getCommentorInfo = $this->db->query("
									SELECT first_name, last_name, username, profile_pic, verified FROM users WHERE username = '$postedBy'
								");
								$commentorInfo = $getCommentorInfo->fetch_assoc();
								$commentUser = $commentorInfo['first_name'] . " " . $commentorInfo['last_name'];
								$commentUsername = $commentorInfo['username'];

								if ($commentorInfo['verified'] == 'yes') {
									$commentVerifiedPic = $this->filePath . "img/icons/verified.svg";
									$commmentUserVerified = "verified";
								} else {
									$commentVerifiedPic = "";
									$commmentUserVerified = "";
								}

								//set times for comments
								//timezone
								$commentStartDate = new DateTime($dateAdded); //time of comment
								$commentInterval = $commentStartDate->diff($endDate); //difference between dates
								$commentHourDiff = $commentInterval->h + ($commentInterval->d*24);
								$newtime = strtotime($dateAdded);

								if ($commentHourDiff >= 24) {
									$commentTimeMessage = date("F j, Y", $newtime);
								} else if ($commentInterval->h >=1) {
									if ($commentInterval->h == 1) {
										$commentTimeMessage = $commentInterval->h . " hr";
									} else {
										$commentTimeMessage = $commentInterval->h . " hr";
									}
								} else if ($commentInterval->i >=1) {
									if ($commentInterval->i == 1) {
										$commentTimeMessage = $commentInterval->i . " min";
									} else {
										$commentTimeMessage = $commentInterval->i . " min";
									}
								} else {
									if ($commentInterval->s < 30) {
										$commentTimeMessage = "Just now";
									} else {
										$commentTimeMessage = $commentInterval->s . " sec";
									}
								}


								$commentsBlock .= "
													<div class='full-comment'>

														<div>

															<a href='" . $this->filePath . "$commentUsername'>
																<img src='" . $this->filePath . $commentorInfo['profile_pic'] . "' class='comment-profile-pic' title='$commentUser'>
															</a>


														</div>

														<div class='comment-section'>
															<div class='comment-user-name'>
																<a href='" . $this->filePath . "$commentUsername' class='post-name'><span>$commentUser</span><img class='$commmentUserVerified' src='" . "$commentVerifiedPic'></a><span class='post-username'>@$commentUsername</span>
															</div
															<span class='comment-time'>$commentTimeMessage</span>
															<div class='comment-body'>$commentBody</div>
														</div>
													</div>
												";

							}

						}//end of comments check

					//======================================================================
					//
					//	check for previous likes
					//
					//======================================================================


						$checkLikes = $this->db->query("
							SELECT * FROM likes WHERE username = '$loggedInUser' AND post_unique_id = '$uniqueId'
						");
						$userLikes = $checkLikes->num_rows;

						if ($userLikes > 0) {
							$likesName = "unlike_button";
							$likesValue = "Unlike";
							$userLikes = $userLikes;
							$likeFill = 'active';
						} else {
							$likesName = "like_button";
							$likesValue = "Like";
							$userLikes = "";
							$likeFill = 'inactive';
						}

						$checkLikes2 = $this->db->query("
							SELECT * FROM likes WHERE post_unique_id = '$uniqueId'
						");
						$totalLikes = $checkLikes2->num_rows;

						if ($totalLikes > 0) {
							$totalLikes = $totalLikes;
						} else {
							$totalLikes = "";
						} // end of likes


					if ($imagePath != "") {
						$imageDiv = "<div class='postedImage'>
													<a class='posted-image$postId' href='$imagePath'><img src='$imagePath'></a>
												</div>";
					} else {
						$imageDiv = "";
					}

					$postBlock[] = "
									<div class='full-post-outer $sharedClass' id='full-post-outer$postId'>
										$sharedDiv

										<div class='full-post' id='full-post$postId'>

											<div class='status-post'>

												<div class='body-content'>

													<div class='posted-by' style='color: #acacac;'>

														<div class='post-profile-pic'>
															<a href='" . $this->filePath . "$addedBy'><img src='" . $this->filePath . "$profilePic' alt='Profile Picture for $firstName $lastName'></a>
														</div>

														<div class='posted-by-info'>
															<div class='person-details'>
																<a href='" . $this->filePath . "$addedBy' class='post-name'><span>$firstName $lastName</span>$postVerifiedCheck</a><span class='post-username'id='post-username$postId'>@$username</span>
															</div>
															<span>$postTimeMessage $editedText</span>
														</div>

														<div class='delete-button btn-group'>
														  <button type='button' data-toggle='dropdown' aria-haspopup='true' aria-expanded='false'>
														  	<img class='delete_button' src='" . $this->filePath . "img/icons/dot-menu.svg' alt='Post Menu'>
														  </button>
														  <ul class='dropdown-menu'>
																$otherUserMenuOptions
														    $shareMenuOption
														    $loggedInUserMenuOptions
														  </ul>
														</div>
													</div>

													<div id='post_body'>
														$body

														$youtubeVideo
														$imageDiv
													</div>

												</div>

											</div>

											<div class='newsfeed-post-options'>
												<div class='comment-button newsfeed-buttons' title='Comment on this post.' onClick='javascript:toggle$postId()'>
													<div>
														<svg id='comments$postId' class='comment-svg post-button' mlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink' viewBox='0 0 505.7 512'>
															<path d='M373.1,511c-12.6,0-25.3-2.3-37.8-6.8c-25.4-9.2-45.7-23.4-60.1-42.3l-0.3-0.4h-0.6
																c-7.8,0.6-14.8,0.9-21.5,0.9c-67.3,0-130.5-24-178.1-67.6C27.2,351.2,1,293.3,1,231.7S27.2,112.2,74.7,68.6
																C122.3,25,185.6,1,252.8,1c67.3,0,130.5,24,178.1,67.6c47.5,43.6,73.7,101.5,73.7,163.1c0,85.4-51.2,163.4-133.6,203.6l-0.9,0.4
																l0.3,0.9c3.4,9.2,7.5,18.5,12.3,27.6c10.5,20.2,33.2,30.8,40,33.6C417.3,501.2,399,511,373.1,511L373.1,511z'/>
															<path d='M252.8,2.1c33.9,0,66.7,6.1,97.7,18.1c29.9,11.6,56.7,28.1,79.7,49.2s41.1,45.6,53.7,73
																c13.1,28.3,19.7,58.3,19.7,89.3c0,42.4-12.7,83.8-36.8,119.8c-23.4,35-56.7,63.7-96.2,83l-1.7,0.8l0.7,1.8
																c3.4,9.2,7.6,18.5,12.3,27.7c9.9,19.1,30,29.6,38.6,33.3c-7,3.9-24.1,11.9-47.3,11.9c-12.5,0-25-2.3-37.4-6.8
																c-25.3-9.2-45.3-23.3-59.7-41.9l-0.7-0.9l-1.1,0.1c-7.8,0.6-14.8,0.9-21.4,0.9c-33.9,0-66.7-6.1-97.7-18.1
																c-29.9-11.6-56.7-28.1-79.7-49.3c-23-21.1-41.1-45.6-53.7-73C8.7,292.7,2.1,262.7,2.1,231.7s6.6-61,19.7-89.3
																c12.6-27.3,30.7-51.9,53.7-73s49.8-37.7,79.7-49.2C186.1,8.2,219,2.1,252.8,2.1 M252.8,0C113.2,0,0,103.7,0,231.7
																c0,127.9,113.2,231.7,252.8,231.7c7.3,0,14.4-0.3,21.5-0.9c12.4,16.1,31.3,32,60.6,42.6c13.9,5,26.8,6.9,38.2,6.9
																c31.7,0,52-14.3,52-14.3s-29-10.3-41.3-33.9c-5.1-9.9-9.1-19.1-12.2-27.5c79.8-39,134.1-116,134.1-204.6
																C505.7,103.7,392.5,0,252.8,0L252.8,0z'/>
														</svg>
														<span class='post-options-nums' id='commentsSpan$postId'>$commentCount</span>
													</div>

												</div>
												<div class='newsfeed-buttons' title='Like this post.'>
												<form action='" . $this->filePath . "inc/handlers/likes.php?post_id=$postId&unique_id=$uniqueId' method='POST' class='like-button' id='likes$postId'>
													<input type='hidden' class='likesBody$postId $likesValue' name='post_body' value='$likesValue'>
													<button type='submit' name='like_button'>
														<div class='like-div'>

															<svg id='like$postId' class='like-svg post-button $likeFill' version='1.1' xmlns='http://www.w3.org/2000/svg'viewBox='0 0 1024 1024'>
															<path d='M513.4,141c0,0,277.1-171.5,441.1,60.5c0,0,113.2,181-19.3,387.3c0,0-104.6,197.2-421.7,363.5
																c0,0-287.5-143.6-411.2-347.4c0,0-140.3-188.7-36.2-392.1C66.1,212.8,187-34.5,513.4,141z'/>
															</svg>

															<span class='post-options-nums' id='likesSpan$postId'>$totalLikes</span>
														</div>
													</button>
												</form>
												</div>
												<div class='share-button newsfeed-buttons $privateAccount $sharedIconClass' title='Share this post with your friends.'>
													<form action='" . $this->filePath . "inc/handlers/share.php?post_id=$postId&unique_id=$uniqueId' method='POST' class='share-button' id='share$postId'>
														<input type='hidden' class='shareBody$postId' name='post_body' value='$shareValue'>
														<button type='submit' name='share_button' class='share$postId'>
															<div>
															<svg class='share-svg post-button share-button$uniqueId' xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink'  viewBox='0 0 20 20'>
																<path d='M19.7,8.2l-6.6-6C12.6,1.7,12,2.2,12,3v3C7.3,6,3.3,8.9,1.4,12.8c-0.7,1.3-1.1,2.7-1.4,4.1
																c-0.2,1,1.3,1.5,1.9,0.6C4.1,14,7.8,11.7,12,11.7V15c0,0.8,0.6,1.3,1.1,0.8l6.6-6C20.1,9.4,20.1,8.6,19.7,8.2z'/>
															</svg>
															<span class='post-options-nums' id='shareSpan$uniqueId'>$totalShares</span>
														</div>
														</button>
													</form>
												</div>
											</div>

											<div class='post-comments' id='toggle_comment$postId' style='display: none;'>
												<div class='post-comment-input'>
													<form class='comment-form comment-form$postId' action='" . $this->filePath . "inc/handlers/comments.php?post_id=$postId&unique_id=$uniqueId' method='POST'>
														<textarea class='autoExpand' rows='1' data-min-rows='1' id='post_comment_body$postId' name='post_body'  placeholder='Say something...''></textarea>
														<input type='submit' name='post_comment$postId' value='Send'>
													</form>
												</div>
												<div id='toggle_comments$postId'>
													$commentsBlock
												</div>
											</div>

										</div>

									</div>
								";

					?>

					<div>
					<script>
						$('.comment-form<?php echo $postId; ?>').submit(function(e) {

							var form = $('.comment-form<?php echo $postId; ?>');
							var formData = $(this).serialize();
							var postBody = $('#post_comment_body<?php echo $postId; ?>');
							var commentCountSpan =  $('#commentsSpan<?php echo $postId; ?>');
							if (!commentCount) {
								var commentCount = "<?php echo $commentCount; ?>";
							}
							var fullName = "<?php echo $userFullname; ?>";
							var username = "<?php echo $_SESSION['username']; ?>";
							var profilePic = "<?php echo $userProfilePic; ?>";
							var verifiedClass = "<?php echo $loggedInUserVerified; ?>";
							var verifiedPic = "<?php echo $loggedInUserVerifiedPic; ?>";
							var filePath = "<?php echo $this->filePath ?>";

							e.preventDefault();
							// Serialize the form data.

							// Submit the form using AJAX.
							$.ajax({
								type: 'POST',
								url: $(form).attr('action'),
								data: formData
							})
							.done(function(response) {
								if ($(postBody).val()) {
									$('#toggle_comment<?php echo $postId; ?>').append("<div class='full-comment' style='padding: 10px 0;'><a href='" + username + "'><img src='" + filePath + profilePic + "' class='comment-profile-pic'></a><div class='comment-section'><div><a href='" + username + "'><span>" + fullName + "</span><img class='" + verifiedClass + "' src='" + verifiedPic + "'></a><span class='post-username'> @" + username + "</span></div><span class='comment-time'>Just Now</span><div class='comment-body'>" + postBody.val() + "</div></div></div>");
									// Clear the form.
									$('#post_comment_body<?php echo $postId; ?>').val('');
									commentCount++;
									$(commentCountSpan).text(' ' + commentCount);
									$('#toggle_comment<?php echo $postId; ?>').css('display',' block');
								} else {
									alert("Uh oh! Comment field is empty!");
								}

							})
							.fail(function(data) {

							});

						});
						</script>

						<script>
						$('#likes<?php echo $postId; ?>').submit(function(e) {

							var form = $('#likes<?php echo $postId; ?>');
							var likesCountSpan =  $('#likesSpan<?php echo $postId; ?>');
							var totalLikes = $('#likesSpan<?php echo $postId; ?>').text();
							totalLikes = parseInt(totalLikes) || 0;
							var likesValue = $('.likesBody<?php echo $postId; ?>').val();

							e.preventDefault();
							// Submit the form using AJAX.
							$.ajax({
								type: 'POST',
								url: $(form).attr('action'),
								data: "likesValue=" + likesValue,
								success: function(data) {
									if (likesValue == 'Like') {
										totalLikes = totalLikes + 1;
										$('#like<?php echo $postId; ?>').css('fill', '#ff4c4c');
										$('.likesBody<?php echo $postId; ?>').removeClass('Like');
										$('.likesBody<?php echo $postId; ?>').addClass('Unlike');
										$('.likesBody<?php echo $postId; ?>').val('Unlike');
									} else if (likesValue == 'Unlike') {
										totalLikes = totalLikes - 1;
										$('#like<?php echo $postId; ?>').css('fill', '#fafafa');
										$('.likesBody<?php echo $postId; ?>').removeClass('Unlike');
										$('.likesBody<?php echo $postId; ?>').addClass('Like');
										$('.likesBody<?php echo $postId; ?>').val('Like');
									}
									$(likesCountSpan).text(' ' + totalLikes);
								}

							});

						});
						</script>

						<script>
						$('.share<?php echo $postId; ?>').click(function(e) {

							var form = $('#share<?php echo $postId; ?>');
							var shared_by = $('<?php echo $username; ?>');
							var shareValue = $('.shareBody<?php echo $postId; ?>').val();
							var shareCountSpan =  $('#shareSpan<?php echo $uniqueId; ?>');
							var totalShares = $('#shareSpan<?php echo $postId; ?>').text();
							totalShares = parseInt(totalShares) || 0;
							var shareMessage = "";
							if (shareValue == 'Share') {
								shareMessage = "You are about to share this post.";
							} else if (shareValue == 'Unshare') {
								shareMessage = "You are about to unshare this post.";
							}

							e.preventDefault();
							bootbox.confirm(shareMessage, function(result) {

								if (result) {
										// Submit the form using AJAX.
									$.ajax({
										type: 'POST',
										url: $(form).attr('action'),
										data: "shared_by=" + shared_by + "&shareValue=" + shareValue,

										success: function(data) {
											if (shareValue == 'Share') {
												totalShares = totalShares + 1;
												$('.share-button<?php echo $uniqueId; ?>').css('fill', '#59b063');
												$('.shareBody<?php echo $postId; ?>').val('Unshare');
											} else if (shareValue == 'Unshare') {
												// totalShares = totalShares - 1;
												// i need to figure out a good way to remove the shared post when another post with the same unique_id is clicked to uns
											}
											$(shareCountSpan).text(' ' + totalShares);
										}

									});
								}

							});

						});
						</script>

						<script>
						var $overlay = $("<div id=\"image-overlay\"></div>");
						var $image = $("<img>");
						var $caption = $("<p></p>");

						$overlay.append($image);
						$overlay.append($caption);
						$("body").append($overlay);

						// Capture the click event on a link to an image
						$(".posted-image<?php echo $postId; ?>").click(function(event) {

						  event.preventDefault();
						  var imageLocation = $(this).attr("href");
						  //  Update overlay with the image linked in the link
						  $image.attr("src", imageLocation);

						  //  Show the overlay
						  $overlay.fadeIn("fast");
							$overlay.css("display", "flex");

						  //  Get child's alt attribute and set caption
						  var captionText = $(this).children("img").attr("alt");
						  $caption.text(captionText);
						});

						// When overlay is clicked
						$overlay.click(function() {
						  // Hide overlay
						  $overlay.fadeOut();
						});
						</script>

						<script>
						$('#sharedbyuser<?php echo $postId; ?>').submit(function(e) {

							e.preventDefault();

						});
						</script>

						 <script>

							$(document).ready(function() {

								$('#post<?php echo $postId; ?>').on('click', function() {
									bootbox.confirm("Are you sure you want to delete this post? ", function(result) {
										$.post("inc/handlers/delete-post.php?post_id=<?php echo $postId; ?>", {result: result});

										if (result) {
											$('#full-post<?php echo $postId; ?>').slideUp("slow");
										}
									});
								});

							});

						</script>

						<script>
						function edit<?php echo $postId; ?>() {

							var postId = "<?php echo $postId; ?>";
							var postBody = "<?php echo $editPostBody; ?>";
							var postImage = "<?php echo $imagePath; ?>";

							$('#postEdit').val("<?php echo $postId; ?>");
							$('.site-post textarea').val(postBody);
							$('.image-upload').hide();
							$('.modal-footer').css("justify-content", "flex-end");

							if (postImage !== "<?php echo $this->filePath; ?>" && postImage !== "") {

								$('#file-img').css("display", "flex");
								$('#file-img-close').hide();
								$('#file-img img').attr('src', postImage);

							}

							$('#youtube-video-edit').append("<?php echo $youtubeVideo; ?>");
							$('#submit_post').text("Edit");

						}
						</script>

						<script>
							function toggle<?php echo $postId; ?>() {
								var target = $(event.target);
								if (!target.is('a')) {

									if (!target.is('a')) {

										$('#toggle_comment<?php echo $postId; ?>').slideToggle("fast");

									}
								}
							}
						</script>

						<script>
						$('#dropdown-post<?php echo $postId; ?>').click(function() {
							var username = $('#post-username<?php echo $postId; ?>').text();
							$('#post_form textarea').text(username + ' ');
						});
						</script>
					</div>

				<?php

				}

			} //end while loop

			if ($count > $limit) {
				$postBlock[] = "<input type='hidden' class='next-page' value='" . ($page + 1) . "'>
						<input type='hidden' class='no-more-posts' value='false'>
						<div class='loading'>
							<h5>Show More Posts</h5>
						</div>";
			?>
			<script>
			$(document).ready(function() {
				$('.loading').on('click', function () {
					var page = $('.posts-area').find('.next-page').val();
					var noMorePosts = $('.posts-area').find('.no-more-posts').val();
					var ajaxReq = $.ajax ({
						url: "/inc/ajax/ajax-load-posts.php",
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
				});
			});
			</script>
			<?php
			} else {
				$postBlock[] = "<input type='hidden' class='no-more-posts' value='true'><p class='no-more-posts'>Back to Top</p>";
			?>

			<script>
			$(document).ready(function() {
				$(".no-more-posts").click(function() {
				  	$("html, body").animate({ scrollTop: 0 }, 400);
	  				return false;
				});
			});
			</script>

			<?php
			}

		}

		foreach ($postBlock as $block) {
			echo $block;
		}
	}

	public function loadProfilePosts($data, $limit, $images, $deleted) {

		$page = $data['page'];
		$profileUser = $data['profileUsername'];
		$loggedInUser = $this->userObj->getUsername();
		$userFullname = $this->userObj->getFirstAndLastName();
		$userProfilePic = $this->userObj->getProfilePic();

		// check to see if account is loggedinuser is following else check if profileuser is private

		if ($page == 1) {
			$start = 0;
		} else {
			$start = ($page - 1) * $limit;
		}

		$postBlock = []; //arrays to return

		if ($images === 'posted-images') {

			$getPosts = $this->db->query("
				SELECT * FROM posts WHERE deleted = 'no' AND ((added_by = '$profileUser' && shared_by = 'none') || shared_by = '$profileUser') AND (image != '' OR youtube != '') ORDER BY id DESC
			");

		} else if ($deleted === 'deleted-posts') {

			$getPosts = $this->db->query("
				SELECT * FROM posts WHERE deleted = 'yes' AND ((added_by = '$profileUser' && shared_by = 'none') || shared_by = '$profileUser') ORDER BY id DESC
			");

		} else {

			$getPosts = $this->db->query("
				SELECT * FROM posts WHERE deleted = 'no' AND ((added_by = '$profileUser' && shared_by = 'none') || shared_by = '$profileUser') ORDER BY id DESC
			");

		}


		// check if loggedInUser is verified
		$verifiedQuery = $this->db->query("
			SELECT verified FROM users WHERE username = '$profileUser'
		");
		$verifiedRow = $verifiedQuery->fetch_assoc();
		if ($verifiedRow['verified'] == 'yes') {
			$loggedInUserVerifiedPic = $this->filePath . "img/icons/verified.svg";
			$loggedInUserVerified = 'verified';
		} else {
			$loggedInUserVerifiedPic = "";
			$loggedInUserVerified = "";
		}

		$loggedInUserObj = new User($this->db, $loggedInUser);

		if ($getPosts->num_rows > 0 && ($loggedInUserObj->isFollowing($profileUser) || $loggedInUserObj->checkIfPrivate($profileUser) != 'yes')) {

			$numIterations =  0; //number of results checked (not necessarily posted)
			$count = 1;

				while ($postRow = $getPosts->fetch_assoc()) {

				//prepare userTo string so it can be included even if not posted to user
				if ($postRow['user_to'] == 'none') {
					$userTo = "";
				} else {
					$userToObj = new User($this->db, $postRow['user_to']);
					$userToName = $userToObj->getFirstAndLastName();
					$userTo = $postRow['user_to'];
					$userTo = "<a class='user-to' href='" . $userTo . "'>@" . $userTo . "</a>";
				}

				// user info
				$addedBy = $postRow['added_by'];
				$dateTime = $postRow['date_added'];
				$imagePath = $this->filePath . $postRow['image'];
				if ($imagePath === $this->filePath) $imagePath = "";
				$youtubeVideo = $postRow['youtube'];
				$postId = $postRow['id'];
				$uniqueId = $postRow['post_unique_id'];
				$postAdded = $postRow['date_added'];
				$sharedBy = $postRow['shared_by'];
				$body = $postRow['body'];

				$editedPost = $postRow['edited'];

				if ($editedPost == 'yes') {
					$editedPostClass = 'edited-post';
					$editedText = "| Edited";
				} else {
					$editedPostClass = '';
					$editedText = "";
				}

				$editBodyArray = [];
				$editBodyArray[] = preg_split("/<div/", $body);
				$editBody = $editBodyArray[0];
				$newEditBody = [];
				foreach ($editBody as $edit) {
					if (strpos($edit, "youtube")) $edit = "";

					$newEditBody[] = $edit;
				}

				$editPostBody = implode(" ", $newEditBody);
				$editPostBody = preg_replace('/\s+/', ' ', $editPostBody); //deletes all spaces

				//format body text
				$body = "<p >" . preg_replace('#(\\\r|\\\r\\\n|\\\n)#', '</p><p>', $body) . "</p>";
				$body = stripcslashes($body);
				//Convert hashtags to <a> links
				$body = preg_replace("/#([A-Za-z0-9\/\.]*)/", "<a class=\"post-hashtag\" href='" . $this->filePath . "i/trending/hashtag/$1'>#$1</a>", $body);
				//Convert @ tags to <a> links
				$body = preg_replace("/@([A-Za-z0-9\/\.]*)/", "<a class=\"post-hashtag\" href='" . $this->filePath . "$1'>@$1</a>", $body);
				//posting links
				if (strpos($body, "youtube.com") == false) {
					// The Regular Expression filter
					$reg_exUrl = "/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/";
					// Check if there is a url in the body
					if (preg_match($reg_exUrl, $body, $url)) {
							$url[0] = strip_tags($url[0]);
					       // make the urls hyper links
						   $out = strlen($url[0]) > 20 ? substr($url[0],0,30)."..." : $url[0];
					       $body = preg_replace($reg_exUrl, "<a class='post-hashtag' href='$url[0]' target='_blank'>$out</a> ", $body);
					} else {
					       // if no urls in the body just return the body
					       $body = $body;
					}
				}

				//check if user who posted, has their account closed
				$addedByObj = new User($this->db, $addedBy);
				if ($addedByObj->isClosed()) {
					continue;
				}

					if ($numIterations++ < $start) {
						continue;
					}

					// once 10 posts have been loaded, break
					if ($count > $limit) {
						break;
					} else {
						$count++;
					}

					if ($loggedInUser == $addedBy) {
						$otherUserMenuOptions = "";
						$loggedInUserMenuOptions = "<li style='cursor: pointer;'><a data-toggle='modal' data-target='#post_form' onClick='javascript:edit$postId()'>Edit Post</a></li>

																				<li style='cursor: pointer;'><a id='post$postId'>Delete Post</a></li>";

					} else {
						$otherUserMenuOptions = "<li data-toggle='modal' data-target='#post_form' 			id='dropdown-post$postId'><a>Say Something to @$addedBy</a></li>";


						$loggedInUserMenuOptions = "";
					}

					//get post user info

					$userDetails = $this->db->query ("
						SELECT first_name, last_name, profile_pic, verified, username, private_account FROM users WHERE username = '$addedBy'
					");

					$userRow = $userDetails->fetch_assoc();
					$firstName = $userRow['first_name'];
					$lastName = $userRow['last_name'];
					$profilePic = $userRow['profile_pic'];
					$username = $userRow['username'];

					if ($userRow['private_account'] == 'yes' || $userRow['username'] == $loggedInUser) {
						$privateAccount = "private-account";
					} else {
						$privateAccount = "";
					}

					//check to see if user is verified
					if ($userRow['verified'] == 'yes') {
						$postVerifiedCheck = "<img class='verified' src='" . $this->filePath . "img/icons/verified.svg' title='Verified user'>";
					} else {
						$postVerifiedCheck = "";
					}

					//======================================================================
					//
					//	 check to see if post was shared
					//
					//======================================================================

						$totalShares = "";

						$getUserShared = $this->db->query("
							SELECT * FROM posts WHERE post_unique_id =  '$uniqueId' AND shared_by = '$loggedInUser'
						");
						$userShares = $getUserShared->num_rows;

						$shareMenuOption = "";
						if ($userShares > 0) {
							$sharedIconClass = 'user-shared';
							$shareValue = "Unshare";
							if ($loggedInUser != $addedBy) {
								$shareMenuOption = "<li class='share-menu-option share$postId'><a>Unshare this post</a></li>";
							}
						} else {
							$sharedIconClass= '';
							$shareValue = "Share";
							if ($loggedInUser != $addedBy) {
								$shareMenuOption = "<li class='share-menu-option share$postId'><a>Share this post</a></li>";
							}
						}

						$getSharedPost = $this->db->query("
							SELECT * FROM posts WHERE post_unique_id = '$uniqueId' AND shared_by = 'none'
						");
						$sharedPost = $getSharedPost->fetch_assoc();

						$getSharedUserName = $this->db->query("
							SELECT first_name, last_name FROM users WHERE username = '$sharedBy'
						");
						$sharedUser = $getSharedUserName->fetch_assoc();

						if (!empty($sharedBy) && $sharedBy != 'none') {


							$sharedDiv = "<div class='shared-div'>
										<a href='$sharedBy' class='post-name'>Shared by&nbsp;<span>" . $sharedUser['first_name'] . " " . $sharedUser['last_name'] . "</span></a>
									 </div>";

							$sharedClass = "shared";

						} else {

							$sharedDiv = "";
							$sharedClass = "";

						}

						$addedBy = $sharedPost['added_by'];
						$dateTime = $sharedPost['date_added'];
						$uniqueId = $sharedPost['post_unique_id'];
						$sharedUserDetails = $this->db->query ("
							SELECT first_name, last_name, profile_pic, verified, username FROM users WHERE username = '$addedBy'
						");

						$sharedRow = $sharedUserDetails->fetch_assoc();
						$firstName = $sharedRow['first_name'];
						$lastName = $sharedRow['last_name'];
						$profilePic = $sharedRow['profile_pic'];
						$username = $sharedRow['username'];

						$checkShares = $this->db->query("
							SELECT * FROM posts WHERE post_unique_id = '$uniqueId' AND shared_by <> 'none'
						");
						$totalShares = $checkShares->num_rows;

						if ($totalShares > 0) {
							$totalShares = $totalShares;
						} else if ($totalShares == 0) {
							$totalShares = "";
						}



						//timezone
						//set times for posts
						$dateTimeNow = date("Y-m-d H:i:s");
						$postStartDate = new DateTime($dateTime); //time of post
						$endDate = new DateTime($dateTimeNow); //current time
						$postInterval = $postStartDate->diff($endDate); //difference between dates
						$hourDiff = $postInterval->h + ($postInterval->d*24);
						$newtime = strtotime($dateTime);
						if ($hourDiff > 24) {
							$postTimeMessage = date("F j, Y", $newtime);
						} else if ($postInterval->h >=1) {
							if ($postInterval->h == 1) {
								$postTimeMessage = $postInterval->h . " hr";
							} else {
								$postTimeMessage = $postInterval->h . " hr";
							}
						} else if ($postInterval->i >=1) {
							if ($postInterval->i == 1) {
								$postTimeMessage = $postInterval->i . " min";
							} else {
								$postTimeMessage = $postInterval->i . " min";
							}
						} else {
							if ($postInterval->s < 30) {
								$postTimeMessage = "Just now";
							} else {
								$postTimeMessage = $postInterval->s . " sec";
							}
						}

					//======================================================================
					//
					//	check for comments
					//
					//======================================================================

						$commentsCheck = $this->db->query("
							SELECT * FROM comments WHERE post_unique_id = '$uniqueId'
						");
						$commentsCheckNum = $commentsCheck->num_rows;

						$commentsBlock = "";
						$getComments = $this->db->query("
							SELECT * FROM comments WHERE post_unique_id = '$uniqueId' ORDER BY id ASC
						");
						$commentCount = $getComments->num_rows;

						if ($commentCount > 0) {
							$commentCount = $commentCount;
						} else {
							$commentCount = "";
						}

						if ($commentCount != 0) {

							while ($comment = $getComments->fetch_assoc()) {
								$commentBody = "<p>" . preg_replace('#(\\\r|\\\r\\\n|\\\n)#', '</p><p>', $comment['post_body']) . "</p>";
								$commentBody = stripcslashes($commentBody);
								$postedTo = $comment['posted_to'];
								$postedBy = $comment['posted_by'];
								$dateAdded = $comment['date_added'];
								$removed = $comment['removed'];

								$getCommentorInfo = $this->db->query("
									SELECT first_name, last_name, username, profile_pic, verified FROM users WHERE username = '$postedBy'
								");
								$commentorInfo = $getCommentorInfo->fetch_assoc();
								$commentUser = $commentorInfo['first_name'] . " " . $commentorInfo['last_name'];
								$commentUsername = $commentorInfo['username'];

								if ($commentorInfo['verified'] == 'yes') {
									$commentVerifiedPic = $this->filePath . "img/icons/verified.svg";
									$commmentUserVerified = "verified";
								} else {
									$commentVerifiedPic = "";
									$commmentUserVerified = "";
								}

								//set times for comments
								//timezone
								$commentStartDate = new DateTime($dateAdded); //time of comment
								$commentInterval = $commentStartDate->diff($endDate); //difference between dates
								$commentHourDiff = $commentInterval->h + ($commentInterval->d*24);
								$newtime = strtotime($dateAdded);

								if ($commentHourDiff >= 24) {
									$commentTimeMessage = date("F j, Y", $newtime);
								} else if ($commentInterval->h >=1) {
									if ($commentInterval->h == 1) {
										$commentTimeMessage = $commentInterval->h . " hr";
									} else {
										$commentTimeMessage = $commentInterval->h . " hr";
									}
								} else if ($commentInterval->i >=1) {
									if ($commentInterval->i == 1) {
										$commentTimeMessage = $commentInterval->i . " min";
									} else {
										$commentTimeMessage = $commentInterval->i . " min";
									}
								} else {
									if ($commentInterval->s < 30) {
										$commentTimeMessage = "Just now";
									} else {
										$commentTimeMessage = $commentInterval->s . " sec";
									}
								}

								$commentsBlock .= "
													<div class='full-comment'>

														<div>

															<a href='" . $this->filePath . "$commentUsername'>
																<img src='" . $this->filePath . $commentorInfo['profile_pic'] . "' class='comment-profile-pic' title='$commentUser'>
															</a>


														</div>

														<div class='comment-section'>
															<div class='comment-user-name'>
																<a href='" . $this->filePath . "$commentUsername' class='post-name'><span>$commentUser</span><img class='$commmentUserVerified' src='" . "$commentVerifiedPic'></a><span class='post-username'>@$commentUsername</span>
															</div
															<span class='comment-time'>$commentTimeMessage</span>
															<div class='comment-body'>$commentBody</div>
														</div>
													</div>
												";

							}

						}//end of comments check

					//======================================================================
					//
					//	check for previous likes
					//
					//======================================================================


						$checkLikes = $this->db->query("
							SELECT * FROM likes WHERE username = '$loggedInUser' AND post_unique_id = '$uniqueId'
						");
						$userLikes = $checkLikes->num_rows;

						if ($userLikes > 0) {
							$likesName = "unlike_button";
							$likesValue = "Unlike";
							$userLikes = $userLikes;
							$likeFill = 'active';
						} else {
							$likesName = "like_button";
							$likesValue = "Like";
							$userLikes = "";
							$likeFill = 'inactive';
						}

						$checkLikes2 = $this->db->query("
							SELECT * FROM likes WHERE post_unique_id = '$uniqueId'
						");
						$totalLikes = $checkLikes2->num_rows;

						if ($totalLikes > 0) {
							$totalLikes = $totalLikes;
						} else {
							$totalLikes = "";
						} // end of likes


						if ($imagePath != "") {
							$imageDiv = "<div class='postedImage'>
														<a class='posted-image$postId' href='$imagePath'><img src='$imagePath'></a>
													</div>";
						} else {
							$imageDiv = "";
						}

						//get delete button and dropdown menu

						if ($deleted !== 'deleted-posts') {

							$dropdownMenu = "
							<div class='delete-button btn-group'>
								<button type='button' data-toggle='dropdown' aria-haspopup='true' aria-expanded='false'>
									<img class='delete_button' src='" . $this->filePath . "img/icons/dot-menu.svg' alt='Post Menu'>
								</button>
								<ul class='dropdown-menu'>
									$otherUserMenuOptions
									$shareMenuOption
									$loggedInUserMenuOptions
								</ul>
							</div>";

						} else {
							$dropdownMenu = "";
						}

					$postBlock[] = "
									<div class='full-post-outer $sharedClass' id='full-post-outer$postId'>
										$sharedDiv

										<div class='full-post' id='full-post$postId'>

											<div class='status-post'>

												<div class='body-content'>

													<div class='posted-by' style='color: #acacac;'>

														<div class='post-profile-pic'>
															<a href='" . $this->filePath . "$addedBy'><img src='" . $this->filePath . "$profilePic' alt='Profile Picture for $firstName $lastName'></a>
														</div>

														<div class='posted-by-info'>
															<div class='person-details'>
																<a href='" . $this->filePath . "$addedBy' class='post-name'><span>$firstName $lastName</span>$postVerifiedCheck</a><span class='post-username'id='post-username$postId'>@$username</span>
															</div>
															<span>$postTimeMessage</span>
														</div>

															$dropdownMenu

													</div>

													<div id='post_body'>
													$body

													$youtubeVideo
													$imageDiv
													</div>

												</div>

											</div>

											<div class='newsfeed-post-options'>
												<div class='comment-button newsfeed-buttons' title='Comment on this post.' onClick='javascript:toggle$postId()'>
													<div>
														<svg id='comments$postId' class='comment-svg post-button' mlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink' viewBox='0 0 505.7 512'>
															<path d='M373.1,511c-12.6,0-25.3-2.3-37.8-6.8c-25.4-9.2-45.7-23.4-60.1-42.3l-0.3-0.4h-0.6
																c-7.8,0.6-14.8,0.9-21.5,0.9c-67.3,0-130.5-24-178.1-67.6C27.2,351.2,1,293.3,1,231.7S27.2,112.2,74.7,68.6
																C122.3,25,185.6,1,252.8,1c67.3,0,130.5,24,178.1,67.6c47.5,43.6,73.7,101.5,73.7,163.1c0,85.4-51.2,163.4-133.6,203.6l-0.9,0.4
																l0.3,0.9c3.4,9.2,7.5,18.5,12.3,27.6c10.5,20.2,33.2,30.8,40,33.6C417.3,501.2,399,511,373.1,511L373.1,511z'/>
															<path d='M252.8,2.1c33.9,0,66.7,6.1,97.7,18.1c29.9,11.6,56.7,28.1,79.7,49.2s41.1,45.6,53.7,73
																c13.1,28.3,19.7,58.3,19.7,89.3c0,42.4-12.7,83.8-36.8,119.8c-23.4,35-56.7,63.7-96.2,83l-1.7,0.8l0.7,1.8
																c3.4,9.2,7.6,18.5,12.3,27.7c9.9,19.1,30,29.6,38.6,33.3c-7,3.9-24.1,11.9-47.3,11.9c-12.5,0-25-2.3-37.4-6.8
																c-25.3-9.2-45.3-23.3-59.7-41.9l-0.7-0.9l-1.1,0.1c-7.8,0.6-14.8,0.9-21.4,0.9c-33.9,0-66.7-6.1-97.7-18.1
																c-29.9-11.6-56.7-28.1-79.7-49.3c-23-21.1-41.1-45.6-53.7-73C8.7,292.7,2.1,262.7,2.1,231.7s6.6-61,19.7-89.3
																c12.6-27.3,30.7-51.9,53.7-73s49.8-37.7,79.7-49.2C186.1,8.2,219,2.1,252.8,2.1 M252.8,0C113.2,0,0,103.7,0,231.7
																c0,127.9,113.2,231.7,252.8,231.7c7.3,0,14.4-0.3,21.5-0.9c12.4,16.1,31.3,32,60.6,42.6c13.9,5,26.8,6.9,38.2,6.9
																c31.7,0,52-14.3,52-14.3s-29-10.3-41.3-33.9c-5.1-9.9-9.1-19.1-12.2-27.5c79.8-39,134.1-116,134.1-204.6
																C505.7,103.7,392.5,0,252.8,0L252.8,0z'/>
														</svg>
														<span class='post-options-nums' id='commentsSpan$postId'>$commentCount</span>
													</div>

												</div>
												<div class='newsfeed-buttons' title='Like this post.'>
												<form action='" . $this->filePath . "inc/handlers/likes.php?post_id=$postId&unique_id=$uniqueId' method='POST' class='like-button' id='likes$postId'>
													<input type='hidden' class='likesBody$postId $likesValue' name='post_body' value='$likesValue'>
													<button type='submit' name='like_button'>
														<div class='like-div'>

															<svg id='like$postId' class='like-svg post-button $likeFill' version='1.1' xmlns='http://www.w3.org/2000/svg'viewBox='0 0 1024 1024'>
															<path d='M513.4,141c0,0,277.1-171.5,441.1,60.5c0,0,113.2,181-19.3,387.3c0,0-104.6,197.2-421.7,363.5
																c0,0-287.5-143.6-411.2-347.4c0,0-140.3-188.7-36.2-392.1C66.1,212.8,187-34.5,513.4,141z'/>
															</svg>

															<span class='post-options-nums' id='likesSpan$postId'>$totalLikes</span>
														</div>
													</button>
												</form>
												</div>

												<div class='share-button newsfeed-buttons $privateAccount $sharedIconClass' title='Share this post with your friends.'>
													<form action='" . $this->filePath . "inc/handlers/share.php?post_id=$postId&unique_id=$uniqueId' method='POST' class='share-button' id='share$postId'>
														<input type='hidden' class='shareBody$postId' name='post_body' value='$shareValue'>
														<button type='submit' name='share_button' class='share$postId'>
															<div>
															<svg class='share-svg post-button share-button$uniqueId' xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink'  viewBox='0 0 20 20'>
																<path d='M19.7,8.2l-6.6-6C12.6,1.7,12,2.2,12,3v3C7.3,6,3.3,8.9,1.4,12.8c-0.7,1.3-1.1,2.7-1.4,4.1
																c-0.2,1,1.3,1.5,1.9,0.6C4.1,14,7.8,11.7,12,11.7V15c0,0.8,0.6,1.3,1.1,0.8l6.6-6C20.1,9.4,20.1,8.6,19.7,8.2z'/>
															</svg>
															<span class='post-options-nums' id='shareSpan$uniqueId'>$totalShares</span>
														</div>
														</button>
													</form>
												</div>
											</div>

											<div class='post-comments' id='toggle_comment$postId' style='display: none;'>
												<div class='post-comment-input'>
													<form class='comment-form comment-form$postId' action='" . $this->filePath . "inc/handlers/comments.php?post_id=$postId&unique_id=$uniqueId' method='POST'>
														<textarea class='autoExpand' rows='1' data-min-rows='1' id='post_comment_body$postId' name='post_body'  placeholder='Say something...''></textarea>
														<input type='submit' name='post_comment$postId' value='Send'>
													</form>
												</div>
												<div id='toggle_comments$postId'>
													$commentsBlock
												</div>
											</div>

										</div>

									</div>
								";

					?>

					<div>
					<script>
						$('.comment-form<?php echo $postId; ?>').submit(function(e) {

							var form = $('.comment-form<?php echo $postId; ?>');
							var formData = $(this).serialize();
							var postBody = $('#post_comment_body<?php echo $postId; ?>');
							var commentCountSpan =  $('#commentsSpan<?php echo $postId; ?>');
							if (!commentCount) {
								var commentCount = "<?php echo $commentCount; ?>";
							}
							var fullName = "<?php echo $userFullname; ?>";
							var username = "<?php echo $_SESSION['username']; ?>";
							var profilePic = "<?php echo $userProfilePic; ?>";
							var verifiedClass = "<?php echo $loggedInUserVerified; ?>";
							var verifiedPic = "<?php echo $loggedInUserVerifiedPic; ?>";
							var filePath = "<?php echo $this->filePath ?>";

							e.preventDefault();
							// Serialize the form data.

							// Submit the form using AJAX.
							$.ajax({
								type: 'POST',
								url: $(form).attr('action'),
								data: formData
							})
							.done(function(response) {
								if ($(postBody).val()) {
									$('#toggle_comment<?php echo $postId; ?>').append("<div class='full-comment' style='padding: 10px 0;'><a href='" + username + "'><img src='" + filePath + profilePic + "' class='comment-profile-pic'></a><div class='comment-section'><div><a href='" + username + "'><span>" + fullName + "</span><img class='" + verifiedClass + "' src='" + verifiedPic + "'></a><span class='post-username'> @" + username + "</span></div><span class='comment-time'>Just Now</span><div class='comment-body'>" + postBody.val() + "</div></div></div>");
									// Clear the form.
									$('#post_comment_body<?php echo $postId; ?>').val('');
									commentCount++;
									$(commentCountSpan).text(' ' + commentCount);
									$('#toggle_comment<?php echo $postId; ?>').css('display',' block');
								} else {
									alert("Uh oh! Comment field is empty!");
								}

							})
							.fail(function(data) {

							});

						});
						</script>

						<script>
						$('#likes<?php echo $postId; ?>').submit(function(e) {

							var form = $('#likes<?php echo $postId; ?>');
							var likesCountSpan =  $('#likesSpan<?php echo $postId; ?>');
							var totalLikes = $('#likesSpan<?php echo $postId; ?>').text();
							totalLikes = parseInt(totalLikes) || 0;
							var likesValue = $('.likesBody<?php echo $postId; ?>').val();

							e.preventDefault();
							// Submit the form using AJAX.
							$.ajax({
								type: 'POST',
								url: $(form).attr('action'),
								data: "likesValue=" + likesValue,
								success: function(data) {
									if (likesValue == 'Like') {
										totalLikes = totalLikes + 1;
										$('#like<?php echo $postId; ?>').css('fill', '#ff4c4c');
										$('.likesBody<?php echo $postId; ?>').removeClass('Like');
										$('.likesBody<?php echo $postId; ?>').addClass('Unlike');
										$('.likesBody<?php echo $postId; ?>').val('Unlike');
									} else if (likesValue == 'Unlike') {
										totalLikes = totalLikes - 1;
										$('#like<?php echo $postId; ?>').css('fill', '#fafafa');
										$('.likesBody<?php echo $postId; ?>').removeClass('Unlike');
										$('.likesBody<?php echo $postId; ?>').addClass('Like');
										$('.likesBody<?php echo $postId; ?>').val('Like');
									}
									$(likesCountSpan).text(' ' + totalLikes);
								}

							});

						});
						</script>

						<script>
						$('.share<?php echo $postId; ?>').click(function(e) {

							var form = $('#share<?php echo $postId; ?>');
							var shared_by = $('<?php echo $username; ?>');
							var shareValue = $('.shareBody<?php echo $postId; ?>').val();
							var shareCountSpan =  $('#shareSpan<?php echo $uniqueId; ?>');
							var totalShares = $('#shareSpan<?php echo $postId; ?>').text();
							totalShares = parseInt(totalShares) || 0;
							var shareMessage = "";
							if (shareValue == 'Share') {
								shareMessage = "You are about to share this post.";
							} else if (shareValue == 'Unshare') {
								shareMessage = "You are about to unshare this post.";
							}

							e.preventDefault();
							bootbox.confirm(shareMessage, function(result) {

								if (result) {
										// Submit the form using AJAX.
									$.ajax({
										type: 'POST',
										url: $(form).attr('action'),
										data: "shared_by=" + shared_by + "&shareValue=" + shareValue,

										success: function(data) {
											if (shareValue == 'Share') {
												totalShares = totalShares + 1;
												$('.share-button<?php echo $uniqueId; ?>').css('fill', '#59b063');
												$('.shareBody<?php echo $postId; ?>').val('Unshare');
											} else if (shareValue == 'Unshare') {
												// totalShares = totalShares - 1;
												// i need to figure out a good way to remove the shared post when another post with the same unique_id is clicked to uns
											}
											$(shareCountSpan).text(' ' + totalShares);
										}

									});
								}

							});

						});
						</script>

						<script>
						var $overlay = $("<div id=\"image-overlay\"></div>");
						var $image = $("<img>");
						var $caption = $("<p></p>");

						$overlay.append($image);
						$overlay.append($caption);
						$("body").append($overlay);

						// Capture the click event on a link to an image
						$(".posted-image<?php echo $postId; ?>").click(function(event) {

						  event.preventDefault();
						  var imageLocation = $(this).attr("href");
						  //  Update overlay with the image linked in the link
						  $image.attr("src", imageLocation);

						  //  Show the overlay
						  $overlay.fadeIn("fast");
							$overlay.css("display", "flex");

						  //  Get child's alt attribute and set caption
						  var captionText = $(this).children("img").attr("alt");
						  $caption.text(captionText);
						});

						// When overlay is clicked
						$overlay.click(function() {
						  // Hide overlay
						  $overlay.fadeOut();
						});
						</script>

						<script>
						$('#sharedbyuser<?php echo $postId; ?>').submit(function(e) {

							e.preventDefault();

						});
						</script>

						 <script>

							$(document).ready(function() {

								$('#post<?php echo $postId; ?>').on('click', function() {
									bootbox.confirm("Are you sure you want to delete this post? ", function(result) {
										$.post("inc/handlers/delete-post.php?post_id=<?php echo $postId; ?>", {result: result});

										if (result) {
											$('#full-post<?php echo $postId; ?>').slideUp("slow");
										}
									});
								});

							});

						</script>

						<script>
						function edit<?php echo $postId; ?>() {

							var postId = "<?php echo $postId; ?>";
							var postBody = "<?php echo $editPostBody; ?>";
							var postImage = "<?php echo $imagePath; ?>";

							$('#postEdit').val("<?php echo $postId; ?>");
							$('.site-post textarea').val(postBody);
							$('.image-upload').hide();
							$('.modal-footer').css("justify-content", "flex-end");

							if (postImage !== "<?php echo $this->filePath; ?>" && postImage !== "") {

								$('#file-img').css("display", "flex");
								$('#file-img-close').hide();
								$('#file-img img').attr('src', postImage);

							}

							$('#youtube-video-edit').append("<?php echo $youtubeVideo; ?>");
							$('#submit_post').text("Edit");

						}
						</script>

						<script>
							function toggle<?php echo $postId; ?>() {
								var target = $(event.target);
								if (!target.is('a')) {

									if (!target.is('a')) {

										$('#toggle_comment<?php echo $postId; ?>').slideToggle("fast");

									}
								}
							}
						</script>

						<script>
						$('#dropdown-post<?php echo $postId; ?>').click(function() {
							var username = $('#post-username<?php echo $postId; ?>').text();
							$('#post_form textarea').text(username + ' ');
						});
						</script>
					</div>

				<?php

			} //end while loop

			if ($count > $limit) {
				$postBlock[] = "<input type='hidden' class='next-page' value='" . ($page + 1) . "'>
						<input type='hidden' class='no-more-posts' value='false'>
						<div class='loading'>
							<h5>Show More Posts</h5>
						</div>";
			?>
			<script>
			$(document).ready(function() {
				$('.loading').on('click', function () {
					var page = $('.posts-area').find('.next-page').val();
					var noMorePosts = $('.posts-area').find('.no-more-posts').val();
					var ajaxReq = $.ajax ({
						url: "inc/ajax/ajax-load-posts.php",
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
				});
			});
			</script>
			<?php
			} else {
				$postBlock[] = "<input type='hidden' class='no-more-posts' value='true'><p class='no-more-posts'>Back to Top</p>";
			?>

			<script>
			$(document).ready(function() {
				$(".no-more-posts").click(function() {
				  	$("html, body").animate({ scrollTop: 0 }, 400);
	  				return false;
				});
			});
			</script>

			<?php
			}

		} else { // main if statement

			$postBlock[] = "<div class='profile-no-post'><h1>This person hasn't said anything yet!</h1><div>";

		}

		foreach ($postBlock as $block) {
			echo $block;
		}
	}

	public function getSinglePost($postId) {

		$loggedInUser = $this->userObj->getUsername();
		$userFullname = $this->userObj->getFirstAndLastName();
		$userProfilePic = $this->userObj->getProfilePic();

		$openedNotification = $this->db->query("
			UPDATE notifications SET opened = 'yes' WHERE user_to = '$loggedInUser' AND link LIKE '%/$postId'
		");

		$getPosts = $this->db->query("
			SELECT * FROM posts WHERE deleted = 'no' AND id = '$postId' ORDER BY id DESC
		");

		$verifiedQuery = $this->db->query("
			SELECT verified FROM users WHERE username = '$loggedInUser'
		");

		$verifiedRow = $verifiedQuery->fetch_assoc();

		if ($verifiedRow['verified'] == 'yes') {
			$loggedInUserVerifiedPic = $this->filePath . "img/icons/verified.svg";
			$loggedInUserVerified = 'verified';
		} else {
			$loggedInUserVerifiedPic = "";
			$loggedInUserVerified = "";
		}

		if ($getPosts->num_rows > 0) {

				$postRow = $getPosts->fetch_assoc();
				//prepare userTo string so it can be included even if not posted to user
				if ($postRow['user_to'] == 'none') {
					$userTo = "";
				} else {
					$userToObj = new User($this->db, $postRow['user_to']);
					$userToName = $userToObj->getFirstAndLastName();
					$userTo = $postRow['user_to'];
					$userTo = "<a class='user-to' href='" . $userTo . "'>@" . $userTo . "</a>";
				}

				$imagePath = $this->filePath . $postRow['image'];
				if ($imagePath === $this->filePath) $imagePath = "";
				$youtubeVideo = $postRow['youtube'];
				$postId = $postRow['id'];
				$body = $postRow['body'];

				$editedPost = $postRow['edited'];

				if ($editedPost == 'yes') {
					$editedPostClass = 'edited-post';
					$editedText = "| Edited";
				} else {
					$editedPostClass = '';
					$editedText = "";
				}

				$editBodyArray = [];
				$editBodyArray[] = preg_split("/<div/", $body);
				$editBody = $editBodyArray[0];
				$newEditBody = [];
				foreach ($editBody as $edit) {
					if (strpos($edit, "youtube")) $edit = "";

					$newEditBody[] = $edit;
				}

				$editPostBody = implode(" ", $newEditBody);
				$editPostBody = preg_replace('/\s+/', ' ', $editPostBody); //deletes all spaces

				$body = "<p >" . preg_replace('#(\\\r|\\\r\\\n|\\\n)#', '</p><p>', $body) . "</p>";
				$body = stripcslashes($body);
				//Convert hashtags to <a> links
				$body = preg_replace("/#([A-Za-z0-9\/\.]*)/", "<a class=\"post-hashtag\" href='" . $this->filePath . "i/trending/hashtag/$1'>#$1</a>", $body);
				//Convert @ tags to <a> links
				$body = preg_replace("/@([A-Za-z0-9\/\.]*)/", "<a class=\"post-hashtag\" href='" . $this->filePath . "$1'>@$1</a>", $body);

				//posting links
				if (strpos($body, "youtube.com") == false) {
					// The Regular Expression filter
					$reg_exUrl = "/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/";
					// Check if there is a url in the body
					if (preg_match($reg_exUrl, $body, $url)) {
							$url[0] = strip_tags($url[0]);
					       // make the urls hyper links
						   $out = strlen($url[0]) > 20 ? substr($url[0],0,30)."..." : $url[0];
					       $body = preg_replace($reg_exUrl, "<a class='post-hashtag' href='$url[0]' target='_blank'>$out</a> ", $body);
					} else {
					       // if no urls in the body just return the body
					       $body = $body;
					}
				}

				$addedBy = $postRow['added_by'];
				$sharedBy = $postRow['shared_by'];
				$dateTime = $postRow['date_added'];
				$uniqueId = $postRow['post_unique_id'];

				//check if user who posted, has their account closed
				$addedByObj = new User($this->db, $addedBy);
				if ($addedByObj->isClosed()) {
					return;
				}

				$loggedInUserObj = new User($this->db, $loggedInUser);

				if ($loggedInUser == $addedBy) {
						$otherUserMenuOptions = "";
						$loggedInUserMenuOptions = "<li style='cursor: pointer;'><a data-toggle='modal' data-target='#post_form' onClick='javascript:edit$postId()'>Edit Post</a></li>

																				<li style='cursor: pointer;'><a id='post$postId'>Delete Post</a></li>";

					} else {
						$otherUserMenuOptions = "<li data-toggle='modal' data-target='#post_form' id='dropdown-post$postId'><a>Say Something to @$addedBy</a></li>

						<li><a href='". $this->filePath ."$addedBy'>Go to @$addedBy's profile</a></li>";
						$loggedInUserMenuOptions = "";
					}

					$userDetails = $this->db->query ("
						SELECT first_name, last_name, profile_pic, verified, username, private_account FROM users WHERE username = '$addedBy'
					");

					$userRow = $userDetails->fetch_assoc();
					$firstName = $userRow['first_name'];
					$lastName = $userRow['last_name'];
					$profilePic = $userRow['profile_pic'];
					$username = $userRow['username'];

					if ($userRow['private_account'] == 'yes' || $userRow['username'] == $loggedInUser) {
						$privateAccount = "private-account";
					} else {
						$privateAccount = "";
					}

					//check to see if user is verified
					if ($userRow['verified'] == 'yes') {
						$postVerifiedCheck = "<img class='verified' src='" . $this->filePath . "img/icons/verified.svg' title='Verified user'>";
					} else {
						$postVerifiedCheck = "";
					}

				//======================================================================
				//
				//	 check to see if post was shared
				//
				//======================================================================

					$totalShares = "";

					$getUserShared = $this->db->query("
						SELECT * FROM posts WHERE post_unique_id =  '$uniqueId' AND shared_by = '$loggedInUser'
					");
					$userShares = $getUserShared->num_rows;
					$shareMenuOption = "";
					if ($userShares > 0) {
						$sharedIconClass = 'user-shared';
						$shareValue = "Unshare";
						if ($loggedInUser != $addedBy) {
							$shareMenuOption = "<li class='share-menu-option share$postId'><a>Unshare this post</a></li>";
						}
					} else {
						$sharedIconClass= '';
						$shareValue = "Share";
						if ($loggedInUser != $addedBy) {
							$shareMenuOption = "<li class='share-menu-option share$postId'><a>Share this post</a></li>";
						}
					}

					if(!isset($totalShares)) {
						$totalShares = '';
					}

					$getSharedPost = $this->db->query("
						SELECT * FROM posts WHERE post_unique_id = '$uniqueId' AND shared_by = 'none'
					");
					$sharedPost = $getSharedPost->fetch_assoc();

					$getSharedUserName = $this->db->query("
						SELECT first_name, last_name FROM users WHERE username = '$sharedBy'
					");
					$sharedUser = $getSharedUserName->fetch_assoc();


					if (!empty($sharedBy) && $sharedBy != 'none') {


							$sharedDiv = "<div class='shared-div'>
										<a href='$sharedBy' class='post-name'>Shared by&nbsp;<span>" . $sharedUser['first_name'] . " " . $sharedUser['last_name'] . "</span></a>
									 </div>";

							$sharedClass = "shared";

						} else {

							$sharedDiv = "";
							$sharedClass = "";

						}

					$addedBy = $sharedPost['added_by'];
					$dateTime = $sharedPost['date_added'];
					$uniqueId = $sharedPost['post_unique_id'];
					$sharedUserDetails = $this->db->query ("
						SELECT first_name, last_name, profile_pic, verified, username FROM users WHERE username = '$addedBy'
					");

					$sharedRow = $sharedUserDetails->fetch_assoc();
					$firstName = $sharedRow['first_name'];
					$lastName = $sharedRow['last_name'];
					$profilePic = $sharedRow['profile_pic'];
					$username = $sharedRow['username'];

					$checkShares = $this->db->query("
						SELECT * FROM posts WHERE post_unique_id = '$uniqueId' AND shared_by <> 'none'
					");
					$totalShares = $checkShares->num_rows;

					if ($totalShares > 0) {
						$totalShares = $totalShares;
					} else if ($totalShares == 0) {
						$totalShares = "";
					}

					//timezone
					//set times for posts
					$dateTimeNow = date("Y-m-d H:i:s");
					$postStartDate = new DateTime($dateTime); //time of post
					$endDate = new DateTime($dateTimeNow); //current time
					$postInterval = $postStartDate->diff($endDate); //difference between dates
					$hourDiff = $postInterval->h + ($postInterval->d*24);
					$newtime = strtotime($dateTime);
					if ($hourDiff > 24) {
						$postTimeMessage = date("F j, Y", $newtime);
					} else if ($postInterval->h >=1) {
						if ($postInterval->h == 1) {
							$postTimeMessage = $postInterval->h . " hr";
						} else {
							$postTimeMessage = $postInterval->h . " hr";
						}
					} else if ($postInterval->i >=1) {
						if ($postInterval->i == 1) {
							$postTimeMessage = $postInterval->i . " min";
						} else {
							$postTimeMessage = $postInterval->i . " min";
						}
					} else {
						if ($postInterval->s < 30) {
							$postTimeMessage = "Just now";
						} else {
							$postTimeMessage = $postInterval->s . " sec";
						}
					}


			//======================================================================
			//
			//	check for comments
			//
			//======================================================================

					$commentsBlock = "";
					$getComments = $this->db->query("
						SELECT * FROM comments WHERE post_id = '$postId' ORDER BY id ASC
					");
					$commentCount = $getComments->num_rows;

					if ($commentCount > 0) {
						$commentCount = $commentCount;
					} else {
						$commentCount = "";
					}

					if ($commentCount != 0) {
						while ($comment = $getComments->fetch_assoc()) {
							$commentBody = "<p>" . preg_replace('#(\\\r|\\\r\\\n|\\\n)#', '</p><p>', $comment['post_body']) . "</p>";
							$commentBody = stripcslashes($commentBody);
							$postedTo = $comment['posted_to'];
							$postedBy = $comment['posted_by'];
							$dateAdded = $comment['date_added'];
							$removed = $comment['removed'];

							$getCommentorInfo = $this->db->query("
								SELECT first_name, last_name, username, profile_pic, verified FROM users WHERE username = '$postedBy'
							");
							$commentorInfo = $getCommentorInfo->fetch_assoc();
							$commentUser = $commentorInfo['first_name'] . " " . $commentorInfo['last_name'];
							$commentUsername = $commentorInfo['username'];

							if ($commentorInfo['verified'] == 'yes') {
								$commentVerifiedPic = $this->filePath . "img/icons/verified.svg";
								$commmentUserVerified = "verified";
							} else {
								$commentVerifiedPic = "";
								$commmentUserVerified = "";
							}

							//set times for comments
							//timezone
							$commentStartDate = new DateTime($dateAdded); //time of comment
							$commentInterval = $commentStartDate->diff($endDate); //difference between dates
							$commentHourDiff = $commentInterval->h + ($commentInterval->d*24);
							$newtime = strtotime($dateAdded);

							if ($commentHourDiff >= 24) {
								$commentTimeMessage = date("F j, Y", $newtime);
							} else if ($commentInterval->h >=1) {
								if ($commentInterval->h == 1) {
									$commentTimeMessage = $commentInterval->h . " hr";
								} else {
									$commentTimeMessage = $commentInterval->h . " hr";
								}
							} else if ($commentInterval->i >=1) {
								if ($commentInterval->i == 1) {
									$commentTimeMessage = $commentInterval->i . " min";
								} else {
									$commentTimeMessage = $commentInterval->i . " min";
								}
							} else {
								if ($commentInterval->s < 30) {
									$commentTimeMessage = "Just now";
								} else {
									$commentTimeMessage = $commentInterval->s . " sec";
								}
							}

							$commentsBlock .= "
													<div class='full-comment'>

														<div>

															<a href='" . $this->filePath . "$commentUsername'>
																<img src='" . $this->filePath . $commentorInfo['profile_pic'] . "' class='comment-profile-pic' title='$commentUser'>
															</a>


														</div>

														<div class='comment-section'>
															<div class='comment-user-name'>
																<a href='" . $this->filePath . "$commentUsername' class='post-name'><span>$commentUser</span><img class='$commmentUserVerified' src='" . "$commentVerifiedPic'></a><span class='post-username'>@$commentUsername</span>
															</div
															<span class='comment-time'>$commentTimeMessage</span>
															<div class='comment-body'>$commentBody</div>
														</div>
													</div>
												";


						} //end check for comments

					}

			//======================================================================
			//
			//	check for previous likes
			//
			//======================================================================

					$checkLikes = $this->db->query("
						SELECT * FROM likes WHERE username = '$loggedInUser' AND post_id = '$postId'
					");
					$userLikes = $checkLikes->num_rows;

					$checkLikes2 = $this->db->query("
						SELECT * FROM likes WHERE post_id = '$postId'
					");
					$totalLikes = $checkLikes2->num_rows;

					if ($userLikes > 0) {
						$likesName = "unlike_button";
						$likesValue = "Unlike";
						$userLikes = $userLikes;
						$likeFill = 'active';
					} else {
						$likesName = "like_button";
						$likesValue = "Like";
						$userLikes = "";
						$likeFill = 'inactive';
					}

					if ($totalLikes > 0) {
						$totalLikes = $totalLikes;
					} else {
						$totalLikes = "";
					}

					if ($imagePath != "") {
						$imageDiv = "<div class='postedImage'>
													<a class='posted-image$postId' href='$imagePath'><img src='$imagePath'></a>
												</div>";
					} else {
						$imageDiv = "";
					}

					echo "
						<div class='full-post-outer'>

							<div class='full-post' id='full-post$postId'>

								<div class='status-post'>

									<div class='body-content'>

										<div class='posted-by' style='color: #acacac;'>
											<div class='post-profile-pic'>
												<a href='" . $this->filePath . "$addedBy'><img src='" . $this->filePath . "$profilePic' alt='Profile Picture for $firstName $lastName'></a>
											</div>

											<div class='posted-by-info'>
												<div class='person-details'>
													<a href='" . $this->filePath . "$addedBy' class='post-name'><span>$firstName $lastName</span>$postVerifiedCheck</a><span class='post-username'>@$username</span>
												</div>
												<span>$postTimeMessage $editedText</span>
											</div>

											<div class='delete-button btn-group'>
											  <button type='button' data-toggle='dropdown' aria-haspopup='true' aria-expanded='false'>
											  	<img class='delete_button' src='" . $this->filePath . "img/icons/dot-menu.svg' alt='Post Menu'>
											  </button>
												<ul class='dropdown-menu'>
													$otherUserMenuOptions
													$shareMenuOption
													$loggedInUserMenuOptions
												</ul>
											</div>
										</div>

										<div id='post_body'>
											$body

											$youtubeVideo
											$imageDiv
										</div>

									</div>

								</div>

								<div class='newsfeed-post-options'>
									<div class='comment-button newsfeed-buttons' title='Comment' onClick='javascript:toggle$postId()'>
										<div>
											<svg id='comments$postId' class='comment-svg post-button' mlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink' viewBox='0 0 505.7 512'>
												<path d='M373.1,511c-12.6,0-25.3-2.3-37.8-6.8c-25.4-9.2-45.7-23.4-60.1-42.3l-0.3-0.4h-0.6
													c-7.8,0.6-14.8,0.9-21.5,0.9c-67.3,0-130.5-24-178.1-67.6C27.2,351.2,1,293.3,1,231.7S27.2,112.2,74.7,68.6
													C122.3,25,185.6,1,252.8,1c67.3,0,130.5,24,178.1,67.6c47.5,43.6,73.7,101.5,73.7,163.1c0,85.4-51.2,163.4-133.6,203.6l-0.9,0.4
													l0.3,0.9c3.4,9.2,7.5,18.5,12.3,27.6c10.5,20.2,33.2,30.8,40,33.6C417.3,501.2,399,511,373.1,511L373.1,511z'/>
												<path d='M252.8,2.1c33.9,0,66.7,6.1,97.7,18.1c29.9,11.6,56.7,28.1,79.7,49.2s41.1,45.6,53.7,73
													c13.1,28.3,19.7,58.3,19.7,89.3c0,42.4-12.7,83.8-36.8,119.8c-23.4,35-56.7,63.7-96.2,83l-1.7,0.8l0.7,1.8
													c3.4,9.2,7.6,18.5,12.3,27.7c9.9,19.1,30,29.6,38.6,33.3c-7,3.9-24.1,11.9-47.3,11.9c-12.5,0-25-2.3-37.4-6.8
													c-25.3-9.2-45.3-23.3-59.7-41.9l-0.7-0.9l-1.1,0.1c-7.8,0.6-14.8,0.9-21.4,0.9c-33.9,0-66.7-6.1-97.7-18.1
													c-29.9-11.6-56.7-28.1-79.7-49.3c-23-21.1-41.1-45.6-53.7-73C8.7,292.7,2.1,262.7,2.1,231.7s6.6-61,19.7-89.3
													c12.6-27.3,30.7-51.9,53.7-73s49.8-37.7,79.7-49.2C186.1,8.2,219,2.1,252.8,2.1 M252.8,0C113.2,0,0,103.7,0,231.7
													c0,127.9,113.2,231.7,252.8,231.7c7.3,0,14.4-0.3,21.5-0.9c12.4,16.1,31.3,32,60.6,42.6c13.9,5,26.8,6.9,38.2,6.9
													c31.7,0,52-14.3,52-14.3s-29-10.3-41.3-33.9c-5.1-9.9-9.1-19.1-12.2-27.5c79.8-39,134.1-116,134.1-204.6
													C505.7,103.7,392.5,0,252.8,0L252.8,0z'/>
											</svg>
											<span class='post-options-nums' id='commentsSpan$postId'>$commentCount</span>
										</div>

									</div>
									<div class='newsfeed-buttons' title='Like'>
										<form action='" . $this->filePath . "inc/handlers/likes.php?post_id=$postId&unique_id=$uniqueId' method='POST' class='like-button' id='likes$postId'>
											<input type='hidden' class='likesBody$postId $likesValue' name='post_body' value='$likesValue'>
											<button type='submit' name='like_button'>
												<div class='like-div'>

													<svg style='' id='like$postId' class='like-svg post-button $likeFill' version='1.1' xmlns='http://www.w3.org/2000/svg'viewBox='0 0 1024 1024'>
													<path d='M513.4,141c0,0,277.1-171.5,441.1,60.5c0,0,113.2,181-19.3,387.3c0,0-104.6,197.2-421.7,363.5
														c0,0-287.5-143.6-411.2-347.4c0,0-140.3-188.7-36.2-392.1C66.1,212.8,187-34.5,513.4,141z'/>
													</svg>

													<span class='post-options-nums' id='likesSpan$postId'>$totalLikes</span>
												</div>
											</button>
										</form>
									</div>

									<div class='share-button newsfeed-buttons $privateAccount $sharedIconClass' title='Share this post with your friends.'>
										<form action='" . $this->filePath . "inc/handlers/share.php?post_id=$postId&unique_id=$uniqueId' method='POST' class='share-button' id='share$postId'>
											<input type='hidden' class='shareBody$postId' name='post_body' value='$shareValue'>
											<button type='submit' name='share_button' class='share$postId'>
												<div>
												<svg class='share-svg post-button share-button$uniqueId' xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink'  viewBox='0 0 20 20'>
													<path d='M19.7,8.2l-6.6-6C12.6,1.7,12,2.2,12,3v3C7.3,6,3.3,8.9,1.4,12.8c-0.7,1.3-1.1,2.7-1.4,4.1
													c-0.2,1,1.3,1.5,1.9,0.6C4.1,14,7.8,11.7,12,11.7V15c0,0.8,0.6,1.3,1.1,0.8l6.6-6C20.1,9.4,20.1,8.6,19.7,8.2z'/>
												</svg>
												<span class='post-options-nums' id='shareSpan$uniqueId'>$totalShares</span>
											</div>
											</button>
										</form>
									</div>
								</div>

								</div>

							</div>

							<div class='post-comments' id='toggle_comment$postId'>
								<div class='post-comment-input'>
									<form class='comment-form' action='" . $this->filePath . "inc/handlers/comments.php?post_id=$postId&unique_id=$uniqueId' id='comment-form$postId' method='POST'>
										<textarea class='autoExpand' rows='1' data-min-rows='1' id='post_comment_body$postId' name='post_body'  placeholder='Say something...''></textarea>
										<input type='submit' name='post_comment$postId' value='Send'>
									</form>
								</div>
								<div id='toggle_comments$postId'>
									$commentsBlock
								</div>
							</div>

						</div>
						";


				?>

				<div>
					<script>
					$('#comment-form<?php echo $postId; ?>').submit(function(e) {

						var form = $('#comment-form<?php echo $postId; ?>');
						var formData = $(this).serialize();
						var postBody = $('#post_comment_body<?php echo $postId; ?>');
						var commentCountSpan =  $('#commentsSpan<?php echo $postId; ?>');
						if (!commentCount) {
							var commentCount = "<?php echo $commentCount; ?>";
						}
						var fullName = "<?php echo $userFullname; ?>";
						var username = "<?php echo $_SESSION['username']; ?>";
						var profilePic = "<?php echo $userProfilePic; ?>";
						var verifiedClass = "<?php echo $loggedInUserVerified; ?>";
						var verifiedPic = "<?php echo $loggedInUserVerifiedPic; ?>";
						var filePath = "<?php echo $this->filePath ?>";

						e.preventDefault();
						// Serialize the form data.

						// Submit the form using AJAX.
						$.ajax({
							type: 'POST',
							url: $(form).attr('action'),
							data: formData
						})
						.done(function(response) {
							if ($(postBody).val()) {
								$('#toggle_comment<?php echo $postId; ?>').append("<div class='full-comment' style='padding: 10px 0;'><a href='" + filePath + username + "'><img src='" + filePath + profilePic + "' class='comment-profile-pic'></a><div class='comment-section'><div><a href='" + filePath + username + "'><span>" + fullName + "</span><img class='" + verifiedClass + "' src='" + verifiedPic + "'></a><span class='post-username'> @" + username + "</span></div><span class='comment-time'>Just Now</span><div class='comment-body'>" + postBody.val() + "</div></div></div>");
								// Clear the form.
								$('#post_comment_body<?php echo $postId; ?>').val('');
								commentCount++;
								$(commentCountSpan).text(' ' + commentCount);
								$('#toggle_comment<?php echo $postId; ?>').css('display',' block');
							} else {
								alert("Uh oh! Comment field is empty!");
							}

						})
						.fail(function(data) {

						});

					});
					</script>

					<script>
					$('#likes<?php echo $postId; ?>').submit(function(e) {

							var form = $('#likes<?php echo $postId; ?>');
							var formData = $(this).serialize();
							var likesCountSpan =  $('#likesSpan<?php echo $postId; ?>');
							var totalLikes = $('#likesSpan<?php echo $postId; ?>').text();
							totalLikes = parseInt(totalLikes) || 0;
							var likesValue = $('.likesBody<?php echo $postId; ?>').val();

							e.preventDefault();
							// Submit the form using AJAX.

							$.ajax({
								type: 'POST',
								url: $(form).attr('action'),
								data: "likesValue=" + likesValue,

								success: function(data) {
									if (likesValue == 'Like') {
										totalLikes = totalLikes + 1;
										$('#like<?php echo $postId; ?>').css('fill', '#ff4c4c');
										$('.likesBody<?php echo $postId; ?>').removeClass('Like');
										$('.likesBody<?php echo $postId; ?>').addClass('Unlike');
										$('.likesBody<?php echo $postId; ?>').val('Unlike');
									} else if (likesValue == 'Unlike') {
										totalLikes = totalLikes - 1;
										$('#like<?php echo $postId; ?>').css('fill', '#fafafa');
										$('.likesBody<?php echo $postId; ?>').removeClass('Unlike');
										$('.likesBody<?php echo $postId; ?>').addClass('Like');
										$('.likesBody<?php echo $postId; ?>').val('Like');
									}
									$(likesCountSpan).text(' ' + totalLikes);
								}

							});

						});
					</script>

					<script>
					var $overlay = $("<div id=\"image-overlay\"></div>");
					var $image = $("<img>");
					var $caption = $("<p></p>");

					$overlay.append($image);
					$overlay.append($caption);
					$("body").append($overlay);

					// Capture the click event on a link to an image
					$(".posted-image<?php echo $postId; ?>").click(function(event) {

						event.preventDefault();
						var imageLocation = $(this).attr("href");
						//  Update overlay with the image linked in the link
						$image.attr("src", imageLocation);

						//  Show the overlay
						$overlay.fadeIn("fast");
						$overlay.css("display", "flex");

						//  Get child's alt attribute and set caption
						var captionText = $(this).children("img").attr("alt");
						$caption.text(captionText);
					});

					// When overlay is clicked
					$overlay.click(function() {
						// Hide overlay
						$overlay.fadeOut();
					});
					</script>

					 <script>

						$(document).ready(function() {

							$('#post<?php echo $postId; ?>').on('click', function() {
								bootbox.confirm("Are you sure you want to delete this post? ", function(result) {
									$.post("inc/handlers/delete-post.php?post_id=<?php echo $postId; ?>", {result: result});

									if (result) {
										$('#full-post<?php echo $postId; ?>').slideUp("slow");
									}
								});
							});

						});

					</script>

					<script>
						function edit<?php echo $postId; ?>() {

							var postId = "<?php echo $postId; ?>";
							var postBody = "<?php echo $editPostBody; ?>";
							var postImage = "<?php echo $imagePath; ?>";

							$('#postEdit').val("<?php echo $postId; ?>");
							$('.site-post textarea').val(postBody);
							$('.image-upload').hide();
							$('.modal-footer').css("justify-content", "flex-end");

							if (postImage !== "<?php echo $this->filePath; ?>" && postImage !== "") {

								$('#file-img').css("display", "flex");
								$('#file-img-close').hide();
								$('#file-img img').attr('src', postImage);

							}

							$('#youtube-video-edit').append("<?php echo $youtubeVideo; ?>");
							$('#submit_post').text("Edit");

						}
						</script>

					<script>
						function toggle<?php echo $postId; ?>() {
							var target = $(event.target);
							if (!target.is('a')) {

								if (!target.is('a')) {

									$('#toggle_comment<?php echo $postId; ?>').slideToggle("fast");

								}
							}
						}

					</script>
				</div>

				<?php

		} else {
			echo "<p>No post found. if you clicked a link, it may be broken.</p>";
			return;
		}
	}

	public function loadPostsTrending($data, $limit, $hashtag) {

		$page = $data['page'];
		$loggedInUser = $this->userObj->getUsername();
		$userFullname = $this->userObj->getFirstAndLastName();
		$userProfilePic = $this->userObj->getProfilePic();

		if ($page == 1) {
			$start = 0;
		} else {
			$start = ($page - 1) * $limit;
		}

		$postBlock = []; //arrays to return
		$getPosts = $this->db->query("
			SELECT * FROM posts WHERE deleted = 'no' AND shared_by = 'none' AND body REGEXP '([[:blank:][:punct:]]|^)#$hashtag([[:blank:][:punct:]]|$)' ORDER BY id DESC
		");

		$verifiedQuery = $this->db->query("
			SELECT verified FROM users WHERE username = '$loggedInUser'
		");

		$verifiedRow = $verifiedQuery->fetch_assoc();

		if ($verifiedRow['verified'] == 'yes') {
			$loggedInUserVerifiedPic = $this->filePath . "img/icons/verified.svg";
			$loggedInUserVerified = 'verified';
		} else {
			$loggedInUserVerifiedPic = "";
			$loggedInUserVerified = "";
		}


		if ($getPosts->num_rows > 0 && !empty($hashtag)) {

			$numIterations =  0; //number of results checked (not necessarily posted)
			$count = 1;

			while ($postRow = $getPosts->fetch_assoc()) {
				//prepare userTo string so it can be included even if not posted to user
				if ($postRow['user_to'] == 'none') {
					$userTo = "";
				} else {
					$userToObj = new User($this->db, $postRow['user_to']);
					$userToName = $userToObj->getFirstAndLastName();
					$userTo = $postRow['user_to'];
					$userTo = "<a class='user-to' href='" . $userTo . "'>@" . $userTo . "</a>";
				}

				// user info
				$addedBy = $postRow['added_by'];
				$dateTime = $postRow['date_added'];
				$imagePath = $this->filePath . $postRow['image'];
				if ($imagePath === $this->filePath) $imagePath = "";
				$youtubeVideo = $postRow['youtube'];
				$postId = $postRow['id'];
				$uniqueId = $postRow['post_unique_id'];
				$postAdded = $postRow['date_added'];
				$sharedBy = $postRow['shared_by'];
				$body = $postRow['body'];

				$editedPost = $postRow['edited'];

				if ($editedPost == 'yes') {
					$editedPostClass = 'edited-post';
					$editedText = "| Edited";
				} else {
					$editedPostClass = '';
					$editedText = "";
				}

				$editBodyArray = [];
				$editBodyArray[] = preg_split("/<div/", $body);
				$editBody = $editBodyArray[0];
				$newEditBody = [];
				foreach ($editBody as $edit) {
					if (strpos($edit, "youtube")) $edit = "";

					$newEditBody[] = $edit;
				}

				$editPostBody = implode(" ", $newEditBody);
				$editPostBody = preg_replace('/\s+/', ' ', $editPostBody); //deletes all spaces

				//format body text
				$body = "<p >" . preg_replace('#(\\\r|\\\r\\\n|\\\n)#', '</p><p>', $body) . "</p>";
				$body = stripcslashes($body);
				//Convert hashtags to <a> links
				$body = preg_replace("/#([A-Za-z0-9\/\.]*)/", "<a class=\"post-hashtag\" href='" . $this->filePath . "i/trending/hashtag/$1'>#$1</a>", $body);
				//Convert @ tags to <a> links
				$body = preg_replace("/@([A-Za-z0-9\/\.]*)/", "<a class=\"post-hashtag\" href='" . $this->filePath . "$1'>@$1</a>", $body);
				//posting links
				if (strpos($body, "youtube.com") == false) {
					// The Regular Expression filter
					$reg_exUrl = "/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/";
					// Check if there is a url in the body
					if (preg_match($reg_exUrl, $body, $url)) {
							$url[0] = strip_tags($url[0]);
					       // make the urls hyper links
						   $out = strlen($url[0]) > 20 ? substr($url[0],0,30)."..." : $url[0];
					       $body = preg_replace($reg_exUrl, "<a class='post-hashtag' href='$url[0]' target='_blank'>$out</a> ", $body);
					} else {
					       // if no urls in the body just return the body
					       $body = $body;
					}
				}

				//check if user who posted, has their account closed
				$addedByObj = new User($this->db, $addedBy);
				if ($addedByObj->isClosed()) {
					continue;
				}

				$userDetails = $this->db->query ("
					SELECT private_account FROM users WHERE username = '$addedBy'
				");
				$checkUserPrivacy = $userDetails->fetch_assoc();

				$loggedInUserObj = new User($this->db, $loggedInUser);

				if ($checkUserPrivacy['private_account'] == 'no' || $loggedInUserObj->isFollowing($addedBy)) {

					if ($numIterations++ < $start) {
						continue;
					}

					// once 10 posts have been loaded, break
					if ($count > $limit) {
						break;
					} else {
						$count++;
					}

					if ($loggedInUser == $addedBy) {
						$otherUserMenuOptions = "";
						$loggedInUserMenuOptions = "<li style='cursor: pointer;'><a data-toggle='modal' data-target='#post_form' onClick='javascript:edit$postId()'>Edit Post</a></li>

																				<li style='cursor: pointer;'><a id='post$postId'>Delete Post</a></li>";

					} else {
						$otherUserMenuOptions = "<li data-toggle='modal' data-target='#post_form' 			id='dropdown-post$postId'><a>Say Something to @$addedBy</a></li>

						<li><a href='". $this->filePath ."$addedBy'>Go to @$addedBy's profile</a></li>";
						$loggedInUserMenuOptions = "";
					}

					//get post user info
					$userDetails = $this->db->query ("
						SELECT first_name, last_name, profile_pic, verified, username, private_account FROM users WHERE username = '$addedBy'
					");

					$userRow = $userDetails->fetch_assoc();
					$firstName = $userRow['first_name'];
					$lastName = $userRow['last_name'];
					$profilePic = $userRow['profile_pic'];
					$username = $userRow['username'];

					if ($userRow['private_account'] == 'yes' || $userRow['username'] == $loggedInUser) {
						$privateAccount = "private-account";
					} else {
						$privateAccount = "";
					}

					//check to see if user is verified
					if ($userRow['verified'] == 'yes') {
						$postVerifiedCheck = "<img class='verified' src='" . $this->filePath . "img/icons/verified.svg' title='Verified user'>";
					} else {
						$postVerifiedCheck = "";
					}

					//======================================================================
					//
					//	 check to see if post was shared
					//
					//======================================================================

						$totalShares = "";

						$getUserShared = $this->db->query("
							SELECT * FROM posts WHERE post_unique_id =  '$uniqueId' AND shared_by = '$loggedInUser'
						");
						$userShares = $getUserShared->num_rows;

						$shareMenuOption = "";
						if ($userShares > 0) {
							$sharedIconClass = 'user-shared';
							$shareValue = "Unshare";
							if ($loggedInUser != $addedBy) {
								$shareMenuOption = "<li class='share-menu-option share$postId'><a>Unshare this post</a></li>";
							}
						} else {
							$sharedIconClass= '';
							$shareValue = "Share";
							if ($loggedInUser != $addedBy) {
								$shareMenuOption = "<li class='share-menu-option share$postId'><a>Share this post</a></li>";
							}
						}

						if(!isset($totalShares)) {
							$totalShares = '';
						}

						$getSharedPost = $this->db->query("
							SELECT * FROM posts WHERE post_unique_id = '$uniqueId' AND shared_by = 'none'
						");
						$sharedPost = $getSharedPost->fetch_assoc();

						$getSharedUserName = $this->db->query("
							SELECT first_name, last_name FROM users WHERE username = '$sharedBy'
						");
						$sharedUser = $getSharedUserName->fetch_assoc();


						if (!empty($sharedBy) && $sharedBy != 'none') {


							$sharedDiv = "<div class='shared-div'>
										<a href='$sharedBy' class='post-name'>Shared by&nbsp;<span>" . $sharedUser['first_name'] . " " . $sharedUser['last_name'] . "</span></a>
									 </div>";

							$sharedClass = "shared";

						} else {

							$sharedDiv = "";
							$sharedClass = "";

						}

						$addedBy = $sharedPost['added_by'];
						$dateTime = $sharedPost['date_added'];
						$uniqueId = $sharedPost['post_unique_id'];
						$sharedUserDetails = $this->db->query ("
							SELECT first_name, last_name, profile_pic, verified, username FROM users WHERE username = '$addedBy'
						");

						$sharedRow = $sharedUserDetails->fetch_assoc();
						$firstName = $sharedRow['first_name'];
						$lastName = $sharedRow['last_name'];
						$profilePic = $sharedRow['profile_pic'];
						$username = $sharedRow['username'];

						$checkShares = $this->db->query("
							SELECT * FROM posts WHERE post_unique_id = '$uniqueId' AND shared_by <> 'none'
						");
						$totalShares = $checkShares->num_rows;

						if ($totalShares > 0) {
							$totalShares = $totalShares;
						} else if ($totalShares == 0) {
							$totalShares = "";
						}


						//timezone
						//set times for posts
						$dateTimeNow = date("Y-m-d H:i:s");
						$postStartDate = new DateTime($dateTime); //time of post
						$endDate = new DateTime($dateTimeNow); //current time
						$postInterval = $postStartDate->diff($endDate); //difference between dates
						$hourDiff = $postInterval->h + ($postInterval->d*24);
						$newtime = strtotime($dateTime);
						if ($hourDiff > 24) {
							$postTimeMessage = date("F j, Y", $newtime);
						} else if ($postInterval->h >=1) {
							if ($postInterval->h == 1) {
								$postTimeMessage = $postInterval->h . " hr";
							} else {
								$postTimeMessage = $postInterval->h . " hr";
							}
						} else if ($postInterval->i >=1) {
							if ($postInterval->i == 1) {
								$postTimeMessage = $postInterval->i . " min";
							} else {
								$postTimeMessage = $postInterval->i . " min";
							}
						} else {
							if ($postInterval->s < 30) {
								$postTimeMessage = "Just now";
							} else {
								$postTimeMessage = $postInterval->s . " sec";
							}
						}

					//======================================================================
					//
					//	check for comments
					//
					//======================================================================

						$commentsCheck = $this->db->query("
							SELECT * FROM comments WHERE post_unique_id = '$uniqueId'
						");
						$commentsCheckNum = $commentsCheck->num_rows;

						$commentsBlock = "";
						$getComments = $this->db->query("
							SELECT * FROM comments WHERE post_unique_id = '$uniqueId' ORDER BY id ASC
						");
						$commentCount = $getComments->num_rows;

						if ($commentCount > 0) {
							$commentCount = $commentCount;
						} else {
							$commentCount = "";
						}

						if ($commentCount != 0) {

							while ($comment = $getComments->fetch_assoc()) {
								$commentBody = "<p>" . preg_replace('#(\\\r|\\\r\\\n|\\\n)#', '</p><p>', $comment['post_body']) . "</p>";
								$commentBody = stripcslashes($commentBody);
								$postedTo = $comment['posted_to'];
								$postedBy = $comment['posted_by'];
								$dateAdded = $comment['date_added'];
								$removed = $comment['removed'];

								$getCommentorInfo = $this->db->query("
									SELECT first_name, last_name, username, profile_pic, verified FROM users WHERE username = '$postedBy'
								");
								$commentorInfo = $getCommentorInfo->fetch_assoc();
								$commentUser = $commentorInfo['first_name'] . " " . $commentorInfo['last_name'];
								$commentUsername = $commentorInfo['username'];

								if ($commentorInfo['verified'] == 'yes') {
									$commentVerifiedPic = $this->filePath . "img/icons/verified.svg";
									$commmentUserVerified = "verified";
								} else {
									$commentVerifiedPic = "";
									$commmentUserVerified = "";
								}

								//set times for comments
								//timezone
								$commentStartDate = new DateTime($dateAdded); //time of comment
								$commentInterval = $commentStartDate->diff($endDate); //difference between dates
								$commentHourDiff = $commentInterval->h + ($commentInterval->d*24);
								$newtime = strtotime($dateAdded);

								if ($commentHourDiff >= 24) {
									$commentTimeMessage = date("F j, Y", $newtime);
								} else if ($commentInterval->h >=1) {
									if ($commentInterval->h == 1) {
										$commentTimeMessage = $commentInterval->h . " hr";
									} else {
										$commentTimeMessage = $commentInterval->h . " hr";
									}
								} else if ($commentInterval->i >=1) {
									if ($commentInterval->i == 1) {
										$commentTimeMessage = $commentInterval->i . " min";
									} else {
										$commentTimeMessage = $commentInterval->i . " min";
									}
								} else {
									if ($commentInterval->s < 30) {
										$commentTimeMessage = "Just now";
									} else {
										$commentTimeMessage = $commentInterval->s . " sec";
									}
								}

								$commentsBlock .= "
													<div class='full-comment'>

														<div>

															<a href='" . $this->filePath . "$commentUsername'>
																<img src='" . $this->filePath . $commentorInfo['profile_pic'] . "' class='comment-profile-pic' title='$commentUser'>
															</a>


														</div>

														<div class='comment-section'>
															<div class='comment-user-name'>
																<a href='" . $this->filePath . "$commentUsername' class='post-name'><span>$commentUser</span><img class='$commmentUserVerified' src='" . "$commentVerifiedPic'></a><span class='post-username'>@$commentUsername</span>
															</div
															<span class='comment-time'>$commentTimeMessage</span>
															<div class='comment-body'>$commentBody</div>
														</div>
													</div>
												";

							}

						}//end of comments check

					//======================================================================
					//
					//	check for previous likes
					//
					//======================================================================


						$checkLikes = $this->db->query("
							SELECT * FROM likes WHERE username = '$loggedInUser' AND post_unique_id = '$uniqueId'
						");
						$userLikes = $checkLikes->num_rows;

						if ($userLikes > 0) {
							$likesName = "unlike_button";
							$likesValue = "Unlike";
							$userLikes = $userLikes;
							$likeFill = 'active';
						} else {
							$likesName = "like_button";
							$likesValue = "Like";
							$userLikes = "";
							$likeFill = 'inactive';
						}

						$checkLikes2 = $this->db->query("
							SELECT * FROM likes WHERE post_unique_id = '$uniqueId'
						");
						$totalLikes = $checkLikes2->num_rows;

						if ($totalLikes > 0) {
							$totalLikes = $totalLikes;
						} else {
							$totalLikes = "";
						} // end of likes


						if ($imagePath != "") {
							$imageDiv = "<div class='postedImage'>
														<a class='posted-image$postId' href='$imagePath'><img src='$imagePath'></a>
													</div>";
						} else {
							$imageDiv = "";
						}


					$postBlock[] = "
									<div class='full-post-outer $sharedClass' id='full-post-outer$postId'>
										$sharedDiv

										<div class='full-post' id='full-post$postId'>

											<div class='status-post'>

												<div class='body-content'>

													<div class='posted-by' style='color: #acacac;'>

														<div class='post-profile-pic'>
															<a href='" . $this->filePath . "$addedBy'><img src='" . $this->filePath . "$profilePic' alt='Profile Picture for $firstName $lastName'></a>
														</div>

														<div class='posted-by-info'>
															<div class='person-details'>
																<a href='" . $this->filePath . "$addedBy' class='post-name'><span>$firstName $lastName</span>$postVerifiedCheck</a><span class='post-username'id='post-username$postId'>@$username</span>
															</div>
															<span>$postTimeMessage</span>
														</div>

														<div class='delete-button btn-group'>
														  <button type='button' data-toggle='dropdown' aria-haspopup='true' aria-expanded='false'>
														  	<img class='delete_button' src='" . $this->filePath . "img/icons/dot-menu.svg' alt='Post Menu'>
														  </button>
														  <ul class='dropdown-menu'>
																$otherUserMenuOptions
																$shareMenuOption
																$loggedInUserMenuOptions
														  </ul>
														</div>
													</div>

													<div id='post_body'>
													$body

													$youtubeVideo
													$imageDiv
													</div>

												</div>

											</div>

											<div class='newsfeed-post-options'>
												<div class='comment-button newsfeed-buttons' title='Comment on this post.' onClick='javascript:toggle$postId()'>
													<div>
														<svg id='comments$postId' class='comment-svg post-button' mlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink' viewBox='0 0 505.7 512'>
															<path d='M373.1,511c-12.6,0-25.3-2.3-37.8-6.8c-25.4-9.2-45.7-23.4-60.1-42.3l-0.3-0.4h-0.6
																c-7.8,0.6-14.8,0.9-21.5,0.9c-67.3,0-130.5-24-178.1-67.6C27.2,351.2,1,293.3,1,231.7S27.2,112.2,74.7,68.6
																C122.3,25,185.6,1,252.8,1c67.3,0,130.5,24,178.1,67.6c47.5,43.6,73.7,101.5,73.7,163.1c0,85.4-51.2,163.4-133.6,203.6l-0.9,0.4
																l0.3,0.9c3.4,9.2,7.5,18.5,12.3,27.6c10.5,20.2,33.2,30.8,40,33.6C417.3,501.2,399,511,373.1,511L373.1,511z'/>
															<path d='M252.8,2.1c33.9,0,66.7,6.1,97.7,18.1c29.9,11.6,56.7,28.1,79.7,49.2s41.1,45.6,53.7,73
																c13.1,28.3,19.7,58.3,19.7,89.3c0,42.4-12.7,83.8-36.8,119.8c-23.4,35-56.7,63.7-96.2,83l-1.7,0.8l0.7,1.8
																c3.4,9.2,7.6,18.5,12.3,27.7c9.9,19.1,30,29.6,38.6,33.3c-7,3.9-24.1,11.9-47.3,11.9c-12.5,0-25-2.3-37.4-6.8
																c-25.3-9.2-45.3-23.3-59.7-41.9l-0.7-0.9l-1.1,0.1c-7.8,0.6-14.8,0.9-21.4,0.9c-33.9,0-66.7-6.1-97.7-18.1
																c-29.9-11.6-56.7-28.1-79.7-49.3c-23-21.1-41.1-45.6-53.7-73C8.7,292.7,2.1,262.7,2.1,231.7s6.6-61,19.7-89.3
																c12.6-27.3,30.7-51.9,53.7-73s49.8-37.7,79.7-49.2C186.1,8.2,219,2.1,252.8,2.1 M252.8,0C113.2,0,0,103.7,0,231.7
																c0,127.9,113.2,231.7,252.8,231.7c7.3,0,14.4-0.3,21.5-0.9c12.4,16.1,31.3,32,60.6,42.6c13.9,5,26.8,6.9,38.2,6.9
																c31.7,0,52-14.3,52-14.3s-29-10.3-41.3-33.9c-5.1-9.9-9.1-19.1-12.2-27.5c79.8-39,134.1-116,134.1-204.6
																C505.7,103.7,392.5,0,252.8,0L252.8,0z'/>
														</svg>
														<span class='post-options-nums' id='commentsSpan$postId'>$commentCount</span>
													</div>
												</div>

												<div class='newsfeed-buttons' title='Like this post.'>
													<form action='" . $this->filePath . "inc/handlers/likes.php?post_id=$postId&unique_id=$uniqueId' method='POST' class='like-button' id='likes$postId'>
														<input type='hidden' class='likesBody$postId $likesValue' name='post_body' value='$likesValue'>
														<button type='submit' name='like_button'>
															<div class='like-div'>

																<svg id='like$postId' class='like-svg post-button $likeFill' version='1.1' xmlns='http://www.w3.org/2000/svg'viewBox='0 0 1024 1024'>
																<path d='M513.4,141c0,0,277.1-171.5,441.1,60.5c0,0,113.2,181-19.3,387.3c0,0-104.6,197.2-421.7,363.5
																	c0,0-287.5-143.6-411.2-347.4c0,0-140.3-188.7-36.2-392.1C66.1,212.8,187-34.5,513.4,141z'/>
																</svg>

																<span class='post-options-nums' id='likesSpan$postId'>$totalLikes</span>
															</div>
														</button>
													</form>
												</div>

												<div class='share-button newsfeed-buttons $privateAccount $sharedIconClass' title='Share this post with your friends.'>
													<form action='" . $this->filePath . "inc/handlers/share.php?post_id=$postId&unique_id=$uniqueId' method='POST' class='share-button' id='share$postId'>
														<input type='hidden' class='shareBody$postId' name='post_body' value='$shareValue'>
														<button type='submit' name='share_button' class='share$postId'>
															<div>
															<svg class='share-svg post-button share-button$uniqueId' xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink'  viewBox='0 0 20 20'>
																<path d='M19.7,8.2l-6.6-6C12.6,1.7,12,2.2,12,3v3C7.3,6,3.3,8.9,1.4,12.8c-0.7,1.3-1.1,2.7-1.4,4.1
																c-0.2,1,1.3,1.5,1.9,0.6C4.1,14,7.8,11.7,12,11.7V15c0,0.8,0.6,1.3,1.1,0.8l6.6-6C20.1,9.4,20.1,8.6,19.7,8.2z'/>
															</svg>
															<span class='post-options-nums' id='shareSpan$uniqueId'>$totalShares</span>
														</div>
														</button>
													</form>
												</div>
											</div>

											<div class='post-comments' id='toggle_comment$postId' style='display: none;'>
												<div class='post-comment-input'>
													<form class='comment-form comment-form$postId' action='" . $this->filePath . "inc/handlers/comments.php?post_id=$postId&unique_id=$uniqueId' method='POST'>
														<textarea class='autoExpand' rows='1' data-min-rows='1' id='post_comment_body$postId' name='post_body'  placeholder='Say something...''></textarea>
														<input type='submit' name='post_comment$postId' value='Send'>
													</form>
												</div>
												<div id='toggle_comments$postId'>
													$commentsBlock
												</div>
											</div>

										</div>

									</div>
								";

					?>

					<div>
					<script>
						$('.comment-form<?php echo $postId; ?>').submit(function(e) {

							var form = $('.comment-form<?php echo $postId; ?>');
							var formData = $(this).serialize();
							var postBody = $('#post_comment_body<?php echo $postId; ?>');
							var commentCountSpan =  $('#commentsSpan<?php echo $postId; ?>');
							if (!commentCount) {
								var commentCount = "<?php echo $commentCount; ?>";
							}
							var fullName = "<?php echo $userFullname; ?>";
							var username = "<?php echo $_SESSION['username']; ?>";
							var profilePic = "<?php echo $userProfilePic; ?>";
							var verifiedClass = "<?php echo $loggedInUserVerified; ?>";
							var verifiedPic = "<?php echo $loggedInUserVerifiedPic; ?>";
							var filePath = "<?php echo $this->filePath ?>";

							e.preventDefault();
							// Serialize the form data.

							// Submit the form using AJAX.
							$.ajax({
								type: 'POST',
								url: $(form).attr('action'),
								data: formData
							})
							.done(function(response) {
								if ($(postBody).val()) {
									$('#toggle_comment<?php echo $postId; ?>').append("<div class='full-comment' style='padding: 10px 0;'><a href='" + username + "'><img src='" + filePath + profilePic + "' class='comment-profile-pic'></a><div class='comment-section'><div><a href='" + username + "'><span>" + fullName + "</span><img class='" + verifiedClass + "' src='" + verifiedPic + "'></a><span class='post-username'> @" + username + "</span></div><span class='comment-time'>Just Now</span><div class='comment-body'>" + postBody.val() + "</div></div></div>");
									// Clear the form.
									$('#post_comment_body<?php echo $postId; ?>').val('');
									commentCount++;
									$(commentCountSpan).text(' ' + commentCount);
									$('#toggle_comment<?php echo $postId; ?>').css('display',' block');
								} else {
									alert("Uh oh! Comment field is empty!");
								}

							})
							.fail(function(data) {

							});

						});
						</script>

						<script>
						$('#likes<?php echo $postId; ?>').submit(function(e) {

							var form = $('#likes<?php echo $postId; ?>');
							var likesCountSpan =  $('#likesSpan<?php echo $postId; ?>');
							var totalLikes = $('#likesSpan<?php echo $postId; ?>').text();
							totalLikes = parseInt(totalLikes) || 0;
							var likesValue = $('.likesBody<?php echo $postId; ?>').val();

							e.preventDefault();
							// Submit the form using AJAX.
							$.ajax({
								type: 'POST',
								url: $(form).attr('action'),
								data: "likesValue=" + likesValue,
								success: function(data) {
									if (likesValue == 'Like') {
										totalLikes = totalLikes + 1;
										$('#like<?php echo $postId; ?>').css('fill', '#ff4c4c');
										$('.likesBody<?php echo $postId; ?>').removeClass('Like');
										$('.likesBody<?php echo $postId; ?>').addClass('Unlike');
										$('.likesBody<?php echo $postId; ?>').val('Unlike');
									} else if (likesValue == 'Unlike') {
										totalLikes = totalLikes - 1;
										$('#like<?php echo $postId; ?>').css('fill', '#fafafa');
										$('.likesBody<?php echo $postId; ?>').removeClass('Unlike');
										$('.likesBody<?php echo $postId; ?>').addClass('Like');
										$('.likesBody<?php echo $postId; ?>').val('Like');
									}
									$(likesCountSpan).text(' ' + totalLikes);
								}

							});

						});
						</script>

						<script>
						$('.share<?php echo $postId; ?>').click(function(e) {

							var form = $('#share<?php echo $postId; ?>');
							var shared_by = $('<?php echo $username; ?>');
							var shareValue = $('.shareBody<?php echo $postId; ?>').val();
							var shareCountSpan =  $('#shareSpan<?php echo $uniqueId; ?>');
							var totalShares = $('#shareSpan<?php echo $postId; ?>').text();
							totalShares = parseInt(totalShares) || 0;
							var shareMessage = "";
							if (shareValue == 'Share') {
								shareMessage = "You are about to share this post.";
							} else if (shareValue == 'Unshare') {
								shareMessage = "You are about to unshare this post.";
							}

							e.preventDefault();
							bootbox.confirm(shareMessage, function(result) {

								if (result) {
										// Submit the form using AJAX.
									$.ajax({
										type: 'POST',
										url: $(form).attr('action'),
										data: "shared_by=" + shared_by + "&shareValue=" + shareValue,

										success: function(data) {
											if (shareValue == 'Share') {
												totalShares = totalShares + 1;
												$('.share-button<?php echo $uniqueId; ?>').css('fill', '#59b063');
												$('.shareBody<?php echo $postId; ?>').val('Unshare');
											} else if (shareValue == 'Unshare') {
												// totalShares = totalShares - 1;
												// i need to figure out a good way to remove the shared post when another post with the same unique_id is clicked to uns
											}
											$(shareCountSpan).text(' ' + totalShares);
										}

									});
								}

							});

						});
						</script>

						<script>
						var $overlay = $("<div id=\"image-overlay\"></div>");
						var $image = $("<img>");
						var $caption = $("<p></p>");

						$overlay.append($image);
						$overlay.append($caption);
						$("body").append($overlay);

						// Capture the click event on a link to an image
						$(".posted-image<?php echo $postId; ?>").click(function(event) {

						  event.preventDefault();
						  var imageLocation = $(this).attr("href");
						  //  Update overlay with the image linked in the link
						  $image.attr("src", imageLocation);

						  //  Show the overlay
						  $overlay.fadeIn("fast");
							$overlay.css("display", "flex");

						  //  Get child's alt attribute and set caption
						  var captionText = $(this).children("img").attr("alt");
						  $caption.text(captionText);
						});

						// When overlay is clicked
						$overlay.click(function() {
						  // Hide overlay
						  $overlay.fadeOut();
						});
						</script>

						<script>
						$('#sharedbyuser<?php echo $postId; ?>').submit(function(e) {

							e.preventDefault();

						});
						</script>

						 <script>

							$(document).ready(function() {

								$('#post<?php echo $postId; ?>').on('click', function() {
									bootbox.confirm("Are you sure you want to delete this post? ", function(result) {
										$.post("inc/handlers/delete-post.php?post_id=<?php echo $postId; ?>", {result: result});

										if (result) {
											$('#full-post<?php echo $postId; ?>').slideUp("slow");
										}
									});
								});

							});

						</script>

						<script>
						function edit<?php echo $postId; ?>() {

							var postId = "<?php echo $postId; ?>";
							var postBody = "<?php echo $editPostBody; ?>";
							var postImage = "<?php echo $imagePath; ?>";

							$('#postEdit').val("<?php echo $postId; ?>");
							$('.site-post textarea').val(postImage);
							$('.image-upload').hide();
							$('.modal-footer').css("justify-content", "flex-end");

							if (postImage !== "<?php echo $this->filePath; ?>" && postImage !== "") {

								$('#file-img').css("display", "flex");
								$('#file-img-close').hide();
								$('#file-img img').attr('src', postImage);

							}

							$('#youtube-video-edit').append("<?php echo $youtubeVideo; ?>");
							$('#submit_post').text("Edit");

						}
						</script>

						<script>
							function toggle<?php echo $postId; ?>() {
								var target = $(event.target);
								if (!target.is('a')) {

									if (!target.is('a')) {

										$('#toggle_comment<?php echo $postId; ?>').slideToggle("fast");

									}
								}
							}
						</script>

						<script>
						$('#dropdown-post<?php echo $postId; ?>').click(function() {
							var username = $('#post-username<?php echo $postId; ?>').text();
							$('#post_form textarea').text(username + ' ');
						});
						</script>
					</div>

				<?php

				}

			} //end while loop

			if ($count > $limit) {
				$postBlock[] = "<input type='hidden' class='next-page' value='" . ($page + 1) . "'>
						<input type='hidden' class='no-more-posts' value='false'>
						<div class='loading'>
							<h5>Show More Posts</h5>
						</div>";
			?>
			<script>
			$(document).ready(function() {
				$('.loading').on('click', function () {
					var page = $('.posts-area').find('.next-page').val();
					var noMorePosts = $('.posts-area').find('.no-more-posts').val();
					var ajaxReq = $.ajax ({
						url: "inc/ajax/ajax-load-posts.php",
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
				});
			});
			</script>
			<?php
			} else {
				$postBlock[] = "<input type='hidden' class='no-more-posts' value='true'><p class='no-more-posts'>Back to Top</p>";
			?>

			<script>
			$(document).ready(function() {
				$(".no-more-posts").click(function() {
				  	$("html, body").animate({ scrollTop: 0 }, 400);
	  				return false;
				});
			});
			</script>

			<?php
			}

		}

		foreach ($postBlock as $block) {
			echo $block;
		}
	}

	public function loadLikedPosts($data, $limit, $likes) {

		$page = $data['page'];
		$profileUser = $data['profileUsername'];
		$loggedInUser = $this->userObj->getUsername();
		$userFullname = $this->userObj->getFirstAndLastName();
		$userProfilePic = $this->userObj->getProfilePic();

		// check to see if account is loggedinuser is following else check if profileuser is private

		if ($page == 1) {
			$start = 0;
		} else {
			$start = ($page - 1) * $limit;
		}

		$postBlock = []; //arrays to return

		// if ($likes === 'liked-posts') {

			$getLikedPosts = $this->db->query("
				SELECT * FROM likes WHERE username = '$profileUser' ORDER BY id DESC
			");

			$likedArray = [];

			while ($likedPosts = $getLikedPosts->fetch_assoc()) {

				$likedArray[] = $likedPosts['post_id'];

			}

		// }


		// check if loggedInUser is verified
		$verifiedQuery = $this->db->query("
			SELECT verified FROM users WHERE username = '$profileUser'
		");
		$verifiedRow = $verifiedQuery->fetch_assoc();
		if ($verifiedRow['verified'] == 'yes') {
			$loggedInUserVerifiedPic = $this->filePath . "img/icons/verified.svg";
			$loggedInUserVerified = 'verified';
		} else {
			$loggedInUserVerifiedPic = "";
			$loggedInUserVerified = "";
		}

		$loggedInUserObj = new User($this->db, $loggedInUser);

		if (($loggedInUserObj->isFollowing($profileUser) || $loggedInUserObj->checkIfPrivate($profileUser) != 'yes')) {

			$numIterations =  0; //number of results checked (not necessarily posted)
			$count = 1;

				foreach ($likedArray as $liked) {

					$getPosts = $this->db->query("
						SELECT * FROM posts WHERE id = '$liked' AND deleted = 'no' ORDER BY date_added DESC
					");
					$postRow = $getPosts->fetch_assoc();

					if ($postRow['deleted'] !== 'no') {
						continue;
					}

				//prepare userTo string so it can be included even if not posted to user
				if ($postRow['user_to'] == 'none') {
					$userTo = "";
				} else {
					$userToObj = new User($this->db, $postRow['user_to']);
					$userToName = $userToObj->getFirstAndLastName();
					$userTo = $postRow['user_to'];
					$userTo = "<a class='user-to' href='" . $userTo . "'>@" . $userTo . "</a>";
				}

				// user info
				$addedBy = $postRow['added_by'];
				$dateTime = $postRow['date_added'];
				$imagePath = $this->filePath . $postRow['image'];
				if ($imagePath === $this->filePath) $imagePath = "";
				$youtubeVideo = $postRow['youtube'];
				$postId = $postRow['id'];
				$uniqueId = $postRow['post_unique_id'];
				$postAdded = $postRow['date_added'];
				$sharedBy = $postRow['shared_by'];
				$body = $postRow['body'];

				$editedPost = $postRow['edited'];

				if ($editedPost == 'yes') {
					$editedPostClass = 'edited-post';
					$editedText = "| Edited";
				} else {
					$editedPostClass = '';
					$editedText = "";
				}

				$editBodyArray = [];
				$editBodyArray[] = preg_split("/<div/", $body);
				$editBody = $editBodyArray[0];
				$newEditBody = [];
				foreach ($editBody as $edit) {
					if (strpos($edit, "youtube")) $edit = "";

					$newEditBody[] = $edit;
				}

				$editPostBody = implode(" ", $newEditBody);
				$editPostBody = preg_replace('/\s+/', ' ', $editPostBody); //deletes all spaces

				//format body text
				$body = "<p >" . preg_replace('#(\\\r|\\\r\\\n|\\\n)#', '</p><p>', $body) . "</p>";
				$body = stripcslashes($body);
				//Convert hashtags to <a> links
				$body = preg_replace("/#([A-Za-z0-9\/\.]*)/", "<a class=\"post-hashtag\" href='" . $this->filePath . "i/trending/hashtag/$1'>#$1</a>", $body);
				//Convert @ tags to <a> links
				$body = preg_replace("/@([A-Za-z0-9\/\.]*)/", "<a class=\"post-hashtag\" href='" . $this->filePath . "$1'>@$1</a>", $body);
				//posting links
				if (strpos($body, "youtube.com") == false) {
					// The Regular Expression filter
					$reg_exUrl = "/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/";
					// Check if there is a url in the body
					if (preg_match($reg_exUrl, $body, $url)) {
							$url[0] = strip_tags($url[0]);
								 // make the urls hyper links
							 $out = strlen($url[0]) > 20 ? substr($url[0],0,30)."..." : $url[0];
								 $body = preg_replace($reg_exUrl, "<a class='post-hashtag' href='$url[0]' target='_blank'>$out</a> ", $body);
					} else {
								 // if no urls in the body just return the body
								 $body = $body;
					}
				}

				//check if user who posted, has their account closed
				$addedByObj = new User($this->db, $addedBy);
				if ($addedByObj->isClosed()) {
					continue;
				}

					if ($numIterations++ < $start) {
						continue;
					}

					// once 10 posts have been loaded, break
					if ($count > $limit) {
						break;
					} else {
						$count++;
					}

					if ($loggedInUser == $addedBy) {
						$otherUserMenuOptions = "";
						$loggedInUserMenuOptions = "<li style='cursor: pointer;'><a data-toggle='modal' data-target='#post_form' onClick='javascript:edit$postId()'>Edit Post</a></li>

																				<li style='cursor: pointer;'><a id='post$postId'>Delete Post</a></li>";

					} else {
						$otherUserMenuOptions = "<li data-toggle='modal' data-target='#post_form' 			id='dropdown-post$postId'><a>Say Something to @$addedBy</a></li>";


						$loggedInUserMenuOptions = "";
					}

					//get post user info

					$userDetails = $this->db->query ("
						SELECT first_name, last_name, profile_pic, verified, username, private_account FROM users WHERE username = '$addedBy'
					");

					$userRow = $userDetails->fetch_assoc();
					$firstName = $userRow['first_name'];
					$lastName = $userRow['last_name'];
					$profilePic = $userRow['profile_pic'];
					$username = $userRow['username'];

					if ($userRow['private_account'] == 'yes' || $userRow['username'] == $loggedInUser) {
						$privateAccount = "private-account";
					} else {
						$privateAccount = "";
					}

					//check to see if user is verified
					if ($userRow['verified'] == 'yes') {
						$postVerifiedCheck = "<img class='verified' src='" . $this->filePath . "img/icons/verified.svg' title='Verified user'>";
					} else {
						$postVerifiedCheck = "";
					}

					//======================================================================
					//
					//	 check to see if post was shared
					//
					//======================================================================

						$totalShares = "";

						$getUserShared = $this->db->query("
							SELECT * FROM posts WHERE post_unique_id =  '$uniqueId' AND shared_by = '$loggedInUser'
						");
						$userShares = $getUserShared->num_rows;

						$shareMenuOption = "";
						if ($userShares > 0) {
							$sharedIconClass = 'user-shared';
							$shareValue = "Unshare";
							if ($loggedInUser != $addedBy) {
								$shareMenuOption = "<li class='share-menu-option share$postId'><a>Unshare this post</a></li>";
							}
						} else {
							$sharedIconClass= '';
							$shareValue = "Share";
							if ($loggedInUser != $addedBy) {
								$shareMenuOption = "<li class='share-menu-option share$postId'><a>Share this post</a></li>";
							}
						}

						if(!isset($totalShares)) {
							$totalShares = '';
						}

						$getSharedPost = $this->db->query("
							SELECT * FROM posts WHERE post_unique_id = '$uniqueId' AND shared_by = 'none'
						");
						$sharedPost = $getSharedPost->fetch_assoc();

						$getSharedUserName = $this->db->query("
							SELECT first_name, last_name FROM users WHERE username = '$sharedBy'
						");
						$sharedUser = $getSharedUserName->fetch_assoc();


						if (!empty($sharedBy) && $sharedBy != 'none') {


							$sharedDiv = "<div class='shared-div'>
										<a href='$sharedBy' class='post-name'>Shared by&nbsp;<span>" . $sharedUser['first_name'] . " " . $sharedUser['last_name'] . "</span></a>
									 </div>";

							$sharedClass = "shared";

						} else {

							$sharedDiv = "";
							$sharedClass = "";

						}

						$addedBy = $sharedPost['added_by'];
						$dateTime = $sharedPost['date_added'];
						$uniqueId = $sharedPost['post_unique_id'];
						$sharedUserDetails = $this->db->query ("
							SELECT first_name, last_name, profile_pic, verified, username FROM users WHERE username = '$addedBy'
						");

						$sharedRow = $sharedUserDetails->fetch_assoc();
						$firstName = $sharedRow['first_name'];
						$lastName = $sharedRow['last_name'];
						$profilePic = $sharedRow['profile_pic'];
						$username = $sharedRow['username'];

						$checkShares = $this->db->query("
							SELECT * FROM posts WHERE post_unique_id = '$uniqueId' AND shared_by <> 'none'
						");
						$totalShares = $checkShares->num_rows;

						if ($totalShares > 0) {
							$totalShares = $totalShares;
						} else if ($totalShares == 0) {
							$totalShares = "";
						}

						//timezone
						//set times for posts
						$dateTimeNow = date("Y-m-d H:i:s");
						$postStartDate = new DateTime($dateTime); //time of post
						$endDate = new DateTime($dateTimeNow); //current time
						$postInterval = $postStartDate->diff($endDate); //difference between dates
						$hourDiff = $postInterval->h + ($postInterval->d*24);
						$newtime = strtotime($dateTime);
						if ($hourDiff > 24) {
							$postTimeMessage = date("F j, Y", $newtime);
						} else if ($postInterval->h >=1) {
							if ($postInterval->h == 1) {
								$postTimeMessage = $postInterval->h . " hr";
							} else {
								$postTimeMessage = $postInterval->h . " hr";
							}
						} else if ($postInterval->i >=1) {
							if ($postInterval->i == 1) {
								$postTimeMessage = $postInterval->i . " min";
							} else {
								$postTimeMessage = $postInterval->i . " min";
							}
						} else {
							if ($postInterval->s < 30) {
								$postTimeMessage = "Just now";
							} else {
								$postTimeMessage = $postInterval->s . " sec";
							}
						}

					//======================================================================
					//
					//	check for comments
					//
					//======================================================================

						$commentsCheck = $this->db->query("
							SELECT * FROM comments WHERE post_unique_id = '$uniqueId'
						");
						$commentsCheckNum = $commentsCheck->num_rows;

						$commentsBlock = "";
						$getComments = $this->db->query("
							SELECT * FROM comments WHERE post_unique_id = '$uniqueId' ORDER BY id ASC
						");
						$commentCount = $getComments->num_rows;

						if ($commentCount > 0) {
							$commentCount = $commentCount;
						} else {
							$commentCount = "";
						}

						if ($commentCount != 0) {

							while ($comment = $getComments->fetch_assoc()) {
								$commentBody = "<p>" . preg_replace('#(\\\r|\\\r\\\n|\\\n)#', '</p><p>', $comment['post_body']) . "</p>";
								$commentBody = stripcslashes($commentBody);
								$postedTo = $comment['posted_to'];
								$postedBy = $comment['posted_by'];
								$dateAdded = $comment['date_added'];
								$removed = $comment['removed'];

								$getCommentorInfo = $this->db->query("
									SELECT first_name, last_name, username, profile_pic, verified FROM users WHERE username = '$postedBy'
								");
								$commentorInfo = $getCommentorInfo->fetch_assoc();
								$commentUser = $commentorInfo['first_name'] . " " . $commentorInfo['last_name'];
								$commentUsername = $commentorInfo['username'];

								if ($commentorInfo['verified'] == 'yes') {
									$commentVerifiedPic = $this->filePath . "img/icons/verified.svg";
									$commmentUserVerified = "verified";
								} else {
									$commentVerifiedPic = "";
									$commmentUserVerified = "";
								}

								//set times for comments
								//timezone
								$commentStartDate = new DateTime($dateAdded); //time of comment
								$commentInterval = $commentStartDate->diff($endDate); //difference between dates
								$commentHourDiff = $commentInterval->h + ($commentInterval->d*24);
								$newtime = strtotime($dateAdded);

								if ($commentHourDiff >= 24) {
									$commentTimeMessage = date("F j, Y", $newtime);
								} else if ($commentInterval->h >=1) {
									if ($commentInterval->h == 1) {
										$commentTimeMessage = $commentInterval->h . " hr";
									} else {
										$commentTimeMessage = $commentInterval->h . " hr";
									}
								} else if ($commentInterval->i >=1) {
									if ($commentInterval->i == 1) {
										$commentTimeMessage = $commentInterval->i . " min";
									} else {
										$commentTimeMessage = $commentInterval->i . " min";
									}
								} else {
									if ($commentInterval->s < 30) {
										$commentTimeMessage = "Just now";
									} else {
										$commentTimeMessage = $commentInterval->s . " sec";
									}
								}

								$commentsBlock .= "
													<div class='full-comment'>

														<div>

															<a href='" . $this->filePath . "$commentUsername'>
																<img src='" . $this->filePath . $commentorInfo['profile_pic'] . "' class='comment-profile-pic' title='$commentUser'>
															</a>


														</div>

														<div class='comment-section'>
															<div class='comment-user-name'>
																<a href='" . $this->filePath . "$commentUsername' class='post-name'><span>$commentUser</span><img class='$commmentUserVerified' src='" . "$commentVerifiedPic'></a><span class='post-username'>@$commentUsername</span>
															</div
															<span class='comment-time'>$commentTimeMessage</span>
															<div class='comment-body'>$commentBody</div>
														</div>
													</div>
												";

							}

						}//end of comments check

					//======================================================================
					//
					//	check for previous likes
					//
					//======================================================================


						$checkLikes = $this->db->query("
							SELECT * FROM likes WHERE username = '$loggedInUser' AND post_unique_id = '$uniqueId'
						");
						$userLikes = $checkLikes->num_rows;

						if ($userLikes > 0) {
							$likesName = "unlike_button";
							$likesValue = "Unlike";
							$userLikes = $userLikes;
							$likeFill = 'active';
						} else {
							$likesName = "like_button";
							$likesValue = "Like";
							$userLikes = "";
							$likeFill = 'inactive';
						}

						$checkLikes2 = $this->db->query("
							SELECT * FROM likes WHERE post_unique_id = '$uniqueId'
						");
						$totalLikes = $checkLikes2->num_rows;

						if ($totalLikes > 0) {
							$totalLikes = $totalLikes;
						} else {
							$totalLikes = "";
						} // end of likes


						if ($imagePath != "") {
							$imageDiv = "<div class='postedImage'>
														<a class='posted-image$postId' href='$imagePath'><img src='$imagePath'></a>
													</div>";
						} else {
							$imageDiv = "";
						}

					$postBlock[] = "
									<div class='full-post-outer $sharedClass' id='full-post-outer$postId'>
										$sharedDiv

										<div class='full-post' id='full-post$postId'>

											<div class='status-post'>

												<div class='body-content'>

													<div class='posted-by' style='color: #acacac;'>

														<div class='post-profile-pic'>
															<a href='" . $this->filePath . "$addedBy'><img src='" . $this->filePath . "$profilePic' alt='Profile Picture for $firstName $lastName'></a>
														</div>

														<div class='posted-by-info'>
															<div class='person-details'>
																<a href='" . $this->filePath . "$addedBy' class='post-name'><span>$firstName $lastName</span>$postVerifiedCheck</a><span class='post-username'id='post-username$postId'>@$username</span>
															</div>
															<span>$postTimeMessage</span>
														</div>

														<div class='delete-button btn-group'>
															<button type='button' data-toggle='dropdown' aria-haspopup='true' aria-expanded='false'>
																<img class='delete_button' src='" . $this->filePath . "img/icons/dot-menu.svg' alt='Post Menu'>
															</button>
															<ul class='dropdown-menu'>
																$otherUserMenuOptions
																$shareMenuOption
																$loggedInUserMenuOptions
															</ul>
														</div>
													</div>

													<div id='post_body'>
													$body

													$youtubeVideo
													$imageDiv
													</div>

												</div>

											</div>

											<div class='newsfeed-post-options'>
												<div class='comment-button newsfeed-buttons' title='Comment on this post.' onClick='javascript:toggle$postId()'>
													<div>
														<svg id='comments$postId' class='comment-svg post-button' mlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink' viewBox='0 0 505.7 512'>
															<path d='M373.1,511c-12.6,0-25.3-2.3-37.8-6.8c-25.4-9.2-45.7-23.4-60.1-42.3l-0.3-0.4h-0.6
																c-7.8,0.6-14.8,0.9-21.5,0.9c-67.3,0-130.5-24-178.1-67.6C27.2,351.2,1,293.3,1,231.7S27.2,112.2,74.7,68.6
																C122.3,25,185.6,1,252.8,1c67.3,0,130.5,24,178.1,67.6c47.5,43.6,73.7,101.5,73.7,163.1c0,85.4-51.2,163.4-133.6,203.6l-0.9,0.4
																l0.3,0.9c3.4,9.2,7.5,18.5,12.3,27.6c10.5,20.2,33.2,30.8,40,33.6C417.3,501.2,399,511,373.1,511L373.1,511z'/>
															<path d='M252.8,2.1c33.9,0,66.7,6.1,97.7,18.1c29.9,11.6,56.7,28.1,79.7,49.2s41.1,45.6,53.7,73
																c13.1,28.3,19.7,58.3,19.7,89.3c0,42.4-12.7,83.8-36.8,119.8c-23.4,35-56.7,63.7-96.2,83l-1.7,0.8l0.7,1.8
																c3.4,9.2,7.6,18.5,12.3,27.7c9.9,19.1,30,29.6,38.6,33.3c-7,3.9-24.1,11.9-47.3,11.9c-12.5,0-25-2.3-37.4-6.8
																c-25.3-9.2-45.3-23.3-59.7-41.9l-0.7-0.9l-1.1,0.1c-7.8,0.6-14.8,0.9-21.4,0.9c-33.9,0-66.7-6.1-97.7-18.1
																c-29.9-11.6-56.7-28.1-79.7-49.3c-23-21.1-41.1-45.6-53.7-73C8.7,292.7,2.1,262.7,2.1,231.7s6.6-61,19.7-89.3
																c12.6-27.3,30.7-51.9,53.7-73s49.8-37.7,79.7-49.2C186.1,8.2,219,2.1,252.8,2.1 M252.8,0C113.2,0,0,103.7,0,231.7
																c0,127.9,113.2,231.7,252.8,231.7c7.3,0,14.4-0.3,21.5-0.9c12.4,16.1,31.3,32,60.6,42.6c13.9,5,26.8,6.9,38.2,6.9
																c31.7,0,52-14.3,52-14.3s-29-10.3-41.3-33.9c-5.1-9.9-9.1-19.1-12.2-27.5c79.8-39,134.1-116,134.1-204.6
																C505.7,103.7,392.5,0,252.8,0L252.8,0z'/>
														</svg>
														<span class='post-options-nums' id='commentsSpan$postId'>$commentCount</span>
													</div>

												</div>
												<div class='newsfeed-buttons' title='Like this post.'>
												<form action='" . $this->filePath . "inc/handlers/likes.php?post_id=$postId&unique_id=$uniqueId' method='POST' class='like-button' id='likes$postId'>
													<input type='hidden' class='likesBody$postId $likesValue' name='post_body' value='$likesValue'>
													<button type='submit' name='like_button'>
														<div class='like-div'>

															<svg id='like$postId' class='like-svg post-button $likeFill' version='1.1' xmlns='http://www.w3.org/2000/svg'viewBox='0 0 1024 1024'>
															<path d='M513.4,141c0,0,277.1-171.5,441.1,60.5c0,0,113.2,181-19.3,387.3c0,0-104.6,197.2-421.7,363.5
																c0,0-287.5-143.6-411.2-347.4c0,0-140.3-188.7-36.2-392.1C66.1,212.8,187-34.5,513.4,141z'/>
															</svg>

															<span class='post-options-nums' id='likesSpan$postId'>$totalLikes</span>
														</div>
													</button>
												</form>
												</div>

												<div class='share-button newsfeed-buttons $privateAccount $sharedIconClass' title='Share this post with your friends.'>
													<form action='" . $this->filePath . "inc/handlers/share.php?post_id=$postId&unique_id=$uniqueId' method='POST' class='share-button' id='share$postId'>
														<input type='hidden' class='shareBody$postId' name='post_body' value='$shareValue'>
														<button type='submit' name='share_button' class='share$postId'>
															<div>
															<svg class='share-svg post-button share-button$uniqueId' xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink'  viewBox='0 0 20 20'>
																<path d='M19.7,8.2l-6.6-6C12.6,1.7,12,2.2,12,3v3C7.3,6,3.3,8.9,1.4,12.8c-0.7,1.3-1.1,2.7-1.4,4.1
																c-0.2,1,1.3,1.5,1.9,0.6C4.1,14,7.8,11.7,12,11.7V15c0,0.8,0.6,1.3,1.1,0.8l6.6-6C20.1,9.4,20.1,8.6,19.7,8.2z'/>
															</svg>
															<span class='post-options-nums' id='shareSpan$uniqueId'>$totalShares</span>
														</div>
														</button>
													</form>
												</div>
											</div>

											<div class='post-comments' id='toggle_comment$postId' style='display: none;'>
												<div class='post-comment-input'>
													<form class='comment-form comment-form$postId' action='" . $this->filePath . "inc/handlers/comments.php?post_id=$postId&unique_id=$uniqueId' method='POST'>
														<textarea class='autoExpand' rows='1' data-min-rows='1' id='post_comment_body$postId' name='post_body'  placeholder='Say something...''></textarea>
														<input type='submit' name='post_comment$postId' value='Send'>
													</form>
												</div>
												<div id='toggle_comments$postId'>
													$commentsBlock
												</div>
											</div>

										</div>

									</div>
								";

					?>

					<div>
					<script>
						$('.comment-form<?php echo $postId; ?>').submit(function(e) {

							var form = $('.comment-form<?php echo $postId; ?>');
							var formData = $(this).serialize();
							var postBody = $('#post_comment_body<?php echo $postId; ?>');
							var commentCountSpan =  $('#commentsSpan<?php echo $postId; ?>');
							if (!commentCount) {
								var commentCount = "<?php echo $commentCount; ?>";
							}
							var fullName = "<?php echo $userFullname; ?>";
							var username = "<?php echo $_SESSION['username']; ?>";
							var profilePic = "<?php echo $userProfilePic; ?>";
							var verifiedClass = "<?php echo $loggedInUserVerified; ?>";
							var verifiedPic = "<?php echo $loggedInUserVerifiedPic; ?>";
							var filePath = "<?php echo $this->filePath ?>";

							e.preventDefault();
							// Serialize the form data.

							// Submit the form using AJAX.
							$.ajax({
								type: 'POST',
								url: $(form).attr('action'),
								data: formData
							})
							.done(function(response) {
								if ($(postBody).val()) {
									$('#toggle_comment<?php echo $postId; ?>').append("<div class='full-comment' style='padding: 10px 0;'><a href='" + username + "'><img src='" + filePath + profilePic + "' class='comment-profile-pic'></a><div class='comment-section'><div><a href='" + username + "'><span>" + fullName + "</span><img class='" + verifiedClass + "' src='" + verifiedPic + "'></a><span class='post-username'> @" + username + "</span></div><span class='comment-time'>Just Now</span><div class='comment-body'>" + postBody.val() + "</div></div></div>");
									// Clear the form.
									$('#post_comment_body<?php echo $postId; ?>').val('');
									commentCount++;
									$(commentCountSpan).text(' ' + commentCount);
									$('#toggle_comment<?php echo $postId; ?>').css('display',' block');
								} else {
									alert("Uh oh! Comment field is empty!");
								}

							})
							.fail(function(data) {

							});

						});
						</script>

						<script>
						$('#likes<?php echo $postId; ?>').submit(function(e) {

							var form = $('#likes<?php echo $postId; ?>');
							var likesCountSpan =  $('#likesSpan<?php echo $postId; ?>');
							var totalLikes = $('#likesSpan<?php echo $postId; ?>').text();
							totalLikes = parseInt(totalLikes) || 0;
							var likesValue = $('.likesBody<?php echo $postId; ?>').val();

							e.preventDefault();
							// Submit the form using AJAX.
							$.ajax({
								type: 'POST',
								url: $(form).attr('action'),
								data: "likesValue=" + likesValue,
								success: function(data) {
									if (likesValue == 'Like') {
										totalLikes = totalLikes + 1;
										$('#like<?php echo $postId; ?>').css('fill', '#ff4c4c');
										$('.likesBody<?php echo $postId; ?>').removeClass('Like');
										$('.likesBody<?php echo $postId; ?>').addClass('Unlike');
										$('.likesBody<?php echo $postId; ?>').val('Unlike');
									} else if (likesValue == 'Unlike') {
										totalLikes = totalLikes - 1;
										$('#like<?php echo $postId; ?>').css('fill', '#fafafa');
										$('.likesBody<?php echo $postId; ?>').removeClass('Unlike');
										$('.likesBody<?php echo $postId; ?>').addClass('Like');
										$('.likesBody<?php echo $postId; ?>').val('Like');
									}
									$(likesCountSpan).text(' ' + totalLikes);
								}

							});

						});
						</script>

						<script>
						$('.share<?php echo $postId; ?>').click(function(e) {

							var form = $('#share<?php echo $postId; ?>');
							var shared_by = $('<?php echo $username; ?>');
							var shareValue = $('.shareBody<?php echo $postId; ?>').val();
							var shareCountSpan =  $('#shareSpan<?php echo $uniqueId; ?>');
							var totalShares = $('#shareSpan<?php echo $postId; ?>').text();
							totalShares = parseInt(totalShares) || 0;
							var shareMessage = "";
							if (shareValue == 'Share') {
								shareMessage = "You are about to share this post.";
							} else if (shareValue == 'Unshare') {
								shareMessage = "You are about to unshare this post.";
							}

							e.preventDefault();
							bootbox.confirm(shareMessage, function(result) {

								if (result) {
										// Submit the form using AJAX.
									$.ajax({
										type: 'POST',
										url: $(form).attr('action'),
										data: "shared_by=" + shared_by + "&shareValue=" + shareValue,

										success: function(data) {
											if (shareValue == 'Share') {
												totalShares = totalShares + 1;
												$('.share-button<?php echo $uniqueId; ?>').css('fill', '#59b063');
												$('.shareBody<?php echo $postId; ?>').val('Unshare');
											} else if (shareValue == 'Unshare') {
												// totalShares = totalShares - 1;
												// i need to figure out a good way to remove the shared post when another post with the same unique_id is clicked to uns
											}
											$(shareCountSpan).text(' ' + totalShares);
										}

									});
								}

							});

						});
						</script>

						<script>
						var $overlay = $("<div id=\"image-overlay\"></div>");
						var $image = $("<img>");
						var $caption = $("<p></p>");

						$overlay.append($image);
						$overlay.append($caption);
						$("body").append($overlay);

						// Capture the click event on a link to an image
						$(".posted-image<?php echo $postId; ?>").click(function(event) {

						  event.preventDefault();
						  var imageLocation = $(this).attr("href");
						  //  Update overlay with the image linked in the link
						  $image.attr("src", imageLocation);

						  //  Show the overlay
						  $overlay.fadeIn("fast");
							$overlay.css("display", "flex");

						  //  Get child's alt attribute and set caption
						  var captionText = $(this).children("img").attr("alt");
						  $caption.text(captionText);
						});

						// When overlay is clicked
						$overlay.click(function() {
						  // Hide overlay
						  $overlay.fadeOut();
						});
						</script>

						<script>
						$('#sharedbyuser<?php echo $postId; ?>').submit(function(e) {

							e.preventDefault();

						});
						</script>

						 <script>

							$(document).ready(function() {

								$('#post<?php echo $postId; ?>').on('click', function() {
									bootbox.confirm("Are you sure you want to delete this post? ", function(result) {
										$.post("inc/handlers/delete-post.php?post_id=<?php echo $postId; ?>", {result: result});

										if (result) {
											$('#full-post<?php echo $postId; ?>').slideUp("slow");
										}
									});
								});

							});

						</script>

						<script>
						function edit<?php echo $postId; ?>() {

							var postId = "<?php echo $postId; ?>";
							var postBody = "<?php echo $editPostBody; ?>";
							var postImage = "<?php echo $imagePath; ?>";

							$('#postEdit').val("<?php echo $postId; ?>");
							$('.site-post textarea').val(postBody);
							$('.image-upload').hide();
							$('.modal-footer').css("justify-content", "flex-end");

							if (postImage !== "<?php echo $this->filePath; ?>" && postImage !== "") {

								$('#file-img').css("display", "flex");
								$('#file-img-close').hide();
								$('#file-img img').attr('src', postImage);

							}

							$('#youtube-video-edit').append("<?php echo $youtubeVideo; ?>");
							$('#submit_post').text("Edit");

						}
						</script>

						<script>
							function toggle<?php echo $postId; ?>() {
								var target = $(event.target);
								if (!target.is('a')) {

									if (!target.is('a')) {

										$('#toggle_comment<?php echo $postId; ?>').slideToggle("fast");

									}
								}
							}
						</script>

						<script>
						$('#dropdown-post<?php echo $postId; ?>').click(function() {
							var username = $('#post-username<?php echo $postId; ?>').text();
							$('#post_form textarea').text(username + ' ');
						});
						</script>
					</div>

				<?php

			} //end while loop

			if ($count > $limit) {
				$postBlock[] = "<input type='hidden' class='next-page' value='" . ($page + 1) . "'>
						<input type='hidden' class='no-more-posts' value='false'>
						<div class='loading'>
							<h5>Show More Posts</h5>
						</div>";
			?>
			<script>
			$(document).ready(function() {
				$('.loading').on('click', function () {
					var page = $('.posts-area').find('.next-page').val();
					var noMorePosts = $('.posts-area').find('.no-more-posts').val();
					var ajaxReq = $.ajax ({
						url: "inc/ajax/ajax-load-posts.php",
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
				});
			});
			</script>
			<?php
			} else {
				$postBlock[] = "<input type='hidden' class='no-more-posts' value='true'><p class='no-more-posts'>Back to Top</p>";
			?>

			<script>
			$(document).ready(function() {
				$(".no-more-posts").click(function() {
						$("html, body").animate({ scrollTop: 0 }, 400);
						return false;
				});
			});
			</script>

			<?php
			}

		}

		foreach ($postBlock as $block) {
			echo $block;
		}
	}

}
