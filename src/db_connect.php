<?php
	$db = mysql_connect($db_host,$db_username,$db_password) or die('Cannot connect to database because ' . mysql_error($db));
	mysql_select_db($db_name, $db);
	mysql_query("SET NAMES 'utf8'");
?>