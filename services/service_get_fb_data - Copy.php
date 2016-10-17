<?php
	session_start();
	//set_time_limit (120);
	$root = "/var/www/html/app";

	include_once($root."/facebook/config.php");
	include_once($root."/php/connect.php");
	include_once($root."/facebook/facebook_class.php");

	//prame 10154137922533329
	//pramecomix@gmail.com
	//pramemanuch45360666
	//run service cmd php -f "C:\xampp\htdocs\datacollection\services\get_fb_data.php"
	
	$userlist = array(); 

	//$sql = "SELECT * FROM tb_facebook_profile WHERE access_token != ''";
	$conn = CreateConnection();
	$sql = "SELECT * FROM tb_facebook_profile WHERE status=0 limit 5";
	if($result = $conn->query($sql))
	{
		while($row = $result->fetch_assoc())
		{	
			array_push($userlist,$row);
			//Update User
			UpdateReserveUser($row['user_id'],1);//reserve
		}
	}
	CloseConnection($conn);
	//print_r($userlist);
	if(count($userlist)>0)
	{	
		
		for($i=0; $i<count($userlist); $i++)
		{	
			$time_start = microtime(true); 
			//echo $userlist[$i]['user_id']."<br/>";
			$access_token = $userlist[$i]['access_token'];
			$gpid = $userlist[$i]['pid'];
			echo "gpid:".$gpid."<br/>";

			$date = date_format(new DateTime("now", new DateTimeZone('Asia/Tokyo')),'Y-m-d_H-i-s');
			$filename = __DIR__."/log/fb_".$date."_".$userlist[$i]['user_id'].".txt";
			//echo $filename;
			$log_file = fopen($filename, "w") or die ("Unable to open file!");
			fwrite($log_file, "----Start Query----\r\n");

			//Prevent incase update
			$since_id= GetLastestPost($userlist[$i]['user_id']);
			echo $since_id."<br/>";

			if(GetFacebookPost($access_token, $userlist[$i]['user_id'], $since_id))
			{
				GetFacebookComment($access_token, $userlist[$i]['user_id'], $since_id);
				GetFacebookLike($access_token, $userlist[$i]['user_id'], $since_id);
			}
			//Update User
			UpdateReserveUser($userlist[$i]['user_id'],2);//finish
			
			fwrite($log_file, "----End Query-----\r\n");
			fwrite($log_file, "Total execution time in seconds: " . (microtime(true) - $time_start)."\r\n");
			fclose($log_file);
			echo 'Total execution time in seconds: ' . (microtime(true) - $time_start)."<br/>";
		}
		
	}
	

	function GetFacebookPost($access_token, $fbid, $since_id)
	{
		global $fb, $gpid, $since_date, $log_file;
		//echo "fbid:".$fbid."  token:".$access_token."<br/>";
		$fbpost = $fblike	= array();

		//$param = $fbid.'/feed?fields=id,created_time,type,from,likes.limit(200),message, status_type,comments{created_time,id,message,from,user_likes,comment_count,attachment}&limit=10';
		$param	 = $fbid.'/feed?fields=id,created_time,status_type,type,from,message,story,parent_id,place,message_tags,story_tags,with_tags,full_picture&limit=200';
		//Prevent incase update
		if($since_id!=null) //update more tweet data
			$param .= '&since='.(strtotime($since_id)+7200);//2hours

		//echo $param."<br/>";
		$response = $fb->get($param, $access_token);
		$feed = $response->getGraphEdge();
		//echo "feed:".count($feed);
		if(count($feed)==0)
			return true; 

		//print_r($post);
		$i=1;
		$break = false;
		//mysqli_query($conn,"START TRANSACTION");
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
				
				$j=1;
				foreach($feed as $p)
				{

					$post = new post();
					//fwrite($log_file, "Post ID ".$p->getField('id')."\r\n");

					$created_at = date_format($p->getField('created_time'),'Y-m-d H:i:s');
					if(empty($p) || $created_at<$since_date)
					{
						$break = true;
						break;
					}
					//echo "Count:".$j."-".$p->getField('id')."->".count($p->getField('likes'))."-".date_format($p->getField('created_time'),'Y-m-d H:i:s')."<br/>";

					/*--------Store post data----------*/
					$post->post_id 		= $p->getField('id');
					$post->created_at 	= date_format($p->getField('created_time'),'Y-m-d H:i:s');
					$post->status_type 	= $p->getField('status_type');
					$post->media 		= $p->getField('type');
					$post->action 		= $fbid != $p->getField('from')->getField('id') ? "tagged" : "post";
					//$post->total_like	= count($p->getField('likes'));
					$post->message 		= $p->getField('message');
					$post->story 		= $p->getField('story');
					$post->parent_id 	= $p->getField('parent_id') != null ? $p->getField('parent_id') : null;
					$post->place 		= $p->getField('place')	!= null ? $p->getField('place')->getField('name') : null;
					$post->message_tags	= count($p->getField('message_tags'));
					$post->story_tags	= count($p->getField('story_tags'));
					$post->with_tags	= count($p->getField('with_tags'));
					$post->pic_url		= $p->getField('full_picture');

					$fbpost[] = $post;

					if($j%50==0)//SaveToDb
					{
						if(count($fbpost)>0)//SaveToDb
						{
							SaveFBPost($fbpost, $fbid);
							//fwrite($log_file, "Add ".count($fbpost)."post\r\n");
							unset($fbpost);
							$fbpost = array();
						}
					}

					$j++;			
				}//end for loop feed
				$i++;


				if(count($fbpost)>0)//SaveToDb
				{
					SaveFBPost($fbpost, $fbid);
					//fwrite($log_file, "Add ".count($fbpost)."post\r\n");
					unset($fbpost);
					$fbpost = array();
				}

				if($break) //no data, then stop looping
					break;
			}while($i!=15);
			fwrite($log_file, "Complete adding post\r\n");	
			return true;
		}catch (Exception $e){
			//mysqli_query($conn,"ROLLBACK");
			fwrite($log_file, $e."\r\n");
			return false;
		}
	}

	function GetFacebookComment($access_token, $fbid, $since_id)
	{
		global $fb, $log_file;

		$fbpost = $fbcomment = $fblike	= array();

		$conn = CreateConnection();
		$sql = "SELECT post_id, created_at FROM tb_facebook_post WHERE user_id= $fbid" ;
		if($since_id!=null)
			$sql .= " and created_at >= '$since_id'";
		//echo $sql."<br/>";
		
		if($result = $conn->query($sql))
		{
			while($row = $result->fetch_assoc())
			{	
				array_push($fbpost,$row);
			}
		}
		CloseConnection($conn);

		if(count($fbpost)>0)
		{
			for($i=0; $i<count($fbpost); $i++)
			{
		    	//echo "Post ID:".$fbpost[$i]."<br/><hr/>";
		    	//print_r($fbpost[$i]);
		    	//echo "<br/>";
		        $param = $fbpost[$i]['post_id'].'/comments?fields=id,message,from,created_time,user_likes,like_count,attachment,comments.limit(200){id,message,from,created_time,user_likes,like_count,attachment,message_tags},message_tags&limit=200';
		        try{
			        $response = $fb->get($param, $access_token);
					$comment= $response->getGraphEdge();
				}catch (Exception $e){
					//mysqli_query($conn,"ROLLBACK");
					fwrite($log_file, "Post ID:".$fbpost[$i]['post_id']."\r\n".$e."\r\n");
					$comment=null;
				}
				if($comment!=null)
				{
					$j=1;
					foreach ($comment as $c) {
						//echo $j.": ".$c->getField('id').$c->getField('message').date_format($c->getField('created_time'),'Y-m-d H:i:s')."<br/>";

						$comment = new comment();
						$comment->comment_id 		= $c->getField('id');
						$comment->parent_post_id 	= $fbpost[$i]['post_id'];
						$comment->user_id			= $c->getField('from')->getField('id');
						$comment->created_at 		= date_format($c->getField('created_time'),'Y-m-d H:i:s');
						$comment->action 			= "comment";
						$comment->total_like		= $c->getField('like_count');
						$comment->total_reply		= count($c->getField('comments'));
						$comment->message 			= $c->getField('message');;
						$comment->media 			= $c->getField('attachment') != null ? $c->getField('attachment')->getField('type') : "text";
						$comment->message_tags 		= count($c->getField('message_tags'));
						
						$fbcomment[]=$comment;
						
						//Get Comment Like
						if($c->getField('user_likes'))
						{
							$like = new like();
							$like->parent_post_id 	= $c->getField('id');
							$like->created_at 		= date_format($c->getField('created_time'),'Y-m-d H:i:s');
							$like->action 			= "comment";
							$fblike[] = $like;
						}

						//Get reply comment
						if(count($c->getField('comments'))>0)
						{
							foreach ($c->getField('comments') as $r) 
							{
								$reply = new comment();
								$reply ->comment_id 	= $r->getField('id');
								$reply ->parent_post_id = $c->getField('id');
								$reply ->user_id		= $r->getField('from')->getField('id');
								$reply ->created_at 	= date_format($r->getField('created_time'),'Y-m-d H:i:s');
								$reply ->action 		= "reply";
								$reply ->total_like		= $r->getField('like_count');
								$reply ->message 		= $r->getField('message');;
								$reply ->media 			= $r->getField('attachment') != null ? $c->getField('type') : "text";
								$reply ->message_tags 	= count($c->getField('message_tags'));

								$fbcomment[]=$reply ;

								//Get Reply Like
								if($r->getField('user_likes'))
								{
									$like = new like();
									$like->parent_post_id 	= $r->getField('id');
									$like->created_at 		= date_format($r->getField('created_time'),'Y-m-d H:i:s');
									$like->action 			= "reply";
									$fblike[] = $like;
								}
							}
						}

						if($j%50==0)//SaveToDb
						{
							if(count($fbcomment)>0)//SaveToDb
							{
								SaveFBComment($fbcomment,$fbid);
								//fwrite($log_file, "Add ".count($fbcomment)."comments\r\n");
								unset($fbcomment);
								$fbcomment = array();
							}
							if(count($fblike)>0)//SaveToDb
							{
								SaveFBLike($fblike, $fbid);
								//fwrite($log_file, "Add ".count($fblike)."likes\r\n");
								unset($fblike);
								$fblike = array();
							}
						}
						$j++;
					}

					/*$param = $fbpost[$i]['post_id'].'/likes?summary=true';
					$response = $fb->get($param, $access_token);
					$likepost= $response->getGraphEdge()->getMetaData()['summary'];

					if($likepost['has_liked'])
					{
						$like = new like();
						$like->parent_post_id 	= $fbpost[$i]['post_id'];
						$like->created_at 		= $fbpost[$i]['created_at'];
						$like->action 			= "post";
						$fblike[] = $like; 
						//SaveFBLike($like, $fbid);
					}*/
					//print_r($like);
					//echo "total_like:".$like['total_count']." can_like:".$like['can_like']." has_like:".$like['has_liked']."<br/>";


					/*echo "<br/>---COMMENT--<br/>";
					print_r($fbcomment);
					echo "<br/>---LIKE--<br/>";
					print_r($fblike);*/
					if(count($fbcomment)>0)//SaveToDb
					{
						SaveFBComment($fbcomment,$fbid);
						//fwrite($log_file, "Add ".count($fbcomment)."comments\r\n");
						unset($fbcomment);
						$fbcomment = array();
					}
					if(count($fblike)>0)//SaveToDb
					{
						SaveFBLike($fblike, $fbid);
						//fwrite($log_file, "Add ".count($fblike)."likes\r\n");
						unset($fblike);
						$fblike = array();
					}
					//Update Post Status
					UpdatePost($fbpost[$i]['post_id'], 0, 1);
				}
			}//end for fbpost
		}//end if $fbpost
		fwrite($log_file, "Complete add comment and like \r\n");
	}

	function GetFacebookLike($access_token, $fbid, $since_id)
	{
		global $fb, $log_file;

		$fbpost = $fblike	= array();

		$conn = CreateConnection();
		$sql = "SELECT post_id, created_at FROM tb_facebook_post WHERE user_id= $fbid" ;
		if($since_id!=null)
			$sql .= " and created_at >= '$since_id'";
		//echo $sql."<br/>";
		
		if($result = $conn->query($sql))
		{
			while($row = $result->fetch_assoc())
			{	
				array_push($fbpost,$row);
			}
		}
		CloseConnection($conn);

		if(count($fbpost)>0)
		{
			for($i=0; $i<count($fbpost); $i++)
			{

				$param = $fbpost[$i]['post_id'].'/likes?summary=true';
				try{
					$response = $fb->get($param, $access_token);
					$likepost= $response->getGraphEdge();//->getMetaData()['summary'];
					//$likepost = $likepost->getMetaData()['su']
				}catch (Exception $e){
					//mysqli_query($conn,"ROLLBACK");
					fwrite($log_file, "Post ID:".$fbpost[$i]['post_id']."\r\n".$e."\r\n");
					$likepost = null;
				}
				if($likepost!=null && count($likepost)>0)
				{
					//print_r($like);
					//echo "total_like:".$like['total_count']." can_like:".$like['can_like']." has_like:".$like['has_liked']."<br/>";
					if($likepost->getMetaData()['summary']['has_liked'])
					{
						$like = new like();
						$like->parent_post_id 	= $fbpost[$i]['post_id'];
						$like->created_at 		= $fbpost[$i]['created_at'];
						$like->action 			= "post";
						$fblike[] = $like; 

						SaveFBLike($fblike, $fbid);
						unset($fblike);
						$fblike = array();
					}

					//Update Post Status
					UpdatePost($fbpost[$i]['post_id'], $likepost->getTotalCount(), 2);
				}
			}
		}
		fwrite($log_file, "Complete add like post \r\n");
	}

	function GetLastestPost($user_id)
	{
		$conn= CreateConnection();
		$sql = "SELECT * FROM tb_facebook_post WHERE user_id= $user_id AND created_at = (SELECT MAX(`created_at`) FROM tb_facebook_post)";

		$result = $conn->query($sql);
		
		if ($result->num_rows > 0) {
			// output data of each row
			$row = $result->fetch_assoc();
			//print_r($row);
			CloseConnection($conn);
			return $row['created_at'];
		}
		CloseConnection($conn);
		return null;
	}

	function SaveFBPost($fbpost, $fbid)
	{
		global $gpid, $log_file;

		$pid = $gpid;
		$conn= CreateConnection();
		$sql = "INSERT IGNORE INTO tb_facebook_post (post_id, user_id, pid, created_at, status_type, media, full_picture, action, parent_id, total_like, message, story, place, no_message_tags, no_story_tagged, no_with_tags) VALUES ";
		$i=1;
		foreach($fbpost as $p)
		{
			if($i!=1)
				$sql .=", ";

			$sql .= "('$p->post_id', '$fbid', $pid, '$p->created_at', '$p->status_type', '$p->media', '$p->pic_url', '$p->action', '$p->parent_id', '$p->total_like','".mysqli_real_escape_string($conn, $p->message)."', '".mysqli_real_escape_string($conn, $p->story)."', '".mysqli_real_escape_string($conn, $p->place)."', '$p->message_tags', '$p->story_tags', '$p->with_tags')";
			$i++;
		}

			//echo $sql;
		//echo $sql."<br/><hr/>";
		if (!mysqli_query($conn, $sql)) {
							    //echo "Insert ID:".$pid."\r\n";
			//echo "Error: " . $sql . "<br>" . mysqli_error($conn);
			fwrite($log_file, "Error: " . $sql . "<br>" . mysqli_error($conn)."\r\n");
		}
		CloseConnection($conn);
		//echo "<hr/><br/>";
	}
	function SaveFBComment($fbcomment, $fbid)
	{
		global $gpid, $log_file;
		$pid = $gpid;

		$conn= CreateConnection();
		$sql = "INSERT IGNORE INTO tb_facebook_comment (comment_id, parent_id, user_id, pid, created_at, type, total_like, total_reply, message, media, message_tags) VALUES ";
		$i=1;
		foreach($fbcomment as $p)
		{
			if($i!=1)
				$sql .=", ";

			$sql .= "('$p->comment_id', '$p->parent_post_id', '$p->user_id', '$pid', '$p->created_at', '$p->action', '$p->total_like', '$p->total_reply','".mysqli_real_escape_string($conn, $p->message)."', '$p->media', '$p->message_tags' )";
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
	function SaveFBLike($fblike, $fbid)
	{
		global $gpid, $log_file;

		$pid = $gpid;

		$conn= CreateConnection();
		$sql = "INSERT IGNORE INTO tb_facebook_like (parent_post_id, user_id, pid, type, created_at) VALUES ";
		$i=1;
		foreach($fblike as $p)
		{
			if($i!=1)
				$sql .=", ";

			$sql .= "('$p->parent_post_id', '$fbid', '$pid', '$p->action', '$p->created_at')";
			$i++;
		}
			//echo $sql;
		if (!mysqli_query($conn, $sql)) {
							    //echo "Insert ID:".$pid."\r\n";
			//echo "Error: " . $sql . "<br>" . mysqli_error($conn);
			fwrite($log_file, "Error: " . $sql . "<br>" . mysqli_error($conn)."\r\n");
		} 
		//echo "<hr/><br/>";
		CloseConnection($conn);
	}

	function UpdatePost($post_id, $total_like, $status)
	{
		$conn = CreateConnection();
		
		$sql = "UPDATE IGNORE tb_facebook_post SET status=$status, total_like=$total_like WHERE post_id='$post_id'";
		$result =mysqli_query($conn, $sql) or die(mysqli_error($conn));

		CloseConnection($conn);
	}

	function UpdateReserveUser($user_id, $status)
	{
		$conn = CreateConnection();
		
		$sql = "UPDATE tb_facebook_profile SET status= $status WHERE user_id='$user_id'";
		$result =mysqli_query($conn, $sql) or die(mysqli_error($conn));

		CloseConnection($conn);
	}


	
?>