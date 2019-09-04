<?php

class session_storage_apc extends session_storage
{
	function __construct($options = array())
	{
		if (!$this->test())
		{
            throw new Exception("The apc extension isn't available");
        }
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
