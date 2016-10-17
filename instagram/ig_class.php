<?php

	class profile
	{
		var $id;
		function set_id($new_id) {$this->id = $new_id;}
		function get_id() {return $this->id;}

		var $username;
		function set_username($new_username) {$this->username = $new_username;}
		function get_username() {return $this->username;}

		var $follower_count;
		function set_follower_count($new_follower_count) {$this->follower_count = $new_follower_count;}
		function get_follower_count() {return $this->follower_count;}

		var $following_count;
		function set_following_count($new_following_count) {$this->following_count = $new_following_count;}
		function get_following_count() {return $this->following_count;}

		var $post_count;
		function set_post_count($new_post_count) {$this->post_count = $new_post_count;}
		function get_post_count() {return $this->post_count;}

	}

	class post
	{
		var $post_id;
		function set_post_id($new_post_id) {$this->post_id = $new_post_id;}
		function get_post_id() {return $this->post_id;}

		var $created_time;
		function set_created_time($new_created_time) {$this->created_time = $new_created_time;}
		function get_created_time() {return $this->created_time;}

		var $caption;
		function set_caption($new_caption) {$this->caption = $new_caption;}
		function get_caption() {return $this->caption;}

		var $type;
		function set_type($new_type) {$this->type = $new_type;}
		function get_type() {return $this->type;}

		var $likes_count;
		function set_likes_count($new_likes_count) {$this->likes_count = $new_likes_count;}
		function get_likes_count() {return $this->likes_count;}

		var $comments_count;
		function set_comments_count($new_comments_count) {$this->comments_count = $new_comments_count;}
		function get_comments_count() {return $this->comments_count;}

		var $img_url;
		function set_img_url($new_img_url) {$this->img_url = $new_img_url;}
		function get_img_url() {return $this->img_url;}

		var $link_url;
		function set_link_url($new_link_url) {$this->link_url = $new_link_url;}
		function get_link_url() {return $this->link_url;}

		var $filter_type;
		function set_filter_type($new_filter_type) {$this->filter_type = $new_filter_type;}
		function get_filter_type() {return $this->filter_type;}

		var $userinphoto_count;
		function set_userinphoto_count($new_userinphoto_count) {$this->userinphoto_count = $new_userinphoto_count;}
		function get_userinphoto_count() {return $this->userinphoto_count;}

		var $tags;
		function set_tags($new_tags) {$this->tags = $new_tags;}
		function get_tags() {return $this->tags;}


	}
?>