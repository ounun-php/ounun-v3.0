<?php
/**
 * [Ounun System] Copyright (c) 2019 Ounun.ORG
 * Ounun.ORG is NOT a free software, it under the license terms, visited https://www.ounun.org/ for more details.
 */

class session_storage_memcache extends session_storage
{
	function __construct($options = [])
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
