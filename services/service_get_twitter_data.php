<?php
	session_start();

	$root = "/var/www/html/app";
	//echo $root;
	include_once($root."/twitter/config.php");
	include_once($root."/php/connect.php");
	include_once($root."/twitter/twitter_class.php");
	require $root.'/twitter/autoload.php';
	use Abraham\TwitterOAuth\TwitterOAuth;

	/*------------Initial Variable-------------*/
	const MAX_PAGE = 16;

	/*------------/Initial Variable-------------*/
	$userlist = array();
	//Prepare twitter connection
	//$access_token = $_SESSION['access_token'];
	$conn = CreateConnection();
	$sql = "SELECT * FROM tb_twitter_profile WHERE status=0  limit 5";
	if($result = $conn->query($sql))
	{
		while($row = $result->fetch_assoc())
		{	
			array_push($userlist,$row);
			UpdateReserveUser($row['user_id'],1);//reserve
		}
	}
	CloseConnection($conn);

	/*$param = array("user_id"=>282918691);
	$user = $connection->get("users/show", $param);
	print_r($user);**/
	if(count($userlist)>0)
	{
		for($i=0; $i<count($userlist); $i++)
		{
			$time_start = microtime(true);

			$gpid = $userlist[$i]['pid'];
			$timezone_offset=$userlist[$i]['timezone_offset'];
			echo "gpid:".$gpid."<br/>";

			//Create log file
			$date = date_format(new DateTime("now", new DateTimeZone('Asia/Tokyo')),'Y-m-d_H-i-s');
			$filename = __DIR__."/log/twitter_".$date."_".$userlist[$i]['user_id'].".txt";
			$log_file = fopen($filename, "w") or die ("Unable to open file!");

			fwrite($log_file, "----Start Query----\r\n");

			//Start query
			$connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $userlist[$i]['token'] , $userlist[$i]['token_secret']);

			GetTwitterActivities($connection, $userlist[$i]['user_id'],$timezone_offset);
			GetTwitterFavorite($connection, $userlist[$i]['user_id'], $timezone_offset);

			//Update User
			UpdateReserveUser($userlist[$i]['user_id'],2);//finish

			fwrite($log_file, "----End Query-----\r\n");
			fwrite($log_file, "Total execution time in seconds: " . (microtime(true) - $time_start)."\r\n");
			fclose($log_file);
			echo 'Total execution time in seconds: ' . (microtime(true) - $time_start)."<br/>";
		}//end for $userlist
	}//end if $userlist


	function GetTwitterActivities($tconn, $userid, $timezone_offset)
	{
		global $log_file, $since_date;
		$tact = array();
		$param = array("count"=>200,"user_id"=>$userid,"page"=>null);//oyoeoyo polycomxix tangjaidee 282918691

		//Prevent incase update
		$since_id=GetLatestUserTweet($userid);
		if($since_id!=null) //update more tweet data
		{
			$param['since_id']=$since_id;
		}

		for($i=1; $i<=MAX_PAGE; $i++)//MAX_PAGE
		{
			
			$param['page'] = $i;
			try{
				$tweet = $tconn->get("statuses/user_timeline",$param);
			}catch (Exception $e){
				//mysqli_query($conn,"ROLLBACK");
				fwrite($log_file, $e."\r\n");
				$tweet=null;
			}
			

			if(empty($tweet))
				break;
			else
			{
				$break = false;
				$j = 1;
				foreach($tweet as $t)
				{
					$created_at = date( 'Y-m-d H:i:s', (strtotime($t->created_at)+$timezone_offset));
					if(empty($t) || $created_at<$since_date)
					{
						$break = true;
						break;
					}

					//define default parameter
					$action = "tweet"; $tweettype = "text";
					$attached_link = $reply_ref = $retweeted_id = $retweeted_created_at =null;

					
					if($t->retweeted){
						$action="retweeted";
						$retweeted_id = $t->retweeted_status->id_str;
						$retweeted_created_at=$t->retweeted_status->created_at;
					}
					else if($t->in_reply_to_user_id!=null){
						$action		="reply-user";
						$reply_ref 	= $t->in_reply_to_user_id;
					}
					else if($t->in_reply_to_status_id!=null){
						$action		="reply-tweet";
						$reply_ref 	= $t->in_reply_to_status_id;
					}
					else if($t->in_reply_to_screen_name!=null){
						$action		="reply-screenname";
						$reply_ref 	= $t->in_reply_to_screen_name;
					}

					
					if(isset($t->entities->media))
					{
						if(isset($t->entities->media->type))
						{
							$tweettype 		= $t->entities->media->type;
							$attached_link	= $t->entities->media->media_url;
						}
							
					}


					$tweetact = new Activities();

					$tweetact->user_id 				= $userid;
					$tweetact->tweet_id 			= $t->id_str;
					$tweetact->created_at 			= date( 'Y-m-d H:i:s', (strtotime($t->created_at)+$timezone_offset));
					$tweetact->action 				= $action;
					$tweetact->text 				= $t->text;
					$tweetact->in_reply_ref			= $reply_ref;
					$tweetact->retweeted_id			= $retweeted_id;
					$tweetact->retweeted_created_at = $retweeted_created_at!=null ? date( 'Y-m-d H:i:s', (strtotime($retweeted_created_at)+$timezone_offset)) : null;
					$tweetact->media 				= $tweettype;
					$tweetact->attached_link		= $attached_link;
					$tweetact->source 				= GetTextFromTag($t->source);
					$tweetact->no_user_mention		= count($t->entities->user_mentions);
					$tweetact->no_hashtags			= count($t->entities->hashtags);
					$tweetact->retweet_count		= $t->retweet_count;
					$tweetact->favorite_count		= $t->favorite_count;

					$tact[] = $tweetact;
					//print_r($tweetact);
					//echo "<br/><hr/><br/>";
					if($j%50==0)
					{
						SaveTweetActToDb($tact);
						unset($tact);
						$tact = array();
					}
					$j++;
				}

				if(count($tact)>0)
				{
						//echo "a <br/>";
					SaveTweetActToDb($tact);
					unset($tact);
					$tact = array();
				}
				if($break) //no data, then stop looping
					break;
			}//end if else condition
		}//end for loop
		fwrite($log_file, "Complete adding tweet\r\n");
	}

	function GetTwitterFavorite($tconn, $userid, $timezone_offset)
	{
		global $log_file, $since_date;

		$favlist = array();
		$param = array("count"=>200,"user_id"=>$userid,"page"=>null);

		//Prevent incase update
		$since_id=GetLatestUserFav($userid);
		if($since_id!=null) //update more tweet data
		{
			$param['since_id']=$since_id;
		}

		for($i=1; $i<=MAX_PAGE; $i++)//MAX_PAGE
		{
			
			$param['page'] = $i;
			try{
				$favorite = $tconn->get("favorites/list",$param);
			}catch (Exception $e){
				//mysqli_query($conn,"ROLLBACK");
				fwrite($log_file, $e."\r\n");
				$favorite=null;
			}
			

			if(empty($favorite))
				break;
			else
			{
				$break = false;
				$j = 1;
				foreach($favorite as $fav)
				{
					$created_at = date( 'Y-m-d H:i:s', (strtotime($fav->created_at)+$timezone_offset));

					if(empty($fav) || $created_at<$since_date)
					{
						$break = true;
						break;
					}

					//define default parameter
					$tweettype = "text";
					$attached_link =null;
					
					if(isset($fav->entities->media))
					{
						if(isset($fav->entities->media[0]->type))
						{
							$tweettype 		= $fav->entities->media[0]->type;
							$attached_link	= $fav->entities->media[0]->media_url;
						}
					}



					$favorite_list = new favorites();
					$favorite_list->user_id 		= $userid;
					$favorite_list->fav_id 			= $fav->id;
					$favorite_list->parent_id		= $fav->id_str;
					$favorite_list->created_at		= $created_at;
					$favorite_list->text 			= $fav->text;
					$favorite_list->media 			= $tweettype;
					$favorite_list->attached_link	= $attached_link;
					$favorite_list->source 			= GetTextFromTag($fav->source);
					$favorite_list->no_user_mention	= count($fav->entities->user_mentions);
					$favorite_list->no_hashtags		= count($fav->entities->hashtags);
					$favorite_list->retweet_count	= $fav->retweet_count;
					$favorite_list->favorite_count	= $fav->favorite_count;


					$favlist[] = $favorite_list;
					//print_r($favorite_list);
					//echo "<br/><hr/><br/>";
					if($j%50==0)
					{
						SaveFavToDb($favlist);
						unset($favlist);
						$favlist = array();
					}
					$j++;
				}

				if(count($favlist)>0)
				{
					SaveFavToDb($favlist);
					unset($favlist);
					$favlist = array();
				}
				if($break) //no data, then stop looping
					break;
			}//end if else condition
		}//end for loop
		fwrite($log_file, "Complete adding favorite\r\n");

	}

	function GetLatestUserTweet($userid)
	{
		$conn = CreateConnection();
		$sql = "SELECT * FROM tb_tweet WHERE user_id= $userid AND created_at = (SELECT MAX(`created_at`) FROM tb_tweet)";
		$result = $conn->query($sql);
		$r = null;
		if ($result->num_rows > 0) {
			// output data of each row
			$row = $result->fetch_assoc();
			//print_r($row);
			//return $row['tweet_id'];
			$r = $row['tweet_id'];
		}
		CloseConnection($conn);
		return $r;
	}
	function GetLatestUserFav($userid)
	{
		$conn = CreateConnection();
		$sql = "SELECT * FROM tb_tweet_fav WHERE user_id= $userid AND created_at = (SELECT MAX(`created_at`) FROM tb_tweet_fav)";
		$result = $conn->query($sql);
		$r = null;
		if ($result->num_rows > 0) {
			// output data of each row
			$row = $result->fetch_assoc();
			//print_r($row);
			//return $row['tweet_id'];
			$r = $row['fav_id'];
		}
		CloseConnection($conn);
		return $r;
	}

	function GetTextFromTag($data)
	{
		$regex = '#<\s*?a\b[^>]*>(.*?)</a\b[^>]*>#s';
		preg_match_all($regex, $data, $source);
		return $source[1][0];
	}

	function SaveTweetActToDb($tacts)
	{
		global $log_file, $CURRENT_DATE, $gpid;
		$pid = $gpid;

		$conn = CreateConnection();

		$sql = "INSERT IGNORE INTO tb_tweet (pid, user_id, tweet_id, created_at, action, message, in_reply_ref, retweeted_id, retweeted_created_at, media, attached_link, source, no_user_mention, no_hashtags, favorite_count, retweet_count) VALUES ";
		$i=1;
		foreach($tacts as $ts)
		{
			if($i!=1)
				$sql .=", ";

			$sql .= "('$pid','$ts->user_id', '$ts->tweet_id', '$ts->created_at', '$ts->action', '".mysqli_real_escape_string($conn, $ts->text)."', '$ts->in_reply_ref', '$ts->retweeted_id', '$ts->retweeted_created_at', '$ts->media', '$ts->attached_link', '$ts->source', '$ts->no_user_mention', '$ts->no_hashtags', '$ts->favorite_count', '$ts->retweet_count')";
			$i++;
		}
			//echo $sql;
		if (!mysqli_query($conn, $sql)) {
			//echo "Insert ID:".$pid."\r\n";
			//echo "Error: " . $sql . "<br>" . mysqli_error($conn);
			fwrite($log_file, "Error: " . $sql . "<br>" . mysqli_error($conn)."\r\n");
		} 

		CloseConnection($conn);
	}

	function SaveFavToDb($favs)
	{
		global $log_file, $CURRENT_DATE, $gpid;
		$pid = $gpid;

		$conn = CreateConnection();

		$sql = "INSERT IGNORE INTO tb_tweet_fav (pid, user_id, fav_id, parent_tweet_id, created_at, message, media, attached_link, source, no_user_mention, no_hashtags, favorite_count, retweet_count) VALUES ";
		$i=1;
		foreach($favs as $ts)
		{
			if($i!=1)
				$sql .=", ";

			$sql .= "('$pid','$ts->user_id', '$ts->fav_id', '$ts->parent_id', '$ts->created_at', '".mysqli_real_escape_string($conn, $ts->text)."', '$ts->media', '$ts->attached_link', '$ts->source', '$ts->no_user_mention', '$ts->no_hashtags', '$ts->favorite_count', '$ts->retweet_count')";
			//echo $sql;
			$i++;
		}
			//echo $sql;
		if (!mysqli_query($conn, $sql)) {
			//echo "Insert ID:".$pid."\r\n";
			//echo "Error: " . $sql . "<br>" . mysqli_error($conn);
			fwrite($log_file, "Error: " . $sql . "<br>" . mysqli_error($conn)."\r\n");
		} 

		CloseConnection($conn);
	}

	function UpdateReserveUser($user_id, $status)
	{
		$conn = CreateConnection();
		
		$sql = "UPDATE tb_twitter_profile SET status=$status WHERE user_id='$user_id'";
		$result =mysqli_query($conn, $sql) or die(mysqli_error($conn));

		CloseConnection($conn);
	}

?>