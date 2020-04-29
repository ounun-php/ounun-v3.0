<?php
namespace ounun\client\session\storage;


use ounun\http\session\storage;

/**
 * [Ounun System] Copyright (c) 2019 Ounun.ORG
 * Ounun.ORG is NOT a free software, it under the license terms, visited https://www.ounun.org/ for more details.
 */

class xcache extends storage
{
    /**
     * xcache constructor.
     * @param array $options
     * @throws \Exception
     */
	public function __construct($options = [])
	{
		if (!$this->test()) {
            throw new \Exception("The xcache extension isn't available");
        }
        parent::__construct($options);
        $this->register();
	}

    public function open($save_path, $session_name)
	{
		return true;
	}

	public function close()
	{
		return true;
	}

    public function read($id)
	{
		$sess_id = 'sess_'.$id;
		if(!xcache_isset( $sess_id )) {
		    return;
        }
		return (string) xcache_get($sess_id);
	}

    public function write($id, $session_data)
	{
		$sess_id = 'sess_'.$id;
		return xcache_set($sess_id, $session_data, ini_get("session.gc_maxlifetime"));
	}

	public function destroy($id)
	{
		$sess_id = 'sess_'.$id;
		if(!xcache_isset( $sess_id )) {
		    return true;
        }
		return xcache_unset($sess_id);
	}

    public function gc($maxlifetime)
	{
		return true;
	}

    public function test()
	{
		return extension_loaded('xcache');
	}
}
