<?php
	class profile
	{
		var $id;
		function set_id($new_id) {$this->id = $new_id;}
		function get_id() {return $this->id;}

		var $name;
		function set_name($new_name) {$this->name = $new_name;}
		function get_name() {return $this->name;}

		var $gender;
		function set_gender($new_gender) {$this->gender = $new_gender;}
		function get_gender() {return $this->gender;}

		var $location;
		function set_location($new_location) {$this->location = $new_location;}
		function get_location() {return $this->location;}

		var $birthyear;
		function set_birthyear($new_birthyear) {$this->birthyear = $new_birthyear;}
		function get_birthyear() {return $this->birthyear;}

		var $total_friend;
		function set_total_friend($new_total_friend) {$this->total_friend = $new_total_friend;}
		function get_total_friend() {return $this->total_friend;}

		var $token;
		function set_token($new_token) {$this->token = $new_token;}
		function get_token() {return $this->token;}
	}
	class post
	{
		var $post_id;
		function set_post_id($new_post_id) {$this->post_id = $new_post_id;}
		function get_post_id() {return $this->post_id;}

		var $created_at;
		function set_created_at($new_created_at) {$this->created_at = $new_created_at;}
		function get_created_at() {return $this->created_at;}

		var $status_type;
		function set_status_type($new_status_type) {$this->status_type = $new_status_type;}
		function get_status_type() {return $this->status_type;}

		var $media;
		function set_media($new_media) {$this->media = $new_media;}
		function get_media() {return $this->media;}

		var $action;
		function set_action($new_action) {$this->action = $new_action;}
		function get_action() {return $this->action;}

		var $total_like;
		function set_total_like($new_total_like) {$this->total_like = $new_total_like;}
		function get_total_like() {return $this->total_like;}

		var $message;
		function set_message($new_message) {$this->message = $new_message;}
		function get_message() {return $this->message;}

		var $story;
		function set_story($new_story) {$this->story = $new_story;}
		function get_story() {return $this->story;}

		var $parent_id;
		function set_parent_id($new_parent_id) {$this->parent_id = $new_parent_id;}
		function get_parent_id() {return $this->parent_id;}

		var $place;
		function set_place($new_place) {$this->place = $new_place;}
		function get_place() {return $this->place;}

		var $message_tags;
		function set_message_tags($new_message_tags) {$this->message_tags = $new_message_tags;}
		function get_message_tags() {return $this->message_tags;}

		var $story_tags;
		function set_story_tags($new_story_tags) {$this->story_tags = $new_story_tags;}
		function get_story_tags() {return $this->story_tags;}

		var $with_tags;
		function set_with_tags($new_with_tags) {$this->with_tags = $new_with_tags;}
		function get_with_tags() {return $this->with_tags;}

		var $pic_url;
		function set_pic_url($new_pic_url) {$this->pic_url = $new_pic_url;}
		function get_pic_url() {return $this->pic_url;}

	}
	class comment
	{
		var $comment_id;
		function set_comment_id($new_comment_id) {$this->comment_id = $new_comment_id;}
		function get_comment_id() {return $this->comment_id;}

		var $parent_post_id;
		function set_parent_post_id($new_parent_post_id) {$this->parent_post_id = $new_parent_post_id;}
		function get_parent_post_id() {return $this->parent_post_id;}

		var $user_id;
		function set_user_id($new_user_id) {$this->user_id = $new_user_id;}
		function get_user_id() {return $this->user_id;}

		var $created_at;
		function set_created_at($new_created_at) {$this->created_at = $new_created_at;}
		function get_created_at() {return $this->created_at;}

		var $action;
		function set_action($new_action) {$this->action = $new_action;}
		function get_action() {return $this->action;}

		var $total_like;
		function set_total_like($new_total_like) {$this->total_like = $new_total_like;}
		function get_total_like() {return $this->total_like;}

		var $total_reply;
		function set_total_reply($new_total_reply) {$this->total_reply = $new_total_reply;}
		function get_total_reply() {return $this->total_reply;}

		var $message;
		function set_message($new_message) {$this->message = $new_message;}
		function get_message() {return $this->message;}

		var $media;
		function set_media($new_media) {$this->media = $new_media;}
		function get_media() {return $this->media;}

		var $message_tags;
		function set_message_tags($new_message_tags) {$this->message_tags = $new_message_tags;}
		function get_message_tags() {return $this->message_tags;}

	}

	class like
	{
		var $parent_post_id;
		function set_parent_post_id($new_parent_post_id) {$this->parent_post_id = $new_parent_post_id;}
		function get_parent_post_id() {return $this->parent_post_id;}

		var $action;
		function set_action($new_action) {$this->action = $new_action;}
		function get_action() {return $this->action;}

		var $created_at;
		function set_created_at($new_created_at) {$this->created_at = $new_created_at;}
		function get_created_at() {return $this->created_at;}
	}
?>