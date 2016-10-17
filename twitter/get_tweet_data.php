<?php
	session_start();
	include_once("config.php");
	include_once("../php/connect.php");
	include_once("twitter_class.php");
	require 'autoload.php';
	use Abraham\TwitterOAuth\TwitterOAuth;

	//error_reporting(0);

	/*------------Initial Variable-------------*/
	const MAX_PAGE = 2;

	//setcookie("pid", "", time() - 3600);
	$tuser = new Profile();
	$gpid = isset($_COOKIE['pid']) ? $_COOKIE['pid'] : null;
	$timezone_offset = null;

	/*------------/Initial Variable-------------*/

	//Prepare twitter connection
	$access_token = $_SESSION['access_token'];
	$connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $access_token['oauth_token'] , $access_token['oauth_token_secret']);

	/*--STEP 1--*/
	GetTwitterProfile($connection);
	if(isset($tuser->id))
	{
		$r = GetTwitterQuizResult($connection, $tuser->id);
		if($r){
			$result =  array(
								"name"		=> $tuser->screen_name,
								"avg"		=> $r['avg'],
								"time"		=> $r['time'],
								"active"	=> $r['active'],
								"success"	=> true,
								"expired"	=> $_COOKIE['pid']
							);
		}
		else
			$result = array(
								"success"	=> false
							);
		
		echo json_encode($result);
	}

	function GetTwitterQuizResult($tconn, $userid)
	{
		//define default parameter
		global $access_token, $since_date, $CURRENT_DATE, $timezone_offset;
		$total_tweet = 0;//
		$period	= array(0,0,0,0,0,0,0,0);
		//echo "timezone_offset:".$timezone_offset."<br/>";
		try{
			//$userid = 282918691;
			$param = array("count"=>200,"user_id"=>$userid,"page"=>null);//oyoeoyo polycomxix tangjaidee 282918691

			for($i=1; $i<=MAX_PAGE; $i++)//MAX_PAGE
			{
				
				$param['page'] = $i;
				try{
					$tweet = $tconn->get("statuses/user_timeline",$param);
				}catch (Exception $e){
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
						$created_at = date( 'Y-m-d H:i:s', (strtotime($t->created_at)+$timezone_offset));;
						if(empty($t) || $created_at<$since_date)
						{
							$break = true;
							break;
						}

						$total_tweet++;
						$p = date( 'H', (strtotime($t->created_at)+$timezone_offset));
						//echo "Date:".date( 'Y-m-d H:i:s', strtotime($t->created_at))." Hour:".$p."<br/>";

						switch ($p) {
							case ($p>=0 && $p<3)://0-3
								$period[0]++;
								break;
							case ($p>=3 && $p<6)://3-6
								$period[1]++;
								break;
							case ($p>=6 && $p<9)://6-9
								$period[2]++;
								break;
							case ($p>=9 && $p<12)://9-12
								$period[3]++;
								break;
							case ($p>=12 && $p<15)://12-15
								$period[4]++;
								break;
							case ($p>=15 && $p<18)://15-18
								$period[5]++;
								break;
							case ($p>=18 && $p<21)://18-21
								$period[6]++;
								break;
							case ($p>=21)://21
								$period[7]++;
								break;
						}
					}

					if($break) //no data, then stop looping
						break;
				}//end if else condition
			}//end for loop
			//print_r($period);
			//echo "<br/> Total Tweet:".$total_tweet."<br/> Max:".array_search(max($period), $period);
			$days = round(abs(strtotime($CURRENT_DATE)-strtotime($since_date))/86400);
			$r['avg'] = number_format($total_tweet/$days,2);

			$hour= array_search(max($period), $period);
			$r['active'] = $hour>=18 ? 2 : 1;
			
			$start 	= ($hour*3).":00";
			$end 	= ($start+3)."00";

			$r['time'] = date('ga',strtotime($start))."-".date('ga',strtotime($end));
			//print_r($r);
			return $r;
		}catch(Exception $e)
		{
			return false;
		}
	}
	
	function GetTwitterProfile($tconn)
	{
		global $tuser, $timezone_offset;
		$user = $tconn->get("account/verify_credentials");

		$tuser->id 			= $user->id;
		$tuser->screen_name = $user->screen_name;
		$tuser->follower	= $user->followers_count;
		$tuser->friend		= $user->friends_count;
		$tuser->favorite	= $user->favourites_count;
		$tuser->statuse		= $user->statuses_count;

		$tuser->joined_date	= date( 'Y-m-d H:i:s', strtotime($user->created_at));
		$tuser->location 	= $user->location != null ? $user->location : $user->time_zone;

		$tuser->timezone_offset = $user->utc_offset;
		$timezone_offset = $user->utc_offset;
		SaveTwitterProfile($tuser);
	}
	
	function GetLatestUserTweet($conn, $userid)
	{
		$conn = CreateConnection();
		$sql = "SELECT * FROM tb_tweet WHERE user_id= $userid AND created_at = (SELECT MAX(`created_at`) FROM tb_tweet)";
		$result = $conn->query($sql);
		$return = null;
		if ($result->num_rows > 0) {
			// output data of each row
			$row = $result->fetch_assoc();
			//print_r($row);
			$return= $row['tweet_id'];
		}
		
		CloseConnection($conn);
		return $return;
	}

	function GetTextFromTag($data)
	{
		$regex = '#<\s*?a\b[^>]*>(.*?)</a\b[^>]*>#s';
		preg_match_all($regex, $data, $source);
		return $source[1][0];
	}

	function IsCurrentUser($tb_name, $field, $val) //already has record in tb_user or tb_twitter profile
	{
		$conn = CreateConnection();
		$s = "SELECT * FROM ".$tb_name." WHERE ".$field." = '$val'";
		//echo "SQL:".$s."<br/>";
		$result = $conn->query($s);
		CloseConnection($conn);
		return $result;
	}
	function CreateUserProfile($twitter_id, $country)
	{
		$conn = CreateConnection();
		$s = 	"INSERT INTO tb_user (twitter_id, country, agreement) ".
		"VALUES ('$twitter_id','$country','1')";
		$return = false;
		if (!mysqli_query($conn, $s)) {
						    //echo "Insert ID:".$pid."\r\n";
			//echo "Error: " . $s . "<br>" . mysqli_error($conn);
			$return = false;
		}
		else{
			$return =  mysqli_insert_id($conn);
		} 
		CloseConnection($conn);
		return $return;
	}
	function UpdateUserProfile($pid, $twitter_id, $country)
	{
		global  $CURRENT_DATE;
		$conn = CreateConnection();
		$s = 	"UPDATE tb_user SET twitter_id = '$twitter_id', country='$country', updated_date= '$CURRENT_DATE' ".
		"WHERE pid = '$pid'";
		$return = true;
		if (!mysqli_query($conn, $s)) {
					    //echo "Insert ID:".$pid."\r\n";
			//echo "Error: " . $s . "<br>" . mysqli_error($conn);
			$return= false;
		}
		CloseConnection($conn);
		return $return;
	}
	function CreateTwitterProfile($user,$pid)
	{
		global $access_token;
		$token = $access_token['oauth_token'];
		$token_secret = $access_token['oauth_token_secret'];

		$conn = CreateConnection();
		$s = 	"INSERT INTO tb_twitter_profile (user_id, pid, screen_name, followers_count, friends_count, favourites_count, statuses_count, joined_date, token, token_secret, timezone_offset) ".
		"VALUES ('$user->id','$pid','$user->screen_name','$user->follower','$user->friend','$user->favorite','$user->statuse','$user->joined_date', '$token', '$token_secret', '$user->timezone_offset')";
		$return = true;
		if (!mysqli_query($conn, $s)) {
							    //echo "Insert ID:".$pid."\r\n";
			echo "Error: " . $s . "<br>" . mysqli_error($conn);
			$return = false;
		} 
		CloseConnection($conn);
		return $return;
	}
	function UpdateTwitterProfile($user)
	{
		global $CURRENT_DATE, $access_token;
		$token = $access_token['oauth_token'];
		$token_secret = $access_token['oauth_token_secret'];
		$return = null;
		$conn = CreateConnection();
		$s = "UPDATE tb_twitter_profile SET screen_name = '$user->screen_name', followers_count='$user->follower', friends_count='$user->friend',".
		"favourites_count='$user->favorite', statuses_count='$user->statuse', joined_date='$user->joined_date', updated_date= '$CURRENT_DATE', token='$token', token_secret='$token_secret', status=0, timezone_offset='$user->timezone_offset' ".
		"WHERE user_id = '$user->id'";
		
		if (!mysqli_query($conn, $s)) {
					    //echo "Insert ID:".$pid."\r\n";
			//echo "Error: " . $s . "<br>" . mysqli_error($conn);
			$return=false;
		}
		else{
			$s = "SELECT pid FROM tb_twitter_profile WHERE user_id = '$user->id';";
			$result = $conn->query($s);
			//print_r($result);
			if ($result->num_rows > 0) {
				// output data of each row
				$row = $result->fetch_assoc();
				$return= $row['pid'];
			}
			else
				$return= false;
			
		}
		CloseConnection($conn);

		return $return;
	}

	function SaveTwitterProfile($user)
	{
		global $cookie_expired, $CURRENT_DATE, $gpid;
		$IsSuccess = true;

		$conn = CreateConnection();
		mysqli_query($conn,"START TRANSACTION");
		try{
			//Check Twitter ID
			$result 		= IsCurrentUser('tb_user','twitter_id',$user->id);
			$hasTwitterId	= ($result->num_rows > 0) ? true : false;
			if(!$hasTwitterId)
			{
				//echo "GPID:".$gpid."<br/>";
				if($gpid!=null)//Current User
				{
					//Update tb_user
					//echo "<br/> Update user";
					$pid = $gpid;
					$country = null;
					$twitter_id = $user->id;

					$result = IsCurrentUser('tb_user', 'pid', $pid);
					if ($result->num_rows > 0) {
					    // output data of each row
						$row = $result->fetch_assoc();
						//echo "<br/>PID:".$row['pid'];
						$country 	= $row['country'];
					}
					$country = $country== null ? $user->location : $country;
					
					//Update tb_user and Create tb_twitter_profile
					if(UpdateUserProfile($pid, $twitter_id, $country))
					{
						if(!CreateTwitterProfile($user,$pid))
							$IsSuccess = false;
						else
							setcookie("pid",$gpid,$cookie_expired, "/");
					}
					
				}
				else //new user
				{
					//Create new user in tb_user
					//echo "<br/> New user";
					$pid = CreateUserProfile($user->id, $user->location);
					//echo "PID:".$pid;
					//Create new twitter profile in tb_twitter_profile
					//echo "PID:".$pid."<br/>";
					if($pid!=false)
					{
						$gpid=$pid; 
						if(!CreateTwitterProfile($user,$pid))
							$IsSuccess = false;
						else
							setcookie("pid",$gpid,$cookie_expired, "/");
					}
				}
			}
			else //Update tb_twitter_profile
			{
				$val = UpdateTwitterProfile($user);
				if(!$val)
				{
					$IsSuccess = false;
					
				}
				else{
					$gpid =$val;
					setcookie("pid",$gpid,$cookie_expired, "/");
				}
			}

			mysqli_query($conn,"COMMIT");
		}catch (Exception $e){
			mysqli_query($conn,"ROLLBACK");
		}
		CloseConnection($conn);
		return $IsSuccess;
	}

	?>