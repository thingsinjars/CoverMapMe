<?php
	include_once("facebook.php");
	
	$facebook = new Facebook(array(
		"appId" => $appId,
		"secret" => $secret,
		"cookie" => true
	));

	$session = $facebook->getSession();
	
	// redirect to login page wherever errors happens or session not found
	function redirect($facebook){
		$loginUrl = $facebook->getLoginUrl(array(
			"canvas" =>1,
			"fbconnect" =>0,
			'req_perms' => "email,publish_stream,user_hometown,user_location,user_photos,friends_photos,
					user_photo_video_tags,friends_photo_video_tags,user_videos,video_upload,friends_videos"
			//'req_perms' => "email,publish_stream,user_hometown,user_location"
			//'req_perms' => "email,publish_stream,status_update,user_hometown,
			//				user_location,user_photos,friends_photos,user_photo_video_tags,friends_photo_video_tags"
		));

		 /*echo "<script type='text/javascript'>top.location.href = '$loginUrl';</script>";*/
		 echo "loginUrl: " . $loginUrl;
	}
	
	function parse_signed_request($signed_request, $secret) {
        list($encoded_sig, $payload) = explode('.', $signed_request, 2);
 
        // decode the data
        $sig = base64_url_decode($encoded_sig);
        $data = json_decode(base64_url_decode($payload), true);
 
        if (strtoupper($data['algorithm']) !== 'HMAC-SHA256') {
            error_log('Unknown algorithm. Expected HMAC-SHA256');
            return null;
        }
 
        // check sig
        $expected_sig = hash_hmac('sha256', $payload, $secret, $raw = true);
        if ($sig !== $expected_sig) {
            error_log('Bad Signed JSON signature!');
            return null;
        }
 
        return $data;
    }
 
	function redirect_fanpage($facebook, $fanpage_url){
		$loginUrl = $facebook->getUrl(
		  'www',
		  'login.php',
		  array_merge(array(
			'api_key'         => $facebook->getAppId(),
			'cancel_url'      => $fanpage_url,
			'display'         => 'page',
			'fbconnect'       => 1,
			'next'            => $fanpage_url,
			'return_session'  => 1,
			'session_version' => 3,
			'v'               => '1.0',
		  ), array(
				"canvas" =>1, 
				"fbconnect" =>0, 
				//'req_perms' => "email,publish_stream,status_update,user_hometown,user_location"
				'req_perms' => "email,publish_stream,user_hometown,user_location,user_photos,friends_photos,
					user_photo_video_tags,friends_photo_video_tags,user_videos,video_upload,friends_videos"
			))
		);
		//echo $loginUrl;
		echo "<script type='text/javascript'>top.location.href = '$loginUrl';</script>";
	}
 
    function base64_url_decode($input) {
        return base64_decode(strtr($input, '-_', '+/'));
    }
?>