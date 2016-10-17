<?php
	session_start();
	require 'autoload.php';
	use Abraham\TwitterOAuth\TwitterOAuth;
	define('CONSUMER_KEY','JsvyN7xucPtrEwXUVY2ZCB9kg');
	define('CONSUMER_SECRET','EMUY1Siei47VwNAKPwVuQZrmuMVWUApzHpXOgxBebAZjsiTE6a');
	define('OAUTH_CALLBACK','http://127.0.0.1:800/datacollection/twitteranalytic.html');

	$connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET);
	$request_token= $connection->oauth('oauth/request_token', array('oauth_callback' => OAUTH_CALLBACK));
	
	$_SESSION['oauth_token'] = $request_token['oauth_token'];
	$_SESSION['oauth_token_secret'] = $request_token['oauth_token_secret'];
	$url = $connection->url('oauth/authorize', array('oauth_token' => $request_token['oauth_token']));
	//header('Location:'.$url);
	//print_r($request_token);
	/*require_once('TwitterAPIExchange.php');

	$settings = array(
	    'oauth_access_token' => $request_token['oauth_token'],
	    'oauth_access_token_secret' => $request_token['oauth_token_secret'],
	    'consumer_key' => CONSUMER_KEY,
	    'consumer_secret' => CONSUMER_SECRET
	);
	//print_r($settings);*/
	$defaultURL = "https://api.twitter.com/1.1/";
	//Yoe Account
	//User: oyoeoyo
	//Pass: .yoe8@YOE?
?>