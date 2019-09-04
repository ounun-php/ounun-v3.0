<?php

class session_storage_xcache extends session_storage
{
	function __construct($options = array())
	{
		if (!$this->test())
		{
            throw new Exception("The xcache extension isn't available");
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
		if(!xcache_isset( $sess_id )) return;
		return (string) xcache_get($sess_id);
	}

	function write($id, $session_data)
	{
		$sess_id = 'sess_'.$id;
		return xcache_set($sess_id, $session_data, ini_get("session.gc_maxlifetime"));
	}

	function destroy($id)
	{
		$sess_id = 'sess_'.$id;
		if(!xcache_isset( $sess_id )) return true;
		return xcache_unset($sess_id);
	}

	function gc($maxlifetime)
	{
		return true;
	}

	function test()
	{
		return extension_loaded('xcache');
	}
}
