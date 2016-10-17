<?php 
	session_start();
	include_once("config.php");


	try{
		$accessToken = $helper->getAccessToken();
	}catch(Facebook\Exceptions\FacebookResponseException $e){
		// When Graph returns an error
		echo 'Graph returned an error: ' . $e->getMessage();
		exit;
	} catch(Facebook\Exceptions\FacebookSDKException $e) {
		// When validation fails or other local issues
		echo 'Facebook SDK returned an error: ' . $e->getMessage();
		exit;
	}

	if (isset($accessToken)) {
		$_SESSION['access_token'] = (string) $accessToken;
		header('Location: ../quiz/facebook.html?approved=true'); 
		//header('Location: ../facebook/get_fb_quizresult.php'); 
		//header('Location: ../facebook/service_get_fb_data.php'); 
  		// Logged in!
	  	/*$response = $fb->get('/me?fields=id,name', $accessToken);
	  	$user = $response->getGraphUser();
	  	print_r($user);*/

	  	// Now you can redirect to another page and use the
	  	// access token from $_SESSION['facebook_access_token']
	}
	else{
		$loginUrl = $helper->getLoginUrl('http://sns.jin.ise.shibaura-it.ac.jp/app/facebook/fbcallback.php', $permissions);
		header("Location: ".$loginUrl);
	}




?>