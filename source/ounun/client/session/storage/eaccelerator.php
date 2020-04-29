<?php
namespace ounun\client\session\storage;


use ounun\http\session\storage;

/**
 * [Ounun System] Copyright (c) 2019 Ounun.ORG
 * Ounun.ORG is NOT a free software, it under the license terms, visited https://www.ounun.org/ for more details.
 */

class eaccelerator extends storage
{
    /**
     * eaccelerator constructor.
     * @param array $options
     * @throws
     */
    public	function __construct( $options = [] )
	{
		if (!$this->test()) {
            throw new \Exception("The eaccelerator extension isn't available");
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
		return (string) eaccelerator_get($sess_id);
	}

    public function write($id, $session_data)
	{
		$sess_id = 'sess_'.$id;
		return eaccelerator_put($sess_id, $session_data, ini_get("session.gc_maxlifetime"));
	}

    public function destroy($id)
	{
		$sess_id = 'sess_'.$id;
		return eaccelerator_rm($sess_id);
	}

    public function gc($maxlifetime)
	{
		eaccelerator_gc();
		return true;
	}

    public function test()
	{
		return (extension_loaded('eaccelerator') && function_exists('eaccelerator_get'));
	}
}
