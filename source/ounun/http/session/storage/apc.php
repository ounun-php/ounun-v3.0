<?php
namespace ounun\http\session\storage;
use ounun\http\session\storage;

/**
 * [Ounun System] Copyright (c) 2019 Ounun.ORG
 * Ounun.ORG is NOT a free software, it under the license terms, visited https://www.ounun.org/ for more details.
 */

class apc extends storage
{
    /**
     * session_storage_apc constructor.
     * @param array $options
     * @throws
     */
    public function __construct($options = [])
	{
		if (!$this->test()) {
            throw new \Exception("The apc extension isn't available");
        }
        parent::__construct($options);
        $this->register();
	}

	function open($save_path, $session_name)
	{
		return true;
	}

	function close()
	{
		return true;
	}

	function read($id)
	{
		$sess_id = 'sess_'.$id;
		return (string) apc_fetch($sess_id);
	}

	function write($id, $session_data)
	{
		$sess_id = 'sess_'.$id;
		return apc_store($sess_id, $session_data, ini_get("session.gc_maxlifetime"));
	}

	function destroy($id)
	{
		$sess_id = 'sess_'.$id;
		return apc_delete($sess_id);
	}

	function gc($maxlifetime)
	{
		return true;
	}

	function test()
	{
		return extension_loaded('apc');
	}
}
