<?php

class request
{
	static $_request_url,
	       $_request_uri,
	       $_request_base,
	       $_pathinfo;
	
	public static function &get_instance()
	{
		static $instance;
		if (!is_null($instance)) return $instance;
		$instance = new request();
		return $instance;
	}

	public static function get_method()
	{
		return $_SERVER['REQUEST_METHOD'];
	}

	public static function get_base()
	{
		if (!is_null(self::$_request_base)) return self::$_request_base;
		$base = self::is_ssl() ? 'https://' : 'http://';
		$base .= self::get_host();
		self::$_request_base = $base;
		return $base;
	}
	
	public static function get_url()
	{
		if (!is_null(self::$_request_url)) return self::$_request_url;
		$url = self::is_ssl() ? 'https://' : 'http://';
		$url .= self::get_host();
		$url .= self::get_uri();
		self::$_request_url = $url;
		return $url;
	}

	public static function get_uri()
	{
		if (!is_null(self::$_request_uri)) return self::$_request_uri;

		if (isset($_SERVER['HTTP_X_REWRITE_URL']))
		{
			$uri = $_SERVER['HTTP_X_REWRITE_URL'];
		}
		elseif (isset($_SERVER['REQUEST_URI']))
		{
			$uri = $_SERVER['REQUEST_URI'];
		}
		elseif (isset($_SERVER['ORIG_PATH_INFO']))
		{
			$uri = $_SERVER['ORIG_PATH_INFO'];
			if (! empty($_SERVER['QUERY_STRING']))
			{
				$uri .= '?' . $_SERVER['QUERY_STRING'];
			}
		}
		else
		{
			$uri = '';
		}
		self::$_request_uri = $uri;
		return $uri;
	}

	public static function get_querystring()
	{
		return $_SERVER['QUERY_STRING'];
	}

	public static function get_pathinfo()
	{
		if (!is_null(self::$_pathinfo)) return self::$_pathinfo;

		if (!empty($_SERVER['PATH_INFO']))
		{
			self::$_pathinfo = $_SERVER['PATH_INFO'];
			return $_SERVER['PATH_INFO'];
		}
		$pathinfo = substr(self::get_uri(), strlen(self::get_scriptname()));
		if(substr($pathinfo, 0, 1) == '/')
		{
			if ($_SERVER['QUERY_STRING']) $pathinfo = substr($pathinfo, 0, strpos($pathinfo, '?'));
			self::$_pathinfo = $pathinfo;
		}
		return self::$_pathinfo;
	}

	public static function get_scriptname()
	{
		$script = self::get_env('SCRIPT_NAME');
		return $script ? $script : self::get_env('ORIG_SCRIPT_NAME');
	}

	public static function get_referer()
	{
		return self::get_env('HTTP_REFERER');
	}

	public static function get_host()
	{
		$host = self::get_env('HTTP_X_FORWARDED_HOST');
		return $host ? $host : self::get_env('HTTP_HOST');
	}

	public static function get_language()
	{
		return self::get_env('HTTP_ACCEPT_LANGUAGE');
	}

	public static function get_charset()
	{
		return $_SERVER['HTTP_ACCEPT_CHARSET'];
	}

	public static function get_clientip()
	{
		if(getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown'))
		{
			$ip = getenv('HTTP_CLIENT_IP');
		}
		elseif(getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown'))
		{
			$ip = getenv('HTTP_X_FORWARDED_FOR');
		}
		elseif(getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown'))
		{
			$ip = getenv('REMOTE_ADDR');
		}
		elseif(isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown'))
		{
			$ip = $_SERVER['REMOTE_ADDR'];
		}
		return preg_match("/[\d\.]{7,15}/", $ip, $matches) ? $matches[0] : 'unknown';
	}

	public static function get_env($key)
	{
		return isset($_SERVER[$key]) ? $_SERVER[$key] : (isset($_ENV[$key]) ? $_ENV[$key] : FALSE);
	}
	
	public static function clean()
	{
		
	}

	public static function is_ssl()
	{
		return (strtolower(self::get_env('HTTPS')) === 'on' || strtolower(self::get_env('HTTP_SSL_HTTPS')) === 'on' || self::get_env('HTTP_X_FORWARDED_PROTO') == 'https');
	}

	public static function is_XmlHttpRequest()
	{
		return (self::get_env('HTTP_X_REQUESTED_WITH') == 'XMLHttpRequest');
	}

	public static function is_post()
	{
		return $_SERVER['REQUEST_METHOD'] === 'POST';
	}

	public static function is_get()
	{
		return $_SERVER['REQUEST_METHOD'] === 'GET';
	}

	public static function is_ie()
	{
		return strpos(self::get_env('HTTP_USER_AGENT'), 'MSIE') ? TRUE : FALSE;
	}

	public static function is_spider()
	{
		static $is_spider;
		if(!is_null($is_spider)) return $is_spider;
		$browsers = 'msie|netscape|opera|konqueror|mozilla';
		$spiders = 'bot|spider|google|isaac|surveybot|baiduspider|yahoo|sohu-search|yisou|3721|qihoo|daqi|ia_archiver|p.arthur|fast-webcrawler|java|microsoft-atl-native|turnitinbot|webgather|sleipnir|msn';
		if(preg_match("/($browsers)/i", $_SERVER['HTTP_USER_AGENT']))
		{
			$is_spider = FALSE;
		}
		elseif(preg_match("/($spiders)/i", $_SERVER['HTTP_USER_AGENT']))
		{
			$is_spider = TRUE;
		}
		return $is_spider;
	}
}