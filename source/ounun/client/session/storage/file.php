<?php
namespace ounun\client\session\storage;


use ounun\http\session\storage;

/**
 * [Ounun System] Copyright (c) 2019 Ounun.ORG
 * Ounun.ORG is NOT a free software, it under the license terms, visited https://www.ounun.org/ for more details.
 */

class file extends storage
{
    /**
     * file constructor.
     * @param array $options
     */
    public function __construct($options = [])
	{
		$path = $options['session_n'] > 0 ? $options['session_n'].';"'.$options['session_path'].'"' : $options['session_path'];
        parent::__construct($options);
		ini_set('session.save_handler', 'files');
    	session_save_path($path);
	}
}
