<?php
/*
 * Show the not logged-in user an intro page
 * Show logged-in users the JS map in Step one
 *  or the static map in Step two
 */
require 'log-into-facebook.php';
?><!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>CoverMap.Me - Nokia Maps as Facebook Timeline covers</title>
	<meta property="og:image" content="http://covermap.me/assets/icon75.png"/>
	<meta property="og:title" content="CoverMap.Me - Nokia Maps as Timeline covers"/>
	<meta property="og:description" content="Position a Nokia Map, choose a tile style and set it as your timeline cover image."/>
	<link rel="icon" href="http://covermap.me/favicon.ico">
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
<body class="<?php echo $logged_in?'logged_in':'logged_out'; ?>">
	<h1>CoverMap Me<div class="fb-like" data-href="http://covermap.me/" data-send="false" data-layout="button_count" data-width="450" data-show-faces="false"></div></h1>
	<?php 
	if($logged_in) {
		?>	<div id="stepone">
		<h2>Step one: Position the map</h2>
		<div id="basicSearchBox"></div>
		<div id="mapContainer"></div>
		<img class="profile" src="<?php echo $profile_pic_url; ?>">
		<a class="fblink" id="nextstep">Next: choose style</a>
	</div>
	<div id="steptwo">
		<h2>Step two: Choose the map style</h2>
		<div id="staticmapcontainer">
			<img id="staticmap" width="851" height="315" src="http://m.nok.it/?app_id=_peU-uCkp-j8ovkzFGNU&token=gBoUkAMoxoqIWfxWA5DuMQ&c=38.895111,-77.036667&z=12&nord&w=851&h=315&nodot&t=7">
		</div>
		<img class="profile" src="<?php echo $profile_pic_url; ?>">
		<a class="fblink" id="previousstep" href="upload-photo.php">Reposition map</a>
		<a class="fblink" id="upload_link" href="upload-photo.php">Set map as cover</a>
		<div class="tileselector">
			<h3>Small Text</h3>
			<ul class="tile-select">
				<li data-tile="0" class="tile1">Standard</li>
				<li data-tile="2" class="tile2">Terrain</li>
				<li data-tile="3" class="tile3">Satellite</li>
				<li data-tile="4" class="tile4">Public Transport</li>
				<li data-tile="5" class="tile5">Pale</li>
				<li data-tile="14" class="tile6">Night</li>
			</ul>
			<h3>Big Text</h3>
			<ul class="tile-select">
				<li data-tile="6" class="tile7">Standard</li>
				<li data-tile="8" class="tile8">Terrain</li>
				<li data-tile="9" class="tile9">Satellite</li>
				<li data-tile="10" class="tile10">Public Transport</li>
				<li data-tile="11" class="tile11">Pale</li>
				<li data-tile="7" class="tile12">Night</li>
			</ul>
			<h3>No Text</h3>
			<ul class="tile-select">
				<li data-tile="1" class="tile13">Satellite</li>
			</ul>
		</div>
	</div>
	<script src="http://api.maps.nokia.com/2.2.1/jsl.js?with=all" class="jsla-script"></script>
	<script>nokia.Settings.set( "appId", "_peU-uCkp-j8ovkzFGNU");nokia.Settings.set( "authenticationToken", "gBoUkAMoxoqIWfxWA5DuMQ");</script>
	<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.7/jquery.min.js"></script>
	<script src="/assets/jquery.jovi.js"></script>
	<script src="/assets/main.js"></script>
<?php
	} else {
		?>	<h2>Set a Nokia map as Facebook cover image<a class="fblink" href="<?php echo $login_url; ?>">Log in with Facebook</a></h2>
		<p><img src="/assets/timeline.png" alt="Cover Map on my Facebook Timeline page" class="timeline">When Facebook introduced timeline covers, my first thought was:</p>
		<blockquote><p>&ldquo;I should totally use a map for that&rdquo;</p></blockquote>
		<p>After seeing how good it looked, I built this tool to let you position a map, choose from different tile styles and set it as your cover photo.</p>
		<p class="c">To be able to set a Nokia map as your Facebook cover image, you must first log in with your Facebook account.</p>
		<p class="f"><a class="fblink" href="<?php echo $login_url; ?>">Log in with Facebook</a></p>
		<div class="card"><img src="http://thingsinjars.com/layout/thingsinjars/profile-smadine.jpg" alt="Simon Madine (thingsinjars)">
			<h2><a href="http://twitter.com/thingsinjars">@thingsinjars</a></h2>
			<p>Hi, I’m Simon Madine and I make <a href="http://thingsinjars.com/toys/">digital toys</a> and write <a href="http://thingsinjars.com/guides/">guides on web development</a>.</p>
			<p>I'm a Technologies Evangelist for <a href="http://maps.nokia.com/">Nokia Maps</a> in Berlin.</p></div>
		<?php
	}
	?>
	<footer><p>Made in 2012 by <a href="http://thingsinjars.com">thingsinjars</a>. Make your own Nokia Maps mashups at <a href="http://jotapp.com">jotapp.com</a>.</p></footer>
	<div id="fb-root"></div>
<script>(function(d, s, id) {
  var js, fjs = d.getElementsByTagName(s)[0];
  if (d.getElementById(id)) return;
  js = d.createElement(s); js.id = id;
  js.src = "//connect.facebook.net/en_US/all.js#xfbml=1&appId=286885644758850";
  fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));</script>
</body>
</html>