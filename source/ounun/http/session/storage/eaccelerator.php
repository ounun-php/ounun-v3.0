<?php

class session_storage_eaccelerator extends session_storage
{
	function __construct( $options = array() )
	{
		if (!$this->test())
		{
            throw new Exception("The eaccelerator extension isn't available");
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
		return (string) eaccelerator_get($sess_id);
	}

	function write($id, $session_data)
	{
		$sess_id = 'sess_'.$id;
		return eaccelerator_put($sess_id, $session_data, ini_get("session.gc_maxlifetime"));
	}

	function destroy($id)
	{
		$sess_id = 'sess_'.$id;
		return eaccelerator_rm($sess_id);
	}

	function gc($maxlifetime)
	{
		eaccelerator_gc();
		return true;
	}

	function test()
	{
		return (extension_loaded('eaccelerator') && function_exists('eaccelerator_get'));
	}
}
