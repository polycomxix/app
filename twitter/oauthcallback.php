<?php 
    session_start();
    include_once("config.php");
    require 'autoload.php';
    use Abraham\TwitterOAuth\TwitterOAuth;

    if(isset($_GET["denied"]))
    {
        header('Location: ../');
        die();
    }
    else
    {
        $request_token = [];
        $request_token['oauth_token'] = $_SESSION['oauth_token'];
        $request_token['oauth_token_secret'] = $_SESSION['oauth_token_secret'];


        if (isset($_REQUEST['oauth_token']) && $request_token['oauth_token'] != $_REQUEST['oauth_token']) {
            // Abort! Something is wrong.
            session_destroy();
            header('Location: ../');
        }

        $connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $request_token['oauth_token'], $request_token['oauth_token_secret']);
        $access_token = $connection->oauth("oauth/access_token", array("oauth_verifier" => $_REQUEST['oauth_verifier']));
        $_SESSION['access_token'] = $access_token;
        header("Location: ../".$_SESSION['lang']."/twitter.html?approved=true"); 
        //header('Location: ../twitter/get_tweet_data.php'); 
    }

?>