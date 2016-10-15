<?php 
	session_start();
	$root = $_SERVER['DOCUMENT_ROOT']."/app";

	include_once($root."/instagram/config.php");
	include_once($root."/instagram/ig_class.php");
	include_once($root."/php/connect.php");

	/*------------Initial Variable-------------*/
	const MAX_PAGE = 2;

	$userlist = array();
	$conn = CreateConnection();
	$sql = "SELECT * FROM tb_ig_profile WHERE status=0  limit 5";
	if($result = $conn->query($sql))
	{
		while($row = $result->fetch_assoc())
		{	
			array_push($userlist,$row);
			//UpdateReserveUser($row['user_id'],1);//reserve
		}
	}
	CloseConnection($conn);

	if(count($userlist)>0)
	{
		for($i=0; $i<count($userlist); $i++)
		{
			$time_start = microtime(true);

			$gpid = $userlist[$i]['pid'];
			echo "gpid:".$gpid."<br/>";
			//echo $userlist[$i]['access_token'];
			$instagram->setAccessToken($userlist[$i]['access_token']);
			//GetIGPost($instagram, $userlist[$i]['user_id']);
			//GetIGUserLikes($instagram, $userlist[$i]['user_id']);

			echo 'Total execution time in seconds: ' . (microtime(true) - $time_start)."<br/>";
		}
	}

	function GetIGPost($instagram, $user_id)
	{
		global $since_date;
		$break=false;
		$igpost = array();

		$response   = $instagram->getUserMedia($user_id,5);
		//print_r($response);
		$since=GetLatestPost($user_id);
		if($since!=null)
		{
			$since_date = $since;
		}
		for($i=1; $i<=MAX_PAGE; $i++)
		{
			if($i!=1)
			{
				try{
					$response = $instagram->pagination($response);
					if(count($response->data)==0)
					{
						$break = true;
						break;
					}
				}catch (Exception $e){
					//fwrite($log_file, $e."\r\n");
					$response=null;
					$break = true;
				}
			}
			$j=1;
			foreach ($response->data as $p) 
			{
				
				$created_at = date('Y-m-d H:i:s', $p->created_time);//date_format(date($p->created_time),'Y-m-d H:i:s');
				if(empty($p) || $created_at<=$since_date)
				{
					$break = true;
					break;
				}

				//Store post data
				$post = new post();
				$post->post_id 			= $p->id;
				$post->created_time		= date('Y-m-d H:i:s', $p->created_time);
				$post->caption 			= $p->caption !=null ? $p->caption->text : null;
				$post->type 			= $p->type;
				$post->likes_count 		= $p->likes->count;
				$post->comments_count 	= $p->comments->count;
				$post->img_url 			= $p->images->standard_resolution->url;
				$post->link_url			= $p->link;
				$post->filter_type 		= $p->filter;
				$post->userinphoto_count= count($p->users_in_photo);
				$post->tags 			= null;
				foreach($p->tags as $t)
				{
					$post->tags .= $t.",";
				}
				$igpost[] = $post;

				if($j%50==0)//SaveToDb
				{
					if(count($igpost)>0)//SaveToDb
					{
						SaveIGPost($igpost, $user_id);
						//fwrite($log_file, "Add ".count($fbpost)."post\r\n");
						unset($igpost);
						$igpost = array();
					}
				}
				$j++;
				//print_r($post);
				echo "<br/><hr/><br/>";
			}

			if(count($igpost)>0)//SaveToDb
			{
				SaveIGPost($igpost, $user_id);
				//fwrite($log_file, "Add ".count($fbpost)."post\r\n");
				unset($igpost);
				$igpost = array();
			}


			if($break) //no data, then stop looping
				break;
		}//end for loop
	}
	
	function GetLatestPost($userid)
	{
		$conn = CreateConnection();
		$sql = "SELECT * FROM tb_ig_post WHERE user_id= $userid AND created_time = (SELECT MAX(`created_time`) FROM tb_ig_post)";
		$result = $conn->query($sql);
		$r = null;
		if ($result->num_rows > 0) {
			// output data of each row
			$row = $result->fetch_assoc();
			//print_r($row);
			//return $row['tweet_id'];
			$r = $row['created_time'];
		}
		CloseConnection($conn);
		return $r;
	}

	function SaveIGPost($igpost, $user_id)
	{
		global $log_file, $CURRENT_DATE, $gpid;

		$conn = CreateConnection();

		$sql = "INSERT IGNORE INTO tb_ig_post (post_id, user_id, pid, created_time, caption, likes_count, comments_count, type, img_url, link_url, filter_type, userinphoto_count, tags ) VALUES ";
		
		$i=1;
		foreach($igpost as $igp)
		{
			if($i!=1)
				$sql .=", ";

			$sql .= "('$igp->post_id','$user_id', '$gpid', '$igp->created_time', '".mysql_real_escape_string($igp->caption)."', '$igp->likes_count', '$igp->comments_count', '$igp->type', '$igp->img_url', '$igp->link_url', '$igp->filter_type', '$igp->userinphoto_count', '$igp->tags')";
			$i++;
		}
		echo $sql."<br/><br/>";

		if (!mysqli_query($conn, $sql)) {
			//echo "Insert ID:".$pid."\r\n";
			echo "Error: " . $sql . "<br>" . mysqli_error($conn);
			//fwrite($log_file, "Error: " . $sql . "<br>" . mysqli_error($conn)."\r\n");
		} 

		CloseConnection($conn);
	}
?>