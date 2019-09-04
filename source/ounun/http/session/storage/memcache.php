<?php

class session_storage_memcache extends session_storage
{
	function __construct($options = array())
	{
		if(!$this->test())
		{
            throw new Exception("The memcache extension isn't available");
        }
		ini_set('session.save_handler', 'memcache');
		ini_set('session.save_path', $options['memcache_servers']);
	}
	
	function test()
	{
		return extension_loaded('memcache');
	}
}
