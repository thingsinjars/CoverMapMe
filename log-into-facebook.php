<?php
/*
 * This file handles getting the user logged into Facebook
 */

// My app token and secrets.
require 'config.php';

// The Facebook SDK:
require 'src/facebook.php';

// Create a Facebook object
$facebook = new Facebook(array(
	'appId'  => $facebookAppId,
	'secret' => $facebookAppSecret,
	"cookie" => true,
	'fileUpload' => true
));

$user_id = $facebook->getUser();

// If the user is not logged in:
if($user_id == 0 || $user_id == "") {
	// Get the URL for them to log in on
	$logged_in = false;
	$login_url = $facebook->getLoginUrl(array(
		'redirect_uri'         => "http://covermap.me/index.php",
		'req_perms'      => "publish_stream"));
} else {
	$logged_in = true;

	// Try getting their details.
	// If this fails, they weren't actually logged in.
	try {
		$user = $facebook->api("/me");
		$username = $user['username'];

		$profile_pic_url = "https://graph.facebook.com/" . $username . "/picture?type=large";
	} catch(Exception $e) {
		$logged_in = false;
		$login_url = $facebook->getLoginUrl(array(
			'redirect_uri'         => "http://covermap.me/index.php",
			'req_perms'      => "publish_stream"));
	}
}
