<?php 
	session_start();
	include_once("config.php");

	if(isset($_GET['lang']))
		$_SESSION['lang']= $_GET['lang'];

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
		header("Location: ../".$_SESSION['lang']."/facebook.html?approved=true"); 

	}
	else{
		$loginUrl = $helper->getLoginUrl('http://sns.jin.ise.shibaura-it.ac.jp/app/facebook/fbcallback.php', $permissions);
		header("Location: ".$loginUrl);
	}




?>