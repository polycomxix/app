<?php
	session_start();
	$root = $_SERVER['DOCUMENT_ROOT']."/app";

	include_once("config.php");
	include_once($root."/php/connect.php");
	include_once("facebook_class.php");

	$gpid = isset($_COOKIE['pid']) ? $_COOKIE['pid'] : null;

	
	if(isset($_SESSION['access_token']))
	{
		$conn = CreateConnection();
		$access_token = $_SESSION['access_token'];
		$fbid = GetFacebookUserProfile($access_token);
		CloseConnection($conn);
		//echo "FBID:".$fbid."<br/>";
		if($fbid != false)
		{
			//$result = GetFacebookQuizResult($access_token, $fbid);
			$r = GetFacebookQuizResult2($access_token, $fbid);
			//print_r($result);
			//echo "RESULT: <hr/><br/>";
			if($r){
			$result =  array(
								"avg"		=> $r['avg'],
								"time"		=> $r['time'],
								"active"	=> $r['active'],
								"success"	=> true
							);
			}
			else
				$result = array(
									"success"	=> false
								);
			
			echo json_encode($result);
		}
		
	}
	else
	{
		header('Location: ../facebook/fbcallback.php');
	}

	function GetFacebookUserProfile($access_token)
	{
		global $fb, $conn, $gpid;
		$IsSuccess = true;

		$response = $fb->get('/me?fields=id,name,gender,birthday,location,friends.summary(true)', $access_token);
		$user = $response->getGraphNode();
		//print_r($user);
		$fuser = new Profile();
		$fuser->id 			= $user->getField('id');
		$fuser->name 		= $user->getField('name');
		$fuser->gender  	= $user->getField('gender');
		$fuser->location 	= $user->getField('location') != null ? $user->getField('location')->getField('name') : null;
		$fuser->birthyear	= $user->getField('birthday') != null ? $user->getField('birthday')->format('Y') : null;
		$fuser->total_friend= $user->getField('friends')->getTotalCount();
		$fuser->token 		= $access_token;

		//SaveFacebookUserProfile
		mysqli_query($conn,"START TRANSACTION");
		try{
			//$gpid = 93;
			$result = IsCurrentUser('tb_user','fb_id',$fuser->id);
			$hasFacebookId = ($result->num_rows > 0) ? true : false;
			if(!$hasFacebookId)
			{
				if($gpid!=null)//Current user
				{
					//Update tb_user
					$pid =$gpid;
					$row = null;
					$result = IsCurrentUser('tb_user', 'pid', $pid);
					if ($result->num_rows > 0) {
					    // output data of each row
					    $row = $result->fetch_assoc();
					}
					$gender = $row['gender']== null ? $fuser->gender : $row['gender'];
					$birthday = $row['birthday']== null ? $fuser->birthyear : $row['birthday'];
					$country = $row['country']== null ? $fuser->location : $row['country'];
					$fb_id = $fuser->id;
					
					//Update tb_user and Create tb_facebook_profile
					if(UpdateUserProfile($pid, $fb_id, $gender, $birthday, $country, $access_token))
					{
						//echo "Update User Profile";
						if(!CreateFacebookUserProfile($fuser,$pid))
							$IsSuccess = false;
						//echo $IsSuccess;
					}
				}
				else // new user
				{
					//Create new user in tb_user
					$pid = CreateUserProfile($fuser);
					//echo "PID:".$pid;
					if($pid!=false)
					{
						$gpid=$pid; 
						if(!CreateFacebookUserProfile($fuser,$pid))
							$IsSuccess = false;
					} 
				}
			}
			else // Update tb_facebook_profile
			{
				if(!UpdateFacebookUserProfile($fuser))
					$IsSuccess = false;
			}
			
			mysqli_query($conn,"COMMIT");
		}catch(Exception $e){
			mysqli_query($conn,"ROLLBACK");
		}
		return $IsSuccess==true ? $fuser->id : $IsSuccess;
	}
	function GetFacebookQuizResult2($access_token, $fbid)
	{

		global $fb, $gpid, $since_date, $CURRENT_DATE;
		$total_post=0;
		$period	= array(0,0,0,0,0,0,0,0);


		//$param = $fbid.'/feed?fields=id,created_time,type,from,likes.limit(200),message, status_type,comments{created_time,id,message,from,user_likes,comment_count,attachment}&limit=10';
		$param	 = $fbid.'/feed?fields=id,created_time,status_type,type,from&limit=200';

		//echo $param."<br/>";
		$response = $fb->get($param, $access_token);
		$feed = $response->getGraphEdge();
		//echo "feed:".count($feed);
		if(count($feed)==0)
			return true; 

		$i=1;
		$break = false;
		try{
			do{
				//echo $i."<br/>";
				if($i!=1)//next loop
				{	
					$feed = $fb->next($feed);
					if(count($feed)==0)
					{
						$break = true;
						break;
					}
				}

				foreach($feed as $f)
				{
					//fwrite($log_file, "Post ID ".$p->getField('id')."\r\n");

					$created_at = date_format($f->getField('created_time'),'Y-m-d H:i:s');

					if(empty($f) || $created_at<$since_date)
					{
						$break = true;
						break;
					}
					$total_post++;
					//$p = date( 'H', strtotime($f->created_at));
					$p = date_format($f->getField('created_time'),'H');
					//echo $f->getField('id')."__".$created_at."----".$p."<br/>";

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
	
				}//end for loop feed
				$i++;

				if($break) //no data, then stop looping
					break;
			}while($i!=15);

			//echo "<br/> Total Tweet:".$total_tweet."<br/> Max:".array_search(max($period), $period);
			$days = round(abs(strtotime($CURRENT_DATE)-strtotime($since_date))/86400);
			$r['avg'] = number_format($total_post/$days,2);

			$hour= array_search(max($period), $period);
			$r['active'] = $hour>=18 ? 2 : 1;
			
			$start 	= ($hour*3).":00";
			$end 	= ($start+3)."00";

			$r['time'] = date('ga',strtotime($start))."-".date('ga',strtotime($end));
			//print_r($r);
			return $r;
		}catch (Exception $e){
			
			return false;
		}
	}
	function GetFacebookQuizResult($access_token, $fbid)
	{
		global $fb;
		$albums_id = "";
		$pic = $count= array();
		$count[0]=$count[1]=$count[2]=$count[3]=$count[4]=0;

		$param = 'me/albums';
		$response = $fb->get($param, $access_token);
		$albums = $response->getGraphEdge();
		//print_r($albums);
		$i=1;
		while($albums)
		{
			foreach($albums as $a)
			{
				//echo $i." name:".$a->getField('name')."<br/>";
				if($a->getField('name')=="Profile Pictures")
				{
					$albums_id =  $a->getField('id');
					break;
				}
				$i++;	
			}
			$albums = $fb->next($albums);
		}
		//echo "albums id:".$albums_id."<br/>";

		$param = $albums_id.'/photos?fields=picture,images,created_time,likes.limit(1000)&limit=200';
		$response = $fb->get($param, $access_token);
		$photo = $response->getGraphEdge();

		foreach($photo as $p)
		{
			$year = date_format($p->getField('created_time'),'Y');
			//echo $year."<br/><img src='".$p->getField('picture')."' alt='' /><br/>";
			

			switch ($year) {
				case '2015':
					if(count($p->getField('likes'))>$count[0])
					{
						$count[0] 	= count($p->getField('likes'));
						$pic[0] 	= $p->getField('picture');
						/*foreach($p->getField('images') as $img)
						{
							if($img->getField('width')==225 || $img->getField('height')==225)
								$pic[0] = $img->getField('source');
						}*/
					}
					break;
				case '2014':
					if(count($p->getField('likes'))>$count[1])
					{
						$count[1] 	= count($p->getField('likes'));
						$pic[1] 	= $p->getField('picture');
						/*foreach($p->getField('images') as $img)
						{
							if($img->getField('width')==225 || $img->getField('height')==225)
								$pic[1] = $img->getField('source');
						}*/
					}
					break;
				case '2013':
					if(count($p->getField('likes'))>$count[2])
					{
						$count[2] 	= count($p->getField('likes'));
						$pic[2] 	= $p->getField('picture');
						/*foreach($p->getField('images') as $img)
						{
							if($img->getField('width')==225 || $img->getField('height')==225)
								$pic[2] = $img->getField('source');
						}*/
					}
					break;
				case '2012':
					if(count($p->getField('likes'))>$count[3])
					{
						$count[3] 	= count($p->getField('likes'));
						$pic[3] 	= $p->getField('picture');
						/*foreach($p->getField('images') as $img)
						{
							if($img->getField('width')==225 || $img->getField('height')==225)
								$pic[3] = $img->getField('source');
						}*/
					}
					break;
				default:
					# code...
					break;
			}
			
		}
		//echo "famous pic id:".$pic[0]."count: ".$count[0]."<br/>";
		/*echo "<img src='".$pic[3]."' alt='' />";
		echo "<img src='".$pic[2]."' alt='' />";
		echo "<img src='".$pic[1]."' alt='' />";
		echo "<img src='".$pic[0]."' alt='' />";*/
		$result = array(
						"success"	=>	true,
						"content"	=>	array(
								array("2012", isset($pic[3]) ? urlencode($pic[3]) : null, isset($count[3]) ? $count[3] : null),
								array("2013", isset($pic[2]) ? urlencode($pic[2]) : null, isset($count[2]) ? $count[2] : null),
								array("2014", isset($pic[1]) ? urlencode($pic[1]) : null, isset($count[1]) ? $count[1] : null),
								array("2015", isset($pic[0]) ? urlencode($pic[0]) : null, isset($count[0]) ? $count[0] : null)
						)
		);
		return $result;
	}

	function IsCurrentUser($tb_name, $field, $val) //already has record in tb_user or tb_twitter profile
	{
		global $conn;
		$s = "SELECT * FROM ".$tb_name." WHERE ".$field." = '$val'";
		$result = $conn->query($s);

		return $result;
	}
	function CreateUserProfile($fuser)
	{
		global $conn;
		$sql = 	"INSERT INTO tb_user (fb_id, gender, birthday,country) ".
				"VALUES ('$fuser->id','$fuser->gender','$fuser->birthyear','$fuser->location')";
		if (!mysqli_query($conn, $sql)) {
						    //echo "Insert ID:".$pid."\r\n";
			echo "Error: " . $sql . "<br>" . mysqli_error($conn);
			return false;
		}
		else{
			return mysqli_insert_id($conn);
		}

	}
	function UpdateUserProfile($pid, $fb_id, $gender, $birthday, $country, $access_token)
	{
		global $conn, $CURRENT_DATE;
		$s = 	"UPDATE tb_user SET fb_id = '$fb_id', gender='$gender', birthday='$birthday', country='$country', updated_date= '$CURRENT_DATE'".
				"WHERE pid = '$pid'";
		if (!mysqli_query($conn, $s)) {
					    //echo "Insert ID:".$pid."\r\n";
			echo "Error: " . $s . "<br>" . mysqli_error($conn);
			return false;
		}
		return true;
	}
	function CreateFacebookUserProfile($fuser,$pid)
	{
		global $conn;
		$sql = 	"INSERT INTO tb_facebook_profile (user_id, pid, name, total_friend, access_token) ".
				"VALUES ('$fuser->id','$pid','$fuser->name','$fuser->total_friend', '$fuser->token')";
		if (!mysqli_query($conn, $sql)) {
							    //echo "Insert ID:".$pid."\r\n";
			echo "Error: " . $sql . "<br>" . mysqli_error($conn);
			return false;
		} 
		return true;
	}
	function UpdateFacebookUserProfile($fuser)
	{
		global $conn, $CURRENT_DATE;
		$sql = 	"UPDATE tb_facebook_profile SET name = '$fuser->name', total_friend='$fuser->total_friend', updated_date= '$CURRENT_DATE', access_token= '$fuser->token' ".
				"WHERE user_id = '$fuser->id'";
		if (!mysqli_query($conn, $sql)) {
					    //echo "Insert ID:".$pid."\r\n";
			echo "Error: " . $sql . "<br>" . mysqli_error($conn);
			return false;
		} 
		return true;
	}

	
?>