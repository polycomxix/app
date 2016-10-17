<?php 
	session_start();
	include_once("config.php");
	require 'autoload.php';
	use Abraham\TwitterOAuth\TwitterOAuth;
	
	//fresh authentication
	$connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET);
	$request_token = $connection->oauth('oauth/request_token', array('oauth_callback' => OAUTH_CALLBACK));

	//received token info from twitter
	$_SESSION['oauth_token'] = $request_token['oauth_token'];
	$_SESSION['oauth_token_secret'] = $request_token['oauth_token_secret'];

	//redirect user to twitter
	$twitter_url = $connection->url('oauth/authorize', array('oauth_token' => $request_token['oauth_token']));
	echo "4".$twitter_url;
	header('Location: ' . $twitter_url); 
?>