<?php 
		require_once('twitter_lib.php');
		//$url = 'https://api.twitter.com/1.1/statuses/user_timeline.json';
		$url = $defaultURL."favorites/list.json";
		$requestMethod = 'GET';

		if (isset($_GET['user']))  {$user = $_GET['user'];}  else {$user  = "tangjaidee";} //oyoeoyo //tangjaidee //PoLYcomXIX
		if (isset($_GET['count'])) {$count = $_GET['count'];} else {$count = 200;}

		$getfield = "?screen_name=$user&count=$count";;
		$twitter = new TwitterAPIExchange($settings);
		//$string = json_decode($twitter->setGetfield($getfield)->buildOauth($url, $requestMethod)->performRequest(),$assoc = TRUE);
		echo $twitter->setGetfield($getfield)->buildOauth($url, $requestMethod)->performRequest();

		//get favorite activity https://api.twitter.com/1.1/favorites/list.json
		//get tweet detail https://api.twitter.com/1.1/statuses/show.json or https://api.twitter.com/1.1/statuses/lookup.json
		//
?>