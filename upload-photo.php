<?php
/*
 * Take the passed parameters and download the image locally
 * Then add the watermark
 * Upload it to Facebook
 * Redirect the user to facebook to let them confirm 
 */
require 'log-into-facebook.php';

$coordinate = $_GET['c'];
$zoom = $_GET['z'];
$tile = $_GET['t'];

// If the values passed in are what we expect them to be
if(preg_match('/^(\-?\d+(\.\d+)?),\s*(\-?\d+(\.\d+)?)$/', $coordinate) && ctype_alnum($zoom) && ctype_alnum($tile)) {

// Generate Local filename
	$filename = "cache/" . hash('SHA256', $coordinate . $zoom . $tile) . ".png";

// Download chosen map tile
	$tileUrl = "http://m.nok.it/?app_id=" . $nokiaMapsAppId . "&token=" . $nokiaMapsToken . "&c=" . $coordinate . '&z=' . $zoom . '&nord&w=851&h=315&nodot&t=' . $tile;
	$ch = curl_init( $tileUrl );
	$fp = fopen( $filename, 'wb' );
	curl_setopt( $ch, CURLOPT_FILE, $fp );
	curl_setopt( $ch, CURLOPT_HEADER, 0 );
	curl_exec( $ch ); 
	curl_close( $ch );
	fclose( $fp );

// Add Watermark
	$source_gd_image = imagecreatefromjpeg($filename);
	$overlay_gd_image = imagecreatefrompng('watermark.png');
	$overlay_width = 851;
	$overlay_height = 315;
	imagecopymerge(
		$source_gd_image,
		$overlay_gd_image,
		0,
		0,
		0,
		0,
		$overlay_width,
		$overlay_height,
		100
		);
	imagejpeg($source_gd_image, $filename, 100);
	imagedestroy($source_gd_image);
	imagedestroy($overlay_gd_image);


//get Facebook profile album
	$albums = $facebook->api("/me/albums");
	$album_id = ""; 
	foreach($albums["data"] as $item){
		if($item["type"] == "profile"){
			$album_id = $item["id"];
			break;
		}
	}

//set photo atributes
	$full_image_path = realpath($filename);
	$args = array('message' => 'Uploaded by CoverMap.me');
	$args['image'] = '@' . $full_image_path;

//upload photo to Facebook
	$data = $facebook->api("/{$album_id}/photos", 'post', $args);

// Figure out the URL the user needs to confirm on
	$user = $facebook->api("/me");
	$username = $user['username'];

	$fb_image_link = "http://www.facebook.com/" . $username . "?preview_cover=" . $data['id'];

	// Delete from local filesystem. Could maybe use this to cache but there would be so few cache hits, it doesn't make sense
	unlink($filename);

	// POST TO STREAM (EXTENDED PERMISSION)
	try {
		$facebook->api('/me/feed', 'POST',array(
			'link' => 'http://covermap.me',
			'message' => 'Update your cover image with a Nokia Map using CoverMap.Me',
			'description' => "Position a Nokia Map, choose a tile style and set it as your timeline cover image.",
			'privacy' => array('value' => 'EVERYONE')));

	} catch (Exception $e) {

	}
	?><!DOCTYPE html>
	<html>
	<head>
		<meta charset="utf-8">
		<title>CoverMap.Me - Nokia Maps as Facebook Timeline covers</title>
		<link rel="icon" href="http://maps.nokia.com/favicon.ico">
		<link rel="stylesheet" href="/assets/main.css">
		<script type="text/javascript">
		var _gaq = _gaq || [];
		_gaq.push(['_setAccount', '<?php echo $gaq; ?>']);
		_gaq.push(['_trackPageview']);
		(function() {
			var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
			ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
			var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
		})();
		</script>
	</head>
	<body>
		<h1>Image uploaded</h1>
		<p>You will now be redirected to your Facebook profile page. Click the 'Save Changes' button to complete.</p>
		<p><a href="<?php echo $fb_image_link; ?>">Click here if you are not redirected within a few seconds</a></p>
		<script type='text/javascript'>setTimeout(function() {top.location.href = '<?php echo $fb_image_link; ?>';}, 5000);</script>
	</body>
	</html>
	<?php 
}
// Something has gone wrong and the wrong URL values have been passed in. Possibly some nefarious attempt at messing with things.
// Redirect to the home page.
?><script type='text/javascript'>top.location.href = '/';</script>