<?php

class response
{
	private static $_cache;
	protected static $status = array(
	'100' => 'Continue',
	'101' => 'Switching Protocols',
	'200' => 'OK',
	'201' => 'Created',
	'202' => 'Accepted',
	'203' => 'Non-Authoritative Information',
	'204' => 'No Content',
	'205' => 'Reset Content',
	'206' => 'Partial Content',
	'300' => 'Multiple Choices',
	'301' => 'Moved Permanently',
	'302' => 'Found',
	'303' => 'See Other',
	'304' => 'Not Modified',
	'305' => 'Use Proxy',
	'306' => '(Unused)',
	'307' => 'Temporary Redirect',
	'400' => 'Bad Request',
	'401' => 'Unauthorized',
	'402' => 'Payment Required',
	'403' => 'Forbidden',
	'404' => 'Not Found',
	'405' => 'Method Not Allowed',
	'406' => 'Not Acceptable',
	'407' => 'Proxy Authentication Required',
	'408' => 'Request Timeout',
	'409' => 'Conflict',
	'410' => 'Gone',
	'411' => 'Length Required',
	'412' => 'Precondition Failed',
	'413' => 'Request Entity Too Large',
	'414' => 'Request-URI Too Long',
	'415' => 'Unsupported Media Type',
	'416' => 'Requested Range Not Satisfiable',
	'417' => 'Expectation Failed',
	'500' => 'Internal Server Error',
	'501' => 'Not Implemented',
	'502' => 'Bad Gateway',
	'503' => 'Service Unavailable',
	'504' => 'Gateway Timeout',
	'505' => 'HTTP Version Not Supported',
	);
  
    function __construct()
    {
    	ob_start('output');
    }

	public static function set_status($code, $text = null)
	{
		if (is_null($text) && isset(self::$status[$code])) $text = self::$status[$code];
		header($_SERVER["SERVER_PROTOCOL"].' '.$code.' '.$text);
	}

	public static function set_contenttype($contenttype = 'text/html', $charset = 'utf-8')
	{
		header("Content-type: $contenttype; charset=$charset");
	}
	
	public static function set_cache($ttl = null)
	{
		if (!is_null($ttl))
		{
			if ($ttl)
			{
				header('Cache-Control: max-age='.$ttl.',must-revalidate');
				header('Pragma:');
				header('Expires: '.gmdate('D, d M Y H:i:s', time() + $ttl).' GMT');
			}
			else
			{
				header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
				header('Pragma: no-cache');
				header('Expires: Mon, 1 Jan 2001 00:00:00 GMT');
			}
		}
	}
	
	public function __toString()
	{
		return ob_get_clean();
	}
}