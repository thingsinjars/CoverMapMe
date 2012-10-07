<?php
    class Cache {  
        function __construct($dir)  
        {  
            $this->dir = $dir;  
        }  
      
        private function _name($key)  
        {  
            return sprintf("%s/%s", $this->dir, sha1($key));  
        }  
      
        public function get($key, $expiration = 3600)  
        {  
      
            if ( !is_dir($this->dir) OR !is_writable($this->dir))  
            {  
                return FALSE;  
            }  
      
            $cache_path = $this->_name($key);  
      
            if (!@file_exists($cache_path))  
            {  
                return FALSE;  
            }  
      
            if (filemtime($cache_path) < (time() - $expiration))  
            {  
                $this->clear($key);  
                return FALSE;  
            }  
      
            if (!$fp = @fopen($cache_path, 'rb'))  
            {  
                return FALSE;  
            }  
      
            flock($fp, LOCK_SH);  
      
            $cache = '';  
      
            if (filesize($cache_path) > 0)  
            {  
                $cache = unserialize(fread($fp, filesize($cache_path)));  
            }  
            else  
            {  
                $cache = NULL;  
            }  
      
            flock($fp, LOCK_UN);  
            fclose($fp);  
      
            return $cache;  
        }  
      
        public function set($key, $data)  
        {  
      
            if ( !is_dir($this->dir) OR !is_writable($this->dir))  
            {  
                return FALSE;  
            }  
      
            $cache_path = $this->_name($key);  
      
            if ( ! $fp = fopen($cache_path, 'wb'))  
            {  
                return FALSE;  
            }  
      
            if (flock($fp, LOCK_EX))  
            {  
                fwrite($fp, serialize($data));  
                flock($fp, LOCK_UN);  
            }  
            else  
            {  
                return FALSE;  
            }  
            fclose($fp);  
            @chmod($cache_path, 0777);  
            return TRUE;  
        }  
      
        public function clear($key)  
        {  
            $cache_path = $this->_name($key);  
      
            if (file_exists($cache_path))  
            {  
                unlink($cache_path);  
                return TRUE;  
            }  
      
            return FALSE;  
        }
		
		public function remove_vietnamese_accents($str)
		{
			$accents_arr=array(
				"à","á","?","?","ã","â","?","?","?","?","?","a",
				"?","?","?","?","?","è","é","?","?","?","ê","?",
				"?","?","?","?",
				"ì","í","?","?","i",
				"ò","ó","?","?","õ","ô","?","?","?","?","?","o",
				"?","?","?","?","?",
				"ù","ú","?","?","u","u","?","?","?","?","?",
				"?","ý","?","?","?",
				"d",
				"À","Á","?","?","Ã","Â","?","?","?","?","?","A",
				"?","?","?","?","?",
				"È","É","?","?","?","Ê","?","?","?","?","?",
				"Ì","Í","?","?","I",
				"Ò","Ó","?","?","Õ","Ô","?","?","?","?","?","O",
				"?","?","?","?","?",
				"Ù","Ú","?","?","U","U","?","?","?","?","?",
				"?","Ý","?","?","?",
				"Ð"
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
		
		public function make_friendly_name($str){
			$str = $this->remove_vietnamese_accents($str);
			$str = strtolower(trim($str));
			//$str = preg_replace("/^.$%'`{}~*!#()&_^:’/", "-", $str);
			$str = preg_replace("/[^a-z0-9\s-]/", "-", $str);
			//$str = preg_replace("/[^.$%'`{}~*!#()&_^:’\s-]/", "-", $str);
			$str = trim(preg_replace("/[\s-]+/", " ", $str));
			//$str = trim(substr($result, 0, $maxLength));
			$str = preg_replace("/\s/", "-", $str);
			return $str;
		}
    }  
?>