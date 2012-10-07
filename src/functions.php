<?php
	//$network = get_network_country();
	function synchonize_user($datainfo, $uid){
		global $table_users;
		$country = "";
		$city = "";
		if(isset($datainfo[0]["current_location"])){
			$country = $datainfo[0]["current_location"]["country"];
			$city = $datainfo[0]["current_location"]["city"];
		}
		
		$user_uid = 0;
		$sql = "select * from {$table_users} where fb_userid='".$uid."'";
		$q = mysql_query($sql);
		if($r = mysql_fetch_array($q)){
			if($r["first_name"] != $datainfo[0]["first_name"] 
			|| $r["last_name"] != $datainfo[0]["last_name"] 
			|| $r["email"] != $datainfo[0]["email"]
			|| $r["locale"] != $datainfo[0]["locale"]
			|| $r["pic_square"] != $datainfo[0]["pic_square"]
			|| $r["city"] != $city
			|| $r["country"] != $country)
			
			$user_uid = $r["id"];
			
			$sql = "update {$table_users} set 
					first_name='".mysql_escape_string($datainfo[0]["first_name"])."', 
					last_name='" . mysql_escape_string($datainfo[0]["last_name"]) . "',
					email='" . mysql_escape_string($datainfo[0]["email"]) . "',
					locale='" . mysql_escape_string($datainfo[0]["locale"]) . "',
					avatar='" . mysql_escape_string($datainfo[0]["pic_square"]) . "',
					sex='" . mysql_escape_string($datainfo[0]["sex"]) . "',
					city='" . mysql_escape_string($city) . "',
					country='" . mysql_escape_string($country) . "', updated_date = now() 
					where fb_userid=".$uid." and id=".(int)$r["id"];
			mysql_query($sql);
		}else{
			$sql = "insert into {$table_users}(first_name, last_name, email, locale, avatar, fb_userid, sex, city, country, updated_date)
					values('".mysql_escape_string($datainfo[0]["first_name"])."',
						   '" . mysql_escape_string($datainfo[0]["last_name"]) . "',
						   '" . mysql_escape_string($datainfo[0]["email"]) . "',
						   '" . mysql_escape_string($datainfo[0]["locale"]) . "',
						   '" . mysql_escape_string($datainfo[0]["pic_square"]) . "',
						   '" . $uid ."',
						   '" . mysql_escape_string($datainfo[0]["sex"]) . "',
						   '" . mysql_escape_string($city) . "',
						   '" . mysql_escape_string($country) . "', now())";
					
			mysql_query($sql);
			$user_uid = mysql_insert_id();
		}
		
		return $user_uid;
	}
	
	function synchonize_friends($friendids, $num, $fb_userid){
		$sql = "select id from apps_friends where fb_userid='".mysql_escape_string($fb_userid)."'";
		$q = mysql_query($sql);
		if(mysql_num_rows($q) > 0){
			$sql = "update apps_friends set friend_ids='".mysql_escape_string($friendids)."', total_friends={$num}, updated_date=now() where fb_userid='".mysql_escape_string($fb_userid)."'";
		}else{
			$sql = "insert into apps_friends(fb_userid, friend_ids, total_friends, updated_date)values('".mysql_escape_string($fb_userid)."','".mysql_escape_string($friendids)."',{$num},now())";
		}
		mysql_query($sql);
	}
	
	function synchonize_friend_detail($id, $name, $avatar, $fb_userid){
		$sql = "select id from dl_fbfriends where fb_userid='".mysql_escape_string($fb_userid)."' and friend_id='".mysql_escape_string($id)."'";
		$q = mysql_query($sql);
		if(mysql_num_rows($q) == 0){
			$sql = "insert into dl_fbfriends(fb_userid, friend_id, friend_name, avatar)values('".mysql_escape_string($fb_userid)."','".mysql_escape_string($id)."', '".mysql_escape_string($avatar)."','".mysql_escape_string($name)."')";
			mysql_query($sql);
		}
	}
	
	function synchonize_ucentral($email, $fb_userid){
		global $secure_url, $member_id, $member_guid, $session, $baseURL, $table_users;
		
		$sql = "select * from {$table_users} where fb_userid='{$fb_userid}'";
		$q = mysql_query($sql);
		if($r = mysql_fetch_array($q)){
			if($r["member_id"] == "" || $r["member_guid"] == ""){
				$sql = "select * from dl_user where email='" . mysql_escape_string($email) . "' limit 1";
				$q = mysql_query($sql);
				if($r = mysql_fetch_array($q)){
					$member_id = $r["id"];
					$member_guid = $r["guid"];
					//Auto activate 
					if($r["flag"] == 0){
						mysql_query("update dl_user set flag=1 where id={$r["id"]} and guid='{$r["guid"]}'");
					}
					$sql = "update {$table_users} set member_id={$r["id"]}, member_guid='{$r["guid"]}' where fb_userid='{$fb_userid}'";
					mysql_query($sql);
					return 1;					
				}else{
					echo "<script type='text/javascript'>top.location.href = '{$baseURL}register.php';</script>";
					exit;
				}
			}else{
				return 1;
			}
		}
		else
		{
			echo "<script type='text/javascript'>top.location.href = '{$baseURL}';</script>";
			exit;
		}
	}
	
	function get_xml_source($api_url){
		try{
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $api_url);
			curl_setopt($ch, CURLOPT_HEADER, false);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
			$xml = curl_exec($ch);
			curl_close($ch);
			$doc = new SimpleXmlElement($xml, LIBXML_NOCDATA);
			
			return $doc;
		}catch (Exception $e){
			return null;
		}
	}
	
	function check_bad_words($badwords_path, $content){
    	$badWord = false;
        
        $xmlDoc = new DOMDocument();
        $xmlDoc->load($badwords_path);
        
        $items = $xmlDoc->getElementsByTagName("word");
        
        foreach($items as $item){
        	$pos = (int)strpos($content, $item->nodeValue);
            if($pos > 0){
            	$badWord = true;
                break;
            }
        }
        
        return $badWord;
    }
	
	function get_token_session($session){
		if(!$session)return "";
		
		$token_session = "{";
		foreach($session as $s => $key){
			$token_session .= ($token_session <> "{" ? "," : "") . '"'.$s.'":"' . $key . '"';
		}
		$token_session .= "}";
		
		return $token_session;
	}
	
	function get_cookie($cookieName){
		if(isset($_COOKIE[$cookieName])){
			return $_COOKIE[$cookieName];
		}
		return "";
	}
	
	function _mysql_extract($q) {
      $res = array();
      $count = 0;
      while($r = mysql_fetch_array($q)) {
            $res[$count] = $r;
            $count++;
      }
      
      return $res;
    }
    
    function mysql_extract($q) {
      $res = array();
      while($r = mysql_fetch_array($q)) {
            $res[] = $r;
      }
      
      return $res;
    }

	function guid() {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 65535), mt_rand(0, 65535), // 32 bits for "time_low"
            mt_rand(0, 65535), // 16 bits for "time_mid"
            mt_rand(0, 4095),  // 12 bits before the 0100 of (version) 4 for "time_hi_and_version"
            bindec(substr_replace(sprintf('%016b', mt_rand(0, 65535)), '01', 6, 2)),
           // 8 bits, the last two of which (positions 6 and 7) are 01, for "clk_seq_hi_res"
           // (hence, the 2nd hex digit after the 3rd hyphen can only be 1, 5, 9 or d)
           // 8 bits for "clk_seq_low"
            mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535) // 48 bits for "node" 
        ); 
    }
	
	function get_network_country(){
		include_once("api/geoip.inc");
		$gi = geoip_open("api/GeoIP.dat",GEOIP_STANDARD);
		$country_code = geoip_country_code_by_addr($gi, $_SERVER[REMOTE_ADDR]);
		$country_name = geoip_country_name_by_addr($gi, $_SERVER[REMOTE_ADDR]);
		
		return array("code" => $country_code, "country_name" => $country_name);
	}
	
	function get_current_week(){
		global $ift_start_day, $ift_start_month, $ift_start_year;
		
		$star_date = mktime(0, 0, 0, $ift_start_month, $ift_start_day, $ift_start_year);
		$current_time = time();
		$timeleft = $current_time - $star_date;
		$dayleft = ceil($timeleft/(24*60*60));
		
		$week = ceil(($dayleft)/7);
		if($week <= 0 || $week > 5)
			return 1;
		 
		return $week;
	}
	
	function create_page_navigation($total_record, $current_page_, $item_per_page, $url, $query_string)
    {
        $current_page = $current_page_ + 1;
        $pagerange = 10;
        $num_pages = ($total_record % $item_per_page == 0) ? ceil($total_record / $item_per_page) : ceil($total_record / $item_per_page);

        $rangecount = 0;
        $table = "<ul>";
        $table .= "<li>Trang {$current_page_ }/{$num_pages} &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</li>";
        if ($current_page_ > 1)
        {
            $tmp = $current_page_ - 1;
            $table .= "<li><a target=\"_top\" href='" . $url . "?page=" . $tmp . "&&" . $query_string . "' class=\"prev\">&nbsp;</a></li>";
        }

        if ($num_pages > 1)
        {
            $rangecount = ceil($num_pages / $pagerange);

            $startpage = 0;
            $count = 0;
            for ($i = 1; $i < $rangecount + 1; $i++)
            {
                $startpage = (($i - 1) * $pagerange) + 1;
                $count = min($i * $pagerange, $num_pages);
                if ((($current_page >= $startpage) && ($current_page <= ($i * $pagerange))))
                {
                    for ($j = $startpage; $j < $count + 1; $j++)
                    {
                        if ($j == $current_page_)
                        {
                            $table .= "<li><a target=\"_top\" href='#' class='current'>" . $j . "</a><b></li>";
                        }
                        else
                        {
                            $table .= "<li><a target=\"_top\" href='" . $url . "?page=" . $j . "&&" . $query_string . "'>" . $j . "</a></li>";
                        }
                    }
                }
            }
        }
        if ($current_page_ < $num_pages)
        {
            $tmp2 = $current_page_ + 1;
            $table .= "<li><a target=\"_top\" href='" . $url . "?page=" . $tmp2 . "&&" . $query_string . "' class=\"next\">&nbsp;</a></li>";
        }
        $table .= "<div class=\"clear\"></div></ul>";
        return $table;
    }
	
	function make_friendly_name($str){
		$str = remove_vietnamese_accents($str);
        $str = strtolower(trim($str));
        //$str = preg_replace("/^.$%'`{}~*!#()&_^:’/", "-", $str);
        $str = preg_replace("/[^a-z0-9\s-]/", "-", $str);
        //$str = preg_replace("/[^.$%'`{}~*!#()&_^:’\s-]/", "-", $str);
        $str = trim(preg_replace("/[\s-]+/", " ", $str));
        //$str = trim(substr($result, 0, $maxLength));
        $str = preg_replace("/\s/", "-", $str);
        return $str;
    }
	
	function resizeMarkupEmbedHtml($markup, $dimensions)
	{
		$w = $dimensions['width'];
		$h = $dimensions['height'];
		 
		$patterns = array();
		$replacements = array();
		if( !empty($w) )
		{
			$patterns[] = '/width="([0-9]+)"/';
			$patterns[] = "/width='([0-9]+)'/";
			$patterns[] = '/width:([0-9]+)/';
			 
			$replacements[] = 'width="'.$w.'"';
			$replacements[] = 'width="'.$w.'"';
			$replacements[] = 'width:'.$w;
		}
		 
		if( !empty($h) )
		{
			$patterns[] = '/height="([0-9]+)"/';
			$patterns[] = "/height='([0-9]+)'/";
			$patterns[] = '/height:([0-9]+)/';
			 
			$replacements[] = 'height="'.$h.'"';
			$replacements[] = 'height="'.$h.'"';
			$replacements[] = 'height:'.$h;
		}
		 
		return preg_replace($patterns, $replacements, $markup);
	}
	
	function remove_vietnamese_accents($str)
	{
		$arr_url = array(':','@','#','"',"'",'{','}',',','.');
		$arr_url_re = array('','','','','','','','','');
		$str = str_replace($arr_url,$arr_url_re,$str);
		$accents_arr=array(
			"à","á","ạ","ả","ã","â","ầ","ấ","ậ","ẩ","ẫ","ă",
			"ằ","ắ","ặ","ẳ","ẵ","è","é","ẹ","ẻ","ẽ","ê","ề",
			"ế","ệ","ể","ễ",
			"ì","í","ị","ỉ","ĩ",
			"ò","ó","ọ","ỏ","õ","ô","ồ","ố","ộ","ổ","ỗ","ơ",
			"ờ","ớ","ợ","ở","ỡ",
			"ù","ú","ụ","ủ","ũ","ư","ừ","ứ","ự","ử","ữ",
			"ỳ","ý","ỵ","ỷ","ỹ",
			"đ",
			"À","Á","Ạ","Ả","Ã","Â","Ầ","Ấ","Ậ","Ẩ","Ẫ","Ă",
			"Ằ","Ắ","Ặ","Ẳ","Ẵ",
			"È","É","Ẹ","Ẻ","Ẽ","Ê","Ề","Ế","Ệ","Ể","Ễ",
			"Ì","Í","Ị","Ỉ","Ĩ",
			"Ò","Ó","Ọ","Ỏ","Õ","Ô","Ồ","Ố","Ộ","Ổ","Ỗ","Ơ",
			"Ờ","Ớ","Ợ","Ở","Ỡ",
			"Ù","Ú","Ụ","Ủ","Ũ","Ư","Ừ","Ứ","Ự","Ử","Ữ",
			"Ỳ","Ý","Ỵ","Ỷ","Ỹ",
			"Đ"
		);
	
		$no_accents_arr=array(
			"a","a","a","a","a","a","a","a","a","a","a",
			"a","a","a","a","a","a",
			"e","e","e","e","e","e","e","e","e","e","e",
			"i","i","i","i","i",
			"o","o","o","o","o","o","o","o","o","o","o","o",
			"o","o","o","o","o",
			"u","u","u","u","u","u","u","u","u","u","u",
			"y","y","y","y","y",
			"d",
			"A","A","A","A","A","A","A","A","A","A","A","A",
			"A","A","A","A","A",
			"E","E","E","E","E","E","E","E","E","E","E",
			"I","I","I","I","I",
			"O","O","O","O","O","O","O","O","O","O","O","O",
			"O","O","O","O","O",
			"U","U","U","U","U","U","U","U","U","U","U",
			"Y","Y","Y","Y","Y",
			"D"
		);
	
		return str_replace($accents_arr,$no_accents_arr,$str);
	}
	
	function GetImageFromUrl($link, $file_path){ 
		try{
			$tmp = explode(".", $link);
			$ext = $tmp[count($tmp) - 1];
			$file_name = guid() . "." . $ext;
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_POST, 0);
			curl_setopt($ch,CURLOPT_URL,$link);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			$result=curl_exec($ch);
			curl_close($ch);
			
			$savefile = fopen($file_path.$file_name, 'w');
			fwrite($savefile, $result);
			fclose($savefile);
			
			return $file_name;
		}catch (Exception $e){return '';}
	}
	
	function getExtensionFileName($file_name){
    	$tmp_file = explode(".", $file_name);
        
        return $tmp_file[count($tmp_file) - 1];
    }
	
	
	function metatag(){
		global $app_name, $app_id;
	?>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
	<title><?=$app_name?></title>
	<meta property="og:title" content="<?=$app_name?>"/>
	<meta property="og:type" content="company"/>
    <meta property="og:url" content="<?=$baseURL?>"/>
    <meta property="og:image" content="images/logo.jpg"/>
    <meta property="og:site_name" content="<?=$app_name?>"/>
    <meta property="fb:app_id" content="<?=$appId?>"/>
	
	<script type="text/javascript" src="Scripts/jquery-1.3.2.min.js"></script>
	<script type="text/javascript" src="Scripts/swfobject.js"></script>
	<script type="text/javascript" src="Scripts/functions.js"></script>
	<script type="text/javascript" src="/js/functions.js"></script>
	<script src="/js/jquery.simplemodal.js" type="text/javascript"></script>	
	<link href="/css/fbapp/style.css" rel="stylesheet" type="text/css" />
	<link href="/css/fbapp/layout.css" rel="stylesheet" type="text/css" />
	<link href="/css/normalize.css" rel="stylesheet" type="text/css" />
	<link href="/css/simplemodal.css" rel="stylesheet" type="text/css" />
	<script src="/js/jquery.selectBox.js"></script>
	<script type="text/javascript">
		$(document).ready(function(){
			$("SELECT").selectBox();
		});
	</script>
	<script src="/js/sitescript.js"></script>
	<!--[if IE 6]>
	<link href="/css/ie6.css" rel="stylesheet" type="text/css" />
	<script src="/js/pngfix.js"></script>
	<script>
		DD_belatedPNG.fix('.png_bg');
		DD_belatedPNG.fix('.png_img');
		DD_belatedPNG.fix('#controls');
		DD_belatedPNG.fix('#controls li a');
	</script>
	<![endif]-->
	<!--[if IE 7]>
	<link href="/css/ie7.css" rel="stylesheet" type="text/css" />
	<![endif]-->
	<?
	}
	
	function header_page(){
		global $app_id, $locale, $lang;
	?>
		<div id="fb-root"></div>
        <script type="text/javascript" src="http://connect.facebook.net/en_US/all.js"></script>
		<script type="text/javascript">
			FB.init({
				appId  : '<?=$app_id?>',
				session : null, // don't refetch the session when PHP already has it
				status : true,
				cookie : true,
				xfbml  : true
			});
		</script>
	<?
	}
	
	function footer_page(){
		global $app_id, $locale, $lang, $network, $secure_url;
	?>		
		<script type="text/javascript">
		  var _gaq = _gaq || [];
		  _gaq.push(['_setAccount', 'UA-16440934-2']);
		  _gaq.push(['_trackPageview']);

		  (function() {
			var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
			ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
			var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
		  })();
		function trackGAPageView(s)
		{
			_gaq.push(['_trackPageview', "/" + s]);
		}
		function gaBecomeAFan(){
			_gaq.push(['_trackPageview', "/fan_page"]);
			return true;
		}
		</script>
	<?
	}
?>