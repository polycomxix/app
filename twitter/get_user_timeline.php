<?php 
		require_once('twitter_lib.php');
		//$url = 'https://api.twitter.com/1.1/statuses/user_timeline.json';
		$url = $defaultURL."statuses/user_timeline.json";
		$requestMethod = 'GET';

		if (isset($_GET['user']))  {$user = $_GET['user'];}  else {$user  = "tangjaidee";} //tangjaidee //PoLYcomXIX
		if (isset($_GET['count'])) {$count = $_GET['count'];} else {$count = 200;}

		$getfield = "?screen_name=$user&count=$count";
		$twitter = new TwitterAPIExchange($settings);
		//$string = json_decode($twitter->setGetfield($getfield)->buildOauth($url, $requestMethod)->performRequest(),$assoc = TRUE);
		$a1 = json_decode($twitter->setGetfield($getfield)->buildOauth($url, $requestMethod)->performRequest());
		$a2 = json_decode($twitter->setGetfield($getfield."&page=2")->buildOauth($url, $requestMethod)->performRequest());
		$res = json_encode(array_merge_recursive( $a1, $a2 ));
		echo $res;

		/*$a1 = json_decode( $json1, true );
		$a2 = json_decode( $json2, true );
		$res = array_merge_recursive( $a1, $a2 );
		$resJson = json_encode( $res );*/

?>