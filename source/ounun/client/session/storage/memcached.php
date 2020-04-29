<?php
namespace ounun\client\session\storage;


use ounun\http\session\storage;

/**
 * [Ounun System] Copyright (c) 2019 Ounun.ORG
 * Ounun.ORG is NOT a free software, it under the license terms, visited https://www.ounun.org/ for more details.
 */

class memcached extends storage
{
    /**
     * memcache constructor.
     * @param array $options
     * @throws
     */
    public function __construct($options = [])
	{
		if(!$this->test()) {
            throw new \Exception("The memcache extension isn't available");
        }
        parent::__construct($options);
		ini_set('session.save_handler', 'memcache');
		ini_set('session.save_path', $options['memcache_servers']);
	}

    public function test()
	{
		return extension_loaded('memcache');
	}
}
