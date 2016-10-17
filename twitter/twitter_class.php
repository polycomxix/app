<?php
	class profile
	{
		var $id;
		function set_id($new_id) {$this->id = $new_id;}
		function get_id() {return $this->id;}

		var $screen_name;
		function set_screen_name($new_screen_name) {$this->screen_name = $new_screen_name;}
		function get_screen_name() {return $this->screen_name;}

		var $follower;
		function set_follower($new_follower) {$this->follower = $new_follower;}
		function get_follower() {return $this->follower;}

		var $friend;
		function set_friend($new_friend) {$this->friend = $new_friend;}
		function get_friend() {return $this->friend;}

		var $favorite;
		function set_favorite($new_favorite) {$this->favorite = $new_favorite;}
		function get_favorite() {return $this->favorite;}

		var $statuse;
		function set_statuse($new_statuse) {$this->statuse = $new_statuse;}
		function get_statuse() {return $this->statuse;}

		var $location;
		function set_location($new_location) {$this->location = $new_location;}
		function get_location() {return $this->location;}

		var $joined_date;
		function set_joined_date($new_joined_date) {$this->joined_date = $new_joined_date;}
		function get_joined_date() {return $this->joined_date;}

	}

	class activities
	{
		var $user_id;
		function set_user_id($new_user_id) {$this->user_id = $new_user_id;}
		function get_user_id() {return $this->user_id;}

		var $tweet_id;
		function set_tweet_id($new_tweet_id) {$this->tweet_id = $new_tweet_id;}
		function get_tweet_id() {return $this->tweet_id;}

		var $created_at;
		function set_created_at($new_created_at) {$this->created_at = $new_created_at;}
		function get_created_at() {return $this->created_at;}

		var $action;
		function set_action($new_action) {$this->action = $new_action;}
		function get_action() {return $this->action;}

		var $text;
		function set_text($new_text) {$this->text = $new_text;}
		function get_text() {return $this->text;}

		var $in_reply_ref;
		function set_in_reply_ref($new_in_reply_ref) {$this->in_reply_ref = $new_in_reply_ref;}
		function get_in_reply_ref() {return $this->in_reply_ref;}

		var $retweeted_id;
		function set_retweeted_id($new_retweeted_id) {$this->retweeted_id = $new_retweeted_id;}
		function get_retweeted_id() {return $this->retweeted_id;}

		var $retweeted_created_at;
		function set_retweeted_created_at($new_retweeted_created_at) {$this->retweeted_created_at = $new_retweeted_created_at;}
		function get_retweeted_created_at() {return $this->retweeted_created_at;}

		var $media;
		function set_media($new_media) {$this->media = $new_media;}
		function get_media() {return $this->media;}

		var $attached_link;
		function set_attached_link($new_attached_link) {$this->attached_link = $new_attached_link;}
		function get_attached_link() {return $this->attached_link;}

		var $source;
		function set_source($new_source) {$this->source = $new_source;}
		function get_source() {return $this->source;}

		var $no_user_mention;
		function set_no_user_mention($new_no_user_mention) {$this->no_user_mention = $new_no_user_mention;}
		function get_no_user_mention() {return $this->no_user_mention;}

		var $no_hashtags;
		function set_no_hashtags($new_no_hashtags) {$this->no_hashtags = $new_no_hashtags;}
		function get_no_hashtags() {return $this->no_hashtags;}

		var $retweet_count;
		function set_retweet_count($new_retweet_count) {$this->retweet_count = $new_retweet_count;}
		function get_retweet_count() {return $this->retweet_count;}

		var $favorite_count;
		function set_favorite_count($new_favorite_count) {$this->favorite_count = $new_favorite_count;}
		function get_favorite_count() {return $this->favorite_count;}
		
	}

	class favorites
	{
		var $user_id;
		function set_user_id($new_user_id) {$this->user_id = $new_user_id;}
		function get_user_id() {return $this->user_id;}

		var $fav_id;
		function set_fav_id($new_fav_id) {$this->fav_id = $new_fav_id;}
		function get_fav_id() {return $this->fav_id;}

		var $parent_id;
		function set_parent_id($new_parent_id) {$this->parent_id = $new_parent_id;}
		function get_parent_id() {return $this->parent_id;}

		var $created_at;
		function set_created_at($new_created_at) {$this->created_at = $new_created_at;}
		function get_created_at() {return $this->created_at;}

		var $text;
		function set_text($new_text) {$this->text = $new_text;}
		function get_text() {return $this->text;}

		var $media;
		function set_media($new_media) {$this->media = $new_media;}
		function get_media() {return $this->media;}

		var $attached_link;
		function set_attached_link($new_attached_link) {$this->attached_link = $new_attached_link;}
		function get_attached_link() {return $this->attached_link;}

		var $source;
		function set_source($new_source) {$this->source = $new_source;}
		function get_source() {return $this->source;}

		var $no_user_mention;
		function set_no_user_mention($new_no_user_mention) {$this->no_user_mention = $new_no_user_mention;}
		function get_no_user_mention() {return $this->no_user_mention;}

		var $no_hashtags;
		function set_no_hashtags($new_no_hashtags) {$this->no_hashtags = $new_no_hashtags;}
		function get_no_hashtags() {return $this->no_hashtags;}

		var $retweet_count;
		function set_retweet_count($new_retweet_count) {$this->retweet_count = $new_retweet_count;}
		function get_retweet_count() {return $this->retweet_count;}

		var $favorite_count;
		function set_favorite_count($new_favorite_count) {$this->favorite_count = $new_favorite_count;}
		function get_favorite_count() {return $this->favorite_count;}

	}

?>