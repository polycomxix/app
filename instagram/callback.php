<?php
	session_start();
	include_once("config.php");

	
	//echo "start";
	if(true===isset($_GET['code']))//Check whether the user has granted access
	{
		$data =  $instagram->getOAuthToken($_GET['code']);
		//print_r($data);
		if(empty($data->user->username))
		{
			header('Location: ../');
        	die();
		}
		else
		{
			//echo $data->access_token;
			$_SESSION['access_token'] = $data->access_token;
        	header('Location: ../quiz/instagram.html?approved=true'); 
        	//header('Location: ../instagram/get_ig_data.php'); 
		}
	}
	else
	{
		if (true === isset($_GET['error'])) 
		{
			echo 'An error occurred: '.$_GET['error_description'];
		}
		else{
			$ig_url = $instagram->getLoginUrl(array(
												  'basic',
												  'likes',
												  'comments',
												  'relationships'
												));
			header('Location: ' . $ig_url);
		}	 
	}



?>